<?php

session_start();

if (empty($_SESSION['user']))
{
    header("Location: ../public/login.php");
    exit;
}
else if (!$_SESSION['user']['is_admin'])
{
    header("Location: ../public/index.php");
    exit;
}

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

// Retrieve users list
$stmt = $pdo->query("
    SELECT id, email, name, is_active, is_admin, created_at 
    FROM users 
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
</head>
<body>

<h1>Users</h1>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Name</th>
        <th>Active?</th>
        <th>Admin?</th>
        <th>Created</th>
        <th>Action</th>
    </tr>

    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= $u['is_active'] ? "Yes" : "No" ?></td>
            <td><?= $u['is_admin'] ? "Yes" : "No" ?></td>
            <td><?= $u['created_at'] ?></td>
            <td>
                <?php if ($_SESSION['user']['id'] != $u['id']): ?>
                    <a href="user_delete.php?id=<?= $u['id'] ?>" 
                       onclick="return confirm('Are you sure you want to delete this user?');">
                       Delete
                    </a>
                <?php else: ?>
                    (Your Account)
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>

<?php require __DIR__ . '/../includes/footer.php'; ?>