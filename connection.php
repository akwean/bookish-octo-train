<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
