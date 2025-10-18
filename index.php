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
                        </a>
                    </li>
                    <?php if(isset($_SESSION['username'])): ?>
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
    <section class="container py-5">
        <h2 class="text-center mb-4">Our Featured Products</h2>
        <div class="row g-4">
            <?php foreach($products as $product): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card product-card">
                    <img src="<?= htmlspecialchars($product['image']) ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="product-price">₹<?= number_format($product['price'], 2) ?></span>
                            <button class="btn btn-primary btn-sm add-to-cart" data-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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
