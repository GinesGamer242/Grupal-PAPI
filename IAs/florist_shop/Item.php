<?php
session_start();
require_once "Connection.php";

//item id
if (!isset($_GET['id'])) { die("Unspecified item"); }
$id = (int)$_GET['id'];

//category item
$stmt = $pdo->prepare("SELECT i.*, c.name AS category_name FROM items i LEFT JOIN categories c ON i.category_id=c.id WHERE i.id=?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) die("Item not found");

//item properties
$stmt = $pdo->prepare("SELECT cp.property_name, ip.value FROM item_properties ip JOIN category_properties cp ON ip.property_id = cp.id WHERE ip.item_id = ?");
$stmt->execute([$id]);
$props = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title><?php echo htmlspecialchars($item['name']) ?></title></head><body>
<h1><?php echo htmlspecialchars($item['name']) ?></h1>
<p><?php echo nl2br(htmlspecialchars($item['description'])) ?></p>
<p>Category: <?php echo htmlspecialchars($item['category_name']) ?></p>
<p>Price: €<?php echo $item['price'] ?></p>
<p>Stock: <?php echo $item['stock'] ?></p>

<?php if ($item['image']): ?><img src="<?php echo $item['image'] ?>" width="200"><?php endif; ?>

<?php if (!empty($props)): ?>
    <h4>Property</h4>
    <ul>
    <?php foreach($props as $p): ?>
        <li><?php echo htmlspecialchars($p['property_name']) ?>: <?php echo htmlspecialchars($p['value']) ?></li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($item['stock'] > 0): ?>
    <form method="GET" action="Cart.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>">
        Amount: <input type="number" name="qty" value="1" min="1" max="<?php echo $item['stock'] ?>">
        <button type="submit">Add to the cart</button>
    </form>
<?php else: ?>
    <p style="color:red;">Sold out</p>
<?php endif; ?>

<p><a href="catalog.php">Back to catalog.</a></p>
</body></html>
