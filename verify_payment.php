<?php
header('Content-Type: application/json');

// Normally verify with Razorpay API using Key Secret
// For demo, we just return success
echo json_encode(['status'=>'success','message'=>'Payment successful!']);
?>
