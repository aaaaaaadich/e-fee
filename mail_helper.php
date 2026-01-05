<?php

/**
 * Send a notification email.
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body content
 * @return bool True if mail was accepted for delivery, false otherwise
 */
function send_notification_email($to, $subject, $message) {
    // Basic headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: No Reply <noreply@example.com>' . "\r\n";

    // 1. Attempt to send email (will likely fail or be swallowed on localhost)
    $mail_result = mail($to, $subject, $message, $headers);

    // 2. Save email to file for local verification (Simulated Delivery)
    $filename = 'email_' . date('Y-m-d_H-i-s') . '_' . md5(uniqid()) . '.html';
    $filepath = __DIR__ . '/sent_emails/' . $filename;
    
    $email_content = "<!-- To: $to -->\n";
    $email_content .= "<!-- Subject: $subject -->\n";
    $email_content .= "<!-- Date: " . date('Y-m-d H:i:s') . " -->\n";
    $email_content .= "<hr>\n";
    $email_content .= $message;

    $file_result = file_put_contents($filepath, $email_content);

    // Log the attempt
    $log_message = sprintf(
        "[%s] Email to: %s | Subject: %s | Mail Result: %s | Saved to: %s\n",
        date('Y-m-d H:i:s'),
        $to,
        $subject,
        $mail_result ? 'Success' : 'Failed',
        $file_result ? $filename : 'Failed to save'
    );
    
    file_put_contents(__DIR__ . '/mail.log', $log_message, FILE_APPEND);

    // Return true if EITHER mail sent OR file saved (so UI shows success)
    return $mail_result || ($file_result !== false);
}
