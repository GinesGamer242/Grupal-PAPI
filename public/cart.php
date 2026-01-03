<?php

require __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user']))
{
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart']))
{
    echo "<p>Cart is empty</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

?>

<h2>Your Cart</h2>

<table border="1">
<tr>
    <th>Product</th>
    <th>Qty</th>
    <th>Price</th>
</tr>

<?php foreach ($_SESSION['cart'] as $item): ?>
<tr>
    <td><?= htmlspecialchars($item['name']) ?></td>
    <td><?= (int)$item['quantity'] ?></td>
    <td><?= number_format($item['subtotal'], 2) ?> €</td>
</tr>
<?php endforeach; ?>
</table>

<button onclick="checkout()">Pay</button>

<?php require __DIR__ . '/../includes/footer.php'; ?>