<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../Connection.php";

if (!isset($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$q = "%" . $_GET['q'] . "%";

$stmt = $pdo->prepare("
    SELECT 
        i.id,
        i.name,
        i.description,
        i.price,
        i.stock,
        c.name AS category
    FROM items i
    JOIN categories c ON i.category_id = c.id
    WHERE i.name LIKE ?
");

$stmt->execute([$q]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
