<?php
/**
 * Export to PHP Array plugin for PHPMyAdmin
 * @version 5.2.1
 */

/**
 * Database `ecommerce`
 */

/* `ecommerce`.`categories` */
$categories = array(
  array('id' => '1','name' => 'Lips'),
  array('id' => '2','name' => 'Eyes'),
  array('id' => '3','name' => 'Face'),
  array('id' => '4','name' => 'Tools')
);

/* `ecommerce`.`orders` */
$orders = array(
);

/* `ecommerce`.`products` */
$products = array(
  array('id' => '1','subcategory_id' => '1','name' => 'Swan Ballet Shine Lipstick','description' => 'The perfect everyday neutral. Little Star is a nude apricot with creamy undertones for a polished, effortless glow.','price' => '20.00','image' => 'src/1766183828_6945d39412722.png','stock' => '25','shipping_cost' => '6'),
  array('id' => '2','subcategory_id' => '1','name' => 'Little Angel Matte Lipstick','description' => '','price' => '18.00','image' => 'src/lipstickMatte.png','stock' => '17','shipping_cost' => '6'),
  array('id' => '3','subcategory_id' => '2','name' => 'Violet Strawberry Rococo Glowy Lip Gloss','description' => '','price' => '16.00','image' => 'src/gloss.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '4','subcategory_id' => '3','name' => 'Butterfly Cloud Collar Mascara','description' => '','price' => '20.00','image' => 'src/mascara.png','stock' => '17','shipping_cost' => '6'),
  array('id' => '5','subcategory_id' => '4','name' => 'Swan Ballet Six-Color Makeup Palette','description' => '','price' => '32.00','image' => 'src/eyeshadow.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '6','subcategory_id' => '5','name' => 'Butterfly Cloud Collar Liquid Eyeliner','description' => '','price' => '15.00','image' => 'src/eyeliner.png','stock' => '18','shipping_cost' => '6'),
  array('id' => '7','subcategory_id' => '6','name' => 'The Sweetie Bear Dual-Ended Brow Gel & Pencil','description' => '','price' => '12.50','image' => 'src/brows.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '8','subcategory_id' => '7','name' => 'The Sweetie Bear 4-Color Concealer Palette','description' => '','price' => '32.00','image' => 'src/concealer.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '9','subcategory_id' => '8','name' => 'Butterfly Cloud Collar Embossed Highlight & Contour Palette','description' => '','price' => '35.00','image' => 'src/contour.png','stock' => '17','shipping_cost' => '6'),
  array('id' => '10','subcategory_id' => '9','name' => 'Strawberry Rococo Embossed Blush','description' => '','price' => '22.50','image' => 'src/blushPowder.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '11','subcategory_id' => '9','name' => 'Strawberry Cupid All Day Glow Liquid Blush','description' => '','price' => '19.80','image' => 'src/blushLiquid.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '12','subcategory_id' => '10','name' => 'Little Angel Embossed Highlighter','description' => '','price' => '24.00','image' => 'src/powderHighlighter.png','stock' => '17','shipping_cost' => '6'),
  array('id' => '13','subcategory_id' => '10','name' => 'Midsummer Fairytales Liquid Highlighter (Multichrome)','description' => '','price' => '31.50','image' => 'src/liquidHighlighter.png','stock' => '19','shipping_cost' => '6'),
  array('id' => '14','subcategory_id' => '11','name' => 'The Sweetie Bear Rounded Blush Brush','description' => '','price' => '15.50','image' => 'src/brush.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '15','subcategory_id' => '12','name' => 'Swan Ballet Hand Mirror','description' => '','price' => '25.00','image' => 'src/swanMirror.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '16','subcategory_id' => '12','name' => 'Strawberry Cupid Hand Mirror','description' => '','price' => '25.00','image' => 'src/pinkMirror.png','stock' => '20','shipping_cost' => '6'),
  array('id' => '17','subcategory_id' => '12','name' => 'The Sweetie Bear Hand Mirror','description' => '','price' => '25.00','image' => 'src/brownMirror.png','stock' => '20','shipping_cost' => '6')
);

/* `ecommerce`.`product_properties` */
$product_properties = array(
  array('product_id' => '1','property_value_id' => '2'),
  array('product_id' => '1','property_value_id' => '4'),
  array('product_id' => '1','property_value_id' => '9'),
  array('product_id' => '2','property_value_id' => '1'),
  array('product_id' => '2','property_value_id' => '6'),
  array('product_id' => '2','property_value_id' => '9'),
  array('product_id' => '3','property_value_id' => '2'),
  array('product_id' => '3','property_value_id' => '3'),
  array('product_id' => '3','property_value_id' => '9'),
  array('product_id' => '4','property_value_id' => '1'),
  array('product_id' => '4','property_value_id' => '7'),
  array('product_id' => '4','property_value_id' => '9'),
  array('product_id' => '5','property_value_id' => '1'),
  array('product_id' => '5','property_value_id' => '2'),
  array('product_id' => '5','property_value_id' => '8'),
  array('product_id' => '5','property_value_id' => '10'),
  array('product_id' => '6','property_value_id' => '1'),
  array('product_id' => '6','property_value_id' => '7'),
  array('product_id' => '6','property_value_id' => '9'),
  array('product_id' => '7','property_value_id' => '1'),
  array('product_id' => '7','property_value_id' => '5'),
  array('product_id' => '7','property_value_id' => '9'),
  array('product_id' => '8','property_value_id' => '2'),
  array('product_id' => '8','property_value_id' => '8'),
  array('product_id' => '8','property_value_id' => '9'),
  array('product_id' => '9','property_value_id' => '2'),
  array('product_id' => '9','property_value_id' => '5'),
  array('product_id' => '9','property_value_id' => '10'),
  array('product_id' => '10','property_value_id' => '1'),
  array('product_id' => '10','property_value_id' => '3'),
  array('product_id' => '10','property_value_id' => '10'),
  array('product_id' => '11','property_value_id' => '2'),
  array('product_id' => '11','property_value_id' => '3'),
  array('product_id' => '11','property_value_id' => '9'),
  array('product_id' => '12','property_value_id' => '2'),
  array('product_id' => '12','property_value_id' => '10'),
  array('product_id' => '13','property_value_id' => '2'),
  array('product_id' => '13','property_value_id' => '8'),
  array('product_id' => '13','property_value_id' => '9'),
  array('product_id' => '14','property_value_id' => '3'),
  array('product_id' => '15','property_value_id' => '8'),
  array('product_id' => '16','property_value_id' => '3'),
  array('product_id' => '17','property_value_id' => '5')
);

/* `ecommerce`.`properties` */
$properties = array(
  array('id' => '1','name' => 'finish'),
  array('id' => '2','name' => 'color'),
  array('id' => '3','name' => 'format')
);

/* `ecommerce`.`property_values` */
$property_values = array(
  array('id' => '1','property_id' => '1','value' => 'matte'),
  array('id' => '2','property_id' => '1','value' => 'glow'),
  array('id' => '3','property_id' => '2','value' => 'pink'),
  array('id' => '4','property_id' => '2','value' => 'red'),
  array('id' => '5','property_id' => '2','value' => 'brown'),
  array('id' => '6','property_id' => '2','value' => 'peachy'),
  array('id' => '7','property_id' => '2','value' => 'black'),
  array('id' => '8','property_id' => '2','value' => 'multicolor'),
  array('id' => '9','property_id' => '3','value' => 'cream'),
  array('id' => '10','property_id' => '3','value' => 'powder')
);

/* `ecommerce`.`subcategories` */
$subcategories = array(
  array('id' => '1','category_id' => '1','name' => 'Lipstick'),
  array('id' => '2','category_id' => '1','name' => 'Gloss'),
  array('id' => '3','category_id' => '2','name' => 'Mascara'),
  array('id' => '4','category_id' => '2','name' => 'Eyeshadow'),
  array('id' => '5','category_id' => '2','name' => 'Eyeliner'),
  array('id' => '6','category_id' => '2','name' => 'Eyebrow'),
  array('id' => '7','category_id' => '3','name' => 'Concealer'),
  array('id' => '8','category_id' => '3','name' => 'Contour'),
  array('id' => '9','category_id' => '3','name' => 'Blush'),
  array('id' => '10','category_id' => '3','name' => 'Highlighter'),
  array('id' => '11','category_id' => '4','name' => 'Brush'),
  array('id' => '12','category_id' => '4','name' => 'Mirror')
);

/* `ecommerce`.`users` */
$users = array(
  array('id' => '1','email' => 'lucixsg.ordenador@gmail.com','password_hash' => '$2y$10$Vl5toATEBpnYy9diPKR/7eREoKD0dUExVMjInlnUiHRGggKEuWcIy','is_admin' => '1','is_active' => '1','activation_token' => NULL,'reset_token' => NULL,'created_at' => '2025-12-20 01:13:56'),
  array('id' => '2','email' => 'luciasollen@gmail.com','password_hash' => '$2y$10$BaFDmjZ4ZaUUGZr4PvkLG.xwp4bnP6pKkG8nnzCuluFMRT8cd32Da','is_admin' => '0','is_active' => '1','activation_token' => NULL,'reset_token' => NULL,'created_at' => '2025-12-20 01:33:15')
);
