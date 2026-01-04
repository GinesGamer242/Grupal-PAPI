<?php

session_start();

// If there is no logged-in user, redirect to the login page
if (empty($_SESSION['user'])) {
    header("Location: ../public/login.php");
    exit;
}
// If the user is logged in but is not an administrator,
// redirect to the public homepage
else if (!$_SESSION['user']['is_admin']) {
    header("Location: ../public/index.php");
    exit;
}

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

// Select all items and join with categories to get the category name
$stmt = $pdo->query('
    SELECT i.*, c.name AS category
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    ORDER BY i.id DESC
');

// Fetch all results into an array
$items = $stmt->fetchAll();

?>

<h2>Items (Admin)</h2>
<p><a href="items_create.php">Create new item</a></p>

<table border="1" cellpadding="6">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Category</th>
    <th>Stock</th>
    <th>Actions</th>
</tr>

<?php foreach ($items as $it): ?>
<tr>
    <td><?= $it['id'] ?></td>
    <td><?= htmlspecialchars($it['name']) ?></td>
    <td><?= htmlspecialchars($it['category']) ?></td>
    <td><?= (int)$it['stock'] ?></td>
    <td>
        <form action="items_delete.php" method="POST" style="display:inline;"
            onsubmit="return confirm('Are you sure you want to delete this item?');">
            
            <input type="hidden" name="id" value="<?= $it['id'] ?>">

            <button type="submit" style="color:red;">Eliminar</button>
        </form>
    </td>
</tr>

<?php endforeach; ?>

</table>

<?php

require __DIR__ . '/../includes/footer.php';

?>