<?php

// Load database configuration values
$config = require __DIR__ . '/db.php';

// Build DSN string for MySQL connection
$dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";

try
{
    // Configure PDO to throw exceptions and fetch associative arrays by default
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}
catch (PDOException $e)
{
    // Stop execution and display connection error message
    die('DB connection error: ' . $e->getMessage());
}

?>