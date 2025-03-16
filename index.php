<?php
require_once 'connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/helper.php';

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
    <link rel="stylesheet" href="assets/css/floating-labels.css">
    <link rel="stylesheet" href="assets/css/scroll-top.css">
</head>
<body>

<?php include 'src/views/header.php'; ?>

<div class="full-width-rectangle">
    <div class="rectangle-text">
        <h1>Welcome to BUPC Clinic<?php
          if(isset($_SESSION['user_id'])) {
            $fullname = getUserName($_SESSION['user_id'], $conn);
            $firstname = explode(' ', $fullname)[0];
            echo ", " . $firstname;
        } else {
            echo "";
          }
         ?> 
        </h1>
        <p>Your health is our priority. We provide quality medical services to ensure your well-being.</p>
    </div>
    <div class="rectangle-image">
        <img src="assets/images/Nurse.png" alt="Clinic Image">
    </div>
    <a href="appointments.php" class="book-appointment">Book Appointment</a>
</div>

<div class="content-layout">
    <div class="left-large-image img-fluid rounded shadow-lg"></div>
    <div class="right-rectangles">
        <a href="AI.html" class="small-rectangle">
            <img src="assets/images/ai-icon.png" alt=""> Artificial Intelligence
        </a>
        <a href="appointments_history.php" class="small-rectangle">
            <img src="assets/images/history-icon.png" alt=""> History
        </a>
        <a href="#" class="small-rectangle">
            <img src="assets/images/university-icon.png" alt=""> Bicol University
        </a>
    </div>
</div>

<!-- Back to Top Button -->
<button id="back-to-top" title="Back to Top">
    <i class="bi bi-arrow-up"></i>
</button>

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

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="assets/js/header.js"></script>
<script src="assets/js/smooth-scroll.js"></script>
<!-- Login modal trigger script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check URL parameters for login_required error
    const urlParams = new URLSearchParams(window.location.search);
    const loginError = urlParams.get('error');
    const loginModal = document.getElementById('loginModal');
    
    // Function to clean up any login alerts
    function removeLoginAlerts() {
        const existingAlerts = document.querySelectorAll('#loginModal .alert-warning');
        existingAlerts.forEach(alert => alert.remove());
    }
    
    // Add event listener to clean up alerts when modal is hidden
    loginModal.addEventListener('hidden.bs.modal', function() {
        removeLoginAlerts();
    });
    
    // When the sign-in button is clicked directly, make sure there's no alert
    document.querySelector('.btn-signin').addEventListener('click', function() {
        removeLoginAlerts();
    });
    
    // If login is required, show the login modal automatically with a message
    if (loginError === 'login_required') {
        // Show login modal
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
        
        // First remove any existing alerts
        removeLoginAlerts();
        
        // Then add the new alert
        const loginAlert = document.createElement('div');
        loginAlert.className = 'alert alert-warning mb-3';
        loginAlert.innerHTML = '<i class="bi bi-shield-lock"></i> You need to log in to access that page.';
        
        // Insert the alert at the top of the login form
        const loginForm = document.querySelector('#loginModal .modal-body form');
        loginForm.insertBefore(loginAlert, loginForm.firstChild);
        
        // Clean up the URL to remove the parameters
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>

</body>
</html>