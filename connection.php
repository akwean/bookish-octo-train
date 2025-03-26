<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set MySQL session timezone to match PHP timezone
$timezone = date_default_timezone_get();
$conn->query("SET time_zone = '+08:00'");  // For Philippines/Manila timezone (UTC+8)
?>
