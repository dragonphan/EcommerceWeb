<?php
session_start();
include("../config.php");

// Initialize variables
$id = "";
$productname = "";
$category = "";
$description = "";
$price = "";
$availableunit = "";
$uploadOk = 0;
$uploadfileName = "";
$image = ""; 
$target_dir_base = "../image/product/";

// Check if ID is provided in the query string
if (isset($_GET["id"]) && $_GET["id"] != "") {
    $id = $_GET["id"];
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $productname = $row["productname"];
        $category = $row["item"];
        $description = $row["description"];
        $price = $row["price"];
        $availableunit = $row["availableunit"];
        $image = $row["image"]; // Fetch the image filename
        $target_dir = $target_dir_base . $category . "/";
    } else {
        echo "Invalid product ID.";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    $productname = mysqli_real_escape_string($conn, trim($_POST["productname"]));
    $category = mysqli_real_escape_string($conn, $_POST["item"]);
    $description = mysqli_real_escape_string($conn, trim($_POST["description"]));
    $price = mysqli_real_escape_string($conn, $_POST["price"]);
    $availableunit = mysqli_real_escape_string($conn, $_POST["availableunit"]);
    $target_dir = $target_dir_base . $category . "/";

    if (!empty($_FILES["fileToUpload"]["name"])) {
        $uploadOk = 1;
        $uploadfileName = basename($_FILES["fileToUpload"]["name"]);
        $target_file = $target_dir . $uploadfileName;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


        if (file_exists($target_file)) {
            echo "ERROR: Sorry, image file $uploadfileName already exists.<br>";
            $uploadOk = 0;
        }

        if ($_FILES["fileToUpload"]["size"] > 500000) {
            echo "ERROR: Sorry, your file is too large.<br>";
            $uploadOk = 0;
        }

        if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
            echo "ERROR: Only JPG, JPEG, PNG files are allowed.<br>";
            $uploadOk = 0;
        }
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                // Ensure that the old image is a file and not a directory
                $oldFilePath = $target_dir . $image;
                if ($image !== $uploadfileName && file_exists($oldFilePath) && is_file($oldFilePath)) {
                    unlink($oldFilePath);
                }
                $image = $uploadfileName; // Update the image filename
            } else {
                echo "Error uploading new file.<br>";
                $uploadOk = 0;
            }
        }
    }

    if ($uploadOk == 1 || empty($_FILES["fileToUpload"]["name"])) {
        // Update the database with the new information
        $sql = "UPDATE products SET productname='$productname', item='$category', 
                description='$description', price='$price', availableunit='$availableunit', 
                image='" . ($uploadOk == 1 ? $uploadfileName : $image) . "' WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_message'] = "Product edit successfully!";
            header("location: product_list.php?status=success");
            exit();
        } else {
            echo "Error updating product: " . mysqli_error($conn) . "<br>";
            echo '<a href="javascript:history.back()">Go Back</a>';
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>

<head>
    <title>Edit Product</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

</head>

<body>
    <h2 align="center">Edit Product</h2>
    <div>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <table border="1" class="small-table" align="center">
                <tr>
                    <td>Product Name</td>
                    <td>:</td>
                    <td>
                        <textarea rows="3" name="productname" cols="40" required><?php echo $productname; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>Product Image</td>
                    <td>:</td>
                    <td>
                        <?php
                        echo '<img src="' . $target_dir . $image . '" alt="Product Image"><br>';
                        ?>
                        <input type="file" name="fileToUpload" id="fileToUpload" accept=".jpg, .jpeg, .png">
                    </td>
                </tr>
                <tr>
                    <td>Category</td>
                    <td>:</td>
                    <td>
                        <select size="1" id="item" name="item" required>
                            <option value="">&nbsp;</option>
                            <?php
                            $categories = ["clothes", "pants", "socks", "shoes"];
                            foreach ($categories as $cat) {
                                $selected = ($category == $cat) ? 'selected' : '';
                                echo '<option value="' . $cat . '" ' . $selected . '>' . ucfirst($cat) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td>:</td>
                    <td>
                        <textarea rows="5" name="description" cols="40" required><?php echo $description; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>Price Per Unit</td>
                    <td>:</td>
                    <td>
                        RM <input type="text" name="price" size="8" value="<?php echo $price; ?>" required>
                    </td>
                </tr>
                <tr>
                    <td>Quantity</td>
                    <td>:</td>
                    <td>
                        <input type="number" name="availableunit" size="8" value="<?php echo $availableunit; ?>"
                            required>
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