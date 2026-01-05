<?php
// Improved migration helper: safely add/fill tracking_id and file_hash to fee_uploads
require_once __DIR__ . '/config.php';

$conn = getDB();
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM fee_uploads");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $cols[$r['Field']] = $r;
    }
    $res->free();
} else {
    die('fee_uploads table not found or DB error: ' . $conn->error);
}

// Step 1: add nullable columns if missing
if (!isset($cols['tracking_id'])) {
    echo "Adding nullable tracking_id column...\n";
    if ($conn->query("ALTER TABLE fee_uploads ADD COLUMN tracking_id VARCHAR(64) NULL AFTER file_path") === false) {
        echo "Failed to add tracking_id: " . $conn->error . "\n";
    } else {
        echo "Added tracking_id.\n";
    }
}
if (!isset($cols['file_hash'])) {
    echo "Adding nullable file_hash column...\n";
    if ($conn->query("ALTER TABLE fee_uploads ADD COLUMN file_hash VARCHAR(128) NULL AFTER tracking_id") === false) {
        echo "Failed to add file_hash: " . $conn->error . "\n";
    } else {
        echo "Added file_hash.\n";
    }
}

// Refresh columns
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM fee_uploads");
while ($r = $res->fetch_assoc()) $cols[$r['Field']] = $r;

// Step 2: populate file_hash where missing (compute from file_path if file exists)
if (isset($cols['file_hash'])) {
    echo "Populating missing file_hash values...\n";
    $q = $conn->query("SELECT id, file_path FROM fee_uploads WHERE file_hash IS NULL OR file_hash = ''");
    while ($row = $q->fetch_assoc()) {
        $full = __DIR__ . '/' . $row['file_path'];
        $hash = '';
        if (is_file($full)) {
            $hash = hash_file('sha256', $full);
        }
        $up = $conn->prepare("UPDATE fee_uploads SET file_hash = ? WHERE id = ?");
        $up->bind_param('si', $hash, $row['id']);
        $up->execute();
        $up->close();
    }
    echo "file_hash population complete.\n";
}

// Step 3: populate tracking_id where missing
if (isset($cols['tracking_id'])) {
    echo "Populating missing tracking_id values...\n";
    $q = $conn->query("SELECT id FROM fee_uploads WHERE tracking_id IS NULL OR tracking_id = ''");
    while ($row = $q->fetch_assoc()) {
        // generate unique tracking id and ensure uniqueness
        do {
            $tid = 'TRK' . strtoupper(bin2hex(random_bytes(4))) . time();
            $chk = $conn->prepare('SELECT id FROM fee_uploads WHERE tracking_id = ?');
            $chk->bind_param('s', $tid);
            $chk->execute();
            $chk->store_result();
            $exists = $chk->num_rows > 0;
            $chk->close();
        } while ($exists);
        $up = $conn->prepare("UPDATE fee_uploads SET tracking_id = ? WHERE id = ?");
        $up->bind_param('si', $tid, $row['id']);
        $up->execute();
        $up->close();
    }
    echo "tracking_id population complete.\n";
}

// Step 4: enforce NOT NULL and add UNIQUE index safely
// Add unique index if not exists
$res = $conn->query("SHOW INDEX FROM fee_uploads WHERE Key_name = 'uniq_tracking_id'");
if ($res && $res->num_rows == 0) {
    echo "Adding unique index on tracking_id...\n";
    if ($conn->query("ALTER TABLE fee_uploads MODIFY tracking_id VARCHAR(64) NOT NULL, ADD UNIQUE KEY uniq_tracking_id (tracking_id)") === false) {
        echo "Failed to add unique index: " . $conn->error . "\n";
    } else {
        echo "Unique index added.\n";
    }
} else {
    echo "Unique index already exists or error checking index.\n";
}

// Make file_hash NOT NULL (allow empty string if still missing)
$res = $conn->query("SHOW INDEX FROM fee_uploads WHERE Key_name = 'idx_file_hash'");
if ($res && $res->num_rows == 0) {
    // create index for faster checks
    $conn->query("ALTER TABLE fee_uploads ADD INDEX idx_file_hash (file_hash)");
}
if ($conn->query("ALTER TABLE fee_uploads MODIFY file_hash VARCHAR(128) NOT NULL") === false) {
    echo "Failed to make file_hash NOT NULL: " . $conn->error . "\n";
} else {
    echo "file_hash set to NOT NULL.\n";
}

echo "\nMigration v2 complete.\n";
$conn->close();
?>