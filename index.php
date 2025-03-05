<?php
require_once 'connection.php';

// If a user is logged in, redirect them to the dashboard.
if (isset($_SESSION['id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUPC Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<?php include 'src/views/header.php'; ?>

<div class="full-width-rectangle">
    <div class="rectangle-text">
        <h1>Welcome to BUPC Clinic</h1>
        <p>Your health is our priority. We provide quality medical services to ensure your well-being.</p>
    </div>
    <div class="rectangle-image">
        <img src="assets/images/Nurse.png" alt="Clinic Image">
    </div>
    <a href="appointments.html" class="book-appointment">Book Appointment</a>
</div>

<div class="content-layout">
    <div class="left-large-image img-fluid rounded shadow-lg"></div>
    <div class="right-rectangles">
        <a href="AI.html" class="small-rectangle">
            <img src="assets/images/ai-icon.png" alt=""> Artificial Intelligence
        </a>
        <a href="History.html" class="small-rectangle">
            <img src="assets/images/history-icon.png" alt=""> History
        </a>
        <a href="#" class="small-rectangle">
            <img src="assets/images/university-icon.png" alt=""> Bicol University
        </a>
    </div>
</div>

<?php include 'src/views/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/header.js"></script>

</body>
</html>