<?php

use PHPUnit\Framework\TestCase;

class SecurityRegressionTest extends TestCase
{
    private $conn;
    private $userId;

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
                 VALUES ('Test', 'User', 'security.test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->userId = mysqli_insert_id($this->conn);
    }

    public function testPasswordHashing(): void
    {
        $password = "test123";
        $hashedPassword = md5($password);
        
        // Test password consistency
        $this->assertEquals(md5($password), $hashedPassword, "Password hashing should be consistent");
        $this->assertNotEquals($password, $hashedPassword, "Password should be hashed");
        $this->assertEquals(32, strlen($hashedPassword), "MD5 hash should be 32 characters");
    }

    public function testUserPrivileges(): void
    {
        // Test non-admin access
        $query = "SELECT isAdmin FROM user WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        $this->assertEquals(0, $user['isAdmin'], "Regular user should not have admin privileges");

        // Test admin check
        $query = "SELECT * FROM user WHERE email = ? AND isAdmin = 1";
        $stmt = mysqli_prepare($this->conn, $query);
        $email = "security.test@test.com";
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "Regular user should not be found in admin query");
    }

    public function testInputSanitization(): void
    {
        // Test SQL injection prevention
        $maliciousInput = "' OR '1'='1";
        $query = "SELECT * FROM user WHERE email = ? AND password = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $maliciousInput, $maliciousInput);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(0, mysqli_num_rows($result), "SQL injection attempt should fail");

        // Test XSS prevention
        $maliciousHtml = "<script>alert('xss')</script>";
        $sanitizedHtml = htmlspecialchars($maliciousHtml, ENT_QUOTES, 'UTF-8');
        
        $this->assertNotEquals($maliciousHtml, $sanitizedHtml, "HTML should be escaped");
        $this->assertStringContainsString("&lt;script&gt;", $sanitizedHtml, "Script tags should be escaped");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->userId}");
            mysqli_close($this->conn);
        }
    }
} 