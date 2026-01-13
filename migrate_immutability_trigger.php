<?php
require_once __DIR__ . '/config.php';

$conn = getDB();

$triggerName = "prevent_status_change";
$sqlDrop = "DROP TRIGGER IF EXISTS $triggerName";

$sqlCreate = "
CREATE TRIGGER $triggerName
BEFORE UPDATE ON fee_uploads
FOR EACH ROW
BEGIN
    -- Allow updating other fields, but strictly control status changes
    -- If the old status was final (Approved or Rejected) AND the new status is different
    IF OLD.status IN ('Approved', 'Rejected') AND OLD.status != NEW.status THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot change status of an approved or rejected receipt.';
    END IF;
END;
";

if ($conn->query($sqlDrop) === TRUE) {
    echo "Old trigger dropped (if existed).\n";
} else {
    echo "Error dropping trigger: " . $conn->error . "\n";
}

if ($conn->query($sqlCreate) === TRUE) {
    echo "Trigger '$triggerName' created successfully. Database level enforcement active.\n";
} else {
    echo "Error creating trigger: " . $conn->error . "\n";
}

$conn->close();
?>
