<?php
session_start();
include('../config.php');

if (isset($_GET["id"]) && $_GET["id"] != "") {
    $id = $_GET["id"];

    $selectSql = "SELECT image, item FROM products WHERE id=" . $id . "";
    $result = mysqli_query($conn, $selectSql);

    if ($row = mysqli_fetch_assoc($result)) {
        $category = $row['item'];
        $filename = $row["image"];
        $filePath = "../image/product/" . $category . "/" . $filename;

        // Additional checks for file path correctness
        if (!empty($category) && !empty($filename) && file_exists($filePath)) {
            if (unlink($filePath)) {
                echo "Photo deleted successfully.<br>";
            } else {
                echo "Error: Unable to delete the photo.<br>";
            }
        } else {
            echo "No photo found or already deleted.<br>";
        }

        // Proceed to delete the record from the database
        $deleteSql = "DELETE FROM products WHERE id=" . $id;
        if (mysqli_query($conn, $deleteSql)) {
            echo "Record deleted successfully.<br>";
        } else {
            echo "Error deleting record: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Error: Unable to find the specified record.<br>";
    }

    echo '<a href="product_list.php">Back</a>';
}

mysqli_close($conn);
?>