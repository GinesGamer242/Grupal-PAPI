<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


//DB
$pdo = new PDO(
    "mysql:host=localhost;dbname=ecommerce;charset=utf8",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

//LOGIN CHECK 
if (!isset($_SESSION["user_id"]) || empty($_SESSION["is_admin"])) {
    die("Access denied. Admins only.");
}


$action = $_GET["action"] ?? "dashboard";

//PROPERTIES
$properties = $pdo->query("SELECT * FROM properties")->fetchAll();
$property_values = $pdo->query("SELECT * FROM property_values")->fetchAll();

//HELPER
function getProductProperties($pdo, $product_id) {
    $stmt = $pdo->prepare("
        SELECT pv.value, p.name AS prop_name 
        FROM product_properties pp 
        JOIN property_values pv ON pp.property_value_id = pv.id
        JOIN properties p ON pv.property_id = p.id
        WHERE pp.product_id = ?
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll();
}

//PRODUCT CRUD
if (
    ($action === "create_product" || $action === "edit_product") &&
    $_SERVER["REQUEST_METHOD"] === "POST"
) {
    $id = $_POST["id"] ?? null;
    $name = trim($_POST["name"] ?? '');
    $subcategory_id = (int)($_POST["subcategory_id"] ?? 0);
    $price = (float)($_POST["price"] ?? 0);
    $description = $_POST["description"] ?? '';
    $stock = (int)($_POST["stock"] ?? 0);
    $shipping_cost = (float)($_POST["shipping_cost"] ?? 0);
    $props = $_POST["properties"] ?? [];

    if ($name === '' || $subcategory_id <= 0) die("Invalid product data");

    //IMAGE 
    $imagePath = $_POST["existing_image"] ?? null;

    if (!empty($_FILES["image"]["name"]) && $_FILES["image"]["error"] === 0) {
        $allowedExtensions = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) die("Invalid image extension");

        $targetDir = "src/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $filename = time() . "_" . uniqid() . "." . $ext;
        $imagePath = $targetDir . $filename;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            die("Error uploading image");
        }
    }

    if ($action === "create_product") {
        $stmt = $pdo->prepare("
            INSERT INTO products
            (subcategory_id, name, description, price, image, stock, shipping_cost)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$subcategory_id,$name,$description,$price,$imagePath,$stock,$shipping_cost]);
        $product_id = $pdo->lastInsertId();
    } else {
        $product_id = (int)$id;
        $stmt = $pdo->prepare("
            UPDATE products
            SET subcategory_id=?, name=?, description=?, price=?, image=?, stock=?, shipping_cost=?
            WHERE id=?
        ");
        $stmt->execute([$subcategory_id,$name,$description,$price,$imagePath,$stock,$shipping_cost,$product_id]);
        $pdo->prepare("DELETE FROM product_properties WHERE product_id=?")->execute([$product_id]);
    }

    $stmt = $pdo->prepare("INSERT INTO product_properties (product_id, property_value_id) VALUES (?, ?)");
    foreach ($props as $pv) $stmt->execute([$product_id, (int)$pv]);

    header("Location: index.php?action=dashboard");
    exit;
}

