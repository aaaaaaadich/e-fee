<?php
// Database configuration - update if needed
define('DB_HOST', 'localhost');
// Optional: set DB_PORT to non-standard MySQL port (e.g. 3307)
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bbis_fee');

// Uploads directory
define('UPLOAD_DIR', __DIR__ . '/uploads');

// Session timeout (seconds)
define('SESSION_TIMEOUT', 60 * 30); // 30 minutes

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout handling
if (!empty($_SESSION['user_id'])) {
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    } elseif (time() - $_SESSION['last_activity'] > (defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 1800)) {
        // timeout
        session_unset();
        session_destroy();
        session_start();
    } else {
        $_SESSION['last_activity'] = time();
    }
}

// Production: hide notices/warnings from the UI and log errors instead
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Create and return mysqli connection
function getDB() {
    $host = DB_HOST;
    $port = defined('DB_PORT') ? (int)DB_PORT : 3306;

    // If DB_HOST includes a colon with port (e.g. localhost:3307), split it
    if (strpos(DB_HOST, ':') !== false) {
        [$h, $p] = explode(':', DB_HOST, 2);
        if (is_numeric($p)) {
            $host = $h;
            $port = (int)$p;
        }
    }

    $conn = new mysqli($host, DB_USER, DB_PASS, DB_NAME, $port);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Helper: ensure role protected pages
function require_role($role) {
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== $role) {
        header('Location: login.php');
        exit;
    }
}

// Helper: redirect based on role
function redirect_by_role() {
    if (!isset($_SESSION['role'])) {
        header('Location: login.php');
        exit;
    }
    if ($_SESSION['role'] === 'admin') header('Location: admin.php');
    else header('Location: dashboard.php');
    exit;
}

?>
