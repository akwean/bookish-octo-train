<?php
require_once '../connection.php';
require_once '../src/Controllers/AppointmentController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if POST request and has appointment_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
    $user_id = $_SESSION['user_id'];
    
    $appointmentController = new AppointmentController();
    
    // Verify the appointment belongs to the user
    $appointment = $appointmentController->getAppointment($appointment_id);
    
    if (!$appointment || $appointment['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found or not authorized']);
        exit();
    }
    
    // Cancel the appointment
    $result = $appointmentController->cancelAppointment($appointment_id);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>