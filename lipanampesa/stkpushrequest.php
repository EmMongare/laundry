<?php
include 'acesstoken.php'; // Include file to generate access token

// Define M-Pesa credentials
$businessShortCode = '174379';
$lipaNaMpesaPasskey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$callbackURL = 'https://0aad-102-219-210-90.ngrok-free.app/lipanampesa/callback.php'; // Replace with actual callback URL

// Function to initiate STK Push request
function initiateSTKPush($amount, $phoneNumber) {
    global $accessToken, $businessShortCode, $lipaNaMpesaPasskey, $callbackURL;

    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $timestamp = date('YmdHis');

    // ✅ Ensure Access Token is Set
    if (empty($accessToken)) {
        die("Error: Access token is missing.");
    }

    // ✅ Ensure Amount is an Integer
    $amount = intval($amount);

    // ✅ Validate Phone Number Format (Must Start with 254 and be 12 Digits)
    if (!preg_match('/^254\d{9}$/', $phoneNumber)) {
        die("Error: Invalid phone number. It must be in the format 2547XXXXXXXX.");
    }

    // Encode Password
    $password = base64_encode($businessShortCode . $lipaNaMpesaPasskey . $timestamp);

    // ✅ Debug: Print Values Before Sending
    echo "Access Token: " . $accessToken . "<br>";
    echo "Phone Number Sent: " . $phoneNumber . "<br>";
    echo "Amount Sent: " . $amount . "<br>";

    // Create API Request Payload
    $payload = [
        'BusinessShortCode' => $businessShortCode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phoneNumber,
        'PartyB' => $businessShortCode,
        'PhoneNumber' => $phoneNumber,
        'CallBackURL' => $callbackURL,
        'AccountReference' => 'Order Number',
        'TransactionDesc' => 'Do you want to pay to Bourbon Bliss Resort'
    ];

    // ✅ Debug: Print the Payload Before Sending
    echo "<pre>Payload Sent:";
    print_r($payload);
    echo "</pre>";

    // Initialize cURL Request
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
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // ✅ Debug: Print API Response
    echo "<pre>API Response:";
    print_r(json_decode($response, true));
    echo "</pre>";

    // Handle Response Errors
    if ($httpCode !== 200) {
        die("HTTP Error: " . $httpCode);
    }

    $responseData = json_decode($response, true);
    if (isset($responseData['CheckoutRequestID'])) {
        echo "STK Push initiated successfully. Checkout Request ID: " . $responseData['CheckoutRequestID'];
        return $responseData['CheckoutRequestID'];
    } else {
        die("Error: " . $response);
    }
}

// Example usage
$amount = 1; // Amount in KES
$phoneNumber = '254798381044'; // Replace with customer's phone number
initiateSTKPush($amount, $phoneNumber);
?>
