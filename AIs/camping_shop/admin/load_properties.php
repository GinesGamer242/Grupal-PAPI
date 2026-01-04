<?php

require __DIR__ . '/../config/conn.php';

$cat = (int)($_GET['cat'] ?? 0);

// Retrieve properties for the selected category
$stmt = $pdo->prepare("SELECT * FROM category_properties WHERE category_id = ?");
$stmt->execute([$cat]);
$props = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Predefined select options for specific properties
$selectOptions = [
    "type" => ["igloo", "tipi", "instant"],
    "material" => ["fiber", "feather"],
    "seasons" => ["2", "3", "4"],
    "size" => ["small", "medium", "large"],
];

// Dynamically generate form inputs based on properties
foreach ($props as $p)
{
    $name = ($p['property_name']);
    $prop_id = $p['id'];

    echo "<label>" . htmlspecialchars($p['property_name']) . ": ";

    // Select input for predefined option lists
    if (isset($selectOptions[$name]))
    {
        echo "<select name='property[$prop_id]'>";
        echo "<option value=''>Select</option>";

        foreach ($selectOptions[$name] as $op)
        {
            echo "<option value='$op'>$op</option>";
        }

        echo "</select>";
    }
    // Numeric input for numeric properties
    elseif ($name === "capacity" || $name === "volume")
    {
        echo "<input type='number' name='property[$prop_id]' min='0'>";
    }
    // Default text input for other properties
    else
    {
        echo "<input type='text' name='property[$prop_id]'>";
    }

    echo "</label><br><br>";
}

?>