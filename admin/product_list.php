<?php
session_start();
include("../config.php");

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
// Check if the success message session variable exists
if (isset($_SESSION['success_message'])) {
	// Sanitize and display the message
	$message = htmlspecialchars($_SESSION['success_message']);
	echo "<script>alert('$message');</script>";

	// Clear the session variable
	unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html>

<head>
	<title>Product List</title>
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<style>
		input[type=button],
		input[type=submit] {
			color: #EFE4D9;
			background: #9A3B3B;
			border-radius: 25px;
		}
	</style>
</head>

<body>
	<?php include("admin_header.php"); ?>
	<h1 style="position: relative; padding-left: 10px;">
		<a href="index.php" style="text-decoration: none; color: black;">&lt;</a>
		Product List
	</h1>

	<div style="padding:0 10px;">
		<div style="text-align: right;">
			<form action="product_search.php" method="post">
				<input type="text" placeholder="Search" name="search">
				<input type="submit" value="Search">
			</form>
			<form action="add_product.php" style="align:right; padding: 10px 0;">
				<input type="button" value="Add New Product" onclick="window.location.href='add_product.php';">
			</form>
		</div>
		<table border="1" width="100%" id="projectable">
			<tr>
				<th>Product Code</th>
				<th>Product Image</th>
				<th>Product Name</th>
				<th>Category</th>
				<th>Description</th>
				<th>Price Per Unit</th>
				<th>Quantity</th>
				<th></th>
			</tr>
			<?php
			$sql = "SELECT * FROM products";
			$result = mysqli_query($conn, $sql);

			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_assoc($result)) {
					$category = $row['item'];
					$imagePath = "../image/product/" . $category . "/" . $row["image"];
					echo "<tr>";
					echo "<td style='text-align:center;'>" . $row["id"] .
						"</td><td style='text-align:center;'><img src='" . $imagePath . "' alt='Product Image' style='width:100px;'></td><td>"
						. $row["productname"] . "</td><td>" . $category . "</td><td>" . $row["description"] . "</td><td style='text-align:center;'>RM "
						. $row["price"] . "</td><td style='text-align:center;'>" . $row["availableunit"] . "</td>";
					echo '<td style="text-align:center;"> <a href = "product_edit.php?id=' . $row["id"] . '">Edit</a>&nbsp;|&nbsp;';
					echo '<a href="product_delete.php?id=' . $row["id"] . '" onClick="return confirm(\'Delete?\');">Delete</a> </td>';
					echo "</tr>" . "\n\t\t";
				}
			} else {
				echo '<tr><td colspan="6">0 results</td></tr>';
			}

			mysqli_close($conn);
			?>
		</table>
</body>

</html>