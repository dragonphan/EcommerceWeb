<?php
namespace App;
use App\Config;

// Define constants for frequently used HTML elements
define('TD_END', '</td>');
define('TR_END', '</tr>');
define('TABLE_STYLE', 'style="width: 100%; text-align: center;"');
define('CELL_STYLE', 'style="color: #FFF6DC; background-color: #C08261;"');

require_once 'config.php';
ob_start();
session_start();

if (!isset($_SESSION['user_login'])) {
    header("location: login.php");
    exit();
}

$user = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $user);
$stmt->execute();
$result = $stmt->get_result();
$get_user_email = $result->fetch_assoc();
$uname_db = $get_user_email['firstname'] ?? '';
?>

<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
    <title>Order Details</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            margin: 0;
            background: url('image/background 4.png')center/cover no-repeat fixed;
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    <h2 style="position: relative; padding-left: 350px;">
        <a href="order.php" style="text-decoration: none; color: black;">&lt;</a>
        Order History
    </h2>
    <div style="margin-top: 20px;">
        <div style="width: 900px; margin: 0 auto;">
            <ul>
                <?php 
                if (isset($_GET['orderid'])) {
                    $requested_order_id = $_GET['orderid'];
                    
                    // Prepare and execute orders query
                    $ordersSql = "SELECT * FROM orders WHERE order_id = ? AND userid = ?";
                    $stmt = $conn->prepare($ordersSql);
                    $stmt->bind_param("ii", $requested_order_id, $user);
                    $stmt->execute();
                    $ordersResult = $stmt->get_result();
                
                    if (!$ordersResult || $ordersResult->num_rows == 0) {
                        echo "<li>No orders found for Order ID: " . htmlspecialchars($requested_order_id) . "</li>";
                    } else {
                        while ($order = $ordersResult->fetch_assoc()) {
                            $orderid = $order['order_id'];
                            $orderdate = $order['orderdate'];
                            $deliverydate = $order['deliverydate'];
                            $delivery_fee = $order['delivery_fee'];
                            $delivery_type = $order['delivery'];
                            
                            echo "<div>";
                            echo "<p><b>Order ID:</b> " . htmlspecialchars($orderid) . "</p>";
                            echo "<p><b>Order Date:</b> " . htmlspecialchars($orderdate) . "</p>";
                            echo "<p><b>Delivery Date:</b> " . htmlspecialchars($deliverydate) . "</p>";
                            echo "<p><b>Delivery Type:</b> " . htmlspecialchars($delivery_type) . "</p>";
                            
                            echo "<table " . TABLE_STYLE . " id='projectable'>";
                            echo "<tr " . CELL_STYLE . ">";
                            echo "<th>No</th>";
                            echo "<th>Product</th>";
                            echo "<th>Price</th>";
                            echo "<th>Quantity</th>";
                            echo "<th>Total</th>";
                            echo TR_END;

                            // Prepare and execute items query
                            $itemsSql = "SELECT oi.quantity, p.productname, p.price
                                       FROM order_items oi
                                       JOIN products p ON oi.productid = p.id
                                       WHERE oi.order_id = ?";
                            $stmt = $conn->prepare($itemsSql);
                            $stmt->bind_param("i", $orderid);
                            $stmt->execute();
                            $itemsResult = $stmt->get_result();

                            if ($itemsResult) {
                                $numrow = 1;
                                $subtotal = 0;
                                while ($item = $itemsResult->fetch_assoc()) {
                                    $quantity = $item['quantity'];
                                    $price = $item['price'];
                                    $productName = $item['productname'];
                                    $total = $quantity * $price;
                                    $subtotal += $total;
                                    
                                    echo "<tr style='text-align: center;'>";
                                    echo "<td>" . $numrow++ . TD_END;
                                    echo "<td>" . htmlspecialchars($productName) . TD_END;
                                    echo "<td>" . $price . TD_END;
                                    echo "<td>" . $quantity . TD_END;
                                    echo "<td>RM" . number_format($total, 2) . TD_END;
                                    echo TR_END;
                                }
                                
                                // Display totals
                                echo "<tr><td colspan='4' style='text-align: right;'>Subtotal:</td>";
                                echo "<td style='text-align: center;'>RM" . number_format($subtotal, 2) . TD_END;
                                echo TR_END;
                                
                                echo "<tr><td colspan='4' style='text-align: right;'>Delivery Fee:</td>";
                                echo "<td style='text-align: center;'>RM" . number_format($delivery_fee, 2) . TD_END;
                                echo TR_END;
                                
                                $final_total = $subtotal + $delivery_fee;
                                echo "<tr><th colspan='4' style='text-align: right;'>Final Total:</th>";
                                echo "<th>RM" . number_format($final_total, 2) . "</th>";
                                echo TR_END;
                            } else {
                                echo "<tr><td colspan='5'>No items found for order ID: " . htmlspecialchars($orderid) . TD_END . TR_END;
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
