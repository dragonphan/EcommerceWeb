<?php

use PHPUnit\Framework\TestCase;

class UserRegistrationTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Load environment variables
        $db_host = getenv('DB_HOST') ?: 'localhost';
        $db_user = getenv('DB_USER') ?: 'root';
        $db_pass = getenv('DB_PASS') ?: '123';
        $db_name = getenv('DB_NAME') ?: 'assgroup_test';

        // Set up database connection for testing
        $this->conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
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
        $this->assertTrue($result);

        // Verify user exists in database
        $email = mysqli_real_escape_string($this->conn, $userData['email']);
        $query = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($this->conn, $query);
        $user = mysqli_fetch_assoc($result);

        // Assert user data was saved correctly
        $this->assertGreaterThan(0, mysqli_num_rows($result));  // Verify user exists
        $this->assertEquals($userData['firstname'], $user['firstname']);
        $this->assertEquals($userData['lastname'], $user['lastname']);
        $this->assertEquals($userData['email'], $user['email']);
        $this->assertEquals($userData['phoneno'], $user['phoneno']);
        $this->assertEquals($userData['address'], $user['address']);

        // Verify password is stored correctly using password hashing
        $this->assertTrue(password_verify($userData['password'], $user['password']));
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
        $this->assertFalse($result);

        // Verify user doesn't exist in database
        $email = mysqli_real_escape_string($this->conn, $userData['email']);
        $query = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($this->conn, $query);

        // Assert user was not created
        $this->assertEquals(0, mysqli_num_rows($result));
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

        // Hash password securely
        $password = password_hash($userData['password'], PASSWORD_DEFAULT);

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

    protected function cleanUpDatabase()
    {
        // Ensure no conflicting test users are left over
        mysqli_query($this->conn, "DELETE FROM user WHERE email LIKE '%@example.com'");
    }

    protected function tearDown(): void
    {
        // Clean up test data after the test
        $this->cleanUpDatabase();
        mysqli_close($this->conn);
    }
}
