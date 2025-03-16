<?php
require_once 'connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/helper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/AppointmentController.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=login_required");
    exit();
}

$appointmentController = new AppointmentController();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - BUPC Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/appoinment.css">
    <link rel="stylesheet" href="assets/css/floating-labels.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>

<?php include 'src/views/header.php'; ?>

<!-- Add notification system -->
<div class="notification-container container mt-5 pt-5">
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                switch($_GET['success']) {
                    case 'appointment_booked':
                        echo "<strong>Success!</strong> Your appointment has been booked successfully.";
                        break;
                    default:
                        echo "<strong>Success!</strong> Your request has been processed.";
                }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> 
            <?php 
                switch($_GET['error']) {
                    case 'missing_fields':
                        echo "Please fill in all required fields.";
                        break;
                    case 'slot_taken':
                        echo "This appointment slot is already taken. Please choose another time.";
                        break;
                    case 'database_error':
                        echo "There was a database issue while booking your appointment. Please try again.";
                        break;
                    case 'login_required':
                        echo "You must be logged in to book an appointment.";
                        break;
                    default:
                        echo "An unexpected error occurred. Please try again.";
                }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>


<div class="appointment-container container mt-3">
    <h2 class="text-center mt-4  mb-4">Book an Appointment</h2>
    
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5>Select a Date</h5>
                </div>
                <div class="card-body">
                    <div id="datepicker"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-7" id="form-container">
            <!-- The appointment form will be loaded here after selecting a date -->
            <div class="alert alert-info">
                Please select a date from the calendar to begin booking your appointment.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize flatpickr date picker
    const fp = flatpickr("#datepicker", {
        inline: true,
        minDate: "today",
        dateFormat: "Y-m-d",
        disable: [
            function(date) {
                // Disable weekends
                return date.getDay() === 0 || date.getDay() === 6;
            }
        ],
        onChange: function(selectedDates, dateStr) {
            loadAppointmentForm(dateStr);
        }
    });

    // Check for URL parameters that indicate errors
    const urlParams = new URLSearchParams(window.location.search);
    const hasError = urlParams.has('error');
    const savedDate = urlParams.get('date');
    
    // If there's an error and a saved date, automatically select that date
    if (hasError && savedDate) {
        // Select the date in the calendar
        fp.setDate(savedDate);
        
        // Load the appointment form with the error
        loadAppointmentForm(savedDate);
    }
    
    // ONLY ONE loadAppointmentForm function - the one with form data persistence
    function loadAppointmentForm(date) {
        // Save existing form data before replacing the form
        const formData = {};
        const currentForm = document.getElementById('appointment-form');
        
        if (currentForm) {
            // Save all form field values
            const inputs = currentForm.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.id !== 'appointment-date') { // Don't save the date
                    formData[input.id] = input.value;
                }
            });
        }
        
        // Load new form
        const formContainer = document.getElementById('form-container');
        formContainer.innerHTML = `
            <?php include 'src/views/appointment_form.php'; ?>
        `;
        
        // Set the selected date
        document.getElementById('appointment-date').value = date;
        
        // Restore previously entered form data
        if (Object.keys(formData).length > 0) {
            Object.keys(formData).forEach(id => {
                const field = document.getElementById(id);
                if (field) {
                    field.value = formData[id];
                }
            });
        }
        
        // Load available time slots
        loadAvailableTimeSlots(date);
        
        // Initialize form submission handler
        initFormHandler();
    }
    
    function loadAvailableTimeSlots(date) {
        const timeSlots = [
            "9:00 AM", "9:30 AM", "10:00 AM", "10:30 AM", "11:00 AM", 
            "1:00 PM", "1:30 PM", "2:00 PM", "2:30 PM", 
            "3:00 PM", "3:30 PM", "4:00 PM", "4:30 PM"
        ];
        
        const selectElement = document.getElementById('time_slot');
        if (selectElement) {
            selectElement.innerHTML = '<option value="">Select Time</option>';
            
            timeSlots.forEach(slot => {
                const option = document.createElement('option');
                option.text = slot;
                option.value = slot;
                selectElement.appendChild(option);
            });
        }
    }
    
    function initFormHandler() {
    const form = document.getElementById('appointment-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission
            
            // Check form validity
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Populate confirmation modal with form data
            document.getElementById('confirm-date').textContent = formatDate(document.getElementById('appointment-date').value);
            document.getElementById('confirm-time').textContent = document.getElementById('time_slot').value;
            document.getElementById('confirm-name').textContent = document.getElementById('name').value;
            document.getElementById('confirm-course').textContent = document.getElementById('course').value;
            document.getElementById('confirm-block').textContent = document.getElementById('block').value;
            
            // For select elements, get the selected option's text
            const yearSelect = document.getElementById('year');
            document.getElementById('confirm-year').textContent = yearSelect.options[yearSelect.selectedIndex].text;
            
            const purposeSelect = document.getElementById('purpose');
            document.getElementById('confirm-purpose').textContent = purposeSelect.options[purposeSelect.selectedIndex].text;
            
            document.getElementById('confirm-parent').textContent = document.getElementById('parent_guardian').value;
            document.getElementById('confirm-contact').textContent = document.getElementById('contact_no').value;
            document.getElementById('confirm-address').textContent = document.getElementById('home_address').value;
            document.getElementById('confirm-notes').textContent = document.getElementById('additional_notes').value || 'None';
            
            // Show the modal
            const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            confirmationModal.show();
            
            // Remove any existing event listener first (important!)
            const confirmBtn = document.getElementById('confirm-submit');
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            // Attach the event listener to the new button
            newConfirmBtn.addEventListener('click', function() {
                // Get the modal instance
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
                
                // Hide the modal
                modalInstance.hide();
                
                // Get the loading overlay elements
                const loadingOverlay = document.getElementById('loadingOverlay');
                const progressBar = document.getElementById('progressBar');
                
                // Make sure loading overlay is visible
                loadingOverlay.style.display = 'flex';
                loadingOverlay.classList.add('active');
                
                // Start progress bar animation
                setTimeout(() => {
                    progressBar.style.width = '100%';
                }, 100);
                
                // Delay form submission to show loading animation
                setTimeout(function() {
                    form.submit(); // Submit the form after delay
                }, 2000); // 2 second delay for the animation
            });
        });
    }
}
    
    // Helper function to format date
    function formatDate(dateString) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    }
});
</script>

<!-- Loading overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner-large"></div>
    <p class="loading-message">Processing your appointment...</p>
    <div class="progress-bar-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>
</div>

</body>
</html>