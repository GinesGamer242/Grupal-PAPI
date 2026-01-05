INSERT INTO items (category_id, name, description, price, shipping_cost, stock, image_path, created_at)
VALUES
(1,'Igloo Tent Solo','Lightweight 1-person igloo tent',79.90,8.50,12,'../uploads/images/igloo_solo.jpg',NOW()),
(1,'Tipi Breeze 2','Compact tipi-style tent for 2 people',129.00,9.90,20,'../uploads/images/tipi_breeze2.jpg',NOW()),
(1,'Instant Pop 3','Instant setup tent for 3 people',149.90,10.00,14,'../uploads/images/instant_pop3.jpg',NOW()),
(1,'Igloo Family 4','Durable igloo tent for small families (4-person)',169.00,10.90,8,'../uploads/images/igloo_family4.jpg',NOW()),
(1,'Tipi Tribe Large','Large tipi tent for groups (5+ people)',249.00,15.00,5,'../uploads/images/tipi_tribe_large.jpg',NOW()),
(1,'Instant QuickCamp 2','Fast-deploy instant tent for 2',139.50,9.50,18,'../uploads/images/quickcamp2.jpg',NOW()),
(1,'Igloo Alpine 3','3-person igloo tent designed for cold climates',189.90,12.00,10,'../uploads/images/igloo_alpine3.jpg',NOW()),
(1,'Tipi Adventure 1','Solo tipi tent for light expeditions',99.00,8.00,22,'../uploads/images/tipi_adv1.jpg',NOW()),
(1,'Instant Storm 4','Instant tent reinforced for storms (4-persons)',229.90,13.00,6,'../uploads/images/instant_storm4.jpg',NOW()),
(1,'Igloo MegaCamp','Large igloo tent for 6+ persons',279.00,16.00,7,'../uploads/images/igloo_megacamp.jpg',NOW());

SET @base_tent_id = (SELECT MAX(id) - 9 FROM items);

INSERT INTO item_property_values (item_id, property_id, value) VALUES
(@base_tent_id + 0, 1, 'igloo'),       (@base_tent_id + 0, 2, '1'),
(@base_tent_id + 1, 1, 'tipi'),        (@base_tent_id + 1, 2, '2'),
(@base_tent_id + 2, 1, 'instant'),     (@base_tent_id + 2, 2, '3'),
(@base_tent_id + 3, 1, 'igloo'),       (@base_tent_id + 3, 2, '4'),
(@base_tent_id + 4, 1, 'tipi'),        (@base_tent_id + 4, 2, '5'),
(@base_tent_id + 5, 1, 'instant'),     (@base_tent_id + 5, 2, '2'),
(@base_tent_id + 6, 1, 'igloo'),       (@base_tent_id + 6, 2, '3'),
(@base_tent_id + 7, 1, 'tipi'),        (@base_tent_id + 7, 2, '1'),
(@base_tent_id + 8, 1, 'instant'),     (@base_tent_id + 8, 2, '4'),
(@base_tent_id + 9, 1, 'igloo'),       (@base_tent_id + 9, 2, '6');


INSERT INTO items (category_id, name, description, price, shipping_cost, stock, image_path, created_at)
VALUES
(2,'Fiber Light 2-Season','Light sleeping bag made of synthetic fiber',49.90,5.90,40,'../uploads/images/fiber2.jpg',NOW()),
(2,'Plume Trek 3-Season','Warm natural-feather sleeping bag for 3 seasons',119.00,6.90,25,'../uploads/images/plume3.jpg',NOW()),
(2,'Fiber Storm 4-Season','Synthetic 4-season sleeping bag designed for harsh climates',139.00,7.00,15,'../uploads/images/fiber4.jpg',NOW()),
(2,'Plume Compact 2-Season','Compact feather sleeping bag for mild temperatures',99.00,5.90,30,'../uploads/images/plume2.jpg',NOW()),
(2,'Fiber Pro 3-Season','Versatile fiber sleeping bag for autumn and spring',79.90,6.50,35,'../uploads/images/fiber3.jpg',NOW()),
(2,'Plume Arctic 4-Season','Extreme feather sleeping bag for winter',199.00,7.50,10,'../uploads/images/plume4.jpg',NOW()),
(2,'Fiber Junior 2-Season','2-season fiber sleeping bag for kids',39.90,4.90,50,'../uploads/images/fiber_kids.jpg',NOW()),
(2,'Plume Summit 3-Season','Professional feather bag for altitude trekking',149.00,6.90,20,'../uploads/images/plume_summit3.jpg',NOW()),
(2,'Fiber Eco 3-Season','Eco-friendly fiber sleeping bag',69.90,5.90,28,'../uploads/images/fiber_eco3.jpg',NOW()),
(2,'Plume Ultra 4-Season','High-end 4-season feather sleeping bag',229.00,7.90,8,'../uploads/images/plume_ultra4.jpg',NOW());

