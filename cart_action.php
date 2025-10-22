<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Cart.php";

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$cart = new Cart($db);
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    switch ($action) {
        case 'add':
            $cart->addToCart($user_id, $product_id);
            break;

        case 'increase':
            $cart->updateQuantity($user_id, $product_id, 1);
            break;

        case 'decrease':
            $cart->updateQuantity($user_id, $product_id, -1);
            break;

        case 'remove':
            $cart->removeFromCart($user_id, $product_id);
            break;

        case 'clear':
            $cart->clearCart($user_id);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            exit;
    }

    // Redirect back to cart page after any action
    header("Location: cart_page.php");
    exit;
}
?>
