<?php

require_once '../src/Controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        header("Location: /index.php?reg_error=Passwords do not match#registerModal");
        exit();
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        header("Location: /index.php?reg_error=Password must be at least 6 characters#registerModal");
        exit();
    }
    
    $userController = new UserController();
    $user_id = $userController->register($name, $email, $password);
    
    if ($user_id) {
        // Registration successful - AUTO LOGIN
        session_start(); // Start session if not already started
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        
        // Redirect to index page as logged in user
        header("Location: /index.php?success=welcome");
        exit();
    } else {
        // Registration failed
        header("Location: /index.php?reg_error=Email already exists#registerModal");
        exit();
    }
}

// If accessed directly
header("Location: /index.php");
exit();
?>