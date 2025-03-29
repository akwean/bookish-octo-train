<?php
require_once 'connection.php';
require_once 'src/Controllers/helper.php';
require_once 'src/Controllers/AppointmentController.php';

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
    <link rel="stylesheet" href="assets/css/scroll-top.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .flatpickr-day.today {
            background-color: #daffda !important;
            border-color: #87d987 !important;
            color: #208e20 !important;
        }
        .flatpickr-day.today.disabled {
            background-color: #daffda !important;
            border-color: #87d987 !important;
            color: #208e20 !important;
            opacity: 0.8;  /* Slightly transparent to show it's disabled */
            cursor: not-allowed;
        }
    </style>
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

<!-- Back to Top Button -->
<button id="back-to-top" title="Back to Top">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="assets/js/smooth-scroll.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize flatpickr date picker
    const fp = flatpickr("#datepicker", {
        inline: true,
        minDate: new Date().fp_incr(1), // Start from tomorrow instead of today
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
            // Replace the event listener for the confirm button with this:
newConfirmBtn.addEventListener('click', function() {
    // Get the modal instance
    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
    
    // Hide the modal
    modalInstance.hide();
    
    // Get the loading overlay elements
    const loadingOverlay = document.getElementById('loadingOverlay');
    const progressBar = document.getElementById('progressBar');
    
    // Reset any previous error states
    loadingOverlay.querySelectorAll('.btn-danger').forEach(btn => btn.remove());
    document.querySelector('.loading-message').textContent = 'Processing your appointment...';
    progressBar.style.backgroundColor = ''; // Reset to default color
    progressBar.style.width = '0%'; // Reset progress
    
    // Show loading overlay
    loadingOverlay.style.display = 'flex';
    loadingOverlay.classList.add('active');
    
    // Animated progress approach (more realistic than instant 100%)
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) {
            // Don't complete to 100% until we get server response
            progress = 90;
            clearInterval(progressInterval);
        }
        progressBar.style.width = `${Math.min(progress, 90)}%`;
    }, 300);
    
    // Get form data for AJAX submission
    const formData = new FormData(form);
    
    // Submit the form via AJAX instead of regular form submission
    fetch('/process/appointment_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                // Clear the loading overlay first
                loadingOverlay.style.display = 'none';
                
                if (data.error === 'slot_taken') {
                    // Create and show a nicer error modal for slot conflicts
                    const errorModal = document.createElement('div');
                    errorModal.className = 'modal fade';
                    errorModal.id = 'slotErrorModal';
                    errorModal.setAttribute('tabindex', '-1');
                    errorModal.setAttribute('role', 'dialog');
                    errorModal.setAttribute('aria-hidden', 'true');
                    errorModal.style.zIndex = '1500'; // Ensure it's above other elements
                    
                    errorModal.innerHTML = `
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-danger">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title fw-bold" style="color: black">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        Time Slot Unavailable
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="text-center mb-4">
                                        <i class="bi bi-calendar-x text-danger" style="font-size: 3.5rem;"></i>
                                    </div>
                                    <div class="alert alert-danger">
                                        <p class="mb-1"><strong>This appointment slot is no longer available.</strong></p>
                                        <p class="small mb-0">Someone else has booked ${data.time} on ${formatDate(data.date)}</p>
                                    </div>
                                    <p>Please select a different time for your appointment.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Choose Another Time</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Add to document
                    document.body.appendChild(errorModal);
                    
                    // Ensure Bootstrap is loaded before initializing modal
                    if (typeof bootstrap !== 'undefined') {
                        const bsModal = new bootstrap.Modal(document.getElementById('slotErrorModal'));
                        bsModal.show();
                        
                        // Remove from DOM after it's hidden
                        errorModal.addEventListener('hidden.bs.modal', function() {
                            errorModal.remove();
                        });
                    } else {
                        // Fallback if Bootstrap JS isn't loaded
                        console.error('Bootstrap not loaded - showing alert instead');
                        alert('Time Slot Unavailable: This appointment slot has been taken. Please select another time.');
                    }
                } else {
                    // ...existing code...
                }
                throw new Error(data.error);
            });
        }
        return response;
    })
    .then(response => {
        // When we get a response, complete the progress bar
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        
        // Check if the response is a redirect (in case of success/error)
        const redirectUrl = response.headers.get('Location');
        if (redirectUrl) {
            // Wait a moment to show the completed progress
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 500);
            return null;
        }
        return response.text();
    })
    .then(data => {
        if (data) {
            // If we got text data instead of a redirect, wait briefly then redirect to history
            setTimeout(() => {
                window.location.href = '/appointments_history.php?success=appointment_booked&refresh=true';
            }, 500);
        }
    })
    .catch(error => {
        // If there's an error, show error message
        document.querySelector('.loading-message').textContent = 'Error occurred. Please try again.';
        progressBar.style.backgroundColor = '#dc3545';
        
        // Log the error
        console.error('Submission error:', error);
        
        // Allow user to close the overlay after error
        const closeButton = document.createElement('button');
        closeButton.className = 'btn btn-danger mt-3';
        closeButton.textContent = 'Close';
        closeButton.addEventListener('click', () => {
            loadingOverlay.style.display = 'none';
        });
        document.querySelector('.loading-overlay').appendChild(closeButton);
    });
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