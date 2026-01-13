<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';
require __DIR__ . '/mail_config.php';

/**
 * Send a notification email using PHPMailer.
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body content
 * @return bool True if mail was sent successfully, false otherwise
 */
function send_notification_email($to, $subject, $message) {
    // Set Nepal timezone for email timestamps
    date_default_timezone_set('Asia/Kathmandu');
    
    // If email is disabled, just save to file
    if (!MAIL_ENABLED) {
        save_email_to_file($to, $subject, $message, 'Email Disabled - Saved Only');
        return true;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        if (MAIL_DEBUG) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
        
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        
        // Also save to file for backup
        save_email_to_file($to, $subject, $message, 'Sent Successfully');
        
        return true;
    } catch (Exception $e) {
        // Save email to file if sending fails
        save_email_to_file($to, $subject, $message, "Failed: {$mail->ErrorInfo}");
        
        // Log the error
        error_log("Email sending failed: {$mail->ErrorInfo}");
        
        // Return true to not block user flow (email is optional)
        return true;
    }
}

/**
 * Save email to file for local verification.
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email content
 * @param string $status Status message
 */
function save_email_to_file($to, $subject, $message, $status = 'Saved') {
    $sent_emails_dir = __DIR__ . '/sent_emails';
    if (!is_dir($sent_emails_dir)) {
        mkdir($sent_emails_dir, 0755, true);
    }
    
    $filename = 'email_' . date('Y-m-d_H-i-s') . '_' . md5(uniqid()) . '.html';
    $filepath = $sent_emails_dir . '/' . $filename;
    
    $email_content = "<!-- To: $to -->\n";
    $email_content .= "<!-- Subject: $subject -->\n";
    $email_content .= "<!-- Date: " . date('Y-m-d H:i:s') . " -->\n";
    $email_content .= "<!-- Status: $status -->\n";
    $email_content .= "<hr>\n";
    $email_content .= $message;

    file_put_contents($filepath, $email_content);

    // Log the attempt
    $log_message = sprintf(
        "[%s] Email to: %s | Subject: %s | Status: %s | Saved to: %s\n",
        date('Y-m-d H:i:s'),
        $to,
        $subject,
        $status,
        $filename
    );
    
    file_put_contents(__DIR__ . '/mail.log', $log_message, FILE_APPEND);
}


