<?php
session_start();
include("../config.php");

// Initialize variables
$productname = "";
$category = "";
$description = "";
$price = "";
$availableunit = "";
$uploadOk = 1; // Assume the upload is OK to start with
$uploadfileName = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Sanitize the inputs to prevent SQL injection
	$productname = mysqli_real_escape_string($conn, trim($_POST["productname"]));
	$category = mysqli_real_escape_string($conn, $_POST["item"]);
	$description = mysqli_real_escape_string($conn, trim($_POST["description"]));
	$price = mysqli_real_escape_string($conn, $_POST["price"]);
	$availableunit = mysqli_real_escape_string($conn, $_POST["availableunit"]);

	// File upload handling
	if (isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] == UPLOAD_ERR_OK) {
		$uploadfileName = basename($_FILES["fileToUpload"]["name"]);
		$target_dir = "../image/product/" . $category . "/";
		// Make sure the directory exists or create it
		if (!file_exists($target_dir)) {
			mkdir($target_dir, 0777, true);
		}
		$target_file = $target_dir . $uploadfileName;
		$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

		// Check if file already exists
		if (file_exists($target_file)) {
			echo "ERROR: Sorry, image file $uploadfileName already exists.<br>";
			$uploadOk = 0;
		}

		// Check file size <= 500000 bytes (approximately 488.28KB)
		if ($_FILES["fileToUpload"]["size"] > 500000) {
			echo "ERROR: Sorry, your file is too large. Try resizing your image.<br>";
			$uploadOk = 0;
		}

		// Allow only certain file formats
		if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
			echo "ERROR: Sorry, only JPG, JPEG, PNG files are allowed.<br>";
			$uploadOk = 0;
		}
	} else {
		echo "ERROR: No file was uploaded.<br>";
		$uploadOk = 0;
	}

	if ($uploadOk) {
		$sql = "INSERT INTO products (productname, item, description, price, availableunit, image) 
                VALUES ('$productname', '$category', '$description', '$price', '$availableunit', '$uploadfileName')";

		if (mysqli_query($conn, $sql)) {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
				$_SESSION['success_message'] = "New product added successfully!";
				header("location: product_list.php?status=success");
				exit();

			} else {
				echo "Sorry, there was an error uploading your file.<br>";
				echo '<a href="javascript:history.back()">Back</a>';
			}
		} else {
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
		}
	} else {
		echo '<a href="javascript:history.back()">Back</a>';
	}
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Add New Product</title>
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
	<?php include("admin_header.php"); ?>
	<h2 align="center">Add New Product</h2>
	<div>
		<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
			<table border="1" class="small-table" align="center">
				<tr>
					<td>Product Name</td>
					<td>:</td>
					<td>
						<textarea rows="3" name="productname" cols="40" required></textarea>
					</td>
				</tr>
				<tr>
					<td>Product Image</td>
					<td>:</td>
					<td>
						<input type="file" name="fileToUpload" id="fileToUpload" accept=".jpg, .jpeg, .png">
					</td>
				</tr>
				<tr>
					<td>Category</td>
					<td>:</td>
					<td>
						<select size="1" id="item" name="item" required>
							<option value="">&nbsp;</option>
							<option value="clothes">clothes</option>
							<option value="pants">pants</option>
							<option value="socks">socks</option>
							<option value="shoes">shoes</option>
						</select>

					</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>:</td>
					<td>
						<textarea rows="5" name="description" cols="40" required></textarea>
					</td>
				</tr>
				<tr>
					<td>Price Per Unit</td>
					<td>:</td>
					<td>
						RM <input type="text" name="price" size="8" required>
					</td>
				</tr>
				<tr>
					<td>Quantity</td>
					<td>:</td>
					<td>
						<input type="number" name="availableunit" size="8" required>
					</td>
				</tr>
				<tr>
					<td colspan="3" align="center">
						<input type="submit" value="Submit">
						<input type="reset" value="Reset">
						<input type="button" value="Back" onclick="window.location.href='product_list.php';">
					</td>
				</tr>
			</table>
		</form>
	</div>
</body>

</html>