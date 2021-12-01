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

-- 15/10/2015
-- version: 2.5.0
-- Add Carrier tracking url

CREATE TABLE IF NOT EXISTS `shopping_shipping_url` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` VARCHAR (255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_status` ENUM('0', '1') DEFAULT '0',
  UNIQUE KEY `name` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 12/12/2016
-- version: 2.5.1
-- Add custom sort for product list
CREATE TABLE IF NOT EXISTS `shopping_draggable` (
  `id` CHAR(32) COLLATE 'utf8_unicode_ci' NOT NULL,
  `data` TEXT COLLATE 'utf8_unicode_ci' NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 28/07/2015
-- version: 2.5.2
-- Add support for digital products
ALTER TABLE `shopping_product` ADD COLUMN  `is_digital` ENUM('0','1') DEFAULT '0';

CREATE TABLE IF NOT EXISTS `shopping_product_digital_goods` (
   `id` INT(10) unsigned AUTO_INCREMENT,
   `file_stored_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Stored file name',
   `file_hash` CHAR(40) NOT NULL COMMENT 'Hash for download link',
   `original_file_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Original file name',
   `display_file_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Display file name',
   `product_id` INT(10) unsigned NOT NULL COMMENT 'Product id',
   `uploaded_at` TIMESTAMP NOT NULL COMMENT 'Upload date',
   `start_date` TIMESTAMP NOT NULL COMMENT 'Start sales date',
   `end_date` TIMESTAMP NOT NULL COMMENT 'End sales date',
   `download_limit` SMALLINT unsigned NOT NULL DEFAULT '0' COMMENT 'File download limit',
   `product_type` ENUM('downloadable','viewable') NOT NULL DEFAULT 'downloadable' COMMENT 'Digital product distribution type',
   `ip_address` VARCHAR(40) DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE(`file_hash`),
   CONSTRAINT `shopping_product_digital_goods_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `shopping_cart_session_content` ADD COLUMN `is_digital` ENUM('0','1') DEFAULT '0';

-- 16/08/2017
-- version: 2.5.3
-- Add product dimension fields
ALTER TABLE `shopping_product` ADD COLUMN `prod_length` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `shopping_product` ADD COLUMN `prod_depth` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `shopping_product` ADD COLUMN `prod_width` DECIMAL(10,2) NULL DEFAULT NULL;

INSERT IGNORE INTO `shopping_config` (`name`, `value`) VALUES
('lengthUnit', 'cm');

-- 06/06/2017
-- version: 2.5.4
-- Add mobile and desktop phone country code
ALTER TABLE `shopping_customer_address` ADD COLUMN `mobilecountrycode` CHAR(2) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `shopping_customer_address` ADD COLUMN `mobile_country_code_value` VARCHAR(16) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `shopping_customer_address` ADD COLUMN `phonecountrycode` CHAR(2) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `shopping_customer_address` ADD COLUMN `phone_country_code_value` VARCHAR(16) COLLATE utf8_unicode_ci DEFAULT NULL;

-- 03/07/2017
-- version: 2.5.5
UPDATE `plugin` SET `tags`='processphones' WHERE `name` = 'shopping';

-- 07/02/2018
-- version: 2.5.6
ALTER TABLE `shopping_cart_session` ADD COLUMN `purchased_on` timestamp NULL;
UPDATE `shopping_cart_session` SET `purchased_on` = `updated_at` WHERE `purchased_on` IS NULL AND  `updated_at` <> '0000-00-00 00:00:00' AND `status` IN('delivered', 'shipped', 'completed', 'refunded');

-- 20/03/2018
-- version: 2.5.7
ALTER TABLE `shopping_product` ADD COLUMN `gtin` BIGINT(10) UNSIGNED DEFAULT NULL;

-- 13/04/2018
-- version: 2.5.8
ALTER TABLE `shopping_product` MODIFY COLUMN `gtin` VARCHAR (255) COLLATE utf8_unicode_ci DEFAULT NULL;

-- 18/06/2017
-- version: 2.5.9
-- Add zone id for the coupon
ALTER TABLE `shopping_coupon` ADD COLUMN `zoneId` int(10) unsigned DEFAULT NULL;

-- 31/07/2018
-- version: 2.6.0
-- Add new prefix column
ALTER TABLE `shopping_customer_address` ADD COLUMN `prefix` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL AFTER `address_type`;

-- 25/04/2015
-- version: 2.6.1
-- Add Supplier
INSERT IGNORE INTO `email_triggers_recipient` (`recipient`)
SELECT CONCAT('supplier') FROM `email_triggers_recipient` WHERE
NOT EXISTS (SELECT `recipient` FROM `email_triggers_recipient`
WHERE `recipient` = 'supplier') LIMIT 1;

INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_suppliercompleted'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_suppliercompleted' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_suppliershipped'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_suppliershipped' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

CREATE TABLE IF NOT EXISTS `shopping_companies`(
  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`company_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_company_products` (
  `product_id` INT(10) unsigned NOT NULL,
  `company_id` INT(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`, `company_id`),
  FOREIGN KEY (`product_id`) REFERENCES `shopping_product`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY (`company_id`) REFERENCES `shopping_companies`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_company_suppliers` (
  `supplier_id` INT(10) unsigned NOT NULL,
  `company_id` INT(10) unsigned NOT NULL,
  PRIMARY KEY (`supplier_id`, `company_id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY (`company_id`) REFERENCES `shopping_companies`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 16/01/2019
-- version: 2.6.2
-- Add charity col
ALTER TABLE `shopping_cart_session` ADD COLUMN `additional_info` text COLLATE utf8_unicode_ci DEFAULT NULL AFTER `purchased_on`;

-- 02/01/2019
-- version: 2.6.3
-- Add zone id for the coupon
ALTER TABLE `shopping_coupon` ADD COLUMN `oneTimeUse` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT 'One time use coupon';

-- 08/01/2019
-- version: 2.6.4
-- Add product allowance
CREATE TABLE IF NOT EXISTS `shopping_allowance_products` (
  `product_id` INT(10) unsigned NOT NULL,
  `allowance_due` date DEFAULT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `observers_queue` (`observable`, `observer`) VALUES ('Models_Model_Product', 'Tools_AllowanceObserver');

-- 15/03/2019
-- version: 2.6.5
-- Add wishlist
CREATE TABLE IF NOT EXISTS `shopping_wishlist_wished_products` (
  `id` int(10) unsigned AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `product_id` INT(10) unsigned NOT NULL,
  `added_date` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY  (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `shopping_product` ADD COLUMN `wishlist_qty` int(10) unsigned DEFAULT '0';

-- 24/07/2019
-- version: 2.6.6
-- Fix group price observer
INSERT IGNORE INTO `observers_queue` (`observable`, `observer`)
SELECT CONCAT('Models_Model_Product'), CONCAT('Tools_GroupPriceObserver') FROM observers_queue WHERE
NOT EXISTS (SELECT `observable`, `observer` FROM `observers_queue`
WHERE `observable` = 'Models_Model_Product' AND `observer` = 'Tools_GroupPriceObserver')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

-- 24/11/2019
-- version: 2.6.7
-- Gift purchase
ALTER TABLE `shopping_cart_session` ADD `is_gift` enum('0','1') COLLATE 'utf8_unicode_ci' DEFAULT '0';
ALTER TABLE `shopping_cart_session` ADD `gift_email` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Gift purchase email';

INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_giftorder'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_giftorder' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

-- 24/11/2019
-- version: 2.6.8
ALTER TABLE `shopping_pickup_location` ADD `external_id` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `shopping_pickup_location` ADD `allowed_to_delete` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '0';

-- 02/12/2019
-- version: 2.6.9
CREATE TABLE IF NOT EXISTS `shopping_customer_rules_general_config` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
  `rule_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `creator_id` INT(10) UNSIGNED DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL,
  `editor_id` INT(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY(`id`),
  FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  FOREIGN KEY (`editor_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  UNIQUE (`rule_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_customer_rules_config` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
  `rule_id` INT(10) UNSIGNED NOT NULL,
  `field_name` VARCHAR (255) COLLATE utf8_unicode_ci NOT NULL,
  `rule_comparison_operator` ENUM('equal', 'notequal', 'like', 'in', 'greaterthan', 'lessthan') DEFAULT 'equal',
  `field_value` MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY(`id`),
  FOREIGN KEY (`rule_id`) REFERENCES `shopping_customer_rules_general_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_customer_rules_actions` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
  `rule_id` INT(10) UNSIGNED NOT NULL,
  `action_type` ENUM ('assign_group') DEFAULT 'assign_group',
  `action_config` TEXT COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE(`rule_id`, `action_type`),
  FOREIGN KEY (`rule_id`) REFERENCES `shopping_customer_rules_general_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 12/12/2019
-- version: 2.7.0
ALTER TABLE `shopping_cart_session` ADD COLUMN `shipping_service_id` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Shipping service external id' AFTER `shipping_tracking_id`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `shipping_availability_days` TEXT COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Availability dates. Json format' AFTER `shipping_service_id`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `shipping_service_info` TEXT COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Additional shipping service info. Json format' AFTER `shipping_availability_days`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `shipping_label_link` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Shipping label link url' AFTER `shipping_service_info`;

INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_delivered'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_delivered' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

ALTER TABLE `shopping_customer_address` ADD COLUMN `customer_notes` TEXT COLLATE utf8_unicode_ci DEFAULT NULL;

-- 06/01/2020
-- version: 2.7.1
ALTER TABLE `shopping_cart_session` ADD `order_subtype` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;

-- 14/02/2020
-- version: 2.7.2
INSERT IGNORE INTO `shopping_config` (`name`, `value`) VALUES
('pickupLocationLinks', 0),
('pickupLocationLinksLimit', 4);

-- 19/02/2020
-- version: 2.7.3
CREATE TABLE IF NOT EXISTS `shopping_notification_notified_products` (
  `id` int(10) unsigned AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `product_id` INT(10) unsigned NOT NULL,
  `added_date` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
   `send_notification` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY  (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `observers_queue` (`observable`, `observer`) VALUES ('Models_Model_Product', 'Tools_NotifyObserver');

INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_customernotification'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_customernotification' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

-- 27/02/2020
-- version: 2.7.4
ALTER TABLE `shopping_group` ADD `nonTaxable` enum('0','1') COLLATE 'utf8_unicode_ci' DEFAULT '0';

-- 23/03/2020
-- version: 2.7.5
CREATE TABLE IF NOT EXISTS `shopping_shipping_service_label` (
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Service Name',
  `label` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Service Custom Label',
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 17/03/2020
-- version: 2.7.6
CREATE TABLE IF NOT EXISTS `shopping_product_custom_fields_config` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
  `param_type` ENUM('text', 'select') DEFAULT 'text',
  `param_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE(`param_type`, `param_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_product_custom_params_data` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
  `param_id` INT(10) UNSIGNED NOT NULL,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `param_value` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `params_option_id` INT(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`param_id`) REFERENCES `shopping_product_custom_fields_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_product_custom_params_options_data` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `custom_param_id` INT UNSIGNED NOT NULL,
  `option_value` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`custom_param_id`) REFERENCES `shopping_product_custom_fields_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;

-- 19/12/2019
-- version: 2.7.7
ALTER TABLE `shopping_cart_session` ADD `shipping_tracking_code_id` int(10) unsigned DEFAULT NULL AFTER `shipping_tracking_id`;

-- 09/10/2020
-- version: 2.7.8
ALTER TABLE `shopping_draggable` ADD COLUMN `updated_at` TIMESTAMP NOT NULL;
ALTER TABLE `shopping_draggable` ADD COLUMN `user_id` int(10) unsigned NOT NULL;
ALTER TABLE `shopping_draggable` ADD COLUMN `ip_address` VARCHAR(45) NOT NULL;
ALTER TABLE `shopping_draggable` ADD COLUMN `page_id` int(10) unsigned DEFAULT NULL;

-- 01/09/2020
-- version: 2.7.9
INSERT IGNORE INTO `shopping_config` (`name`, `value`) VALUES
('usNumericFormat', '0');

-- 29/10/2020
-- version: 2.8.0
INSERT IGNORE INTO `shopping_config` (`name`, `value`) VALUES
('minimumOrder', '0');
ALTER TABLE `shopping_product` ADD COLUMN `minimum_order` int(3) unsigned DEFAULT '0';

-- 26/12/2018
-- version: 2.8.1
-- Add textarea option
ALTER TABLE `shopping_product_option`
CHANGE `type` `type` enum('dropdown','radio','text','date','file','textarea') COLLATE 'utf8_unicode_ci' NOT NULL AFTER `title`;

-- 18/08/2020
-- version: 2.8.2
ALTER TABLE `shopping_cart_session` ADD COLUMN `partial_percentage` DECIMAL(10,2) DEFAULT '0.00';
ALTER TABLE `shopping_cart_session` ADD COLUMN `is_partial` ENUM('0', '1') DEFAULT '0';
ALTER TABLE `shopping_cart_session` ADD COLUMN `partial_paid_amount` DECIMAL(10,2) DEFAULT '0.00';
ALTER TABLE `shopping_cart_session` ADD COLUMN `partial_purchased_on` timestamp NULL;
INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_partialpayment'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_partialpayment' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;
INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_partialpaymentnotif'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_partialpaymentnotif' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

CREATE TABLE IF NOT EXISTS `plugin_shopping_notification_partial_log` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
  `cart_id` INT(10) UNSIGNED NOT NULL,
  `notified_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 07/10/2020
-- version: 2.8.3
-- Add new prefix column
ALTER TABLE `shopping_customer_address` ADD COLUMN `position` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;

-- 15/12/2020
-- version: 2.8.4
-- Add partial payment action email notification
INSERT IGNORE INTO `email_triggers` (`id`, `enabled`, `trigger_name`, `observer`)
SELECT CONCAT(NULL), CONCAT('1'), CONCAT('store_partialpaymentsecond'), CONCAT('Tools_StoreMailWatchdog') FROM email_triggers WHERE
NOT EXISTS (SELECT `id`, `enabled`, `trigger_name`, `observer` FROM `email_triggers`
WHERE `enabled` = '1' AND `trigger_name` = 'store_partialpaymentsecond' AND `observer` = 'Tools_StoreMailWatchdog')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

-- 14/04/2021
-- version: 2.8.5
ALTER TABLE `shopping_cart_session` MODIFY COLUMN `partial_percentage` DECIMAL(10,6) DEFAULT '0.00';

-- 23/04/2021
-- version: 2.8.6
-- Add additionalpricefield option
ALTER TABLE `shopping_product_option`
    CHANGE `type` `type` enum('dropdown','radio','text','date','file','textarea', 'additionalpricefield') COLLATE 'utf8_unicode_ci' NOT NULL AFTER `title`;

-- 02/07/2021
-- version: 2.8.7
ALTER TABLE `shopping_product` ADD COLUMN `negative_stock` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '0';

-- 14/07/2021
-- version: 2.8.8
ALTER TABLE `shopping_cart_session` ADD COLUMN `partial_type` ENUM('amount', 'percentage') DEFAULT NULL AFTER `purchased_on`;
UPDATE `shopping_cart_session` SET `partial_type` = 'percentage' WHERE `shopping_cart_session`.`is_partial`='1';

-- 24/09/2021
-- version: 2.8.9
INSERT IGNORE INTO `shopping_config` (`name`, `value`) VALUES
('fiscalYearStart', '1');

-- 14/07/2021
-- version: 2.9.0
ALTER TABLE `shopping_cart_session` ADD COLUMN `purchase_error_message` TEXT COLLATE utf8_unicode_ci DEFAULT NULL AFTER `partial_purchased_on`;

-- These alters are always the latest and updated version of the database
UPDATE `plugin` SET `version`='2.9.1' WHERE `name`='shopping';
SELECT version FROM `plugin` WHERE `name` = 'shopping';

