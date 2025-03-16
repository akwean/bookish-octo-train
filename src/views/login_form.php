<div class="modal-body">
    <?php if(isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
        <div class="alert alert-danger">Invalid email or password. Please try again.</div>
    <?php endif; ?>
    
    <form action="/process/login_process.php" method="POST">
        <div class="mb-3">
            <div class="floating-label">
                <input type="email" class="floating-label__input" id="email" name="email" placeholder=" " required>
                <label for="email" class="floating-label__label">Email address</label>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="floating-label floating-label-password">
                <input type="password" class="floating-label__input" id="password" name="password" placeholder=" " required>
                <label for="password" class="floating-label__label">Password</label>
                <button type="button" class="password-toggle" id="togglePassword">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>

<!-- Add this registration link -->
<div class="mt-3 text-center">
    <p>Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register here</a></p>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
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