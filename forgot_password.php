<?php
require_once 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BUPC Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/floating-labels.css">
</head>
<body>

<?php include 'src/views/header.php'; ?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card" style="margin: 200px auto;">
                <div class="card-header">
                    <h5>Forgot Password</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($_GET['status']) && $_GET['status'] == 'sent'): ?>
                        <div class="alert alert-success">
                            A password reset link has been sent to your email if it exists in our database. Please check your inbox and follow the instructions.
                        </div>
                    <?php elseif(isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                        <div class="alert alert-danger">
                            There was an error processing your request. Please try again later.
                        </div>
                    <?php else: ?>
                        <p>Enter your email address below and we'll send you a link to reset your password.</p>
                        <form action="process/send_reset_email.php" method="POST">
                            <div class="mb-3">
                                <div class="floating-label">
                                    <input type="email" class="floating-label__input" id="email" name="email" placeholder=" " required>
                                    <label for="email" class="floating-label__label">Email address</label>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Send Reset Link</button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="mt-3 text-center">
                        <a href="index.php" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
