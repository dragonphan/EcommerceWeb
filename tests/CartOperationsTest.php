<?php

use PHPUnit\Framework\TestCase;

class CartOperationsTest extends TestCase
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
                 VALUES ('Cart', 'Test', 'cart.test@test.com', '1234567890', 'Test Address', ?, 0)";
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

    public function testAddToCart(): void
    {
        // Test adding item to cart
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        
        $this->assertTrue(mysqli_stmt_execute($stmt));

        // Verify cart item
        $query = "SELECT * FROM cart WHERE userid = ? AND productid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result));
    }

    public function testUpdateCartQuantity(): void
    {
        // Add item to cart first
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, 1)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);

        // Update quantity
        $newQuantity = 2;
        $query = "UPDATE cart SET quantity = ? WHERE userid = ? AND productid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'iii', $newQuantity, $this->userId, $this->productId);
        
        $this->assertTrue(mysqli_stmt_execute($stmt));

        // Verify updated quantity
        $query = "SELECT quantity FROM cart WHERE userid = ? AND productid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cart = mysqli_fetch_assoc($result);
        
        $this->assertEquals($newQuantity, $cart['quantity']);
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up
            mysqli_query($this->conn, "DELETE FROM cart WHERE userid = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->userId}");
            mysqli_query($this->conn, "DELETE FROM products WHERE id = {$this->productId}");
            mysqli_close($this->conn);
        }
    }
} 