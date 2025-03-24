<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "laundry_management_system"; // ✅ Updated database name

$conn = new mysqli($servername, $username, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['order_id'])) {
    die("Order ID is missing. Please place an order first.");
}

$order_id = $_SESSION['order_id'];

// Fetch customer_id from orders table
$query = "SELECT customer_id, total_price FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

$customer_id = $order['customer_id'];
$amount = $order['total_price']; // ✅ Using total_price as the amount

// Fetch phone number from users table
$query = "SELECT phone FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

$phone_number = $user['phone'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STK Push Mpesa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet"/>
</head>
<body>

    <header>
        <nav class="navbar bg-success">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h3 text-white">Laundry Management System - Mpesa STK Push</span>
            </div>
        </nav>
    </header>

    <main>
        <div class="card">
            <div class="center">
                <img src="mpesa-logo.png" class="card-img-top" alt="M-Pesa Logo">
            </div>  
            <div class="card-body">
                <form method="POST" action="stk_push.php"> 
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>"> <!-- ✅ Pass order_id -->
                    <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer_id); ?>">  <!-- ✅ Pass customer_id -->

                    <!-- Phone Number Field -->
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Mpesa Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" required>
                        <div id="phoneHelp" class="form-text">Mpesa Number <b>MUST</b> start with '254'</div>
                    </div>

                    <!-- Amount Field -->
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" readonly>
                        <div id="amountHelp" class="form-text">Amount to Send in Kenya Shillings (KSh).</div>
                    </div>
                    
                    <div class="center">
                        <button type="submit" class="btn btn-success" name="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>  
    </main>

</body>
</html>  
