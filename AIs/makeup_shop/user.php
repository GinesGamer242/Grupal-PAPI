<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// DB
$pdo = new PDO(
    "mysql:host=localhost;dbname=ecommerce;charset=utf8",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$action = $_GET['action'] ?? 'shop';
if ($action === 'shop') $action = 'list';


//ADD / REMOVE
if ($action === 'add_to_cart' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p && $p['stock'] > 0) {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
        $pdo->prepare("UPDATE products SET stock = stock - 1 WHERE id = ?")->execute([$id]);
    }
    header('Location: index.php?action=shop');
    exit;
}

if ($action === 'remove_from_cart' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (isset($_SESSION['cart'][$id])) {
        $qty = $_SESSION['cart'][$id];
        unset($_SESSION['cart'][$id]);
        $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$qty, $id]);
    }
    header('Location: index.php?action=cart');
    exit;
}

//CHECKOUT
if ($action === 'checkout') {
    if (!empty($_SESSION['cart'])) {
        $total = 0;
        $items = [];
        foreach ($_SESSION['cart'] as $id => $qty) {
            $stmt = $pdo->prepare("SELECT name, price, image FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $p = $stmt->fetch();
            if ($p) {
                $items[] = ['id'=>$id,'name'=>$p['name'],'qty'=>$qty,'price'=>$p['price'],'image'=>$p['image']];
                $total += $p['price'] * $qty;
            }
        }
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, items, total) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'] ?? 1, json_encode($items), $total]);
        $_SESSION['cart'] = [];
        echo "<h2 style='color:green;text-align:center;'>Order placed! Total: $".$total."</h2><div style='text-align:center;'><a href='index.php'><button>Back to shop</button></a></div>";
        exit;
    } else {
        echo "<h2 style='text-align:center;'>Your cart is empty!</h2><div style='text-align:center;'><a href='index.php'><button>Back to shop</button></a></div>";
        exit;
    }
}

//FILTERS
$where = [];
$params = [];
$joins = '';

if (!empty($_GET['category'])) {
    $where[] = 'c.id = ?';
    $params[] = $_GET['category'];
}

if (!empty($_GET['subcategory'])) {
    $where[] = 's.id = ?';
    $params[] = $_GET['subcategory'];
}

if (!empty($_GET['search'])) {
    $where[] = 'p.name LIKE ?';
    $params[] = '%'.$_GET['search'].'%';
}

/* Property filters */
$propertyValues = ['finish','color','format'];
$propertyFilters = [];
foreach ($propertyValues as $prop) {
    if (!empty($_GET[$prop])) $propertyFilters[$prop] = $_GET[$prop];
}

if ($propertyFilters) {
    $joins .= " JOIN product_properties pp ON pp.product_id = p.id
                JOIN property_values pv ON pv.id = pp.property_value_id
                JOIN properties pr ON pr.id = pv.property_id";
    foreach ($propertyFilters as $prop => $val) {
        $where[] = '(pr.name = ? AND pv.value = ?)';
        $params[] = $prop;
        $params[] = $val;
    }
}

$whereSQL = $where ? 'WHERE '.implode(' AND ', $where) : '';

//SORT & PAGINATION 
$sort = $_GET['sort'] ?? 'p.name';
$order = $_GET['order'] ?? 'ASC';
$allowedSort = ['p.name','p.price'];
if (!in_array($sort, $allowedSort)) $sort = 'p.name';
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6; // 3 por fila, 2 filas por página
$offset = ($page - 1) * $perPage;

//FETCH PRODUCTS 
$sql = "
SELECT DISTINCT p.*, s.name as subcategory, c.name as category
FROM products p
JOIN subcategories s ON p.subcategory_id = s.id
JOIN categories c ON s.category_id = c.id
$joins
$whereSQL
ORDER BY $sort $order
LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

