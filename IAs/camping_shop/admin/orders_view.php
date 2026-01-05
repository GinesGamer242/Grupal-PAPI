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

// Retrieve order information
$order_id = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT o.*, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");

$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Stop execution if order does not exist
if (!$order)
{
    die("Order not found.");
}

// Retrieve items belonging to the order
$items_stmt = $pdo->prepare("
    SELECT oi.*, i.name 
    FROM order_items oi
    JOIN items i ON oi.item_id = i.id
    WHERE oi.order_id = ?
");

$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
</head>
<body>

<h1>Order #<?= $order['id'] ?></h1>

<p><strong>User:</strong> <?= htmlspecialchars($order['email']) ?></p>
<p><strong>Total:</strong> €<?= number_format($order['total'], 2) ?></p>
<p><strong>Status:</strong> <?= $order['status'] ?></p>
<p><strong>Date:</strong> <?= $order['created_at'] ?></p>

<h2>Items</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>Item</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Shipping</th>
    </tr>

    <?php foreach ($order_items as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['name']) ?></td>
            <td><?= $i['quantity'] ?></td>
            <td>€<?= number_format($i['price'], 2) ?></td>
            <td>€<?= number_format($i['shipping_cost'], 2) ?></td>
        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>