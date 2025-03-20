<?php
require_once '../connection.php';
require_once '../src/Controllers/UserController.php';
// Use Composer's autoloader for PHPMailer
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create the password_resets table if it doesn't exist
$checkTableQuery = "SHOW TABLES LIKE 'password_resets'";
$result = $conn->query($checkTableQuery);

if ($result->num_rows == 0) {
    // Table doesn't exist, create it
    $createTableQuery = "CREATE TABLE IF NOT EXISTS `password_resets` (
        `email` varchar(255) NOT NULL,
        `token` varchar(255) NOT NULL,
        `expiry` datetime NOT NULL,
        PRIMARY KEY (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($createTableQuery)) {
        // Log the error
        error_log("Failed to create password_resets table: " . $conn->error);
        header("Location: ../forgot_password.php?status=error");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../forgot_password.php?status=sent"); // Show same message for security
        exit();
    }
    
    // Generate a unique token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
    
    // Check if the email exists in the database
    $sql = "SELECT user_id, name FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Store the token in the database
        $sql = "INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE token = VALUES(token), expiry = VALUES(expiry)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $token, $expiry);
        
        if ($stmt->execute()) {
            // Send email with the reset link
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Change to your SMTP server
                $mail->SMTPAuth   = true;
                $mail->Username   = 'tiknumberone.1@gmail.com'; // Change to your email
                $mail->Password   = 'ifhg jbqx fofs jjay'; // Change to your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // Recipients
                $mail->setFrom('noreply@bupc-clinic.com', 'BUPC Clinic');
                $mail->addAddress($email, $user['name']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset - BUPC Clinic';
                
                $resetLink = 'http://localhost:8080/reset_password.php?token=' . $token . '&email=' . urlencode($email);
                
                $mail->Body = '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #ff8000; color: white; padding: 10px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; }
                        .button { display: inline-block; padding: 10px 20px; background-color: #ff8000; color: white; text-decoration: none; border-radius: 5px; }
                        .footer { margin-top: 20px; font-size: 12px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h1>Password Reset Request</h1>
                        </div>
                        <div class="content">
                            <p>Hello ' . $user['name'] . ',</p>
                            <p>We received a request to reset your password for your BUPC Clinic account. To reset your password, click the button below:</p>
                            <p style="text-align: center;">
                                <a href="' . $resetLink . '" class="button">Reset Your Password</a>
                            </p>
                            <p>If you didn\'t request a password reset, you can ignore this email. The link will expire in 1 hour.</p>
                            <p>If the button doesn\'t work, you can also copy and paste the following link into your browser:</p>
                            <p>' . $resetLink . '</p>
                        </div>
                        <div class="footer">
                            <p>This is an automated email, please do not reply.</p>
                            <p>&copy; ' . date('Y') . ' BUPC Clinic. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>';
                
                $mail->AltBody = 'Hello ' . $user['name'] . ', please use the following link to reset your password: ' . $resetLink;
                
                $mail->send();
                
                // For security reasons, always show the same message whether or not the email exists
                header("Location: ../forgot_password.php?status=sent");
                exit();
                
            } catch (Exception $e) {
                // Log the error but don't reveal details to the user
                error_log("PHPMailer Error: " . $mail->ErrorInfo);
                header("Location: ../forgot_password.php?status=error");
                exit();
            }
        } else {
            header("Location: ../forgot_password.php?status=error");
            exit();
        }
    } else {
        // If email doesn't exist, still show the same message for security
        header("Location: ../forgot_password.php?status=sent");
        exit();
    }
}

// If accessed directly without POST request
header("Location: ../index.php");
exit();
