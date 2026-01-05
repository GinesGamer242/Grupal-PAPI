<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../Connection.php";

$itemId = $_POST['item_id'] ?? $_GET['item_id'] ?? null;
$qty    = $_POST['qty']     ?? $_GET['qty']     ?? null;

if ($itemId === null || $qty === null) {
    http_response_code(400);
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

$itemId = (int)$itemId;
$qty    = (int)$qty;

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT stock FROM items WHERE id = ? FOR UPDATE"
    );
    $stmt->execute([$itemId]);
    $stock = $stmt->fetchColumn();

    if ($stock === false || $stock < $qty) {
        throw new Exception("Not enough stock");
    }

    $stmt = $pdo->prepare(
        "UPDATE items SET stock = stock - ? WHERE id = ?"
    );
    $stmt->execute([$qty, $itemId]);

    $pdo->commit();
    echo json_encode(["ok" => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
