<?php
// Run this once to create database and tables and an admin user.
require_once __DIR__ . '/config.php';

// Create database if not exists
$tmp = new mysqli(DB_HOST, DB_USER, DB_PASS, '', defined('DB_PORT') ? (int)DB_PORT : 3306);
if ($tmp->connect_error) die('Connection error: ' . $tmp->connect_error);
$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$tmp->query($sql)) die('DB create failed: ' . $tmp->error);
$tmp->close();

$conn = getDB();

// Create users table
$createUsers = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') NOT NULL DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($createUsers)) die('Create users failed: ' . $conn->error);

// Create fee_uploads table
$createUploads = "CREATE TABLE IF NOT EXISTS fee_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    orig_filename VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    tracking_id VARCHAR(64) NOT NULL UNIQUE,
    file_hash VARCHAR(128) NOT NULL,
    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    remarks VARCHAR(255) DEFAULT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($createUploads)) die('Create uploads failed: ' . $conn->error);

// Create uploads directory
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) die('Failed to create uploads directory at ' . UPLOAD_DIR);
}
// Ensure uploads directory is writable
if (!is_writable(UPLOAD_DIR)) {
    if (!chmod(UPLOAD_DIR, 0755)) die('Uploads directory is not writable: ' . UPLOAD_DIR);
}

// Insert default admin if not exists
$adminEmail = 'admin@kusom.edu.np';
$check = $conn->prepare('SELECT id FROM users WHERE email = ?');
$check->bind_param('s', $adminEmail);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $name = 'Admin User';
    $role = 'admin';
    $stmt->bind_param('ssss', $name, $adminEmail, $adminPass, $role);
    if (!$stmt->execute()) {
        die('Failed to create admin user: ' . $stmt->error);
    }
    $stmt->close();
    $created = true;
} else {
    $created = false;
}
$check->close();

$conn->close();

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Installer - BBIS Fee Receipt Upload</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Installer</h1>
    <p>Database and tables have been created (or already existed).</p>
    <?php if ($created): ?>
        <p>Admin account created with email: <strong><?php echo htmlspecialchars($adminEmail); ?></strong>. Please log in and change the password immediately.</p>
    <?php else: ?>
        <p>Admin account already exists or was not created.</p>
    <?php endif; ?>
    <p>Now <a href="login.php">go to Login</a>. For security, remove this file from the server after installation.</p>
</div>
</body>
</html>
