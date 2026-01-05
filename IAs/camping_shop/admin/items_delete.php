<?php

session_start();

// Redirect to login page if no user is logged in
if (empty($_SESSION['user'])) 
{
    header("Location: ../public/login.php");
    exit;
}
// Redirect to public index if the user is not an administrator
else if (!$_SESSION['user']['is_admin'])
{
    header("Location: ../public/index.php");
    exit;
}

require __DIR__ . '/../config/conn.php';

// Cast the received ID to integer for basic validation
$id = (int)($_POST['id'] ?? 0);

// Only execute the deletion if the ID is valid
if ($id > 0)
{
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;

?>