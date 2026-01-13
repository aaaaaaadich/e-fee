<?php
// Migration script to add password reset columns to users table
require_once __DIR__ . '/config.php';

echo "<h2>Password Reset Migration</h2>";
echo "<p>Adding password reset columns to users table...</p>";

$conn = getDB();

// Add reset_token and reset_token_expiry columns
$sql = "ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS reset_token_expiry DATETIME DEFAULT NULL";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Password reset columns added successfully!</p>";
} else {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($conn->error) . "</p>";
}

$conn->close();
echo "<p><a href='index.php'>Go to Home</a></p>";
?>
