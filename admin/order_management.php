<?php
session_start();
include("../config.php");

// Redirect if the user is not logged in or is not an admin.
if (!isset($_SESSION['user_login'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user_login'];
$result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");

if (mysqli_num_rows($result) !== 1) {
    header("Location: index.php");
    exit();
}

$user_data = mysqli_fetch_assoc($result);
if ($user_data['isAdmin'] != 1) {
    header("Location: index.php");
    exit();
}

// Handling form submission for updating order details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['orderid']) && isset($_POST['newOrderStatus']) && isset($_POST['newDeliveryDate'])) {
        $orderid = $_POST['orderid'];
        $newOrderStatus = $_POST['newOrderStatus'];
        $newDeliveryDate = $_POST['newDeliveryDate'];

        // Check if 'No' order status requires NULL delivery date
        if ($newOrderStatus === 'No') {
            $newDeliveryDate = NULL;
        }

        // Validate: If 'Yes' order status, check for empty delivery date
        if ($newOrderStatus === 'Yes' && empty($newDeliveryDate)) {
            echo "Error: Delivery date is required when order status is Yes.";
            exit();
        }

        // Update order status and delivery date in the database
        $updateQuery = "UPDATE orders SET payment_status = '$newOrderStatus', deliverydate = '$newDeliveryDate' WHERE order_id = '$orderid'";
        $updateResult = mysqli_query($conn, $updateQuery);

        if (!$updateResult) {
            echo "Error updating order: " . mysqli_error($conn);
            exit();
        }
        echo "Order updated successfully!";
        exit();
    }
}

$limit = 20; // Number of orders per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit; // Calculate the starting row

$result = mysqli_query($conn, "SELECT COUNT(order_id) AS id FROM orders");
$custCount = mysqli_fetch_assoc($result);
$total = $custCount['id'];
$pages = ceil($total / $limit);

$orderQuery = "SELECT o.order_id, CONCAT(u.firstname, ' ', u.lastname) AS customer_name, 
               u.phoneno, u.email, u.address, o.orderdate, o.payment_status, o.deliverydate 
               FROM orders o
               LEFT JOIN user u ON o.userid = u.id 
               ORDER BY o.order_id DESC 
               LIMIT $start, $limit";
$orderResult = mysqli_query($conn, $orderQuery);

if (!$orderResult) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <?php include("admin_header.php"); ?>
    <h1 style="position: relative; padding-left: 10px;">
        <a href="index.php" style="text-decoration: none; color: black;">&lt;</a>
        Order Management
    </h1>
    <div class="pagination">
        <label>Page</label>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <div class="admin-orders">
        <table border="1" width="100%">
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>Order Date</th>
                <th>Order Status</th>
                <th>Delivery Date</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($orderResult)): ?>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($row['order_id']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['customer_name']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['phoneno']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['email']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['address']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['orderdate']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['payment_status']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['deliverydate']); ?>
                    </td>
                    <td>
                        <button
                            onclick="toggleEdit(this, '<?php echo $row['order_id']; ?>', '<?php echo $row['payment_status']; ?>', '<?php echo $row['deliverydate']; ?>', this.parentElement.previousElementSibling.previousElementSibling, this.parentElement.previousElementSibling)">Edit</button>
                    </td>

                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
        function toggleEdit(button, orderId, currentOrderStatus, currentDeliveryDate, statusCell, dateCell) {
            if (button.textContent === "Edit") {
                // Create select element for order status editing
                var statusSelect = document.createElement("select");
                statusSelect.innerHTML = "<option value='Yes'>Yes</option><option value='No'>No</option>";
                statusSelect.value = currentOrderStatus;

                // Create date input field for delivery date editing
                var dateInput = document.createElement("input");
                dateInput.type = "date";
                dateInput.value = currentDeliveryDate;

                // Create Save button for updating order details
                var saveButton = document.createElement("button");
                saveButton.textContent = "Save";
                saveButton.onclick = function () {
                    var newOrderStatus = statusSelect.value;
                    var newDeliveryDate = dateInput.value;

                    // Check for empty delivery date if order status is 'Yes'
                    if (newOrderStatus === 'No') {
                        newDeliveryDate = '';
                    }
                    if (newOrderStatus === 'Yes' && newDeliveryDate === '') {
                        alert("Delivery date is required when order status is Yes.");
                        return;
                    }

                    // AJAX call to update order details
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function () {
                        if (this.readyState == 4 && this.status == 200) {
                            alert(this.responseText);
                            location.reload();
                        }
                    };
                    xhttp.open("POST", "<?php echo $_SERVER['PHP_SELF']; ?>", true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("orderid=" + orderId + "&newOrderStatus=" + newOrderStatus + "&newDeliveryDate=" + newDeliveryDate);
                };

                // Create Cancel button for reverting changes
                var cancelButton = document.createElement("button");
                cancelButton.textContent = "Cancel";
                cancelButton.onclick = function () {
                    statusCell.innerHTML = currentOrderStatus;
                    dateCell.innerHTML = currentDeliveryDate;
                    button.textContent = "Edit";
                    button.parentNode.replaceChild(button.cloneNode(true), button);
                };

                // Replace status cell content with select element
                statusCell.innerHTML = "";
                statusCell.appendChild(statusSelect);

                // Replace date cell content with date input field
                dateCell.innerHTML = "";
                dateCell.appendChild(dateInput);

                // Replace button content with Save and Cancel buttons
                button.textContent = "";
                button.appendChild(saveButton);
                button.appendChild(cancelButton);
            }
        }
    </script>
</body>

</html>