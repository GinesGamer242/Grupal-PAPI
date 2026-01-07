<?php

require __DIR__ . '/../config/session.php';
require __DIR__ . '/../includes/header.php';

if (empty($_SESSION['user_id']))
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
fetch('/PAPI/Grupal-PAPI/api/orders.php')
    .then(r => r.json())
    .then(data => {

        const container = document.getElementById('orders');

        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<p>No orders found.</p>';
            return;
        }

        let html = '';

        data.forEach(order => {
            html += `
                <div style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
                    <strong>Order #${order.order_id}</strong><br>
                    <strong>Date:</strong> ${order.date}<br>
                    <strong>Status:</strong> ${order.status}<br>
                    <strong>Total:</strong> ${order.total} €<br>
                    <strong>Items:</strong>
                    <ul>
            `;

            order.items.forEach(item => {
                html += `
                    <li>
                        ${item.product_name} — ${item.quantity} × ${item.price} € 
                        (<em>${item.shop}</em>)
                    </li>
                `;
            });

            html += `
                    </ul>
                </div>
            `;
        });

        container.innerHTML = html;

    })
    .catch(err => {
        document.getElementById('orders').innerHTML =
            `<p>Error loading orders: ${err.message}</p>`;
    });
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
