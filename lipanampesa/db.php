<?php
include 'stkpushrequest.php'; // Assuming this file contains functions like access token generation

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $jsonData = file_get_contents('php://input');
  $data = json_decode($jsonData);

  // Extract transaction details from the data object
  if (isset($data)) {
    $ResultCode = $data->ResultCode;
    $ResultDesc = $data->ResultDesc;
    $MerchantRequestID = $data->MerchantRequestID;
    $CheckoutRequestID = $data->CheckoutRequestID;
    $Amount = $data->Amount;
    $MpesaReceiptNumber = $data->MpesaReceiptNumber;
    $TransactionDate = $data->TransactionDate;
    $PhoneNumber = $data->PhoneNumber;

    // Database connection details (replace with your actual credentials)
    $hostname = 'localhost';
    $username = 'root';
    $password = '';

    try {
      $db = new PDO("mysql:host=$hostname;dbname=blissdb", $username, $password);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Prepare SQL statement for insertion
      $sql = "INSERT INTO transactionstb (ResultCode, ResultDesc, MerchantRequestID, CheckoutRequestID, Amount, MpesaReceiptNumber, TransactionDate, PhoneNumber) VALUES (:ResultCode, :ResultDesc, :MerchantRequestID, :CheckoutRequestID, :Amount, :MpesaReceiptNumber, :TransactionDate, :PhoneNumber)";
      $stmt = $db->prepare($sql);

      // Bind parameters to prevent SQL injection
      $stmt->bindParam(':ResultCode', $ResultCode);
      $stmt->bindParam(':ResultDesc', $ResultDesc);
      $stmt->bindParam(':MerchantRequestID', $MerchantRequestID);
      $stmt->bindParam(':CheckoutRequestID', $CheckoutRequestID);
      $stmt->bindParam(':Amount', $Amount);
      $stmt->bindParam(':MpesaReceiptNumber', $MpesaReceiptNumber);
      $stmt->bindParam(':TransactionDate', $TransactionDate);
      $stmt->bindParam(':PhoneNumber', $PhoneNumber);

      // Execute the prepared statement
      $stmt->execute();

      echo "Transaction details successfully inserted into database!";

    } catch(PDOException $e) {
      echo "Error inserting data: " . $e->getMessage();
    }

    $db = null; // Close the database connection (optional)
  }
}
?>
