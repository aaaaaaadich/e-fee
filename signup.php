<?php
date_default_timezone_set('Asia/Kathmandu');
session_start();
require 'config.php';
require 'mail_helper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!preg_match('/@kusom\.edu\.np$/i', $email)) {
        $error = "Only emails ending in @kusom.edu.np are allowed (e.g., student@kusom.edu.np).";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)) {
        $error = "Password must be at least 8 characters long and contain at least one uppercase letter.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $conn = getDB();
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
            $stmt->close();
            $conn->close();
        } else {
            $stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
            $ins->bind_param('sss', $name, $email, $hashed_password);
            if ($ins->execute()) {
                $success = "Registration successful! You can now login.";
                
                // Send welcome email
                $subject = "Welcome to Feenix System";
                $message = "
                <html>
                <head>
                    <title>Welcome to Feenix</title>
                </head>
                <body>
                    <h2>Welcome, " . htmlspecialchars($name) . "!</h2>
                    <p>Thank you for registering with the Feenix System.</p>
                    <p>Your account has been successfully created.</p>
                    <p>You can now <a href='http://localhost/feenix/login.php'>login</a> to your dashboard.</p>
                </body>
                </html>
                ";
                send_notification_email($email, $subject, $message);
            } else {
                $error = "Registration failed. Please try again.";
            }
            $ins->close();
            $conn->close();
        }
    }
}

require 'header.php';
?>

<div style="background: linear-gradient(135deg, var(--bg-color) 0%, #e9ecef 100%); min-height: 85vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="card animate-slide-up" style="width: 100%; max-width: 500px; border-top: 5px solid var(--primary-color);">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; background: var(--primary-gradient); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem; box-shadow: var(--shadow-md);">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Create Account</h2>
            <p>Join us to manage your fee receipts easily</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
            <p style="text-align: center;"><a href="login.php" class="btn btn-primary">Go to Login</a></p>
        <?php else: ?>

        <form method="POST" action="signup.php">
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <div style="position: relative;">
                    <input type="text" name="name" id="name" class="form-control" placeholder="John Doe" style="padding-left: 45px;" required>
                    <i class="fas fa-user" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div style="position: relative;">
                    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" style="padding-left: 45px;" required>
                    <i class="fas fa-envelope" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" style="padding-left: 45px;" required>
                    <i class="fas fa-lock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm your password" style="padding-left: 45px;" required>
                    <i class="fas fa-check-double" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Sign Up <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
            </button>
        </form>
        
        <div class="mt-4" style="text-align: center; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <p style="margin-bottom: 0.5rem;">Already have an account?</p>
            <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Sign In</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>
