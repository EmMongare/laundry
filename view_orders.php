<?php
session_start();

// Check if the user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
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

$user_id = $_SESSION['user_id'];

// Fetch all orders of the logged-in user
$order_query = "SELECT * FROM orders WHERE customer_id = $user_id ORDER BY pickup_date DESC";
$order_result = $conn->query($order_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            text-align: center;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        nav {
            margin-top: 20px;
        }
        a {
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <h1>Your Orders</h1>
    </header>
    <table>
        <thead>
            <tr>
                <th>Service Type</th>
                <th>Pickup Date</th>
                <th>Status</th>
                <th>Pickup Address</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $order_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $order['service_type']; ?></td>
                    <td><?php echo $order['pickup_date']; ?></td>
                    <td><?php echo ucfirst($order['status']); ?></td>
                    <td><?php echo $order['address']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <nav>
        <a href="customer_dashboard.php">Back to Dashboard</a>
    </nav>
</body>
</html>
