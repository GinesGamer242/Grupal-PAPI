const qInput     = document.getElementById('q');
const resultsDiv = document.getElementById('results');
const cartDiv    = document.getElementById('cart');
const maxPriceEl = document.getElementById('maxPrice');

let allProducts = [];
let cart = [];

/* =========================
   SEARCH
========================= */
function search()
{
    fetch('../api/search.php?q=' + encodeURIComponent(qInput.value))
        .then(r => r.json())
        .then(data => {
            allProducts = data;
            renderResults(data);
        });
}

/* =========================
   FILTERS (client-side)
========================= */
function applyFilters()
{
    let filtered = [...allProducts];

    const maxPrice = parseFloat(maxPriceEl.value);
    if (!isNaN(maxPrice))
    {
        filtered = filtered.filter(p => p.price <= maxPrice);
    }

    renderResults(filtered);
}

/* =========================
   RENDER PRODUCTS
========================= */
function renderResults(products)
{
    resultsDiv.innerHTML = '';

    if (products.length === 0)
    {
        resultsDiv.innerHTML = '<p>No products found.</p>';
        return;
    }

    products.forEach(p => {
        resultsDiv.innerHTML += `
            <div style="
            border:1px solid #ccc;
            padding:10px;
            margin-bottom:10px;
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            ">
            <div style="flex:1; padding-right:15px;">
                <strong>${p.name}</strong><br><br>

                ${p.description ?? ''}<br><br>

                <em>Category:</em> ${p.category}<br>
                <em>Shop:</em> ${p.shop}<br><br>

                <strong>${p.price} €</strong><br><br>

                <a href="item.php?shop=${p.shop}&product_id=${p.product_id}">
                    View details
                </a>
                <br><br>

                <button onclick="addToCart('${p.shop}', ${p.product_id})">
                    Add to cart
                </button>
            </div>

            ${p.image ? `
                <div style="min-width:220px; text-align:right;">
                    <img src="${p.image}" style="max-width:220px; height:auto;">
                </div>
            ` : ''}
            </div>
        `;
    });
}

/* =========================
   ADD TO CART
========================= */
function addToCart(source, productId)
{
    fetch('../api/reserve.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            shop: source,
            product_id: productId,
            quantity: 1
        }),
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
        if (res.ok || res.success)
        {
            cart.push({ source, product_id: productId, quantity: 1 });
            renderCart();
        }
        else
        {
            alert(res.error || 'Error reserving product');
        }
    });
}

/* =========================
   RENDER CART
========================= */
function renderCart()
{
    if (cart.length === 0)
    {
        cartDiv.innerHTML = '<p>Cart is empty.</p>';
        return;
    }

    let html = '<ul>';
    cart.forEach(i => {
        html += `
            <li>
                ${i.source} - Product #${i.product_id} (x${i.quantity})
            </li>
        `;
    });
    html += '</ul>';

    cartDiv.innerHTML = html;
}

/* =========================
   CHECKOUT
========================= */
function checkout()
{
    fetch('../api/checkout.php', { method: 'POST' })
        .then(r => r.json())
        .then(res => {
            if (res.ok)
            {
                cart = [];
                renderCart();
                alert('Order completed!');
                window.location.href = 'orders.php';
            }
            else
            {
                alert('Checkout failed');
            }
        });
}