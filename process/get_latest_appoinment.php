<?php
session_start();
require_once '../../config.php';
require_once '../../connection.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

header('Content-Type: application/json');

try {
    // Get the last check timestamp from the request
    $last_checked = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
    
    // Get filter parameters (optional)
    $date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    // Build SQL query based on filters
    $sql = "SELECT * FROM appointments WHERE 1=1";
    
    if ($date_filter) {
        $sql .= " AND appointment_date = ?";
    }
    
    if ($status_filter && $status_filter != 'all') {
        $sql .= " AND status = ?";
    }
    
    // Add timestamp filter to only get records newer than last check
    if ($last_checked) {
        $sql .= " AND created_at > FROM_UNIXTIME(?)";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters based on which filters are active
    if ($date_filter && $status_filter && $status_filter != 'all' && $last_checked) {
        $stmt->bind_param("ssd", $date_filter, $status_filter, $last_checked);
    } else if ($date_filter && $last_checked) {
        $stmt->bind_param("sd", $date_filter, $last_checked);
    } else if ($status_filter && $status_filter != 'all' && $last_checked) {
        $stmt->bind_param("sd", $status_filter, $last_checked);
    } else if ($last_checked) {
        $stmt->bind_param("d", $last_checked);
    } else if ($date_filter && $status_filter && $status_filter != 'all') {
        $stmt->bind_param("ss", $date_filter, $status_filter);
    } else if ($date_filter) {
        $stmt->bind_param("s", $date_filter);
    } else if ($status_filter && $status_filter != 'all') {
        $stmt->bind_param("s", $status_filter);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    // Get current server timestamp to use for next poll
    $current_timestamp = time();
    
    echo json_encode([
        'success' => true,
        'appointments' => $appointments,
        'timestamp' => $current_timestamp
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}