<!--<.?php //

require __DIR__ . '/../config/session.php';
require __DIR__ . '/../includes/header.php';

//$shop = $_GET['shop'] ?? '';
//$productId = $_GET['product_id'] ?? '';
$shop = $_GET['shop'] ?? null;
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;

if (!$shop || !$productId)
{
    echo "<p>Invalid product.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

?>

<h2>Product details</h2>

<div id="product-container">
    <p>Loading product...</p>
</div>

<script>
const shop = <?= json_encode($shop) ?>;
const productId = <?= json_encode($productId) ?>;
/*
fetch(`../api/search.php?shop=${shop}&product_id=${productId}`)
    .then(r => r.json())
    .then(data => {

        if (!data || data.error)
        {
            document.getElementById('product-container').innerHTML =
                '<p>Product not found.</p>';
            return;
        }

        let html = `
            <h3>${data.name}</h3>
            ${data.image ? `<img src="${data.image}" style="max-width:300px;">` : ''}
            <p>${data.description || ''}</p>
            <p><strong>Category:</strong> ${data.category}</p>
            <p><strong>Price:</strong> ${data.price} €</p>

            <label>
                Quantity:
                <input type="number" id="qty" value="1" min="1">
            </label>
            <br><br>

            <button onclick="addToCart(shop, productId)">Add to cart</button>
            <p id="msg"></p>
        `;

        document.getElementById('product-container').innerHTML = html;
    });*/
    fetch(`../api/search.php?shop=${shop}&product_id=${productId}`)
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (!data || data.error) {
            document.getElementById('product-container').innerHTML =
                '<p>Product not found.</p>';
            return;
        }

        let html = `
            <h3>${data.name}</h3>
            ${data.image ? `<img src="${data.image}" style="max-width:300px;">` : ''}
            <p>${data.description || ''}</p>
            <p><strong>Category:</strong> ${data.category}</p>
            <p><strong>Price:</strong> ${data.price} €</p>
        `;

        document.getElementById('product-container').innerHTML = html;
    })
    .catch(err => {
        document.getElementById('product-container').innerHTML =
            `<p>Error loading product: ${err.message}</p>`;
    });

</script>
<..?php require __DIR__ . '/../includes/footer.php'; ?>-->

<?php
require __DIR__ . '/../config/session.php';
require __DIR__ . '/../includes/header.php';

$shop = $_GET['shop'] ?? null;
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;

if (!$shop || !$productId) {
    echo "<p>Invalid product.</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<h2>Product details</h2>

<div id="product-container">
    <p>Loading product...</p>
</div>

<script>
const shop = <?= json_encode($shop) ?>;
const productId = <?= json_encode($productId) ?>;

fetch(`/PAPI/Grupal-PAPI/api/search.php?shop=${shop}&product_id=${productId}`)
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (!data || data.error) {
            document.getElementById('product-container').innerHTML =
                '<p>Product not found.</p>';
            return;
        }

        document.getElementById('product-container').innerHTML = `
            <h3>${data.name}</h3>
            ${data.image ? `<img src="/PAPI/Grupal-PAPI/IAs/${shop}_shop/${data.image}" style="max-width:300px;">` : ''}
            <p>${data.description || ''}</p>
            <p><strong>Category:</strong> ${data.category}</p>
            <p><strong>Price:</strong> ${data.price} €</p>
        `;
    })
    .catch(err => {
        document.getElementById('product-container').innerHTML =
            `<p>Error loading product: ${err.message}</p>`;
    });
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>

