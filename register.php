<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/User.php";

$db = new Database();
$userObj = new User($db);

$message = "";
if(isset($_POST['register'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if($password !== $confirm){
        $message = "Passwords do not match!";
    } else {
        if($userObj->register($username, $password)){
            $message = "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $message = "Username already exists!";
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Create Account</h1>
            <?php if($message): ?>
                <div class="alert <?= strpos($message, 'successful') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="register" class="btn btn-primary">Create Account</button>
            </form>
            <div class="auth-links">
                <p class="mb-0 mt-3">Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
