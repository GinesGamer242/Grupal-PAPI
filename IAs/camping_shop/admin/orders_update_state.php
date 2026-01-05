<?php

session_start();

// Only allow access to authenticated administrators
if (empty($_SESSION['user']) || !$_SESSION['user']['is_admin'])
{
    header("Location: ../public/login.php");
    exit;
}

// Validate incoming order ID
if (!isset($_POST['id']))
{
    die("No order ID provided");
}

$id = (int) $_POST['id'];
$state = isset($_POST['state']) && $_POST['state'] !== "" ? $_POST['state'] : null;

require __DIR__ . '/../config/conn.php';

// Update order state
$stmt = $pdo->prepare("UPDATE orders SET state = ? WHERE id = ?");
$stmt->execute([$state, $id]);

header("Location: orders.php");
exit;

?>