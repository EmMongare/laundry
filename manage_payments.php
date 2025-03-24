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

// Fetch all payments
$payment_query = "SELECT payments.id, users.name AS customer_name, orders.id AS order_id, payments.amount, payments.payment_status, payments.payment_date 
                  FROM payments 
                  JOIN users ON payments.customer_id = users.id 
                  JOIN orders ON payments.order_id = orders.id 
                  ORDER BY payments.payment_date DESC";
$payment_result = $conn->query($payment_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f9; }
        header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .container { max-width: 900px; margin: auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #444; color: white; }
        .action-btn { padding: 5px 10px; text-decoration: none; margin: 5px; display: inline-block; }
        .update { background-color: #f39c12; color: white; }
        .delete { background-color: #e74c3c; color: white; }
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
    <h1>Manage Payments</h1>
</header>
<div class="container">
    <table>
        <tr>
            <th>Payment ID</th>
            <th>Customer</th>
            <th>Order ID</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Payment Date</th>
            <th>Actions</th>
        </tr>
        <?php while ($payment = $payment_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $payment['id']; ?></td>
                <td><?php echo $payment['customer_name']; ?></td>
                <td><?php echo $payment['order_id']; ?></td>
                <td><?php echo number_format($payment['amount'], 2); ?></td>
                <td><?php echo ucfirst($payment['payment_status']); ?></td>
                <td><?php echo $payment['payment_date']; ?></td>
                <td>
                    <a href="update_payment.php?id=<?php echo $payment['id']; ?>" class="action-btn update">Update</a>
                    <a href="delete_payment.php?id=<?php echo $payment['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <nav>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </nav>
</div>
</body>
</html>
