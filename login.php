<?php
session_start();
require 'config.php';
require 'mail_helper.php';

$error = '';
$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!preg_match('/@kusom\.edu\.np$/i', $email)) {
        $error = "Only emails ending in @kusom.edu.np are allowed.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)) {
        $error = "Password must be at least 8 characters long and contain at least one uppercase letter.";
    } else {
        $conn = getDB();
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        $conn->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['last_activity'] = time();

            // Send login notification email (non-blocking)
            date_default_timezone_set('Asia/Kathmandu');
            $subject = "Login Notification - Feenix System";
            $login_time = date('Y-m-d H:i:s');
            $message = "
            <html>
            <head>
                <title>Login Notification</title>
            </head>
            <body>
                <h2>Hello, " . htmlspecialchars($user['name']) . "</h2>
                <p>This is to notify you that your account was accessed on <strong>$login_time</strong>.</p>
                <p>If this was not you, please contact support immediately.</p>
            </body>
            </html>
            ";
            send_notification_email($email, $subject, $message);

            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require 'header.php';
?>

<div style="background: linear-gradient(135deg, var(--bg-color) 0%, #e9ecef 100%); min-height: 85vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
    <div class="card animate-slide-up" style="width: 100%; max-width: 450px; border-top: 5px solid var(--primary-color);">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; background: var(--primary-gradient); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem; box-shadow: var(--shadow-md);">
                <i class="fas fa-user"></i>
            </div>
            <h2>Welcome Back</h2>
            <p>Sign in to continue to your dashboard</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div style="position: relative;">
                    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" style="padding-left: 45px;" value="<?php echo htmlspecialchars($email); ?>" required>
                    <i class="fas fa-envelope" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" style="padding-left: 45px; padding-right: 100px;" value="<?php echo htmlspecialchars($password); ?>" required>
                    <i class="fas fa-lock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                    
                    <button type="button" onclick="togglePasswordVisibility()" class="btn btn-sm btn-outline-secondary" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: var(--primary-color); cursor: pointer; font-size: 0.8rem; font-weight: 600;">
                        <span id="toggleText">Show</span>
                    </button>
                </div>
                <div style="text-align: right; margin-top: 0.5rem;">
                    <a href="forgot_password.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>
            </div>
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Sign In <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
            </button>
        </form>
        
        <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleText = document.getElementById('toggleText');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleText.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleText.textContent = 'Show';
            }
        }
        </script>
        
        <div class="mt-4" style="text-align: center; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <p style="margin-bottom: 0.5rem;">Don't have an account?</p>
            <a href="signup.php" style="color: var(--primary-color); font-weight: 600;">Create an Account</a>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
        </form>
        
        <div class="mt-4" style="text-align: center; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <p style="margin-bottom: 0.5rem;">Don't have an account?</p>
            <a href="signup.php" style="color: var(--primary-color); font-weight: 600;">Create an Account</a>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
