<?php
// This is a test script to verify email functionality
require_once 'connection.php';
require_once 'vendor/autoload.php';
require_once 'config/email_settings.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set to true to see detailed debug output
$debug = true;

try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    if ($debug) {
        // Enable debug output
        $mail->SMTPDebug = 2; // 2 = verbose debug output
        $mail->Debugoutput = 'html';
    }
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;
    
    // Set sender
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    
    // Add recipients (use your own email for testing)
    $mail->addAddress('craigpark292@gmail.com', 'Test User');
    
    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from BUPC Clinic System';
    $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd;">
        <h2 style="color: #ff8000;">Test Email</h2>
        <p>This is a test email to verify that the email notification system is working correctly.</p>
        <p>If you received this email, it means the SMTP settings are configured properly.</p>
        <p>Time sent: ' . date('Y-m-d H:i:s') . '</p>
    </div>';
    $mail->AltBody = 'This is a test email. Time sent: ' . date('Y-m-d H:i:s');
    
    // Send the email
    $mail->send();
    echo '<div style="color: green; padding: 20px; background: #eeffee; border: 1px solid green; margin: 20px;">Email has been sent successfully! Please check your inbox (and spam folder).</div>';
    
} catch (Exception $e) {
    echo '<div style="color: red; padding: 20px; background: #ffeeee; border: 1px solid red; margin: 20px;">Email sending failed: ' . $mail->ErrorInfo . '</div>';
}

// List configured recipients from settings
echo '<div style="padding: 20px; background: #f5f5f5; border: 1px solid #ddd; margin: 20px;">';
echo '<h3>Configured Notification Recipients:</h3>';
echo '<ul>';
foreach ($GLOBALS['staff_notification_emails'] as $email => $name) {
    echo "<li><strong>$name</strong>: $email</li>";
}
echo '</ul></div>';
?>
