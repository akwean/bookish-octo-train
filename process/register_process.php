<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/UserController.php';

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
        // Registration successful
        header("Location: /index.php?success=registered#loginModal");
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