<?php
require_once '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        header("Location: ../reset_password.php?token=$token&email=$email&error=mismatch");
        exit();
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        header("Location: ../reset_password.php?token=$token&email=$email&error=length");
        exit();
    }
    
    // Verify token is valid and not expired
    $sql = "SELECT * FROM password_resets WHERE email = ? AND token = ? AND expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update the user's password
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            // Delete the used token
            $sql = "DELETE FROM password_resets WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            // Redirect to login with success message
            header("Location: ../index.php?success=password_reset");
            exit();
        } else {
            header("Location: ../reset_password.php?token=$token&email=$email&error=database");
            exit();
        }
    } else {
        // Invalid or expired token
        header("Location: ../reset_password.php?token=$token&email=$email&error=invalid");
        exit();
    }
}

// If accessed directly
header("Location: ../index.php");
exit();
