<?php
session_start();
require_once "Connection.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$props = isset($_GET['prop']) ? $_GET['prop'] : [];
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id_desc';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$perPage = 8;



$whereParts = ["1=1"];
$whereParams = [];
$joinProps = "";
$joinParams = [];
$idx = 0;

// Search
if ($q !== '') {
    $whereParts[] = "(i.name LIKE ? OR i.description LIKE ?)";
    $whereParams[] = "%$q%";
    $whereParams[] = "%$q%";
}

// Filters
if ($category_id && !empty($props)) {
    foreach ($props as $propId => $val) {
        $val = trim($val);
        if ($val === '') continue;

        $alias = "ip$idx";

        $joinProps .= " JOIN item_properties $alias
            ON $alias.item_id = i.id
            AND $alias.property_id = ?
            AND LOWER($alias.value) LIKE LOWER(?) ";

        $joinParams[] = (int)$propId;
        $joinParams[] = "%" . $val . "%";

        $idx++;
    }
}

// Filter category
if ($category_id) {
    $whereParts[] = "i.category_id = ?";
    $whereParams[] = $category_id;
}

$params = array_merge($joinParams, $whereParams);

//order
$orderBy = "i.id DESC";
if ($sort === 'price_asc') $orderBy = "i.price ASC";
if ($sort === 'price_desc') $orderBy = "i.price DESC";

//pag
$sqlCount = "SELECT COUNT(DISTINCT i.id)
             FROM items i
             $joinProps
             WHERE " . implode(" AND ", $whereParts);

$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$total = $stmt->fetchColumn();

$pages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;



$sql = "SELECT DISTINCT i.*
        FROM items i
        $joinProps
        WHERE " . implode(" AND ", $whereParts) . "
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);



$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$allProps = $pdo->query("SELECT * FROM category_properties")->fetchAll(PDO::FETCH_ASSOC);

$propsByCategory = [];
foreach ($allProps as $p) {
    $propsByCategory[$p['category_id']][] = $p;
}

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">

<title>Catalog</title>
</head>

<body>
<p>
    <a href="orders.php">
        <button>My Orders</button>
    </a>
</p>
<a href="index.php?logout=1">Log out</a>
<h1>Catalog</h1>

<form method="GET" id="filterForm">

    Search:
    <input type="text" name="q" value="<?php echo htmlspecialchars($q) ?>">

    Category:
    <select name="category" onchange="document.getElementById('filterForm').submit()">
        <option value="">All</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?php echo $c['id'] ?>"
                <?php if ($category_id == $c['id']) echo 'selected' ?>>
                <?php echo htmlspecialchars($c['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    Order:
    <select name="sort" onchange="document.getElementById('filterForm').submit()">
        <option value="created_at_desc" <?php if($sort=='id_desc') echo 'selected' ?>>Most recent</option>
        <option value="price_asc" <?php if($sort=='price_asc') echo 'selected' ?>>Price (asc)</option>
        <option value="price_desc" <?php if($sort=='price_desc') echo 'selected' ?>>Price (desc)</option>
    </select>

    <button type="submit">Filter</button>

    <button type="button" onclick="window.location='catalog.php'">Delete filters</button>

    <div id="propsArea">
        <?php if ($category_id && isset($propsByCategory[$category_id])): ?>
            <h4>Properties</h4>
            <?php foreach ($propsByCategory[$category_id] as $p): ?>
                <?php $val = isset($_GET['prop'][$p['id']]) ? $_GET['prop'][$p['id']] : ''; ?>
                <?php echo htmlspecialchars($p['property_name']); ?>:
                <input type="text" name="prop[<?php echo $p['id'] ?>]"
                       value="<?php echo htmlspecialchars($val) ?>"><br>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</form>

<hr>

<?php if (empty($items)): ?>

    <p>There are no matching items.</p>

<?php else: ?>

    <?php foreach ($items as $it): ?>
        <div style="border:1px solid #ccc; padding:8px; margin-bottom:8px;">
            <h3>
                <a href="item.php?id=<?php echo $it['id'] ?>">
                    <?php echo htmlspecialchars($it['name']) ?>
                </a>
            </h3>

            <p><?php echo nl2br(htmlspecialchars($it['description'])) ?></p>
            <p>Price: €<?php echo $it['price'] ?> — Stock: <?php echo $it['stock'] ?></p>

            <?php if ($it['image']): ?>
                <img src="<?php echo $it['image'] ?>" width="120">
            <?php endif; ?>

            <p>
                <a href="item.php?id=<?php echo $it['id'] ?>">View</a> |
                <a href="cart.php?action=add&id=<?php echo $it['id'] ?>&qty=1">Add to the cart</a>
            </p>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<div>
    <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">« previous</a>
    <?php endif; ?>

    Pag <?php echo $page ?> of <?php echo max(1,$pages) ?>

    <?php if ($page < $pages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">next »</a>
    <?php endif; ?>
</div>

</body>
</html>
