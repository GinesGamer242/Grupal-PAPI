<?php
require __DIR__ . '/../config/session.php';
header('Content-Type: application/json');

$shops = [
    'camping' => 'http://localhost/camping_shop/api/search.php'
    // añadir más IAs aquí
];

/*
|--------------------------------------------------------------------------
| MODO 1: DETALLE DE PRODUCTO
|--------------------------------------------------------------------------
*/
if (isset($_GET['shop'], $_GET['product_id']))
{
    $shop = $_GET['shop'];
    $productId = (int)$_GET['product_id'];

    if (!isset($shops[$shop]))
    {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid shop']);
        exit;
    }

    $json = file_get_contents($shops[$shop]);
    if ($json === false)
    {
        http_response_code(502);
        echo json_encode(['error' => 'IA not reachable']);
        exit;
    }

    $products = json_decode($json, true);

    foreach ($products as $product)
    {
        if ((int)$product['product_id'] === $productId)
        {
            echo json_encode($product);
            exit;
        }
    }

    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

/*
|--------------------------------------------------------------------------
| MODO 2: BÚSQUEDA GLOBAL
|--------------------------------------------------------------------------
*/
$q = $_GET['q'] ?? '';
$results = [];

foreach ($shops as $shop => $url)
{
    $json = file_get_contents($url . '?q=' . urlencode($q));
    if ($json === false)
    {
        continue;
    }

    $products = json_decode($json, true);
    if (!is_array($products))
    {
        continue;
    }

    foreach ($products as $product)
    {
        $product['shop'] = $shop;
        $results[] = $product;
    }
}

echo json_encode($results);

?>