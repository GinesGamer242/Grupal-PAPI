<?php

require __DIR__ . '/../config/session.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MSE Shop</title>
    <script src="/js/app.js" defer></script>
</head>
<body>

<header>
    <h1>Meta-Search E-Commerce</h1>
    <nav>
        <a href="/PAPI/GA_GinesLuciaIrene/Grupal-PAPI/public/index.php">Products</a> |
        <a href="/PAPI/GA_GinesLuciaIrene/Grupal-PAPI/public/cart.php">Cart</a> |
        <a href="/PAPI/GA_GinesLuciaIrene/Grupal-PAPI/public/orders.php">Orders</a> |
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="/PAPI/GA_GinesLuciaIrene/Grupal-PAPI/public/logout.php">Logout</a>
        <?php else: ?>
            <a href="/PAPI/GA_GinesLuciaIrene/Grupal-PAPI/public/register.php">Register</a> |
            <a href="/PAPI/GA_GinesLuciaIrene/Grupal-PAPI/public/login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<hr>