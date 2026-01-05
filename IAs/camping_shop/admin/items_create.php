<?php

session_start();

// Redirect to login page if no user is logged in
if (empty($_SESSION['user']))
{
    header("Location: ../public/login.php");
    exit;
}

// Redirect to public index if the user is not an administrator
if (!$_SESSION['user']['is_admin'])
{
    header("Location: ../public/index.php");
    exit;
}

require __DIR__ . '/../config/conn.php';
require __DIR__ . '/../includes/header.php';

// Fetch all categories to populate the category select field
$cats = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

$ok = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    try
    {
        // Begin database transaction to ensure data consistency
        $pdo->beginTransaction();

        $name = $_POST['name'];
        $cat = (int)$_POST['category_id'];
        $price = (float)$_POST['price'];
        $shipping = (float)$_POST['shipping_cost'];
        $stock = (int)$_POST['stock'];
        $desc = $_POST['description'];
        $img = $_POST['image'];

        $stmt = $pdo->prepare("
            INSERT INTO items (category_id, name, description, price, shipping_cost, stock, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$cat, $name, $desc, $price, $shipping, $stock, $img]);

        // Get the ID of the newly created item
        $item_id = $pdo->lastInsertId();

        // Loop through all submitted properties for the item
        foreach ($_POST['property'] as $prop_id => $value)
        {
            if ($value === "" || $value === null) continue;

            $stmt = $pdo->prepare("
                INSERT INTO item_property_values (item_id, property_id, value)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$item_id, $prop_id, $value]);
        }

        // Commit transaction if everything succeeded
        $pdo->commit();
        $ok = "Item correctly created";

    }
    catch (Exception $e)
    {
        // Roll back transaction in case of error
        $pdo->rollBack();
        $err = "Error: " . $e->getMessage();
    }
}

?>

<h2>Create item</h2>

<?php if ($err): ?>
    <p style="color:red;"><?= htmlspecialchars($err) ?></p>
<?php endif; ?>

<?php if ($ok): ?>
    <p style="color:green;"><?= htmlspecialchars($ok) ?></p>
<?php endif; ?>

<form method="post">
    <label>Name: <input name="name" required></label><br>

    <label>Category:
        <select name="category_id" id="categorySelect" required>
            <option value="">Select</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>

    <div id="propertiesContainer"></div>

    <label>Price: <input name="price" type="number" step="0.01" required></label><br>
    <label>Shipping cost: <input name="shipping_cost" type="number" step="0.01"></label><br>
    <label>Stock: <input name="stock" type="number" required></label><br>
    <label>Description: <textarea name="description"></textarea></label><br>
    <label>Image (path): <input name="image"></label><br>

    <button>Create</button>
</form>

<script>

document.getElementById("categorySelect").addEventListener("change", function ()
{
    let cat = this.value;

    let container = document.getElementById("propertiesContainer");
    container.innerHTML = "";

    // Do nothing if no category is selected
    if (!cat) return;

    // AJAX request to load properties for the selected category
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "load_properties.php?cat=" + cat);
    xhr.onload = function () {
        container.innerHTML = this.responseText;
    };
    xhr.send();
});

</script>

<?php

require __DIR__ . '/../includes/footer.php';

?>