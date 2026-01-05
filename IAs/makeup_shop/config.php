<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = new PDO(
    "mysql:host=localhost;dbname=ecommerce;charset=utf8",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);


function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: index.php?action=login");
        exit;
    }
}

function require_admin() {
    require_login();
    if (!$_SESSION["is_admin"]) {
        die("Access denied");
    }
}
?>