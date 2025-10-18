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
$items = $cart->getCartItems($_SESSION['user_id']);
$total = $cart->getTotalAmount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart – BrewHub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f9f5f1; }
        .cart-container { max-width: 900px; margin: 2rem auto; }
        .table thead { background: #6f4e37; color: #fff; }
        .btn-primary { background: #6f4e37; border: none; }
        .btn-primary:hover { background: #543826; }
        .btn-danger { background: #c0392b; border: none; }
        .btn-danger:hover { background: #a93226; }
    </style>
</head>
<body>
    <div class="cart-container">
        <h2 class="mb-4 text-center">Your Cart</h2>
        <?php if(count($items) > 0): ?>
        <div class="card p-4 mb-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            <td>
                                <form method="post" action="cart_action.php" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                                <!-- Debug: product_id for this row: <?= isset($item['product_id']) ? $item['product_id'] : $item['id'] ?> -->
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total</strong></td>
                            <td colspan="2"><strong>₹<?= number_format($total, 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                <form method="post" action="cart_action.php">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-outline-danger">Cancel & Clear Cart</button>
                </form>
                <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
            </div>
        </div>
        <?php else: ?>
        <div class="card p-4 text-center">
            <h4>Your cart is empty.</h4>
            <a href="index.php" class="btn btn-primary mt-3">Go back to shop</a>
        </div>
        <?php endif; ?>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
