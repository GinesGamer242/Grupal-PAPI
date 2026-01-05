<?php
session_start();
require_once "Connection.php";

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

$message = '';

// Create an item
if (isset($_POST['create_item'])) {

    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $shipping_cost = $_POST['shipping_cost'];
    $category_id = $_POST['category_id'];

    if (!empty($_FILES['image']['name'])) {
        if(!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        $imagePath = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    } else {
        $imagePath = NULL;
    }

    $stmt = $pdo->prepare("INSERT INTO items (category_id, name, description, price, stock, shipping_cost, image)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $name, $description, $price, $stock, $shipping_cost, $imagePath]);
    $item_id = $pdo->lastInsertId();

    if(isset($_POST['properties']) && is_array($_POST['properties'])){
        foreach($_POST['properties'] as $property_id => $value){
            $stmt = $pdo->prepare("INSERT INTO item_properties (item_id, property_id, value) VALUES (?, ?, ?)");
            $stmt->execute([$item_id, $property_id, $value]);
        }
    }

    $message = "Item created correctly.";
}

// Eliminate item
if (isset($_GET['delete_item'])) {

    $itemId = $_GET['delete_item'];

    $pdo->prepare("DELETE FROM item_properties WHERE item_id = ?")->execute([$itemId]);
    $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$itemId]);

    $message = "Item successfully removed.";
}


