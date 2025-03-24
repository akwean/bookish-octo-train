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

// Get timestamp parameter from request
$last_timestamp = isset($_GET['timestamp']) ? (int)$_GET['timestamp'] : 0;
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Convert timestamp to MySQL datetime format for comparison
$last_checked = date('Y-m-d H:i:s', $last_timestamp);

try {
    // Build SQL query based on filters
    // For main table updates, we look for appointments matching the filter
    $mainTableSql = "SELECT * FROM appointments WHERE (created_at > ? OR updated_at > ?)";
    
    // Apply date filter if needed
    if ($date_filter) {
        $mainTableSql .= " AND appointment_date = ?";
    }
    
    // Apply status filter if needed
    if ($status_filter && $status_filter != 'all') {
        $mainTableSql .= " AND status = ?";
    }
    
    $mainTableSql .= " ORDER BY created_at DESC LIMIT 10";

    // For recent appointments panel, we get the newest ones regardless of date/status
    $recentSql = "SELECT * FROM appointments WHERE created_at > ? ORDER BY created_at DESC LIMIT 5";
    
    // For upcoming appointments panel, we get pending/approved ones in the next week
    $today = date('Y-m-d');
    $nextWeek = date('Y-m-d', strtotime('+7 days'));
    $upcomingSql = "SELECT * FROM appointments 
                   WHERE (created_at > ? OR updated_at > ?)
                   AND appointment_date BETWEEN ? AND ? 
                   AND (status = 'pending' OR status = 'approved')
                   ORDER BY appointment_date ASC, time_slot ASC LIMIT 5";

    // Prepare and execute main table query
    $stmt = $conn->prepare($mainTableSql);
    
    // Bind parameters based on provided filters
    if ($date_filter && $status_filter && $status_filter != 'all') {
        $stmt->bind_param("ssss", $last_checked, $last_checked, $date_filter, $status_filter);
    } else if ($date_filter) {
        $stmt->bind_param("sss", $last_checked, $last_checked, $date_filter);
    } else {
        $stmt->bind_param("ss", $last_checked, $last_checked);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    // Prepare and execute recent appointments query 
    $recentStmt = $conn->prepare($recentSql);
    $recentStmt->bind_param("s", $last_checked);
    $recentStmt->execute();
    $recentResult = $recentStmt->get_result();
    
    while ($row = $recentResult->fetch_assoc()) {
        // Add to appointments if not already included
        if (!in_array($row['appointment_id'], array_column($appointments, 'appointment_id'))) {
            $appointments[] = $row;
        }
    }
    
    // Prepare and execute upcoming appointments query
    $upcomingStmt = $conn->prepare($upcomingSql);
    $upcomingStmt->bind_param("ssss", $last_checked, $last_checked, $today, $nextWeek);
    $upcomingStmt->execute();
    $upcomingResult = $upcomingStmt->get_result();
    
    while ($row = $upcomingResult->fetch_assoc()) {
        // Add to appointments if not already included
        if (!in_array($row['appointment_id'], array_column($appointments, 'appointment_id'))) {
            $appointments[] = $row;
        }
    }
    
    // Return new data and current timestamp
    echo json_encode([
        'success' => true,
        'timestamp' => time(),
        'appointments' => $appointments
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
