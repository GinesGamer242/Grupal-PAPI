<?php
session_start();
require_once "Connection.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; 
}

$action = isset($_GET['action']) ? $_GET['action'] : null;

// ADD PRODUCT
if ($action === 'add' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $qty = isset($_GET['qty']) ? max(1,(int)$_GET['qty']) : 1;

    $stmt = $pdo->prepare("SELECT stock FROM items WHERE id=?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        die("Item not found");
    }

    $current = $_SESSION['cart'][$id] ?? 0;

    if ($current + $qty > $item['stock']) {
        $_SESSION['cart_error'] = "Not enough stock (available: {$item['stock']}).";
    } else {
        $_SESSION['cart'][$id] = $current + $qty;
    }

    header("Location: cart.php");
    exit;
}

// REMOVE PRODUCT
if ($action === 'remove' && isset($_GET['id'])) {
    unset($_SESSION['cart'][(int)$_GET['id']]);
    header("Location: cart.php");
    exit;
}

// CLEAR CART
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// UPDATE CART
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {

    foreach ($_POST['qty'] as $id => $qty) {
        $id = (int)$id;
        $qty = max(0, (int)$qty);

        if ($qty == 0) {
            unset($_SESSION['cart'][$id]);
            continue;
        }

        $stmt = $pdo->prepare("SELECT stock FROM items WHERE id=?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item && $qty <= $item['stock']) {
            $_SESSION['cart'][$id] = $qty;
        } else {
            $_SESSION['cart_error'] = "Not enough stock for item ID {$id}.";
        }
    }

    header("Location: cart.php");
    exit;
}

// CART
$cart = $_SESSION['cart'];
$items = [];
$total = 0;
$shipping_total = 0;

if (!empty($cart)) {
    $ids = array_keys($cart);
    $in = implode(",", array_fill(0, count($ids), "?"));

    $stmt = $pdo->prepare("SELECT * FROM items WHERE id IN ($in)");
    $stmt->execute($ids);

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $i) {
        $qty = $cart[$i['id']];
        $subtotal = $qty * $i['price'];
        $shipping_subtotal = $i['shipping_cost'];
        $total += $subtotal + $shipping_subtotal;
        $shipping_total += $shipping_subtotal;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
</head>
<body>

<h1>Shopping Cart</h1>

<?php
if (isset($_SESSION['cart_error'])) {
    echo "<p style='color:red'>" . $_SESSION['cart_error'] . "</p>";
    unset($_SESSION['cart_error']);
}
?>

<?php if (empty($cart)): ?>
    <p>Your cart is empty.</p>
    <a href="catalog.php">Back to catalog</a>

<?php else: ?>

<form method="POST">
<table border="1" cellpadding="6">
<tr>
    <th>Product</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Subtotal</th>
    <th>Actions</th>
</tr>

<?php foreach ($items as $i): 
$qty = $cart[$i['id']];
$subtotal = $qty * $i['price'];
$shipping_subtotal = $i['shipping_cost'];
?>

<tr>
    <td><?= htmlspecialchars($i['name']) ?></td>
    <td>€<?= number_format($i['price'], 2) ?></td>
    <td>
        <input type="number" name="qty[<?= $i['id'] ?>]" value="<?= $qty ?>" min="1" max="<?= $i['stock'] ?>">
    </td>
    <td>€<?= number_format($subtotal, 2) ?> + €<?= number_format($shipping_subtotal, 2) ?> shipping</td>
    <td>
        <a href="cart.php?action=remove&id=<?= $i['id'] ?>">Remove</a>
    </td>
</tr>

<?php endforeach; ?>

<tr>
    <td colspan="3"><strong>Products Subtotal</strong></td>
    <td>€<?= number_format($total - $shipping_total, 2) ?></td>
    <td></td>
</tr>
<tr>
    <td colspan="3"><strong>Shipping Subtotal</strong></td>
    <td>€<?= number_format($shipping_total, 2) ?></td>
    <td></td>
</tr>
<tr>
    <td colspan="3"><strong>Total</strong></td>
    <td>€<?= number_format($total, 2) ?></td>
    <td></td>
</tr>

</table>

<br>
<button type="submit" name="update_cart">Update Cart</button>
<a href="cart.php?action=clear" onclick="return confirm('Clear cart?')">Clear Cart</a>
</form>

<br>
<a href="checkout.php">Proceed to Checkout</a>
<p><a href="catalog.php">← Back to catalog</a></p>

<?php endif; ?>

</body>
</html>
