<?php
session_start();
require_once __DIR__ . "/Database.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$user_id = $_SESSION['user_id'];

// Fetch user's orders with product details
$stmt = $db->conn->prepare("
    SELECT 
        o.id,
        o.quantity,
        o.total_amount,
        o.razorpay_payment_id,
        o.status,
        o.created_at,
        p.name AS product_name,
        p.description,
        p.image,
        p.price
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Group orders by payment ID for better display
$grouped_orders = [];
foreach ($orders as $order) {
    $payment_id = $order['razorpay_payment_id'];
    if (!isset($grouped_orders[$payment_id])) {
        $grouped_orders[$payment_id] = [
            'payment_id' => $payment_id,
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'total_amount' => 0,
            'items' => []
        ];
    }
    $grouped_orders[$payment_id]['total_amount'] += $order['total_amount'];
    $grouped_orders[$payment_id]['items'][] = $order;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - BrewHub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        body {
            background-color: var(--background-color);
            font-family: 'Poppins', sans-serif;
        }
        
        .orders-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .order-header {
            background: linear-gradient(135deg, var(--primary-color), #8b5a3c);
            color: white;
            padding: 1.5rem;
        }
        
        .order-body {
            padding: 1.5rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 1rem;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .item-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .item-quantity {
            color: #888;
            font-size: 0.9rem;
        }
        
        .item-price {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .empty-orders {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-orders i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .back-btn {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            transition: background-color 0.2s;
        }
        
        .back-btn:hover {
            background-color: #5a3e2a;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coffee me-2"></i>BrewHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart_page.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_orders.php">
                            <i class="fas fa-box"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="orders-container">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        
        <h1 class="page-title">My Orders</h1>
        
        <?php if (empty($grouped_orders)): ?>
            <div class="empty-orders">
                <i class="fas fa-box-open"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="index.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($grouped_orders as $group): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-1">
                                    <i class="fas fa-receipt me-2"></i>
                                    Payment ID: <?= substr($group['payment_id'], -12) ?>
                                </h5>
                                <small>Ordered on <?= date("d M Y, h:i A", strtotime($group['created_at'])) ?></small>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="status-badge <?= $group['status'] === 'Paid' ? 'status-paid' : 'status-pending' ?>">
                                    <?= htmlspecialchars($group['status']) ?>
                                </div>
                                <div class="mt-2">
                                    <strong>Total: ₹<?= number_format($group['total_amount'], 2) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <?php foreach ($group['items'] as $item): ?>
                            <div class="order-item">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                <div class="item-details">
                                    <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div class="item-description"><?= htmlspecialchars($item['description']) ?></div>
                                    <div class="item-quantity">
                                        Quantity: <?= $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?>
                                    </div>
                                </div>
                                <div class="item-price">
                                    ₹<?= number_format($item['total_amount'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
