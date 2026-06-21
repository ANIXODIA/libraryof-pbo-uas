<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting test...\n";

try {
    include 'database.php';
    echo "Database included successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Testing connection...\n";
if ($connection->connect_error) {
    echo "Connection failed: " . $connection->connect_error;
} else {
    echo "Connection successful\n";
}

echo "Test complete.";
?>
