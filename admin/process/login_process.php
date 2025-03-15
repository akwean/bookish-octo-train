<?php
session_start();
require_once '../../config.php';
require_once '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../index.php");
        exit();
    }
    
    // Prepare SQL statement
    $sql = "SELECT * FROM staff WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $staff = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $staff['password'])) {
            // Set session variables
            $_SESSION['staff_id'] = $staff['staff_id'];
            $_SESSION['staff_name'] = $staff['name'];
            $_SESSION['staff_role'] = $staff['role'];
            
            // Redirect to dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: ../index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>