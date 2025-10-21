<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Cart.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$cart = new Cart($db);
$user_id = $_SESSION['user_id'];

// Fetch cart items
$items = $cart->getCartItems($user_id);
$total = 0;
$cart_data = [];

foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
    $cart_data[] = [
        'product_id' => $item['product_id'],
        'quantity' => $item['quantity']
    ];
}

// Save total and items in session for verify_payment.php
$_SESSION['total'] = $total;
$_SESSION['cart_items'] = $cart_data;

// Razorpay key
$keyId = "rzp_test_rCNFk3kITtnNBO";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout – BrewHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        body { background-color: #f6f5f3; font-family: "Poppins", sans-serif; }
        .checkout-card { max-width: 700px; margin: 60px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); padding: 30px; }
        .brand-title { font-size: 1.8rem; font-weight: 600; color: #6f4e37; text-align: center; margin-bottom: 20px; }
        .item-list { border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; margin: 20px 0; padding: 10px 0; }
        .item-row { display: flex; align-items: center; justify-content: space-between; margin: 10px 0; }
        .item-row img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 10px; }
        .item-info { flex: 1; }
        .total-amount { font-size: 1.2rem; font-weight: 600; color: #333; text-align: right; }
        #pay-btn { background-color: #8B4513; border: none; border-radius: 8px; color: white; font-weight: 600; padding: 12px 0; width: 100%; transition: 0.3s; }
        #pay-btn:hover { background-color: #704214; }
    </style>
</head>
<body>

<div class="checkout-card">
    <a href="cart_page.php" class="btn btn-dark mb-4">&larr; Back to cart</a>
    <div class="brand-title">☕ BrewHub Checkout</div>
    <h5 class="mb-3">Items in Your Cart</h5>
    <div class="item-list">
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <div class="item-row">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                    <div class="item-info">
                        <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                        <small><?= htmlspecialchars($item['description']) ?></small>
                    </div>
                    <span>₹<?= number_format($item['price'], 2) ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <div class="total-amount">
        Total: ₹<?= number_format($total, 2); ?>
    </div>

    <button id="pay-btn" class="mt-4">Pay Securely with Razorpay</button>
</div>

<div id="payment-message"></div>

<script>
var options = {
    "key": "<?= $keyId ?>",
    "amount": <?= $total * 100 ?>, // in paise
    "currency": "INR",
    "name": "BrewHub",
    "description": "Coffee Purchase",
    "handler": function (response) {
    $.post("verify_payment.php", { 
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_order_id: response.razorpay_order_id,
        razorpay_signature: response.razorpay_signature
    }, function(data) {
        console.log(data); // check JSON in console
        if (data.status === 'success') {
            window.location.href = "order_success.php?order_id=" + data.order_id;
        } else {
            alert(data.message || "Payment verification failed.");
        }
    }, "json");
},

    "theme": { "color": "#8B4513" }
};

document.getElementById('pay-btn').onclick = function(e) {
    var rzp = new Razorpay(options);
    rzp.open();
    e.preventDefault();
};
</script>

</body>
</html>