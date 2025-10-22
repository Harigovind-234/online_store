<?php
session_start();
require_once __DIR__ . "/Database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo "<h3>Invalid order.</h3>";
    exit;
}

$db = new Database();
$conn = $db->conn;

// Fetch order details and items
$stmt = $conn->prepare("
    SELECT o.*, p.name, p.image 
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<div class='alert alert-danger text-center'>Order not found or access denied.</div>";
    exit;
}

// Fetch all orders with the same payment ID (for multi-item orders)
$items_stmt = $conn->prepare("
    SELECT o.quantity, o.total_amount, p.name, p.image, p.price
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.razorpay_payment_id = ? AND o.user_id = ?
");
$items_stmt->bind_param("si", $order['razorpay_payment_id'], $_SESSION['user_id']);
$items_stmt->execute();
$order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total amount for all items in this payment
$total_amount = 0;
foreach ($order_items as $item) {
    $total_amount += $item['total_amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success – BrewHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f7f5f2; font-family: 'Poppins', sans-serif; }
        .success-card { max-width: 700px; margin: 60px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 30px; }
        .success-title { font-size: 1.8rem; color: #2ecc71; font-weight: 600; text-align: center; margin-bottom: 20px; }
        .item-row { display: flex; align-items: center; justify-content: space-between; margin: 10px 0; }
        .item-row img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 10px; }
        .item-info { flex: 1; }
    </style>
</head>
<body>
<div class="success-card">
    <h2 class="success-title">✅ Payment Successful!</h2>
    <p class="text-center text-muted">Thank you for your order. Below are your order details.</p>

    <h5 class="mt-4">Order #<?= htmlspecialchars($order_id) ?></h5>
    <p><strong>Payment ID:</strong> <?= htmlspecialchars($order['razorpay_payment_id']) ?></p>
    <p><strong>Total:</strong> ₹<?= number_format($total_amount, 2) ?></p>
    <p><strong>Status:</strong> <span class="badge bg-success"><?= htmlspecialchars($order['status']) ?></span></p>
    <p><strong>Order Date:</strong> <?= date("d M Y, h:i A", strtotime($order['created_at'])) ?></p>

    <div class="border-top my-3"></div>
    <h6>Order Items:</h6>

    <?php foreach ($order_items as $item): ?>
        <div class="item-row">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <div class="item-info">
                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                <small class="text-muted">Quantity: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></small>
            </div>
            <span>₹<?= number_format($item['total_amount'], 2) ?></span>
        </div>
    <?php endforeach; ?>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
    </div>
</div>
</body>
</html>
