<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="index.php">
        <img src="assets/images/logo.png" alt="Logo" style="height: 40px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">About Us</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Contact</a>
            </li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- Show these options when user is logged in -->
                <li class="nav-item dropdown">
                    <!-- This trigger element is essential even without dropdown-toggle class -->
                    <a class="nav-link" href="#" id="navbarDropdown" 
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        My Account
                        <!-- Custom icon instead of default caret -->
                        <i class="bi bi-person-circle ms-1"></i>
                                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                
                        <li><a class="dropdown-item" href="appointments_history.php">My Appointments</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/process/logout_process.php">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <!-- Show login button when user is not logged in -->
                <li class="nav-item">      
                    <button class="btn btn-signin" data-bs-toggle="modal" data-bs-target="#loginModal">Sign In</button>
                </li>
            <?php endif; ?>
        </ul>
            </div>
    </div>
</nav>