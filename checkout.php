<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Cart.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$db = new Database();
$cart = new Cart($db);
$total = $cart->getTotalAmount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout – BrewHub</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
<h1>Checkout</h1>
<p>Total Amount: ₹<?= $total ?></p>
<button id="pay-btn">Pay with Razorpay</button>

<script>
var options = {
    "key": "YOUR_RAZORPAY_KEY_ID",
    "amount": <?= $total*100 ?>,
    "currency": "INR",
    "name": "BrewHub",
    "description": "Coffee Purchase",
    "handler": function (response){
        $.post("verify_payment.php", {payment_id: response.razorpay_payment_id}, function(data){
            alert(data.message);
            window.location.href = "index.php";
        });
    }
};
document.getElementById('pay-btn').onclick = function(e){
    var rzp = new Razorpay(options);
    rzp.open();
    e.preventDefault();
}
</script>
</body>
</html>
