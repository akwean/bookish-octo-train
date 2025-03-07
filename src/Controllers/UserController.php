<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Models/User.php';

class UserController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Authentication methods
    public function login($email, $password) {
        // Make sure session is started
    if (session_status() === PHP_SESSION_NONE) {
            session_start();
    }
        // Validate credentials and create session
        return $this->userModel->authenticate($email, $password);
    }
    
    public function register($name, $email, $password) {
        // Validate and create new user
        return $this->userModel->createUser($name, $email, $password);
    }
    
    public function logout() {
        // End user session
        session_start(); 
        session_destroy();
    }
    
    // Profile management
    public function getUserProfile($userId) {
        return $this->userModel->getUserById($userId);
    }
    
    public function updateUserProfile($userId, $data) {
        return $this->userModel->updateUser($userId, $data);
    }
}
?>