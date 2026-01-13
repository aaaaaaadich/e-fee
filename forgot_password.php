<?php
date_default_timezone_set('Asia/Kathmandu');
session_start();
require 'config.php';
require 'mail_helper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!preg_match('/@kusom\.edu\.np$/i', $email)) {
        $error = "Only emails ending in @kusom.edu.np are allowed.";
    } else {
        $conn = getDB();
        
        // Check if user exists and is not admin
        $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($user && $user['role'] === 'student') {
            // Generate secure reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save token to database
            $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $updateStmt->bind_param('ssi', $token, $expiry, $user['id']);
            
            if ($updateStmt->execute()) {
                // Send password reset email
                $reset_link = "http://localhost/feenix/reset_password.php?token=" . urlencode($token);
                $subject = "Password Reset Request - Feenix System";
                $message = "
                <html>
                <head>
                    <title>Password Reset Request</title>
                </head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                        <h2 style='color: #800000;'>Password Reset Request</h2>
                        <p>Hello " . htmlspecialchars($user['name']) . ",</p>
                        <p>We received a request to reset your password for your Feenix System account.</p>
                        <p>Click the button below to reset your password:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$reset_link}' style='background: linear-gradient(135deg, #7f0f0f 0%, #8b2446 50%, #5d2a6e 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                        </div>
                        <p>Or copy and paste this link into your browser:</p>
                        <p style='word-break: break-all; color: #666;'>{$reset_link}</p>
                        <p><strong>This link will expire in 1 hour.</strong></p>
                        <p>If you did not request a password reset, please ignore this email and your password will remain unchanged.</p>
                        <hr style='border: 1px solid #ddd; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #666;'>This is an automated message from Feenix System. Please do not reply to this email.</p>
                    </div>
                </body>
                </html>
                ";
                
                send_notification_email($user['email'], $subject, $message);
                $success = "Password reset link has been sent to your email address. Please check your inbox.";
            } else {
                $error = "Failed to process your request. Please try again.";
            }
            
            $updateStmt->close();
        } else {
            // For security, show same success message even if user doesn't exist or is admin
            $success = "If an account exists with this email, a password reset link has been sent.";
        }
        
        $conn->close();
    }
}

require 'header.php';
?>

<div style="background: linear-gradient(135deg, var(--bg-color) 0%, #e9ecef 100%); min-height: 85vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="card animate-slide-up" style="width: 100%; max-width: 500px; border-top: 5px solid var(--primary-color);">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; background: var(--primary-gradient); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem; box-shadow: var(--shadow-md);">
                <i class="fas fa-key"></i>
            </div>
            <h2>Forgot Password</h2>
            <p>Enter your email to receive a password reset link</p>
        </div>
        
        <?php if ($error): ?>
            <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="flash success"><?php echo htmlspecialchars($success); ?></div>
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="login.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Back to Login</a>
            </div>
        <?php else: ?>
            <form method="POST" action="forgot_password.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="your.email@kusom.edu.np" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <small style="color: #666;">Enter your KUSOM email address</small>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="login.php" style="color: var(--primary-color); text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>
