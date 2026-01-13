<?php
require_once __DIR__ . '/config.php';
require_role('admin');

$conn = getDB();

// Filter Logic
$where_clauses = ["1=1"];
if (!empty($_GET['q'])) {
    $q = $conn->real_escape_string($_GET['q']);
    $where_clauses[] = "(f.tracking_id LIKE '%$q%')";
}
if (!empty($_GET['year'])) {
    $y = $conn->real_escape_string($_GET['year']);
    $where_clauses[] = "f.student_year = '$y'";
}
if (!empty($_GET['semester'])) {
    $s = $conn->real_escape_string($_GET['semester']);
    $where_clauses[] = "f.semester = '$s'";
}

$where_sql = implode(' AND ', $where_clauses);

$sql = "SELECT f.id, f.user_id, f.orig_filename, f.file_path, f.status, f.remarks, f.uploaded_at, f.tracking_id, f.student_year, f.semester, u.name, u.email
    FROM fee_uploads f
        JOIN users u ON f.user_id = u.id
    WHERE $where_sql
    ORDER BY f.uploaded_at DESC";
$res = $conn->query($sql);

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
                    <a href="admin.php" style="display: flex; align-items: center; padding: 12px 20px; border-radius: var(--radius-md); background-color: rgba(128, 0, 0, 0.05); color: var(--primary-color); font-weight: 600;">
                        <i class="fas fa-tachometer-alt" style="width: 25px;"></i> Dashboard
                    </a>
                </li>
                <li class="mb-1">
                    <a href="admin_settings.php" style="display: flex; align-items: center; padding: 12px 20px; border-radius: var(--radius-md); color: var(--text-secondary); transition: all 0.2s;">
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
            <div class="flex-between mb-4 flex-wrap gap-2">
                <div>
                    <h2 style="margin-bottom: 0.5rem;">Admin Panel</h2>
                    <p style="color: var(--text-secondary);"><?php echo date('l, F j, Y'); ?></p>
                </div>
                <form method="GET" action="admin.php" style="display:flex; gap:10px; flex-wrap: wrap; align-items: center;">
                    <select name="year" class="form-control" style="width: 150px;" onchange="this.form.submit()">
                        <option value="">All Years</option>
                        <option value="First Year" <?php if(($_GET['year']??'')=='First Year') echo 'selected';?>>First Year</option>
                        <option value="Second Year" <?php if(($_GET['year']??'')=='Second Year') echo 'selected';?>>Second Year</option>
                        <option value="Third Year" <?php if(($_GET['year']??'')=='Third Year') echo 'selected';?>>Third Year</option>
                        <option value="Fourth Year" <?php if(($_GET['year']??'')=='Fourth Year') echo 'selected';?>>Fourth Year</option>
                    </select>

                    <div style="position: relative;">
                        <input type="text" name="q" class="form-control" placeholder="Search tracking ID..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" style="padding-left: 40px; width: 250px;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                    </div>
                    <button class="btn btn-primary">Search</button>
                    <?php if(!empty($_GET['q'])): ?>
                        <a href="admin.php" class="btn btn-outline">Clear</a>
                    <?php endif; ?>
                </form>
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
                        <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Total Submissions</p>
                        <h3 style="font-size: 2rem; color: var(--text-primary); margin: 0;"><?php echo $res->num_rows; ?></h3>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(128, 0, 0, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.5rem;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <!-- Add more stats if needed -->
            </div>

            <div class="card animate-slide-up">
                <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="margin: 0;">Recent Uploads</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Tracking ID</th>
                                <th>Student Details</th>
                                <th>Year/Sem</th>
                                <th>File Info</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
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
                                        <div style="width: 32px; height: 32px; background: var(--bg-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-size: 0.8rem;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($row['name']); ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-light);"><?php echo htmlspecialchars($row['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($row['student_year'] ?? '-'); ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-light);"><?php echo htmlspecialchars($row['semester'] ?? '-'); ?></div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-file-pdf" style="color: var(--error-color);"></i>
                                        <?php echo htmlspecialchars($row['orig_filename']); ?>
                                    </div>
                                </td>
                                <td style="color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?></td>
                                <td><span style="<?php echo $statusStyle; ?>"><?php echo $statusIcon . htmlspecialchars($row['status']); ?></span></td>
                                <td>
                                    <div class="flex" style="gap: 8px;">
                                        <a href="admin_action.php?action=download&id=<?php echo $row['id']; ?>" class="btn btn-icon btn-download" title="Download"><i class="fas fa-download"></i></a>
                                        <?php if ($row['status'] === 'Pending'): ?>
                                            <a href="admin_action.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-icon btn-approve" title="Approve"><i class="fas fa-check"></i></a>
                                            <!-- Verified is disabled as per "Only act on Pending" rule, or we can allow Pending -> Verify -> Approve
                                                But the prompt says "Allow admin actions only when the receipt status is PENDING".
                                                So once Verified, it is stuck? Assuming Verified is not used or is final for some reason.
                                                However, standard flow usually allows Verify. I will allow Verify ONLY if Pending.
                                            -->
                                            <a href="admin_action.php?action=verify&id=<?php echo $row['id']; ?>" class="btn btn-icon btn-verify" title="Verify"><i class="fas fa-search"></i></a>
                                            <a href="admin_reject.php?id=<?php echo $row['id']; ?>" class="btn btn-icon btn-reject" title="Reject"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; 
                        else: ?>
                            <tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--text-light);">
                                <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                <p>No submissions found.</p>
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
