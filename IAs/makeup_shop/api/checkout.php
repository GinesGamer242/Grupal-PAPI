<?php

require __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$userId = (int)($data['user_id'] ?? 0);
$items  = $data['items'] ?? [];
$shippingAddress = $data['shipping_address'] ?? null;

if ($userId <= 0 || empty($items)) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        throw new Exception("User not found");
    }

    $total = 0;
    $orderItems = [];

    foreach ($items as $item) {
        if (!isset($item['product_id'], $item['quantity'])) {
            throw new Exception("Invalid item format");
        }

        $productId = (int)$item['product_id'];
        $quantity  = (int)$item['quantity'];

        $stmt = $pdo->prepare("
            SELECT name, price, shipping_cost 
            FROM products 
            WHERE id = ?
        ");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found");
        }

        $lineTotal = ($product['price'] + $product['shipping_cost']) * $quantity;
        $total += $lineTotal;

        $orderItems[] = [
            "product_id" => $productId,
            "name" => $product['name'],
            "price" => (float)$product['price'],
            "shipping_cost" => (float)$product['shipping_cost'],
            "quantity" => $quantity
        ];
    }

    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, items, status, total, shipping_address, created_at)
        VALUES (?, ?, 'sent', ?, ?, NOW())
    ");

    $stmt->execute([
        $userId,
        json_encode($orderItems),
        $total,
        $shippingAddress
    ]);

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'order_id' => (int)$pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'error' => 'Checkout failed',
        'details' => $e->getMessage()
    ]);
}
