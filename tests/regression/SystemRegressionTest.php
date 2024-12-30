<?php

use PHPUnit\Framework\TestCase;

class SystemRegressionTest extends TestCase
{
    private $conn;
    private $userId;
    private $adminId;
    private $productId;
    private $orderId;

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

        // Create test data
        $this->setupTestData();
    }

    private function setupTestData(): void
    {
        // Create test user
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Test', 'User', 'regression.test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->userId = mysqli_insert_id($this->conn);

        // Create test product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES ('Regression Test Product', 99.99, 'Test Description', 100, 'clothes', 'test.png')";
        mysqli_query($this->conn, $query);
        $this->productId = mysqli_insert_id($this->conn);
    }

    public function testUserAuthentication(): void
    {
        // Test user login
        $email = 'regression.test@test.com';
        $password = md5('123456');
        
        $query = "SELECT * FROM user WHERE email = ? AND password = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $email, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "User authentication should work");
    }

    public function testProductManagement(): void
    {
        // Test product creation
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Product should exist");

        // Test product update
        $newPrice = 149.99;
        $query = "UPDATE products SET price = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'di', $newPrice, $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify update
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        
        $this->assertEquals($newPrice, $product['price'], "Product price should be updated");
    }

    public function testCartOperations(): void
    {
        // Test add to cart
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, 1)";
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

        // Test update quantity
        $query = "UPDATE cart SET quantity = 2 WHERE userid = ? AND productid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify quantity update
        $query = "SELECT quantity FROM cart WHERE userid = ? AND productid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cart = mysqli_fetch_assoc($result);
        
        $this->assertEquals(2, $cart['quantity'], "Cart quantity should be updated");
    }

    public function testOrderProcessing(): void
    {
        // Create order
        $query = "INSERT INTO orders (userid, billingaddress, phoneno, orderdate, delivery, total, delivery_fee) 
                 VALUES (?, 'Test Address', '1234567890', CURDATE(), 'Standard', 99.99, 10.00)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $this->orderId = mysqli_insert_id($this->conn);

        // Add order items
        $query = "INSERT INTO order_items (order_id, productid, quantity) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->orderId, $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify order creation
        $query = "SELECT * FROM orders WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Order should be created");

        // Test order status update
        $query = "UPDATE orders SET payment_status = 'Completed' WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->orderId);
        mysqli_stmt_execute($stmt);

        // Verify status update
        $query = "SELECT payment_status FROM orders WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        
        $this->assertEquals('Completed', $order['payment_status'], "Order status should be updated");
    }

    public function testInventoryManagement(): void
    {
        // Test stock update
        $newStock = 90;
        $query = "UPDATE products SET availableunit = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $newStock, $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify stock update
        $query = "SELECT availableunit FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        
        $this->assertEquals($newStock, $product['availableunit'], "Stock should be updated");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up test data
            mysqli_query($this->conn, "DELETE FROM order_items WHERE order_id = {$this->orderId}");
            mysqli_query($this->conn, "DELETE FROM orders WHERE order_id = {$this->orderId}");
            mysqli_query($this->conn, "DELETE FROM cart WHERE userid = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM products WHERE id = {$this->productId}");
            mysqli_close($this->conn);
        }
    }
} 