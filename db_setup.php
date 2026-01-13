<?php
// DB setup script for XAMPP - run once via browser: http://localhost/feenix/db_setup.php
require_once __DIR__ . '/config.php';

// Connect without selecting DB to create it if missing
$tmp = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($tmp->connect_error) {
    die('Connection to MySQL failed: ' . $tmp->connect_error);
}

echo "<h2>BBIS Fee DB Setup</h2>";
echo "<p>Connected to MySQL server as " . htmlspecialchars(DB_USER) . "@" . htmlspecialchars(DB_HOST) . "</p>";

$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($tmp->query($sql)) {
    echo "<p>Database <strong>" . htmlspecialchars(DB_NAME) . "</strong> created or already exists.</p>";
} else {
    die('Failed to create database: ' . $tmp->error);
}
$tmp->close();

// Now use existing helpers to create tables
$conn = getDB();

$createUsers = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') NOT NULL DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($createUsers)) {
    echo "<p>Table <strong>users</strong> exists or was created.</p>";
} else {
    die('Create users table failed: ' . $conn->error);
}

$createUploads = "CREATE TABLE IF NOT EXISTS fee_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    orig_filename VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    remarks VARCHAR(255) DEFAULT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($createUploads)) {
    echo "<p>Table <strong>fee_uploads</strong> exists or was created.</p>";
} else {
    die('Create fee_uploads table failed: ' . $conn->error);
}

// Create uploads folder
if (!is_dir(UPLOAD_DIR)) {
    if (mkdir(UPLOAD_DIR, 0755, true)) {
        echo "<p>Uploads directory created at " . htmlspecialchars(UPLOAD_DIR) . "</p>";
    } else {
        echo "<p style='color:red'>Failed to create uploads directory at " . htmlspecialchars(UPLOAD_DIR) . " - check permissions.</p>";
    }
} else {
    echo "<p>Uploads directory already exists: " . htmlspecialchars(UPLOAD_DIR) . "</p>";
}

// Insert default admin if missing
$adminEmail = 'admin@kusom.edu.np';
$check = $conn->prepare('SELECT id FROM users WHERE email = ?');
$check->bind_param('s', $adminEmail);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $adminPass = password_hash('Admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $name = 'Admin User';
    $role = 'admin';
    $stmt->bind_param('ssss', $name, $adminEmail, $adminPass, $role);
    if ($stmt->execute()) {
        echo "<p>Default admin created: <strong>{$adminEmail}</strong> with password <strong>admin123</strong></p>";
    } else {
        echo "<p style='color:red'>Failed to insert admin: " . htmlspecialchars($stmt->error) . "</p>";
    }
    $stmt->close();
} else {
    echo "<p>Admin user already exists: <strong>{$adminEmail}</strong></p>";
}
$check->close();

$conn->close();

echo "<p>Setup complete. Remove this file for security: <code>db_setup.php</code></p>";

// Also output CLI hint
echo "<pre style=\"background:#f6f6f6;padding:10px;border-radius:4px\">To run from command line (PHP CLI):\nphp " . __FILE__ . "\n</pre>";

?>
