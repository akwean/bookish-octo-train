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

// Build SQL query based on filters
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
    
    /* Fix dropdown positioning - CRITICAL FIX */
    .dropdown-menu {
        position: fixed !important; 
        z-index: 1050 !important;
        margin: 0 !important;
    }
    
    /* Make sure dropdowns are big enough */
    .dropdown-menu {
        min-width: 10rem;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
    }
    
    /* Prevent vertical scrolling in the table for most screens */
    .table-container {
        max-height: none;
        overflow: visible;
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
    
    /* Only add vertical scrolling on very small screens */
    @media (max-height: 600px) {
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
    }
</style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container mt-4">
    <h2>Appointment Dashboard</h2>
    
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

        <!-- Find the filter form (around line 73) and modify it: -->
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
        </div>
        
        <!-- Replace your entire table div with this: -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive" style="over-flow: visible;">
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
    </div>
</div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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
});
</script>