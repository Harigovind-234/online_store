<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Cart.php";

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['count' => 0]);
        exit;
    }

    $db = new Database();
    $cart = new Cart($db);
    $user_id = $_SESSION['user_id'];
    
    $items = $cart->getCartItems($user_id);
    $count = 0;
    
    foreach ($items as $item) {
        $count += $item['quantity'];
    }
    
    echo json_encode(['count' => $count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
}
?>
