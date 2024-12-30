<?php

use PHPUnit\Framework\TestCase;

class UserSessionIntegrationTest extends TestCase
{
    private $conn;
    private $userId;
    private $userEmail = 'session.test@test.com';
    private $userPassword = '123456';

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
                 VALUES ('Session', 'Test', ?, '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5($this->userPassword);
        mysqli_stmt_bind_param($stmt, 'ss', $this->userEmail, $password);
        mysqli_stmt_execute($stmt);
        $this->userId = mysqli_insert_id($this->conn);
    }

    public function testUserSessionFlow(): void
    {
        // Test login
        $query = "SELECT * FROM user WHERE email = ? AND password = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5($this->userPassword);
        mysqli_stmt_bind_param($stmt, 'ss', $this->userEmail, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $this->assertEquals(1, mysqli_num_rows($result), "Login should succeed");
        
        $userData = mysqli_fetch_assoc($result);
        
        // Simulate session data
        $_SESSION['user_login'] = $userData['id'];
        
        // Test profile update
        $newPhone = '9876543210';
        $query = "UPDATE user SET phoneno = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $newPhone, $_SESSION['user_login']);
        mysqli_stmt_execute($stmt);

        // Verify profile update
        $query = "SELECT phoneno FROM user WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_login']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        $this->assertEquals($newPhone, $user['phoneno'], "Profile should be updated");

        // Simulate logout
        unset($_SESSION['user_login']);
        $this->assertFalse(isset($_SESSION['user_login']), "Session should be destroyed");
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->userId}");
            mysqli_close($this->conn);
        }
    }
} 