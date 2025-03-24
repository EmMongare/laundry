<?php
// Include the file that generates the M-Pesa access token
include 'acesstoken.php';

// Generate the access token
$accessToken = generateAccessToken();

// Log access token instead of displaying it (for debugging)
file_put_contents('mpesa_log.txt', "Access Token: $accessToken\n", FILE_APPEND);

// M-Pesa API credentials
$businessShortCode = '174379';  
$lipaNaMpesaPasskey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$callbackURL = 'https://your-ngrok-url.ngrok-free.app/lipanampesa/callback.php';

// Database configuration
$dbHost = 'localhost';
$dbName = 'laundry_management_system';
$dbUser = 'root';
$dbPassword = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    
    // Retrieve and sanitize form values
    $phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : ''; 
    $total_price = isset($_POST['amount']) ? intval($_POST['amount']) : 0; 
    $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0; 
    $checkoutID = isset($_POST['checkoutID']) ? trim($_POST['checkoutID']) : ''; 
    $transactionStatus = 'Pending'; // Default status before confirmation

    // Log received form data
    file_put_contents('mpesa_log.txt', "Received Form Data: " . json_encode($_POST) . "\n", FILE_APPEND);

    // Validate inputs
    if (!preg_match('/^254\d{9}$/', $phone)) { 
        die("<script>alert('❌ Invalid phone number. Must be in 2547XXXXXXXX format.'); window.history.back();</script>");
    }
    if ($total_price <= 0) {
        die("<script>alert('❌ Invalid amount. Must be greater than zero.'); window.history.back();</script>");
    }

    try {
        // Connect to the database
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if a transaction already exists for this phone and order
        $query = $pdo->prepare("
            SELECT * FROM transactiontb 
            WHERE phone = :phone AND orderId = :order_id
        ");
        $query->execute([
            ':phone' => $phone, 
            ':order_id' => $orderId
        ]);
        $transaction = $query->fetch(PDO::FETCH_ASSOC);

        if ($transaction) {
            echo "Transaction Found: <br>";
            echo "Checkout ID: " . htmlspecialchars($transaction['checkoutID']) . "<br>";
            echo "Total Price: Ksh " . htmlspecialchars($transaction['total_price']) . "<br>";
            echo "Transaction Status: " . htmlspecialchars($transaction['transactionStatus']) . "<br>";
            echo "Date: " . htmlspecialchars($transaction['createdAt']) . "<br>";
        } else {
            // Initiate M-Pesa STK Push
            $responseData = initiateSTKPush($total_price, $phone, $accessToken);
            file_put_contents('mpesa_log.txt', "STK Push Response: " . json_encode($responseData) . "\n", FILE_APPEND);

            if ($responseData !== false && isset($responseData['CheckoutRequestID'])) {
                $checkoutID = $responseData['CheckoutRequestID'];

                // Insert transaction into the database
                $stmt = $pdo->prepare("INSERT INTO transactiontb (checkoutID, total_price, phone, orderId, transactionStatus, createdAt) 
                VALUES (:checkoutID, :total_price, :phone, :orderId, :transactionStatus, NOW())");

                $stmt->execute([
                    ':checkoutID' => $checkoutID,
                    ':total_price' => $total_price, 
                    ':phone' => $phone,
                    ':orderId' => $orderId,
                    ':transactionStatus' => 'Pending' 
                ]);

                file_put_contents('mpesa_log.txt', "Transaction Inserted - CheckoutID: $checkoutID\n", FILE_APPEND);

                // Send SMS Notification
                if (sendSMSNotification($phone, "Your payment request of KES $total_price has been initiated. Check your phone.")) {
                    echo "<script>
                        alert('✅ STK Push sent. Check your phone and enter M-Pesa PIN.');
                        window.location.href = 'download_ticket.php?checkoutID=" . urlencode($checkoutID) . "';
                    </script>";
                } else {
                    echo "<script>
                        alert('✅ STK Push sent but SMS failed. Proceed to ticket download.');
                        window.location.href = 'download_ticket.php?checkoutID=" . urlencode($checkoutID) . "';
                    </script>";
                }
            } else {
                die("<script>alert('❌ STK Push Failed. Try again.'); window.history.back();</script>");
            }
        }
    } catch (PDOException $e) {
        file_put_contents('mpesa_log.txt', "Database Error: " . $e->getMessage() . "\n", FILE_APPEND);
        die("<script>alert('❌ Database Error: Transaction failed.'); window.history.back();</script>");
    }
}

// Initiate STK Push Function
function initiateSTKPush($amount, $phone, $accessToken) {
    global $businessShortCode, $lipaNaMpesaPasskey, $callbackURL;

    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $timestamp = date('YmdHis');
    $password = base64_encode($businessShortCode . $lipaNaMpesaPasskey . $timestamp);

    $payload = [
        'BusinessShortCode' => $businessShortCode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => $businessShortCode,
        'PhoneNumber' => $phone,
        'CallBackURL' => $callbackURL,
        'AccountReference' => 'Laundry Payment',
        'TransactionDesc' => 'Payment for laundry'
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken 
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}

// Simulated SMS function
function sendSMSNotification($phone, $message) {
    file_put_contents('mpesa_log.txt', "SMS Sent to $phone: $message\n", FILE_APPEND);
    return true;
}
?>
