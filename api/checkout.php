<?php

require __DIR__ . '/../config/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']))
{
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

if (empty($_SESSION['cart']))
{
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

/*
 * Map shops → IA checkout endpoints
 */
$shops = [
    'camping' => '../AIs/camping_shop/api/checkout.php',
    'makeup' => '../AIs/makeup_shop/api/checkout.php'
    // AÑADIR OTRAS TIENDAS
];

/*
 * Group items by shop
 */
$itemsByShop = [];

foreach ($_SESSION['cart'] as $item)
{
    $shop = $item['shop'];

    if (!isset($shops[$shop])) {
        echo json_encode(['error' => 'Unknown shop in cart']);
        exit;
    }

    $itemsByShop[$shop][] = [
        'product_id' => $item['product_id'],
        'quantity'   => $item['quantity']
    ];
}

/*
 * Checkout per shop
 */
foreach ($itemsByShop as $shop => $items)
{

    $payload = json_encode([
        'mse_user_id' => $_SESSION['user']['id'],
        'items'       => $items
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
        echo json_encode([
            'error' => "Checkout failed for shop: $shop"
        ]);
        exit;
    }

    $res = json_decode($response, true);

    if (!isset($res['ok']) || !$res['ok'])
    {
        echo json_encode([
            'error' => "Checkout rejected by shop: $shop"
        ]);
        exit;
    }
}

$_SESSION['cart'] = [];

echo json_encode(['success' => true]);

?>