<?php
// Include the Africa's Talking PHP SDK
require_once('AfricasTalkingGateway.php'); // Ensure this path is correct

// Set up your Africa's Talking API credentials
$username = "YOUR_USERNAME";  // Africa's Talking username
$apiKey   = "YOUR_API_KEY";   // Africa's Talking API key

// Initialize the Africa's Talking Gateway
$gateway = new AfricasTalkingGateway($username, $apiKey);

// USSD Request Variables
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"]; // User's input

// Database Connection (Replace with Render credentials)
$host = "your-render-host";
$dbname = "your-db-name";
$username = "your-db-username";
$password = "your-db-password";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "END Service unavailable. Please try again later.";
    exit();
}

// Simulate a database connection (Replace with actual queries)
$users = [
    "254740844704" => ["name" => "james", "is_registered" => true],
];

// Explode user input to track session steps
$inputArray = explode("*", $text);
$level = count($inputArray);

if (!isset($users[$phoneNumber]) || !$users[$phoneNumber]['is_registered']) {
    if ($text == "") {
        $response = "CON Welcome to Laundry Services \n";
        $response .= "1. Sign Up \n";
        $response .= "2. Login";
    } elseif ($text == "1") {
        $response = "CON Enter your full name:";
    } elseif ($level == 2 && $inputArray[0] == "1") {
        $name = $inputArray[1];
        $response = "END Thank you, $name! You are now registered. Dial again to continue.";
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (name, phone, user_type, created_at) VALUES (?, ?, 'customer', NOW())");
        $stmt->execute([$name, $phoneNumber]);
    } elseif ($text == "2") {
        $response = "END Login successful. Proceed to the main menu.";
    } else {
        $response = "END Invalid choice. Please try again.";
    }
} else {
    if ($text == "") {
        $response = "CON Welcome to Laundry Services \n";
        $response .= "1. Place Order \n";
        $response .= "2. Check Order Status \n";
        $response .= "3. Cancel Order \n";
        $response .= "4. Contact Support";
    } elseif ($text == "1") {
        $response = "CON Select Service: \n";
        $response .= "1. Washing & Drying \n";
        $response .= "2. Ironing & Folding \n";
        $response .= "3. Dry Cleaning \n";
        $response .= "4. Stain Removal \n";
        $response .= "5. Eco-Friendly Laundry";
    } elseif ($level == 2 && $inputArray[0] == "1") {
        $service = $inputArray[1];
        $response = "CON Select Item: \n";
        $response .= "1. Shirt/T-shirt - Ksh 50 \n";
        $response .= "2. Trousers/Jeans - Ksh 80 \n";
        $response .= "3. Dress - Ksh 100";
    } elseif ($level == 3 && $inputArray[0] == "1") {
        $response = "CON Confirm Order: \n";
        $response .= "Total: Ksh 150 \n";
        $response .= "1. Confirm & Pay \n";
        $response .= "2. Cancel";
    } elseif ($level == 4 && $inputArray[0] == "1" && $inputArray[3] == "1") {
        $serviceType = "Washing & Drying";  // Extract dynamically
        $item = "Shirt/T-shirt";  // Extract dynamically
        $totalAmount = 150;  // Extract dynamically

        // Save order in database
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, service_type, total_price, status, created_at, phone) VALUES (?, ?, ?, 'Pending', NOW(), ?)");
        $stmt->execute([$phoneNumber, $serviceType, $totalAmount, $phoneNumber]);

        // M-Pesa STK Push
        $mpesaResponse = sendMpesaSTKPush($phoneNumber, $totalAmount);

        $response = "END Order placed successfully! \n";
        $response .= "You will receive an M-Pesa prompt to pay Ksh $totalAmount.";
    } else {
        $response = "END Invalid choice. Please try again.";
    }
}

// Send Response
header("Content-type: text/plain");
echo $response;

// Function to send M-Pesa STK Push
function sendMpesaSTKPush($phoneNumber, $amount) {
    $businessShortCode = "174379";
    $lipaNaMpesaPasskey = "YOUR_MPESA_PASSKEY";
    $timestamp = date("YmdHis");
    $password = base64_encode($businessShortCode . $lipaNaMpesaPasskey . $timestamp);

    $stkRequestData = [
        "BusinessShortCode" => $businessShortCode,
        "Password" => $password,
        "Timestamp" => $timestamp,
        "TransactionType" => "CustomerPayBillOnline",
        "Amount" => $amount,
        "PartyA" => $phoneNumber,
        "PartyB" => $businessShortCode,
        "PhoneNumber" => $phoneNumber,
        "CallBackURL" => "https://your-render-url/callback.php",
        "AccountReference" => "LaundryService",
        "TransactionDesc" => "Laundry Payment"
    ];

    $stkUrl = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
    $accessToken = "YOUR_ACCESS_TOKEN";  // Get from M-Pesa OAuth API

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $stkUrl);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stkRequestData));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}
?>
