<?php
session_start();
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Product.php";

$db = new Database();
$productObj = new Product($db);
$products = $productObj->getAllProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrewHub - Your Premium Coffee Destination</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart_page.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <span class="badge bg-light text-dark ms-1" id="header-cart-count">0</span>
                        </a>
                    </li>
                    <?php if(isset($_SESSION['username'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my_orders.php">
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
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero text-center">
        <div class="container">
            <h1>Welcome to BrewHub</h1>
            <p class="lead">Discover our premium selection of coffee and brewing equipment</p>
        </div>
    </section>

    <!-- Products Section -->
    <section class="featured-products">
        <div class="container">
            <h2 class="text-center">Our Featured Products</h2>
            <div class="row g-4">
                <?php foreach($products as $product): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 product-col">
                    <div class="card product-card">
                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                            <div class="product-footer">
                                <div class="product-price-section">
                                    <span class="product-price">₹<?= number_format($product['price'], 2) ?></span>
                                </div>
                                <div class="product-button-section">
                                    <button class="btn add-to-cart" data-id="<?= $product['id'] ?>">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <div class="cart-container">
                <h2 class="cart-title">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Ready to Brew?
                </h2>
                <p class="cart-subtitle">
                    Your perfect coffee experience is just a click away. View your cart and complete your order to start enjoying premium coffee at home.
                </p>
                <a href="cart_page.php" class="cart-button">
                    <i class="fas fa-shopping-bag"></i>
                    View My Cart
                </a>
                <div class="cart-stats">
                    <div class="cart-stat">
                        <span class="cart-stat-number" id="cart-items-count">0</span>
                        <span class="cart-stat-label">Items in Cart</span>
                    </div>
                    <div class="cart-stat">
                        <span class="cart-stat-number">24/7</span>
                        <span class="cart-stat-label">Support</span>
                    </div>
                    <div class="cart-stat">
                        <span class="cart-stat-number">Free</span>
                        <span class="cart-stat-label">Delivery</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-coffee me-2"></i>BrewHub</h5>
                    <p>Your premium coffee destination</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 BrewHub. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Function to update cart count
        function updateCartCount() {
            $.get("get_cart_count.php", function(data) {
                const count = data.count || 0;
                $("#cart-items-count").text(count);
                $("#header-cart-count").text(count);
                
                // Hide badge if count is 0
                if (count === 0) {
                    $("#header-cart-count").hide();
                } else {
                    $("#header-cart-count").show();
                }
            }, "json").fail(function() {
                $("#cart-items-count").text("0");
                $("#header-cart-count").text("0").hide();
            });
        }
        
        // Load cart count on page load
        updateCartCount();
        
        $(".add-to-cart").click(function(){
            var button = $(this);
            var product_id = button.data("id");
            
            // Disable button while processing
            button.prop('disabled', true);
            
            $.post("cart_action.php", {
                product_id: product_id,
                action: 'add'
            }, function(response){
                var res = JSON.parse(response);
                
                // Show Bootstrap toast or alert
                var alertHtml = '<div class="alert alert-' + 
                    (res.success ? 'success' : 'danger') + 
                    ' alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert">' +
                    res.message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                
                // Remove any existing alerts
                $('.alert').remove();
                
                // Add new alert
                $('body').append(alertHtml);
                
                // Update cart count if item was added successfully
                if (res.success) {
                    updateCartCount();
                }
                
                // Enable button
                button.prop('disabled', false);
                
                // Auto dismiss after 3 seconds
                setTimeout(function() {
                    $('.alert').fadeOut();
                }, 3000);
            });
        });
    });
    </script>
</body>
</html>
