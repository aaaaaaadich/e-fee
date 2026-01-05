-- SQL script to create database and tables for BBIS Fee Receipt Upload System
CREATE DATABASE IF NOT EXISTS `bbis_fee` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bbis_fee`;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') NOT NULL DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fee_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    orig_filename VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    tracking_id VARCHAR(64) NOT NULL UNIQUE,
    file_hash VARCHAR(128) NOT NULL,
    status ENUM('Pending','Verified','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    remarks VARCHAR(255) DEFAULT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
