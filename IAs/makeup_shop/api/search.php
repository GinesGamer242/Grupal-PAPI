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

    $search = $_GET['q'] ?? '';

    $sql = "
        SELECT 
            p.id,
            p.name,
            p.description,
            p.price,
            p.stock,
            p.image,
            c.name AS category

        FROM products p
        JOIN subcategories s ON p.subcategory_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE p.name LIKE :search
           OR p.description LIKE :search
        ORDER BY p.name
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":search" => '%' . $search . '%'
    ]);

    $products = $stmt->fetchAll();

    $response = [];

    foreach ($products as $product) {
        $response[] = [
            "shop" => "makeup",
            "product_id" => (int)$product["id"],
            "name" => $product["name"] ?? "",
            "description" => $product["description"] ?? "",
            "price" => (float)$product["price"],
            "stock" => (int)$product["stock"],
            "category" => $product["category"] ?? "",
            "image" => $product["image"] ?? ""
        ];

    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "error" => "Unable to search products",
        "details" => $e->getMessage()
    ]);
}
