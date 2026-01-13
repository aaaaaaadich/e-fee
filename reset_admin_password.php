<?php
// Admin password reset script - run once via browser or CLI
require_once __DIR__ . '/config.php';

$conn = getDB();

$adminEmail = 'admin@kusom.edu.np';
$newPassword = 'Admin123'; // Change this to your desired password

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ?');
$stmt->bind_param('ss', $hashedPassword, $adminEmail);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<h2>Password Reset Successful</h2>";
        echo "<p>Admin password has been updated for: <strong>" . htmlspecialchars($adminEmail) . "</strong></p>";
        echo "<p>New password: <strong>" . htmlspecialchars($newPassword) . "</strong></p>";
        echo "<p style='color:green'>You can now log in with the new password.</p>";
    } else {
        echo "<p style='color:orange'>No user found with email: " . htmlspecialchars($adminEmail) . "</p>";
        echo "<p>Make sure you've run db_setup.php first to create the admin user.</p>";
    }
} else {
    echo "<p style='color:red'>Error updating password: " . htmlspecialchars($stmt->error) . "</p>";
}

$stmt->close();
$conn->close();

echo "<hr><p><strong>Security Note:</strong> Delete this file after use: <code>reset_admin_password.php</code></p>";
?>
