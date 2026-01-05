const qInput     = document.getElementById('q');
const resultsDiv = document.getElementById('results');
const cartDiv    = document.getElementById('cart');
const maxPriceEl = document.getElementById('maxPrice');

let allProducts = [];
let cart = [];

/* =========================
   SEARCH
========================= */
/*function search()
{
    fetch('https:((/api/search.php?q=' + encodeURIComponent(qInput.value))
        .then(r => r.json())
        .then(data => {
            allProducts = data;
            renderResults(data);
        });
}
*/
function search(query) {
    const q = query !== undefined ? query : qInput.value;

    fetch('/PAPI/Grupal-PAPI/api/search.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            allProducts = data;
            populateFilters(data);
            renderResults(data);
        })
        .catch(err => {
            console.error(err);
            resultsDiv.innerHTML = '<p>Error loading products</p>';
        });
}





/* =========================
   FILTERS (client-side)
========================= */
function applyFilters()
{
    if (allProducts.length === 0) return;

    let filtered = [...allProducts];

    const maxPrice = parseFloat(maxPriceEl.value);
    if (!isNaN(maxPrice)) {
        filtered = filtered.filter(p => p.price <= maxPrice);
    }

    const category = document.getElementById('categoryFilter').value;
    if (category) {
        filtered = filtered.filter(p => p.category === category);
    }

    const shop = document.getElementById('shopFilter').value;
    if (shop) {
        filtered = filtered.filter(p => p.shop === shop);
    }

    renderResults(filtered);
}


function populateFilters(products)
{
    const categorySelect = document.getElementById('categoryFilter');
    const shopSelect = document.getElementById('shopFilter');

    categorySelect.innerHTML = '<option value="">All</option>';
    shopSelect.innerHTML = '<option value="">All</option>';

    const categories = new Set();
    const shops = new Set();

    products.forEach(p => {
        if (p.category) categories.add(p.category);
        if (p.shop) shops.add(p.shop);
    });

    categories.forEach(c => {
        categorySelect.innerHTML += `<option value="${c}">${c}</option>`;
    });

    shops.forEach(s => {
        shopSelect.innerHTML += `<option value="${s}">${s}</option>`;
    });
}


/* =========================
   RENDER PRODUCTS
========================= */
/*
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
}*/
function renderResults(products)
{
    resultsDiv.innerHTML = '';

    if (!Array.isArray(products) || products.length === 0)
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
            ">
                <div style="flex:1;">
                    <strong>${p.name}</strong><br><br>

                    ${p.description ?? ''}<br><br>

                    <em>Category:</em> ${p.category}<br>
                    <em>Shop:</em> ${p.shop}<br><br>

                    <strong>${p.price} €</strong><br><br>

                    <a href="item.php?shop=${p.shop}&product_id=${p.product_id}">
                        View details
                    </a><br><br>

                    <button onclick="addToCart('${p.shop}', ${p.product_id})">
                        Add to cart
                    </button>
                </div>

                ${p.image ? `
                    <div>
                        <img src="/PAPI/Grupal-PAPI/IAs/${p.shop}_shop/${p.image}"
                             style="max-width:200px;">
                    </div>
                ` : ''}
            </div>
        `;
    });
}


/* =========================
   ADD TO CART
========================= */
/*function addToCart(source, productId)
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
}*/

function addToCart(shop, productId) {
    fetch('/PAPI/Grupal-PAPI/api/reserve.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ shop, product_id: productId, quantity: 1 }),
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            // Actualizamos carrito en memoria
            const existing = cart.find(i => i.shop === shop && i.product_id === productId);
            if (existing) {
                existing.quantity += 1;
            } else {
                cart.push({ shop, product_id: productId, quantity: 1 });
            }
            renderCart();
        } else {
            alert(res.error || 'Error reserving product');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error reserving product');
    });
}


/* =========================
   RENDER CART
========================= */
/*function renderCart()
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
}*/

function renderCart() {
    if (cart.length === 0) {
        cartDiv.innerHTML = '<p>Cart is empty.</p>';
        return;
    }

    let html = '<ul>';
    cart.forEach(i => {
        html += `<li>${i.shop} - Product #${i.product_id} (x${i.quantity})</li>`;
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


qInput.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        search();
    }
});

// Auto-apply filters when user changes them
maxPriceEl.addEventListener('input', applyFilters);

document.getElementById('categoryFilter')
    .addEventListener('change', applyFilters);

document.getElementById('shopFilter')
    .addEventListener('change', applyFilters);


// Ejecutar búsqueda inicial al cargar la página
window.addEventListener('DOMContentLoaded', () => {
    search(); // busca todo con q=""
});
