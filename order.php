<?php
include("config.php");
ob_start();
session_start();
if (!isset($_SESSION['user_login'])) {
	header("location: login.php");
} else {
	$user = $_SESSION['user_login'];
	$result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
	$get_user_email = mysqli_fetch_assoc($result);
	$uname_db = $get_user_email != null ? $get_user_email['firstname'] : null;
}
$limit = 20; // Number of orders per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit; // Calculate the starting row
$result = mysqli_query($conn, "SELECT COUNT(order_id) AS id FROM orders");
$custCount = mysqli_fetch_assoc($result);
$total = $custCount['id'];
$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html>

<head>
	<title>Order History</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<style>
		body {
			margin: 0;
			background: url('image/background 4.png')center/cover no-repeat fixed;
		}
	</style>
</head>

<body>
	<?php include("header.php"); ?>
	<div class="profile-nav">
		<a href="profile.php?uid='.$user.'">User Profile</a>
		<a href="order.php?uid=' . $user.'">Order History</a>
	</div>
	<h1 style="position: relative; padding-left: 200px;">
		<a href="index.php" style="text-decoration: none; color: black;">&lt;</a>
		Order History
	</h1>
	<div class="pagination">
		<label>Page</label>
		<?php for ($i = 1; $i <= $pages; $i++): ?>
			<a href="?page=<?php echo $i; ?>">
				<?php echo $i; ?>
			</a>
		<?php endfor; ?>
	</div>
	<div style="margin-top: 20px;">
		<div style="width: 1500px; margin: 0 auto;">
			<table border="1" width="100%" id="projectable">
				<tr style="font-weight: bold; color:#FFF6DC;" colspan="10" bgcolor="#C08261">
					<th>Order ID</th>
					<th>Order Date</th>
					<th>Delivery Date</th>
					<th>Delivery Status</th>
					<th>Total</th>
				</tr>

				<?php

				$sql = "SELECT o.order_id, o.orderdate, o.deliverydate, o.payment_status, o.delivery_fee, o.total 
                        FROM orders o
                        LEFT JOIN order_items oi ON o.order_id = oi.order_id
                        LEFT JOIN products p ON oi.productid = p.id
                        WHERE o.userid = '$user'
                        GROUP BY o.order_id
                        ORDER BY o.order_id DESC
						LIMIT $start, $limit";
				$result = mysqli_query($conn, $sql);
				if (!$result) {
					echo "Error in SQL query: " . mysqli_error($conn);
					exit;
				}
				if (mysqli_num_rows($result) == 0) {
					echo "<tr><td colspan='4'>No orders found for user ID: $user</td></tr>";
				} else {
					while ($row = mysqli_fetch_assoc($result)) {
						$final_total = $row['total'];
						echo '<tr style="text-align:center;">';
						echo '<td><b><a href="view_order.php?orderid=' . $row['order_id'] . '">' . $row['order_id'] . '</a></b></td>';
						echo '<td><b>' . $row['orderdate'] . '</b></td>';
						echo '<td><b>' . $row['deliverydate'] . '</b></td>';
						echo '<td><b>' . $row['payment_status'] . '</b></td>';
						echo '<td><b>RM' . number_format($final_total, 2) . '</b></td>';
						echo '</tr>';
					}
				}
				?>
			</table>
		</div>
	</div>
</body>

</html>