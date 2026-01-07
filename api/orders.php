<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/session.php';
require __DIR__ . '/../config/conn.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {
    $stmtOrders = $pdo->prepare("
        SELECT id AS order_id, total, created_at
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmtOrders->execute([$userId]);
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    if ($orders) {
        $stmtItems = $pdo->prepare("
            SELECT shop, product_id, product_name, quantity, price
            FROM order_items
            WHERE order_id = ?
        ");

        foreach ($orders as $order) {
            $stmtItems->execute([$order['order_id']]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            $result[] = [
                'order_id' => $order['order_id'],
                'date'     => $order['created_at'],
                'status'   => 'paid',
                'total'    => $order['total'],
                'items'    => $items
            ];
        }
    }

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Failed to fetch orders',
        'exception' => $e->getMessage()
    ]);
}
