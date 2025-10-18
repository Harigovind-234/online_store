<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/User.php";

$db = new Database();
$userObj = new User($db);

$message = "";
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = $userObj->login($username, $password);
    if($user){
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Redirect based on role
        if($user['username'] === 'admin'){
            header("Location: admin.php"); // Admin dashboard
        } else {
            header("Location: index.php"); // Regular user shop
        }
        exit;
    } else {
        // Optional debug mode: add ?debug=1 to URL
        if(isset($_GET['debug']) && $_GET['debug'] == '1'){
            $stmt = $db->conn->prepare("SELECT id, password FROM users WHERE username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $found = $stmt->get_result()->fetch_assoc();
            if(!$found){
                $message = "Debug: username not found.";
            } else {
                $message = "Debug: username found, password mismatch.";
            }
        } else {
            $message = "Invalid username or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – BrewHub</title>
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
            <h1>Welcome Back</h1>
            <?php if($message): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($message) ?>
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
                <button type="submit" name="login" class="btn btn-primary">Sign In</button>
            </form>
            <div class="auth-links">
                <p class="mb-0 mt-3">Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
