<?php
require __DIR__ . '/../config/conn.php';
header('Content-Type: application/json');

// --------------------
// 1️⃣ Leer JSON del body
// --------------------
$data = json_decode(file_get_contents("php://input"), true);

$productId = (int)($data['product_id'] ?? 0);
$qty       = (int)($data['quantity'] ?? 0);

if ($productId <= 0 || $qty <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid data']);
    exit;
}

// --------------------
// 2️⃣ Intentar descontar stock (transacción segura)
// --------------------
try {
    $pdo->beginTransaction();

    // Bloquear fila del producto para evitar race conditions
    $stmt = $pdo->prepare("SELECT stock FROM items WHERE id = ? FOR UPDATE");
    $stmt->execute([$productId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'error' => 'Product not found']);
        exit;
    }

    if ($item['stock'] < $qty) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'error' => 'Insufficient stock']);
        exit;
    }

    // Restar stock
    $stmt = $pdo->prepare("UPDATE items SET stock = stock - ? WHERE id = ?");
    $stmt->execute([$qty, $productId]);

    $pdo->commit();

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => 'DB error', 'exception' => $e->getMessage()]);
}
