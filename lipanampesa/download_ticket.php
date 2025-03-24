<?php
// Database configuration
$dbHost = 'localhost';
$dbName = 'laundry_management_system';
$dbUser = 'root';
$dbPassword = '';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the latest laundry order
    $orderStmt = $pdo->query("
        SELECT o.*, u.name, u.phone 
        FROM orders o
        JOIN users u ON o.customer_id = u.id
        ORDER BY o.id DESC 
        LIMIT 1
    ");
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    // Fetch the latest payment details from transactiontb table
    $payStmt = $pdo->query("SELECT * FROM transactiontb ORDER BY id DESC LIMIT 1");
    $payment = $payStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Order Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
        }
        .receipt-container {
            background-color: #fff;
            padding: 20px;
            width: 50%;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .details-table th {
            background-color: #f2f2f2;
            width: 40%;
        }
        .btn {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
<header style="display: flex; justify-content: space-between; align-items: center; background-color: #333; color: white; padding: 15px;">
    <h1 style="margin: 0; font-size: 22px; color: white;">Laundry Management System</h1>

    <div>
        <a href="/laundry_management/profile.php" style="text-decoration: none;">
            <button style="background-color: blue; color: white; border: none; padding: 8px 12px; border-radius: 5px; font-size: 14px; cursor: pointer; margin-right: 10px;">
                My Profile
            </button>
        </a>
        <a href="/laundry_management/view_orders.php" style="text-decoration: none;">
            <button style="background-color: blue; color: white; border: none; padding: 8px 12px; border-radius: 5px; font-size: 14px; cursor: pointer; margin-right: 10px;">
                View Orders
            </button>
        </a>
        <form method="POST" style="display: inline;">
            <button type="submit" name="logout" style="background-color: blue; color: white; border: none; padding: 8px 12px; border-radius: 5px; font-size: 14px; cursor: pointer;">
                Logout
            </button>
        </form>
    </div>
</header>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_start();
    session_destroy();
    header("Location: /laundry_management/index.php");
    exit();
}
?>

<div class="receipt-container" id="receipt">
    <h2>Order Receipt</h2>
    
    <?php if ($order): ?>
        <h3>Order Details</h3>
        <table class="details-table">
            <tr><th>Customer Name</th><td><?php echo htmlspecialchars($order['name']); ?></td></tr>
            <tr><th>Phone</th><td><?php echo htmlspecialchars($order['phone']); ?></td></tr>
            <tr><th>Service Type</th><td><?php echo htmlspecialchars($order['service_type']); ?></td></tr>
            <tr><th>Total Price</th><td>Ksh <?php echo htmlspecialchars($order['total_price']); ?></td></tr>
            <tr><th>Order Status</th><td><?php echo htmlspecialchars($order['status']); ?></td></tr>
            <tr><th>Pickup Date</th><td><?php echo htmlspecialchars($order['pickup_date']); ?></td></tr>
            <tr><th>Address</th><td><?php echo htmlspecialchars($order['address']); ?></td></tr>
        </table>
    <?php else: ?>
        <p>No order details found.</p>
    <?php endif; ?>

    <h3>Payment Details</h3>
    <table class="details-table">
        <tr><th>Transaction ID</th><td><?php echo $payment ? htmlspecialchars($payment['id']) : 'Pending'; ?></td></tr>
        <tr><th>Amount Paid</th><td><?php echo $payment ? 'Ksh ' . htmlspecialchars($payment['total_price']) : 'Pending'; ?></td></tr>
        <tr><th>Payment Date</th><td><?php echo $payment ? htmlspecialchars($payment['createdAt']) : 'Pending'; ?></td></tr>
        <tr><th>Phone Number</th><td><?php echo $payment ? htmlspecialchars($payment['phone']) : 'Pending'; ?></td></tr>
        <tr><th>Payment Status</th><td><?php echo $payment && $payment['transactionStatus'] === 'Completed' ? 'Confirmed' : 'Pending'; ?></td></tr>
    </table>

    <button class="btn" onclick="downloadReceipt()">Download Receipt</button>
</div>

<script>
function downloadReceipt() {
    let printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Order Receipt</title></head><body>');
    printWindow.document.write(document.getElementById('receipt').innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>

<footer style="background: #333; color: white; text-align: center; padding: 15px; position: relative; width: 100%; bottom: 0; margin-top: auto;">
    <p>&copy; <?php echo date("Y"); ?> Laundry Management System. All rights reserved.</p>
</footer>

</body>
</html>
