<!--<.?php

require __DIR__ . '/../config/session.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['shop']) ||
    empty($data['product_id']) ||
    empty($data['quantity']) ||
    (int)$data['quantity'] <= 0
)
{
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$shop = $data['shop'];
$productId = (int)$data['product_id'];
$quantity = (int)$data['quantity'];

$shops = [
    'camping' => '../IAs/camping_shop/api/reserve.php',
    'makeup' => '../IAs/makeup_shop/api/reserve.php',
    'florist' => '../IAs/florist_shop/api/reserve.php'
];

if (!isset($shops[$shop]))
{
    echo json_encode(['error' => 'Unknown shop']);
    exit;
}

/*
 * Payload sent to IA
 */
$payload = json_encode([
    'product_id' => $productId,
    'quantity' => $quantity,
    'mse_user_id' => $_SESSION['user']['id']
]);

$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => $payload,
        'timeout' => 5
    ]
]);

$response = @file_get_contents($shops[$shop], false, $context);

if ($response === false)
{
    echo json_encode(['error' => 'IA not reachable']);
    exit;
}

$res = json_decode($response, true);

if (!isset($res['ok']) || !$res['ok'])
{
    echo json_encode([
        'error' => $res['error'] ?? 'Reservation failed'
    ]);
    exit;
}

/*
 * Store in MSE cart (session)
 */
$_SESSION['cart'][] = [
    'shop'       => $shop,
    'product_id'=> $productId,
    'quantity'  => $quantity,
    'added_at'  => time()
];

echo json_encode(['success' => true]);

?>-->
<?php
require __DIR__ . '/../config/session.php';
header('Content-Type: application/json');

// Recibir datos del frontend
$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['shop']) ||
    empty($data['product_id']) ||
    empty($data['quantity']) ||
    (int)$data['quantity'] <= 0
) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$shop = $data['shop'];
$productId = (int)$data['product_id'];
$quantity = (int)$data['quantity'];

// Rutas de las APIs de cada tienda
$shops = [
    'camping' => '../IAs/camping_shop/api/reserve.php',
    'makeup'  => '../IAs/makeup_shop/api/reserve.php',
    'florist' => '../IAs/florist_shop/api/reserve.php'
];

if (!isset($shops[$shop])) {
    echo json_encode(['error' => 'Unknown shop']);
    exit;
}

// Preparar payload según la tienda
switch ($shop) {
    case 'florist':
        // Esta tienda espera POST normal (form-data)
        $payload = http_build_query([
            'item_id' => $productId,
            'qty'     => $quantity
        ]);
        $header = "Content-Type: application/x-www-form-urlencoded\r\n";
        break;

    case 'camping':
    case 'makeup':
        // Estas tiendas esperan JSON
        $payload = json_encode([
            'product_id' => $productId,
            'quantity'   => $quantity,
            'mse_user_id'=> $_SESSION['user']['id']
        ]);
        $header = "Content-Type: application/json\r\n";
        break;

    default:
        echo json_encode(['error' => 'Shop not supported']);
        exit;
}

// Crear contexto HTTP
$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => $header,
        'content' => $payload,
        'timeout' => 5
    ]
]);

// Hacer request a la tienda
$response = @file_get_contents($shops[$shop], false, $context);

if ($response === false) {
    echo json_encode(['error' => 'IA not reachable']);
    exit;
}

$res = json_decode($response, true);

// Manejar respuesta según formato de cada tienda
if ($shop === 'florist') {
    if (!isset($res['status']) || $res['status'] !== 'ok') {
        echo json_encode([
            'error' => $res['error'] ?? 'Reservation failed'
        ]);
        exit;
    }
} else {
    if (!isset($res['ok']) || !$res['ok']) {
        echo json_encode([
            'error' => $res['error'] ?? 'Reservation failed'
        ]);
        exit;
    }
}

// Guardar en carrito de sesión
$_SESSION['cart'][] = [
    'shop'       => $shop,
    'product_id' => $productId,
    'quantity'   => $quantity,
    'added_at'   => time()
];

echo json_encode(['success' => true]);
?>
