<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/AppointmentController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php?error=login_required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Collect all form data
    $appointmentData = [
        'user_id' => $user_id,
        'name' => $_POST['name'],
        'course' => $_POST['course'],
        'block' => $_POST['block'],
        'year' => $_POST['year'],
        'purpose' => $_POST['purpose'],
        'time_slot' => $_POST['time_slot'],
        'parent_guardian' => $_POST['parent_guardian'],
        'contact_no' => $_POST['contact_no'],
        'home_address' => $_POST['home_address'],
        'appointment_date' => $_POST['appointment_date'],
        'status' => 'pending'
    ];
    
    // Add additional notes if provided
    if (!empty($_POST['additional_notes'])) {
        $appointmentData['additional_notes'] = $_POST['additional_notes'];
    }
    
    // Required fields validation
    $required = ['name', 'course', 'block', 'year', 'purpose', 'time_slot', 
                'parent_guardian', 'contact_no', 'home_address', 'appointment_date'];
    
    foreach ($required as $field) {
        if (empty($appointmentData[$field])) {
            header("Location: /appointments.php?error=missing_fields");
            exit();
        }
    }
    
    // Process the appointment
    $appointmentController = new AppointmentController();
    
    // Check if time slot is available
    if (!$appointmentController->checkTimeSlotAvailability($appointmentData['appointment_date'], $appointmentData['time_slot'])) {
        header("Location: /appointments.php?error=slot_taken");
        exit();
    }
    
    // Book the appointment
    $appointment_id = $appointmentController->bookAppointment($appointmentData);
    
    if ($appointment_id) {
        // Success
        header("Location: /index.php?success=appointment_booked");
        exit();
    } else {
        // Error
        header("Location: /appointments.php?error=database_error");
        exit();
    }
}

// If not POST request
header("Location: /appointments.php");
exit();