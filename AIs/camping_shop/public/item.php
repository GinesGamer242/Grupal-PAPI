<?php
require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

$id = (int)($_GET['id'] ?? 0);

// Obtain item and category
$stmt = $pdo->prepare("
    SELECT i.*, c.name AS category
    FROM items i
    LEFT JOIN categories c ON c.id = i.category_id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item)
{
    echo "<p>Item not found</p>";
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// Obtain item properties
$propStmt = $pdo->prepare("
    SELECT cp.property_name, ipv.value
    FROM item_property_values ipv
    INNER JOIN category_properties cp ON cp.id = ipv.property_id
    WHERE ipv.item_id = ?
");
$propStmt->execute([$id]);
$properties = $propStmt->fetchAll(PDO::FETCH_ASSOC);

$prettyNames = [
    "type" => "Type",
    "capacity" => "Capacity",
    "material" => "Material",
    "seasons" => "Seasons",
    "size" => "Size",
    "volume" => "Volume",
];

?>

<h2><?= htmlspecialchars($item['name']) ?></h2>

<?php if (!empty($item['image_path'])): ?>
    <img 
        src="<?= htmlspecialchars($item['image_path']) ?>" 
        alt="Item image" 
        style="max-width:250px; display:block; margin-bottom:20px;"
    >
<?php endif; ?>

<p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
<p><strong>Price:</strong> <?= number_format($item['price'], 2) ?> €</p>
<p><strong>Shipping cost:</strong> <?= number_format($item['shipping_cost'], 2) ?> €</p>
<p><strong>Stock:</strong> <?= (int)$item['stock'] ?></p>

<p><?= nl2br(htmlspecialchars($item['description'])) ?></p>

<?php if ($properties): ?>
    <h3>Properties</h3>
    <ul>
        <?php foreach ($properties as $p): 
            $pname = strtolower($p['property_name']);
            $pretty = $prettyNames[$pname] ?? ucfirst($p['property_name']);
        ?>
            <li>
                <strong><?= htmlspecialchars($pretty) ?>:</strong> 
                <?= htmlspecialchars($p['value']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="cart.php">
    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">

    <?php if ($item['stock'] > 0): ?>
        <label>Amount:
            <input type="number" name="quantity" value="1" min="1" max="<?= (int)$item['stock'] ?>">
        </label>

        <button>Add to cart</button>
    <?php else: ?>
        <p style="color:red;">No stock.</p>
    <?php endif; ?>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>