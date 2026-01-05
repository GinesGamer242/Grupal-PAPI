<?php

require __DIR__ . '/../config/conn.php';
session_start();

// Handle add-to-cart POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id']))
{
    $item_id = (int)$_POST['item_id'];
    $qty = max(1,(int)$_POST['quantity']);

    $pdo->beginTransaction();

    // Lock item row to avoid concurrent stock issues
    $stmt = $pdo->prepare('SELECT stock FROM items WHERE id = ? FOR UPDATE');
    $stmt->execute([$item_id]);
    $it = $stmt->fetch();

    // If item does not exist or insufficient stock, rollback
    if (!$it || $it['stock'] < $qty)
    {
        $pdo->rollBack();
        header('Location: item.php?id='.$item_id);
        exit;
    }

    // Reduce item stock
    $pdo->prepare('UPDATE items SET stock = stock - ? WHERE id = ?')->execute([$qty,$item_id]);

    // Reserve cart item for 15 minutes
    $reserved_until = date('Y-m-d H:i:s', time()+15*60);

    // Insert item into cart
    $user_id = $_SESSION['user']['id'] ?? 0;
    $pdo->prepare('INSERT INTO cart (user_id,item_id,quantity,reserved_until) VALUES (?,?,?,?)')
        ->execute([$user_id,$item_id,$qty,$reserved_until]);

    // Commit transaction
    $pdo->commit();

    header('Location: cart.php');
    exit;
}

require __DIR__ . '/../includes/header.php';

// Retrieve cart items with item details
$stmt = $pdo->query('SELECT c.*, i.name, i.price FROM cart c JOIN items i ON c.item_id = i.id');
$rows = $stmt->fetchAll();

?>

<h2>Cart</h2>

<?php

// If cart is empty, show message
if(empty($rows)) echo '<p>Empty cart</p>';
else
{

?>

<table>
<tr><th>Item</th><th>Amount</th><th>Price</th></tr>
<?php foreach($rows as $r): ?>
<tr>
    <td><?php echo htmlspecialchars($r['name']); ?></td>
    <td><?php echo (int)$r['quantity']; ?></td>
    <td><?php echo number_format($r['price'],2); ?> €</td>
</tr>
<?php endforeach; ?>
</table>

<p><a href="checkout.php">Procceed to payment</a></p>

<?php

}

?>

<?php require __DIR__ . '/../includes/footer.php'; ?>