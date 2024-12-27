<?php
include("config.php");
session_start();
if (isset($_SESSION['user_login'])) {
	header("location: index.php");
	exit;
}
?>
<!doctype html>
<html>

<head>
	<title>Welcome to E-Commerce</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<style>
	body{
		margin: 0;
		background:url('image/background 1.png')center/cover no-repeat fixed;
	}
	</style>
</head>

<body>
	<?php
	$first_name_db = "";
	$last_name_db = "";
	$u_email = "";
	$u_phoneno = "";
	$u_address = "";
	$u_pass = "";
	$error_message = "";

	if (isset($_POST['signup'])) {
		$first_name_db = mysqli_real_escape_string($conn, $_POST['first_name']);
		$last_name_db = mysqli_real_escape_string($conn, $_POST['last_name']);
		$u_email = mysqli_real_escape_string($conn, $_POST['email']);
		$u_phoneno = mysqli_real_escape_string($conn, $_POST['phoneno']);
		$u_address = mysqli_real_escape_string($conn, $_POST['signupaddress']);
		$u_pass = mysqli_real_escape_string($conn, $_POST['password']);

		try {
			if (empty($first_name_db) || empty($last_name_db) || empty($u_email) || empty($u_phoneno) || empty($u_address) || empty($u_pass)) {
				throw new Exception('All fields are required.');
			}

			if (strlen($first_name_db) < 2 || strlen($first_name_db) > 20 || is_numeric($first_name_db[0])) {
				throw new Exception('Firstname must be 2-20 characters and start with a letter.');
			}

			if (strlen($last_name_db) < 2 || strlen($last_name_db) > 20 || is_numeric($last_name_db[0])) {
				throw new Exception('Lastname must be 2-20 characters and start with a letter.');
			}

			$e_check = mysqli_query($conn, "SELECT email FROM `user` WHERE email='$u_email'");
			$email_check = mysqli_num_rows($e_check);

			if ($email_check > 0) {
				throw new Exception('Email already taken.');
			}

			if (strlen($u_pass) <= 1) {
				throw new Exception('Password should be stronger.');
			}

			$hashed_password = md5($u_pass);

			$query = "INSERT INTO user (firstName, lastName, email, phoneno, address, password) VALUES ('$first_name_db', '$last_name_db', '$u_email', '$u_phoneno', '$u_address', '$hashed_password')";
			$result = mysqli_query($conn, $query);

			if ($result) {
				$success_message = '<div class="signupform_text" style="font-size: 18px; text-align: center;"><h2><font face="bookman">Registration successful!</font></h2>
				<div class="signupform_text" style="font-size: 18px;">
				<font face="bookman">
				Email: ' . $u_email . '<br>
				</font></div></div>';
				echo $success_message;
				echo '<p style="text-align:center;"><a href="login.php"> | Login |</a></p>';
			} else {
				throw new Exception('Error registering user. Error: ' . mysqli_error($conn));
			}

		} catch (Exception $e) {
			$error_message = $e->getMessage();
		}
	}
	?>
	<div class="login_register_container">
		<?php if (!isset($success_message)) { ?>
			<h2>Sign Up Form!</h2>
			<form action="" method="POST" class="login_register">
				<div>
					<input name="first_name" id="first_name" placeholder="First Name" required="required"
					class="type_box" type="text" size="30" value="<?= $first_name_db ?>">
				</div>
				<div>
					<input name="last_name" id="last_name" placeholder="Last Name" required="required"
					class="type_box" type="text" size="30" value="<?= $last_name_db ?>">
				</div>
				<div>
					<input name="email" placeholder="Enter Your Email" required="required" class="type_box"
					type="email" size="30" value="<?= $u_email ?>">
				</div>
				<div>
					<input name="phoneno" placeholder="Enter Your Phone Number" required="required" class="type_box"
					type="text" size="30" value="<?= $u_phoneno ?>">
				</div>
				<div>
					<input name="signupaddress" placeholder="Write Your Full Address" required="required"
					class="type_box" type="text" size="30" value="<?= $u_address ?>">
				</div>
				<div>
					<input name="password" id="password-1" required="required" placeholder="Enter New Password"
					class="type_box" type="password" size="30" value="<?= $u_pass ?>">
				</div>
				<div>
					<input name="signup" class="button" type="submit" value="Sign Me Up!">
				</div>
				<div>
					<input type="button" class="button" value="Back" onclick="window.location.href='index.php';">
				</div>
			</form>
			<div class="signup_error_msg">
				<?php if (isset($error_message)) {
					echo $error_message;
				} ?>
			</div>
		<?php } ?>
	</div>
</body>

</html>