// Actualice item
if (isset($_POST['update_item'])) {

    $id = $_POST['item_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $shipping_cost = $_POST['shipping_cost'];

    if (!empty($_FILES['image']['name'])) {
        $imagePath = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    } else {
        $imagePath = $_POST['current_image'];  
    }

    $stmt = $pdo->prepare("
        UPDATE items 
        SET name=?, description=?, price=?, stock=?, shipping_cost=?, image=? 
        WHERE id=?
    ");
    $stmt->execute([$name, $description, $price, $stock, $shipping_cost, $imagePath, $id]);

    if (isset($_POST['properties'])) {
        foreach($_POST['properties'] as $propId => $value){
            $stmt = $pdo->prepare("
                UPDATE item_properties 
                SET value=? 
                WHERE item_id=? AND property_id=?
            ");
            $stmt->execute([$value, $id, $propId]);
        }
    }

    $message = "Item successfully updated.";
}


// Categories and properties
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$allProperties = $pdo->query("SELECT * FROM category_properties")->fetchAll(PDO::FETCH_ASSOC);
$propertiesByCategory = [];
foreach($allProperties as $prop){
    $propertiesByCategory[$prop['category_id']][] = $prop;
}




// Eliminate user 
if (isset($_GET['delete_user'])) {
    $userId = $_GET['delete_user'];

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    $message = "User successfully deleted.";
}


// Send order
if (isset($_GET['send_order'])) {
    $id = $_GET['send_order'];
    $pdo->prepare("UPDATE orders SET status='sent' WHERE id=?")->execute([$id]);
    $message = "Order marked as shipped.";
}


// Cancel order
if (isset($_GET['cancel_order'])) {
    $id = $_GET['cancel_order'];

    $stmt = $pdo->prepare("SELECT item_id, quantity FROM order_items WHERE order_id=?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $it) {
        $pdo->prepare("
            UPDATE items 
            SET stock = stock + ? 
            WHERE id = ?
        ")->execute([$it['quantity'], $it['item_id']]);
    }

    $pdo->prepare("UPDATE orders SET status='cancelled' WHERE id=?")->execute([$id]);

    $message = "Order successfully cancelled. Stock restored.";
}



// Eliminate order
if (isset($_GET['delete_order'])) {
    $id = $_GET['delete_order'];

    $stmt = $pdo->prepare("SELECT item_id, quantity FROM order_items WHERE order_id=?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $it) {
        $pdo->prepare("
            UPDATE items 
            SET stock = stock + ? 
            WHERE id = ?
        ")->execute([$it['quantity'], $it['item_id']]);
    }

    $pdo->prepare("DELETE FROM order_items WHERE order_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM orders WHERE id=?")->execute([$id]);

    $message = "Order cancelled. Stock restored.";
}






?>

<!DOCTYPE html>
<html>
<head>
    <title>Administration Panel</title>
</head>
<body>
    
<h2>Administration Panel</h2>
<a href="index.php?logout=1">Logout</a>
<p><?php echo $message; ?></p>







<h3>Create new item</h3>
<form method="POST" enctype="multipart/form-data">
    Name: <input type="text" name="name" required><br>
    Description: <textarea name="description"></textarea><br>
    Price: <input type="number" step="0.01" name="price" required><br>
    Stock: <input type="number" name="stock" required><br>
    Shipping cost: <input type="number" step="0.01" name="shipping_cost" required><br>

    Category:
    <select name="category_id" id="categorySelect" required onchange="showProperties()">
        <option value="">Select category</option>
        <?php foreach($categories as $category): ?>
            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
        <?php endforeach; ?>
    </select><br>

    <div id="categoryProperties"></div>

    Image: <input type="file" name="image"><br>

    <button type="submit" name="create_item">Create item</button>
</form>
<script>
const propertiesByCategory = <?= json_encode($propertiesByCategory) ?>;

function showProperties(){
    const categoryId = document.getElementById("categorySelect").value;
    const container = document.getElementById("categoryProperties");
    container.innerHTML = '';

    if(categoryId === '' || !propertiesByCategory[categoryId]) return;

    propertiesByCategory[categoryId].forEach(prop => {
        container.innerHTML += `${prop.property_name}: <input type="text" name="properties[${prop.id}]"><br>`;
    });
}
</script>








<h3>List of items</h3>
<?php
$items = $pdo->query("
    SELECT i.*, c.name AS category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);

foreach($items as $item){

    echo "<b>{$item['name']}</b> - Category: {$item['category_name']} - Price: \${$item['price']} - Stock: {$item['stock']}<br>";

    $stmt = $pdo->prepare("
        SELECT cp.id AS prop_id, cp.property_name, ip.value
        FROM item_properties ip
        JOIN category_properties cp ON ip.property_id = cp.id
        WHERE ip.item_id=?
    ");
    $stmt->execute([$item['id']]);
    $props = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<form method='POST' enctype='multipart/form-data' style='margin:10px 0;'>";
    echo "<input type='hidden' name='item_id' value='{$item['id']}'>";
    echo "Name: <input type='text' name='name' value='{$item['name']}'><br>";
    echo "Description: <textarea name='description'>{$item['description']}</textarea><br>";
    echo "Price: <input type='number' step='0.01' name='price' value='{$item['price']}'><br>";
    echo "Stock: <input type='number' name='stock' value='{$item['stock']}'><br>";
    echo "Shipping cost: <input type='number' step='0.01' name='shipping_cost' value='{$item['shipping_cost']}'><br>";

    foreach ($props as $p){
        echo "{$p['property_name']}: <input type='text' name='properties[{$p['prop_id']}]' value='{$p['value']}'><br>";
    }

    if($item['image']){
        echo "<img src='{$item['image']}' width='60'><br>";
        echo "<input type='hidden' name='current_image' value='{$item['image']}'>";
    }

    echo "Change image: <input type='file' name='image'><br>";

    echo "<button type='submit' name='update_item'>Update</button> ";
    echo "<a href='Admin.php?delete_item={$item['id']}' onclick='return confirm(\"¿Eliminate item?\");'>Eliminate</a>";
    echo "</form>";

    echo "<hr>";
}
?>







<h3>List of users</h3>
<?php
$users = $pdo->query("SELECT id, email, is_admin, is_active FROM users")->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $u) {

    echo "<b>ID:</b> {$u['id']} - ";
    echo "<b>Email:</b> {$u['email']} - ";
    echo "<b>Admin:</b> " . ($u['is_admin'] ? 'Sí' : 'No') . " - ";
    echo "<b>Active:</b> " . ($u['is_active'] ? 'Sí' : 'No');

    echo " | <a href='Admin.php?delete_user={$u['id']}' onclick='return confirm(\"¿Eliminate user?\")'>
            Eliminate
          </a>";

    echo "<br><hr>";
}
?>





<h3>List of orders</h3>

<?php
$orders = $pdo->query("
    SELECT o.*, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($orders as $o) {

    echo "<b>Orden #{$o['id']}</b><br>";
    echo "User: {$o['email']}<br>";
    echo "Total: \${$o['total']}<br>";
    echo "State: {$o['status']}<br>";
    echo "Date: {$o['created_at']}<br>";

    echo "<u>Product:</u><br>";

    $stmt = $pdo->prepare("
        SELECT oi.*, i.name 
        FROM order_items oi
        JOIN items i ON oi.item_id = i.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$o['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $it) {
        echo "- {$it['name']} (x{$it['quantity']}) — \${$it['price']} c/u<br>";
    }

    echo "<a href='Admin.php?send_order={$o['id']}'>Mark sent</a> | ";
    echo "<a href='Admin.php?cancel_order={$o['id']}'>Cancel</a> | ";
    echo "<a href='Admin.php?delete_order={$o['id']}' onclick='return confirm(\"¿Eliminate order?\")'>Eliminate</a>";

    echo "<hr>";
}
?>



</body>
</html>
