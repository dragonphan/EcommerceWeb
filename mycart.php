<?php
include("config.php");
session_start();

if (!isset($_SESSION['user_login'])) {
    header("location: login.php");
} else {
    $user = $_SESSION['user_login'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
    $get_user_email = mysqli_fetch_assoc($result);
    $first_name_db = $get_user_email != null ? $get_user_email['firstname'] : null;
    $last_name_db = $get_user_email != null ? $get_user_email['lastname'] : null;
    $email_db = $get_user_email != null ? $get_user_email['email'] : null;
    $phone_number_db = $get_user_email != null ? $get_user_email['phoneno'] : null;
    $address_db = $get_user_email != null ? $get_user_email['address'] : null;
}

if (isset($_REQUEST['userid'])) {
    $user2 = mysqli_real_escape_string($conn, $_REQUEST['userid']);
    if ($user != $user2) {
        header('location: index.php');
    }
} else {
    header('location: index.php');
}

if (isset($_REQUEST['did'])) {
    $did = mysqli_real_escape_string($conn, $_REQUEST['did']);
    if (mysqli_query($conn, "DELETE FROM orders WHERE productid='$did' AND userid='$user'")) {
        header('location: mycart.php?userid=' . $user . '');
    } else {
        header('location: index.php');
    }
}

$search_value = "";
$total = 0;
$subtotal = 0;
$query = "SELECT * FROM cart WHERE userid='$user'";
$run = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($run)) {
    $quantity = $row['quantity'];
    $productid = $row['productid'];
    // Fetch the price for the product
    $query1 = "SELECT price FROM products WHERE id='$productid'";
    $result1 = mysqli_query($conn, $query1);
    $row1 = mysqli_fetch_assoc($result1);
    $total = ($quantity * $row1['price']);
    $subtotal += $total;
}

