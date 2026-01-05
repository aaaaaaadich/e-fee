<?php
// Migration: ensure 'Verified' exists in status enum
require_once __DIR__ . '/config.php';
$conn = getDB();

$res = $conn->query("SHOW COLUMNS FROM fee_uploads LIKE 'status'");
if (!$res || $res->num_rows === 0) {
    die('status column not found');
}
$row = $res->fetch_assoc();
$type = $row['Type'];
if (strpos($type, 'Verified') !== false) {
    echo "status enum already contains Verified\n";
    exit;
}

echo "Altering enum to add Verified...\n";
if ($conn->query("ALTER TABLE fee_uploads MODIFY status ENUM('Pending','Verified','Approved','Rejected') NOT NULL DEFAULT 'Pending'") === false) {
    echo "Failed: " . $conn->error . "\n";
} else {
    echo "Success.\n";
}
$conn->close();
?>
