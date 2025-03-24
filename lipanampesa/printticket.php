<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Ticket</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 10px 20px;
            margin-right: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-print {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <h1>Booking Details</h1>

    <?php
    // Database configuration
    $dbHost = 'localhost';
    $dbName = 'tourtacdb';
    $dbUser = 'root';
    $dbPassword = '';

    try {
        // Connect to the database
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch booking details from bookingtb
        $bookingStmt = $pdo->query("SELECT * FROM bookingtb");
        $bookingDetails = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch transaction details from transactiontb
        $transactionStmt = $pdo->query("SELECT checkoutID, amount, created_at FROM transactiontb");
        $transactionDetails = $transactionStmt->fetchAll(PDO::FETCH_ASSOC);

        // Display booking details in a table
        echo '<table>';
        echo '<tr><th>Name</th><th>Email</th><th>Tour Name</th><th>Price Per Adult</th><th>Price Per Kid</th><th>Number of Adults</th><th>Number of Kids</th><th>Duration</th><th>Check-In Date</th><th>Check-Out Date</th><th>Total Price</th></tr>';
        foreach ($bookingDetails as $booking) {
            echo '<tr>';
            echo '<td>' . $booking['name'] . '</td>';
            echo '<td>' . $booking['email'] . '</td>';
            echo '<td>' . $booking['tourName'] . '</td>';
            echo '<td>' . $booking['pricePerAdult'] . '</td>';
            echo '<td>' . $booking['pricePerKid'] . '</td>';
            echo '<td>' . $booking['numberOfAdults'] . '</td>';
            echo '<td>' . $booking['numberOfKids'] . '</td>';
            echo '<td>' . $booking['duration'] . '</td>';
            echo '<td>' . $booking['checkInDate'] . '</td>';
            echo '<td>' . $booking['checkOutDate'] . '</td>';
            echo '<td>' . $booking['totalPrice'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        // Display transaction details in a table
        echo '<h1>Transaction Details</h1>';
        echo '<table>';
        echo '<tr><th>Checkout ID</th><th>Amount</th><th>Created At</th></tr>';
        foreach ($transactionDetails as $transaction) {
            echo '<tr>';
            echo '<td>' . $transaction['checkoutID'] . '</td>';
            echo '<td>' . $transaction['amount'] . '</td>';
            echo '<td>' . $transaction['created_at'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        // Add buttons for printing and downloading
        echo '<a href="#" class="btn btn-print" onclick="window.print()">Print Ticket</a>';
        echo '<a href="#" class="btn" download="ticket.pdf">Download Ticket</a>';
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    ?>

</body>
</html>
