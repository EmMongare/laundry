<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form input values
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    
    // Set user_type to "customer" (since only customers can self-register)
    $user_type = "customer";

    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        die("All fields are required!");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format!");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Database connection
    $servername = "localhost";
    $username = "root";
    $db_password = ""; // Your database password
    $dbname = "laundry_management_system";

    // Create a connection
    $conn = new mysqli($servername, $username, $db_password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        die("Email is already registered!");
    }
    $check_stmt->close();

    // Insert query
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $user_type);

    // Execute query
    if ($stmt->execute()) {
        header("Location: login.html"); // Redirect to login page
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close connection
    $stmt->close();
    $conn->close();
}
?>
