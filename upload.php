<?php
date_default_timezone_set('Asia/Kathmandu');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mail_helper.php';
require_role('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash'] = 'File upload failed.';
    header('Location: dashboard.php');
    exit;
}

$file = $_FILES['receipt'];
$allowed = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!array_key_exists($ext, $allowed)) {
    $_SESSION['flash'] = 'Invalid file type.';
    header('Location: dashboard.php');
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    $_SESSION['flash'] = 'File too large. Max 5MB.';
    header('Location: dashboard.php');
    exit;
}

// Build a safe filename and destination
$safeName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$dest = UPLOAD_DIR . '/' . $safeName;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    $_SESSION['flash'] = 'Failed to move uploaded file.';
    header('Location: dashboard.php');
    exit;
}

// Compute file hash to detect duplicates
$file_hash = hash_file('sha256', $dest);

$conn = getDB();
// Check duplicate by hash for the same user (prevent same user re-uploading identical file)
$check = $conn->prepare('SELECT id FROM fee_uploads WHERE file_hash = ? AND user_id = ?');
if ($check === false) {
    // Likely missing column/table - cleanup and notify admin
    @unlink($dest);
    $_SESSION['flash'] = 'Server configuration error: ' . htmlspecialchars($conn->error) . '. Please run the migration script.';
    $conn->close();
    header('Location: dashboard.php');
    exit;
}
$uid = $_SESSION['user_id'];
$check->bind_param('si', $file_hash, $uid);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    // Duplicate found - remove uploaded file and notify user
    @unlink($dest);
    $_SESSION['flash'] = 'Duplicate upload detected. This receipt (or identical file) already exists.';
    $check->close();
    $conn->close();
    header('Location: dashboard.php');
    exit;
}
$check->close();

// Generate a shorter tracking id (8 chars) and ensure uniqueness
function generate_tracking_id() {
    return 'TRK' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}
$tracking_id = generate_tracking_id();

// Capture Year and Semester
$student_year = $_POST['student_year'] ?? '';
$semester = $_POST['semester'] ?? '';

// Basic validation
$valid_years = ['First Year', 'Second Year', 'Third Year', 'Fourth Year'];
if (!in_array($student_year, $valid_years)) {
    @unlink($dest);
    $_SESSION['flash'] = 'Invalid Academic Year selected.';
    header('Location: dashboard.php');
    exit;
}
if (empty($semester)) {
     @unlink($dest);
    $_SESSION['flash'] = 'Semester is required.';
    header('Location: dashboard.php');
    exit;
}

// Prepare insert statement
$stmt = $conn->prepare('INSERT INTO fee_uploads (user_id, orig_filename, filename, file_path, tracking_id, student_year, semester, file_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
if ($stmt === false) {
    // Likely missing column/table - cleanup and notify admin
    @unlink($dest);
    $_SESSION['flash'] = 'Server configuration error: ' . htmlspecialchars($conn->error) . '. Please run the migration script.';
    $conn->close();
    header('Location: dashboard.php');
    exit;
}
$u = $_SESSION['user_id'];
$orig = $file['name'];
$path = 'uploads/' . $safeName;
$stmt->bind_param('isssssss', $u, $orig, $safeName, $path, $tracking_id, $student_year, $semester, $file_hash);
if (!$stmt->execute()) {
    // Insert failed - cleanup file and show error
    @unlink($dest);
    $_SESSION['flash'] = 'Failed to save upload. Please try again.';
    $stmt->close();
    $conn->close();
    header('Location: dashboard.php');
    exit;
}
$stmt->close();
$conn->close();

// Fetch user email and name for notification
$conn = getDB();
$userStmt = $conn->prepare('SELECT email, name FROM users WHERE id = ?');
$userStmt->bind_param('i', $u);
$userStmt->execute();
$userStmt->bind_result($userEmail, $userName);
$userStmt->fetch();
$userStmt->close();
$conn->close();

// Send email notification
if (!empty($userEmail)) {
    $subject = "Fee Receipt Upload Successful - KUSOM";
    $message = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
            <h2 style='color: #800000;'>Receipt Upload Confirmation</h2>
            <p>Dear {$userName},</p>
            <p>Your fee receipt has been uploaded successfully to the KUSOM Feenix System.</p>
            <p><strong>Tracking ID:</strong> {$tracking_id}</p>
            <p><strong>Academic Year:</strong> {$student_year}</p>
            <p><strong>Semester:</strong> {$semester}</p>
            <p><strong>Status:</strong> Pending Review</p>
            <p>You will be notified upon approval or rejection of your receipt.</p>
            <p>Thank you for using the KUSOM Feenix System.</p>
            <hr style='border: 1px solid #ddd; margin: 20px 0;'>
            <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
        </div>
    </body>
    </html>
    ";
    send_notification_email($userEmail, $subject, $message);
}

$_SESSION['flash'] = 'Upload successful. Tracking ID: ' . $tracking_id . '. Awaiting approval.';
header('Location: dashboard.php');
exit;
