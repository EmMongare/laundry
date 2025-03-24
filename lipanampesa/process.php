<?php
include 'acesstoken.php';

// Retrieve form data
$phoneNumber = $_POST['phone'];
$amount = $_POST['amount'];

// Initiate STK Push request
initiateSTKPush($amount, $phoneNumber);

// Function to initiate STK Push request
function initiateSTKPush($amount, $phoneNumber) {
    global $accessToken, $businessShortCode, $lipaNaMpesaPasskey, $callbackURL;

    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $timestamp = date('YmdHis');

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ),
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(array(
            'BusinessShortCode' => $businessShortCode,
            'Password' => base64_encode($businessShortCode . $lipaNaMpesaPasskey . $timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $businessShortCode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $callbackURL,
            'AccountReference' => 'Order Number', // Optional
            'TransactionDesc' => 'Payment for goods/services'
        )),
        CURLOPT_RETURNTRANSFER => true
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $responseData = json_decode($response);

    if (isset($responseData->CheckoutRequestID)) {
        // Successful STK push initiation
        echo "STK Push initiated successfully. Checkout request ID: " . $responseData->CheckoutRequestID;
    } else {
        // Error handling
        echo "Error initiating STK push: " . $response;
    }
}
?>
