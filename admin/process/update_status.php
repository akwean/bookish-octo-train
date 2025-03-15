<?php
session_start();
require_once '../../config.php';
require_once '../../connection.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $appointment_id = $_GET['id'];
    $status = $_GET['status'];
    
    // Validate status
    $valid_statuses = ['pending', 'approved', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status";
        header("Location: ../dashboard.php");
        exit();
    }
    
    // Update appointment status
    $sql = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $appointment_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment status updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update appointment status";
    }
    
    // Build redirect URL with preserved date filter but always set status to "all"
    $redirect_url = "../dashboard.php";
    
    // Preserve date filter if present
    if(isset($_GET['date'])) {
        $redirect_url .= "?date=" . $_GET['date'];
        // Always set status to "all" after updating
        $redirect_url .= "&status=all";
    } else {
        // If no date was specified, still use "all" for status
        $redirect_url .= "?status=all";
    }
    
    header("Location: $redirect_url");
    exit();
} else {
    header("Location: ../dashboard.php");
    exit();
}
?>