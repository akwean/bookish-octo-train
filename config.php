<?php
// config.php

// Base URL: adjust this if your app is deployed in a subdirectory
define('BASE_URL', 'http://localhost:8080');

// Assets path: used for linking CSS, JS, images, etc.
define('ASSETS_PATH', BASE_URL . '/assets');

// Source path: absolute path to your src directory (useful for including controllers, models, etc.)
define('SRC_PATH', __DIR__ . '/src');

$servername = "db"; // Using service name from docker-compose
$username = "clinic_user";
$password = "clinic_password";
$database = "clinic_db";

// Error reporting and timezone settings
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
?>
