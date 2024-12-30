<?php
namespace App;
use App\Config;

require_once 'config.php';
session_start();

if (!isset($_SESSION['user_login'])) {
	header("Location: login.php");
	exit();
}

$user = $_SESSION['user_login'];
$result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
$get_user_data = mysqli_fetch_assoc($result);

$uname_db = isset($get_user_data['firstname']) ? $get_user_data['firstname'] : '';
$lname_db = isset($get_user_data['lastname']) ? $get_user_data['lastname'] : '';
$email_db = isset($get_user_data['email']) ? $get_user_data['email'] : null;
$phoneno_db = isset($get_user_data['phoneno']) ? $get_user_data['phoneno'] : null;
$address_db = isset($get_user_data['address']) ? $get_user_data['address'] : null;
?>

<!DOCTYPE html>
<html lang="en" xml:lang="en">

<head>
	<title>User Profile</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<style>
		body {
			margin: 0;
			background: url('image/background 4.png')center/cover no-repeat fixed;
		}

		table {
			width: 50%;
			border-collapse: collapse;
			table-layout: fixed;
			margin: 0 auto;
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

<body class="profile_bg">
	<?php require_once 'header.php'; ?>

	<div class="profile-nav">
		<a href="profile.php?uid=<?php echo $user; ?>">User Profile</a>
		<a href="order.php?uid=<?php echo $user; ?>">Order History</a>
	</div>
	<div class="profile-container">
		<h1 style="text-align:center;">User Profile</h1>
		<table>
			<thead>
				<tr>
					<th>Field</th>
					<th></th>
					<th>Value</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>First Name</strong></td>
					<td>:</td>
					<td><?php echo $uname_db; ?></td>
				</tr>
				<tr>
					<td><strong>Last Name</strong></td>
					<td>:</td>
					<td><?php echo $lname_db; ?></td>
				</tr>
				<tr>
					<td><strong>Email</strong></td>
					<td>:</td>
					<td><?php echo $email_db; ?></td>
				</tr>
				<tr>
					<td><strong>Phone Number</strong></td>
					<td>:</td>
					<td><?php echo $phoneno_db; ?></td>
				</tr>
				<tr>
					<td><strong>Address</strong></td>
					<td>:</td>
					<td><?php echo $address_db; ?></td>
				</tr>
			</tbody>
		</table>
		<div style="text-align: center; margin-top: 20px;">
			<input type="button" value="Edit Profile" class="button" onclick="window.location.href='profile_edit.php';">
			<input type="button" class="button" value="Back" onclick="window.location.href='index.php';">
		</div>
	</div>
</body>

</html>