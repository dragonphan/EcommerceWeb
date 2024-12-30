<?php
namespace App;

// Define constants for frequently used URLs
define('CART_URL', 'location: mycart.php?userid=');
define('INDEX_URL', 'location: index.php');

require "config.php";
session_start();

if (!isset($_SESSION['user_login'])) {
    header(INDEX_URL);
    exit();
} 

$user = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $user);
$stmt->execute();
$result = $stmt->get_result();
$get_user_email = $result->fetch_assoc();

$first_name_db = $get_user_email['firstname'] ?? '';
$email_db = $get_user_email['email'] ?? '';
$phone_number_db = $get_user_email['phoneno'] ?? '';
$address_db = $get_user_email['address'] ?? '';

// Delete item from cart
if (isset($_REQUEST['did'])) {
    $did = $_REQUEST['did'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE productid = ? AND userid = ?");
    $stmt->bind_param("ii", $did, $user);
    
    if ($stmt->execute()) {
        header(CART_URL . $user);
        exit();
    } else {
        header(INDEX_URL);
        exit();
    }
}

// Increase quantity
if (isset($_REQUEST['aid'])) {
    $aid = $_REQUEST['aid'];
    $stmt = $conn->prepare("SELECT * FROM cart WHERE productid = ?");
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();
    $get_p = $result->fetch_assoc();
    $num = $get_p['quantity'] + 1;

    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE productid = ? AND userid = ?");
    $stmt->bind_param("iii", $num, $aid, $user);
    
    if ($stmt->execute()) {
        header(CART_URL . $user);
        exit();
    } else {
        header(INDEX_URL);
        exit();
    }
}

// Decrease quantity
if (isset($_REQUEST['zid'])) {
    $zid = $_REQUEST['zid'];
    $stmt = $conn->prepare("SELECT * FROM cart WHERE productid = ?");
    $stmt->bind_param("i", $zid);
    $stmt->execute();
    $result = $stmt->get_result();
    $get_p = $result->fetch_assoc();
    $num = max(1, $get_p['quantity'] - 1);

    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE productid = ? AND userid = ?");
    $stmt->bind_param("iii", $num, $zid, $user);
    
    if ($stmt->execute()) {
        header(CART_URL . $user);
        exit();
    } else {
        header(INDEX_URL);
        exit();
    }
}
?>