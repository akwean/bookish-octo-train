<?php
session_start();
require_once '../config.php';
require_once '../connection.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$appointment_id = $_GET['id'];
$sql = "SELECT * FROM appointments WHERE appointment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: dashboard.php");
    exit();
}

$appointment = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Appointment Details</h4>
                        <span class="badge bg-<?php 
                            echo $appointment['status'] == 'pending' ? 'warning' : 
                                ($appointment['status'] == 'approved' ? 'info' : 
                                ($appointment['status'] == 'completed' ? 'success' : 'danger')); 
                            ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Appointment ID:</strong> <?php echo $appointment['appointment_id']; ?></p>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['name']); ?></p>
                                <p><strong>Course:</strong> <?php echo htmlspecialchars($appointment['course']); ?></p>
                                <p><strong>Year:</strong> <?php echo htmlspecialchars($appointment['year']); ?></p>
                                <p><strong>Block:</strong> <?php echo htmlspecialchars($appointment['block']); ?></p>
                                <p><strong>Purpose:</strong> <?php echo htmlspecialchars($appointment['purpose']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Appointment Date:</strong> <?php echo date('F d, Y', strtotime($appointment['appointment_date'])); ?></p>
                                <p><strong>Time Slot:</strong> <?php echo $appointment['time_slot']; ?></p>
                                <p><strong>Parent/Guardian:</strong> <?php echo htmlspecialchars($appointment['parent_guardian']); ?></p>
                                <p><strong>Contact No:</strong> <?php echo htmlspecialchars($appointment['contact_no']); ?></p>
                                <p><strong>Home Address:</strong> <?php echo htmlspecialchars($appointment['home_address']); ?></p>
                                <p><strong>Created At:</strong> <?php echo date('F d, Y h:i A', strtotime($appointment['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <p><strong>Additional Notes:</strong></p>
                            <p><?php echo !empty($appointment['additional_notes']) ? nl2br(htmlspecialchars($appointment['additional_notes'])) : 'No additional notes'; ?></p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <?php 
                            // Get date parameter from URL if it exists
                            $date_param = isset($_GET['date']) ? '?date=' . $_GET['date'] : '';
                            ?>
                            <a href="dashboard.php<?php echo $date_param; ?>" class="btn btn-secondary">Back to Dashboard</a>
                            
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Update Status
                                </button>
                                <ul class="dropdown-menu">
                                    <?php 
                                    // Get date parameter for status update links
                                    $date_param = isset($_GET['date']) ? '&date=' . $_GET['date'] : '';
                                    ?>
                                    <li><a class="dropdown-item" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=approved<?php echo $date_param; ?>&reset=true">Approve</a></li>
                                    <li><a class="dropdown-item" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=completed<?php echo $date_param; ?>&reset=true">Complete</a></li>
                                    <li><a class="dropdown-item" href="process/update_status.php?id=<?php echo $appointment['appointment_id']; ?>&status=cancelled<?php echo $date_param; ?>&reset=true">Cancel</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>