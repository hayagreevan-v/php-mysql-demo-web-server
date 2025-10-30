<?php
// Fetch credentials from environment variables
$host     = getenv('DB_HOST');
$dbname   = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}
echo "✅ Database connection successful!<br>";

// Close connection
mysqli_close($conn);
?>