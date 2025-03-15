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

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form action="" method="get" class="d-flex gap-2">
                    <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>">
                    <select name="status" class="form-control">
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
        
        <!-- Appointments Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Course/Year/Block</th>
                        <th>Purpose</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No appointments found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo $appointment['appointment_id']; ?></td>
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
                                <td>
                                <a href="view_appoinment.php?id=<?php echo $appointment['appointment_id']; ?>&date=<?php echo $date_filter; ?>" class="btn btn-sm btn-info">View</a>
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Update
                                        </button>
                                        <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=approved&date=<?php echo $date_filter; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?>">Approve</a></li>
                                        <li><a class="dropdown-item" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=completed&date=<?php echo $date_filter; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?>">Complete</a></li>
                                        <li><a class="dropdown-item" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=cancelled&date=<?php echo $date_filter; ?><?php echo $status_filter != 'all' ? '&status='.$status_filter : ''; ?>">Cancel</a></li>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>