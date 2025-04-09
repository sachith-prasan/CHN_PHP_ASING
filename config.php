<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Docker MySQL connection settings
$servername = "db";  // service name from docker-compose.yml
$username = "sachith"; // MYSQL_USER from docker-compose.yml
$password = "112233"; // MYSQL_PASSWORD from docker-compose.yml
$dbname = "vps";     // MYSQL_DATABASE from docker-compose.yml

// Create connection with retry mechanism
function connectWithRetry($servername, $username, $password, $dbname, $maxAttempts = 5) {
    $attempt = 1;
    while ($attempt <= $maxAttempts) {
        try {
            $conn = new mysqli($servername, $username, $password, $dbname);
            if (!$conn->connect_error) {
                $conn->set_charset("utf8mb4");
                return $conn;
            }
        } catch (Exception $e) {
            if ($attempt === $maxAttempts) {
                die("Connection failed after {$maxAttempts} attempts: " . $e->getMessage());
            }
            sleep(2); // Wait 2 seconds before retrying
        }
        $attempt++;
    }
}

$conn = connectWithRetry($servername, $username, $password, $dbname);

// Test connection with a simple query
try {
    $test = $conn->query("SELECT 1");
    if ($test === false) {
        throw new Exception("Database connection test failed");
    }
} catch (Exception $e) {
    die("Connection verification failed: " . $e->getMessage());
}
