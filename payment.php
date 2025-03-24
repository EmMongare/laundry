<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    echo "Invalid order.";
    exit();
}

$order_id = $_GET['order_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
</head>
<body>
    <h2>Processing Payment</h2>
    <p>Order ID: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>
    <p>Payment integration (M-Pesa STK push) coming soon...</p>

    <a href="customer_dashboard.php">Back to Dashboard</a>
</body>
</html>
