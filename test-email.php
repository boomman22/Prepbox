<?php
require 'includes/config.php';
require 'includes/functions.php';

$to = 'recipient@example.com';
$subject = 'PHPMailer Test';
$body = '<h1>Test Email</h1><p>This is a test email from PHPMailer.</p>';

if (sendEmail($to, $subject, $body)) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email. Check error logs.";
}
?>