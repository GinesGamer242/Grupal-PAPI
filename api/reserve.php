<?php

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
    'camping' => 'http://localhost/camping_shop/api/reserve.php',
    // LAS OTRAS TIENDAS
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

?>