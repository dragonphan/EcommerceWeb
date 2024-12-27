<?php include("config.php"); ?>
<?php
session_start();
if (!isset($_SESSION['user_login'])) {
	$user = "";
} else {
	$user = $_SESSION['user_login'];
	$result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
	$get_user_email = mysqli_fetch_assoc($result);
	$first_name_db = $get_user_email != null ? $get_user_email['firstname'] : null;
}
?>
<!DOCTYPE html>
<html>

<head>
	<title>Welcome to E-Commerce</title>
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		body {
			margin: 0;
			background: url('image/background 3.png')center/cover no-repeat fixed;
		}
	</style>
</head>

<body>
	<?php include("header.php"); ?>
	<div class="home-welcome-text">
		<h1 style="text-align: center;">Welcome To E-Commerce</h1>
	</div>

	<div class="home-prodlist">
		<div>
			<h3 style="text-align: center;">Products Category</h3>
		</div>
		<div style="display: flex; justify-content: space-around; padding: 20px 30px; width: 85%; margin: 0 auto;">
			<div style="padding: 25px;">
				<div class="home-prodlist-img"><a href="Products/Clothes.php">
						<img src="./image/product/clothes/cm.png" class="home-prodlist-imgi">
					</a>
				</div>
			</div>
			<div style="padding: 25px;">
				<div class="home-prodlist-img"><a href="Products/Pants.php">
						<img src="./image/product/pants/pm.png" class="home-prodlist-imgi">
					</a>
				</div>
			</div>
			<div style="padding: 25px;">
				<div class="home-prodlist-img"><a href="Products/Shoes.php">
						<img src="./image/product/shoes/sm.png" class="home-prodlist-imgi"></a>
				</div>
			</div>
			<div style="padding: 25px;">
				<div class="home-prodlist-img"><a href="Products/Socks.php">
						<img src="./image/product/socks/som.png" class="home-prodlist-imgi"></a>
				</div>
			</div>
		</div>

</body>

</html>