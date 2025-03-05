<?php

require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if ($password !== $confirm_password) {
        header("Location: index.php?reg_error=Passwords do not match");
        exit();
    }
    
    if (strlen($password) < 6) {
        header("Location: index.php?reg_error=Password must be at least 6 characters");
        exit();
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        header("Location: index.php?reg_error=Email already exists");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
    
    if ($insert_stmt->execute()) {
        header("Location: index.php?success=registered#loginModal");
        exit();
    } else {
        header("Location: index.php?reg_error=Registration failed");
        exit();
    }
}
else {
    // If someone tries to access register.php directly
    header("Location: index.php");
    exit();
}
?>