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

<?php include 'src/views/footer.php'; ?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if(isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
                    <div class="alert alert-danger">Invalid email or password. Please try again.</div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register here</a></p>
                </div>
            </div>
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
            <div class="modal-body">
                <?php if(isset($_GET['reg_error'])): ?>
                    <div class="alert alert-danger"><?php echo $_GET['reg_error']; ?></div>
                <?php endif; ?>
                
                <?php if(isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
                    <div class="alert alert-success">Registration successful! You can now login.</div>
                <?php endif; ?>
                
                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="reg_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="reg_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="reg_email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="reg_email" name="email" required>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="reg_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="reg_password" name="password" required>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y password-toggle" tabindex="-1">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y password-toggle" tabindex="-1">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/header.js"></script>

</body>
</html>