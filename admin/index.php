<?php
// Admin login page

session_start();
// Redirect if already logged in
if(isset($_SESSION['staff_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/floating-labels.css">
    <style>
        .login-card {
            max-width: 450px;
            margin: 100px auto;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }
        .card-header {
            background: linear-gradient(to right, #87cefa, #ffffff, #ff9830);
            border-bottom: none;
            padding: 1.5rem;
            text-align: center;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card login-card">
                    <div class="card-header">
                        <h3 class="mb-0">Staff Login</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        
                        <form action="process/login_process.php" method="post">
                            <div class="mb-3">
                                <div class="floating-label">
                                    <input type="email" class="floating-label__input" id="email" name="email" placeholder=" " required>
                                    <label for="email" class="floating-label__label">Email</label>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="floating-label floating-label-password">
                                    <input type="password" class="floating-label__input" id="password" name="password" placeholder=" " required>
                                    <label for="password" class="floating-label__label">Password</label>
                                    <button type="button" class="password-toggle" id="toggleAdminPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleAdminPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>