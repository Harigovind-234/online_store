<?php
class User {
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    // Check if username or email is already taken
    public function isAvailable($username, $email){
        $stmt = $this->db->conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows === 0; // true if available
    }

    // Register a new user
    public function register($username, $password, $email, $phone = null){
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->conn->prepare("INSERT INTO users (username, password, email, phone, is_approved, created_at) VALUES (?,?,?,?,0,NOW())");
        $stmt->bind_param("ssss", $username, $hashed, $email, $phone);
        return $stmt->execute();
    }

    // Optional: login method
    public function login($username, $password){
        $stmt = $this->db->conn->prepare("SELECT id, password, is_approved FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows === 1){
            $user = $res->fetch_assoc();
            if(password_verify($password, $user['password'])){
                if($user['is_approved'] == 1){
                    return $user;
                }
            }
        }
        return false;
    }

    // Fetch all users (for admin panel)
    public function getAllUsers(){
        $result = $this->db->conn->query("SELECT * FROM users ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Approve a user
    public function approve($id){
        $stmt = $this->db->conn->prepare("UPDATE users SET is_approved=1 WHERE id=?");
        $stmt->bind_param("i",$id);
        return $stmt->execute();
    }

    // Reject (delete) a user
    public function reject($id){
        $stmt = $this->db->conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i",$id);
        return $stmt->execute();
    }
}
