<?php
require_once '../connection.php';
require_once '../src/Controllers/AppointmentController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$appointmentController = new AppointmentController();

try {
    // Get appointments
    $appointments = $appointmentController->getUserAppointments($user_id);
    
    // Return as JSON
    echo json_encode($appointments);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to retrieve appointments', 'message' => $e->getMessage()]);
}
?>