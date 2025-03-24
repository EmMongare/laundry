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

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_users.php?error=Invalid user ID");
    exit();
}
$user_id = $_GET['id'];

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    header("Location: manage_users.php?error=You cannot delete your own account");
    exit();
}

// Delete user from database
$delete_query = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    header("Location: manage_users.php?success=User deleted successfully");
} else {
    header("Location: manage_users.php?error=Failed to delete user");
}
$stmt->close();
$conn->close();
exit();
?>
