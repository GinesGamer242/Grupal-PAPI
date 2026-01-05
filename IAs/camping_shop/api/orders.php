<?php

require __DIR__ . '/../config/conn.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Validar y obtener el ID de usuario
$mseUser = isset($_GET['mse_user_id']) ? (int)$_GET['mse_user_id'] : 0;

if ($mseUser <= 0)
{
    http_response_code(400);
    echo json_encode([
        'error' => 'Parámetro mse_user_id inválido o ausente.'
    ]);
    exit;
}

try
{
    // Obtener pedidos e ítems
    $stmt = $pdo->prepare("
        SELECT o.id AS order_id, o.created_at, 
               oi.quantity, oi.price, i.name
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        JOIN items i ON i.id = oi.item_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC, o.id DESC
    ");
    $stmt->execute([$mseUser]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar ítems por pedido
    $orders = [];
    foreach ($rows as $row) {
        $id = $row['order_id'];
        if (!isset($orders[$id])) {
            $orders[$id] = [
                'id' => $id,
                'created_at' => $row['created_at'],
                'items' => []
            ];
        }
        $orders[$id]['items'][] = [
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];
    }

    echo json_encode(array_values($orders));

}
catch (PDOException $e)
{
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al obtener los pedidos.',
        'details' => $e->getMessage()
    ]);
}