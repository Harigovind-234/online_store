<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Product.php";

$db = new Database();
$productObj = new Product($db);
$products = $productObj->getAllProducts();
?>
<!DOCTYPE html>
<html>
<head>
    <title>BrewHub – Coffee Store</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<h1>BrewHub – Coffee Collection</h1>
<a href="cart_page.php">View Cart</a> | 
<?php if(isset($_SESSION['username'])): ?>
    Hello, <?= $_SESSION['username'] ?> | <a href="logout.php">Logout</a>
<?php else: ?>
    <a href="login.php">Login/Register</a>
<?php endif; ?>

<div class="products">
    <?php foreach($products as $p): ?>
    <div class="product-card">
        <img src="<?= $p['image'] ?>" width="150">
        <h3><?= $p['name'] ?></h3>
        <p><?= $p['description'] ?></p>
        <p>₹<?= $p['price'] ?></p>
        <button class="add-to-cart" data-id="<?= $p['id'] ?>">Add to Cart</button>
    </div>
    <?php endforeach; ?>
</div>

<script>
$(".add-to-cart").click(function(){
    var product_id = $(this).data("id");
    $.post("cart_action.php", {product_id: product_id}, function(response){
        var res = JSON.parse(response);
        alert(res.message);
    });
});
</script>
</body>
</html>
