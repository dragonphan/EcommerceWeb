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
<<<<<<< HEAD
<<<<<<< HEAD
    $kid = mysqli_real_escape_string($conn, $_REQUEST['keywords']);
<<<<<<< HEAD
=======
    // Allow letters, numbers, spaces, and common symbols
    $kid = preg_replace('/[^a-zA-Z0-9\s\-_.,&@!?()]/', '', $_REQUEST['keywords']);
>>>>>>> parent of 0a71290 (Merge branch 'main' into View-&-Search-Product)
    if (trim($kid) != "") {
        // Continue with search
=======
    if ($kid != "" && ctype_alnum($kid)) {
        // Handle search logic if needed
>>>>>>> parent of 134d247 (Update search.php )
    } else {
        header('location: index.php');
    }
} else {
    header('location: index.php');
=======
    $kid = mysqli_real_escape_string($conn, $_REQUEST['keywords']);
    if (trim($kid) != "") {
        // Continue with search
    } else {
        header('location: index.php');
        exit();
    }
} else {
    header('location: index.php');
    exit();
>>>>>>> d221b62 (Testing commit)
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
<<<<<<< HEAD
<<<<<<< HEAD
                $search_value = trim($_GET['keywords']);
<<<<<<< HEAD
=======
                $search_value = trim($_GET['keywords']);
>>>>>>> d221b62 (Testing commit)
                $stmt = $conn->prepare("SELECT * FROM products WHERE productname LIKE ? OR item LIKE ? ORDER BY id DESC");
                $search_pattern = "%{$search_value}%";
                $stmt->bind_param("ss", $search_pattern, $search_pattern);
                $stmt->execute();
                $getposts = $stmt->get_result();
<<<<<<< HEAD
=======
                $search_value = mysqli_real_escape_string($conn, $search_value);
                $sql = "SELECT * FROM products WHERE productname LIKE '%$search_value%' OR item LIKE '%$search_value%' ORDER BY id DESC";
                $getposts = mysqli_query($conn, $sql) or die(mysqli_error($conn));
>>>>>>> parent of 134d247 (Update search.php )
=======
                // Allow specific characters
                $search_value = preg_replace('/[^a-zA-Z0-9\s\-_.,&@!?()]/', '', $search_value);
                $search_value = mysqli_real_escape_string($conn, $search_value);
                
                $sql = "SELECT * FROM products WHERE productname LIKE '%$search_value%' OR item LIKE '%$search_value%' ORDER BY id DESC";
                $getposts = mysqli_query($conn, $sql) or die(mysqli_error($conn));
>>>>>>> parent of 0a71290 (Merge branch 'main' into View-&-Search-Product)
                $total = mysqli_num_rows($getposts);
=======
                $total = mysqli_num_rows($getposts);
                
>>>>>>> d221b62 (Testing commit)
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
