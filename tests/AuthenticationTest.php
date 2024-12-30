<?php

use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    private $conn;
    private $testUserId;
    private $testAdminId;

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

        // Create test regular user
        $password = md5('123456');
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Test', 'User', 'test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->testUserId = mysqli_insert_id($this->conn);

        // Create test admin user
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Admin', 'Test', 'admin@test.com', '1234567890', 'Test Address', ?, 1)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->testAdminId = mysqli_insert_id($this->conn);
    }

    public function testUserLogin(): void
    {
        // Test user login with correct credentials
        $email = 'test@test.com';
        $password = '123456';
        $password_md5 = md5($password);

        $query = "SELECT * FROM user WHERE email=? AND password=?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $email, $password_md5);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Login should succeed with correct credentials");

        // Test with incorrect password
        $wrong_password = md5('wrongpassword');
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $email, $wrong_password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "Login should fail with incorrect password");
    }

    public function testAdminPrivileges(): void
    {
        // Test admin user privileges
        $query = "SELECT * FROM user WHERE email=? AND isAdmin=1";
        $stmt = mysqli_prepare($this->conn, $query);
        $admin_email = 'admin@test.com';
        mysqli_stmt_bind_param($stmt, 's', $admin_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Admin user should have admin privileges");

        // Verify regular user does not have admin privileges
        $query = "SELECT * FROM user WHERE email=? AND isAdmin=1";
        $stmt = mysqli_prepare($this->conn, $query);
        $user_email = 'test@test.com';
        mysqli_stmt_bind_param($stmt, 's', $user_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "Regular user should not have admin privileges");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up test users
            if ($this->testUserId) {
                mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->testUserId}");
            }
            if ($this->testAdminId) {
                mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->testAdminId}");
            }
            mysqli_close($this->conn);
        }
    }
} 