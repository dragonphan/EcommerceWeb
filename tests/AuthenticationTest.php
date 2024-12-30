<?php

use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    private $conn;

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
    }

    public function testAdminPrivileges(): void
    {
        // Test admin user privileges
        $query = "SELECT * FROM user WHERE email='admin@test.com' AND isAdmin=1";
        $result = mysqli_query($this->conn, $query);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Admin user should have admin privileges");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
} 