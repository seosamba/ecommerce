-- version: 2.1.0

-- 23/01/2014
-- version: 2.1.1
ALTER TABLE `shopping_cart_session` ADD COLUMN `discount_tax_rate` enum('0','1', '2', '3') COLLATE utf8_unicode_ci DEFAULT '0' AFTER `gateway`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `shipping_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Shipping Tax' AFTER `sub_total`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `discount_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Discount Tax' AFTER `shipping_tax`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `sub_total_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Sub total Tax' AFTER `discount_tax`;
ALTER TABLE `shopping_quote` ADD COLUMN `creator_id` int(10) unsigned DEFAULT '0' AFTER `edited_by`;

-- 23/02/2014
-- version: 2.1.2

-- 15/04/2014
-- version: 2.2.0
CREATE TABLE IF NOT EXISTS `shopping_filtering_attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Attribute ID',
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Attribute Name',
  `label` tinytext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Attribute Label',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS  `shopping_filtering_widget_settings` (
  `filter_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'Filter ID',
  `settings` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'Widget Settings',
  PRIMARY KEY (`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS  `shopping_filtering_tags_has_attributes` (
  `tag_id` int(10) unsigned NOT NULL,
  `attribute_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`attribute_id`),
  KEY `attribute_id` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_filtering_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL COMMENT 'Product ID',
  `attribute_id` int(10) unsigned NOT NULL COMMENT 'Attribute ID',
  `value` tinytext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Attribute Value',
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 29/05/2014
-- version: 2.2.1
ALTER TABLE `shopping_quote` ADD COLUMN `internal_note` text COLLATE utf8_unicode_ci AFTER `disclaimer`;

-- 30/05/2014
-- version: 2.2.2
CREATE TABLE IF NOT EXISTS `shopping_import_orders` (
  `real_order_id` int(10) unsigned NOT NULL,
  `import_order_id` VARCHAR(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`real_order_id`,`import_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- These alters are always the latest and updated version of the database
UPDATE `shopping_config` SET `value`='2.2.3' WHERE `name`='version';
SELECT value FROM `shopping_config` WHERE `name` = 'version';
