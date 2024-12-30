<?php

use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    private $conn;
    private $userId;
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

        // Create test user
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Cart', 'Test', 'cart.test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        if (!mysqli_stmt_execute($stmt)) {
            $this->fail("Failed to create test user: " . mysqli_error($this->conn));
        }
        $this->userId = mysqli_insert_id($this->conn);

        // Create test product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES ('Cart Test Product', 99.99, 'Test Description', 100, 'clothes', 'test.png')";
        if (!mysqli_query($this->conn, $query)) {
            $this->fail("Failed to create test product: " . mysqli_error($this->conn));
        }
        $this->productId = mysqli_insert_id($this->conn);
    }

    public function testAddToCart(): void
    {
        // Add item to cart
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        $quantity = 2;
        mysqli_stmt_bind_param($stmt, 'iii', $this->userId, $this->productId, $quantity);
        
        $this->assertTrue(mysqli_stmt_execute($stmt), "Failed to add item to cart.");
        $cartId = mysqli_insert_id($this->conn);
        $this->assertGreaterThan(0, $cartId, "Cart ID should be greater than 0.");

        // Verify cart item
        $query = "SELECT * FROM cart WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $cartId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cartItem = mysqli_fetch_assoc($result);

        $this->assertNotNull($cartItem, "Cart item should not be null.");
        $this->assertEquals($this->userId, $cartItem['userid']);
        $this->assertEquals($this->productId, $cartItem['productid']);
        $this->assertEquals(2, $cartItem['quantity']);
    }

    public function testUpdateCartQuantity(): void
    {
        // First add item to cart
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        $quantity = 1;
        mysqli_stmt_bind_param($stmt, 'iii', $this->userId, $this->productId, $quantity);
        mysqli_stmt_execute($stmt);
        $cartId = mysqli_insert_id($this->conn);

        // Update quantity
        $query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        $newQuantity = 3;
        mysqli_stmt_bind_param($stmt, 'ii', $newQuantity, $cartId);
        
        $this->assertTrue(mysqli_stmt_execute($stmt), "Failed to update cart quantity.");

        // Verify updated quantity
        $query = "SELECT quantity FROM cart WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $cartId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cartItem = mysqli_fetch_assoc($result);

        $this->assertNotNull($cartItem, "Cart item should not be null.");
        $this->assertEquals(3, $cartItem['quantity']);
    }

    public function testRemoveFromCart(): void
    {
        // Add item to cart
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        $quantity = 1;
        mysqli_stmt_bind_param($stmt, 'iii', $this->userId, $this->productId, $quantity);
        mysqli_stmt_execute($stmt);
        $cartId = mysqli_insert_id($this->conn);

        // Remove item
        $query = "DELETE FROM cart WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $cartId);
        
        $this->assertTrue(mysqli_stmt_execute($stmt), "Failed to remove item from cart.");

        // Verify item is removed
        $query = "SELECT * FROM cart WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $cartId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $this->assertEquals(0, mysqli_num_rows($result), "Cart item should be removed.");
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
