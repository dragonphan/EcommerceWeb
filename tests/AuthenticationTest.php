<?php

use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    private $conn;
    private $testUserId;
    private $testUserEmail = 'test.user@test.com';
    private $adminEmail = 'admin@gmail.com';
    private $adminPassword = '123';

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

        // Clean up any existing test users (except admin)
        mysqli_query($this->conn, "DELETE FROM user WHERE email = '{$this->testUserEmail}'");

        // Create test regular user
        $password = md5('123456');
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Test', 'User', ?, '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $this->testUserEmail, $password);
        mysqli_stmt_execute($stmt);
        $this->testUserId = mysqli_insert_id($this->conn);
    }

    public function testUserLogin(): void
    {
        // Test user login with correct credentials
        $password = '123456';
        $password_md5 = md5($password);

        $query = "SELECT * FROM user WHERE email=? AND password=?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $this->testUserEmail, $password_md5);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Login should succeed with correct credentials");

        // Test with incorrect password
        $wrong_password = md5('wrongpassword');
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $this->testUserEmail, $wrong_password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "Login should fail with incorrect password");
    }

    public function testAdminLogin(): void
    {
        // Test admin login with correct credentials
        $admin_password_md5 = md5($this->adminPassword);
        
        $query = "SELECT * FROM user WHERE email=? AND password=? AND isAdmin=1";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $this->adminEmail, $admin_password_md5);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Admin login should succeed with correct credentials");
    }

    public function testAdminPrivileges(): void
    {
        // Verify admin has admin privileges
        $query = "SELECT * FROM user WHERE email=? AND isAdmin=1";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $this->adminEmail);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Admin user should have admin privileges");

        // Verify regular user does not have admin privileges
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $this->testUserEmail);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "Regular user should not have admin privileges");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up test user only (don't remove admin)
            mysqli_query($this->conn, "DELETE FROM user WHERE email = '{$this->testUserEmail}'");
            mysqli_close($this->conn);
        }
    }
} 