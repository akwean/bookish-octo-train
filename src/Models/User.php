<?php

class User {
    private $conn;
    private $table = 'users';
    
    // User properties
    public $user_id;
    public $name;
    public $email;
    public $password;
    public $created_at;
    
    
    public function __construct() {
        require_once dirname(__DIR__, 2) . '/config.php';
        
        // Create a direct connection instead of using the global one
        $this->conn = new mysqli($servername, $username, $password, $database);
        
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    // Authenticate user for login
    public function authenticate($email, $password) {
        $query = "SELECT user_id, password FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                return $row['user_id'];
            }
        }
        return false;
    }

    // Create new user/registration
    public function createUser($name, $email, $password) {
        // Check if email already exists
        if ($this->emailExists($email)) {
            return false;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $query = "INSERT INTO {$this->table} (name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // Check if email exists
    private function emailExists($email) {
        $query = "SELECT user_id FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // Get user by ID
    public function getUserById($user_id) {
        $query = "SELECT user_id, name, email, created_at FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

     // Update user information
     public function updateUser($user_id, $data) {
        $fields = "";
        $types = "";
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key != 'user_id') {
                $fields .= "$key = ?, ";
                $types .= "s";
                $values[] = $value;
            }
        }
        // Remove trailing comma
        $fields = rtrim($fields, ", ");
        
        // Add user_id for WHERE clause
        $types .= "i";
        $values[] = $user_id;
        
        $query = "UPDATE {$this->table} SET $fields WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        
        // Dynamically bind parameters
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }
}
    
        
?>