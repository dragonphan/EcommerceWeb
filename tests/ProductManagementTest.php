<?php

use PHPUnit\Framework\TestCase;

class ProductManagementTest extends TestCase
{
    private $conn;
    private $adminId;
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

        // Create test admin user
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Admin', 'Test', 'admin.test@test.com', '1234567890', 'Test Address', ?, 1)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->adminId = mysqli_insert_id($this->conn);
    }

    public function testAddProduct(): void
    {
        // Test adding a new product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        $productname = "Test Product";
        $price = 99.99;
        $description = "Test Description";
        $availableunit = 100;
        $item = "clothes";
        $image = "test.png";
        
        mysqli_stmt_bind_param($stmt, 'sdsiis', $productname, $price, $description, $availableunit, $item, $image);
        $this->assertTrue(mysqli_stmt_execute($stmt));
        $this->productId = mysqli_insert_id($this->conn);

        // Verify product was added
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result));
    }

    public function testUpdateProduct(): void
    {
        // First add a product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES ('Test Product', 99.99, 'Test Description', 100, 'clothes', 'test.png')";
        mysqli_query($this->conn, $query);
        $this->productId = mysqli_insert_id($this->conn);

        // Update product
        $newPrice = 149.99;
        $query = "UPDATE products SET price = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'di', $newPrice, $this->productId);
        
        $this->assertTrue(mysqli_stmt_execute($stmt));

        // Verify update
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        
        $this->assertEquals($newPrice, $product['price']);
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up
            mysqli_query($this->conn, "DELETE FROM products WHERE id = {$this->productId}");
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->adminId}");
            mysqli_close($this->conn);
        }
    }
} 