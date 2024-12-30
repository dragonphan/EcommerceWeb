<?php

use PHPUnit\Framework\TestCase;

class ProductManagementIntegrationTest extends TestCase
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

        // Create test admin
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Admin', 'Test', 'admin.integration@test.com', '1234567890', 'Test Address', ?, 1)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->adminId = mysqli_insert_id($this->conn);
    }

    public function testProductLifecycle(): void
    {
        // Create product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        
        $productname = "Test Product";
        $price = 99.99;
        $description = "Test Description";
        $availableunit = 100;
        $category = "clothes";
        $image = "test.png";
        
        mysqli_stmt_bind_param($stmt, 'sdsiis', $productname, $price, $description, $availableunit, $category, $image);
        mysqli_stmt_execute($stmt);
        $this->productId = mysqli_insert_id($this->conn);

        // Update product
        $newPrice = 149.99;
        $newQuantity = 75;
        
        $query = "UPDATE products SET price = ?, availableunit = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'dii', $newPrice, $newQuantity, $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify updates
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        $this->assertEquals($newPrice, $product['price'], "Price should be updated");
        $this->assertEquals($newQuantity, $product['availableunit'], "Quantity should be updated");

        // Delete product
        $query = "DELETE FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify deletion
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $this->assertEquals(0, mysqli_num_rows($result), "Product should be deleted");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_query($this->conn, "DELETE FROM products WHERE id = {$this->productId}");
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->adminId}");
            mysqli_close($this->conn);
        }
    }
} 