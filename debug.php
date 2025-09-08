<?php
// Simple diagnostic script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>\n";
echo "<html><head><title>OpenSIS Debug</title></head><body>\n";
echo "<h1>OpenSIS Diagnostic</h1>\n";

echo "<h2>1. PHP Basic Test</h2>\n";
echo "<p>✅ PHP is working - you can see this message</p>\n";

echo "<h2>2. Environment Variables</h2>\n";
$db_host = getenv('DB_HOST');
$db_user = getenv('DB_USER'); 
$db_pass = getenv('DB_PASS');
$db_name = getenv('DB_NAME');

echo "<p>DB_HOST: " . ($db_host ?: '❌ NOT SET') . "</p>\n";
echo "<p>DB_USER: " . ($db_user ?: '❌ NOT SET') . "</p>\n";
echo "<p>DB_PASS: " . ($db_pass ? '✅ SET' : '❌ NOT SET') . "</p>\n";
echo "<p>DB_NAME: " . ($db_name ?: '❌ NOT SET') . "</p>\n";

echo "<h2>3. File Existence Check</h2>\n";
$files = ['Data.php', 'ConfigInc.php', 'DatabaseInc.php', 'UpgradeInc.php', 'Warehouse.php'];
foreach ($files as $file) {
    $exists = file_exists($file);
    echo "<p>$file: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "</p>\n";
}

echo "<h2>4. Install Directory</h2>\n";
$install_exists = is_dir('install');
echo "<p>install/ directory: " . ($install_exists ? '✅ EXISTS' : '❌ MISSING') . "</p>\n";

echo "<h2>5. Database Connection Test</h2>\n";
if ($db_host && $db_user && $db_name) {
    try {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($conn->connect_error) {
            echo "<p>❌ Database connection failed: " . $conn->connect_error . "</p>\n";
        } else {
            echo "<p>✅ Database connection successful</p>\n";
            
            // Check for key tables
            $tables = ['app', 'staff', 'students', 'schools'];
            echo "<h3>Database Tables:</h3>\n";
            foreach ($tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                $exists = $result && $result->num_rows > 0;
                echo "<p>$table: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "</p>\n";
            }
            
            $conn->close();
        }
    } catch (Exception $e) {
        echo "<p>❌ Database error: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p>❌ Cannot test database - missing environment variables</p>\n";
}

echo "<h2>6. What happens when we include Data.php?</h2>\n";
if (file_exists('Data.php')) {
    try {
        include_once 'Data.php';
        echo "<p>✅ Data.php included successfully</p>\n";
        global $DatabaseServer, $DatabaseUsername, $DatabasePassword, $DatabaseName;
        echo "<p>DatabaseServer from Data.php: " . ($DatabaseServer ?: '❌ NOT SET') . "</p>\n";
        echo "<p>DatabaseUsername from Data.php: " . ($DatabaseUsername ?: '❌ NOT SET') . "</p>\n";
        echo "<p>DatabasePassword from Data.php: " . ($DatabasePassword ? '✅ SET' : '❌ NOT SET') . "</p>\n";
        echo "<p>DatabaseName from Data.php: " . ($DatabaseName ?: '❌ NOT SET') . "</p>\n";
    } catch (Exception $e) {
        echo "<p>❌ Error including Data.php: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p>❌ Data.php does not exist</p>\n";
}

echo "</body></html>";
?>