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
                    <div class="floating-label">
                        <input type="text" class="floating-label__input" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" readonly placeholder=" ">
                        <label for="name" class="floating-label__label">Full Name</label>
                    </div>
                    <small class="text-muted">Auto-filled from your profile</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="floating-label">
                        <input type="text" class="floating-label__input" id="course" name="course" placeholder=" " required>
                        <label for="course" class="floating-label__label">Course</label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="floating-label">
                        <input type="text" class="floating-label__input" id="block" name="block" placeholder=" " required>
                        <label for="block" class="floating-label__label">Block</label>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="floating-label">
                        <select class="floating-label__input floating-label__select" id="year" name="year" required>
                            <option value="" selected disabled></option>
                            <?php foreach(AppointmentController::YEAR_CHOICES as $value => $label): ?>
                                <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="year" class="floating-label__label">Year</label>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="floating-label">
                        <select class="floating-label__input floating-label__select" id="time_slot" name="time_slot" required>
                            <option value="" selected disabled></option>
                            <!-- Time slots will be loaded via AJAX -->
                        </select>
                        <label for="time_slot" class="floating-label__label">Time Slot</label>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="floating-label">
                    <select class="floating-label__input floating-label__select" id="purpose" name="purpose" required placeholder=" ">
                        <option value="" selected disabled></option>
                        <?php foreach(AppointmentController::PURPOSE_CHOICES as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="purpose" class="floating-label__label">Purpose</label>
                </div>
            </div>

            <div class="mb-3">
                <div class="floating-label">
                    <input type="text" class="floating-label__input" id="parent_guardian" name="parent_guardian" placeholder=" " required>
                    <label for="parent_guardian" class="floating-label__label">Parent/Guardian Name</label>
                </div>
            </div>

            <div class="mb-3">
                <div class="floating-label">
                    <input type="tel" class="floating-label__input" id="contact_no" name="contact_no" placeholder=" " required>
                    <label for="contact_no" class="floating-label__label">Contact Number</label>
                </div>
            </div>

            <div class="mb-3">
                <div class="floating-label">
                    <textarea class="floating-label__input floating-label__textarea" id="home_address" name="home_address" placeholder=" " rows="2" required></textarea>
                    <label for="home_address" class="floating-label__label">Home Address</label>
                </div>
            </div>

            <div class="mb-3">
                <div class="floating-label">
                    <textarea class="floating-label__input floating-label__textarea" id="additional_notes" name="additional_notes" placeholder=" " rows="3"></textarea>
                    <label for="additional_notes" class="floating-label__label">Additional Notes (Optional)</label>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Book Appointment</button>
            </div>
        </form>
    </div>
</div>

<!-- Add this at the end of appointment_form.php, just before the closing </div> tag -->
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Your Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    Please review your appointment details before confirming.
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Date:</strong> <span id="confirm-date"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Time:</strong> <span id="confirm-time"></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Name:</strong> <span id="confirm-name"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Course:</strong> <span id="confirm-course"></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Block:</strong> <span id="confirm-block"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Year:</strong> <span id="confirm-year"></span>
                    </div>
                    <div class="col-md-4 mt-3">
                        <strong>Purpose:</strong> <span id="confirm-purpose"></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Parent/Guardian:</strong> <span id="confirm-parent"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Contact Number:</strong> <span id="confirm-contact"></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Home Address:</strong> <span id="confirm-address"></span>
                </div>
                
                <div class="mb-3">
                    <strong>Additional Notes:</strong> <span id="confirm-notes"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-submit">Confirm Appointment</button>
            </div>
        </div>
    </div>
</div>