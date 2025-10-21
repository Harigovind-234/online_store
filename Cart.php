<?php
require_once __DIR__ . "/Database.php";

class Cart {
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

   public function addToCart($user_id, $product_id, $quantity = 1) {
    $quantity = max(1, (int)$quantity);

    $sql = "INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
    
    $stmt = $this->db->conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    return $stmt->execute();
}

public function updateQuantity($user_id, $product_id, $change) {
    // Check current quantity
    $stmt = $this->db->conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $change;

        if ($new_quantity > 0) {
            // Update quantity
            $updateStmt = $this->db->conn->prepare(
                "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?"
            );
            $updateStmt->bind_param("iii", $new_quantity, $user_id, $product_id);
            return $updateStmt->execute();
        } else {
            // Remove item if quantity goes to 0 or below
            return $this->removeFromCart($user_id, $product_id);
        }
    } else {
        // If no item exists and change is positive, add it
        if ($change > 0) {
            return $this->addToCart($user_id, $product_id, $change);
        }
    }
    return false;
}

  public function getCartItems($user_id) {
    $stmt = $this->db->conn->prepare(
        "SELECT 
            p.id AS product_id,
            p.name AS product_name,
            p.description,
            p.price,
            p.image,
            SUM(c.quantity) AS quantity
         FROM cart c
         JOIN products p ON c.product_id = p.id
         WHERE c.user_id = ?
         GROUP BY p.id, p.name, p.description, p.price, p.image"
    );

    if (!$stmt) {
        die('Prepare failed: ' . $this->db->conn->error);
    }

    $stmt->bind_param('i', $user_id);
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
