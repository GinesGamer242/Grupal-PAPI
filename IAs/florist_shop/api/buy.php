<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../Connection.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['mse_user_id']) || !isset($input['items'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$mseUserId = $input['mse_user_id'];
$items     = $input['items'];

try {
    $pdo->beginTransaction();

    $total = 0;
    $map = [];

    foreach ($items as $it) {
        $stmt = $pdo->prepare(
            "SELECT id, price, stock FROM items WHERE id = ? FOR UPDATE"
        );
        $stmt->execute([$it['item_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['stock'] < $it['qty']) {
            throw new Exception("Not enough stock for item ".$it['item_id']);
        }

        $map[$it['item_id']] = $row;
        $total += $row['price'] * $it['qty'];
    }

    $stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, mse_user_id, total, status)
         VALUES (NULL, ?, ?, 'paid')"
    );
    $stmt->execute([$mseUserId, $total]);
    $orderId = $pdo->lastInsertId();

    $insertItem = $pdo->prepare(
        "INSERT INTO order_items (order_id, item_id, quantity, price)
         VALUES (?, ?, ?, ?)"
    );
    $updateStock = $pdo->prepare(
        "UPDATE items SET stock = stock - ? WHERE id = ?"
    );

    foreach ($items as $it) {
        $price = $map[$it['item_id']]['price'];

        $insertItem->execute([
            $orderId,
            $it['item_id'],
            $it['qty'],
            $price
        ]);

        $updateStock->execute([
            $it['qty'],
            $it['item_id']
        ]);
    }

    $pdo->commit();

    /*echo json_encode([
        "ok" => true,
        "order_id" => $orderId
    ]);*/
    echo json_encode([
        "id" => (int)$orderId
    ]);


} catch (Exception $e) {
    $pdo->rollBack();
    /*http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);*/
    // ERROR
    http_response_code(500);
    echo json_encode([
        "error" => "Order failed",
        "details" => $e->getMessage()
    ]);

}
?>
