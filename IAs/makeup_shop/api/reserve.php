<?php

require __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$productId = (int)($data['product_id'] ?? 0);
$quantity  = (int)($data['quantity'] ?? 0);
$userId    = (int)($data['user_id'] ?? 0);

if ($productId <= 0 || $quantity <= 0 || $userId <= 0) {
    echo json_encode([
        'error' => 'Invalid data'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    // comprobar usuario
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        $pdo->rollBack();
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // bloquear producto y comprobar stock
    $stmt = $pdo->prepare(
        "SELECT id, name, price, stock 
         FROM products 
         WHERE id = ? 
         FOR UPDATE"
    );
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product || $product['stock'] < $quantity) {
        $pdo->rollBack();
        echo json_encode([
            'error' => 'Insufficient stock'
        ]);
        exit;
    }

    // reducir stock
    $stmt = $pdo->prepare(
        "UPDATE products 
         SET stock = stock - ? 
         WHERE id = ?"
    );
    $stmt->execute([$quantity, $productId]);

    // crear pedido (status = pending)
    $items = [
        [
            "product_id" => $productId,
            "name" => $product['name'],
            "price" => (float)$product['price'],
            "quantity" => $quantity
        ]
    ];

    $total = $product['price'] * $quantity;

    $stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, items, status, total, created_at)
         VALUES (?, ?, 'pending', ?, NOW())"
    );
    $stmt->execute([
        $userId,
        json_encode($items),
        $total
    ]);

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'order_id' => (int)$pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'error' => 'DB error'
    ]);
}
