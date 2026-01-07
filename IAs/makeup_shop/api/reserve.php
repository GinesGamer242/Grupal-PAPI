<?php
require __DIR__ . '/../config.php';

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
    // 🔒 IMPORTANTE: lanzar excepciones reales
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    // Bloquear fila del producto para evitar race conditions
    $stmt = $pdo->prepare(
        "SELECT stock FROM products WHERE id = ? FOR UPDATE"
    );
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'error' => 'Product not found']);
        exit;
    }

    if ((int)$product['stock'] < $qty) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'error' => 'Insufficient stock']);
        exit;
    }

    // Restar stock
    $stmt = $pdo->prepare(
        "UPDATE products SET stock = stock - ? WHERE id = ?"
    );
    $stmt->execute([$qty, $productId]);

    // 🔍 Comprobación extra (opcional pero recomendable)
    if ($stmt->rowCount() !== 1) {
        throw new Exception('Stock update failed');
    }

    $pdo->commit();

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'ok' => false,
        'error' => 'DB error',
        'exception' => $e->getMessage()
    ]);
}
