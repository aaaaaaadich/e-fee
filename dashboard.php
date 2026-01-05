<?php
require_once __DIR__ . '/config.php';
require_role('student');

$conn = getDB();
$stmt = $conn->prepare('SELECT id, orig_filename, filename, file_path, tracking_id, status, remarks, uploaded_at FROM fee_uploads WHERE user_id = ? ORDER BY uploaded_at DESC');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();

require 'header.php';
?>

<div class="container mt-4 mb-4">
    <div class="flex flex-wrap" style="gap: 30px; align-items: flex-start;">
        <!-- Sidebar -->
        <aside class="card" style="width: 100%; max-width: 280px; padding: 0; overflow: hidden; position: sticky; top: 100px;">
            <div style="background: var(--primary-gradient); padding: 30px 20px; text-align: center; color: white;">
                <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 15px; border: 2px solid rgba(255,255,255,0.3);">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </div>
                <h3 style="font-size: 1.2rem; margin-bottom: 5px; color: white;"><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
                <span style="font-size: 0.9rem; opacity: 0.8; background: rgba(0,0,0,0.2); padding: 4px 12px; border-radius: 20px;">Student</span>
            </div>
            <ul style="padding: 15px;">
                <li class="mb-1">
                    <a href="dashboard.php" style="display: flex; align-items: center; padding: 12px 20px; border-radius: var(--radius-md); background-color: rgba(128, 0, 0, 0.05); color: var(--primary-color); font-weight: 600;">
                        <i class="fas fa-tachometer-alt" style="width: 25px;"></i> Dashboard
                <li class="mb-1">
                    <a href="profile.php" style="display: flex; align-items: center; padding: 12px 20px; border-radius: var(--radius-md); color: var(--text-secondary); transition: all 0.2s;">
                        <i class="fas fa-user" style="width: 25px;"></i> Profile
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
            <div class="flex-between mb-4 flex-wrap gap-2">
                <div>
                    <h2 style="margin-bottom: 0.5rem;">Dashboard Overview</h2>
                    <p>Welcome back, manage your fee receipts here.</p>
                </div>
                <button onclick="document.getElementById('upload-form').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary">
                    <i class="fas fa-plus-circle" style="margin-right: 8px;"></i> New Upload
                </button>
            </div>

            <?php if (!empty($_SESSION['flash'])): ?>
                <div class="alert alert-success animate-fade-in">
                    <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                    <span><?php echo htmlspecialchars($_SESSION['flash']); ?></span>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 30px;">
                <div class="card flex-between" style="padding: 20px; border-left: 5px solid var(--primary-color);">
                    <div>
                        <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Total Uploads</p>
                        <h3 style="font-size: 2rem; color: var(--text-primary); margin: 0;"><?php echo $res->num_rows; ?></h3>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(128, 0, 0, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.5rem;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
                <!-- Add more stats if available -->
            </div>

            <div class="card mb-4 animate-slide-up" id="upload-form">
                <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="margin: 0;">Upload Fee Receipt</h3>
                </div>
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Select Receipt File</label>
                        <div style="border: 2px dashed var(--border-color); padding: 30px; border-radius: var(--radius-md); text-align: center; background: var(--bg-color); transition: all 0.3s; cursor: pointer;" onclick="document.querySelector('input[type=file]').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--text-light); margin-bottom: 15px;"></i>
                            <p style="margin-bottom: 10px; font-weight: 500;">Click to upload or drag and drop</p>
                            <p style="font-size: 0.85rem; color: var(--text-light);">PDF, JPG, PNG (Max 5MB)</p>
                            <input type="file" name="receipt" class="form-control" style="display: none;" required onchange="this.parentElement.style.borderColor = 'var(--primary-color)'; this.parentElement.querySelector('p').textContent = this.files[0].name;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload" style="margin-right: 8px;"></i> Upload Receipt
                    </button>
                </form>
            </div>

            <div class="card">
                <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="margin: 0;">Recent Activity</h3>
                </div>
                
                <div class="alert" style="background-color: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb;">
                    <i class="fas fa-info-circle"></i>
                    <span>Tracking IDs are generated automatically. Use them for any queries.</span>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tracking ID</th>
                                <th>File Details</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $res->data_seek(0); // Reset pointer
                        if ($res->num_rows > 0):
                            while ($row = $res->fetch_assoc()): 
                                $statusStyle = '';
                                $statusIcon = '';
                                if ($row['status'] == 'Approved') {
                                    $statusStyle = 'background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-block;';
                                    $statusIcon = '<i class="fas fa-check-circle" style="margin-right:5px;"></i>';
                                } elseif ($row['status'] == 'Rejected') {
                                    $statusStyle = 'background: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-block;';
                                    $statusIcon = '<i class="fas fa-times-circle" style="margin-right:5px;"></i>';
                                } else {
                                    $statusStyle = 'background: #fff3cd; color: #856404; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-block;';
                                    $statusIcon = '<i class="fas fa-clock" style="margin-right:5px;"></i>';
                                }
                        ?>
                            <tr>
                                <td style="font-weight:600; color: var(--primary-color);">#<?php echo htmlspecialchars($row['tracking_id'] ?? '-'); ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; background: var(--bg-color); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <span style="font-weight: 500;"><?php echo htmlspecialchars($row['orig_filename']); ?></span>
                                    </div>
                                </td>
                                <td style="color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?></td>
                                <td><span style="<?php echo $statusStyle; ?>"><?php echo $statusIcon . htmlspecialchars($row['status']); ?></span></td>
                                <td style="color: var(--text-secondary); font-size: 0.9rem;"><?php echo htmlspecialchars($row['remarks'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($row['status'] === 'Approved'): ?>
                                        <a href="admin_action.php?action=download&id=<?php echo $row['id']; ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-light); font-size: 1.2rem;">&bull;</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; 
                        else: ?>
                            <tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--text-light);">
                                <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                <p>No uploads found yet.</p>
                            </td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
