<?php

use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    private $conn;
    private $userId;
    private $productId;
    private $orderId;

    protected function setUp(): void
    {
        // Database configuration from phpunit.xml
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
                 VALUES ('Order', 'Test', 'order.test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->userId = mysqli_insert_id($this->conn);

        // Create test product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES ('Order Test Product', 99.99, 'Test Description', 100, 'clothes', 'test.png')";
        mysqli_query($this->conn, $query);
        $this->productId = mysqli_insert_id($this->conn);
    }

    public function testCreateOrder(): void
    {
        // Create order
        $query = "INSERT INTO orders (userid, billingaddress, phoneno, orderdate, delivery, total, delivery_fee) 
                 VALUES (?, ?, ?, CURDATE(), 'Standard', 99.99, 10.00)";
        $stmt = mysqli_prepare($this->conn, $query);
        $address = "123 Test St";
        $phone = "1234567890";
        mysqli_stmt_bind_param($stmt, 'iss', $this->userId, $address, $phone);
        
        $this->assertTrue(mysqli_stmt_execute($stmt), "Failed to create order");
        $this->orderId = mysqli_insert_id($this->conn);

        // Add order items
        $query = "INSERT INTO order_items (order_id, productid, quantity) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->orderId, $this->productId);
        $this->assertTrue(mysqli_stmt_execute($stmt), "Failed to add order items");

        // Verify order
        $query = "SELECT * FROM orders WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);

        $this->assertNotNull($order);
        $this->assertEquals($this->userId, $order['userid']);
        $this->assertEquals('Pending', $order['payment_status']);
        $this->assertEquals(99.99, $order['total']);
        $this->assertEquals(10.00, $order['delivery_fee']);
    }

    public function testUpdateOrderStatus(): void
    {
        // First create an order
        $query = "INSERT INTO orders (userid, billingaddress, phoneno, orderdate, delivery, total, delivery_fee) 
                 VALUES (?, 'Test Address', '1234567890', CURDATE(), 'Standard', 99.99, 10.00)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $orderId = mysqli_insert_id($this->conn);

        // Update payment status
        $query = "UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        $this->assertTrue(mysqli_stmt_execute($stmt));

        // Verify status update
        $query = "SELECT payment_status FROM orders WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);

        $this->assertEquals('Paid', $order['payment_status']);
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up
            mysqli_query($this->conn, "DELETE FROM order_items WHERE order_id IN (SELECT order_id FROM orders WHERE userid = {$this->userId})");
            mysqli_query($this->conn, "DELETE FROM orders WHERE userid = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM products WHERE id = {$this->productId}");
            mysqli_close($this->conn);
        }
    }
} 