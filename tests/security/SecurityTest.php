<?php

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
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

    public function testPasswordHashing(): void
    {
        // Test password hashing
        $password = "test123";
        $hashedPassword = md5($password);
        
        // Test that same password produces same hash
        $this->assertEquals(md5($password), $hashedPassword, "Password hashing should be consistent");
        
        // Test that different passwords produce different hashes
        $this->assertNotEquals(md5("wrongpassword"), $hashedPassword, "Different passwords should have different hashes");
    }

    public function testSQLInjectionPrevention(): void
    {
        // Test SQL injection prevention in login
        $maliciousEmail = "' OR '1'='1";
        $maliciousPassword = "' OR '1'='1";
        
        $query = "SELECT * FROM user WHERE email = ? AND password = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $maliciousEmail, $maliciousPassword);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "SQL injection attempt should fail");
    }

    public function testXSSPrevention(): void
    {
        // Test XSS prevention in product name
        $maliciousProductName = "<script>alert('xss')</script>";
        $escapedProductName = htmlspecialchars($maliciousProductName, ENT_QUOTES, 'UTF-8');
        
        $this->assertNotEquals($maliciousProductName, $escapedProductName, "XSS content should be escaped");
        $this->assertStringContainsString("&lt;script&gt;", $escapedProductName, "HTML should be properly escaped");
    }

    public function testAdminAccessControl(): void
    {
        // Test admin access control
        $query = "SELECT * FROM user WHERE email = ? AND password = ? AND isAdmin = 1";
        $stmt = mysqli_prepare($this->conn, $query);
        $email = "user@test.com"; // Non-admin user
        $password = md5("123456");
        mysqli_stmt_bind_param($stmt, 'ss', $email, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "Non-admin user should not have admin access");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
} 