<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Cart.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode(['status'=>'error','message'=>'Login required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $cart = new Cart($db);
    $user_id = $_SESSION['user_id'];

    $action = isset($_POST['action']) ? $_POST['action'] : 'add';

    if ($action === 'add' && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        if($cart->addToCart($user_id, $product_id)){
            echo json_encode(['status'=>'success','message'=>'Product added to cart']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to add product']);
        }
    } elseif ($action === 'remove' && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        // Debug output
        error_log("Remove action: user_id=$user_id, product_id=$product_id");
        if($cart->removeFromCart($user_id, $product_id)){
            // Redirect to cart_page.php after removal
            header('Location: cart_page.php');
            exit;
        } else {
            error_log("Failed to remove: user_id=$user_id, product_id=$product_id");
            echo json_encode(['status'=>'error','message'=>'Failed to remove product']);
        }
    } elseif ($action === 'clear') {
        if($cart->clearCart($user_id)){
            // Redirect to cart_page.php after clearing
            header('Location: cart_page.php');
            exit;
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to clear cart']);
        }
    } else {
        echo json_encode(['status'=>'error','message'=>'Invalid action']);
    }
}
?>
