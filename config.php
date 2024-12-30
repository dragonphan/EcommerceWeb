<?php
// Load environment variables or configuration
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
	$env = parse_ini_file($envFile);
}

// Set default values if .env file is not found
$servername = trim($env['DB_HOST'] ?? "localhost");
$username = trim($env['DB_USER'] ?? "root");  // Default XAMPP username
$password = trim($env['DB_PASSWORD'] ?? "");   // Default XAMPP password (empty)
$dbname = trim($env['DB_NAME'] ?? "assgroup");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}?>
