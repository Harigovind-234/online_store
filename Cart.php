<?php
require_once __DIR__ . "/Database.php";

class Cart {
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    public function addToCart($user_id, $product_id, $quantity = 1){
        $stmt = $this->db->conn->prepare(
            "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity=quantity+?"
        );
        $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
        return $stmt->execute();
    }

    public function getCartItems($user_id){
        $stmt = $this->db->conn->prepare(
            "SELECT c.id, c.product_id, p.name, p.price, p.image, c.quantity 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id=?"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalAmount($user_id){
        $items = $this->getCartItems($user_id);
        $total = 0;
        foreach($items as $item){
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    // Remove a single product from cart
    public function removeFromCart($user_id, $product_id){
        $stmt = $this->db->conn->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
        $stmt->bind_param("ii", $user_id, $product_id);
        return $stmt->execute();
    }

    // Clear all products from cart
    public function clearCart($user_id){
        $stmt = $this->db->conn->prepare("DELETE FROM cart WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
}
?>
