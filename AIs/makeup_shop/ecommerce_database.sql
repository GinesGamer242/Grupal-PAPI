-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-12-2025 a las 01:41:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ecommerce`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Lips'),
(2, 'Eyes'),
(3, 'Face'),
(4, 'Tools');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `items` text NOT NULL,
  `status` enum('pending','sent','cancelled') DEFAULT 'pending',
  `total` decimal(8,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `shipping_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `shipping_cost` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `subcategory_id`, `name`, `description`, `price`, `image`, `stock`, `shipping_cost`) VALUES
(1, 1, 'Swan Ballet Shine Lipstick', 'The perfect everyday neutral. Little Star is a nude apricot with creamy undertones for a polished, effortless glow.', 20.00, 'src/1766183828_6945d39412722.png', 25, 6),
(2, 1, 'Little Angel Matte Lipstick', '', 18.00, 'src/lipstickMatte.png', 17, 6),
(3, 2, 'Violet Strawberry Rococo Glowy Lip Gloss', '', 16.00, 'src/gloss.png', 20, 6),
(4, 3, 'Butterfly Cloud Collar Mascara', '', 20.00, 'src/mascara.png', 17, 6),
(5, 4, 'Swan Ballet Six-Color Makeup Palette', '', 32.00, 'src/eyeshadow.png', 20, 6),
(6, 5, 'Butterfly Cloud Collar Liquid Eyeliner', '', 15.00, 'src/eyeliner.png', 18, 6),
(7, 6, 'The Sweetie Bear Dual-Ended Brow Gel & Pencil', '', 12.50, 'src/brows.png', 20, 6),
(8, 7, 'The Sweetie Bear 4-Color Concealer Palette', '', 32.00, 'src/concealer.png', 20, 6),
(9, 8, 'Butterfly Cloud Collar Embossed Highlight & Contour Palette', '', 35.00, 'src/contour.png', 17, 6),
(10, 9, 'Strawberry Rococo Embossed Blush', '', 22.50, 'src/blushPowder.png', 20, 6),
(11, 9, 'Strawberry Cupid All Day Glow Liquid Blush', '', 19.80, 'src/blushLiquid.png', 20, 6),
(12, 10, 'Little Angel Embossed Highlighter', '', 24.00, 'src/powderHighlighter.png', 17, 6),
(13, 10, 'Midsummer Fairytales Liquid Highlighter (Multichrome)', '', 31.50, 'src/liquidHighlighter.png', 19, 6),
(14, 11, 'The Sweetie Bear Rounded Blush Brush', '', 15.50, 'src/brush.png', 20, 6),
(15, 12, 'Swan Ballet Hand Mirror', '', 25.00, 'src/swanMirror.png', 20, 6),
(16, 12, 'Strawberry Cupid Hand Mirror', '', 25.00, 'src/pinkMirror.png', 20, 6),
(17, 12, 'The Sweetie Bear Hand Mirror', '', 25.00, 'src/brownMirror.png', 20, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_properties`
--

CREATE TABLE `product_properties` (
  `product_id` int(11) NOT NULL,
  `property_value_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `product_properties`
--

INSERT INTO `product_properties` (`product_id`, `property_value_id`) VALUES
(1, 2),
(1, 4),
(1, 9),
(2, 1),
(2, 6),
(2, 9),
(3, 2),
(3, 3),
(3, 9),
(4, 1),
(4, 7),
(4, 9),
(5, 1),
(5, 2),
(5, 8),
(5, 10),
(6, 1),
(6, 7),
(6, 9),
(7, 1),
(7, 5),
(7, 9),
(8, 2),
(8, 8),
(8, 9),
(9, 2),
(9, 5),
(9, 10),
(10, 1),
(10, 3),
(10, 10),
(11, 2),
(11, 3),
(11, 9),
(12, 2),
(12, 10),
(13, 2),
(13, 8),
(13, 9),
(14, 3),
(15, 8),
(16, 3),
(17, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `properties`
--

INSERT INTO `properties` (`id`, `name`) VALUES
(1, 'finish'),
(2, 'color'),
(3, 'format');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `property_values`
--

CREATE TABLE `property_values` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `value` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `property_values`
--

INSERT INTO `property_values` (`id`, `property_id`, `value`) VALUES
(1, 1, 'matte'),
(2, 1, 'glow'),
(3, 2, 'pink'),
(4, 2, 'red'),
(5, 2, 'brown'),
(6, 2, 'peachy'),
(7, 2, 'black'),
(8, 2, 'multicolor'),
(9, 3, 'cream'),
(10, 3, 'powder');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`) VALUES
(1, 1, 'Lipstick'),
(2, 1, 'Gloss'),
(3, 2, 'Mascara'),
(4, 2, 'Eyeshadow'),
(5, 2, 'Eyeliner'),
(6, 2, 'Eyebrow'),
(7, 3, 'Concealer'),
(8, 3, 'Contour'),
(9, 3, 'Blush'),
(10, 3, 'Highlighter'),
(11, 4, 'Brush'),
(12, 4, 'Mirror');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 0,
  `activation_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `is_admin`, `is_active`, `activation_token`, `reset_token`, `created_at`) VALUES
(1, 'lucixsg.ordenador@gmail.com', '$2y$10$Vl5toATEBpnYy9diPKR/7eREoKD0dUExVMjInlnUiHRGggKEuWcIy', 1, 1, NULL, NULL, '2025-12-20 00:13:56'),
(2, 'luciasollen@gmail.com', '$2y$10$BaFDmjZ4ZaUUGZr4PvkLG.xwp4bnP6pKkG8nnzCuluFMRT8cd32Da', 0, 1, NULL, NULL, '2025-12-20 00:33:15');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indices de la tabla `product_properties`
--
ALTER TABLE `product_properties`
  ADD PRIMARY KEY (`product_id`,`property_value_id`),
  ADD KEY `property_value_id` (`property_value_id`);

--
-- Indices de la tabla `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `property_values`
--
ALTER TABLE `property_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indices de la tabla `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `property_values`
--
ALTER TABLE `property_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`);

--
-- Filtros para la tabla `product_properties`
--
ALTER TABLE `product_properties`
  ADD CONSTRAINT `product_properties_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_properties_ibfk_2` FOREIGN KEY (`property_value_id`) REFERENCES `property_values` (`id`);

--
-- Filtros para la tabla `property_values`
--
ALTER TABLE `property_values`
  ADD CONSTRAINT `property_values_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Filtros para la tabla `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
