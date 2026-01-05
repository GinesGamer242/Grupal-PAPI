<?php
$host = "sql313.infinityfree.com";
$dbname = "if0_40626272_individualTaskii";
$user = "if0_40626272";
$pass = "DLmeYuwDOBgIUXL";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Connection error: " . $e->getMessage());
}
?>
