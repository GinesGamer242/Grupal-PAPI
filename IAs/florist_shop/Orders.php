<?php
session_start();
require_once "Connection.php";

// Verifica que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Obtener todos los pedidos del usuario
$stmt = $pdo->prepare("
    SELECT * 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
</head>
<body>

<h1>My Orders</h1>

<!-- Botón para ir al catálogo -->
<p>
    <a href="catalog.php">
        <button>Go to Catalog</button>
    </a>
</p>

<?php if (empty($orders)): ?>
    <p>You haven't made any orders yet.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
            <h3>Order #<?php echo $order['id']; ?> — Status: <?php echo ucfirst($order['status']); ?></h3>
            <p>Date: <?php echo $order['created_at']; ?></p>
            <p>Total: €<?php echo number_format($order['total'],2); ?></p>

            <h4>Items:</h4>
            <ul>
                <?php
                // Obtener productos de este pedido
                $stmtItems = $pdo->prepare("
                    SELECT oi.quantity, oi.price, i.name 
                    FROM order_items oi 
                    JOIN items i ON oi.item_id = i.id
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$order['id']]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                foreach ($items as $item):
                ?>
                    <li>
                        <?php echo htmlspecialchars($item['name']); ?> 
                        — Quantity: <?php echo $item['quantity']; ?> 
                        — Price: €<?php echo number_format($item['price'],2); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Botón para refrescar la página de pedidos -->
<p>
    <a href="orders.php">
        <button>Refresh Orders</button>
    </a>
</p>

</body>
</html>
