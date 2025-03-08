<?php
// Include the controller to access the constants
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/AppointmentController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/helper.php';

$appointmentController = new AppointmentController();

// Get the user's name
$userName = "";
if(isset($_SESSION['user_id'])) {
    $userName = getUserName($_SESSION['user_id'], $conn);
}
?>

<div class="card">
    <div class="card-header">
        <h5>Book an Appointment</h5>
    </div>
    <div class="card-body">
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    switch($_GET['error']) {
                        case 'missing_fields':
                            echo "Please fill in all required fields.";
                            break;
                        case 'slot_taken':
                            echo "This appointment slot is already taken. Please choose another time.";
                            break;
                        case 'database_error':
                            echo "There was a problem booking your appointment. Please try again.";
                            break;
                        default:
                            echo "An error occurred. Please try again.";
                    }
                ?>
            </div>
        <?php endif; ?>
        
        <form id="appointment-form" action="/process/appointment_process.php" method="POST">
            <input type="hidden" id="appointment-date" name="appointment_date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                    <small class="text-muted">Auto-filled from your profile</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="course" class="form-label">Course</label>
                    <input type="text" class="form-control" id="course" name="course" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="block" class="form-label">Block</label>
                    <input type="text" class="form-control" id="block" name="block" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="year" class="form-label">Year</label>
                    <select class="form-select" id="year" name="year" required>
                        <option value="">Select Year</option>
                        <?php foreach(AppointmentController::YEAR_CHOICES as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="time_slot" class="form-label">Time Slot</label>
                    <select class="form-select" id="time_slot" name="time_slot" required>
                        <option value="">Select Time</option>
                        <!-- Time slots will be loaded via AJAX -->
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <select class="form-select" id="purpose" name="purpose" required>
                    <option value="">Select Purpose</option>
                    <?php foreach(AppointmentController::PURPOSE_CHOICES as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="parent_guardian" class="form-label">Parent/Guardian Name</label>
                <input type="text" class="form-control" id="parent_guardian" name="parent_guardian" required>
            </div>
            
            <div class="mb-3">
                <label for="contact_no" class="form-label">Contact Number</label>
                <input type="tel" class="form-control" id="contact_no" name="contact_no" required>
            </div>
            
            <div class="mb-3">
                <label for="home_address" class="form-label">Home Address</label>
                <textarea class="form-control" id="home_address" name="home_address" rows="2" required></textarea>
            </div>
            
            <div class="mb-3">
                <label for="additional_notes" class="form-label">Additional Notes (Optional)</label>
                <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3"></textarea>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Book Appointment</button>
            </div>
        </form>
    </div>
</div>