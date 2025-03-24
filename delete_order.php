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

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_orders.php?error=Invalid order ID");
    exit();
}
$order_id = $_GET['id'];

// Delete order from database
$delete_query = "DELETE FROM orders WHERE id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    header("Location: manage_orders.php?success=Order deleted successfully");
} else {
    header("Location: manage_orders.php?error=Failed to delete order");
}
$stmt->close();
$conn->close();
exit();
?>
