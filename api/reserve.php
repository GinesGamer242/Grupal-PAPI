<?php
require __DIR__ . '/../config/session.php';
require __DIR__ . '/../config/conn.php';

header('Content-Type: application/json');

// --------------------
// 1️⃣ Validar sesión
// --------------------
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// --------------------
// 2️⃣ Leer JSON del body
// --------------------
$data = json_decode(file_get_contents('php://input'), true);

$shop      = $data['shop'] ?? '';
$productId = (int)($data['product_id'] ?? 0);
$quantity  = (int)($data['quantity'] ?? 0);

if (!$shop || $productId <= 0 || $quantity <= 0) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// --------------------
// 3️⃣ Mapear tiendas a endpoints IA
// --------------------
$shops = [
    'camping' => 'http://localhost/PAPI/Grupal-PAPI/IAs/camping_shop/api/reserve.php',
    'makeup'  => 'http://localhost/PAPI/Grupal-PAPI/IAs/makeup_shop/api/reserve.php',
    'florist' => 'http://localhost/PAPI/Grupal-PAPI/IAs/florist_shop/api/reserve.php'
];


if (!isset($shops[$shop])) {
    echo json_encode(['error' => 'Unknown shop']);
    exit;
}

// --------------------
// 4️⃣ Preparar payload correcto según tienda
// --------------------
// Ahora todas las tiendas reciben solo product_id + quantity
$payload = json_encode([
    'product_id' => $productId,
    'quantity'   => $quantity
]);

$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => $payload,
        'timeout' => 5
    ]
]);

// --------------------
// 5️⃣ Llamar IA
// --------------------
$response = @file_get_contents($shops[$shop], false, $context);

if ($response === false) {
    echo json_encode(['error' => 'Shop API not reachable', 'shop' => $shop]);
    exit;
}

// Limpiar respuesta y decodificar JSON
$res = json_decode(trim($response), true);

// --------------------
// 6️⃣ Validar respuesta IA
// --------------------
$ok = false;
if (is_array($res)) {
    if (isset($res['ok']) && $res['ok']) $ok = true;
    if (isset($res['success']) && $res['success']) $ok = true;
} else {
    if ($response !== '') $ok = true;
}

if (!$ok) {
    echo json_encode(['error' => "Reservation failed by $shop", 'shop_response' => $res ?? $response]);
    exit;
}

// --------------------
// 7️⃣ Obtener nombre y precio del producto
// --------------------
$productApi  = "http://localhost/PAPI/Grupal-PAPI/api/search.php?shop=$shop&product_id=$productId";
$productJson = @file_get_contents($productApi);
$product     = json_decode($productJson, true);

$productName  = $product['name'] ?? "Product $productId";
$productPrice = isset($product['price']) ? (float)$product['price'] : 0;

// --------------------
// 8️⃣ Inicializar carrito
// --------------------
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// --------------------
// 9️⃣ Añadir / actualizar carrito
// --------------------
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['shop'] === $shop && $item['product_id'] === $productId) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'shop'       => $shop,
        'product_id' => $productId,
        'name'       => $productName,
        'price'      => $productPrice,
        'quantity'   => $quantity
    ];
}

// --------------------
// 🔟 OK
// --------------------
echo json_encode([
    'success' => true,
    'item' => [
        'shop'       => $shop,
        'product_id' => $productId,
        'name'       => $productName,
        'price'      => $productPrice,
        'quantity'   => $quantity
    ]
]);
