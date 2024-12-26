<?php
include("config.php");
ob_start();
session_start();

if (!isset($_SESSION['user_login'])) {
    $user = "";
} else {
    $user = $_SESSION['user_login'];
    $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
    $get_user_data = mysqli_fetch_assoc($result);
    $uname_db = isset($get_user_data['firstname']) ? $get_user_data['firstname'] : '';
    $lname_db = isset($get_user_data['lastname']) ? $get_user_data['lastname'] : '';
}

// Check if the form is submitted
if (isset($_POST['update_profile'])) {
    // Retrieve form data
    $update_data = array(
        'firstname' => mysqli_real_escape_string($conn, $_POST['fname']),
        'lastname' => mysqli_real_escape_string($conn, $_POST['lname']),
        'email' => mysqli_real_escape_string($conn, $_POST['email']),
        'phoneno' => mysqli_real_escape_string($conn, $_POST['phoneno']),
        'address' => mysqli_real_escape_string($conn, $_POST['address'])
    );

    // Update user information in the database
    $update_query = "UPDATE user SET ";
    foreach ($update_data as $key => $value) {
        $update_query .= "$key='$value', ";
    }
    $update_query = rtrim($update_query, ", ") . " WHERE id='$user'";

    $update_result = mysqli_query($conn, $update_query);

    if ($update_result) {
        // Update successful, redirect to profile.php
        header("Location: profile.php?uid=$user");
        exit();
    } else {
        // Update failed, handle the error
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Profile - Nita's Online Grocery</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<style>
	body{
		margin: 0;
		background:url('image/background 4.png')center/cover no-repeat fixed;
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

<body style="min-width: 980px;">
	<?php include("header.php"); ?>
    <div class="profile-container">
		<h1 style="text-align:center;">Edit Profile</h1>
		<form method="post" action="">
			 <table border="0" align="center" width="100%" >
				<tr>
					<td><b><label for="fname">First Name</label></td>
					<td><b>:</td>
					<td><input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($uname_db); ?>"></td>
				</tr>
				
				<tr>
					<td><b><label for="lname">Last Name</label></td>
					<td><b>:</td>
					<td><input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($lname_db); ?>"></td>
				</tr>

				<?php
				$fields = array(
					'email' => 'Email',
					'phoneno' => 'Phone Number',
					'address' => 'Address'
				);
	
				foreach ($fields as $field => $label) {
					echo "<tr>
							<td><b><label for=\"$field\">$label</label></td>
							<td><b>:</td>
							<td><input type=\"text\" id=\"$field\" name=\"$field\" value=\"" . (!empty($get_user_data[$field]) ? htmlspecialchars($get_user_data[$field]) : '') . "\"></td>
						</tr>";
				}
				?>
			</table>
			<input type="submit" name="update_profile" value="Update Profile" class="button">
			<input type="button" value="Back" class="button" onclick="window.location.href='profile.php';">
		</form>
	</div>

</body>

</html>
