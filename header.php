<?php
if (!isset($_SESSION['user_login'])) {
    $user = "";
    $first_name_db = ""; // Set to empty if user is not logged in
} else {
    $user = $_SESSION['user_login'];
    // Assuming $conn is your database connection
    $result = mysqli_query($conn, "SELECT * FROM user WHERE id='$user'");
    if ($result) {
        $get_user_email = mysqli_fetch_assoc($result);
        $first_name_db = ($get_user_email != null) ? $get_user_email['firstname'] : "";
    } else {
        $first_name_db = ""; // Set to empty if query fails
    }
}
?>

<div class="homepageheader" style="position: relative;">
    <div class="registerButton loginButton">
        <div class="uiloginbutton registerButton loginButton" style="margin-right: 40px;">
            <?php
            if ($user != "") {
                $logout_url = file_exists("logout.php") ? "logout.php" : "../logout.php";
                echo '<a style="text-decoration: none; color: #76453B;" href="' . $logout_url . '">LOG OUT</a>';
            } else {
                $register_url = file_exists("register.php") ? "register.php" : "../register.php";
                echo '<a style="color: #76453B; text-decoration: none;" href="' . $register_url . '">SIGN UP</a>';
            }
            ?>

        </div>
        <div class="uiloginbutton registerButton loginButton">
            <?php
            if ($user != "") {
                $profile_url = file_exists("profile.php") ? 'profile.php?userid=' . $user : '../profile.php?userid=' . $user;
                echo '<a style="text-decoration: none; color: #76453B;" href="' . $profile_url . '">Hi ' . $first_name_db . '</a>';
            } else {
                $login_url = file_exists("login.php") ? "login.php" : "../login.php";
                echo '<a style="text-decoration: none; color: #76453B;" href="' . $login_url . '">LOG IN</a>';
            }
            ?>
        </div>

        <div class="cartButton">
            <?php
            $cart_url = file_exists("mycart.php") ? 'mycart.php?userid=' . $user : '../mycart.php?userid=' . $user;
            echo '<a href="' . $cart_url . '"><i class="bx bxs-cart bx-lg" style="color:#E2C799;"></i></a>';
            ?>
        </div>




    </div>

    <div style="float: left; margin: 5px 0px 0px 23px;">
        <?php
        $logo_path = 'image/logo.png';

        if (file_exists($logo_path)) {
            echo '<a href="index.php"><img style="height: 75px; width: 160px;" src="' . $logo_path . '"></a>';
        } else {
            echo '<a href="../index.php"><img style="height: 75px; width: 160px;" src="../image/logo.png"></a>';
        }
        ?>
    </div>

    <div>
        <form id="search" method="get"
            action="<?php echo file_exists('Products/search.php') ? 'Products/search.php' : 'search.php'; ?>">
            <input type="text" class="srctextinput" name="keywords" placeholder="Search Here..." style="height: 30px;">
            <input type="submit" value="search" class="srcbutton">
        </form>
    </div>
</div>