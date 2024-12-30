<?php

use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase
{
    private $conn;
    private $adminId;
    private $regularUserId;

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

        // Create test admin user
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Admin', 'Test', 'admin.test@test.com', '1234567890', 'Test Address', ?, 1)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->adminId = mysqli_insert_id($this->conn);

        // Create regular test user
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Regular', 'User', 'regular.test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->regularUserId = mysqli_insert_id($this->conn);
    }

    public function testAdminPrivileges(): void
    {
        // Verify admin status
        $query = "SELECT isAdmin FROM user WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->adminId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        $this->assertEquals(1, $admin['isAdmin']);

        // Verify regular user status
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->regularUserId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $regular = mysqli_fetch_assoc($result);

        $this->assertEquals(0, $regular['isAdmin']);
    }

    public function testProductManagement(): void
    {
        // Test adding a product (admin function)
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        $productname = "Admin Test Product";
        $price = 199.99;
        $description = "Test Description";
        $availableunit = 50;
        $item = "electronics";
        $image = "test.jpg";
        
        mysqli_stmt_bind_param($stmt, 'sdsiis', $productname, $price, $description, $availableunit, $item, $image);
        $this->assertTrue(mysqli_stmt_execute($stmt));
        
        $productId = mysqli_insert_id($this->conn);
        
        // Test updating product
        $query = "UPDATE products SET price = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        $newPrice = 299.99;
        mysqli_stmt_bind_param($stmt, 'di', $newPrice, $productId);
        $this->assertTrue(mysqli_stmt_execute($stmt));
        
        // Verify update
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        
        $this->assertEquals($newPrice, $product['price']);
        
        // Clean up
        mysqli_query($this->conn, "DELETE FROM products WHERE id = $productId");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up
            mysqli_query($this->conn, "DELETE FROM user WHERE id IN ({$this->adminId}, {$this->regularUserId})");
            mysqli_close($this->conn);
        }
    }
} 