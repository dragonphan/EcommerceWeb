<?php
include("../config.php");
session_start();
if (!isset($_SESSION['user_login'])) {
    $user = "";
} else {
    $user = $_SESSION['user_login'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
    $get_user_email = mysqli_fetch_assoc($result);
    $first_name_db = $get_user_email['firstname'];
}
if (isset($_REQUEST['productid'])) {

    $productid = mysqli_real_escape_string($conn, $_REQUEST['productid']);
} else {
    die("Query failed: " . mysqli_error($conn));
}

$getposts = mysqli_query($conn, "SELECT * FROM products WHERE id ='$productid'") or die(mysqlI_error($conn));
if (mysqli_num_rows($getposts)) {
    $row = mysqli_fetch_assoc($getposts);
    $id = $row['id'];
    $productname = $row['productname'];
    $price = $row['price'];
    $description = $row['description'];
    $availableunit = $row['availableunit'];
    $item = $row['item'];
    $image = $row['image'];
    $availableunit = $row['availableunit'];
}

if (isset($_POST['addcart'])) {
    // Check if the product is already in the cart
    $checkCart = mysqli_query($conn, "SELECT * FROM cart WHERE productid ='$productid' AND userid='$user'");
    if (mysqli_num_rows($checkCart) > 0) {
        // Product is already in the cart, so update the quantity
        $updateQuantity = mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE productid ='$productid' AND userid='$user'");
        if ($updateQuantity) {
            header('location: ../mycart.php?userid=' . $user . '');
        } else {
            die("Query failed: " . mysqli_error($conn));
        }
    } else {
        // Product is not in the cart, so insert a new record with quantity 1
        $insertCart = mysqli_query($conn, "INSERT INTO cart (userid, productid, quantity) VALUES ('$user', '$productid', 1)");
        if ($insertCart) {
            header('location: ../mycart.php?userid=' . $user . '');
        } else {
            die("Query failed: " . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        body {
            margin: 0;
            background: url('../image/background 4.png')center/cover no-repeat fixed;
        }
    </style>
</head>

<body>
    <?php include("../header.php");
    include 'menu.php'; ?>
    <div class="view-product-container">
        <div class="product-image-container">
            <img src="../image/product/<?php echo $item . '/' . $image; ?>" class="product-image"
                style="width: 399px; height: 399px; border: 2px solid #c7587e;">
        </div>
        <div class="product-details-container">

            <h2>
                <?php echo $productname; ?>
            </h2>
            <hr>
            <h3>Price: RM
                <?php echo $price; ?>
            </h3>
            <hr>
            <h3>Description:</h3>
            <p>
                <?php echo $description; ?>
            </p>
            <div>
                <h3>Want to buy this product?</h3>
                <form id="" method="post" action="">
                    <input type="submit" name="addcart" value="Add To Cart" class="button">
                </form>
            </div>
        </div>
    </div>
</body>

</html>