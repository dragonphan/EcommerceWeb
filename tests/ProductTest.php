<?php

use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private $conn;
    private $productId;

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

        // Insert a test product into the products table
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                  VALUES ('Test Product', 99.99, 'Test Description', 100, 'clothes', 'test.png')";
        $result = mysqli_query($this->conn, $query);

        // Ensure the query succeeded and retrieve the inserted product ID
        $this->productId = mysqli_insert_id($this->conn);
        $this->assertGreaterThan(0, $this->productId, "Product ID should be greater than 0.");
    }

    public function testProductRetrieval(): void
    {
        // Verify product retrieval from the database
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        // Ensure the product exists in the database
        $this->assertNotNull($product, "Product should exist in the database.");
        $this->assertEquals('Test Product', $product['productname']);
        $this->assertEquals(99.99, $product['price']);
        $this->assertEquals('Test Description', $product['description']);
        $this->assertEquals(100, $product['availableunit']);
        $this->assertEquals('clothes', $product['item']);
        $this->assertEquals('test.png', $product['image']);
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up test data
            mysqli_query($this->conn, "DELETE FROM products WHERE id = {$this->productId}");
            mysqli_close($this->conn);
        }
    }
}
