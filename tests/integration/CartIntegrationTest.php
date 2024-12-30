<?php

use PHPUnit\Framework\TestCase;

class CartIntegrationTest extends TestCase
{
    private $conn;
    private $userId;
    private $productId;

    protected function setUp(): void
    {
        $db_host = getenv('DB_HOST');
        $db_user = getenv('DB_USERNAME');
        $db_pass = getenv('DB_PASSWORD');
        $db_name = getenv('DB_DATABASE');
        
        $this->conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        
        if (!$this->conn) {
            $this->fail("Database connection failed: " . mysqli_connect_error());
        }

        // Create test user
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Cart', 'Test', 'cart.integration@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->userId = mysqli_insert_id($this->conn);

        // Create test product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES ('Test Product', 99.99, 'Test Description', 100, 'clothes', 'test.png')";
        mysqli_query($this->conn, $query);
        $this->productId = mysqli_insert_id($this->conn);
    }

    public function testAddToCartAndCheckout(): void
    {
        // Add item to cart
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, 2)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify cart item
        $query = "SELECT * FROM cart WHERE userid = ? AND productid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Item should be in cart");

        // Create order from cart
        $query = "INSERT INTO orders (userid, billingaddress, phoneno, orderdate, delivery, total, delivery_fee) 
                 VALUES (?, 'Test Address', '1234567890', CURDATE(), 'Standard', 199.98, 10.00)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $orderId = mysqli_insert_id($this->conn);

        // Move cart items to order_items (removed price field)
        $query = "INSERT INTO order_items (order_id, productid, quantity) 
                 SELECT ?, productid, quantity FROM cart WHERE userid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $orderId, $this->userId);
        mysqli_stmt_execute($stmt);

        // Clear cart
        $query = "DELETE FROM cart WHERE userid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);

        // Verify cart is empty
        $query = "SELECT * FROM cart WHERE userid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "Cart should be empty after checkout");

        // Verify order items
        $query = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Order items should exist");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_query($this->conn, "DELETE FROM cart WHERE userid = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM order_items WHERE order_id IN (SELECT order_id FROM orders WHERE userid = {$this->userId})");
            mysqli_query($this->conn, "DELETE FROM orders WHERE userid = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM products WHERE id = {$this->productId}");
            mysqli_close($this->conn);
        }
    }
} 