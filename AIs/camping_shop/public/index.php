<?php

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

// Retrieve all product categories
$stmt = $pdo->query("SELECT id, name FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Read filter parameters from GET request
$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';
$property_filters = $_GET['property'] ?? [];

// Load properties related to selected category
$properties = [];
if ($category_id) {
    $stmt = $pdo->prepare("SELECT * FROM category_properties WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Base query for items list
$query = "
    SELECT items.*, categories.name AS category
    FROM items
    LEFT JOIN categories ON items.category_id = categories.id
    WHERE 1
";

$params = [];

// Apply text search filter
if (!empty($search))
{
    $query .= " AND items.name LIKE ? ";
    $params[] = "%$search%";
}

// Apply category filter
if (!empty($category_id))
{
    $query .= " AND items.category_id = ? ";
    $params[] = $category_id;
}

// Apply dynamic property filters
foreach ($property_filters as $prop_id => $value)
{
    if ($value === '') continue;

    if ($value === "capacity_5plus") {
        $query .= "
            AND items.id IN (
                SELECT item_id FROM item_property_values
                WHERE property_id = $prop_id AND value >= 5
            )
        ";
        continue;
    }

    if ($value === "volume_50plus") {
        $query .= "
            AND items.id IN (
                SELECT item_id FROM item_property_values
                WHERE property_id = $prop_id AND value >= 50
            )
        ";
        continue;
    }

    if (str_contains($value, "_range_")) {
        list($min, $max) = explode("_range_", $value);
        $query .= "
            AND items.id IN (
                SELECT item_id FROM item_property_values
                WHERE property_id = $prop_id
                AND value BETWEEN $min AND $max
            )
        ";
        continue;
    }

    $query .= "
        AND items.id IN (
            SELECT item_id FROM item_property_values
            WHERE property_id = ? AND value LIKE ?
        )
    ";
    $params[] = $prop_id;
    $params[] = "%$value%";
}

// Execute final query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Items</h2>

<h2>Search and filter products</h2>

<form method="GET" action="" style="margin-bottom:20px;">
    <input type="text" name="search" placeholder="Search by name..."
           value="<?= htmlspecialchars($search) ?>">

    <select name="category" onchange="this.form.submit()">
        <option value="">-- All categories --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"
                <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (!empty($properties)): ?>
        <div style="margin-top:10px;">
            <h4>Filter by properties:</h4>

            <?php foreach ($properties as $prop): ?>
                <label><?= htmlspecialchars($prop['property_name']) ?>:</label>

                <?php

                // Render filter input based on property type
                if ($prop['property_name'] === "type"): ?>
                    <select name="property[<?= $prop['id'] ?>]">
                        <option value="">-- any --</option>
                        <option <?= ($property_filters[$prop['id']] ?? "") == "igloo" ? "selected":"" ?>>igloo</option>
                        <option <?= ($property_filters[$prop['id']] ?? "") == "tipi" ? "selected":"" ?>>tipi</option>
                        <option <?= ($property_filters[$prop['id']] ?? "") == "instant" ? "selected":"" ?>>instant</option>
                    </select>

                <?php elseif ($prop['property_name'] === "capacity"): ?>
                    <select name="property[<?= $prop['id'] ?>]">
                        <option value="">-- any --</option>
                        <option value="1" <?= (($property_filters[$prop['id']]??"")=="1")?"selected":"" ?>>1</option>
                        <option value="2" <?= (($property_filters[$prop['id']]??"")=="2")?"selected":"" ?>>2</option>
                        <option value="3" <?= (($property_filters[$prop['id']]??"")=="3")?"selected":"" ?>>3</option>
                        <option value="4" <?= (($property_filters[$prop['id']]??"")=="4")?"selected":"" ?>>4</option>
                        <option value="capacity_5plus" <?= (($property_filters[$prop['id']]??"")=="capacity_5plus")?"selected":"" ?>>5+</option>
                    </select>

                <?php elseif ($prop['property_name'] === "material"): ?>
                    <select name="property[<?= $prop['id'] ?>]">
                        <option value="">-- any --</option>
                        <option value="fiber" <?= (($property_filters[$prop['id']]??"")=="fiber")?"selected":"" ?>>fiber</option>
                        <option value="feathers" <?= (($property_filters[$prop['id']]??"")=="feathers")?"selected":"" ?>>feathers</option>
                    </select>

                <?php elseif ($prop['property_name'] === "seasons"): ?>
                    <select name="property[<?= $prop['id'] ?>]">
                        <option value="">-- any --</option>
                        <option value="2" <?= (($property_filters[$prop['id']]??"")=="2")?"selected":"" ?>>2</option>
                        <option value="3" <?= (($property_filters[$prop['id']]??"")=="3")?"selected":"" ?>>3</option>
                        <option value="4" <?= (($property_filters[$prop['id']]??"")=="4")?"selected":"" ?>>4</option>
                    </select>

                <?php elseif ($prop['property_name'] === "size"): ?>
                    <select name="property[<?= $prop['id'] ?>]">
                        <option value="">-- any --</option>
                        <option value="small" <?= (($property_filters[$prop['id']]??"")=="small")?"selected":"" ?>>small</option>
                        <option value="medium" <?= (($property_filters[$prop['id']]??"")=="medium")?"selected":"" ?>>medium</option>
                        <option value="large" <?= (($property_filters[$prop['id']]??"")=="large")?"selected":"" ?>>large</option>
                    </select>

                <?php elseif ($prop['property_name'] === "volume"): ?>
                    <select name="property[<?= $prop['id'] ?>]">
                        <option value="">-- any --</option>
                        <option value="0_range_30" <?= (($property_filters[$prop['id']]??"")=="0_range_30")?"selected":"" ?>>0–30 L</option>
                        <option value="30_range_50" <?= (($property_filters[$prop['id']]??"")=="30_range_50")?"selected":"" ?>>30–50 L</option>
                        <option value="volume_50plus" <?= (($property_filters[$prop['id']]??"")=="volume_50plus")?"selected":"" ?>>50+ L</option>
                    </select>

                <?php else: ?>
                    <input type="text"
                        name="property[<?= $prop['id'] ?>]"
                        value="<?= htmlspecialchars($property_filters[$prop['id']] ?? '') ?>">
                <?php endif; ?>

                <br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <br>
    <button type="submit">Apply filters</button>
</form>

<hr>

<ul>
<?php foreach ($items as $it): ?>
    <li>
        <img src="/PAPI/camping_shop/uploads/<?= $it['image_path']; ?>"
             style="max-width:40px;">
        <a href="item.php?id=<?= $it['id']; ?>">
            <?= htmlspecialchars($it['name']); ?>
        </a>
        (<?= htmlspecialchars($it['category']) ?>)
        - <?= number_format($it['price'],2) ?> €
        (Stock: <?= (int)$it['stock'] ?>)
    </li>
<?php endforeach; ?>
</ul>

<?php require __DIR__ . '/../includes/footer.php'; ?>