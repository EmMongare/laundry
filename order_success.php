<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "Invalid order.";
    exit();
}

$order_id = intval($_GET['order_id']); // Ensure it's a valid number
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body>
    <h2>Order Placed Successfully!</h2>
    <p>Your Order ID: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>
    <p>Thank you for placing an order. Click the button below to proceed with payment.</p>

    <a href="lipanampesa/index.php?order_id=<?php echo $order_id; ?>"><button>Pay Now</button></a>
    <br><br>
    <a href="customer_dashboard.php">Back to Dashboard</a>
</body>
</html>
