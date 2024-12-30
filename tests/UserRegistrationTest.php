<?php

use PHPUnit\Framework\TestCase;

class UserRegistrationTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Database configuration from phpunit.xml
        $db_host = getenv('DB_HOST');
        $db_user = getenv('DB_USERNAME');
        $db_pass = getenv('DB_PASSWORD');
        $db_name = getenv('DB_DATABASE');
        
        // Set up database connection for testing
        $this->conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

        if (!$this->conn) {
            $this->fail("Database connection failed: " . mysqli_connect_error());
        }

        // Clean up any existing test data
        $this->cleanUpDatabase();
    }

    protected function cleanUpDatabase(): void
    {
        // Clean up test data but keep the structure
        mysqli_query($this->conn, "DELETE FROM order_items");
        mysqli_query($this->conn, "DELETE FROM orders");
        mysqli_query($this->conn, "DELETE FROM cart");
        mysqli_query($this->conn, "DELETE FROM user WHERE email LIKE '%test.com'");
    }

    public function testUserRegistration(): void
    {
        // Test user registration
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('John', 'Doe', 'john@test.com', '1234567890', 'Test Address', ?, 0)";
        
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        
        $this->assertTrue(mysqli_stmt_execute($stmt));
        $userId = mysqli_insert_id($this->conn);
        $this->assertGreaterThan(0, $userId);

        // Verify user was created correctly
        $query = "SELECT * FROM user WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        $this->assertEquals('John', $user['firstname']);
        $this->assertEquals('Doe', $user['lastname']);
        $this->assertEquals('john@test.com', $user['email']);
        $this->assertEquals($password, $user['password']);
    }

    public function testUserLogin(): void
    {
        // Insert a test user
        $password = md5('123456');
        $email = 'test.login@test.com';
        
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES (?, ?, ?, ?, ?, ?, 0)";
        
        $stmt = mysqli_prepare($this->conn, $query);
        $firstname = 'Test';
        $lastname = 'Login';
        $phone = '1234567890';
        $address = 'Test Address';
        
        mysqli_stmt_bind_param($stmt, 'ssssss', $firstname, $lastname, $email, $phone, $address, $password);
        mysqli_stmt_execute($stmt);

        // Test login with correct credentials
        $query = "SELECT * FROM user WHERE email = ? AND password = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $email, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        $this->assertNotNull($user);
        $this->assertEquals('Test', $user['firstname']);
        
        // Test login with incorrect password
        $wrongPassword = md5('wrongpass');
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $email, $wrongPassword);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result));
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}