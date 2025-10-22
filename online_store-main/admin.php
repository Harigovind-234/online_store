<?php
session_start();
require_once "Database.php";
require_once "Product.php";

// --- Only allow admin ---
if(!isset($_SESSION['username']) || $_SESSION['username'] != 'admin'){
    header("Location: login.php");
    exit;
}

// --- Initialize database and product object ---
$db = new Database();
$productObj = new Product($db);
$message = "";

// --- Handle product addition ---
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
        $allowed = ['jpg','jpeg','png','webp'];
        if(!in_array(strtolower($ext), $allowed)){
            $errorDetail = "Invalid image type.";
        } else {
            $filename = uniqid('img_', true) . '.' . $ext;
            $targetFile = $uploadDir . $filename;
            if(move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            } else {
                $errorDetail = 'Image upload failed.';
            }
        }
    } else if (!empty($_POST['image'])) {
        $imagePath = trim($_POST['image']); // fallback to URL
    }

    if(empty($imagePath)){
        $message = "Failed to add product. Image is required. $errorDetail";
    } else {
        $stmt = $db->conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $description, $price, $imagePath);
        if($stmt->execute()){
            $message = "Product added successfully!";
        } else {
            $message = "Failed to add product. SQL Error: " . htmlspecialchars($stmt->error);
        }
    }
}

// --- Handle product deletion ---
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $db->conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

// --- Handle user approve/reject ---
if(isset($_GET['approve'])){
    $userId = intval($_GET['approve']);
    $stmt = $db->conn->prepare("UPDATE users SET is_approved=1 WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $_SESSION['message'] = "User ID $userId approved successfully.";
    header("Location: admin.php");
    exit;
}

if(isset($_GET['reject'])){
    $userId = intval($_GET['reject']);
    $stmt = $db->conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $_SESSION['message'] = "User ID $userId rejected successfully.";
    header("Location: admin.php");
    exit;
}

// --- Fetch products ---
$products = $productObj->getAllProducts();

// --- Fetch users ---
$users = [];
$result = $db->conn->query("SELECT id, username, email, created_at, is_approved FROM users ORDER BY created_at DESC");
if($result){
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

// --- Dashboard metrics ---
// Calculate actual total sales from paid orders
$salesQuery = "SELECT SUM(total_amount) as total_sales FROM orders WHERE status = 'Paid'";
$salesResult = $db->conn->query($salesQuery);
$totalSales = 0;
if ($salesResult && $salesResult->num_rows > 0) {
    $salesRow = $salesResult->fetch_assoc();
    $totalSales = $salesRow['total_sales'] ?? 0;
}

// Calculate pending orders count
$pendingQuery = "SELECT COUNT(DISTINCT razorpay_payment_id) as pending_count FROM orders WHERE status = 'Pending'";
$pendingResult = $db->conn->query($pendingQuery);
$pendingOrders = 0;
if ($pendingResult && $pendingResult->num_rows > 0) {
    $pendingRow = $pendingResult->fetch_assoc();
    $pendingOrders = $pendingRow['pending_count'] ?? 0;
}

$totalProducts = count($products);
$totalUsers = count($users);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – BrewHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f9f5f1; }
.navbar { background: #6f4e37; }
.navbar-brand, .nav-link, .navbar-text { color: #fff !important; }
.admin-container { max-width: 1100px; margin: 2rem auto; }
.card { border-radius: 15px; box-shadow: 0 2px 8px rgba(121, 74, 74, 0.07); }
.table thead { background: #6f4e37; color: #fff; }
.table img { border-radius: 8px; }
.form-label { font-weight: 500; }
.btn-primary { background: #6f4e37; border: none; }
.btn-primary:hover { background: #543826; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">BrewHub Admin</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="admin-container">

<!-- Dashboard Metrics -->
<div class="row mb-4 g-3">
    <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">Total Sales</h6><h2 class="text-success">₹<?= number_format($totalSales) ?></h2></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">Pending Orders</h6><h2 class="text-warning"><?= $pendingOrders ?></h2></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">Total Products</h6><h2 class="text-primary"><?= $totalProducts ?></h2></div></div>
    <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">Registered Users</h6><h2 class="text-info"><?= $totalUsers ?></h2></div></div>
</div>

<!-- User Registrations -->
<div class="card p-4 mt-4">
    <h3 class="mb-3">User Registrations</h3>
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr><th>ID</th><th>Username</th><th>Email</th><th>Registered At</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= $user['created_at'] ?></td>
                    <td><?= $user['is_approved'] ? '<span class="text-success">Approved</span>' : '<span class="text-warning">Pending</span>' ?></td>
                    <td>
                        <?php if(!$user['is_approved']): ?>
                            <a href="admin.php?approve=<?= $user['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="admin.php?reject=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject this user?')">Reject</a>
                        <?php else: ?>
                            <span class="text-muted">No actions</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add New Product -->
<div class="row g-4 mt-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h3 class="mb-3">Add New Product</h3>
            <?php if($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3"><label class="form-label">Product Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2" required></textarea></div>
                <div class="mb-3"><label class="form-label">Price (₹)</label><input type="number" name="price" class="form-control" step="0.01" min="0" required></div>
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

    <!-- Existing Products -->
    <div class="col-lg-7">
        <div class="card p-4">
            <h3 class="mb-3">Existing Products</h3>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Image</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['description']) ?></td>
                            <td>₹<?= number_format($p['price'],2) ?></td>
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

<!-- Orders Section -->
<!-- Orders Section -->
<div class="card p-4 mt-5">
    <h3 class="mb-3">Order Details</h3>
    <div class="table-responsive">
        <table class="table align-middle table-bordered">
            <thead class="table-dark text-center">
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Amount</th>
                    <th>Payment ID</th>
                    <th>Status</th>
                    <th>Ordered At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once "Database.php";
                $db = new Database();

                // Fetch all paid orders of approved users
                $orderQuery = "
                    SELECT 
                        o.id,
                        u.username,
                        p.name AS product_name,
                        o.quantity,
                        o.total_amount,
                        o.razorpay_payment_id,
                        o.status,
                        o.created_at
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    JOIN products p ON o.product_id = p.id
                    WHERE u.is_approved = 1
                    ORDER BY o.created_at DESC
                ";

                $orderResult = $db->conn->query($orderQuery);

                if ($orderResult && $orderResult->num_rows > 0):
                    while ($order = $orderResult->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                    <td class="text-center"><?= $order['quantity'] ?></td>
                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($order['razorpay_payment_id']) ?></td>
                    <td>
                        <?php if ($order['status'] === 'Paid'): ?>
                            <span class="badge bg-success">Paid</span>
                        <?php elseif ($order['status'] === 'Pending'): ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($order['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= date("d M Y, h:i A", strtotime($order['created_at'])) ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">No orders found yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>