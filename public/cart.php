<?php

require __DIR__ . '/../config/session.php';
require __DIR__ . '/../includes/header.php';

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];

?>

<h2>Your Cart</h2>

<?php if (empty($cart)): ?>

    <p>Cart is empty.</p>

<?php else: ?>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Shop</th>
            <th>Product ID</th>
            <th>Quantity</th>
        </tr>

        <?php foreach ($cart as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['shop']) ?></td>
                <td><?= (int)$item['product_id'] ?></td>
                <td><?= (int)$item['quantity'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <br>

    <button onclick="checkout()">Pay</button>

    <p id="checkout-status"></p>

<?php endif; ?>

<script>
function checkout()
{
    const status = document.getElementById('checkout-status');
    status.textContent = 'Processing checkout...';

    fetch('../api/checkout.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            status.textContent = 'Order completed successfully.';
            window.location.href = 'orders.php';
        } else {
            status.textContent = data.error || 'Checkout failed.';
        }

    })
    .catch(() => {
        status.textContent = 'Network error during checkout.';
    });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
