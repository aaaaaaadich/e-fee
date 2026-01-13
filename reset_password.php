<?php
date_default_timezone_set('Asia/Kathmandu');
session_start();
require 'config.php';
require 'mail_helper.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_data = null;

// Validate token
if (!empty($token)) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT id, name, email, reset_token_expiry, role FROM users WHERE reset_token = ? AND role = 'student'");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    $conn->close();

    if ($user_data) {
        // Check if token has expired
        $expiry_time = strtotime($user_data['reset_token_expiry']);
        $current_time = time();
        
        if ($current_time <= $expiry_time) {
            $valid_token = true;
        } else {
            $error = "This password reset link has expired. Please request a new one.";
        }
    } else {
        $error = "Invalid password reset link.";
    }
} else {
    $error = "No reset token provided.";
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)) {
        $error = "Password must be at least 8 characters long and contain at least one uppercase letter.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $conn = getDB();
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $updateStmt->bind_param('si', $hashed_password, $user_data['id']);
        
        if ($updateStmt->execute()) {
            $success = "Your password has been reset successfully. You can now login with your new password.";
            $valid_token = false; // Prevent form from showing again
            
            // Send confirmation email
            $subject = "Password Changed - Feenix System";
            $message = "
            <html>
            <head>
                <title>Password Changed</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                    <h2 style='color: #800000;'>Password Successfully Changed</h2>
                    <p>Hello " . htmlspecialchars($user_data['name']) . ",</p>
                    <p>Your password for the Feenix System has been successfully changed.</p>
                    <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                    <p>If you did not make this change, please contact support immediately.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/feenix/login.php' style='background: linear-gradient(135deg, #7f0f0f 0%, #8b2446 50%, #5d2a6e 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Login Now</a>
                    </div>
                    <hr style='border: 1px solid #ddd; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #666;'>This is an automated message from Feenix System. Please do not reply to this email.</p>
                </div>
            </body>
            </html>
            ";
            
            send_notification_email($user_data['email'], $subject, $message);
        } else {
            $error = "Failed to reset password. Please try again.";
        }
        
        $updateStmt->close();
        $conn->close();
    }
}

require 'header.php';
?>

<div style="background: linear-gradient(135deg, var(--bg-color) 0%, #e9ecef 100%); min-height: 85vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="card animate-slide-up" style="width: 100%; max-width: 500px; border-top: 5px solid var(--primary-color);">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; background: var(--primary-gradient); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem; box-shadow: var(--shadow-md);">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Reset Password</h2>
            <p>Enter your new password</p>
        </div>
        
        <?php if ($error): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="forgot_password.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Request New Link</a>
            </div>
        <?php elseif ($success): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="login.php" class="btn-primary" style="text-decoration: none; display: inline-block;">
                    <i class="fas fa-sign-in-alt"></i> Login Now
                </a>
            </div>
        <?php elseif ($valid_token): ?>
            <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password" required>
                    <small style="color: #666;">At least 8 characters with one uppercase letter</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="login.php" style="color: var(--primary-color); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
