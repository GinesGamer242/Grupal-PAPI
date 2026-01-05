<?php

require __DIR__ . '/../config/conn.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$mseUser = (int)($data['mse_user_id'] ?? 0);
$items = $data['items'] ?? [];

if ($mseUser <= 0 || empty($items))
{
    echo json_encode(['ok' => false]);
    exit;
}

$pdo->beginTransaction();

$stmt = $pdo->prepare("
    INSERT INTO orders (user_id, total, status)
    VALUES (?, 0, 'paid')
");

$stmt->execute([$mseUser]);
$orderId = $pdo->lastInsertId();

$total = 0;

foreach ($items as $i)
{
    $stmt = $pdo->prepare("
        SELECT price, shipping_cost FROM items WHERE id = ?
    ");

    $stmt->execute([$i['product_id']]);
    $p = $stmt->fetch();

    $line = ($p['price'] + $p['shipping_cost']) * $i['quantity'];
    $total += $line;

    $pdo->prepare("
        INSERT INTO order_items
        (order_id, item_id, quantity, price, shipping_cost)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([
        $orderId,
        $i['product_id'],
        $i['quantity'],
        $p['price'],
        $p['shipping_cost']
    ]);
}

$pdo->prepare(
    "UPDATE orders SET total = ? WHERE id = ?"
)->execute([$total, $orderId]);

$pdo->commit();

echo json_encode(['ok' => true]);

?>