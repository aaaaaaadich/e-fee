<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mail_helper.php';
require_role('admin');

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: admin.php');
    exit;
}

$conn = getDB();

if ($action === 'approve' || $action === 'reject' || $action === 'verify') {
    // 1. Strict Status Enforcement: Only allow actions if current status is 'Pending'
    $checkStmt = $conn->prepare('SELECT status FROM fee_uploads WHERE id = ?');
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $checkStmt->bind_result($currentStatus);
    if (!$checkStmt->fetch()) {
        $checkStmt->close();
        // Record not found
        header('Location: admin.php');
        exit;
    }
    $checkStmt->close();

    if ($currentStatus !== 'Pending') {
        $_SESSION['flash'] = "Action denied. Receipt status is already '{$currentStatus}'.";
        header('Location: admin.php');
        exit;
    }

    // 2. Proceed with Update
    if ($action === 'approve') $status = 'Approved';
    elseif ($action === 'verify') $status = 'Verified';
    else $status = 'Rejected';
    
    // Remarks handling
    if ($action === 'reject') {
        $remarks = $_POST['remarks'] ?? '';
        $stmt = $conn->prepare('UPDATE fee_uploads SET status = ?, remarks = ? WHERE id = ?');
        $stmt->bind_param('ssi', $status, $remarks, $id);
    } else {
        $stmt = $conn->prepare('UPDATE fee_uploads SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);
    }
    
    // 3. Database & Error Handling
    if (!$stmt->execute()) {
        // This might catch the trigger error if the PHP check somehow failed or race condition
        $_SESSION['flash'] = "Error updating status: " . $stmt->error;
        $stmt->close();
        header('Location: admin.php');
        exit;
    }
    $stmt->close();

    // 4. Send Notification
    $q = $conn->prepare('SELECT u.email, u.name, f.orig_filename FROM fee_uploads f JOIN users u ON f.user_id = u.id WHERE f.id = ?');
    $q->bind_param('i', $id);
    $q->execute();
    $q->bind_result($stuEmail, $stuName, $origFile);
    if ($q->fetch()) {
        // Fix for "Pending" check - fetch() needs to be called after execute
        // But we already fetched above effectively
        
        $subject = "Fee Receipt {$status} - KUSOM";
        $message = "
        <html>
        <body>
            <p>Dear {$stuName},</p>
            <p>Your uploaded receipt ('{$origFile}') has been <strong>{$status}</strong>.</p>
            " . ($status === 'Rejected' && !empty($remarks) ? "<p><strong>Reason:</strong> " . htmlspecialchars($remarks) . "</p>" : "") . "
             " . ($status === 'Rejected' ? "<p>Please upload a new receipt via your dashboard. Do not try to edit this submission.</p>" : "") . "
            <p>Regards,<br>KUSOM Administration</p>
        </body>
        </html>";
        
        send_notification_email($stuEmail, $subject, $message);
    }
    $q->close();

    $_SESSION['flash'] = "Receipt has been {$status}. Notification sent.";
    header('Location: admin.php');
    exit;
}

if ($action === 'download') {
    $stmt = $conn->prepare('SELECT filename, file_path, orig_filename FROM fee_uploads WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($filename, $file_path, $orig);
    if ($stmt->fetch()) {
        $full = __DIR__ . '/' . $file_path;
        if (is_file($full)) {
            // Serve file
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($orig) . '"');
            header('Content-Length: ' . filesize($full));
            readfile($full);
            exit;
        }
    }
    $stmt->close();
    header('Location: admin.php');
    exit;
}

header('Location: admin.php');
exit;
