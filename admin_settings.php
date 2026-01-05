<?php
require_once __DIR__ . '/config.php';
require_role('admin');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
    if ($new !== $confirm) $errors[] = 'New passwords do not match.';

    if (empty($errors)) {
        $conn = getDB();
        $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($hash);
        if ($stmt->fetch()) {
            if (!password_verify($current, $hash)) {
                $errors[] = 'Current password is incorrect.';
            } else {
                $stmt->close();
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $upd = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                $upd->bind_param('si', $newHash, $_SESSION['user_id']);
                if ($upd->execute()) {
                    $_SESSION['flash'] = 'Password updated successfully.';
                    $upd->close();
                    $conn->close();
                    header('Location: admin.php');
                    exit;
                } else {
                    $errors[] = 'Failed to update password.';
                }
            }
        } else {
            $errors[] = 'User not found.';
        }
        $stmt->close();
        $conn->close();
    }
}

require 'header.php';
?>

<div class="container mt-4 mb-4">
    <div class="flex flex-wrap" style="gap: 30px; align-items: flex-start;">
        <!-- Sidebar -->
        <aside class="card" style="width: 100%; max-width: 280px; padding: 0; overflow: hidden; position: sticky; top: 100px;">
            <div style="background: var(--primary-gradient); padding: 30px 20px; text-align: center; color: white;">
                <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 15px; border: 2px solid rgba(255,255,255,0.3);">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 style="font-size: 1.2rem; margin-bottom: 5px; color: white;"><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
                <span style="font-size: 0.9rem; opacity: 0.8; background: rgba(0,0,0,0.2); padding: 4px 12px; border-radius: 20px;">Administrator</span>
            </div>
            <ul style="padding: 15px;">
                <li class="mb-1">
                    <a href="admin.php" style="display: flex; align-items: center; padding: 12px 20px; border-radius: var(--radius-md); color: var(--text-secondary); transition: all 0.2s;">
                        <i class="fas fa-tachometer-alt" style="width: 25px;"></i> Dashboard
                    </a>
                </li>
                <li class="mb-1">
                    <a href="admin_settings.php" style="display: flex; align-items: center; padding: 12px 20px; border-radius: var(--radius-md); background-color: rgba(128, 0, 0, 0.05); color: var(--primary-color); font-weight: 600;">
                        <i class="fas fa-cog" style="width: 25px;"></i> Settings
                    </a>
                </li>
                <li class="mb-1">
                    <a href="logout.php" style="display: flex; align-items: center; padding: 12px 20px; border-radius: var(--radius-md); color: var(--error-color); transition: all 0.2s;">
                        <i class="fas fa-sign-out-alt" style="width: 25px;"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div style="flex: 1; min-width: 0;">
            <div class="card animate-slide-up" style="max-width: 600px; border-top: 5px solid var(--primary-color);">
                <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">
                    <h2 style="margin: 0;">Change Password</h2>
                    <p style="margin-top: 5px; font-size: 0.9rem;">Ensure your account stays secure by updating your password periodically.</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <div style="position: relative;">
                            <input type="password" name="current" class="form-control" style="padding-left: 45px;" required>
                            <i class="fas fa-lock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div style="position: relative;">
                            <input type="password" name="new" class="form-control" style="padding-left: 45px;" required>
                            <i class="fas fa-key" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div style="position: relative;">
                            <input type="password" name="confirm" class="form-control" style="padding-left: 45px;" required>
                            <i class="fas fa-check-double" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
