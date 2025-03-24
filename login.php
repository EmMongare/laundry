<?php
session_start();

// Database connection (internal)
$host = "localhost";  // Change if necessary
$user = "root";       // Default XAMPP MySQL user
$pass = "";           // Default XAMPP has no password
$dbname = "laundry_management_system"; // Your database name

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check in the users table (for customers)
    $query = "SELECT id, name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = "customer"; // Identifies the user as a customer
            header("Location: customer_dashboard.php");
            exit();
        }
    }

    // Check in the staff table
    $query = "SELECT id, name, password FROM staff WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        if (password_verify($password, $staff['password'])) {
            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['user_type'] = "staff"; // Identifies the user as staff
            header("Location: staff_dashboard.php");
            exit();
        }
    }

    // Check in the admin table
    $query = "SELECT id, name, password FROM admins WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['user_type'] = "admin"; // Identifies the user as an admin
            header("Location: admin_dashboard.php");
            exit();
        }
    }

    // If no match is found, return an error
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
    exit();
}
?>
