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

// Get user ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}
$user_id = $_GET['id'];

// Fetch user details
$user_query = "SELECT id, name, email, user_type FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: manage_users.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

    $update_query = "UPDATE users SET name = ?, email = ?, user_type = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $name, $email, $user_type, $user_id);
    if ($stmt->execute()) {
        header("Location: manage_users.php?success=User updated successfully");
        exit();
    } else {
        $error = "Failed to update user.";
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
    <title>Edit User</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .container { max-width: 500px; margin: 50px auto; padding: 20px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 8px; }
        h2 { text-align: center; }
        form { display: flex; flex-direction: column; }
        label { margin-top: 10px; font-weight: bold; }
        input, select { padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { margin-top: 15px; background: #28a745; color: white; padding: 10px; border: none; cursor: pointer; }
        .btn:hover { background: #218838; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit User</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <button type="submit" class="btn">Update User</button>
    </form>
</div>
</body>
</html>
