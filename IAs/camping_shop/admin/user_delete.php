<?php

session_start();

if (empty($_SESSION['user']))
{
    header("Location: ../public/login.php");
    exit;
}
else if (!$_SESSION['user']['is_admin'])
{
    header("Location: ../public/index.php");
    exit;
}

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

// Delete user by ID
$user_id = intval($_GET['id']);

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

header("Location: users.php");
exit;

?>