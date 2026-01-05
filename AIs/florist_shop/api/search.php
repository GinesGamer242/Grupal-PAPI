<?php
/*
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
*/

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
        c.name AS category,
        i.image
    FROM items i
    JOIN categories c ON i.category_id = c.id
    WHERE i.name LIKE ?
");

$stmt->execute([$q]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($rows as $r) {
    $result[] = [
        "shop"        => "camping",
        "product_id" => (int)$r['id'],
        "name"        => $r['name'],
        "description" => $r['description'],
        "price"       => (float)$r['price'],
        "category"    => $r['category'],
        "image"       => $r['image'] ?? ""
    ];
}

echo json_encode($result);

?>
