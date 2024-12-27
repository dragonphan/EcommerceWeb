<?php
session_start();
include("../config.php");

if (!isset($_SESSION['user_login'])) {
    header("location: login.php");
    exit();
} else {
    $user = $_SESSION['user_login'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user' AND isAdmin=1");
    $num_rows = mysqli_num_rows($result);
    if ($num_rows !== 1) {
        // Redirect if the user is not an admin
        header("location: index.php");
        exit();
    }
    $get_user_email = mysqli_fetch_assoc($result);
    $uname_db = $get_user_email['firstname'];
}
$search_value = "";
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sales Report</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        input[type=button],
        input[type=submit] {
            color: #EFE4D9;
            background: #9A3B3B;
            border-radius: 25px;
        }

        input[type=text] {
            background: #EFE4D9 border-radius: 25px;
        }
    </style>
</head>

<body>
    <?php include("admin_header.php"); ?>
    <h1 style="position: relative; padding-left: 10px;">
        <a href="index.php" style="text-decoration: none; color: black;">&lt;</a>
        Sales Report
    </h1>

    <div style="padding:0 10px;">
        <div style="text-align: right;">
            <form action="sales_report.php" method="post">
                <label for="selected_month">Select Month:</label>
                <input type="month" id="selected_month" name="selected_month" required>
                <input type="submit" value="Apply Filter">
            </form>
        </div>

        <table border="1" width="100%" id="admin_report_table">
            <tr>
                <th>Date</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price Per Unit(RM)</th>
                <th>Sales Quantity (Unit)</th>
                <th>Total Price (RM)</th>
            </tr>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $selectedMonth = $_POST["selected_month"];

                // Validate and sanitize the selected month (you can customize this based on your requirements)
                $selectedMonth = mysqli_real_escape_string($conn, $selectedMonth);

                // Convert the selected month to a date range (e.g., first day of the month to last day of the month)
                $startDate = date('Y-m-01', strtotime($selectedMonth));
                $endDate = date('Y-m-t', strtotime($selectedMonth));

                $query = "SELECT p.id, p.productname, p.price, oi.quantity, SUM(oi.quantity) * p.price AS totalprice, SUM(oi.quantity) AS salesqty, o.orderdate
							  FROM order_items oi
							  INNER JOIN orders o ON oi.order_id = o.order_id
							  INNER JOIN products p ON oi.productid = p.id
							  WHERE o.orderdate BETWEEN '$startDate' AND '$endDate'
							  GROUP BY o.orderdate, p.id, p.productname, p.price
							  ORDER BY o.orderdate, p.id";

                $result = mysqli_query($conn, $query);

                if (!$result) {
                    die("Query failed: " . mysqli_error($conn));
                }

                // Set message based on result availability
                $message = mysqli_num_rows($result) > 0 ? "" : "No data available for the selected month.";

                // Initialize $totalSales to 0
                $totalSales = 0;
                $totalQty = 0;
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $totalSales += $row['totalprice'];
                        $totalQty += $row['quantity'];
                        echo "<tr>
									<td style='text-align:center;'>{$row['orderdate']}</td>
									<td style='text-align:center;'>{$row['id']}</td>
									<td>{$row['productname']}</td>
									<td style='text-align:center;'>{$row['price']}</td>
									<td style='text-align:center;'>{$row['salesqty']}</td>
									<td style='text-align:center;'>{$row['totalprice']}</td>	
								</tr>";
                    }
                    echo "<tr>
								<th colspan='4' align='right'>Total Sales:&nbsp;</th>
								<th style='text-align:center;'>" . $totalQty . "</th>
								<th style='text-align:center;'>" . number_format($totalSales, 2) . "</th>
							</tr>";
                } else {
                    // Display the message if there is no data
                    echo "<tr><td colspan='7'>$message</td></tr>";
                }
            }
            ?>

        </table>
    </div>
</body>

</html>