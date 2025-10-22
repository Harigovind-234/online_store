<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/User.php";

$db = new Database();
$userObj = new User($db);

$message = "";
$errors = [];

if(isset($_POST['register'])){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // --- Server-side validation ---
    if(empty($username) || strlen($username) < 3){
        $errors[] = "Username must be at least 3 characters.";
    }

    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Invalid email address.";
    }

    if(!empty($phone) && !preg_match('/^\+?\d{7,15}$/', $phone)){
        $errors[] = "Phone number must be 7-15 digits and can start with '+'.";
    }

    if(empty($password) || strlen($password) < 6){
        $errors[] = "Password must be at least 6 characters.";
    }

    if($password !== $confirm){
        $errors[] = "Passwords do not match.";
    }

    // Check if username/email already exists
    if(empty($errors) && !$userObj->isAvailable($username, $email)){
        $errors[] = "Username or email already exists!";
    }

    // If no errors, register user
    if(empty($errors)){
        if($userObj->register($username, $password, $email, $phone)){
            $message = "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $errors[] = "Registration failed due to a database error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register – BrewHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h1>Create Account</h1>

        <?php if(!empty($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required minlength="3">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone (optional)</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" pattern="^\+?\d{7,15}$">
                <small class="text-muted">7-15 digits, can start with +</small>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                <small class="text-muted">At least 6 characters</small>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" name="register" class="btn btn-primary w-100">Create Account</button>
        </form>

        <div class="auth-links mt-3">
            <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector("form");
    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const phone = document.getElementById("phone");
    const password = document.getElementById("password");
    const confirm = document.getElementById("confirm_password");

    const showError = (input, message) => {
        let small = input.nextElementSibling;
        if(!small || small.tagName !== 'SMALL') {
            small = document.createElement('small');
            small.classList.add('text-danger', 'd-block');
            input.parentNode.appendChild(small);
        }
        small.textContent = message;
        input.classList.add('is-invalid');
    };

    const clearError = (input) => {
        let small = input.nextElementSibling;
        if(small && small.tagName === 'SMALL') small.textContent = '';
        input.classList.remove('is-invalid');
    };

    // Validation functions
    const validateUsername = () => {
        if(username.value.trim().length < 3) {
            showError(username, "Username must be at least 3 characters.");
            return false;
        }
        clearError(username);
        return true;
    };

    const validateEmail = () => {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(!re.test(email.value.trim())) {
            showError(email, "Invalid email address.");
            return false;
        }
        clearError(email);
        return true;
    };

    const validatePhone = () => {
        if(phone.value.trim() === '') {
            clearError(phone);
            return true; // optional field
        }
        const re = /^\+?\d{7,15}$/;
        if(!re.test(phone.value.trim())) {
            showError(phone, "Phone number must be 7-15 digits and can start with '+'.");
            return false;
        }
        clearError(phone);
        return true;
    };

    const validatePassword = () => {
        if(password.value.length < 6) {
            showError(password, "Password must be at least 6 characters.");
            return false;
        }
        clearError(password);
        return true;
    };

    const validateConfirm = () => {
        if(confirm.value !== password.value) {
            showError(confirm, "Passwords do not match.");
            return false;
        }
        clearError(confirm);
        return true;
    };

    // Event listeners for real-time validation
    username.addEventListener('input', validateUsername);
    email.addEventListener('input', validateEmail);
    phone.addEventListener('input', validatePhone);
    password.addEventListener('input', validatePassword);
    confirm.addEventListener('input', validateConfirm);

    // Validate on submit
    form.addEventListener('submit', function(e) {
        let valid = validateUsername() & validateEmail() & validatePhone() & validatePassword() & validateConfirm();
        if(!valid) e.preventDefault();
    });
});
</script>

</body>
</html>
