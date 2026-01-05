<?php

require __DIR__ . '/../config/conn.php';

session_start();

require __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try
    {
        // Start transaction if not already active
        if (!$pdo->inTransaction())
        {
            $pdo->beginTransaction();
        }

        // Retrieve cart items with prices and shipping costs
        $stmt = $pdo->prepare("
            SELECT c.*, i.price, i.shipping_cost 
            FROM cart c 
            JOIN items i ON c.item_id = i.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user']['id']]);
        $rows = $stmt->fetchAll();

        // Prevent checkout with empty cart
        if (empty($rows))
        {
            throw new Exception("Empty cart.");
        }

        // Calculate order total
        $total = 0;
        foreach ($rows as $r)
        {
            $total += $r['price'] * $r['quantity'] + $r['shipping_cost'];
        }

        // Create order record
        $stmt = $pdo->prepare(
            "INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)"
        );
        $stmt->execute([$_SESSION['user']['id'], $total, 'paid']);
        $order_id = $pdo->lastInsertId();

        // Insert order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items 
            (order_id, item_id, quantity, price, shipping_cost) 
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($rows as $r)
        {
            $stmt->execute([
                $order_id,
                $r['item_id'],
                $r['quantity'],
                $r['price'],
                $r['shipping_cost']
            ]);
        }

        // Clear user's cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user']['id']]);

        // Commit transaction
        $pdo->commit();

        echo "<p style='color:green;'>Payment complete. Order's ID: $order_id</p>";
    }
    catch (Exception $e)
    {
        // Rollback transaction on error
        if ($pdo->inTransaction())
        {
            $pdo->rollBack();
        }

        echo "<p style='color:red;'>Payment error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    require __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<h2>Checkout (simulated)</h2>

<form method="post">
    <p>Introduce payment info (simulated).</p>
    <button>Pay</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>