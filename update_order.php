<?php
session_start();

// Check if the user is logged in and is either admin or staff
if ((!isset($_SESSION['admin_id']) && !isset($_SESSION['staff_id'])) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'staff')) {
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

// Get order ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid order ID.");
}
$order_id = $_GET['id'];

// Fetch order details
$order_query = "SELECT id, service_type, status FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Handle form submission (Only Update Status)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $_POST['status'];

    // Ensure only status can be updated (not other order details)
    $update_query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        // Redirect back to dashboard
        if ($_SESSION['user_type'] === 'admin') {
            header("Location: manage_orders.php?success=OrderUpdatedSuccessfully");
        } else {
            header("Location: staff_dashboard.php?success=OrderUpdatedSuccessfully");
        }
        exit();
    } else {
        $error = "Failed to update order.";
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .container { max-width: 500px; margin: 50px auto; padding: 20px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 8px; }
        h2 { text-align: center; }
        form { display: flex; flex-direction: column; }
        label { margin-top: 10px; font-weight: bold; }
        select { padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { margin-top: 15px; background: #f39c12; color: white; padding: 10px; border: none; cursor: pointer; }
        .btn:hover { background: #e67e22; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>Update Order Status</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <label for="status">Order Status:</label>
        <select name="status" id="status">
            <option value="pending" <?php if ($order['status'] === 'pending') echo 'selected'; ?>>Pending</option>
            <option value="processing" <?php if ($order['status'] === 'processing') echo 'selected'; ?>>Processing</option>
            <option value="completed" <?php if ($order['status'] === 'completed') echo 'selected'; ?>>Completed</option>
            <option value="cancelled" <?php if ($order['status'] === 'cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
        <button type="submit" class="btn">Update Status</button>
    </form>
</div>
</body>
</html>
