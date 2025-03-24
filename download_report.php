<?php
session_start();
require_once('libs/tcpdf/tcpdf.php');

// Database connection
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "laundry_management_system";
$conn = new mysqli($servername, $username, $password_db, $dbname);

$from_date = $_SESSION['from_date'];
$to_date = $_SESSION['to_date'];
$status = $_SESSION['status'];
$customer_name = $_SESSION['customer_name'];

// Query with filters
$query = "SELECT orders.id, orders.status, orders.total_price, orders.created_at, users.name AS customer_name 
          FROM orders 
          JOIN users ON orders.customer_id = users.id
          WHERE 1";

if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND orders.created_at BETWEEN '$from_date' AND '$to_date'";
}
if (!empty($status)) {
    $query .= " AND orders.status = '$status'";
}
if (!empty($customer_name)) {
    $query .= " AND users.name LIKE '%$customer_name%'";
}

$result = $conn->query($query);

// Create PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// Report Title
$pdf->Cell(190, 10, 'Filtered Orders Report', 1, 1, 'C');
$pdf->Ln(5); // Space

// Table Headers
$html = '<table border="1" cellpadding="5">
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Status</th>
                <th>Total Price</th>
                <th>Created At</th>
            </tr>';

// Fetch Data and Add to Table
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
                <td>'.$row['id'].'</td>
                <td>'.$row['customer_name'].'</td>
                <td>'.$row['status'].'</td>
                <td>Ksh '.number_format($row['total_price'], 2).'</td>
                <td>'.$row['created_at'].'</td>
              </tr>';
}
$html .= '</table>';

// Write to PDF
$pdf->writeHTML($html);
$pdf->Output('Filtered_Report.pdf', 'D');
?>
