<?php
namespace App;
use App\Config;

require_once 'config.php';

session_start();
if (isset($_SESSION['user_login'])) {
	header("location: index.php");
	exit();
}

$emails = "";
$passs = "";

if (isset($_POST['login']) && isset($_POST['email']) && isset($_POST['password'])) {
	$user_login = mysqli_real_escape_string($conn, $_POST['email']);
	$user_login = mb_convert_case($user_login, MB_CASE_LOWER, "UTF-8");
	$password_login = mysqli_real_escape_string($conn, $_POST['password']);
	$password_login_md5 = md5($password_login);
	
	$result = mysqli_query($conn, "SELECT * FROM user WHERE (email='$user_login') AND password='$password_login_md5'");
	$num = mysqli_num_rows($result);

	if ($num > 0) {
		$user_row = mysqli_fetch_assoc($result);
		$_SESSION['user_login'] = $user_row['id'];
		
		if ($user_row['isAdmin'] == 1) {
			header('location: admin/index.php');
		} else {
			header('location: index.php');
		}
		exit();
	} else {
		$error_message = '<br><br>
				<div class="maincontent_text" style="text-align: center; font-size: 18px;">
				<font face="bookman">Email or Password incorrect.<br>
				</font></div>';
	}
}
?>

<!DOCTYPE html>
<html lang="en" xml:lang="en">

<head>
	<title>Welcome to E-Commerce</title>
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		body {
			margin: 0;
			background: url('image/background 1.png')center/cover no-repeat fixed;
		}
	</style>
</head>

<body>
	<div class="login_register_container">
		<h2>Login Form</h2>
		<form action="" method="POST" class="login_register">
			<div>
				<label for="email"><i class='bx bxs-user'></i> Email:</label><br>
				<input id="email" name="email" placeholder="Enter Your Email" required="required" 
					   class="type_box" type="email" size="30" value="<?php echo $emails; ?>">
			</div>
			<div>
				<label for="password"><i class='bx bxs-lock-alt'></i> Password:</label><br>
				<input id="password" name="password" required="required" placeholder="Enter Password"
					   class="type_box" type="password" size="30" value="<?php echo $passs; ?>">
			</div>
			<div>
				<input name="login" class="button" type="submit" value="Login">
			</div>
			<div>
				<input name="register" class="button" type="submit" value="Sign Up Now"
					   onclick="window.location.href='register.php';">
			</div>
			<div>
				<input type="button" class="button" value="Back" onclick="window.location.href='index.php';">
			</div>
			<div class="signup_error_msg">
				<?php
				if (isset($error_message)) {
					echo $error_message;
				}
				?>
			</div>
		</form>
	</div>
</body>

</html>