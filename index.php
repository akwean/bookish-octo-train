<?php
require_once 'connection.php';

// // If a user is logged in, redirect them to the dashboard.
if (isset($_SESSION['user_id'])) 

//     {header("Location: dashboard.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUPC Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/auth.css">
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
<?php
require_once 'connection.php';

// Debug information
echo "<div style='background: #eee; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
echo "Session ID: " . session_id() . "<br>";
echo "User ID in session: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "<br>";
echo "</div>";

// Rest of your code
?>
<?php include 'src/views/footer.php'; ?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <?php include 'src/views/login_form.php'; ?>
            </div>
        </div>
    </div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Register</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?php include 'src/views/register_form.php'; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/header.js"></script>

</body>
</html>