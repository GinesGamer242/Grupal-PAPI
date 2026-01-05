<?php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$folder = '';

// If the logged user is an admin, redirect main links to admin area
if (!empty($_SESSION['user']) && $_SESSION['user']['is_admin'])
{
    $folder = '/PAPI/camping_shop/admin/';
}
else
{
    $folder = '/PAPI/camping_shop/public/';
}

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Camping Shop</title>
<link rel="stylesheet" href="/camping_shop/public/assets/style.css">
</head>
<body>
<header>
    <?php if(empty($_SESSION['user'])): ?>
        <h1><a href="/PAPI/camping_shop/public/login.php">Camping Shop</a></h1>
    <?php else: ?>
        <h1><a href="<?php echo $folder; ?>">Camping Shop</a></h1>
    <?php endif; ?>

    <nav>
        <?php if(empty($_SESSION['user'])): ?>
            <a href="/PAPI/camping_shop/public/login.php">Login</a> |
            <a href="/PAPI/camping_shop/public/register.php">Register</a>
        <?php else: ?>
            <a href="<?php echo $folder; ?>">Items</a> |
            Welcome <?php echo htmlspecialchars($_SESSION['user']['name']); ?> |
            <a href="/PAPI/camping_shop/public/logout.php">Logout</a> |
            <?php if (!empty($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
                <a href="/PAPI/camping_shop/admin/users.php">Users</a> |
                <a href="/PAPI/camping_shop/admin/orders.php">Orders</a>
            <?php else: ?>
                <a href="/PAPI/camping_shop/public/cart.php">Cart</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
</header>
<main>