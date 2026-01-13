# BBIS Fee Receipt Upload System

Simple PHP/MySQL application for Kathmandu University School of Management (KUSOM) to upload and approve student fee receipts.

Requirements
- XAMPP (PHP 7.4+/MySQL)
- Place project in `C:\xampp\htdocs\feenix` and open in browser.

Quick setup
1. Put files into `C:\xampp\htdocs\feenix`.
2. Open in browser: `http://localhost/feenix/install.php` to create database, tables, and an admin user (`admin@kusom.edu.np`, password `admin123`).
3. Remove or secure `install.php` after running.
4. Login: `http://localhost/feenix/login.php`.

Default pages
- `install.php` — creates DB, tables, uploads folder and default admin
- `signup.php` — student registration
- `login.php` — login for students and admin
- `dashboard.php` — student dashboard and uploads
- `upload.php` — upload handler
- `admin.php` — admin panel to approve/reject and download files
- `admin_action.php` — approve/reject/download actions (sends email notification on status change)
- `admin_settings.php` — change admin password
- `logout.php` — logout

Database (created by installer)

users table:

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

fee_uploads table:

CREATE TABLE fee_uploads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  orig_filename VARCHAR(255) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  remarks VARCHAR(255) DEFAULT NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

Notes
- Email notifications use PHP `mail()`; configure your SMTP/PHPMailer on the server for reliable delivery.
- File uploads are stored in the `uploads/` folder — ensure it's writable by the web server.
- Change DB credentials in `config.php` if your environment differs.
