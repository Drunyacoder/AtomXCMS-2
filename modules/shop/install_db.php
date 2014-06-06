<?php
$db_prefix = Config::read('db.prefix');
$FpsInstallQueries = array();

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_attributes`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `label` varchar(100) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT 'text',
  `is_filterable` enum('0','1') NOT NULL DEFAULT '1',
  `params` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$FpsInstallQueries[] = "INSERT INTO `{$db_prefix}shop_attributes` (`id`, `group_id`, `title`, `label`, `type`, `is_filterable`) VALUES
(1, 1, 'CPU', 'CPU', 'text', '1'),
(2, 1, 'RAM', 'RAM', 'text', '1'),
(3, 2, 'size', 'size', 'text', '1'),
(4, 2, 'color', 'color', 'text', '1')";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_attributes_content`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_attributes_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$FpsInstallQueries[] = "INSERT INTO `{$db_prefix}shop_attributes_content` (`id`, `attribute_id`, `product_id`, `content`) VALUES
(1, 1, 1, '2.2Mhz'),
(2, 1, 2, '1.7Mhz'),
(3, 2, 1, '8 Gb'),
(4, 2, 2, '6 Gb'),
(5, 3, 3, '37'),
(6, 3, 4, '41'),
(7, 4, 3, 'red'),
(8, 4, 4, 'blue')";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_attributes_groups`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_attributes_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$FpsInstallQueries[] = "INSERT INTO `{$db_prefix}shop_attributes_groups` (`id`, `title`) VALUES
(1, 'PC'),
(2, 'Shoe')";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_categories`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `icon_image` varchar(255) NOT NULL DEFAULT '',
  `discount` int(11) NOT NULL DEFAULT '0',
  `view_on_home` enum('0','1') NOT NULL DEFAULT '1',
  `no_access` varchar(255) NOT NULL DEFAULT '',
  `hide_not_exists` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$FpsInstallQueries[] = "INSERT INTO `{$db_prefix}shop_categories` (`id`, `title`) VALUES (1, 'Компутеры'), (2, 'Обувка')";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_delivery_types`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_delivery_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(11,2) NOT NULL DEFAULT '0',
  `total_for_free` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$FpsInstallQueries[] = "INSERT INTO `{$db_prefix}shop_delivery_types` (`id`, `title`, `price`) VALUES (1, 'Почта России', 50)";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_orders`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `status` enum('process','delivery','complete') NOT NULL DEFAULT 'process',
  `total` decimal(11,2) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `delivery_address` text NOT NULL,
  `delivery_type_id` int(11) NOT NULL DEFAULT '0',
  `telephone` varchar(255) NOT NULL DEFAULT '',
  `first_name` varchar(30) NOT NULL DEFAULT '',
  `last_name` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";


$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_orders_products`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_orders_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";


$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_products`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_id` int(11) NOT NULL DEFAULT '0' COMMENT 'For similar products with a different some property',
  `stock_description` varchar(255) NOT NULL DEFAULT '' COMMENT 'For similar products with a different some property',
  `attributes_group_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `orders_cnt` int(11) NOT NULL DEFAULT '0',
  `comments_cnt` int(11) NOT NULL DEFAULT '0',
  `available` enum('0','1') NOT NULL DEFAULT '1',
  `commented` enum('0','1') NOT NULL DEFAULT '1',
  `view_on_home` enum('0','1') NOT NULL DEFAULT '1',
  `hide_not_exists` enum('0','1') NOT NULL DEFAULT '0',
  `article` varchar(50) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(11,2) NOT NULL DEFAULT '0',
  `discount` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$FpsInstallQueries[] = "INSERT INTO `{$db_prefix}shop_products` (`id`, `attributes_group_id`, `title`, `description`, `category_id`, `vendor_id`, `user_id`, `date`, `price`) VALUES
(1, 1, 'Razor Game PC', 'Один из лучших игровых ПК с умопомрачительными характеристиками', 1, 1, 1, NOW(), 1000),
(2, 1, 'Microsoft PC gen.1', 'ПК от всемирно известной компании Мелкософт, со всеми вытекающими', 1, 2, 1, NOW(), 700),
(3, 2, 'Imperia 1', 'Ботинки Империя. Подошва - ништяк, кожа - ништяк', 2, 3, 1, NOW(), 200),
(4, 2, 'Imperia 2', 'Ботинки Империя. Подошва - ништяк, кожа - ништяк', 2, 3, 1, NOW(), 170)";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}shop_vendors`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}shop_vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `logo_image` varchar(255) NOT NULL DEFAULT '',
  `discount` int(11) NOT NULL DEFAULT '0',
  `hide_not_exists` enum('0','1') NOT NULL DEFAULT '0',
  `view_on_home` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$FpsInstallQueries[] = "INSERT INTO `{$db_prefix}shop_vendors` (`id`, `title`) VALUES
(1, 'Razor'),
(2, 'Microsoft'),
(3, 'Imperia')";

