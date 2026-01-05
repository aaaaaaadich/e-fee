<?php
require 'mail_helper.php';

echo "Testing email sending...\n";
$result = send_notification_email('test@example.com', 'Test Subject', 'Test Message');

if ($result) {
    echo "Mail function returned true (accepted for delivery).\n";
} else {
    echo "Mail function returned false (failed).\n";
}

echo "Check mail.log for details.\n";
?>
