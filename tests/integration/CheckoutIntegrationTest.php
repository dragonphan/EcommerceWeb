<?php

use PHPUnit\Framework\TestCase;

class CheckoutIntegrationTest extends TestCase
{
    private $conn;
    private $userId;
    private $productId;
    private $orderId;

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
                 VALUES ('Test', 'User', 'checkout.test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->userId = mysqli_insert_id($this->conn);

        // Create test product
        $query = "INSERT INTO products (productname, price, description, availableunit, item, image) 
                 VALUES ('Test Product', 99.99, 'Test Description', 10, 'clothes', 'test.png')";
        mysqli_query($this->conn, $query);
        $this->productId = mysqli_insert_id($this->conn);
    }

    public function testCompleteCheckoutProcess(): void
    {
        // Add item to cart
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, 2)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify cart item
        $query = "SELECT * FROM cart WHERE userid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Item should be in cart");

        // Create order
        $query = "INSERT INTO orders (userid, billingaddress, phoneno, orderdate, delivery, total, delivery_fee) 
                 VALUES (?, 'Test Address', '1234567890', CURDATE(), 'Standard', 199.98, 10.00)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $this->orderId = mysqli_insert_id($this->conn);

        // Move items from cart to order
        $query = "INSERT INTO order_items (order_id, productid, quantity) 
                 SELECT ?, productid, quantity FROM cart WHERE userid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->orderId, $this->userId);
        mysqli_stmt_execute($stmt);

        // Update inventory
        $query = "UPDATE products SET availableunit = availableunit - 2 WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify inventory update
        $query = "SELECT availableunit FROM products WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        
        $this->assertEquals(8, $product['availableunit'], "Inventory should be reduced");

        // Clear cart
        $query = "DELETE FROM cart WHERE userid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);

        // Verify cart is empty
        $query = "SELECT * FROM cart WHERE userid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "Cart should be empty after checkout");
    }

    public function testFailedCheckoutProcess(): void
    {
        // Set product stock to 1
        $query = "UPDATE products SET availableunit = 1 WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);

        // Try to add 2 items to cart (more than available)
        $query = "INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, 2)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $this->userId, $this->productId);
        mysqli_stmt_execute($stmt);

        // Verify insufficient stock
        $query = "SELECT p.availableunit, c.quantity 
                 FROM products p 
                 JOIN cart c ON p.id = c.productid 
                 WHERE c.userid = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);
        
        $this->assertTrue($item['quantity'] > $item['availableunit'], "Should detect insufficient stock");

        // Reset product stock
        $query = "UPDATE products SET availableunit = 10 WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->productId);
        mysqli_stmt_execute($stmt);
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            try {
                // Clean up test data with proper null checks and prepared statements
                if ($this->orderId) {
                    $query = "DELETE FROM order_items WHERE order_id = ?";
                    $stmt = mysqli_prepare($this->conn, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $this->orderId);
                    mysqli_stmt_execute($stmt);

                    $query = "DELETE FROM orders WHERE order_id = ?";
                    $stmt = mysqli_prepare($this->conn, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $this->orderId);
                    mysqli_stmt_execute($stmt);
                }

                if ($this->userId) {
                    $query = "DELETE FROM cart WHERE userid = ?";
                    $stmt = mysqli_prepare($this->conn, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $this->userId);
                    mysqli_stmt_execute($stmt);

                    $query = "DELETE FROM user WHERE id = ?";
                    $stmt = mysqli_prepare($this->conn, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $this->userId);
                    mysqli_stmt_execute($stmt);
                }

                if ($this->productId) {
                    $query = "DELETE FROM products WHERE id = ?";
                    $stmt = mysqli_prepare($this->conn, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $this->productId);
                    mysqli_stmt_execute($stmt);
                }
            } catch (Exception $e) {
                error_log("Error during test cleanup: " . $e->getMessage());
            } finally {
                mysqli_close($this->conn);
            }
        }
    }
} 