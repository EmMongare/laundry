<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
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

// Fetch system summary
$user_query = "SELECT COUNT(*) AS total_users FROM users";
$order_query = "SELECT COUNT(*) AS total_orders, 
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders, 
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_orders
                FROM orders";
$revenue_query = "SELECT SUM(amount) AS total_revenue FROM payments";

$user_result = $conn->query($user_query);
$order_result = $conn->query($order_query);
$revenue_result = $conn->query($revenue_query);

if (!$user_result || !$order_result || !$revenue_result) {
    die("Error fetching data: " . $conn->error);
}

$user_data = $user_result->fetch_assoc();
$order_data = $order_result->fetch_assoc();
$revenue_data = $revenue_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f9; }
        header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        nav { text-align: center; background-color: #444; padding: 10px; }
        nav a { color: white; text-decoration: none; margin: 0 15px; padding: 8px 16px; font-size: 18px; }
        nav a:hover { background-color: #ddd; color: black; }
        .active { background-color: #666; color: white; }
        .dashboard { max-width: 800px; margin: auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        p { font-size: 18px; margin: 10px 0; }
    </style>
</head>
<body>
<header>
<h1>Welcome, <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : "Admin"; ?>!</h1>
</header>
<nav>
    <a href="manage_users.php" class="active">Manage Users</a>
    <a href="manage_staff.php">Manage Staff</a>
    <a href="manage_orders.php">Manage Orders</a>
    <a href="manage_payments.php">Payments</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
</nav>
<div class="dashboard">
    <div class="card">
        <h2>System Summary</h2>
        <p><strong>Total Users:</strong> <?php echo $user_data['total_users']; ?></p>
        <p><strong>Total Orders:</strong> <?php echo $order_data['total_orders']; ?></p>
        <p><strong>Pending Orders:</strong> <?php echo $order_data['pending_orders']; ?></p>
        <p><strong>Completed Orders:</strong> <?php echo $order_data['completed_orders']; ?></p>
        <p><strong>Total Revenue:</strong> Ksh <?php echo number_format($revenue_data['total_revenue'], 2); ?></p>
    </div>
</div>
</body>
</html>