//order
//order
if (isset($_POST['order'])) {
    //declare variables
    $mbl = $_POST['phoneno'];
    $addr = $_POST['address'];
    $del = $_POST['delivery'];

    if (empty($_POST['phoneno'])) {
        $error_message = 'Error: Phone number cannot be empty';
    } else if (empty($_POST['address'])) {
        $error_message = 'Error: Address cannot be empty';
    } else if (empty($_POST['delivery'])) {
        $error_message = 'Error: Type of delivery cannot be empty';
    } else {
        $d = date("Y-m-d");

        // Calculate the final total
        $delivery_fee = ($del == "Express delivery") ? 10 : 0;
        $final_total = $subtotal + $delivery_fee;

        // Store the final total in session
        $_SESSION['final_total'] = $final_total;

        // After calculating final_total
        $insertOrder = "INSERT INTO orders (userid, billingaddress, phoneno, orderdate, delivery, delivery_fee, total) VALUES ('$user', '$addr', '$mbl', '$d', '$del', '$delivery_fee', '$final_total')";

        // Check if the order was successfully placed
        if (mysqli_query($conn, $insertOrder)) {
            $orderid = mysqli_insert_id($conn);
            $result = mysqli_query($conn, "SELECT * FROM cart WHERE userid='$user'");
            $t = mysqli_num_rows($result);

            if ($t <= 0) {
                $error_message = 'Error: No product in cart. Add products first.';
            } else {
                while ($get_p = mysqli_fetch_assoc($result)) {
                    $num = $get_p['quantity'];
                    $productid = $get_p['productid'];
                    mysqli_query($conn, "INSERT INTO order_items (order_id, productid, quantity) VALUES ('$orderid', '$productid', '$num')");
                    // Fetch the availableunit for the product
                    $queryAvailableUnit = "SELECT availableunit FROM products WHERE id='$productid'";
                    $resultAvailableUnit = mysqli_query($conn, $queryAvailableUnit);
                    $rowAvailableUnit = mysqli_fetch_assoc($resultAvailableUnit);
                    $availableUnit = $rowAvailableUnit['availableunit'];

                    // Check if there are enough available units
                    if ($num > $availableUnit) {
                        // Error: Not enough available units
                        $error_message = 'Error: Not enough available units for ' . $get_p['productname'];
                        break;  // Stop the loop if there's an error
                    }

                    // Update availableunit in the products table
                    $updateAvailableUnit = mysqli_query($conn, "UPDATE products SET availableunit = availableunit - $num WHERE id = '$productid'");

                    if (!$updateAvailableUnit) {
                        // Error updating availableunit
                        $error_message = 'Error updating availableunit: ' . mysqli_error($conn);
                        break;  // Stop the loop if there's an error
                    }
                }

                if (!isset($error_message)) {
                    // Delete items from the cart after successful order placement
                    if (mysqli_query($conn, "DELETE FROM cart WHERE userid='$user'")) {
                        // Success message
                        $success_message = '<div class="signupform_content">
                            <h2><font face="bookman"></font></h2>
                            <div class="signupform_text" style="font-size: 18px; text-align: center;">
                                <font face="bookman"></font>
                            </div>
                        </div>';
                    } else {
                        // Error deleting cart items
                        $error_message = 'Error deleting cart items: ' . mysqli_error($conn);
                    }
                }
            }
        } else {
            // Error placing order
            $error_message = 'Error placing order: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Cart</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .type_box {
            width: 100%;
            /* Full width of the cart-summary */
            padding: 10px;
            margin-bottom: 10px;
            /* Add space between inputs */
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <?php include("header.php");
    if (isset($success_message)) {
        echo '<h3 class="confirm-order">Payment & Delivery</h3>';
        $user = $_SESSION['user_login'];
        $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
        $get_user_email = mysqli_fetch_assoc($result);
        $first_name_db = $get_user_email['firstname'];
        $last_name_db = $get_user_email['lastname'];
        $email_db = $get_user_email['email'];
        $phone_number_db = $get_user_email['phoneno'];
        $address_db = $get_user_email['address'];
        $del = $_POST['delivery'];
        echo '<div class="confirm-order">
								<p><b>Full Name: </b>' . $first_name_db . ' ' . $last_name_db . '</p>
							</div>
							<div class="confirm-order">
								<p><b>Email: </b>' . $email_db . '</p>
							</div>
							<div class="confirm-order">
								<p><b>Contact Number: </b>' . $phone_number_db . '</p>
							</div>
							<div class="confirm-order">
								<p><b>Home Address: </b>' . $address_db . '</p>
							</div>
							
							<div class="confirm-order">
								<p><b>Delivery Method: </b>' . $del . '</p>
							</div>
							<div class="confirm-order">
								<p><b>Total: </b>RM ' . number_format($subtotal, 2) . '</p>
							</div>
							<div class="confirm-order">
								<p><b>Delivery Fee: </b>RM ' . number_format($delivery_fee, 2) . '</p>
							</div>
							<div class="confirm-order">
								<p><b>Final Total: </b>RM ' . number_format($final_total, 2) . '</p>
							</div>
							<div>
								<input type="button" class="button" value="View Your Order"onclick="window.location.href=\'order.php\';">
							</div>';
    } else {
        echo '<div class="cart-container" style="margin-top: 20px; padding: 0 1%;">';
        echo '<div class="cart-left">';
        echo '<table border="1" id="projectable">';
        echo '<tr style="font-weight: bold; color:#FFF6DC;" colspan="10" bgcolor="#C08261">';
        echo '<th style="width:5%">Remove</th>';
        echo '<th style="width:10%">View</th>';
        echo '<th style="width:20%">Product Name</th>';
        echo '<th style="width:25%">Description</th>';
        echo '<th style="width:10%">Price</th>';
        echo '<th style="width:10%">Unit</th>';
        echo '<th style="width:10%">Total</th>';
        echo '</tr>';

        $subtotal = 0;
        $run = mysqli_query($conn, "SELECT * FROM cart WHERE userid='$user' ORDER BY id DESC");
        while ($row = mysqli_fetch_assoc($run)) {
            $productid = $row['productid'];
            $quantity = $row['quantity'];
            $query1 = "SELECT * FROM products WHERE id='$productid'";
            $run1 = mysqli_query($conn, $query1);
            $row1 = mysqli_fetch_assoc($run1);
            $total = $quantity * $row1['price'];
            $subtotal += $total;

            echo '<tr>';
            echo '<th><div class="home-prodlist-img"><a href="delete_cart.php?did=' . $row1['id'] . '" style="text-decoration: none;color: black;">X</a></div></th>';
            echo '<th><div class="home-prodlist-img"><a href="Products/view_product.php?productid=' . $row1['id'] . '"><img src="image/product/' . $row1['item'] . '/' . $row1['image'] . '" class="home-prodlist-imgi" style="height: 75px; width: 75px;"></a></div></th>';
            echo '<th>' . $row1['productname'] . '</th>';
            echo '<th>' . $row1['description'] . '</th>';
            echo '<th>RM' . number_format($row1['price'], 2) . '</th>';
            echo '<th><div class="quantity-modifiers"><a href="delete_cart.php?zid=' . $row1['id'] . '" class="modifier-link">-</a><span class="quantity">' . $quantity . '</span><a href="delete_cart.php?aid=' . $productid . '" class="modifier-link">+</a></div></th>';
            echo '<th>RM' . number_format($total, 2) . '</th>';
            echo '</tr>';
        }

        echo '<tr>';
        echo '<th colspan="6" align="right">Total:&nbsp;</th>';
        echo '<th>RM' . number_format($subtotal, 2) . '</th>';
        echo '</tr>';
        echo '</table>';
        echo '<div class="cart-right">
					<h3 style="text-align: center; ">Cart Summary</h3>';
        echo '<form action="" method="POST" class="cart_info_container">
                                <div>
                                    <h3>Accepting Cash On Delivery Only</h3>
									<div>
										<label>Full Name:</label>
										<input name="fullname" placeholder="Your name" required="required" class="type_box" value="' . $first_name_db . ' ' . $last_name_db . '">
									</div>
                                    <div>
										<label>Phone Number:</label>
										<input name="phoneno" placeholder="Your phone number" required="required" class="type_box" value="' . $phone_number_db . '">
                                    </div>
									<div>
										<label>Home Address</label>
										<input name="address" id="password-1" required="required" placeholder="Write your full address" class="type_box" value="' . $address_db . '">
                                    </div>
									<div>
										<label>Delivery Method: </label>
										<div>
											<input name="delivery" required="required" value="Express delivery" type="radio">Express Delivery +RM10
										</div>
										<div>
											<input name="delivery" required="required" value="Standard delivery" type="radio">Standard Delivery FREE
										</div>
									</div>
                                    <div style="margin-top: 10px;">
                                            <input onclick="myFunction()" name="order" class="button" type="submit" value="Confirm Order" style="width: 150px;">
                                    </div>

                                    <div class="signup_error_msg"> ';
        if (isset($error_message)) {
            echo $error_message;
        }
        echo '</div>
                </form>
                </div>
                    ';
    }
    ?>
</body>

</html>