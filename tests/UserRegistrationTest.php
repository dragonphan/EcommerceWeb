<?php

use PHPUnit\Framework\TestCase;

class UserRegistrationTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Database configuration
        $db_host = '127.0.0.1';  // Use IP instead of 'localhost'
        $db_user = 'root';
        $db_pass = 'root';
        $db_name = 'assgroup_test';
        $db_port = 3306;

        // Set up database connection for testing
        $this->conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

        if (!$this->conn) {
            $this->fail("Database connection failed: " . mysqli_connect_error());
        }

        // Clean up any existing test data
        $this->cleanUpDatabase();
    }

    public function testValidUserRegistration()
    {
        // Test data
        $userData = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'phoneno' => '1234567890',
            'address' => '123 Test St',
            'password' => 'password123'
        ];

        // Test the registration
        $result = $this->registerUser($userData);

        // Assert registration was successful
        $this->assertTrue($result, "User registration failed");

        // Verify user exists in database
        $email = mysqli_real_escape_string($this->conn, $userData['email']);
        $query = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($this->conn, $query);
        
        $this->assertNotFalse($result, "Database query failed");
        $this->assertGreaterThan(0, mysqli_num_rows($result), "User not found in database");

        $user = mysqli_fetch_assoc($result);

        // Assert user data was saved correctly
        $this->assertEquals($userData['firstname'], $user['firstname'], "First name mismatch");
        $this->assertEquals($userData['lastname'], $user['lastname'], "Last name mismatch");
        $this->assertEquals($userData['email'], $user['email'], "Email mismatch");
        $this->assertEquals($userData['phoneno'], $user['phoneno'], "Phone number mismatch");
        $this->assertEquals($userData['address'], $user['address'], "Address mismatch");
        
        // Verify password was hashed
        $this->assertNotEquals($userData['password'], $user['password'], "Password was not hashed");
        $this->assertEquals(32, strlen($user['password']), "Password hash length incorrect"); // MD5 hash length
    }

    public function testInvalidUserRegistration()
    {
        // Test data with missing required fields
        $userData = [
            'firstname' => '',  // Empty firstname
            'lastname' => 'Doe',
            'email' => 'invalid@example.com',
            'phoneno' => '1234567890',
            'address' => '123 Test St',
            'password' => 'password123'
        ];

        // Test the registration
        $result = $this->registerUser($userData);

        // Assert registration failed
        $this->assertFalse($result, "Invalid registration was accepted");

        // Verify user doesn't exist in database
        $email = mysqli_real_escape_string($this->conn, $userData['email']);
        $query = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($this->conn, $query);
        
        $this->assertNotFalse($result, "Database query failed");
        $this->assertEquals(0, mysqli_num_rows($result), "Invalid user was created in database");
    }

    private function registerUser($userData)
    {
        // Validate required fields
        if (empty($userData['firstname']) || 
            empty($userData['lastname']) || 
            empty($userData['email']) || 
            empty($userData['password'])) {
            return false;
        }

        // Hash password using MD5 (as per your existing system)
        $password = md5($userData['password']);

        // Escape strings to prevent SQL injection
        $firstname = mysqli_real_escape_string($this->conn, $userData['firstname']);
        $lastname = mysqli_real_escape_string($this->conn, $userData['lastname']);
        $email = mysqli_real_escape_string($this->conn, $userData['email']);
        $phoneno = mysqli_real_escape_string($this->conn, $userData['phoneno']);
        $address = mysqli_real_escape_string($this->conn, $userData['address']);

        // Insert user into database
        $query = "INSERT INTO user (firstname, lastname, email, password, phoneno, address) 
                 VALUES ('$firstname', '$lastname', '$email', '$password', '$phoneno', '$address')";

        return mysqli_query($this->conn, $query);
    }

    private function cleanUpDatabase()
    {
        // Clean up any existing test data
        mysqli_query($this->conn, "DELETE FROM user WHERE email LIKE '%@example.com'");
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanUpDatabase();

        // Close database connection
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}