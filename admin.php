<?php
session_start();
require_once "Database.php";
require_once "Product.php";

// Simulate admin login (or check session)

// Only allow admin
if(!isset($_SESSION['username']) || $_SESSION['username'] != 'admin'){
    header("Location: login.php");
    exit;
}


$db = new Database();
$productObj = new Product($db);
$message = "";

// Add product with image upload
if(isset($_POST['add_product'])){
    $name = trim($_POST['name']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $price = $_POST['price'];
    $imagePath = '';
    $errorDetail = '';
    if(isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . $ext;
        $targetFile = $uploadDir . $filename;
        if(move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        } else {
            $errorDetail = 'Image upload failed.';
        }
    } else if (!empty($_POST['image'])) {
        $imagePath = trim($_POST['image']); // fallback to URL
    }
    if(empty($imagePath)) {
        $message = "Failed to add product. Image is required.";
    } else {
        $stmt = $db->conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $description, $price, $imagePath);
        if($stmt->execute()){
            $message = "Product added successfully!";
        } else {
            $message = "Failed to add product.<br>SQL Error: " . htmlspecialchars($stmt->error) . "<br>DB Error: " . htmlspecialchars($db->conn->error) . "<br>Image: " . htmlspecialchars($imagePath) . "<br>" . $errorDetail;
        }
    }
}

// Delete product
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $db->conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin.php");
}

$products = $productObj->getAllProducts();
?>

<?php
// Dashboard metrics (replace with real queries as needed)
$totalSales = 12500; // Example value
$pendingOrders = 8; // Example value
$totalProducts = count($products);
$totalUsers = 42; // Example value
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – BrewHub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f9f5f1; }
        .navbar { background: #6f4e37; }
        .navbar-brand, .nav-link, .navbar-text { color: #fff !important; }
        .admin-container { max-width: 1100px; margin: 2rem auto; }
        .card { border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .table thead { background: #6f4e37; color: #fff; }
        .table img { border-radius: 8px; }
        .form-label { font-weight: 500; }
        .btn-primary { background: #6f4e37; border: none; }
        .btn-primary:hover { background: #543826; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">BrewHub Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <!-- Dashboard Overview -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h6 class="text-muted">Total Sales</h6>
                    <h2 class="text-success">₹<?= number_format($totalSales) ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h6 class="text-muted">Pending Orders</h6>
                    <h2 class="text-warning"><?= $pendingOrders ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h6 class="text-muted">Total Products</h6>
                    <h2 class="text-primary"><?= $totalProducts ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3">
                    <h6 class="text-muted">Registered Users</h6>
                    <h2 class="text-info"><?= $totalUsers ?></h2>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <div class="card p-3">
                    <h6 class="mb-3">Sales Trends</h6>
                    <canvas id="salesChart" height="120"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <h6 class="mb-3">Popular Products</h6>
                    <canvas id="popularChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card p-4 mb-4">
                    <h3 class="mb-3">Add New Product</h3>
                    <?php if($message): ?>
                        <div class="alert alert-info" role="alert">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price (₹)</label>
                            <input type="number" name="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image Upload</label>
                            <input type="file" name="image_file" class="form-control mb-2">
                            <small class="text-muted">or paste an image URL below</small>
                            <input type="text" name="image" class="form-control mt-1" placeholder="Image URL">
                        </div>
                        <button type="submit" name="add_product" class="btn btn-primary w-100">Add Product</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card p-4">
                    <h3 class="mb-3">Existing Products</h3>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($products as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['description']) ?></td>
                                    <td>₹<?= number_format($p['price'], 2) ?></td>
                                    <td>
                                        <?php if($p['image']): ?>
                                            <img src="<?= htmlspecialchars($p['image']) ?>" width="60" style="max-height:60px;object-fit:cover;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Sales Trends Chart (sample data)
    new Chart(document.getElementById('salesChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Sales',
                data: [1200, 1900, 3000, 2500, 3200, 2800, 3500],
                borderColor: '#6f4e37',
                backgroundColor: 'rgba(111,78,55,0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });
    // Popular Products Chart (sample data)
    new Chart(document.getElementById('popularChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Espresso', 'Latte', 'French Press', 'Mug', 'Drip Kit'],
            datasets: [{
                label: 'Orders',
                data: [32, 27, 19, 15, 12],
                backgroundColor: ['#6f4e37', '#d4a574', '#c7a17a', '#a67c52', '#8d5524']
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });
    </script>
</body>
</html>
