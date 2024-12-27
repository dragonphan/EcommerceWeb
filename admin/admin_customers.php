<?php
include("../config.php");
session_start();

// Handle form submission for updating isAdmin status
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['userid']) && isset($_POST['newIsAdmin'])) {
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);
        $newIsAdmin = mysqli_real_escape_string($conn, $_POST['newIsAdmin']);

        // Update isAdmin status in the database
        $updateQuery = "UPDATE user SET isAdmin = '$newIsAdmin' WHERE id = '$userid'";
        $updateResult = mysqli_query($conn, $updateQuery);

        if (!$updateResult) {
            // Display error if update fails
            echo "Error updating isAdmin status: " . mysqli_error($conn);
            exit();
        }
        // Confirmation message upon successful update
        echo "isAdmin status updated successfully!";
        exit();
    }
}

// Fetch user data for the table
$query = "SELECT id AS user_id, firstname, lastname, email, phoneno, isAdmin FROM user";
$result = mysqli_query($conn, $query);

// Check for a result before attempting to fetch
if ($result) {
    // Fetch all rows at once if there are any results
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // Terminate script if the query fails
    die("Query failed: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin and Customer</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php include("admin_header.php"); ?>
    <h1 style="position: relative; padding-left: 10px;">
        <a href="index.php" style="text-decoration: none; color: black;">&lt;</a>
        Admin and Customers
    </h1>
    <div class="admin-customers-container">
        <table border="1" width="100%" id="projectable">
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>isAdmin</th>
            </tr>

            <?php
            // Check if we have any users
            if (isset($users) && count($users) > 0) {
                // Output user data as table rows
                foreach ($users as $row) {
                    echo "<tr style='text-align: center;'>
                            <td>{$row['user_id']}</td>
                            <td>{$row['firstname']}</td>
                            <td>{$row['lastname']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['phoneno']}</td>
                            <td>{$row['isAdmin']}</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No data found</td></tr>";
            }
            ?>
        </table>
    </div>
</body>

</html>