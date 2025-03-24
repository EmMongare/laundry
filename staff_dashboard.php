<?php
session_start();

// Check if the user is logged in and is a staff member
if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
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

// Fetch orders assigned to staff
$order_query = "SELECT orders.id, users.name AS customer_name, orders.service_type, orders.status, orders.created_at 
                FROM orders 
                JOIN users ON orders.customer_id = users.id 
                ORDER BY orders.created_at DESC";
$order_result = $conn->query($order_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        header { background-color: #007bff; color: white; padding: 20px; text-align: center; position: relative; }
        .container { max-width: 900px; margin: auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #444; color: white; }
        .update { background-color: #f39c12; color: white; padding: 5px 10px; text-decoration: none; display: inline-block; }
        .logout { position: absolute; top: 15px; right: 20px; background-color: red; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .logout:hover { background-color: darkred; }
    </style>
</head>
<body>
<header>
    <h1>Staff Dashboard</h1>
    <a href="logout.php" class="logout">Logout</a>
</header>
<div class="container">
    <h2>Manage Orders</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Service Type</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
        <?php while ($order = $order_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['customer_name']; ?></td>
                <td><?php echo $order['service_type']; ?></td>
                <td><?php echo ucfirst($order['status']); ?></td>
                <td><?php echo $order['created_at']; ?></td>
                <td>
                    <a href="update_order.php?id=<?php echo $order['id']; ?>" class="update">Update Status</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
