<?php

require __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user']))
{
    header("Location: login.php");
    exit;
}

?>

<h2>My Orders</h2>

<div id="orders">
    <p>Loading orders...</p>
</div>

<script>
fetch('../api/orders.php')
    .then(r => r.json())
    .then(data => {

        if (!Array.isArray(data) || data.length === 0) {
            document.getElementById('orders').innerHTML =
                '<p>No orders found.</p>';
            return;
        }

        let html = '';

        data.forEach(o => {

            html += `
                <div style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
                    <strong>Order #${o.order_id}</strong><br>
                    <strong>Shop:</strong> ${o.shop}<br>
                    <strong>Date:</strong> ${o.date}<br>
                    <strong>Status:</strong> ${o.status}<br>
                    <strong>Total:</strong> ${o.total} €<br>
            `;

            if (o.items && o.items.length > 0)
            {
                html += '<ul>';
                o.items.forEach(it => {
                    html += `
                        <li>
                            ${it.name} — 
                            ${it.quantity} × ${it.price} €
                        </li>
                    `;
                });
                html += '</ul>';
            }

            html += '</div>';
        });

        document.getElementById('orders').innerHTML = html;
    })
    .catch(() => {
        document.getElementById('orders').innerHTML =
            '<p>Error loading orders.</p>';
    });
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>