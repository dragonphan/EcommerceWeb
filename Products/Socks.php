<?php
session_start();
include("../config.php");
if (!isset($_SESSION['user_login'])) {
    $user = "";
} else {
    $user = $_SESSION['user_login'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
    $get_user_email = mysqli_fetch_assoc($result);
    $first_name_db = $get_user_email['firstname'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Socks</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <div class="show-product-container">
        <div class="product-container">
            <?php
            $getposts = mysqli_query($conn, "SELECT * FROM products WHERE availableunit >='1' AND item ='socks'  ORDER BY id DESC LIMIT 10") or die(mysqli_error($conn));
            if (mysqli_num_rows($getposts)) {
                while ($row = mysqli_fetch_assoc($getposts)) {
                    $id = $row['id'];
                    $productname = $row['productname'];
                    $price = $row['price'];
                    $description = $row['description'];
                    $image = $row['image'];

                    echo '
                    <div class="product-item">
                        <a href="view_product.php?productid=' . $id . '">
                            <img src="../image/product/socks/' . $image . '" class="product-image" alt="' . $productname . '">
                        </a>
                        <div class="product-details">
                            <span class="product-name">' . $productname . '</span><br>
                            <span class="product-price">RM' . $price . ' </span>
                        </div>
                    </div>
                ';
                }
            }
            ?>
        </div>
    </div>


</body>

</html>