<?php
/*header("Content-Type: application/json");
require_once "../Connection.php";

if (!isset($_GET['mse_user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing mse_user_id"]);
    exit;
}

$mseUserId = $_GET['mse_user_id'];

$stmt = $pdo->prepare("
    SELECT 
        o.id AS order_id,
        o.total,
        o.created_at,
        o.status,
        i.name AS item_name,
        oi.quantity,
        oi.price
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN items i ON i.id = oi.item_id
    WHERE o.mse_user_id = ?
    ORDER BY o.created_at DESC
");

$stmt->execute([$mseUserId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por pedido
$orders = [];
foreach ($rows as $r) {
    $id = $r['order_id'];

    if (!isset($orders[$id])) {
        $orders[$id] = [
            "order_id" => $id,
            "total" => $r['total'],
            "status" => $r['status'],
            "created_at" => $r['created_at'],
            "items" => []
        ];
    }

    $orders[$id]['items'][] = [
        "name" => $r['item_name'],
        "quantity" => $r['quantity'],
        "price" => $r['price']
    ];
}

echo json_encode(array_values($orders));*/

header("Content-Type: application/json");
require_once "../Connection.php";

if (!isset($_GET['mse_user_id'])) {
    http_response_code(400);
    echo json_encode([
        "error" => "Missing mse_user_id"
    ]);
    exit;
}

$mseUserId = $_GET['mse_user_id'];

$stmt = $pdo->prepare("
    SELECT 
        o.id,
        o.created_at,
        i.name AS item_name,
        oi.quantity,
        oi.price
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN items i ON i.id = oi.item_id
    WHERE o.mse_user_id = ?
    ORDER BY o.created_at DESC
");

$stmt->execute([$mseUserId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$orders = [];

foreach ($rows as $r) {
    $id = $r['id'];

    if (!isset($orders[$id])) {
        $orders[$id] = [
            "id" => (int)$id,
            "created_at" => $r['created_at'],
            "items" => []
        ];
    }

    $orders[$id]['items'][] = [
        "name" => $r['item_name'],
        "quantity" => (int)$r['quantity'],
        "price" => (float)$r['price']
    ];
}

echo json_encode(array_values($orders));

?>