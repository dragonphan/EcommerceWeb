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
$user = $_SESSION['user_login'];
$result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
$get_user_data = mysqli_fetch_assoc($result);

$uname_db = isset($get_user_data['firstname']) ? $get_user_data['firstname'] : '';
$lname_db = isset($get_user_data['lastname']) ? $get_user_data['lastname'] : '';
$email_db = isset($get_user_data['email']) ? $get_user_data['email'] : null;
$phoneno_db = isset($get_user_data['phoneno']) ? $get_user_data['phoneno'] : null;
$address_db = isset($get_user_data['address']) ? $get_user_data['address'] : null;
$search_value = "";
?>
<!DOCTYPE html>
<html>

<head>
	<title>Admin Profile</title>
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<style>
		body {
			margin: 0;
			background: url('../image/background 4.png')center/cover no-repeat fixed;
		}

		table {
			width: 50%;
			border-collapse: collapse;
			table-layout: fixed;
		}

		td {
			padding: 8px;
		}

		td:first-child {
			width: 30%;
			text-align: right;
			padding-right: 15px;
		}

		td:nth-child(2) {
			width: 5%;
		}

		td:last-child {
			text-align: left;
		}
	</style>
</head>

<body>
	<?php include("admin_header.php"); ?>
	<h2 style="text-align:center" ;>Admin Profile</h2>
	<div class="profile-container">
		<table border="0" align="center" width="100%">
			<tr>
				<td><strong>First Name</strong></td>
				<td>:</td>
				<td>
					<?php echo $uname_db; ?>
				</td>
			</tr>
			<tr>
				<td><strong>Last Name</strong></td>
				<td>:</td>
				<td>
					<?php echo $lname_db; ?>
				</td>
			</tr>
			<tr>
				<td><strong>Email</strong></td>
				<td>:</td>
				<td>
					<?php echo $email_db; ?>
				</td>
			</tr>
			<tr>
				<td><strong>Phone Number</strong></td>
				<td>:</td>
				<td>
					<?php echo $phoneno_db; ?>
				</td>
			</tr>
			<tr>
				<td><strong>Address</strong></td>
				<td>:</td>
				<td>
					<?php echo $address_db; ?>
				</td>
			</tr>
		</table>
		<input type="button" value="Back" class="button" onclick="window.location.href='index.php';">
	</div>
</body>

</html>