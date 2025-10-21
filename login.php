<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/User.php";

$db = new Database();
$userObj = new User($db);

$message = "";

if(isset($_POST['login'])){
    $loginId = trim($_POST['username']); // username or email
    $password = $_POST['password'];

    // Fetch user by username or email
    $stmt = $db->conn->prepare("SELECT id, username, password, is_approved FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param("ss", $loginId, $loginId);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows === 1){
        $user = $res->fetch_assoc();

        if(!password_verify($password, $user['password'])){
            $message = "Incorrect password!";
        } elseif($user['is_approved'] == 0){
            $message = "Your account is pending approval. Please wait for admin approval.";
        } else {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Redirect based on role
            if($user['username'] === 'admin'){
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    } else {
        $message = "Username or email not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – BrewHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h1>Welcome Back</h1>

        <?php if($message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username or Email</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Sign In</button>
        </form>

        <div class="auth-links mt-3">
            <p class="mb-0">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
