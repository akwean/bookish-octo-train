<?php
// filepath: /home/cj/Documents/bupc-clinic/process/get_user_appointments_updates.php
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
$last_checked = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;

try {
    // Get updated appointments since last check
    $sql = "SELECT * FROM appointments WHERE user_id = ? AND created_at > FROM_UNIXTIME(?) ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $user_id, $last_checked);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $appointments = [];
    
    while($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    // Get current timestamp for next poll
    $current_timestamp = time();
    
    echo json_encode([
        'success' => true,
        'appointments' => $appointments,
        'timestamp' => $current_timestamp
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to retrieve appointment updates', 
        'message' => $e->getMessage()
    ]);
}