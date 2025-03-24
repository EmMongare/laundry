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

// Service and item pricing
$services = [
    "washing_and_drying" => [
        "Shirt/T-shirt" => 50, "Trousers/Jeans" => 80, "Dress" => 100,
        "Bedsheet (Single)" => 120, "Bedsheet (Double)" => 150, "Blanket" => 250, "Duvet" => 400
    ],
    "ironing_and_folding" => [
        "Shirt/T-shirt" => 30, "Trousers/Jeans" => 50, "Dress" => 70,
        "Bedsheet (Single)" => 80, "Bedsheet (Double)" => 100
    ],
    "dry_cleaning" => [
        "Suit (2-piece)" => 150, "Suit (3-piece)" => 200, "Coat/Blazer" => 120,
        "Winter Jacket" => 200, "Wedding Dress" => 500
    ],
    "household_items_cleaning" => [
        "Curtains (per kg)" => 250, "Cushion Covers" => 50, "Sofa Set (per seat)" => 500,
        "Carpet (per square meter)" => 300
    ],
    "stain_removal" => [
        "Shirt/T-shirt" => 70, "Trousers/Jeans" => 100, "Dress" => 120,
        "Suit" => 180
    ],
    "eco_friendly_laundry" => [
        "Shirt/T-shirt" => 80, "Trousers/Jeans" => 100, "Dress" => 150,
        "Duvet" => 500
    ],
    "leather_suede_cleaning" => [
        "Leather Jacket" => 300, "Suede Shoes" => 250, "Leather Handbag" => 400
    ]
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_type = $_POST['service_type'] ?? '';
    $pickup_date = $_POST['pickup_date'] ?? '';
    $address = $_POST['address'] ?? '';
    $selected_items = $_POST['selected_items'] ?? [];
    $total_price = $_POST['total_price'] ?? 0;
    $user_id = $_SESSION['user_id'];

    if (!empty($service_type) && !empty($pickup_date) && !empty($address) && !empty($selected_items)) {
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, service_type, items, total_price, pickup_date, address, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $items_json = json_encode($selected_items);
        $stmt->bind_param("issdss", $user_id, $service_type, $items_json, $total_price, $pickup_date, $address);

        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id; // Get last inserted order ID
            
            // ✅ Store order_id in session after successful execution
            $_SESSION['order_id'] = $order_id;
        
            header("Location: order_success.php?order_id=" . $order_id); // Pass order_id
            exit();
        }
        
        
        $stmt->close();
    } else {
        echo "<script>alert('All fields are required, and at least one item must be selected!');</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
        text-align: center;
    }
    
    h1 {
        color: #333;
    }

    form {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        margin: 0 auto;
        text-align: left;
    }

    label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
    }

    select, input, textarea {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    textarea {
        resize: none;
    }

    #items {
        margin-top: 10px;
        padding: 10px;
        background: #f9f9f9;
        border-radius: 5px;
        max-height: 150px;
        overflow-y: auto;
    }

    p#total_cost {
        font-size: 16px;
        font-weight: bold;
        color: #0275d8;
        margin-top: 10px;
    }

    input[type="submit"] {
        background: #0275d8;
        color: #fff;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 10px;
    }

    input[type="submit"]:hover {
        background: #025aa5;
    }
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
    <script>
        let serviceItems = <?php echo json_encode($services); ?>;
        let selectedItems = {};
        let totalCost = 0;

        function updateItems() {
    let serviceType = document.getElementById("service_type").value;
    let itemsDiv = document.getElementById("items");
    itemsDiv.innerHTML = "";
    selectedItems = {};
    totalCost = 0;
    document.getElementById("total_cost").innerText = "Total Cost: Ksh 0";

    if (serviceItems[serviceType]) {
        let table = `<table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <th style="text-align: left; padding: 5px;">Select</th>
                            <th style="text-align: left; padding: 5px;">Item</th>
                            <th style="text-align: left; padding: 5px;">Price (Ksh)</th>
                        </tr>`;

        for (let [item, price] of Object.entries(serviceItems[serviceType])) {
            table += `<tr>
                        <td style="padding: 5px;">
                            <input type='checkbox' name='selected_items[${item}]' value='${price}' onchange='calculateCost(this)'>
                        </td>
                        <td style="padding: 5px;">${item}</td>
                        <td style="padding: 5px;">${price}</td>
                      </tr>`;
        }

        table += `</table>`;
        itemsDiv.innerHTML = table;
    }
}


        function calculateCost(element) {
            let item = element.name.replace('selected_items[', '').replace(']', '');
            let price = parseFloat(element.value);

            if (element.checked) {
                selectedItems[item] = price;
            } else {
                delete selectedItems[item];
            }

            totalCost = Object.values(selectedItems).reduce((sum, cost) => sum + cost, 0);
            document.getElementById("total_cost").innerText = "Total Cost: Ksh " + totalCost;
            document.getElementById("total_price").value = totalCost;
        }
    </script>
</head>
<body>
    <h1>Place a Laundry Order</h1>
    <form method="POST" action="place_order.php">
        <label for="service_type">Service Type:</label>
        <select id="service_type" name="service_type" required onchange="updateItems()">
            <option value="">Select Service</option>
            <?php foreach ($services as $key => $items) { echo "<option value='$key'>" . ucfirst(str_replace('_', ' ', $key)) . "</option>"; } ?>
        </select>

        <div id="items"></div>
        <p id="total_cost">Total Cost: Ksh 0</p>
        <input type="hidden" id="total_price" name="total_price" value="0">

        <label for="pickup_date">Pickup Date:</label>
        <input type="date" id="pickup_date" name="pickup_date" required><br><br>

        <label for="address">Pickup Address:</label>
        <textarea id="address" name="address" rows="4" required></textarea><br><br>

        <input type="submit" value="Place Order">
    </form>
</body>
</html>