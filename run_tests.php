<?php
require_once 'config.php';

class CartTest {
    private $conn;
    private $testUserId;
    private $testProductId;
    
    public function __construct() {
        $this->conn = mysqli_connect("localhost", "root", "", "assgroup_test");
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $this->setupTestData();
    }

    private function setupTestData() {
        // Create test user
        $email = "test.cart@example.com";
        mysqli_query($this->conn, "DELETE FROM user WHERE email='$email'");
        mysqli_query($this->conn, 
            "INSERT INTO user (firstname, lastname, email, password, phoneno, address) 
             VALUES ('Test', 'Cart', '$email', '" . md5('password123') . "', '1234567890', 'Test Address')"
        );
        $this->testUserId = mysqli_insert_id($this->conn);

        // Create test product
        mysqli_query($this->conn, 
            "INSERT INTO products (productname, price, description) 
             VALUES ('Test Product', 99.99, 'Test Description')"
        );
        $this->testProductId = mysqli_insert_id($this->conn);
    }

    public function testAddToCart() {
        echo "\nTesting Add to Cart:\n";

        // Test adding item to cart
        $query = "INSERT INTO cart (userid, productid, quantity) 
                 VALUES ({$this->testUserId}, {$this->testProductId}, 1)";
        
        if (mysqli_query($this->conn, $query)) {
            echo "? Added item to cart successfully\n";
            
            // Verify cart item exists
            $result = mysqli_query($this->conn, 
                "SELECT * FROM cart WHERE userid={$this->testUserId} AND productid={$this->testProductId}"
            );
            
            if (mysqli_fetch_assoc($result)) {
                echo "? Cart item verification successful\n";
            } else {
                echo "? Cart item verification failed\n";
            }
        } else {
            echo "? Failed to add item to cart\n";
        }
    }

    public function testUpdateCartQuantity() {
        echo "\nTesting Update Cart Quantity:\n";

        // Update quantity
        $query = "UPDATE cart SET quantity = 2 
                 WHERE userid={$this->testUserId} AND productid={$this->testProductId}";
        
        if (mysqli_query($this->conn, $query)) {
            echo "? Updated cart quantity successfully\n";
            
            // Verify quantity update
            $result = mysqli_query($this->conn, 
                "SELECT quantity FROM cart WHERE userid={$this->testUserId} AND productid={$this->testProductId}"
            );
            $cart_item = mysqli_fetch_assoc($result);
            
            if ($cart_item && $cart_item['quantity'] == 2) {
                echo "? Quantity update verification successful\n";
            } else {
                echo "? Quantity update verification failed\n";
            }
        } else {
            echo "? Failed to update cart quantity\n";
        }
    }

    public function testRemoveFromCart() {
        echo "\nTesting Remove from Cart:\n";

        // Remove item from cart
        $query = "DELETE FROM cart 
                 WHERE userid={$this->testUserId} AND productid={$this->testProductId}";
        
        if (mysqli_query($this->conn, $query)) {
            echo "? Removed item from cart successfully\n";
            
            // Verify item was removed
            $result = mysqli_query($this->conn, 
                "SELECT * FROM cart WHERE userid={$this->testUserId} AND productid={$this->testProductId}"
            );
            
            if (mysqli_num_rows($result) == 0) {
                echo "? Cart item removal verification successful\n";
            } else {
                echo "? Cart item removal verification failed\n";
            }
        } else {
            echo "? Failed to remove item from cart\n";
        }
    }

    public function __destruct() {
        // Clean up test data
        mysqli_query($this->conn, "DELETE FROM cart WHERE userid={$this->testUserId}");
        mysqli_query($this->conn, "DELETE FROM products WHERE id={$this->testProductId}");
        mysqli_query($this->conn, "DELETE FROM user WHERE id={$this->testUserId}");
        mysqli_close($this->conn);
    }
}