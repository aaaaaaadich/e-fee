<?php
// Migration helper: add tracking_id and file_hash to fee_uploads if missing
require_once __DIR__ . '/config.php';

$conn = getDB();
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM fee_uploads");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $cols[$r['Field']] = true;
    }
    $res->free();
} else {
    die('fee_uploads table not found or DB error: ' . $conn->error);
}
$queries = [];
if (!isset($cols['tracking_id'])) {
    $queries[] = "ALTER TABLE fee_uploads ADD COLUMN tracking_id VARCHAR(64) NOT NULL UNIQUE AFTER file_path";
}
if (!isset($cols['file_hash'])) {
    $queries[] = "ALTER TABLE fee_uploads ADD COLUMN file_hash VARCHAR(128) NOT NULL AFTER tracking_id";
}
if (empty($queries)) {
    echo "No changes needed. Columns already present.";
    $conn->close();
    exit;
}
foreach ($queries as $q) {
    if ($conn->query($q) === false) {
        echo "Failed: " . htmlspecialchars($conn->error) . "\n";
    } else {
        echo "Executed: " . htmlspecialchars($q) . "\n";
    }
}
$conn->close();
echo "\nMigration complete.\n";
?>