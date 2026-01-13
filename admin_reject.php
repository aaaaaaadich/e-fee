<?php
require_once __DIR__ . '/config.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: admin.php');
    exit;
}

$conn = getDB();
$stmt = $conn->prepare('SELECT status FROM fee_uploads WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($status);
if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: admin.php');
    exit;
}
$stmt->close();

if ($status !== 'Pending') {
    $_SESSION['flash'] = "Cannot reject receipt. Status is already '{$status}'.";
    header('Location: admin.php');
    exit;
}

require 'header.php';
?>
<div class="container" style="padding:40px 0;">
    <div class="card" style="max-width:600px;margin:0 auto;">
        <h3>Reject Submission</h3>
        <form method="post" action="admin_action.php?action=reject&id=<?php echo $id; ?>">
            <div class="form-group">
                <label for="remarks" class="form-label">Remarks (optional)</label>
                <textarea name="remarks" id="remarks" class="form-control" rows="4"></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn btn-primary" style="background-color: var(--error-color);">Reject</button>
                <a href="admin.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require 'footer.php'; ?>