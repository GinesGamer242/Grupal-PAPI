 <?php
 $pdo = new PDO("mysql:host=127.0.0.1;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS individualtaskii CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE individualtaskii");

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating base data and inserting categories and items<br>";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $tables = [
        "order_items", "orders", "item_properties", "items",
        "category_properties", "categories", "users"
    ];
    foreach ($tables as $t) {
        try { $pdo->exec("DROP TABLE IF EXISTS `$t`"); } catch (Exception $e) {}
    }

    $queries = [

"CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

"CREATE TABLE `category_properties` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `property_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

"CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

"CREATE TABLE `item_properties` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

"CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','sent','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

"CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

"CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `activation_token` varchar(255) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `reset_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

    foreach ($queries as $q) $pdo->exec($q);


    $inserts = [

"INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Bouquets'), (2, 'Plants'), (3, 'Seeds');",

"INSERT INTO `category_properties` (`id`, `category_id`, `property_name`) VALUES
(1, 1, 'Size (0-10)'),
(2, 1, 'Predominant color'),
(3, 2, 'Size (0-10)'),
(4, 2, 'Irrigation needs (0-10)'),
(5, 3, 'Season'),
(6, 3, 'Difficulty level (0-10)');",

"INSERT INTO `items` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `shipping_cost`, `image`) VALUES
(1, 2, 'WASHINGTONIA PALM', 'Washingtonia palms are native...', 29.99, 12, 3.99, 'uploads/palmera-washingtonia-.webp'),
(2, 2, 'PHALAENOPSIS ORCHID 1 STEM POT 12CM', 'The Phalaenopsis orchid...', 12.99, 20, 2.99, 'uploads/-orquidea-phalaenopsis-1-tallo-en-maceta-12cm-surtido-variado.webp'),
(3, 2, 'BIRD OF PARADISE WHITE FLOWER', 'Strelitzia Nicolai...', 7.99, 15, 2.99, 'uploads/ave-del-paraiso-flor-blanca-m-13.webp'),
(4, 3, 'ORGANIC SEEDS ROUND RED RADISH ', 'Organic seeds...', 1.69, 100, 1.99, 'uploads/semillas-ecologicas-rabanito-redondo-rojo-verdecora.webp'),
(5, 3, 'SUGAR BABY WATERMELON SEEDS', 'Sugar baby...', 1.49, 120, 1.99, 'uploads/semillas-sandia-sugar-baby-verdecora.webp'),
(6, 3, 'NANTES CARROT SEEDS 5', 'Nantes Carrot...', 1.49, 94, 1.99, 'uploads/semillas-zanahoria-nantesa-5-verdecora.webp'),
(7, 1, 'BOUQUET OF RED ROSES', 'Bouquet...', 60.00, 20, 4.99, 'uploads/ramo-super-love.webp'),
(8, 1, 'BOUQUET OF WHITE DAISIES', 'Bouquet...', 29.99, 28, 2.99, 'uploads/ramo-monoflor-margarita.webp'),
(9, 1, 'BOUQUET OF SUNFLOWERS', 'Bouquet...', 19.99, 2, 2.99, 'uploads/ramo-girasoles.webp'),
(10, 1, 'CHRISTMAS BOUQUET', 'Bouquet...', 34.99, 23, 2.99, 'uploads/ramo-navidad-dorado.webp'),
(11, 2, 'GERANIUM', 'Geranium...', 2.59, 53, 1.99, 'uploads/geranio.webp'),
(12, 3, 'STRELITZIA SEEDS (BIRD OF PARADISE)', 'Seeds...', 2.19, 103, 0.99, 'uploads/semillas-strelitzia-ave-paraiso.webp');",


"INSERT INTO `item_properties` (`id`, `item_id`, `property_id`, `value`) VALUES
(1, 1, 3, '9'),
(2, 1, 4, '8'),
(3, 2, 3, '5'),
(4, 2, 4, '6'),
(5, 3, 3, '9'),
(6, 3, 4, '8'),
(7, 4, 5, 'All the year'),
(8, 4, 6, '2'),
(9, 5, 5, 'Spring'),
(10, 5, 6, '5'),
(11, 6, 5, 'All the year'),
(12, 6, 6, '1'),
(13, 7, 1, '8'),
(14, 7, 2, 'Red'),
(15, 8, 1, '6'),
(16, 8, 2, 'White'),
(17, 9, 1, '5'),
(18, 9, 2, 'Yellow'),
(19, 10, 1, '7'),
(20, 10, 2, 'Green'),
(21, 11, 3, '5'),
(22, 11, 4, '9'),
(23, 12, 5, 'Summer'),
(24, 12, 6, '9');",

];
foreach ($inserts as $ins) $pdo->exec($ins);


$alter = [

"ALTER TABLE `categories` ADD PRIMARY KEY (`id`);
 ALTER TABLE `categories` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;",

"ALTER TABLE `category_properties` ADD PRIMARY KEY (`id`), ADD KEY `category_id` (`category_id`);
 ALTER TABLE `category_properties` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;",

"ALTER TABLE `items` ADD PRIMARY KEY (`id`), ADD KEY `category_id` (`category_id`);
 ALTER TABLE `items` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;",

"ALTER TABLE `item_properties` ADD PRIMARY KEY (`id`), ADD KEY `item_id` (`item_id`), ADD KEY `property_id` (`property_id`);
 ALTER TABLE `item_properties` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;",

"ALTER TABLE `orders` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);
 ALTER TABLE `orders` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;",

"ALTER TABLE `order_items` ADD PRIMARY KEY (`id`), ADD KEY `order_id` (`order_id`), ADD KEY `item_id` (`item_id`);
 ALTER TABLE `order_items` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;",

"ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`);
 ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;",

"ALTER TABLE `category_properties`
 ADD CONSTRAINT `category_properties_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;",

"ALTER TABLE `items`
 ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;",

"ALTER TABLE `item_properties`
 ADD CONSTRAINT `item_properties_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
 ADD CONSTRAINT `item_properties_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `category_properties` (`id`) ON DELETE CASCADE;",

"ALTER TABLE `orders`
 ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);",

"ALTER TABLE `order_items`
 ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
 ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);"
];

    foreach ($alter as $a) $pdo->exec($a);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Database created<br>";
?>
<a href="index.php?logout=1">Return to Autentification</a>
