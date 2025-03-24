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
    // Get counts for each appointment status
    $stats = [
        'pending' => 0,
        'approved' => 0, 
        'completed' => 0,
        'cancelled' => 0,
        'total' => 0
    ];
    
    $sql = "SELECT status, COUNT(*) as count FROM appointments GROUP BY status";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        if (isset($stats[$row['status']])) {
            $stats[$row['status']] = $row['count'];
        }
        $stats['total'] += $row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
