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
    
    // Check if the appointment is already cancelled
    $check_sql = "SELECT status FROM appointments WHERE appointment_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $appointment_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'cancelled') {
            $_SESSION['error'] = "Cannot update a cancelled appointment";
            
            // Redirect back with appropriate parameters
            $redirect_url = "../dashboard.php";
            if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
                $redirect_url = "../view_appoinment.php?id=" . $appointment_id;
                if (isset($_GET['date'])) {
                    $redirect_url .= "&date=" . $_GET['date'];
                }
            } else {
                if (isset($_GET['date'])) {
                    $redirect_url .= "?date=" . $_GET['date'];
                    if (isset($_GET['status']) && $_GET['status'] !== 'all') {
                        $redirect_url .= "&status=" . $_GET['status'];
                    }
                } elseif (isset($_GET['status']) && $_GET['status'] !== 'all') {
                    $redirect_url .= "?status=" . $_GET['status'];
                }
            }
            
            header("Location: " . $redirect_url);
            exit();
        }
    }
    
    // Set the MySQL session variable to pass the acting staff's ID into the trigger
    $staff_id = $_SESSION['staff_id'];
    
    // Fix: Check if user_id exists in session, otherwise set it to NULL for database
    // This handles admin-only login scenarios (different browser/incognito mode)
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    
    // Set the staff ID for database triggers
    $conn->query("SET @current_staff = $staff_id");
    
    // Only set user_id if it exists, otherwise use NULL
    $conn->query("SET @current_user = $user_id");
    
    // Update appointment status
    $sql = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $appointment_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment status updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update appointment status";
    }
    
    // Handle redirect based on where the request came from
    if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
        // Redirect back to the appointment view page
        $redirect = "../view_appoinment.php?id=" . $appointment_id;
        if (isset($_GET['date'])) {
            $redirect .= "&date=" . $_GET['date'];
        }
    } else {
        // Redirect back to dashboard with filters preserved
        $redirect = "../dashboard.php";
        $params = [];
        
        if (isset($_GET['date'])) {
            $params[] = "date=" . $_GET['date'];
        }
        
        if (isset($_GET['status']) && $_GET['status'] !== 'all') {
            $params[] = "status=" . $_GET['status'];
        }
        
        if (!empty($params)) {
            $redirect .= "?" . implode("&", $params);
        }
    }
    
    header("Location: $redirect");
    exit();
}

// If accessed without proper parameters
header("Location: ../dashboard.php");
exit();
?>