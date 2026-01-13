<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBIS Fee Upload</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container flex-between">
            <?php $__logo_path = __DIR__ . '/uploads/ku-logo.png'; ?>
            <a href="index.php" class="logo">
                <?php if (file_exists($__logo_path)): ?>
                    <img src="uploads/ku-logo.png" alt="KU Logo" style="height:45px; width:auto;">
                <?php else: ?>
                    <div style="width:40px; height:40px; background:var(--primary-gradient); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white;">
                        <i class="fas fa-university"></i>
                    </div>
                <?php endif; ?>
                <div class="brand-text">
                    <span class="brand-main">Feenix</span>
                
                </div>
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <?php if(!empty($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt" style="margin-right:8px;"></i> Dashboard</a></li>
                        <li><a href="logout.php" class="btn btn-outline">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn btn-primary">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
