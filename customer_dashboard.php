<?php
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header ("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "laundry_management_system";
$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch order summary
$order_query = "SELECT COUNT(*) AS total_orders, 
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders, 
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_orders
                FROM orders WHERE customer_id = $user_id";
$order_result = $conn->query($order_query);
if (!$order_result) {
    die("Error fetching order data: " . $conn->error);
}
$order_data = $order_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f9; }
        header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        nav { text-align: center; background-color: #333; padding: 10px; }
        nav a { color: white; text-decoration: none; margin: 0 15px; padding: 8px 16px; font-size: 18px; }
        nav a:hover { background-color: #ddd; color: black; }
        .active { background-color: #007bff; color: white; }
        .dashboard { max-width: 800px; margin: auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        p { font-size: 18px; margin: 10px 0; }
    </style>
</head>
<body>
<header>
<h1>Welcome, <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : "Customer"; ?>!</h1>
</header>
<nav>
    <a href="place_order.php" class="active">Place Order</a>
    <a href="view_orders.php">Track Orders</a>
    <a href="pricing.php">Pricing</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>
<div class="dashboard">
    <div class="card">
        <h2>Order Summary</h2>
        <p><strong>Total Orders:</strong> <?php echo $order_data['total_orders']; ?></p>
        <p><strong>Pending Orders:</strong> <?php echo $order_data['pending_orders']; ?></p>
        <p><strong>Completed Orders:</strong> <?php echo $order_data['completed_orders']; ?></p>
    </div>
</div>
</body>
</html>
