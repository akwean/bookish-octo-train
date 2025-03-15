<?php
session_start();
require_once '../config.php';
require_once '../connection.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Get filter parameters
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// DASHBOARD STATISTICS
// Get counts for each appointment status
$stats = [
    'pending' => 0,
    'approved' => 0, 
    'completed' => 0,
    'cancelled' => 0,
    'total' => 0
];

$sql_stats = "SELECT status, COUNT(*) as count FROM appointments GROUP BY status";
$result_stats = $conn->query($sql_stats);
while ($row = $result_stats->fetch_assoc()) {
    if (isset($stats[$row['status']])) {
        $stats[$row['status']] = $row['count'];
    }
    $stats['total'] += $row['count'];
}

// RECENT APPOINTMENTS
$sql_recent = "SELECT * FROM appointments ORDER BY created_at DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);
$recent_appointments = [];
while ($row = $result_recent->fetch_assoc()) {
    $recent_appointments[] = $row;
}

// UPCOMING APPOINTMENTS
$today = date('Y-m-d');
$next_week = date('Y-m-d', strtotime('+7 days'));
$sql_upcoming = "SELECT * FROM appointments 
                WHERE appointment_date BETWEEN '$today' AND '$next_week'
                AND status = 'pending' OR status = 'approved'
                ORDER BY appointment_date ASC, time_slot ASC LIMIT 5";
$result_upcoming = $conn->query($sql_upcoming);
$upcoming_appointments = [];
while ($row = $result_upcoming->fetch_assoc()) {
    $upcoming_appointments[] = $row;
}

// Build SQL query for main appointments list based on filters
$sql = "SELECT * FROM appointments WHERE 1=1";
if ($date_filter) {
    $sql .= " AND appointment_date = '$date_filter'";
}
if ($status_filter && $status_filter != 'all') {
    $sql .= " AND status = '$status_filter'";
}
$sql .= " ORDER BY appointment_date, time_slot";

$result = $conn->query($sql);
$appointments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Add this in the <head> section -->
    <style>
    /* Improved table styles */
    .table {
        font-size: 0.9rem;
    }
    
    .dropdown-menu {
        position: absolute !important;
        transform: none !important;
        top: 100% !important;
        left: auto !important;
        right: 0 !important;
        margin-top: 2px !important;
        min-width: 10rem;
    }

    .dropdown {
        position: relative !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    /* Make dropdown items more clickable */
    .dropdown-item {
        padding: 8px 16px;
        font-size: 0.9rem;
    }
    
    /* Better hover effects */
    .table tr:hover {
        background-color: rgba(13, 110, 253, 0.05) !important;
    }
    
    /* Make status badges bigger */
    .badge {
        font-size: 0.8rem;
        padding: 0.35em 0.65em;
    }
    
    /* Action buttons */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        margin-right: 0.25rem;
    }
    
    /* Make sure action column doesn't wrap */
    .action-column {
        white-space: nowrap;
        min-width: 160px;
    }
    
    /* Dashboard stats cards */
    .stats-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    
    .stats-card.pending {
        background-color: #fff3cd;
        border-left: 5px solid #ffc107;
    }
    
    .stats-card.approved {
        background-color: #d1ecf1;
        border-left: 5px solid #17a2b8;
    }
    
    .stats-card.completed {
        background-color: #d4edda;
        border-left: 5px solid #28a745;
    }
    
    .stats-card.cancelled {
        background-color: #f8d7da;
        border-left: 5px solid #dc3545;
    }
    
    /* Quick view panels */
    .quick-view-panel {
        height: 360px;
        overflow-y: auto;
    }
    
    .appointment-item {
        border-left: 3px solid #6c757d;
        padding: 10px 15px;
        margin-bottom: 10px;
        transition: all 0.2s ease;
    }
    
    .appointment-item:hover {
        background-color: #f8f9fa;
    }
    
    .appointment-item.new {
        border-left-color: #17a2b8;
    }
    
    .appointment-item.upcoming {
        border-left-color: #28a745;
    }
    
    @media (max-width: 768px) {
        .quick-view-panel {
            height: auto;
            max-height: 300px;
        }
    }
