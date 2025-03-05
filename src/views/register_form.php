<div class="modal-body">
    <?php if(isset($_GET['reg_error'])): ?>
        <div class="alert alert-danger"><?php echo $_GET['reg_error']; ?></div>
    <?php endif; ?>
    
    <form action="/process/register_process.php" method="POST">
        <div class="mb-3">
            <label for="reg_name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="reg_name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="reg_email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="reg_email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="reg_password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="reg_password" name="password" required>
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('reg_password')">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        <div class="mb-3">
            <label for="reg_confirm_password" class="form-label">Confirm Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="reg_confirm_password" name="confirm_password" required>
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('reg_confirm_password')">
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