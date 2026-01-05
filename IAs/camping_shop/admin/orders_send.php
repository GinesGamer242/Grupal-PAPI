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

require __DIR__ . '/../config/conn.php';

// Update order status to sent
$orderId = (int)$_POST['id'];

$stmt = $pdo->prepare("UPDATE orders SET status = 'sent' WHERE id = ?");
$stmt->execute([$orderId]);

header("Location: orders.php?msg=sent");
exit;

?>