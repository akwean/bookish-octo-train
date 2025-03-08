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
            // Load the appointment form with the selected date
            loadAppointmentForm(dateStr);
        }
    });
    
    function loadAppointmentForm(date) {
        // In a real app, this would load the form via AJAX
        // For simplicity, we'll include the form directly
        const formContainer = document.getElementById('form-container');
        
        // You'd typically fetch this via AJAX
        formContainer.innerHTML = `
            <?php include 'src/views/appointment_form.php'; ?>
        `;
        
        // Set the selected date
        document.getElementById('appointment-date').value = date;
        
        // Load available time slots via AJAX
        loadAvailableTimeSlots(date);
    }
    
    function loadAvailableTimeSlots(date) {
        // In a real application, this would be an AJAX call to get available slots
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
});
</script>

</body>
</html>