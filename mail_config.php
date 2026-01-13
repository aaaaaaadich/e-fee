<?php
/**
 * Email Configuration
 * 
 * IMPORTANT: Update these settings with your actual email credentials
 * 
 * For Gmail:
 * 1. Enable 2-Factor Authentication in your Google Account
 * 2. Generate an App Password: https://myaccount.google.com/apppasswords
 * 3. Use the App Password (not your regular password) in MAIL_PASSWORD
 * 
 * For other email providers, update MAIL_HOST and MAIL_PORT accordingly
 */

// SMTP Configuration
define('MAIL_HOST', 'smtp.gmail.com');           // SMTP server (e.g., smtp.gmail.com, smtp.office365.com)
define('MAIL_PORT', 587);                        // SMTP port (587 for TLS, 465 for SSL)
define('MAIL_USERNAME', 'sahaaditya936@gmail.com'); // Your email address
define('MAIL_PASSWORD', 'bjngqptkctpbrjmg');     // Your email password or app password
define('MAIL_ENCRYPTION', 'tls');                // 'tls' or 'ssl'

// Sender Information
define('MAIL_FROM_ADDRESS', 'noreply@kusom.edu.np');
define('MAIL_FROM_NAME', 'KUSOM Feenix System');

// Email Features
define('MAIL_ENABLED', true);  // Set to false to disable email sending (will only save to file)
define('MAIL_DEBUG', false);   // Set to true to see detailed SMTP debug output

?>
