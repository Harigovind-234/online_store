<?php
class User {
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    // Login function
    public function login($username, $password){
        // Prepare statement to avoid SQL injection
        $stmt = $this->db->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Check if user exists and verify password
        if($user && password_verify($password, $user['password'])){
            return $user; // Success: return user data
        }

        return false; // Fail
    }

    // Optional: Register function with automatic hashing
    public function register($username, $password){
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);
        return $stmt->execute();
    }
}
?>
