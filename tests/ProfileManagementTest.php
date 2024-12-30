<?php

use PHPUnit\Framework\TestCase;

class ProfileManagementTest extends TestCase
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
        
        // Create test user
        $query = "INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) 
                 VALUES ('Test', 'User', 'profile.test@test.com', '1234567890', 'Test Address', ?, 0)";
        $stmt = mysqli_prepare($this->conn, $query);
        $password = md5('123456');
        mysqli_stmt_bind_param($stmt, 's', $password);
        mysqli_stmt_execute($stmt);
        $this->userId = mysqli_insert_id($this->conn);
    }

    public function testProfileUpdate(): void
    {
        // Test profile update
        $newPhone = '9876543210';
        $newAddress = 'New Test Address';
        
        $query = "UPDATE user SET phoneno = ?, address = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $newPhone, $newAddress, $this->userId);
        mysqli_stmt_execute($stmt);

        // Verify updates
        $query = "SELECT * FROM user WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $this->userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        $this->assertEquals($newPhone, $user['phoneno']);
        $this->assertEquals($newAddress, $user['address']);
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            mysqli_query($this->conn, "DELETE FROM user WHERE id = {$this->userId}");
            mysqli_close($this->conn);
        }
    }
} 