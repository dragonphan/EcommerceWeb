<?php

use PHPUnit\Framework\TestCase;

class DeliveryTest extends TestCase
{
    private $conn;
    private $userId;
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
                 VALUES ('Delivery', 'Test', 'delivery.test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->userId = mysqli_insert_id($this->conn);

        // Create test order
        $query = "INSERT INTO orders (userid, billingaddress, phoneno, orderdate, delivery, total, delivery_fee) 
                 VALUES (?, 'Test Address', '1234567890', CURDATE(), 'Standard', 99.99, 10.00)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $this->orderId = mysqli_insert_id($this->conn);
    }

    public function testUpdateDeliveryDate(): void
    {
        // Set delivery date
        $deliveryDate = date('Y-m-d', strtotime('+3 days'));
        $query = "UPDATE orders SET deliverydate = ? WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $deliveryDate, $this->orderId);
        
        $this->assertTrue(mysqli_stmt_execute($stmt));

        // Verify delivery date
        $query = "SELECT deliverydate FROM orders WHERE order_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);

        $this->assertEquals($deliveryDate, $order['deliverydate']);
    }

    public function testDeliveryFeeCalculation(): void
    {
        // Test different delivery options
        $deliveryOptions = [
            'Express' => 15.00,
            'Standard' => 10.00,
            'Economy' => 5.00
        ];

        foreach ($deliveryOptions as $option => $fee) {
            // Update delivery option and fee
            $query = "UPDATE orders SET delivery = ?, delivery_fee = ? WHERE order_id = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, 'sdi', $option, $fee, $this->orderId);
            mysqli_stmt_execute($stmt);

            // Verify fee
            $query = "SELECT delivery, delivery_fee FROM orders WHERE order_id = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $this->orderId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $order = mysqli_fetch_assoc($result);

            $this->assertEquals($option, $order['delivery']);
            $this->assertEquals($fee, $order['delivery_fee']);
        }
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up
            mysqli_query($this->conn, "DELETE FROM orders WHERE userid = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->userId}");
            mysqli_close($this->conn);
        }
    }
} 