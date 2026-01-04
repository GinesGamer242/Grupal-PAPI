<?php

require __DIR__ . '/../config/session.php';
require __DIR__ . '/../includes/header.php';

if (empty($_SESSION['user_id']))
{
    header("Location: login.php");
    exit;
}

?>

<h2>Meta-Search Shop</h2>

<div style="margin-bottom:15px;">
    <input id="q" placeholder="Search products..." style="width:250px;">
    <button onclick="search()">Search</button>
</div>

<div id="filters" style="margin-bottom:15px;">
    <label>
        Max price:
        <input type="number" id="maxPrice" onchange="applyFilters()">
    </label>
</div>

<hr>

<h3>Results</h3>
<div id="results">
    <p>Use the search bar to find products.</p>
</div>

<hr>

<h3>Cart</h3>
<div id="cart">
    <p>Cart is empty.</p>
</div>

<button onclick="checkout()">Checkout</button>

<script src="../js/app.js"></script>

<?php require __DIR__ . '/../includes/footer.php'; ?>