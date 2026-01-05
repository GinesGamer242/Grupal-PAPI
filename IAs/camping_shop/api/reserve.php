<?php

require __DIR__ . '/../config/conn.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$productId = (int)($data['product_id'] ?? 0);
$qty = (int)($data['quantity'] ?? 0);
$mseUser = (int)($data['mse_user_id'] ?? 0);

if ($productId <= 0 || $qty <= 0 || $mseUser <= 0)
{
    echo json_encode(['ok' => false, 'error' => 'Invalid data']);
    exit;
}

try
{
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT stock FROM items WHERE id = ? FOR UPDATE"
    );

    $stmt->execute([$productId]);
    $item = $stmt->fetch();

    if (!$item || $item['stock'] < $qty)
    {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'error' => 'Insufficient stock']);
        exit;
    }

    $pdo->prepare(
        "UPDATE items SET stock = stock - ? WHERE id = ?"
    )->execute([$qty, $productId]);

    $pdo->commit();

    echo json_encode(['ok' => true]);

}
catch (Exception $e)
{
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => 'DB error']);
}

?>