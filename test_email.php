<?php
require_once __DIR__ . '/mail_helper.php';

echo "<h2>Email Test</h2>";
echo "<p>Testing email configuration...</p>";

$test_email = 'info.aayojana@gmail.com'; // Send test to your own email
$subject = 'Test Email from Feenix System';
$message = '<h2>Test Email</h2><p>If you receive this, your email configuration is working correctly!</p>';

echo "<p>Attempting to send test email to: <strong>$test_email</strong></p>";
echo "<hr>";

$result = send_notification_email($test_email, $subject, $message);

echo "<hr>";
if ($result) {
    echo "<p style='color: green;'><strong>Email function returned success!</strong></p>";
    echo "<p>Check your inbox and the mail.log file for details.</p>";
} else {
    echo "<p style='color: red;'><strong>Email function returned failure.</strong></p>";
    echo "<p>Check the mail.log file for error details.</p>";
}

echo "<h3>Troubleshooting Steps:</h3>";
echo "<ol>";
echo "<li><strong>App Password:</strong> Make sure you're using a Gmail App Password (not your regular password)</li>";
echo "<li><strong>2-Factor Auth:</strong> Ensure 2-Factor Authentication is enabled on your Gmail account</li>";
echo "<li><strong>Generate New App Password:</strong> Visit <a href='https://myaccount.google.com/apppasswords' target='_blank'>https://myaccount.google.com/apppasswords</a></li>";
echo "<li><strong>Check Spaces:</strong> Make sure there are no extra spaces in the app password (current: 'bjng qptk ctpb rjmg')</li>";
echo "<li><strong>Less Secure Apps:</strong> Some Gmail accounts need 'Allow less secure apps' enabled (though App Passwords should work)</li>";
echo "</ol>";

echo "<h3>Current Configuration:</h3>";
echo "<pre>";
echo "Email: " . MAIL_USERNAME . "\n";
echo "SMTP Host: " . MAIL_HOST . "\n";
echo "SMTP Port: " . MAIL_PORT . "\n";
echo "Encryption: " . MAIL_ENCRYPTION . "\n";
echo "Password: " . substr(MAIL_PASSWORD, 0, 4) . " **** **** " . substr(MAIL_PASSWORD, -4) . "\n";
echo "Mail Enabled: " . (MAIL_ENABLED ? 'Yes' : 'No') . "\n";
echo "Debug Mode: " . (MAIL_DEBUG ? 'Yes' : 'No') . "\n";
echo "</pre>";
?>
