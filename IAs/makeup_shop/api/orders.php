<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ecommerce;charset=utf8",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // 🔐 user_id es obligatorio porque orders depende de users
    if (!isset($_GET['user_id'])) {
        throw new Exception("Missing user_id");
    }

    $userId = (int)$_GET['user_id'];

    $stmt = $pdo->prepare("
        SELECT id, items, created_at
        FROM orders
        WHERE user_id = :user
        ORDER BY created_at DESC
    ");

    $stmt->execute([
        ":user" => $userId
    ]);

    $orders = $stmt->fetchAll();

    $response = [];

    foreach ($orders as $order) {

        // items es JSON almacenado como TEXT
        $items = json_decode($order['items'], true);

        if (!is_array($items)) {
            $items = [];
        }

        $formattedItems = [];

        foreach ($items as $item) {
            $formattedItems[] = [
                "name" => $item["name"] ?? "",
                "quantity" => (int)($item["quantity"] ?? 0),
                "price" => (float)($item["price"] ?? 0)
            ];
        }

        $response[] = [
            "id" => (int)$order["id"],
            "created_at" => substr($order["created_at"], 0, 10),
            "items" => $formattedItems
        ];
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        "error" => "Unable to retrieve orders",
        "details" => $e->getMessage()
    ]);
}
