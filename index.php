<?php
session_start();
require 'header.php';
?>

<div class="hero-section" style="padding:60px 0; text-align:center;">
    <div class="container">
        <h1 style="font-size:2.5rem; margin-bottom:10px;">BBIS Fee Upload</h1>
        <p style="color:var(--text-light); max-width:700px; margin:0 auto 20px;">Upload fee receipts (PDF/JPG/PNG). Administrators can review, approve or reject submissions.</p>
        <div style="margin-top:20px;">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-primary" style="margin-right:10px;">Go to Dashboard</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary" style="margin-right:10px;">Login</a>
                <a href="signup.php" class="btn btn-outline">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="section" style="padding:40px 0;">
    <div class="container">
        <div class="card" style="max-width:900px;margin:0 auto;">
            <h3 style="margin-bottom:10px;">How it works</h3>
            <ol style="color:var(--text-light);">
                <li>Students upload fee receipts via the dashboard.</li>
                <li>Admins review submissions and set status: Approved / Rejected.</li>
                <li>Approved receipts are available for download by the student.</li>
            </ol>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
