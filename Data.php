<?php 
$DatabaseType = 'mysqli'; 

// Use environment variables for Cloud Run, with fallbacks for local development
$DatabaseServer = getenv('DB_HOST') ?: 'localhost'; 
$DatabaseUsername = getenv('DB_USER') ?: 'root'; 
$DatabasePassword = getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: ''; 
$DatabaseName = getenv('DB_NAME') ?: 'opensis'; 
$DatabasePort = getenv('DB_PORT') ?: '3306'; 
?>