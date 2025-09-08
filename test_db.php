<?php
// Simple test to check database connection and tables
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'opensis';

echo "DB_HOST: " . $host . "\n";
echo "DB_USER: " . $user . "\n";
echo "DB_NAME: " . $dbname . "\n";
echo "DB_PASS exists: " . (empty($pass) ? "NO" : "YES") . "\n\n";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    echo "Database connection successful!\n";
    
    // Check for key OpenSIS tables
    $tables = ['app', 'staff', 'students', 'schools'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "Table '$table' exists\n";
        } else {
            echo "Table '$table' does NOT exist\n";
        }
    }
    
    // If app table exists, check for version info
    $result = $conn->query("SHOW TABLES LIKE 'app'");
    if ($result && $result->num_rows > 0) {
        $version_result = $conn->query("SELECT name, value FROM app WHERE name IN ('version', 'build') LIMIT 5");
        if ($version_result && $version_result->num_rows > 0) {
            echo "\nApp configuration:\n";
            while ($row = $version_result->fetch_assoc()) {
                echo $row['name'] . ": " . $row['value'] . "\n";
            }
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>