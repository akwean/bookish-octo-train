<?php
require_once 'connection.php';

// Check if token and email are provided
$validRequest = false;
$tokenExpired = false;

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    
    // Check if token is valid and not expired
    $sql = "SELECT * FROM password_resets WHERE email = ? AND token = ? AND expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $validRequest = true;
    } else {
        // Check if token exists but is expired
        $sql = "SELECT * FROM password_resets WHERE email = ? AND token = ? AND expiry <= NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $tokenExpired = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BUPC Clinic</title>
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
                    <h5>Reset Password</h5>
                </div>
                <div class="card-body">
                    <?php if ($validRequest): ?>
                        <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                switch($_GET['error']) {
                                    case 'mismatch':
                                        echo "The passwords you entered don't match. Please try again.";
                                        break;
                                    case 'length':
                                        echo "Password must be at least 6 characters long.";
                                        break;
                                    default:
                                        echo "An error occurred. Please try again.";
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="process/reset_password_process.php" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            
                            <div class="mb-3">
                                <div class="floating-label floating-label-password">
                                    <input type="password" class="floating-label__input" id="password" name="password" placeholder=" " required>
                                    <label for="password" class="floating-label__label">New Password</label>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="floating-label floating-label-password">
                                    <input type="password" class="floating-label__input" id="confirm_password" name="confirm_password" placeholder=" " required>
                                    <label for="confirm_password" class="floating-label__label">Confirm New Password</label>
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Reset Password</button>
                            </div>
                        </form>
                    <?php elseif ($tokenExpired): ?>
                        <div class="alert alert-warning">
                            <p>This password reset link has expired. Please request a new one.</p>
                        </div>
                        <div class="d-grid">
                            <a href="forgot_password.php" class="btn btn-primary">Request New Link</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <p>Invalid or missing password reset link. Please request a new one.</p>
                        </div>
                        <div class="d-grid">
                            <a href="forgot_password.php" class="btn btn-primary">Request New Link</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-3 text-center">
                        <a href="index.php" class="text-decoration-none">Return to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const icon = event.currentTarget.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
</body>
</html>
