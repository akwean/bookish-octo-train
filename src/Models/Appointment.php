<?php

class Appointment {
    private $conn;
    private $table = 'appointments';
    
    // Appointment properties
    public $appointment_id;
    public $user_id;
    public $name;
    public $course;
    public $block;
    public $year;
    public $purpose;
    public $time_slot;
    public $parent_guardian;
    public $contact_no;
    public $home_address;
    public $additional_notes;
    public $appointment_date;
    public $status;
    public $created_at;
    
    public function __construct() {
        require_once dirname(__DIR__, 2) . '/config.php';
        
        // Use global keyword to access the variables from config.php
        global $servername, $username, $password, $database;
        
        // Create connection
        $this->conn = new mysqli($servername, $username, $password, $database);
        
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }
    
    // Create new appointment
    public function createAppointment($data) {
        $fields = implode(", ", array_keys($data));
        $placeholders = str_repeat("?, ", count($data) - 1) . "?";
        
        $query = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt) {
            // Dynamically bind parameters
            $types = str_repeat("s", count($data)); // Assuming all strings for simplicity
            $values = array_values($data);
            
            $stmt->bind_param($types, ...$values);
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
        }
        
        return false;
    }
    
    // Get appointments for a user
    public function getUserAppointments($user_id) {
        // Change from ASC to DESC for appointment_date to get newest first
        $query = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $appointments = [];
        
        while($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        
        return $appointments;
    }
    
    // Get a single appointment
    public function getAppointmentById($appointment_id) {
        $query = "SELECT * FROM {$this->table} WHERE appointment_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Check if time slot is available - only block if there's an approved appointment
    public function isTimeSlotAvailable($date, $time) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE appointment_date = ? AND time_slot = ? AND status = 'approved'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $date, $time);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] == 0;
    }
    
    // Update appointment status
    public function updateAppointmentStatus($appointment_id, $status) {
        $query = "UPDATE {$this->table} SET status = ? WHERE appointment_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $status, $appointment_id);
        
        return $stmt->execute();
    }
}
?>