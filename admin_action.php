<?php
require_once __DIR__ . '/config.php';
require_role('admin');

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: admin.php');
    exit;
}

$conn = getDB();

if ($action === 'approve' || $action === 'reject' || $action === 'verify') {
    if ($action === 'approve') $status = 'Approved';
    elseif ($action === 'verify') $status = 'Verified';
    else $status = 'Rejected';
    if ($action === 'reject') {
        $remarks = $_POST['remarks'] ?? '';
        $stmt = $conn->prepare('UPDATE fee_uploads SET status = ?, remarks = ? WHERE id = ?');
        $stmt->bind_param('ssi', $status, $remarks, $id);
    } else {
        $stmt = $conn->prepare('UPDATE fee_uploads SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);
    }
    $stmt->execute();
    $stmt->close();

    // Notify student by email (basic mail()). Configure PHP mail on server for this to work.
    $q = $conn->prepare('SELECT u.email, u.name, f.orig_filename FROM fee_uploads f JOIN users u ON f.user_id = u.id WHERE f.id = ?');
    $q->bind_param('i', $id);
    $q->execute();
    $q->bind_result($stuEmail, $stuName, $origFile);
    if ($q->fetch()) {
        $to = $stuEmail;
        $subject = "Fee Receipt {$status} - KUSOM";
        $message = "Dear {$stuName},\n\nYour uploaded receipt ('{$origFile}') has been {$status}.\n\nRegards,\nKUSOM Administration";
        $headers = 'From: admin@kusom.edu.np' . "\r\n";
        // Suppress errors to avoid exposing server details; developer can log if needed.
        @mail($to, $subject, $message, $headers);
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
