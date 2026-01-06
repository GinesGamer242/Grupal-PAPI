<?php

require __DIR__ . '/../config/session.php';
header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| 1️⃣ Auth check
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| 2️⃣ Read input
|--------------------------------------------------------------------------
*/
$data = json_decode(file_get_contents('php://input'), true);

if (
    !is_array($data) ||
    empty($data['shop']) ||
    empty($data['product_id']) ||
    empty($data['quantity']) ||
    (int)$data['quantity'] <= 0
) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$shop      = $data['shop'];
$productId = (int) $data['product_id'];
$quantity  = (int) $data['quantity'];

/*
|--------------------------------------------------------------------------
| 3️⃣ IA endpoints
|--------------------------------------------------------------------------
*/
$shops = [
    'camping' => __DIR__ . '/../IAs/camping_shop/api/reserve.php',
    'makeup'  => __DIR__ . '/../IAs/makeup_shop/api/reserve.php',
    'florist' => __DIR__ . '/../IAs/florist_shop/api/reserve.php'
];

if (!isset($shops[$shop])) {
    echo json_encode(['error' => 'Unknown shop']);
    exit;
}

/*
|--------------------------------------------------------------------------
| 4️⃣ Build payload per shop
|--------------------------------------------------------------------------
*/
switch ($shop) {

    case 'florist':
        // Florist suele usar form-data
        $payload = http_build_query([
            'item_id' => $productId,
            'qty'     => $quantity
        ]);
        $headers = "Content-Type: application/x-www-form-urlencoded\r\n";
        break;

    case 'camping':
    case 'makeup':
        // Camping / Makeup → JSON
        $payload = json_encode([
            'product_id' => $productId,
            'quantity'   => $quantity
        ]);
        $headers = "Content-Type: application/json\r\n";
        break;

    default:
        echo json_encode(['error' => 'Shop not supported']);
        exit;
}

/*
|--------------------------------------------------------------------------
| 5️⃣ Send request to IA
|--------------------------------------------------------------------------
*/
$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => $headers,
        'content' => $payload,
        'timeout' => 5
    ]
]);

$response = @file_get_contents($shops[$shop], false, $context);

if ($response === false) {
    echo json_encode([
        'error' => "Shop API not reachable",
        'shop'  => $shop
    ]);
    exit;
}

$response = trim($response);

/*
|--------------------------------------------------------------------------
| 6️⃣ Normalize IA response (ROBUST)
|--------------------------------------------------------------------------
*/

// Try JSON decode (if possible)
$res = json_decode($response, true);

// Case 1: valid JSON with known success fields
if (is_array($res)) {

    if (
        (isset($res['ok']) && $res['ok']) ||
        (isset($res['success']) && $res['success']) ||
        (isset($res['status']) && $res['status'] === 'ok') ||
        (isset($res['result']) && $res['result'] === 1)
    ) {
        // OK
    }
    else {
        echo json_encode([
            'error' => "Reservation failed by $shop",
            'shop_response' => $res
        ]);
        exit;
    }

}
// Case 2: plain text response (VERY COMMON)
else {

    // Accept any non-empty response as success
    if ($response === '') {
        echo json_encode([
            'error' => "Empty response from $shop"
        ]);
        exit;
    }

    // Otherwise: assume success
}


/*
|--------------------------------------------------------------------------
| 7️⃣ Init cart
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/*
|--------------------------------------------------------------------------
| 8️⃣ Merge item into cart
|--------------------------------------------------------------------------
*/
$merged = false;

foreach ($_SESSION['cart'] as &$item) {
    if ($item['shop'] === $shop && $item['product_id'] === $productId) {
        $item['quantity'] += $quantity;
        $merged = true;
        break;
    }
}
unset($item);

if (!$merged) {
    $_SESSION['cart'][] = [
        'shop'       => $shop,
        'product_id' => $productId,
        'quantity'   => $quantity,
        'added_at'   => time()
    ];
}

/*
|--------------------------------------------------------------------------
| 9️⃣ Success
|--------------------------------------------------------------------------
*/
echo json_encode(['success' => true]);