SET @base_bag_id = (SELECT MAX(id) - 9 FROM items);

INSERT INTO item_property_values (item_id, property_id, value) VALUES
(@base_bag_id + 0, 3, 'fiber'),   (@base_bag_id + 0, 4, '2'),
(@base_bag_id + 1, 3, 'plume'),   (@base_bag_id + 1, 4, '3'),
(@base_bag_id + 2, 3, 'fiber'),   (@base_bag_id + 2, 4, '4'),
(@base_bag_id + 3, 3, 'plume'),   (@base_bag_id + 3, 4, '2'),
(@base_bag_id + 4, 3, 'fiber'),   (@base_bag_id + 4, 4, '3'),
(@base_bag_id + 5, 3, 'plume'),   (@base_bag_id + 5, 4, '4'),
(@base_bag_id + 6, 3, 'fiber'),   (@base_bag_id + 6, 4, '2'),
(@base_bag_id + 7, 3, 'plume'),   (@base_bag_id + 7, 4, '3'),
(@base_bag_id + 8, 3, 'fiber'),   (@base_bag_id + 8, 4, '3'),
(@base_bag_id + 9, 3, 'plume'),   (@base_bag_id + 9, 4, '4');


INSERT INTO items (category_id, name, description, price, shipping_cost, stock, image_path, created_at)
VALUES
(3,'Urban Pack Small','Small day-trip backpack (20L)',39.90,6.00,50,'../uploads/images/back_small1.jpg',NOW()),
(3,'Trekking Medium 35L','Medium trekking backpack',69.90,7.00,40,'../uploads/images/back_med35.jpg',NOW()),
(3,'Mountain Large 55L','Large alpine backpack',119.00,8.00,25,'../uploads/images/back_large55.jpg',NOW()),
(3,'Explorer Small 25L','Compact 25L pack for daily hiking',49.90,6.00,60,'../uploads/images/back_small25.jpg',NOW()),
(3,'Voyager Medium 45L','Versatile 45L backpack',89.00,7.00,35,'../uploads/images/back_med45.jpg',NOW()),
(3,'Expedition Large 70L','70L expedition pack for long trips',149.00,9.00,15,'../uploads/images/back_large70.jpg',NOW()),
(3,'Compact Travel 18L','Small light travel bag (18L)',34.90,5.50,70,'../uploads/images/back_small18.jpg',NOW()),
(3,'Trail Runner 32L','Hybrid 32L fast-hiking pack',74.90,7.00,42,'../uploads/images/back_med32.jpg',NOW()),
(3,'ProClimb 50L','Professional 50L climbing pack',129.00,8.50,20,'../uploads/images/back_large50.jpg',NOW()),
(3,'MegaHaul 80L','80L extra-capacity expedition backpack',169.00,10.00,10,'../uploads/images/back_large80.jpg',NOW());

SET @base_back_id = (SELECT MAX(id) - 9 FROM items);

INSERT INTO item_property_values (item_id, property_id, value) VALUES
(@base_back_id + 0, 5, 'small'),   (@base_back_id + 0, 6, '20'),
(@base_back_id + 1, 5, 'medium'),  (@base_back_id + 1, 6, '35'),
(@base_back_id + 2, 5, 'large'),   (@base_back_id + 2, 6, '55'),
(@base_back_id + 3, 5, 'small'),   (@base_back_id + 3, 6, '25'),
(@base_back_id + 4, 5, 'medium'),  (@base_back_id + 4, 6, '45'),
(@base_back_id + 5, 5, 'large'),   (@base_back_id + 5, 6, '70'),
(@base_back_id + 6, 5, 'small'),   (@base_back_id + 6, 6, '18'),
(@base_back_id + 7, 5, 'medium'),  (@base_back_id + 7, 6, '32'),
(@base_back_id + 8, 5, 'large'),   (@base_back_id + 8, 6, '50'),
(@base_back_id + 9, 5, 'large'),   (@base_back_id + 9, 6, '80');