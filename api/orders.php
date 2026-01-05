<?php

require __DIR__ . '/../config/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']))
{
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$mseUserId = $_SESSION['user']['id'];

/*
 * Map shops → IA orders endpoints
 */
$shops = [
    'camping' => '../IAs/camping_shop/api/orders.php',
    'makeup' => '../IAs/makeup_shop/api/orders.php',
    'florist' => '../IAs/florist_shop/api/orders.php'
];

$allOrders = [];

foreach ($shops as $shop => $url)
{
    $fullUrl = $url . '?mse_user_id=' . urlencode($mseUserId);

    $json = @file_get_contents($fullUrl);

    // If one IA fails, we just skip it
    if ($json === false)
    {
        continue;
    }

    $orders = json_decode($json, true);

    if (!is_array($orders))
    {
        continue;
    }

    foreach ($orders as $order)
    {
        $order['shop'] = $shop;
        $allOrders[] = $order;
    }
}

echo json_encode($allOrders);

?>