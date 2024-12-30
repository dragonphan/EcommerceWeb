<?php
namespace App;
use App\Config;
use App\Exceptions\ValidationException;
use App\Exceptions\DatabaseException;

require_once 'config.php';
session_start();

// Custom Exception classes
class ValidationException extends \Exception {}
class DatabaseException extends \Exception {}

if (isset($_SESSION['user_login'])) {
	header("location: index.php");
	exit;
}

// Initialize variables
$first_name_db = $last_name_db = $u_email = $u_phoneno = $u_address = $u_pass = $error_message = "";

if (isset($_POST['signup'])) {
	try {
		// Prepare statement for email check
		$check_email_stmt = $conn->prepare("SELECT email FROM user WHERE email = ?");
		if (!$check_email_stmt) {
			throw new DatabaseException("Failed to prepare email check statement");
		}

		// Prepare statement for user insertion
		$insert_stmt = $conn->prepare("INSERT INTO user (firstName, lastName, email, phoneno, address, password) VALUES (?, ?, ?, ?, ?, ?)");
		if (!$insert_stmt) {
			throw new DatabaseException("Failed to prepare insert statement");
		}

		// Get and validate form data
		$first_name_db = trim($_POST['first_name']);
		$last_name_db = trim($_POST['last_name']);
		$u_email = trim($_POST['email']);
		$u_phoneno = trim($_POST['phoneno']);
		$u_address = trim($_POST['signupaddress']);
		$u_pass = trim($_POST['password']);

		// Validate required fields
		if (empty($first_name_db) || empty($last_name_db) || empty($u_email) || 
			empty($u_phoneno) || empty($u_address) || empty($u_pass)) {
			throw new ValidationException('All fields are required.');
		}

		// Validate first name
		if (strlen($first_name_db) < 2 || strlen($first_name_db) > 20 || is_numeric($first_name_db[0])) {
			throw new ValidationException('Firstname must be 2-20 characters and start with a letter.');
		}

		// Validate last name
		if (strlen($last_name_db) < 2 || strlen($last_name_db) > 20 || is_numeric($last_name_db[0])) {
			throw new ValidationException('Lastname must be 2-20 characters and start with a letter.');
		}

		// Check if email exists
		$check_email_stmt->bind_param("s", $u_email);
		$check_email_stmt->execute();
		$result = $check_email_stmt->get_result();
		if ($result->num_rows > 0) {
			throw new ValidationException('Email already taken.');
		}

		// Validate password strength
		if (strlen($u_pass) < 8) {
			throw new ValidationException('Password must be at least 8 characters long.');
		}

		// Hash password using modern algorithm
		$hashed_password = password_hash($u_pass, PASSWORD_DEFAULT);

		// Insert new user
		$insert_stmt->bind_param("ssssss", 
			$first_name_db, $last_name_db, $u_email, $u_phoneno, $u_address, $hashed_password);

		if (!$insert_stmt->execute()) {
			throw new DatabaseException('Error registering user: ' . $insert_stmt->error);
		}

		$success_message = '<div class="signupform_text" style="font-size: 18px; text-align: center;">
			<h2><font face="bookman">Registration successful!</font></h2>
			<div class="signupform_text" style="font-size: 18px;">
			<font face="bookman">Email: ' . htmlspecialchars($u_email) . '</font></div></div>';

	} catch (ValidationException $e) {
		$error_message = $e->getMessage();
	} catch (DatabaseException $e) {
		$error_message = "Database error occurred. Please try again later.";
		error_log($e->getMessage());
	} catch (\Exception $e) {
		$error_message = "An unexpected error occurred. Please try again.";
		error_log($e->getMessage());
	}
}
?>

<!DOCTYPE html>
<html lang="en" xml:lang="en">

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
	<div class="login_register_container">
		<?php if (!isset($success_message)) { ?>
			<h2>Sign Up Form!</h2>
			<form action="" method="POST" class="login_register">
				<?php include 'includes/register_form_fields.php'; ?>
			</form>
			<div class="signup_error_msg">
				<?php if (isset($error_message)) echo htmlspecialchars($error_message); ?>
			</div>
		<?php } else {
			echo $success_message;
			echo '<p style="text-align:center;"><a href="login.php"> | Login |</a></p>';
		} ?>
	</div>
</body>

</html>