//DELETE PRODUCT
if ($action === "delete_product") {
    $id = (int)$_GET["id"];
    $pdo->prepare("DELETE FROM product_properties WHERE product_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    header("Location: index.php?action=dashboard");
    exit;
}

//USER MANAGEMENT
if ($action === "delete_user") {
    $id = (int)$_GET["id"];
    if ($id === (int)$_SESSION["user_id"]) die("You cannot delete yourself.");
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    header("Location: index.php?action=users");
    exit;
}

//ORDER UPDATE
if ($action === "update_order" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = isset($_POST["order_id"]) ? (int)$_POST["order_id"] : 0;
    $status = isset($_POST["status"]) ? trim($_POST["status"]) : '';

    if ($order_id > 0 && in_array($status, ['pending', 'sent', 'cancelled'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->execute([$status, $order_id]);
    }

    header("Location: index.php?action=orders");
    exit;
}

//HEADER / MENU
?>
<h1>Admin Dashboard</h1>
<a href="index.php?action=logout">Logout</a> |
<a href="index.php?action=dashboard">Products</a> |
<a href="index.php?action=users">Users</a> |
<a href="index.php?action=orders">Orders</a>
<hr>

<?php
//ORDERS 
if ($action === "orders"):
    $orders = $pdo->query("
        SELECT o.*, u.email 
        FROM orders o 
        JOIN users u ON o.user_id=u.id 
        ORDER BY o.id DESC
    ")->fetchAll();

    if (!is_array($orders)) $orders = [];
?>
<h2>Orders</h2>
<div class="orders-container" style="display:flex;flex-direction:column;gap:10px;max-height:600px;overflow-y:auto;">
<?php foreach ($orders as $o): ?>
    <div class="order-card" style="border:1px solid #ccc;padding:10px;background:#f9f9f9;">
        <strong>Order #<?= (int)$o['id'] ?></strong> by <?= htmlspecialchars($o['email']) ?><br>
        Status: <?= htmlspecialchars($o['status']) ?><br>
        Total: €<?= number_format($o['total'],2) ?><br>
        Address: <?= htmlspecialchars($o['shipping_address']) ?><br>

        <ul>
        <?php
        $items = json_decode($o['items'], true);
        if (is_array($items)):
            foreach ($items as $it):
        ?>
            <li><?= htmlspecialchars($it['name']) ?> × <?= (int)$it['qty'] ?> (€<?= number_format($it['price'],2) ?>)</li>
        <?php
            endforeach;
        else:
        ?>
            <li>No items</li>
        <?php endif; ?>
        </ul>

        <form method="POST" action="index.php?action=update_order">
            <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
            <select name="status">
                <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
                <option value="sent" <?= $o['status']=='sent'?'selected':'' ?>>Sent</option>
                <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
            <button>Update</button>
        </form>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php
//PRODUCTS
if ($action === "dashboard" || $action === "products"):
    $products = $pdo->query("SELECT p.*, s.name AS subname FROM products p JOIN subcategories s ON p.subcategory_id=s.id")->fetchAll();
?>
<h2>Products</h2>
<a href="index.php?action=create_product_form">Create New Product</a>
<ul>
<?php foreach ($products as $p):
    $props = getProductProperties($pdo,$p['id']);
    $propStr = implode(", ", array_map(fn($x)=>htmlspecialchars($x['prop_name']).": ".htmlspecialchars($x['value']),$props));
?>
<li>
<strong><?= htmlspecialchars($p['name']) ?></strong> (<?= htmlspecialchars($p['subname']) ?>) - €<?= $p['price'] ?><br>
Properties: <?= $propStr ?><br>
<?php if ($p['image']): ?><img src="<?= htmlspecialchars($p['image']) ?>" width="80"><br><?php endif; ?>
<a href="index.php?action=edit_product_form&id=<?= $p['id'] ?>">Edit</a> |
<a href="index.php?action=delete_product&id=<?= $p['id'] ?>">Delete</a>
</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php
//USERS 
if ($action === "users"):
    $users = $pdo->query("SELECT id,email,is_admin,is_active FROM users")->fetchAll();
?>
<h2>Users</h2>
<ul>
<?php foreach ($users as $u): ?>
<li>
<?= htmlspecialchars($u['email']) ?> - <?= $u['is_admin'] ? 'Admin' : 'User' ?> - <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
<?php if (!$u['is_admin']): ?> | <a href="index.php?action=delete_user&id=<?= $u['id'] ?>">Delete</a><?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<?php
//CREATE / EDIT PRODUCT FORM
if ($action==="create_product_form" || $action==="edit_product_form"):
    $product = ["id"=>"","name"=>"","subcategory_id"=>"","price"=>"","description"=>"","stock"=>"","shipping_cost"=>"","image"=>""];
    $selected_props = [];

    if ($action==="edit_product_form") {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
        $stmt->execute([(int)$_GET["id"]]);
        $product = $stmt->fetch();
        $selected_props = array_column(
            $pdo->query("SELECT property_value_id FROM product_properties WHERE product_id=".(int)$product['id'])->fetchAll(),
            'property_value_id'
        );
    }

    $subs = $pdo->query("SELECT * FROM subcategories")->fetchAll();
?>
<h2><?= $action==="create_product_form"?"Create Product":"Edit Product" ?></h2>
<form method="POST" action="index.php?action=<?= $action==="create_product_form"?"create_product":"edit_product" ?>" enctype="multipart/form-data">
<?php if($action==="edit_product_form"): ?>
    <input type="hidden" name="id" value="<?= $product['id'] ?>">
    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['image']) ?>">
<?php endif; ?>

Name:<br>
<input name="name" value="<?= htmlspecialchars($product['name']) ?>"><br><br>

Subcategory:<br>
<select name="subcategory_id">
<?php foreach ($subs as $s): ?>
<option value="<?= $s['id'] ?>" <?= $s['id']==$product['subcategory_id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
<?php endforeach; ?>
</select><br><br>

Price:<br>
<input name="price" value="<?= htmlspecialchars($product['price']) ?>"><br><br>

Stock:<br>
<input name="stock" value="<?= htmlspecialchars($product['stock']) ?>"><br><br>

Shipping Cost:<br>
<input name="shipping_cost" value="<?= htmlspecialchars($product['shipping_cost']) ?>"><br><br>

Description:<br>
<textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea><br><br>

Image:<br>
<input type="file" name="image"><br>
<?php if (!empty($product['image'])): ?>
Current image:<br>
<img src="<?= htmlspecialchars($product['image']) ?>" width="120"><br>
<?php endif; ?>

<h4>Properties</h4>
<?php foreach ($properties as $prop): ?>
<strong><?= htmlspecialchars($prop['name']) ?></strong><br>
<?php foreach ($property_values as $pv):
    if($pv['property_id']!=$prop['id']) continue; ?>
<label>
<input type="checkbox" name="properties[]" value="<?= $pv['id'] ?>" <?= in_array($pv['id'],$selected_props)?'checked':'' ?>>
<?= htmlspecialchars($pv['value']) ?>
</label>
<?php endforeach; ?><br><br>
<?php endforeach; ?>

<button><?= $action==="create_product_form"?"Create Product":"Update Product" ?></button>
</form>
<?php endif; ?>
