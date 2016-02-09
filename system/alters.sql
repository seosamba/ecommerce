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


-- 30/05/2014
-- version: 2.2.2
CREATE TABLE IF NOT EXISTS `shopping_import_orders` (
  `real_order_id` int(10) unsigned NOT NULL,
  `import_order_id` VARCHAR(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`real_order_id`,`import_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 01/06/2014
-- version: 2.2.3
ALTER TABLE `shopping_filtering_values` ADD UNIQUE (`attribute_id`, `product_id`);

-- 29/09/2014
-- version: 2.2.4
UPDATE `page_option` SET `option_usage`='once' WHERE `page_option`.`id` = 'option_checkout';
UPDATE `page_option` SET `option_usage`='once' WHERE `page_option`.`id` = 'option_storethankyou';
UPDATE `page_option` SET `option_usage`='once' WHERE `page_option`.`id` = 'option_storeclientlogin';
UPDATE `page_option` SET `option_usage`='once' WHERE `page_option`.`id` = 'option_storeshippingterms';

-- 24/10/2014
-- version: 2.3.0
CREATE TABLE IF NOT EXISTS `shopping_pickup_location_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `img` varchar(300) COLLATE utf8_unicode_ci NULL,
  `external_category` varchar(200) COLLATE utf8_unicode_ci NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_pickup_location` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `address2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `working_hours` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `location_category_id` int(10) unsigned NOT NULL,
  `lat` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lng` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text 	COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `country` (`country`),
  INDEX `city` (`city`),
  INDEX `country_city` (`city`, `country`),
  FOREIGN KEY (`location_category_id`)
        REFERENCES `shopping_pickup_location_category`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_pickup_location_config` (
  `id` int(10) unsigned NOT NULL,
  `amount_type_limit` enum('up to','over','eachover') COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount_limit` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_pickup_location_zones` (
  `config_id` int(10) unsigned NOT NULL,
  `pickup_location_category_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `amount_location_category` decimal(10,2) DEFAULT NULL,
  `config_zone_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`config_id`, `config_zone_id`),
  FOREIGN KEY (`config_id`)
        REFERENCES `shopping_pickup_location_config`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_pickup_location_cart` (
  `cart_id` int(10) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `address2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `working_hours` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `location_category_id` int(10) unsigned NOT NULL,
  `lat` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lng` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`cart_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 24/11/2014
-- version: 2.3.1
-- Rename titles for templates
UPDATE `template_type` SET `title` = 'Checkout' WHERE `id` = 'typecheckout';
UPDATE `template_type` SET `title` = 'Product' WHERE `id` = 'typeproduct';

-- 10/12/2014
-- version: 2.3.2
-- Add column to store mobile phone country code
ALTER TABLE `shopping_customer_address` ADD `mobilecountrycode` VARCHAR( 2 ) NULL DEFAULT NULL COMMENT 'Contains mobile phone country code';

-- 20/04/2015
-- version: 2.4.0

-- 20/04/2015
-- version: 2.4.1
-- add recurring payments
CREATE TABLE IF NOT EXISTS `shopping_recurring_payment` (
  `cart_id` int(10) unsigned NOT NULL COMMENT 'Cart id',
  `subscription_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Subscription id',
  `ipn_tracking_id` VARCHAR (255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Ipn number',
  `gateway_type` VARCHAR (100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'Payment gateway name',
  `payment_period` VARCHAR (30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'Frequency of recurring payment',
  `recurring_times` SMALLINT unsigned NOT NULL COMMENT 'Amount of payments',
  `subscription_date` TIMESTAMP NOT NULL COMMENT 'Subscription date',
  `payment_cycle_amount` decimal(10,4) DEFAULT NULL COMMENT 'Amount for each recurring cycle',
  `total_amount_paid` decimal(10,4) DEFAULT NULL COMMENT 'Amount paid',
  `last_payment_date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Last payment date',
  `next_payment_date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Next payment date',
  `recurring_status` ENUM('new', 'active', 'pending', 'expired', 'suspended', 'canceled') DEFAULT 'new' NOT NULL COMMENT 'Recurring payment status',
  `accept_changing_next_billing_date` ENUM('0', '1') DEFAULT '0' COMMENT 'Flag for change next payment date',
  `accept_changing_shipping_address` ENUM('0', '1') DEFAULT '0' COMMENT 'Flag for change shipping address',
  `free_transaction_cycle` TINYINT unsigned  DEFAULT NULL COMMENT 'Free transaction cycle quantity',
  `transactions_quantity` SMALLINT unsigned DEFAULT NULL COMMENT 'Transaction total quantity',
  `custom_type` VARCHAR (50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Additional information for payment',
  PRIMARY KEY(`cart_id`),
  CONSTRAINT `shopping_recurring_payment_ibfk_2` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart_session` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_cart_session_has_recurring` (
  `recurring_cart_id` int(10) unsigned NOT NULL COMMENT 'recurrent payment id',
  `cart_id` int(10) unsigned NOT NULL COMMENT 'dependent cart id to recurring payment',
  PRIMARY KEY(`recurring_cart_id`, `cart_id`),
  CONSTRAINT `shopping_cart_session_has_recurring_ibfk_2` FOREIGN KEY (`recurring_cart_id`) REFERENCES `shopping_cart_session` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `shopping_cart_session_has_recurring_ibfk_3` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart_session` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `shopping_cart_session` ADD `free_cart` enum('0','1') COLLATE 'utf8_unicode_ci' NULL DEFAULT '0';
ALTER TABLE `shopping_product_has_freebies` ADD FOREIGN KEY(`freebies_id`) REFERENCES `shopping_product`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- 02/07/2015
-- version: 2.4.2
-- Add coupon sales history
CREATE TABLE IF NOT EXISTS `shopping_coupon_sales` (
  `coupon_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Coupon code',
  `cart_id` int(10) unsigned NOT NULL COMMENT 'Cart Id',
  PRIMARY KEY (`coupon_code`,`cart_id`),
  KEY `cart_id` (`cart_id`),
  CONSTRAINT `shopping_coupon_sales_ibfk_3` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart_session` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 06/08/2015
-- version: 2.4.3
-- Add product type id
INSERT INTO `page_types` (`page_type_id`, `page_type_name`) VALUES ('2', 'product');
UPDATE page SET `page_type` = 2 WHERE `id` IN (SELECT `page_id` from `shopping_product`);

-- 15/10/2015
-- version: 2.4.4
-- Add order refund information
ALTER TABLE `shopping_cart_session` ADD COLUMN `refund_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Partial or full refund amount';
ALTER TABLE `shopping_cart_session` ADD COLUMN `refund_notes` TEXT DEFAULT NULL COMMENT 'Refund info';

INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_refund'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_refund' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

-- 09/02/2015
-- version: 2.4.5

-- These alters are always the latest and updated version of the database
UPDATE `plugin` SET `version`='2.5.0' WHERE `name`='shopping';
SELECT version FROM `plugin` WHERE `name` = 'shopping';

