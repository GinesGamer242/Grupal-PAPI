

<?php
require __DIR__ . '/../config/session.php';
header('Content-Type: application/json');

// URLs ABSOLUTAS a las IAs
$shops = [
    //'camping' => 'http://localhost/PAPI/Grupal-PAPI/IAs/camping_shop/api/search.php',
    //'makeup'  => 'http://localhost/PAPI/Grupal-PAPI/IAs/makeup_shop/api/search.php',
    'florist' => 'http://localhost/PAPI/Grupal-PAPI/IAs/florist_shop/api/search.php'
];

/*
|--------------------------------------------------------------------------
| MODO 1: DETALLE DE PRODUCTO
|--------------------------------------------------------------------------
*/
if (isset($_GET['shop'], $_GET['product_id'])) {

    $shop = $_GET['shop'];
    $productId = (int)$_GET['product_id'];

    if (!isset($shops[$shop])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid shop']);
        exit;
    }

    // Pedimos TODOS los productos a la IA
    $json = file_get_contents($shops[$shop] . '?q=');
    $products = json_decode($json, true);

    if (!is_array($products)) {
        http_response_code(502);
        echo json_encode(['error' => 'Invalid IA response']);
        exit;
    }

    foreach ($products as $product) {
        if ((int)$product['product_id'] === $productId) {
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

foreach ($shops as $shopName => $url) {

    $json = file_get_contents($url . '?q=' . urlencode($q));
    $products = json_decode($json, true);

    if (!is_array($products)) continue;

    foreach ($products as $product) {
        $product['shop'] = $shopName;
        $results[] = $product;
    }
}

echo json_encode($results);
