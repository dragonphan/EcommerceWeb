<?php

use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    private $conn;
    private $productIds = [];

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

        // Create test products
        $testProducts = [
            ['Test Shirt', 'clothes', 'A test shirt'],
            ['Test Pants', 'pants', 'A test pants'],
            ['Test Shoes', 'shoes', 'A test shoes']
        ];

        foreach ($testProducts as $product) {
            $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                     VALUES (?, 99.99, ?, 100, ?, 'test.png')";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, 'sss', $product[0], $product[2], $product[1]);
            mysqli_stmt_execute($stmt);
            $this->productIds[] = mysqli_insert_id($this->conn);
        }
    }

    public function testSearchByKeyword(): void
    {
        // Test search by product name
        $keyword = 'Shirt';
        $query = "SELECT * FROM products WHERE productname LIKE ? OR item LIKE ?";
        $stmt = mysqli_prepare($this->conn, $query);
        $searchTerm = "%$keyword%";
        mysqli_stmt_bind_param($stmt, 'ss', $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Should find one product with 'Shirt' in name");
    }

    public function testSearchByCategory(): void
    {
        // Test search by category
        $category = 'clothes';
        $query = "SELECT * FROM products WHERE item = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $category);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Should find one product in 'clothes' category");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            foreach ($this->productIds as $id) {
                mysqli_query($this->conn, "DELETE FROM products WHERE id = $id");
            }
            mysqli_close($this->conn);
        }
    }
} 