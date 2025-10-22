<?php
session_start();
require_once "Database.php";
require_once __DIR__ . "/razorpay-php/Razorpay.php";

use Razorpay\Api\Api;

// Razorpay API keys
$keyId = "rzp_test_rCNFk3kITtnNBO";
$keySecret = "y7Rv1n26su7rNAZ5LeHidSKw";

$api = new Api($keyId, $keySecret);

// --- Check session ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['razorpay_payment_id'])) {
    die("Invalid payment response.");
}

$razorpay_payment_id = $_POST['razorpay_payment_id'];
$user_id = $_SESSION['user_id'];
$total = $_SESSION['total'];
$cart_items = $_SESSION['cart_items']; // store ['product_id', 'quantity']

$db = new Database();

try {
    // ✅ Verify payment status
    $payment = $api->payment->fetch($razorpay_payment_id);
    if ($payment->status === 'captured') {

        // ✅ Insert into orders table
        foreach ($cart_items as $item) {
            $stmt = $db->conn->prepare("
                INSERT INTO orders (user_id, product_id, quantity, total_amount, razorpay_payment_id, status)
                VALUES (?, ?, ?, ?, ?, 'Paid')
            ");
            $stmt->bind_param("iiids", $user_id, $item['product_id'], $item['quantity'], $total, $razorpay_payment_id);
            $stmt->execute();
        }

        // ✅ Clear user's cart
        $clear = $db->conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear->bind_param("i", $user_id);
        $clear->execute();

        // ✅ Success message
        echo "<script>
            alert('Payment successful! Your order has been placed.');
            window.location.href = 'order_success.php';
        </script>";
    } else {
        echo "<script>
            alert('Payment verification failed.');
            window.location.href = 'checkout.php';
        </script>";
    }

} catch (Exception $e) {
    echo "Error verifying payment: " . $e->getMessage();
}
?>
