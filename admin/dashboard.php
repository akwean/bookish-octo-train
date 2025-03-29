<?php
session_start();
require_once '../config.php';
require_once '../connection.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    // Only return JSON if it's an AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not authenticated']);
        exit();
    } else {
        // Redirect to login page for regular requests
        header("Location: index.php");
        exit();
    }
}

// Only set JSON header for AJAX requests
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    header('Content-Type: application/json');
}

// Get filter parameters
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

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

// If it's an AJAX request asking for stats, return JSON and exit
if ($isAjax) {
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    exit();
}

// Otherwise, continue with the HTML page...

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
                                            <?php if($appointment['status'] != 'cancelled'): ?>
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
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="Cancelled appointments cannot be updated">
                                                <i class="bi bi-lock"></i> Locked
                                            </button>
                                            <?php endif; ?>
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
        
        // Auto-refresh the dashboard every 5 minutes
// Real-time appointment updates
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with current timestamp
    let lastCheckedTimestamp = Math.floor(Date.now() / 1000);
    console.log('Starting polling with timestamp:', lastCheckedTimestamp);
    
    // Get current filters
    const dateFilter = document.getElementById('dateFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    console.log('Filters:', { dateFilter, statusFilter });
    
    // Start polling for updates
    const pollInterval = setInterval(checkForUpdates, 10000); // Poll every 10 seconds

    function checkForUpdates() {
        console.log('Checking for updates...');
        const url = `/admin/process/get_latest_appointments.php?timestamp=${lastCheckedTimestamp}&date=${dateFilter}&status=${statusFilter}`;
        console.log('Request URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                if (data.success && data.appointments && data.appointments.length > 0) {
                    // Update timestamp for next poll
                    lastCheckedTimestamp = data.timestamp;
                    console.log('Updated timestamp to:', lastCheckedTimestamp);
                    
                    // Show notification for new appointments
                    const notificationCount = data.appointments.length;
                    console.log(`Found ${notificationCount} new appointment(s)`);
                    showNotification(`${notificationCount} new appointment(s) received!`);
                    
                    // Update the table with new appointments
                    updateAppointmentsTable(data.appointments);
                    
                    // Update dashboard statistics
                    updateDashboardStats();
                    
                    // Update quick panels
                    updateQuickPanels(data.appointments);
                } else {
                    console.log('No new appointments found.');
                }
            })
            .catch(error => {
                console.error('Error polling for updates:', error);
            });
    }

    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show position-fixed bottom-0 end-0 m-3';
        notification.innerHTML = `
            <strong>Update!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        // Add to document
        document.body.appendChild(notification);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    function updateAppointmentsTable(appointments) {
        const tableBody = document.querySelector('table tbody');
        if (!tableBody) return;
        
        // Add new appointments to the table
        appointments.forEach(appointment => {
            // Check if appointment is already in table
            const existingRow = document.querySelector(`tr[data-appointment-id="${appointment.appointment_id}"]`);
            if (existingRow) {
                // Update existing row
                updateExistingRow(existingRow, appointment);
            } else {
                // Create new row and add to table
                const newRow = createAppointmentRow(appointment);
                tableBody.insertBefore(newRow, tableBody.firstChild);
                
                // Highlight new row
                highlightRow(newRow);
            }
        });
    }

    function updateExistingRow(row, appointment) {
        // Update row data based on appointment
        const statusCell = row.querySelector('td:nth-child(8)');
        if (statusCell) {
            statusCell.innerHTML = `
                <span class="badge bg-${getStatusClass(appointment.status)}">
                    ${capitalizeFirstLetter(appointment.status)}
                </span>
            `;
            highlightRow(row);
        }
    }

    function createAppointmentRow(appointment) {
        const row = document.createElement('tr');
        row.setAttribute('data-appointment-id', appointment.appointment_id);
        
        // Create the row based on your table structure
        row.innerHTML = `
            <td class="px-3">${appointment.appointment_id}</td>
            <td>${htmlEscape(appointment.name)}</td>
            <td>${htmlEscape(appointment.course + ' ' + appointment.year + '-' + appointment.block)}</td>
            <td>${htmlEscape(appointment.purpose)}</td>
            <td>${formatDate(appointment.appointment_date)}</td>
            <td>${appointment.time_slot}</td>
            <td>
                <span class="badge bg-${getStatusClass(appointment.status)}">
                    ${capitalizeFirstLetter(appointment.status)}
                </span>
            </td>
            <td class="action-column">
                <a href="view_appoinment.php?id=${appointment.appointment_id}&date=${dateFilter}" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> View
                </a>
                ${appointment.status !== 'cancelled' ? `
                <div class="dropdown d-inline">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" 
                            data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false">
                        Update
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item py-2" href="process/update_status.php?id=${appointment.appointment_id}&status=approved&date=${dateFilter}${statusFilter != 'all' ? '&status='+statusFilter : ''}">
                            <span class="text-info">✓</span> Approve
                        </a></li>
                        <li><a class="dropdown-item py-2" href="process/update_status.php?id=${appointment.appointment_id}&status=completed&date=${dateFilter}${statusFilter != 'all' ? '&status='+statusFilter : ''}">
                            <span class="text-success">✓</span> Complete
                        </a></li>
                        <li><a class="dropdown-item py-2" href="process/update_status.php?id=${appointment.appointment_id}&status=cancelled&date=${dateFilter}${statusFilter != 'all' ? '&status='+statusFilter : ''}">
                            <span class="text-danger">✕</span> Cancel
                        </a></li>
                    </ul>
                </div>
                ` : `
                <button class="btn btn-sm btn-secondary" disabled title="Cancelled appointments cannot be updated">
                    <i class="bi bi-lock"></i> Locked
                </button>
                `}
            </td>
        `;
        
        return row;
    }
    
    function highlightRow(row) {
        // Highlight the row with a yellow background that fades out
        row.style.animation = 'highlight-fade 3s';
        
        // Add this CSS if not already in your styles:
        if (!document.getElementById('highlight-animation')) {
            const style = document.createElement('style');
            style.id = 'highlight-animation';
            style.textContent = `
                @keyframes highlight-fade {
                    0% { background-color: #fff3cd; }
                    100% { background-color: transparent; }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    function updateDashboardStats() {
        // Fetch updated statistics
        fetch('/admin/process/get_dashboard_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the stats cards with new numbers
                    document.querySelector('.stats-card.pending .card-title').textContent = data.stats.pending;
                    document.querySelector('.stats-card.approved .card-title').textContent = data.stats.approved;
                    document.querySelector('.stats-card.completed .card-title').textContent = data.stats.completed;
                    document.querySelector('.stats-card.cancelled .card-title').textContent = data.stats.cancelled;
                }
            })
            .catch(error => {
                console.error('Error updating dashboard stats:', error);
            });
    }
    
    // New function to update the quick panels with new appointment data
    function updateQuickPanels(appointments) {
        console.log('Updating quick panels with new appointments');
        
        // First, update the Recent Appointments panel
        updateRecentAppointmentsPanel(appointments);
        
        // Then, update the Upcoming Appointments panel
        updateUpcomingAppointmentsPanel(appointments);
    }
    
    function updateRecentAppointmentsPanel(appointments) {
        // Get the recent appointments panel container
        const recentPanel = document.querySelector('.card-header.bg-info').closest('.card').querySelector('.quick-view-panel');
        if (!recentPanel) {
            console.log('Could not find recent appointments panel');
            return;
        }
        
        // Only update if there's new content
        if (appointments.length === 0) return;
        
        // Remove the "No recent appointments found" message if it exists
        const emptyMessage = recentPanel.querySelector('.text-center.text-muted');
        if (emptyMessage) {
            recentPanel.innerHTML = '';
        }
        
        // Limit to displaying the 5 most recent appointments
        const recentAppointments = appointments.sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        }).slice(0, 5);
        
        // Add the new appointments to the top of the panel
        recentAppointments.forEach(appointment => {
            // Check if this appointment is already in the panel
            const existingItem = recentPanel.querySelector(`.appointment-item[data-id="${appointment.appointment_id}"]`);
            if (existingItem) {
                // Update the existing item
                updateAppointmentItem(existingItem, appointment, 'recent');
            } else {
                // Create a new appointment item
                const isNew = (Date.now() / 1000 - new Date(appointment.created_at).getTime() / 1000) < 86400; // 24 hours
                const statusClass = getStatusClass(appointment.status);
                
                const newItem = document.createElement('div');
                newItem.className = 'appointment-item new';
                newItem.setAttribute('data-id', appointment.appointment_id);
                newItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${htmlEscape(appointment.name)}
                            ${isNew ? '<span class="badge bg-info ms-1">New</span>' : ''}
                            </h6>
                            <div class="text-muted small">
                                ${formatDate(appointment.appointment_date)} at ${appointment.time_slot}
                            </div>
                            <div class="small mt-1">
                                Purpose: ${htmlEscape(appointment.purpose)}
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-${statusClass}">
                                ${capitalizeFirstLetter(appointment.status)}
                            </span>
                            <div class="small text-muted mt-1">
                                Created: ${formatDateTime(appointment.created_at)}
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="view_appoinment.php?id=${appointment.appointment_id}" class="btn btn-sm btn-outline-secondary">View Details</a>
                    </div>
                `;
                
                // Insert at the top
                if (recentPanel.firstChild) {
                    recentPanel.insertBefore(newItem, recentPanel.firstChild);
                } else {
                    recentPanel.appendChild(newItem);
                }
                
                // Highlight the new item
                highlightElement(newItem);
            }
        });
        
        // Limit the panel to show only 5 items
        const items = recentPanel.querySelectorAll('.appointment-item');
        if (items.length > 5) {
            for (let i = 5; i < items.length; i++) {
                items[i].remove();
            }
        }
    }
    
    function updateUpcomingAppointmentsPanel(appointments) {
        // Get the upcoming appointments panel container
        const upcomingPanel = document.querySelector('.card-header.bg-success').closest('.card').querySelector('.quick-view-panel');
        if (!upcomingPanel) {
            console.log('Could not find upcoming appointments panel');
            return;
        }
        
        // Only process appointments that are relevant for upcoming (pending or approved, and in the next week)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const nextWeek = new Date();
        nextWeek.setDate(nextWeek.getDate() + 7);
        nextWeek.setHours(23, 59, 59, 999);
        
        const upcomingAppointments = appointments.filter(appointment => {
            const appointmentDate = new Date(appointment.appointment_date);
            return (appointment.status === 'pending' || appointment.status === 'approved') && 
                   appointmentDate >= today && appointmentDate <= nextWeek;
        }).sort((a, b) => {
            return new Date(a.appointment_date) - new Date(b.appointment_date);
        });
        
        // If no relevant upcoming appointments, don't update
        if (upcomingAppointments.length === 0) return;
        
        // Remove the "No upcoming appointments" message if it exists
        const emptyMessage = upcomingPanel.querySelector('.text-center.text-muted');
        if (emptyMessage) {
            upcomingPanel.innerHTML = '';
        }
        
        // Process each appointment
        upcomingAppointments.forEach(appointment => {
            // Check if this appointment is already in the panel
            const existingItem = upcomingPanel.querySelector(`.appointment-item[data-id="${appointment.appointment_id}"]`);
            if (existingItem) {
                // Update the existing item
                updateAppointmentItem(existingItem, appointment, 'upcoming');
            } else {
                // Create a new appointment item
                const appointmentDate = new Date(appointment.appointment_date);
                const isToday = appointmentDate.toDateString() === today.toDateString();
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const isTomorrow = appointmentDate.toDateString() === tomorrow.toDateString();
                
                const newItem = document.createElement('div');
                newItem.className = 'appointment-item upcoming';
                newItem.setAttribute('data-id', appointment.appointment_id);
                newItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${htmlEscape(appointment.name)}
                            ${isToday ? '<span class="badge bg-danger ms-1">Today</span>' : ''}
                            ${!isToday && isTomorrow ? '<span class="badge bg-warning ms-1">Tomorrow</span>' : ''}
                            </h6>
                            <div class="text-muted small">
                                ${formatDayDate(appointment.appointment_date)} at ${appointment.time_slot}
                            </div>
                            <div class="small mt-1">
                                Course: ${htmlEscape(appointment.course)} • Block: ${htmlEscape(appointment.block)}
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-${appointment.status === 'pending' ? 'warning' : 'info'}">
                                ${capitalizeFirstLetter(appointment.status)}
                            </span>
                            <div class="small text-muted mt-1">
                                Contact: ${htmlEscape(appointment.contact_no)}
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="view_appoinment.php?id=${appointment.appointment_id}" class="btn btn-sm btn-outline-secondary">View Details</a>
                    </div>
                `;
                
                // Insert in the correct position (sorted by date)
                let inserted = false;
                const existingItems = upcomingPanel.querySelectorAll('.appointment-item');
                for (let i = 0; i < existingItems.length; i++) {
                    const itemId = existingItems[i].getAttribute('data-id');
                    const existingAppointment = upcomingAppointments.find(a => a.appointment_id.toString() === itemId);
                    
                    if (existingAppointment && new Date(appointment.appointment_date) < new Date(existingAppointment.appointment_date)) {
                        upcomingPanel.insertBefore(newItem, existingItems[i]);
                        inserted = true;
                        break;
                    }
                }
                
                if (!inserted) {
                    upcomingPanel.appendChild(newItem);
                }
                
                // Highlight the new item
                highlightElement(newItem);
            }
        });
        
        // Limit the panel to show only 5 items
        const items = upcomingPanel.querySelectorAll('.appointment-item');
        if (items.length > 5) {
            for (let i = 5; i < items.length; i++) {
                items[i].remove();
            }
        }
    }
    
    function updateAppointmentItem(item, appointment, type) {
        // Update the status badge
        const statusBadge = item.querySelector('.badge');
        if (statusBadge) {
            if (type === 'recent') {
                statusBadge.className = `badge bg-${getStatusClass(appointment.status)}`;
            } else {
                statusBadge.className = `badge bg-${appointment.status === 'pending' ? 'warning' : 'info'}`;
            }
            statusBadge.textContent = capitalizeFirstLetter(appointment.status);
        }
        
        // Highlight the updated item
        highlightElement(item);
    }
    
    function highlightElement(element) {
        // First remove any existing highlight
        element.style.transition = 'background-color 1s';
        element.style.backgroundColor = '#fff3cd'; // Highlight color
        
        // After a short delay, start fading back to normal
        setTimeout(() => {
            element.style.backgroundColor = '';
        }, 3000);
    }
       
    // Helper function to format date and time for display
    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit' }) + ', ' +
               date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
    
    function formatDayDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: '2-digit' });
    }
    
    // Helper functions
    function htmlEscape(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    }
    
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    function getStatusClass(status) {
        switch (status) {
            case 'pending': return 'warning';
            case 'approved': return 'info';
            case 'completed': return 'success';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }
});

    </script>
</body>
</html>