<?php
include("config.php");
session_start();

if (!isset($_SESSION['user_login'])) {
    header("location: login.php");
} else {
    $user = $_SESSION['user_login'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
    $get_user_email = mysqli_fetch_assoc($result);
    $first_name_db = $get_user_email['firstname'];
    $email_db = $get_user_email['email'];
    $phone_number_db = $get_user_email['phoneno'];
    $address_db = $get_user_email['address'];
}

if (isset($_REQUEST['did'])) {
    $did = mysqli_real_escape_string($conn, $_REQUEST['did']);
    if (mysqli_query($conn, "DELETE FROM cart WHERE productid='$did' AND userid='$user'")) {
        header('location: mycart.php?userid=' . $user);
    } else {
        header('location: index.php');
    }
}

if (isset($_REQUEST['aid'])) {
    $aid = mysqli_real_escape_string($conn, $_REQUEST['aid']);
    $result = mysqli_query($conn, "SELECT * FROM cart WHERE productid='$aid'");
    $get_p = mysqli_fetch_assoc($result);
    $num = $get_p['quantity'];
    $num += 1;

    if (mysqli_query($conn, "UPDATE cart SET quantity='$num' WHERE productid='$aid' AND userid='$user'")) {
        header('location: mycart.php?userid=' . $user);
    } else {
        header('location: index.php');
    }
}

if (isset($_REQUEST['zid'])) {
    $zid = mysqli_real_escape_string($conn, $_REQUEST['zid']);
    $result = mysqli_query($conn, "SELECT * FROM cart WHERE productid='$zid'");
    $get_p = mysqli_fetch_assoc($result);
    $num = $get_p['quantity'];
    $num -= 1;

    if ($num <= 0) {
        $num = 1;
    }

    if (mysqli_query($conn, "UPDATE cart SET quantity='$num' WHERE productid='$zid' AND userid='$user'")) {
        header('location: mycart.php?userid=' . $user);
    } else {
        header('location: index.php');
    }
}
?>