<?php
require __DIR__ . '/../config/conn.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$q = "%$q%";

$stmt = $pdo->prepare("
    SELECT i.id, i.name, i.description, i.price, i.stock, i.image_path,
       c.name AS category
    FROM items i
    JOIN categories c ON c.id = i.category_id
    WHERE i.name LIKE ? OR i.description LIKE ?
");
$stmt->execute([$q, $q]);

$products = [];

foreach ($stmt as $row)
{
    $cleanPath = str_replace('../', '', $row['image_path']);
    
    $products[] = [
    'shop' => 'camping',
    'product_id' => (int)$row['id'],
    'name' => $row['name'],
    'description' => $row['description'],
    'price' => (float)$row['price'],
    'stock' => (int)$row['stock'],
    'category' => $row['category'],
    'image' => $cleanPath
];

}

echo json_encode($products);

?>