<?php
include("../config.php");
session_start();
if (!isset($_SESSION['user_login'])) {
	header("location: login.php");
	exit();
} else {
	$user = $_SESSION['user_login'];
	$result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user' AND isAdmin=1");
	$num_rows = mysqli_num_rows($result);
	if ($num_rows !== 1) {
		// Redirect if the user is not an admin
		header("location: index.php"); // Redirect to index.php or any other page
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
	<title>E-Commerce | Admin</title>
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<style>
		body {
			margin: 0;
			background: url('../image/background 1.png')center/cover no-repeat fixed;
		}

		button {
			color: #EFE4D9;
			background: #9A3B3B;
			font-size: 18px;
			border-radius: 25px;
			padding: 25px 25px;
			margin: 8px auto;
			/* Center horizontally */
			border: none;
			cursor: pointer;
			width: 50%;
			display: block;
			/* Make sure it takes the full width of the parent container */
		}

		button:hover {
			opacity: 0.9;
		}
	</style>
</head>

<body class="profile_bg">
	<?php include("admin_header.php"); ?>
	<div style="text-align: center; color: #76453B; font-size: 25px;">
		<h1>Welcome To Admin Panel</h1>
		<h2>You have all permission to do!</h2>
	</div>
	<div>
		<button type="submit" onclick="window.location.href='order_management.php';">Orders</button>
	</div>
	<div>
		<button type="submit" onclick="window.location.href='product_list.php';">Products</button>
	</div>
	<div>
		<button type="submit" onclick="window.location.href='sales_report.php';">Sales Report</button>
	</div>
	<div>
		<button type="submit" onclick="window.location.href='admin_customers.php';">Admin and Customer</button>
	</div>


</body>

</html>