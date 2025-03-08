<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Models/Appointment.php';

class AppointmentController {
    private $appointmentModel;
    
    // Constants for dropdown fields
    public const YEAR_CHOICES = [
        '1st' => 'First Year',
        '2nd' => 'Second Year',
        '3rd' => 'Third Year',
        '4th' => 'Fourth Year'
    ];
    
    public const PURPOSE_CHOICES = [
        'medical' => 'Medical (consultation & treatment)',
        'physical_examination' => 'Physical examination (e.g., athletic activities, OJT/internship, extra-curricular, scholarship)',
        'dental' => 'Dental consultation & treatment',
        'vaccination' => 'Vaccination (Flu & Pneumonia) done annually (free)'
    ];
    
    public function __construct() {
        $this->appointmentModel = new Appointment();
    }
    
    // Book a new appointment
    public function bookAppointment($data) {
        // Add timestamp for creation
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Default status is pending
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }
        
        return $this->appointmentModel->createAppointment($data);
    }
    
    // Get appointments for a user
    public function getUserAppointments($user_id) {
        return $this->appointmentModel->getUserAppointments($user_id);
    }
    
    // Get a specific appointment details
    public function getAppointment($appointment_id) {
        return $this->appointmentModel->getAppointmentById($appointment_id);
    }
    
    // Check if time slot is available
    public function checkTimeSlotAvailability($date, $time) {
        return $this->appointmentModel->isTimeSlotAvailable($date, $time);
    }
    
    // Cancel an appointment
    public function cancelAppointment($appointment_id) {
        return $this->appointmentModel->updateAppointmentStatus($appointment_id, 'cancelled');
    }
    
    // Get all available time slots for a date
    public function getAvailableTimeSlots($date) {
        $allSlots = [
            "9:00 AM", "9:30 AM", "10:00 AM", "10:30 AM", "11:00 AM", 
            "1:00 PM", "1:30 PM", "2:00 PM", "2:30 PM", 
            "3:00 PM", "3:30 PM", "4:00 PM", "4:30 PM"
        ];
        
        $availableSlots = [];
        foreach ($allSlots as $slot) {
            if ($this->checkTimeSlotAvailability($date, $slot)) {
                $availableSlots[] = $slot;
            }
        }
        
        return $availableSlots;
    }
}
?>