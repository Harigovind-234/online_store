<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Cart.php";

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }

    // Check if this is a POST request with payment data
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    // Get payment data from POST
    $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
    $razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
    $razorpay_signature = $_POST['razorpay_signature'] ?? '';

    if (empty($razorpay_payment_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Payment ID missing']);
        exit;
    }

    // Initialize database and cart
    $db = new Database();
    $cart = new Cart($db);
    $user_id = $_SESSION['user_id'];

    // Get cart items and calculate total
    $items = $cart->getCartItems($user_id);
    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }

    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Start database transaction
    $db->conn->begin_transaction();

    try {
        // Insert orders for each cart item
        $order_ids = [];
        $stmt = $db->conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_amount, razorpay_payment_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'Paid', NOW())");
        
        foreach ($items as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $stmt->bind_param("iidds", 
                $user_id, 
                $item['product_id'], 
                $item['quantity'], 
                $item_total, 
                $razorpay_payment_id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert order: " . $stmt->error);
            }
            
            $order_ids[] = $db->conn->insert_id;
        }

        // Clear the cart after successful order creation
        $cart->clearCart($user_id);

        // Commit transaction
        $db->conn->commit();

        // Return success response
        echo json_encode([
            'status' => 'success', 
            'message' => 'Payment verified and order created successfully',
            'order_id' => $order_ids[0], // Return first order ID for redirect
            'total_orders' => count($order_ids)
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Payment verification failed: ' . $e->getMessage()
    ]);
}