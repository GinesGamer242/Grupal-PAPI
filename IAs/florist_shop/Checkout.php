<?php
session_start();
require_once "Connection.php";

// VERIFY
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// CART
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    die("Carrito vacío");
}

$ids = array_keys($cart);
$in = implode(",", array_fill(0, count($ids), "?"));

$stmt = $pdo->prepare("SELECT * FROM items WHERE id IN ($in) FOR UPDATE");
$stmt->execute($ids);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$map = [];
foreach ($items as $it) {
    $map[$it['id']] = $it;
}

// VALID
$total = 0;
$errors = [];

foreach ($cart as $id => $qty) {

    if (!isset($map[$id])) {
        $errors[] = "This $id doesnt exist.";
        continue;
    }

    if ($map[$id]['stock'] < $qty) {
        $errors[] = "Stock insuficient for {$map[$id]['name']}.";
    }

    $total += $qty * $map[$id]['price'];
}

if (!empty($errors)) {
    echo "<h3>Errors in the purchase</h3><ul>";
    foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>";
    echo "</ul><a href='cart.php'>Go to the catalog</a>";
    exit;
}

//CREATE PURCHASE
try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'paid')");
    $stmt->execute([$_SESSION['user_id'], $total]);

    $orderId = $pdo->lastInsertId();

    $insert = $pdo->prepare("INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
    $updateStock = $pdo->prepare("UPDATE items SET stock = stock - ? WHERE id = ?");

    foreach ($cart as $id => $qty) {

        $price = $map[$id]['price'];

        $insert->execute([$orderId, $id, $qty, $price]);
        $updateStock->execute([$qty, $id]);
    }

    $pdo->commit();

    $_SESSION['cart'] = [];

    echo "<h2>Valid purchase</h2>";
    echo "<p>Purchase number: $orderId</p>";
    echo "<a href='catalog.php'>Go to catalog</a>";

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
