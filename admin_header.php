<?php
namespace App\Admin;
use App\Config;

// Define constants for commonly used styles and links
define('LINK_STYLE', 'text-decoration: none; color: #76453B;');
define('HEADER_STYLE', 'background-color: #F8FAE5; box-shadow: 2px 2px 2px #76453B;');

require_once '../config.php';

if (!isset($_SESSION['admin_login'])) {
    header("location: login.php");
    exit();
}

// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header style="<?php echo HEADER_STYLE; ?>">
    <div class="nav container">
        <a href="index.php" class="logo">E-Commerce</a>
        <nav>
            <?php
            $nav_items = [
                'index.php' => 'Home',
                'admin_customers.php' => 'Customers',
                'add_product.php' => 'Add Product',
                'admin_orders.php' => 'Orders'
            ];

            foreach ($nav_items as $url => $label) {
                $active_class = ($current_page === $url) ? 'active' : '';
                echo sprintf(
                    '<a href="%s" style="%s" class="%s">%s</a>',
                    $url,
                    LINK_STYLE,
                    $active_class,
                    htmlspecialchars($label)
                );
            }
            ?>
        </nav>
        <div class="nav-icons">
            <a href="../logout.php" style="<?php echo LINK_STYLE; ?>">
                <i class='bx bx-log-out'></i>
            </a>
        </div>
    </div>
</header>