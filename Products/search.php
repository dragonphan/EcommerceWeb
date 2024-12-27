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

if (isset($_REQUEST['keywords'])) {
    $kid = mysqli_real_escape_string($conn, $_REQUEST['keywords']);
    if ($kid != "" && ctype_alnum($kid)) {
        // Handle search logic if needed
    } else {
        header('location: index.php');
    }
} else {
    header('location: index.php');
}

$search_value = trim($_GET['keywords']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search</title>
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

    <div class="search_result">
        <div class="product-container">
            <?php
            if (isset($_GET['keywords']) && $_GET['keywords'] != "") {
                $search_value = mysqli_real_escape_string($conn, $search_value);
                $sql = "SELECT * FROM products WHERE productname LIKE '%$search_value%' OR item LIKE '%$search_value%' ORDER BY id DESC";
                $getposts = mysqli_query($conn, $sql) or die(mysqli_error($conn));
                $total = mysqli_num_rows($getposts);
                echo '<div style="text-align: center;">' . $total . ' Product(s) Found</div><br>';
                echo '<div class="product-container">';
                while ($row = mysqli_fetch_assoc($getposts)) {
                    $id = $row['id'];
                    $productname = $row['productname'];
                    $price = $row['price'];
                    $description = $row['description'];
                    $image = $row['image'];
                    $item = $row['item'];

                    echo '
        <div class="product-item">
            <a href="view_product.php?productid=' . $id . '">
                <img src="../image/product/' . $item . '/' . $image . '" class="product-image" alt="' . $productname . '">
            </a>
            <div class="product-details">
                <span class="product-name">' . $productname . '</span><br>
                <span class="product-price"> RM' . $price . '</span>
            </div>
        </div>
    ';
                }
                echo '</div>'; // Close product-container div
            } else {
                echo "Input Something...";
            }
            ?>
        </div>
    </div>

</body>

</html>