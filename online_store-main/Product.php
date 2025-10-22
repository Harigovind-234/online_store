<?php
require_once __DIR__ . "/Database.php";

class Product {
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    public function getAllProducts(){
        $sql = "SELECT * FROM products";
        $result = $this->db->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductById($id){
        $stmt = $this->db->conn->prepare("SELECT * FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
