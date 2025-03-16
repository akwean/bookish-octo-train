<div class="modal-body">
    <?php if(isset($_GET['reg_error'])): ?>
        <div class="alert alert-danger"><?php echo $_GET['reg_error']; ?></div>
    <?php endif; ?>
    
    <form action="/process/register_process.php" method="POST">
        <div class="mb-3">
            <div class="floating-label">
                <input type="text" class="floating-label__input" id="reg_name" name="name" placeholder=" " required>
                <label for="reg_name" class="floating-label__label">Full Name</label>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="floating-label">
                <input type="email" class="floating-label__input" id="reg_email" name="email" placeholder=" " required>
                <label for="reg_email" class="floating-label__label">Email address</label>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="floating-label floating-label-password">
                <input type="password" class="floating-label__input" id="reg_password" name="password" placeholder=" " required>
                <label for="reg_password" class="floating-label__label">Password</label>
                <button type="button" class="password-toggle" onclick="togglePassword('reg_password')">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="floating-label floating-label-password">
                <input type="password" class="floating-label__input" id="reg_confirm_password" name="confirm_password" placeholder=" " required>
                <label for="reg_confirm_password" class="floating-label__label">Confirm Password</label>
                <button type="button" class="password-toggle" onclick="togglePassword('reg_confirm_password')">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <div class="mt-3 text-center">
        <p>Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login here</a></p>
    </div>
</div>

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