<?php

ob_start();

require_once '../connection.php';
require_once '../src/Controllers/AppointmentController.php';
require_once '../vendor/autoload.php'; // Add PHPMailer autoloader
require_once '../config/email_settings.php'; // Include email settings

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
            $date = $_POST['appointment_date'];
            header("Location: /appointments.php?error=missing_fields&date=" . $date);
            exit();
        }
    }
    
    // Check if timeslot is already taken
    $sql = "SELECT appointment_id FROM appointments WHERE appointment_date = ? AND time_slot = ? AND status <> 'cancelled'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $appointmentData['appointment_date'], $appointmentData['time_slot']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Return a JSON response with detailed error information for AJAX requests
        http_response_code(409); // Conflict status code
        echo json_encode([
            'error' => 'slot_taken',
            'message' => 'This time slot is already booked',
            'date' => $appointmentData['appointment_date'],
            'time' => $appointmentData['time_slot']
        ]);
        exit();
    }

    // Process the appointment
    $appointmentController = new AppointmentController();
    
    // Check if time slot is available
    if (!$appointmentController->checkTimeSlotAvailability($appointmentData['appointment_date'], $appointmentData['time_slot'])) {
        http_response_code(400);
        echo json_encode(['error' => 'slot_taken']);
        exit();
    }
    
    // Book the appointment
    $appointment_id = $appointmentController->bookAppointment($appointmentData);
    
    if ($appointment_id) {
        // Use PHP's error log instead of trying to create files
        error_log("Starting email notification for appointment #" . $appointment_id);
        
        // Send email notification to clinic staff (don't let errors disrupt the user experience)
        try {
            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);
            
            // Server settings - suppress verbose output that would break headers
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            
            // Set sender
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            // Add recipients
            foreach ($GLOBALS['staff_notification_emails'] as $email => $name) {
                $mail->addAddress($email, $name);
            }
            
            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'New Appointment Booking - ' . date('F j, Y', strtotime($appointmentData['appointment_date']));
            
            // Create purpose mapping for readable values
            $purposeMap = [
                'medical' => 'Medical consultation & treatment',
                'physical_examination' => 'Physical examination',
                'dental' => 'Dental consultation & treatment',
                'vaccination' => 'Vaccination (Flu & Pneumonia)'
            ];
            
            // Create email body - same content as before
            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #ff8000; color: white; padding: 15px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; }
                    .appointment-details { margin: 20px 0; }
                    .appointment-details table { width: 100%; border-collapse: collapse; }
                    .appointment-details th { text-align: left; padding: 8px; background-color: #f2f2f2; }
                    .appointment-details td { padding: 8px; border-top: 1px solid #ddd; }
                    .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>New Appointment Notification</h1>
                    </div>
                    <div class="content">
                        <p>A new appointment has been booked in the BUPC Clinic system.</p>
                        
                        <div class="appointment-details">
                            <table>
                                <tr>
                                    <th colspan="2">Appointment Details</th>
                                </tr>
                                <tr>
                                    <td><strong>Patient Name:</strong></td>
                                    <td>' . $appointmentData['name'] . '</td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>' . date('l, F j, Y', strtotime($appointmentData['appointment_date'])) . '</td>
                                </tr>
                                <tr>
                                    <td><strong>Time:</strong></td>
                                    <td>' . $appointmentData['time_slot'] . '</td>
                                </tr>
                                <tr>
                                    <td><strong>Purpose:</strong></td>
                                    <td>' . (isset($purposeMap[$appointmentData['purpose']]) ? $purposeMap[$appointmentData['purpose']] : $appointmentData['purpose']) . '</td>
                                </tr>
                                <tr>
                                    <td><strong>Course/Year/Block:</strong></td>
                                    <td>' . $appointmentData['course'] . ' ' . $appointmentData['year'] . '-' . $appointmentData['block'] . '</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Number:</strong></td>
                                    <td>' . $appointmentData['contact_no'] . '</td>
                                </tr>
                                <tr>
                                    <td><strong>Parent/Guardian:</strong></td>
                                    <td>' . $appointmentData['parent_guardian'] . '</td>
                                </tr>
                            </table>
                            
                            ' . (!empty($appointmentData['additional_notes']) ? '<p><strong>Additional Notes:</strong><br>' . nl2br($appointmentData['additional_notes']) . '</p>' : '') . '
                        </div>
                        
                        <p>You can view and manage this appointment in the <a href="http://localhost:8080/admin/dashboard.php">Admin Dashboard</a>.</p>
                    </div>
                    <div class="footer">
                        <p>This is an automated notification from the BUPC Clinic Appointment System.</p>
                        <p>&copy; ' . date('Y') . ' BUPC Clinic. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>';
            
            // Plain text alternative
            $mail->AltBody = 'New Appointment Notification
            
Patient: ' . $appointmentData['name'] . '
Date: ' . date('F j, Y', strtotime($appointmentData['appointment_date'])) . '
Time: ' . $appointmentData['time_slot'] . '
Purpose: ' . (isset($purposeMap[$appointmentData['purpose']]) ? $purposeMap[$appointmentData['purpose']] : $appointmentData['purpose']) . '
Course/Block/Year: ' . $appointmentData['course'] . ' ' . $appointmentData['year'] . '-' . $appointmentData['block'] . '
Contact: ' . $appointmentData['contact_no'] . '
            
Please check the admin dashboard to manage this appointment.';
            
            // Send the email (but don't block user experience if it fails)
            $mail->send();
            
        } catch (Exception $e) {
            // Just log the error but don't halt execution
            error_log("Email notification error: " . $e->getMessage());
        }

        // Turn off output buffering
        ob_end_clean();
        
        // Success - Redirect to appointment history page with auto-refresh parameter
        header("Location: /appointments_history.php?success=appointment_booked&refresh=true");
        exit();
    } else {
        // Error - Redirect with error message
        header("Location: /appointments.php?error=database_error&date=" . $appointmentData['appointment_date']);
        exit();
    }
}

// If not POST request
header("Location: /appointments.php");
exit();