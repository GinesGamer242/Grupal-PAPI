<?php

session_start();

if (empty($_SESSION['user'])) {
    header("Location: ../public/login.php");
    exit;
}
else if (!$_SESSION['user']['is_admin']) {
    header("Location: ../public/index.php");
    exit;
}

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

// Retrieve orders list
$stmt = $pdo->query("
    SELECT o.id, o.user_id, o.total, o.status, o.created_at,
           u.name AS user_name, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
");
$orders = $stmt->fetchAll();
?>

<h2>Orders</h2>

<table border="1" cellpadding="6">
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Email</th>
    <th>Total</th>
    <th>Status</th>
    <th>Date</th>
    <th>Actions</th>
</tr>

<?php foreach ($orders as $o): ?>
<tr>
    <td><?= $o['id'] ?></td>
    <td><?= htmlspecialchars($o['user_name']) ?></td>
    <td><?= htmlspecialchars($o['email']) ?></td>
    <td><?= number_format($o['total'],2) ?> €</td>
    <td><?= htmlspecialchars($o['status']) ?></td>
    <td><?= $o['created_at'] ?></td>

    <td>

        <?php if ($o['status'] !== 'sent' && $o['status'] !== 'cancelled'): ?>
            <form method="POST" action="orders_send.php" style="display:inline;"
                  onsubmit="return confirm('Mark order as sent?');">
                <input type="hidden" name="id" value="<?= $o['id'] ?>">
                <button style="color:green;">Mark as sent</button>
            </form>
        <?php endif; ?>

        <?php if ($o['status'] !== 'cancelled'): ?>
            <form method="POST" action="orders_cancel.php" style="display:inline;"
                  onsubmit="return confirm('Cancel order?');">
                <input type="hidden" name="id" value="<?= $o['id'] ?>">
                <button style="color:red;">Cancel</button>
            </form>
        <?php endif; ?>

        <?php if ($o['status'] === 'cancelled'): ?>
            <span style="color:gray;">Cancelled</span>
        <?php endif; ?>

    </td>

</tr>

<?php endforeach; ?>

</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>