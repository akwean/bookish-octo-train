<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/UserController.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $userController = new UserController();
    $user_id = $userController->login($email, $password);
    
    if ($user_id) {
        // Login successful
        $_SESSION['user_id'] = $user_id;
        header("Location: /index.php");
        exit();
    } else {
        // Login failed
        header("Location: /index.php?error=invalid#loginModal");
        exit();
    }
}

// If accessed directly
header("Location: /index.php");
exit();
?>