<?php
require __DIR__ . '/../config/session.php';
require __DIR__ . '/../config/conn.php'; // tu conexión PDO
header('Content-Type: application/json');

// ----------------------------
// 1️⃣ Autenticación
// ----------------------------
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

// ----------------------------
// 2️⃣ Reservar stock en cada tienda
// ----------------------------
$shopsEndpoints = [
    'camping' => '../IAs/camping_shop/api/reserve.php',
    'makeup'  => '../IAs/makeup_shop/api/reserve.php',
    'florist' => '../IAs/florist_shop/api/reserve.php'
];

// Guardar items que vamos a insertar en DB
$cartForDB = [];

foreach ($cart as $item) {
    $shop = $item['shop'];
    if (!isset($shopsEndpoints[$shop])) {
        echo json_encode(['error' => "Unknown shop: $shop"]);
        exit;
    }

    // Preparar payload según tienda
    switch ($shop) {
        case 'florist':
            $payload = http_build_query([
                'item_id' => $item['product_id'],
                'qty' => $item['quantity']
            ]);
            $header = "Content-Type: application/x-www-form-urlencoded\r\n";
            break;

        case 'camping':
        case 'makeup':
            $payload = json_encode([
                'mse_user_id' => $userId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity']
            ]);
            $header = "Content-Type: application/json\r\n";
            break;

        default:
            echo json_encode(['error' => "Shop not supported: $shop"]);
            exit;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => $header,
            'content' => $payload,
            'timeout' => 5
        ]
    ]);

    $response = @file_get_contents($shopsEndpoints[$shop], false, $context);
    if ($response === false) {
        echo json_encode(['error' => "Reservation failed for $shop"]);
        exit;
    }

    $res = json_decode(trim($response), true);
    if (is_array($res)) {
        $ok = false;
        if ((isset($res['ok']) && $res['ok']) ||
            (isset($res['success']) && $res['success']) ||
            (isset($res['status']) && $res['status'] === 'ok')) {
            $ok = true;
        }
        if (!$ok) {
            echo json_encode(['error' => "Reservation rejected by $shop", 'shop_response' => $res]);
            exit;
        }
    }

    // Guardamos item para DB
    $cartForDB[] = [
        'shop' => $shop,
        'product_id' => $item['product_id'],
        'product_name' => $item['name'] ?? "Product ".$item['product_id'],
        'quantity' => $item['quantity'],
        'price' => $item['price'] ?? 50 // fallback si no tenemos price
    ];
}

// ----------------------------
// 3️⃣ Guardar pedido en DB
// ----------------------------
try {
    $pdo->beginTransaction();

    // Calcular total
    $total = 0;
    foreach ($cartForDB as $item) {
        $total += $item['quantity'] * $item['price'];
    }

    // Insertar pedido
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->execute([$userId, $total]);
    $orderId = $pdo->lastInsertId();

    // Insertar items
    $stmtItem = $pdo->prepare("
        INSERT INTO order_items
        (order_id, shop, product_id, product_name, quantity, price)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($cartForDB as $item) {
        $stmtItem->execute([
            $orderId,
            $item['shop'],
            $item['product_id'],
            $item['product_name'],
            $item['quantity'],
            $item['price']
        ]);
    }

    $pdo->commit();

    // Limpiar carrito
    $_SESSION['cart'] = [];

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Checkout failed', 'exception' => $e->getMessage()]);
}
