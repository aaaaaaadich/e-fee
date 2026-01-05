<?php
require_once __DIR__ . '/config.php';
require_role('student');

$error = '';
$success = '';

$conn = getDB();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    if (empty($name) || empty($email)) {
        $error = 'Please fill in all fields.';
    } else {
        // check email uniqueness
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Email already in use.';
            $stmt->close();
        } else {
            $stmt->close();
            $up = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $up->bind_param('ssi', $name, $email, $_SESSION['user_id']);
            if ($up->execute()) {
                $_SESSION['name'] = $name;
                $success = 'Profile updated.';
            } else {
                $error = 'Update failed.';
            }
            $up->close();
        }
    }
}

$stmt = $conn->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();
$conn->close();

require 'header.php';
?>
<div class="container" style="padding:40px 0;">
    <div class="card" style="max-width:600px;margin:0 auto;">
        <h3>Edit Profile</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="post" action="profile.php">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Save</button>
        </form>
    </div>
</div>
<?php require 'footer.php'; ?>
