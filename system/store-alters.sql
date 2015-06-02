-- version: 2.1.0
-- version: 2.1.1
ALTER TABLE `shopping_cart_session` ADD COLUMN `discount_tax_rate` enum('0','1', '2', '3') COLLATE utf8_unicode_ci DEFAULT '0' AFTER `gateway`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `shipping_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Shipping Tax' AFTER `sub_total`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `discount_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Discount Tax' AFTER `shipping_tax`;
ALTER TABLE `shopping_cart_session` ADD COLUMN `sub_total_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Sub total Tax' AFTER `discount_tax`;
ALTER TABLE `shopping_quote` ADD COLUMN `creator_id` int(10) unsigned DEFAULT '0' AFTER `edited_by`;
-- version: 2.1.2
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
-- version: 2.2.1
ALTER TABLE `shopping_quote` ADD COLUMN `internal_note` text COLLATE utf8_unicode_ci AFTER `disclaimer`;
-- version: 2.2.2
CREATE TABLE IF NOT EXISTS `shopping_import_orders` (
  `real_order_id` int(10) unsigned NOT NULL,
  `import_order_id` VARCHAR(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`real_order_id`,`import_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- version: 2.2.3
ALTER TABLE `shopping_filtering_values` ADD UNIQUE (`attribute_id`, `product_id`);
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
UPDATE `template_type` SET `title` = 'Checkout' WHERE `id` = 'typecheckout';
UPDATE `template_type` SET `title` = 'Product' WHERE `id` = 'typeproduct';
ALTER TABLE `shopping_customer_address` ADD `mobilecountrycode` VARCHAR( 2 ) NULL DEFAULT NULL COMMENT 'Contains mobile phone country code';
INSERT INTO `plugin` (`name`, `status`, `tags`, `version`) VALUES ('delivery', 1, '', '2.2.0');

CREATE TABLE IF NOT EXISTS `plugin_paypal_settings` (
  `id`           INT(10)      NOT NULL,
  `email`        VARCHAR(255) NOT NULL,
  `apiSignature` VARCHAR(255) NOT NULL,
  `apiUser`      VARCHAR(255) NOT NULL,
  `apiPassword`  VARCHAR(255) NOT NULL,
  `useSandbox`   TINYINT(1)   NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  CHARACTER SET utf8
  COLLATE utf8_unicode_ci;

INSERT INTO `plugin_paypal_settings` VALUES ('1', '', '', '', '', '0')
ON DUPLICATE KEY UPDATE id = 1;

CREATE TABLE IF NOT EXISTS `plugin_paypal_transactions` (
  `id`                      INT          NOT NULL AUTO_INCREMENT,
  `txnId`                   VARCHAR(200) NULL,
  `payerId`                 VARCHAR(200) NULL,
  `payerMail`               VARCHAR(150) NULL,
  `amount`                  VARCHAR(25)  NULL,
  `shippingAmount`          VARCHAR(25)  NULL,
  `tax`                     VARCHAR(25)  NULL,
  `currency`                TEXT         NULL,
  `paymentStatus`           TEXT         NULL,
  `status`                  TEXT         NULL,
  `paymentType`             TEXT         NULL,
  `paymentId`               INT          NULL,
  `paymentDate`             TIMESTAMP    NULL     DEFAULT NULL,
  `pFirstName`              VARCHAR(50)  NULL,
  `pLastName`               VARCHAR(50)  NULL,
  `pCountry`                VARCHAR(60)  NULL,
  `pCountryCode`            TEXT         NULL,
  `pAddressState`           VARCHAR(60)  NULL,
  `pAddressCity`            VARCHAR(60)  NULL,
  `pAddressZip`             TEXT         NULL,
  `pAddressName`            VARCHAR(150) NULL,
  `cartId`                  INT(10)      NULL,
  `pendingReason`           VARCHAR(50)  NULL,
  `subscribeStatus`         VARCHAR(60)           DEFAULT NULL,
  `subscribePeriod`         INT(10)               DEFAULT NULL,
  `subscribePeriodType`     VARCHAR(50)           DEFAULT NULL,
  `subscribeQuantity`       INT(10)               DEFAULT NULL,
  `subscribeAmount`         DECIMAL(10, 4)        DEFAULT NULL,
  `subscribeDate`           VARCHAR(60)           DEFAULT NULL,
  `subscriptionId`          VARCHAR(255)          DEFAULT NULL,
  `subscriptionDatePayed`   VARCHAR(60)           DEFAULT NULL,
  `subscriptionAmountPayed` VARCHAR(60)           DEFAULT NULL,
  `emailSent`               ENUM('0', '1')        DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  CHARACTER SET utf8
  COLLATE utf8_unicode_ci;
INSERT INTO `plugin` (`name`, `status`, `tags`, `version`) VALUES ('paypal', 1, '', '2.2.3');

ALTER TABLE `shopping_product_has_freebies` ADD FOREIGN KEY(`freebies_id`) REFERENCES `shopping_product`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

UPDATE `plugin` SET `version`='2.4.2' WHERE `name`='shopping';
UPDATE `shopping_config` SET `value`='2.4.2' WHERE `name`='version';