</style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">Appointment Dashboard</h2>
    
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Dashboard Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card pending h-100" onclick="window.location.href='dashboard.php?status=pending'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Pending</h6>
                                <h3 class="card-title"><?php echo $stats['pending']; ?></h3>
                            </div>
                            <div class="stats-icon text-warning">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stats-card approved h-100" onclick="window.location.href='dashboard.php?status=approved'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Approved</h6>
                                <h3 class="card-title"><?php echo $stats['approved']; ?></h3>
                            </div>
                            <div class="stats-icon text-info">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stats-card completed h-100" onclick="window.location.href='dashboard.php?status=completed'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Completed</h6>
                                <h3 class="card-title"><?php echo $stats['completed']; ?></h3>
                            </div>
                            <div class="stats-icon text-success">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stats-card cancelled h-100" onclick="window.location.href='dashboard.php?status=cancelled'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Cancelled</h6>
                                <h3 class="card-title"><?php echo $stats['cancelled']; ?></h3>
                            </div>
                            <div class="stats-icon text-danger">
                                <i class="bi bi-x-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick View Panels -->
        <div class="row mb-4">
            <!-- Recent Appointments -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-bell"></i> Recent Appointments</h5>
                    </div>
                    <div class="card-body quick-view-panel">
                        <?php if (empty($recent_appointments)): ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                                <p class="mt-2">No recent appointments found.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_appointments as $appointment): ?>
                                <?php 
                                    $isNew = (time() - strtotime($appointment['created_at'])) < 86400; // 24 hours
                                    $statusClass = $appointment['status'] == 'pending' ? 'warning' : 
                                        ($appointment['status'] == 'approved' ? 'info' : 
                                        ($appointment['status'] == 'completed' ? 'success' : 'danger'));
                                ?>
                                <div class="appointment-item new">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($appointment['name']); ?>
                                            <?php if($isNew): ?>
                                                <span class="badge bg-info ms-1">New</span>
                                            <?php endif; ?>
                                            </h6>
                                            <div class="text-muted small">
                                                <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?> at <?php echo $appointment['time_slot']; ?>
                                            </div>
                                            <div class="small mt-1">
                                                Purpose: <?php echo htmlspecialchars($appointment['purpose']); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                            <div class="small text-muted mt-1">
                                                Created: <?php echo date('M d, g:i a', strtotime($appointment['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="view_appoinment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-secondary">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Appointments -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Upcoming Appointments</h5>
                    </div>
                    <div class="card-body quick-view-panel">
                        <?php if (empty($upcoming_appointments)): ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                                <p class="mt-2">No upcoming appointments scheduled.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <?php 
                                    $daysUntil = (strtotime($appointment['appointment_date']) - time()) / 86400;
                                    $isToday = $appointment['appointment_date'] == date('Y-m-d');
                                    $isTomorrow = $appointment['appointment_date'] == date('Y-m-d', strtotime('+1 day'));
                                ?>
                                <div class="appointment-item upcoming">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($appointment['name']); ?>
                                            <?php if($isToday): ?>
                                                <span class="badge bg-danger ms-1">Today</span>
                                            <?php elseif($isTomorrow): ?>
                                                <span class="badge bg-warning ms-1">Tomorrow</span>
                                            <?php endif; ?>
                                            </h6>
                                            <div class="text-muted small">
                                                <?php echo date('l, M d', strtotime($appointment['appointment_date'])); ?> at <?php echo $appointment['time_slot']; ?>
                                            </div>
                                            <div class="small mt-1">
                                                Course: <?php echo htmlspecialchars($appointment['course']); ?> • Block: <?php echo htmlspecialchars($appointment['block']); ?>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge bg-<?php echo $appointment['status'] == 'pending' ? 'warning' : 'info'; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                            <div class="small text-muted mt-1">
                                                Contact: <?php echo htmlspecialchars($appointment['contact_no']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="view_appoinment.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-secondary">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Find the filter form and modify it: -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form action="" method="get" class="d-flex gap-2" id="filterForm">
                    <input type="date" name="date" id="dateFilter" class="form-control" value="<?php echo $date_filter; ?>">
                    <select name="status" id="statusFilter" class="form-control">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <h5 class="mb-0">Appointments for: <?php echo date('F d, Y', strtotime($date_filter)); ?></h5>
            </div>
        </div>
        
        <!-- Replace your entire table div with this: -->
        <div class="card shadow-sm mb-5">
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow: visible;">
                    <table class="table table-striped table-hover m-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">ID</th>
                                <th>Name</th>
                                <th>Course/Year</th>
                                <th>Purpose</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th class="action-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($appointments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                                            <p class="mt-2">No appointments found for the selected criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td class="px-3"><?php echo $appointment['appointment_id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['course'] . ' ' . $appointment['year'] . '-' . $appointment['block']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['purpose']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo $appointment['time_slot']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                            echo $appointment['status'] == 'pending' ? 'warning' : 
                                                ($appointment['status'] == 'approved' ? 'info' : 
                                                ($appointment['status'] == 'completed' ? 'success' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td class="action-column">
                                            <a href="view_appoinment.php?id=<?php echo $appointment['appointment_id']; ?>&date=<?php echo $date_filter; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <div class="dropdown d-inline">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false">
                                                    Update
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item py-2" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=approved&date=<?php echo $date_filter; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?>">
                                                        <span class="text-info">✓</span> Approve
                                                    </a></li>
                                                    <li><a class="dropdown-item py-2" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=completed&date=<?php echo $date_filter; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?>">
                                                        <span class="text-success">✓</span> Complete
                                                    </a></li>
                                                    <li><a class="dropdown-item py-2" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=cancelled&date=<?php echo $date_filter; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?>">
                                                        <span class="text-danger">✕</span> Cancel
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3"><!-- Bottom padding space --></div>
            </div>
        </div>
    </div>
    
    <div class="container-fluid py-4">
        <!-- Footer spacing -->
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Add this before the closing </body> tag -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const filterForm = document.getElementById('filterForm');
        const dateFilter = document.getElementById('dateFilter');
        const statusFilter = document.getElementById('statusFilter');
        
        // Add event listeners to auto-submit form when inputs change
        dateFilter.addEventListener('change', function() {
            filterForm.submit();
        });
        
        statusFilter.addEventListener('change', function() {
            filterForm.submit();
        });
        
        // Auto-refresh the dashboard every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 5 * 60 * 1000); // 5 minutes
    });
    </script>
</body>
</html>