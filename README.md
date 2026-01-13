# BBIS Fee Receipt Upload System

Simple PHP/MySQL application for Kathmandu University School of Management (KUSOM) to upload and approve student fee receipts.

Requirements
- XAMPP (PHP 8.0+/MySQL)
- PHPMailer (included in PHPMailer-master/ folder)
- Gmail account for sending email notifications

Quick setup
1. Start Apache and MySQL in XAMPP Control Panel.
2. Put files into `C:\xampp\htdocs\feenix`.
3. Open in browser: `http://localhost/feenix/install.php` to create database, tables, and an admin user (`admin@kusom.edu.np`, password `admin123`).
4. Configure email in `mail_config.php` with your Gmail credentials (use App Password).
5. Run migration: `http://localhost/feenix/migrate_password_reset.php` to enable password reset.
6. Remove or secure `install.php` after running.
7. Login: `http://localhost/feenix/login.php`.

Default pages
- `install.php` — creates DB, tables, uploads folder and default admin
- `index.php` — landing page
- `signup.php` — student registration (@kusom.edu.np only)
- `login.php` — login for students and admin
- `forgot_password.php` — request password reset link
- `reset_password.php` — reset password with token
- `dashboard.php` — student dashboard and uploads
- `profile.php` — student profile and password change
- `upload.php` — upload handler
- `admin.php` — admin panel to verify/approve/reject and download files
- `admin_action.php` — approve/verify/download actions
- `admin_reject.php` — rejection handler with remarks
- `admin_settings.php` — change admin password
- `logout.php` — logout

Migration scripts (run if updating from older version)
- `migrate_password_reset.php` — adds password reset columns
- `migrate_status_enum.php` — adds 'Verified' status
- `migrate_add_columns_v2.php` — adds tracking_id and file_hash
- `migrate_add_year_semester.php` — adds year and semester fields
- `migrate_immutability_trigger.php` — prevents modification of approved records

Database (created by installer)

users table:

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  reset_token VARCHAR(64) DEFAULT NULL,
  reset_token_expiry DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

fee_uploads table:

CREATE TABLE fee_uploads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  orig_filename VARCHAR(255) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  tracking_id VARCHAR(64) NOT NULL UNIQUE,
  file_hash VARCHAR(128) NOT NULL,
  student_year VARCHAR(50),
  semester VARCHAR(50),
  status ENUM('Pending','Verified','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  remarks VARCHAR(255) DEFAULT NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_file_hash (file_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

Notes
- Email notifications use PHPMailer; configure SMTP in `mail_config.php` with Gmail App Password.
- File uploads are stored in the `uploads/` folder — ensure it's writable by the web server.
- Email logs saved to `sent_emails/` folder.
- PHP errors logged to `php-error.log`.
- Change DB credentials in `config.php` if your environment differs.
- Multi-status workflow: Pending → Verified → Approved/Rejected.
- Each upload gets a unique tracking ID and duplicate detection via file hashing.
- Session timeout: 30 minutes.
- Change default admin password immediately after first login.
