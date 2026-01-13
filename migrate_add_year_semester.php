<?php
require_once __DIR__ . '/config.php';

$conn = getDB();

// Add student_year column
try {
    $conn->query("ALTER TABLE fee_uploads ADD COLUMN student_year VARCHAR(50) AFTER tracking_id");
    echo "Added student_year column.<br>";
} catch (Exception $e) {
    echo "student_year column might already exist or error: " . $e->getMessage() . "<br>";
}

// Add semester column
try {
    $conn->query("ALTER TABLE fee_uploads ADD COLUMN semester VARCHAR(50) AFTER student_year");
    echo "Added semester column.<br>";
} catch (Exception $e) {
    echo "semester column might already exist or error: " . $e->getMessage() . "<br>";
}

echo "Migration completed.";
?>
