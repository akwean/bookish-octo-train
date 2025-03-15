<?php
require_once '../config.php';
require_once '../connection.php';

// Only run this once to set up the first admin
// Delete this file after running it!

// Check if the admin table exists, if not create it
$sql = "CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'nurse',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

$conn->query($sql);

// Create default admin user
$name = "Admin User";
$email = "admin@example.com";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Change this!
$role = "admin";

$sql = "INSERT INTO staff (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $password, $role);

if ($stmt->execute()) {
    echo "Admin user created successfully!";
    echo "<p>Email: admin@example.com</p>";
    echo "<p>Password: admin123</p>";
    echo "<p>IMPORTANT: Delete this file after setup!</p>";
} else {
    echo "Error: " . $stmt->error;
}
?>