//HTML / CSS 
?>
<style>
body {font-family: Arial, sans-serif;text-align:center;}
.product-grid {display:flex;flex-wrap:wrap;justify-content:center;margin:20px 0;}
.product-card {border:1px solid #ccc;padding:50px;margin:10px;width:300px;text-align:center;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
.product-card img {width:150px;height:150px;object-fit:cover;margin-bottom:10px;}
button {padding:7px 12px;margin:5px;background:#28a745;color:white;border:none;border-radius:5px;cursor:pointer;}
button:hover {background:#218838;}
.cart-link {float:right;font-weight:bold;margin:20px;}
.cart-table {width:80%;margin:20px auto;border-collapse:collapse;}
.cart-table th, .cart-table td {border:1px solid #ccc;padding:10px;text-align:center;}
</style>

<h1>Shop</h1>
<?php if (isset($_SESSION['user_id'])): ?>
    <div style="position:absolute; top:15px; right:20px;">
        <a href="index.php?action=logout">Logout</a>
    </div>
<?php endif; ?>
<?php if (!empty($_SESSION['is_admin'])): ?>
    <a href="index.php?action=dashboard">Admin panel</a>
<?php endif; ?>


<div class="cart-link">
    <a href="index.php?action=cart">Cart (<?= array_sum($_SESSION['cart']) ?>)</a>
</div>

<?php if($action==='list'): ?>
<form method="GET" action="index.php">
<input type="hidden" name="action" value="shop">

    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <select name="category">
        <option value="">All categories</option>
        <?php foreach ($pdo->query("SELECT * FROM categories") as $c):
            $sel = ($_GET['category'] ?? '') == $c['id'] ? 'selected' : '';
        ?>
        <option value="<?= $c['id'] ?>" <?= $sel ?>><?= $c['name'] ?></option>
        <?php endforeach; ?>
    </select>
    <select name="subcategory">
        <option value="">All subcategories</option>
        <?php foreach ($pdo->query("SELECT * FROM subcategories") as $s):
            $sel = ($_GET['subcategory'] ?? '') == $s['id'] ? 'selected' : '';
        ?>
        <option value="<?= $s['id'] ?>" <?= $sel ?>><?= $s['name'] ?></option>
        <?php endforeach; ?>
    </select>

    <?php
    foreach ($propertyValues as $prop):
        echo "<select name='$prop'><option value=''>Any $prop</option>";
        $stmt = $pdo->prepare("SELECT value FROM property_values pv JOIN properties p ON p.id = pv.property_id WHERE p.name=?");
        $stmt->execute([$prop]);
        foreach ($stmt->fetchAll() as $v):
            $sel = ($_GET[$prop] ?? '') == $v['value'] ? 'selected' : '';
            echo "<option value='{$v['value']}' $sel>{$v['value']}</option>";
        endforeach;
        echo "</select>";
    endforeach;
    ?>
    <select name="sort">
        <option value="p.name">Sort by name</option>
        <option value="p.price">Sort by price</option>
    </select>
    <select name="order">
        <option value="ASC">ASC</option>
        <option value="DESC">DESC</option>
    </select>
    <button>Filter</button>
</form>

<div class="product-grid">
<?php foreach($products as $p): ?>
<div class="product-card">
    <?php if($p['image']): ?>
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
    <?php endif; ?>
    <h3><?= htmlspecialchars($p['name']) ?></h3>
    <p>$<?= $p['price'] ?></p>
    <p>Stock: <?= $p['stock'] ?></p>
    <a href="index.php?action=view&id=<?= $p['id'] ?>"><button>View</button></a>
    <a href="index.php?action=add_to_cart&id=<?= $p['id'] ?>"><button>Add to cart</button></a>
</div>
<?php endforeach; ?>
</div>

<?php
$totalStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT p.id) as cnt
    FROM products p
    JOIN subcategories s ON p.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    $joins
    $whereSQL
");
$totalStmt->execute($params);
$totalRows = $totalStmt->fetch()['cnt'];
$totalPages = ceil($totalRows / $perPage);
?>
<div>
<?php if($page>1): ?>
    <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>"><button>Prev</button></a>
<?php endif; ?>
<?php if($page<$totalPages): ?>
    <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>"><button>Next</button></a>
<?php endif; ?>
</div>
<?php endif; ?>

<!-- ================= SINGLE PRODUCT ================= -->
<?php
if ($action==='view' && isset($_GET['id'])):
    $id=(int)$_GET['id'];
    $stmt=$pdo->prepare("
        SELECT p.*, s.name as subcategory, c.name as category
        FROM products p
        JOIN subcategories s ON p.subcategory_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE p.id=?
    ");
    $stmt->execute([$id]);
    $p=$stmt->fetch();
    if ($p):
        $stmt=$pdo->prepare("
            SELECT pr.name, pv.value
            FROM product_properties pp
            JOIN property_values pv ON pv.id=pp.property_value_id
            JOIN properties pr ON pr.id=pv.property_id
            WHERE pp.product_id=?
        ");
        $stmt->execute([$id]);
        $props=$stmt->fetchAll();
?>
<h2><?= htmlspecialchars($p['name']) ?></h2>
<?php if($p['image']): ?>
    <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:200px;">
<?php endif; ?>
<p>Category: <?= $p['category'] ?> - <?= $p['subcategory'] ?></p>
<p>Price: $<?= $p['price'] ?> - Stock: <?= $p['stock'] ?></p>
<p><?= htmlspecialchars($p['description']) ?></p>
<ul>
<?php foreach($props as $prop): ?>
    <li><?= $prop['name'] ?>: <?= $prop['value'] ?></li>
<?php endforeach; ?>
</ul>
<a href="index.php?action=add_to_cart&id=<?= $p['id'] ?>"><button>Add to cart</button></a>
<a href="index.php"><button>Back to shop</button></a>
<?php
    endif;
endif;
?>

<!-- ================= CART VIEW ================= -->
<?php if ($action==='cart'): ?>
<h2>Your Cart</h2>
<?php if(!empty($_SESSION['cart'])): ?>
<table class="cart-table">
<tr><th>Image</th><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th>Action</th></tr>
<?php foreach($_SESSION['cart'] as $id=>$qty):
    $stmt=$pdo->prepare("SELECT name, price, image FROM products WHERE id=?");
    $stmt->execute([$id]);
    $p=$stmt->fetch();
?>
<tr>
    <td><?php if($p['image']): ?><img src="<?= htmlspecialchars($p['image']) ?>" style="width:60px;"><?php endif; ?></td>
    <td><?= htmlspecialchars($p['name']) ?></td>
    <td><?= $qty ?></td>
    <td>$<?= $p['price'] ?></td>
    <td>$<?= $p['price']*$qty ?></td>
    <td><a href="index.php?action=remove_from_cart&id=<?= $id ?>"><button>Remove</button></a></td>
</tr>
<?php endforeach; ?>
<tr><td colspan="4"><strong>Total</strong></td><td colspan="2"><strong>$<?= array_sum(array_map(function($id,$q){global $pdo;$p=$pdo->query("SELECT price FROM products WHERE id=$id")->fetch();return $p['price']*$q;},array_keys($_SESSION['cart']),$_SESSION['cart'])) ?></strong></td></tr>
</table>
<div>
<a href="index.php?action=checkout"><button>Checkout</button></a>
<a href="index.php"><button>Back to shop</button></a>
</div>
<?php else: ?>
<p>Your cart is empty.</p>
<a href="index.php"><button>Back to shop</button></a>
<?php endif; ?>
<?php endif; ?>
