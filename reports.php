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

// Get filters
$from_date = $_POST['from_date'] ?? "";
$to_date = $_POST['to_date'] ?? "";
$status = $_POST['status'] ?? "";
$customer_name = $_POST['customer_name'] ?? "";

// Query with filters
$query = "SELECT orders.id, orders.status, orders.total_price, orders.created_at, users.name AS customer_name 
          FROM orders 
          JOIN users ON orders.customer_id = users.id
          WHERE 1";

if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND orders.created_at BETWEEN '$from_date' AND '$to_date'";
}
if (!empty($status)) {
    $query .= " AND orders.status = '$status'";
}
if (!empty($customer_name)) {
    $query .= " AND users.name LIKE '%$customer_name%'";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f9; }
        header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .container { max-width: 900px; margin: auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn { padding: 10px 15px; background: green; color: white; border: none; cursor: pointer; margin-right: 10px; }
        @media print { .btn { display: none; } }
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
    <h1>Reports Dashboard</h1>
</header>

<div class="container">
    <form method="post">
        <div class="form-group">
            <label>From Date:</label>
            <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>">
        </div>
        <div class="form-group">
            <label>To Date:</label>
            <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>">
        </div>
        <div class="form-group">
            <label>Status:</label>
            <select name="status">
                <option value="">All</option>
                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= $status == 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>
        <div class="form-group">
            <label>Customer Name:</label>
            <input type="text" name="customer_name" placeholder="Enter name" value="<?= htmlspecialchars($customer_name) ?>">
        </div>
        <button type="submit" class="btn">Filter</button>
        <button type="submit" name="download" class="btn">Download PDF</button>
        <button type="button" onclick="window.print()" class="btn">Print Report</button>
    </form>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Status</th>
            <th>Total Price</th>
            <th>Created At</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>Ksh <?= number_format($row['total_price'], 2) ?></td>
            <td><?= htmlspecialchars($row['created_at']) ?></td>
        </tr>
        <?php } ?>
    </table>
    <nav>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </nav>
</div>

<?php
if (isset($_POST['download'])) {
    $_SESSION['from_date'] = $from_date;
    $_SESSION['to_date'] = $to_date;
    $_SESSION['status'] = $status;
    $_SESSION['customer_name'] = $customer_name;
    header("Location: download_report.php");
    exit();
}
?>

</body>
</html>
