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

// Handle adding staff
if (isset($_POST['add_staff'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO staff (name, email, phone, user_type, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $role, $password);
    $stmt->execute();
    $stmt->close();
}

// Handle updating staff
if (isset($_POST['update_staff'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['user_type'];
    
    $stmt = $conn->prepare("UPDATE staff SET name=?, email=?, phone=?, user_type=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $phone, $role, $id);
    $stmt->execute();
    $stmt->close();
}

// Handle deleting staff
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM staff WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all staff
$result = $conn->query("SELECT * FROM staff");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f9; }
        header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .container { max-width: 800px; margin: auto; padding: 20px; background: white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        button, input[type='submit'] { background-color: #28a745; color: white; border: none; padding: 10px; cursor: pointer; }
        .delete { background-color: red; }
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
    <h1>Manage Staff</h1>
</header>
<div class="container">
    <h2>Add Staff</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <input type="text" name="role" placeholder="Role" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="add_staff" value="Add Staff">
    </form>

    <h2>Staff List</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['phone']; ?></td>
            <td><?php echo $row['user_type']; ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="name" value="<?php echo $row['name']; ?>" required>
                    <input type="email" name="email" value="<?php echo $row['email']; ?>" required>
                    <input type="text" name="phone" value="<?php echo $row['phone']; ?>" required>
                    <input type="text" name="user_type" value="<?php echo $row['user_type']; ?>" required>
                    <input type="submit" name="update_staff" value="Update">
                </form>
                <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?');">
                    <button class="delete">Delete</button>
                </a>
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
<?php $conn->close(); ?>
