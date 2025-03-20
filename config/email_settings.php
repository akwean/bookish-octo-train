<?php
// Email configuration for the application

// SMTP settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'tiknumberone.1@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'ifhg jbqx fofs jjay'); // Your app password
define('SMTP_SECURE', 'tls');
define('SMTP_PORT', 587);
define('SMTP_FROM_EMAIL', 'noreply@bupc-clinic.com');
define('SMTP_FROM_NAME', 'BUPC Clinic System');

// Staff notification recipients
$GLOBALS['staff_notification_emails'] = [
    'craigpark292@gmail.com' => 'Clinic Nurse', // Fixed the comma to a period
    // Add more staff emails as needed
];
?>
