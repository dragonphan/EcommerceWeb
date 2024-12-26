<?php 
include("config.php");
ob_start();
session_start();

if (!isset($_SESSION['user_login'])) {
    header("location: login.php");
    exit();
} else {
    $user = $_SESSION['user_login'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
    $get_user_email = mysqli_fetch_assoc($result);
    $uname_db = $get_user_email['firstname'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<style>
	body{
		margin: 0;
		background:url('image/background 4.png')center/cover no-repeat fixed;
	}
	</style>
</head>
<body>
    <?php include("header.php"); ?>
	<h2 style="position: relative; padding-left: 350px;">
		<a href="order.php" style="text-decoration: none; color: black;">&lt;</a>
		Order History
	</h2>
    <div style="margin-top: 20px;">
        <div style="width: 900px; margin: 0 auto;">
            <ul>
                <?php 
                if (isset($_GET['orderid'])) {
                    $requested_order_id = mysqli_real_escape_string($conn, $_GET['orderid']);

                    $ordersSql = "SELECT * FROM orders WHERE order_id = '$requested_order_id' AND userid = '$user'";
                    $ordersResult = mysqli_query($conn, $ordersSql);
                
                    if (!$ordersResult || mysqli_num_rows($ordersResult) == 0) {
                        echo "<li>No orders found for Order ID: $requested_order_id</li>";
                    } else {
                        while ($order = mysqli_fetch_assoc($ordersResult)) {
                            $orderid = $order['order_id'];
                            $orderdate = $order['orderdate'];
							$deliverydate = $order['deliverydate'];
                            $delivery_fee = $order['delivery_fee'];
                            $delivery_type = $order['delivery'];
                            echo "<div>";
                            echo "<p><b>Order ID:</b> $orderid</p>";
                            echo "<p><b>Order Date:</b> $orderdate</p>";
							echo "<p><b>Delivery Date:</b> $deliverydate</p>";
                            echo "<p><b>Delivery Type:</b> $delivery_type</p>";
                            echo "<table border='1' width='100%' id='projectable'>";
                            echo "<tr style='font-weight: bold;color:#FFF6DC;' bgcolor='#C08261'>";
                            echo "<th>No</th>";
                            echo "<th>Product</th>";
                            echo "<th>Price</th>";
                            echo "<th>Quantity</th>";
                            echo "<th>Total</th>";
                            echo "</tr>";

                            $itemsSql = "SELECT oi.quantity, p.productname, p.price
                                         FROM order_items oi
                                         JOIN products p ON oi.productid = p.id
                                         WHERE oi.order_id = '$orderid'";
                            $itemsResult = mysqli_query($conn, $itemsSql);
                            if ($itemsResult) {
                                $numrow = 1;
                                $subtotal = 0;
                                while ($item = mysqli_fetch_assoc($itemsResult)) {
                                    $quantity = $item['quantity'];
                                    $price = $item['price'];
                                    $productName = $item['productname'];
                                    $total = $quantity * $price;
                                    $subtotal += $total;
                                    echo "<tr style='text-align: center;'>";
                                    echo "<td>" . $numrow++ . "</td>";
                                    echo "<td>" . $productName . "</td>";
                                    echo "<td>" . $price . "</td>";
                                    echo "<td>" . $quantity . "</td>";
                                    echo "<td>RM" . number_format($total, 2) . "</td>";
                                    echo "</tr>";
                                }
                                // Display subtotal, delivery fee, and final total
                                echo "<tr><td colspan='4' align='right'>Subtotal:</td><td style='text-align: center;'>RM" . number_format($subtotal, 2) . "</td></tr>";
                                echo "<tr><td colspan='4' align='right'>Delivery Fee:</td><td style='text-align: center;'>RM" . number_format($delivery_fee, 2) . "</td></tr>";
                                $final_total = $subtotal + $delivery_fee;
                                echo "<tr><th colspan='4' align='right'>Final Total:</th><th>RM" . number_format($final_total, 2) . "</th></tr>";
                            } else {
                                echo "<tr><td colspan='5'>No items found for order ID: $orderid</td></tr>";
                            }
                            echo "</table>";
                            echo "</div>";
                        }
                    }
                } else {
                    echo "<li>Please select an order to view.</li>";
                }
                ?>
            </ul>
        </div>
    </div>
</body>
</html>
