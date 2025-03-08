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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>

<?php include 'src/views/header.php'; ?>

<div class="appointment-container container mt-5 pt-5">
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
    
    function loadAppointmentForm(date) {
        const formContainer = document.getElementById('form-container');
        
        formContainer.innerHTML = `
            <?php include 'src/views/appointment_form.php'; ?>
        `;
        
        document.getElementById('appointment-date').value = date;
        loadAvailableTimeSlots(date);
        
        // Initialize form submission handler after form is loaded
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
                
                // Handle confirmation button
                document.getElementById('confirm-submit').addEventListener('click', function() {
                    form.submit(); // Submit the form when confirmed
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

</body>
</html>