<?php

use PHPUnit\Framework\TestCase;

class InventoryTest extends TestCase
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

        // Create test product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES ('Inventory Test Product', 149.99, 'Test Description', 100, 'electronics', 'test.png')";
        mysqli_query($this->conn, $query);
        $this->productId = mysqli_insert_id($this->conn);
    }

    public function testUpdateInventory(): void
    {
        // Update available units
        $query = "UPDATE products SET availableunit = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        $newUnits = 75;
        mysqli_stmt_bind_param($stmt, 'ii', $newUnits, $this->productId);
        
        $this->assertTrue(mysqli_stmt_execute($stmt));

        // Verify update
        $query = "SELECT availableunit FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        $this->assertEquals($newUnits, $product['availableunit']);
    }

    public function testLowInventoryAlert(): void
    {
        // Set low inventory threshold
        $lowInventoryThreshold = 10;

        // Update product to low inventory
        $query = "UPDATE products SET availableunit = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        $lowUnits = 5;
        mysqli_stmt_bind_param($stmt, 'ii', $lowUnits, $this->productId);
        mysqli_stmt_execute($stmt);

        // Check for low inventory
        $query = "SELECT * FROM products WHERE availableunit < ? AND id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $lowInventoryThreshold, $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $this->assertEquals(1, mysqli_num_rows($result), "Should detect low inventory");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up
            mysqli_query($this->conn, "DELETE FROM products WHERE id = {$this->productId}");
            mysqli_close($this->conn);
        }
    }
} 