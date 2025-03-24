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

// Fetch user profile details
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user_data = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Update the profile
    $update_query = "UPDATE users SET name = '$name', email = '$email', phone = '$phone' WHERE id = $user_id";
    if ($conn->query($update_query) === TRUE) {
        echo "Profile updated successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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
        form {
            background-color: white;
            padding: 20px;
            width: 50%;
            margin: 20px auto;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        label, input {
            display: block;
            margin: 10px auto;
            width: 80%;
            padding: 8px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            width: 50%;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
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
        <h1>Your Profile</h1>
    </header>
    <form method="POST" action="profile.php">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $user_data['name']; ?>" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $user_data['email']; ?>" required><br><br>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo $user_data['phone']; ?>" required><br><br>

        <input type="submit" value="Update Profile">
    </form>
    <nav>
        <a href="customer_dashboard.php">Back to Dashboard</a>
    </nav>
</body>
</html>
