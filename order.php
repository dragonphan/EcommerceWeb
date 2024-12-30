<?php
namespace App;
use App\Config;

// Define constants for frequently used HTML elements and URLs
define('TD_START', '<td><b>');
define('TD_END', '</b></td>');
define('TR_END', '</tr>');
define('TABLE_STYLE', 'style="width: 100%;"');
define('CELL_STYLE', 'style="text-align: center; color: #FFF6DC; background-color: #C08261;"');

require_once 'config.php';
session_start();

if (!isset($_SESSION['user_login'])) {
	header("location: login.php");
	exit();
}

$user = $_SESSION['user_login'];
$result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
$get_user_email = mysqli_fetch_assoc($result);
$uname_db = $get_user_email['firstname'] ?? null;

$limit = 20; // Number of orders per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;
$result = mysqli_query($conn, "SELECT COUNT(order_id) AS id FROM orders");
$custCount = mysqli_fetch_assoc($result);
$total = $custCount['id'];
$pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en" xml:lang="en">

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
	<?php require_once 'header.php'; ?>
	<div class="profile-nav">
		<a href="profile.php?uid=<?php echo $user; ?>">User Profile</a>
		<a href="order.php?uid=<?php echo $user; ?>">Order History</a>
	</div>
	<h1 style="position: relative; padding-left: 200px;">
		<a href="index.php" style="text-decoration: none; color: black;">&lt;</a>
		Order History
	</h1>
	<div class="pagination">
		<label for="page-select">Page</label>
		<select id="page-select" onchange="window.location.href='?page=' + this.value">
			<?php for ($i = 1; $i <= $pages; $i++): ?>
				<option value="<?php echo $i; ?>" <?php echo ($page == $i) ? 'selected' : ''; ?>>
					<?php echo $i; ?>
				</option>
			<?php endfor; ?>
		</select>
	</div>
	<div style="margin-top: 20px;">
		<div style="width: 1500px; margin: 0 auto;">
			<table <?php echo TABLE_STYLE; ?> id="projectable">
				<tr <?php echo CELL_STYLE; ?>>
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
						WHERE o.userid = ?
						GROUP BY o.order_id
						ORDER BY o.order_id DESC
						LIMIT ?, ?";
				
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("iii", $user, $start, $limit);
				$stmt->execute();
				$result = $stmt->get_result();

				if ($result->num_rows === 0) {
					echo "<tr><td colspan='5'>No orders found</td></tr>";
				} else {
					while ($row = $result->fetch_assoc()) {
						$final_total = $row['total'];
						echo '<tr style="text-align:center;">';
						echo TD_START . '<a href="view_order.php?orderid=' . $row['order_id'] . '">' . $row['order_id'] . '</a>' . TD_END;
						echo TD_START . $row['orderdate'] . TD_END;
						echo TD_START . $row['deliverydate'] . TD_END;
						echo TD_START . $row['payment_status'] . TD_END;
						echo TD_START . 'RM' . number_format($final_total, 2) . TD_END;
						echo TR_END;
					}
				}
				?>
			</table>
		</div>
	</div>
</body>

</html>