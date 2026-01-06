(() => {
    // =========================
    // GUARD: solo ejecutar en index (donde existe #q)
    // =========================
    const qInput = document.getElementById('q');
    if (!qInput) return;

    // =========================
    // ELEMENTS
    // =========================
    const resultsDiv     = document.getElementById('results');
    const maxPriceEl     = document.getElementById('maxPrice');
    const categoryFilter = document.getElementById('categoryFilter');
    const shopFilter     = document.getElementById('shopFilter');

    let allProducts = [];

    // =========================
    // SEARCH
    // =========================
    function search(query) {
        const q = query !== undefined ? query : qInput.value;

        fetch('/PAPI/Grupal-PAPI/api/search.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                allProducts = Array.isArray(data) ? data : [];
                populateFilters(allProducts);
                renderResults(allProducts);
            })
            .catch(err => {
                console.error('Search error:', err);
                resultsDiv.innerHTML = '<p>Error loading products</p>';
            });
    }

    // =========================
    // FILTERS
    // =========================
    function applyFilters() {
        if (allProducts.length === 0) return;

        let filtered = [...allProducts];

        const maxPrice = parseFloat(maxPriceEl.value);
        if (!isNaN(maxPrice)) {
            filtered = filtered.filter(p => Number(p.price) <= maxPrice);
        }

        const category = categoryFilter.value;
        if (category) {
            filtered = filtered.filter(p => p.category === category);
        }

        const shop = shopFilter.value;
        if (shop) {
            filtered = filtered.filter(p => p.shop === shop);
        }

        renderResults(filtered);
    }

    function populateFilters(products) {
        categoryFilter.innerHTML = '<option value="">All</option>';
        shopFilter.innerHTML     = '<option value="">All</option>';

        const categories = new Set();
        const shops      = new Set();

        products.forEach(p => {
            if (p.category) categories.add(p.category);
            if (p.shop) shops.add(p.shop);
        });

        categories.forEach(c => {
            categoryFilter.innerHTML += `<option value="${c}">${c}</option>`;
        });

        shops.forEach(s => {
            shopFilter.innerHTML += `<option value="${s}">${s}</option>`;
        });
    }

    // =========================
    // RENDER PRODUCTS
    // =========================
    function renderResults(products) {
        resultsDiv.innerHTML = '';

        if (!Array.isArray(products) || products.length === 0) {
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
                    gap:15px;
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
                    ${
                        p.image
                        ? `<img src="/PAPI/Grupal-PAPI/IAs/${p.shop}_shop/${p.image}"
                               style="max-width:200px;">`
                        : ''
                    }
                </div>
            `;
        });
    }

    // =========================
    // ADD TO CART (SERVER IS SOURCE OF TRUTH)
    // =========================
    function addToCart(shop, productId) {
        fetch('/PAPI/Grupal-PAPI/api/reserve.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                shop: shop,
                product_id: productId,
                quantity: 1
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert('Product added to cart');
            } else {
                alert(res.error || 'Error reserving product');
            }
        })
        .catch(err => {
            console.error('Add to cart error:', err);
            alert('Network error while reserving product');
        });
    }

    // =========================
    // EVENT LISTENERS
    // =========================
    qInput.addEventListener('keyup', e => {
        if (e.key === 'Enter') search();
    });

    maxPriceEl.addEventListener('input', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);
    shopFilter.addEventListener('change', applyFilters);

    window.addEventListener('DOMContentLoaded', search);

    // =========================
    // EXPOSE ONLY WHAT HTML NEEDS
    // =========================
    window.search       = search;
    window.applyFilters = applyFilters;
    window.addToCart    = addToCart;

})();
