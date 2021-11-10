DROP TABLE IF EXISTS `shopping_list_country`;
CREATE TABLE IF NOT EXISTS `shopping_list_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `shopping_list_country` (`id`, `country`) VALUES
(1, 'AD'), (2, 'AE'), (3, 'AF'), (4, 'AG'), (5, 'AI'), (6, 'AL'), (7, 'AM'), (8, 'AN'), (9, 'AO'), (10, 'AQ'),
(11, 'AR'), (12, 'AS'), (13, 'AT'), (14, 'AU'), (15, 'AW'), (16, 'AX'), (17, 'AZ'), (18, 'BA'), (19, 'BB'), (20, 'BD'),
(21, 'BE'), (22, 'BF'), (23, 'BG'), (24, 'BH'), (25, 'BI'), (26, 'BJ'), (27, 'BL'), (28, 'BM'), (29, 'BN'), (30, 'BO'),
(31, 'BQ'), (32, 'BR'), (33, 'BS'), (34, 'BT'), (35, 'BV'), (36, 'BW'), (37, 'BY'), (38, 'BZ'), (39, 'CA'), (40, 'CC'),
(41, 'CD'), (42, 'CF'), (43, 'CG'), (44, 'CH'), (45, 'CI'), (46, 'CK'), (47, 'CL'), (48, 'CM'), (49, 'CN'), (50, 'CO'),
(51, 'CR'), (52, 'CS'), (53, 'CT'), (54, 'CU'), (55, 'CV'), (56, 'CX'), (57, 'CY'), (58, 'CZ'), (59, 'DD'), (60, 'DE'),
(61, 'DJ'), (62, 'DK'), (63, 'DM'), (64, 'DO'), (65, 'DZ'), (66, 'EC'), (67, 'EE'), (68, 'EG'), (69, 'EH'), (70, 'ER'),
(71, 'ES'), (72, 'ET'), (73, 'FI'), (74, 'FJ'), (75, 'FK'), (76, 'FM'), (77, 'FO'), (78, 'FQ'), (79, 'FR'), (80, 'FX'),
(81, 'GA'), (82, 'GB'), (83, 'GD'), (84, 'GE'), (85, 'GF'), (86, 'GG'), (87, 'GH'), (88, 'GI'), (89, 'GL'), (90, 'GM'),
(91, 'GN'), (92, 'GP'), (93, 'GQ'), (94, 'GR'), (95, 'GS'), (96, 'GT'), (97, 'GU'), (98, 'GW'), (99, 'GY'), (100, 'HK'),
(101, 'HM'), (102, 'HN'), (103, 'HR'), (104, 'HT'), (105, 'HU'), (106, 'ID'), (107, 'IE'), (108, 'IL'), (109, 'IM'), (110, 'IN'),
(111, 'IO'), (112, 'IQ'), (113, 'IR'), (114, 'IS'), (115, 'IT'), (116, 'JE'), (117, 'JM'), (118, 'JO'), (119, 'JP'), (120, 'JT'),
(121, 'KE'), (122, 'KG'), (123, 'KH'), (124, 'KI'), (125, 'KM'), (126, 'KN'), (127, 'KP'), (128, 'KR'), (129, 'KW'), (130, 'KY'),
(131, 'KZ'), (132, 'LA'), (133, 'LB'), (134, 'LC'), (135, 'LI'), (136, 'LK'), (137, 'LR'), (138, 'LS'), (139, 'LT'), (140, 'LU'),
(141, 'LV'), (142, 'LY'), (143, 'MA'), (144, 'MC'), (145, 'MD'), (146, 'ME'), (147, 'MF'), (148, 'MG'), (149, 'MH'), (150, 'MI'),
(151, 'MK'), (152, 'ML'), (153, 'MM'), (154, 'MN'), (155, 'MO'), (156, 'MP'), (157, 'MQ'), (158, 'MR'), (159, 'MS'), (160, 'MT'),
(161, 'MU'), (162, 'MV'), (163, 'MW'), (164, 'MX'), (165, 'MY'), (166, 'MZ'), (167, 'NA'), (168, 'NC'), (169, 'NE'), (170, 'NF'),
(171, 'NG'), (172, 'NI'), (173, 'NL'), (174, 'NO'), (175, 'NP'), (176, 'NQ'), (177, 'NR'), (178, 'NT'), (179, 'NU'), (180, 'NZ'),
(181, 'OM'), (182, 'PA'), (183, 'PC'), (184, 'PE'), (185, 'PF'), (186, 'PG'), (187, 'PH'), (188, 'PK'), (189, 'PL'), (190, 'PM'),
(191, 'PN'), (192, 'PR'), (193, 'PS'), (194, 'PT'), (195, 'PU'), (196, 'PW'), (197, 'PY'), (198, 'PZ'), (199, 'QA'), (200, 'RE'),
(201, 'RO'), (202, 'RS'), (203, 'RU'), (204, 'RW'), (205, 'SA'), (206, 'SB'), (207, 'SC'), (208, 'SD'), (209, 'SE'), (210, 'SG'),
(211, 'SH'), (212, 'SI'), (213, 'SJ'), (214, 'SK'), (215, 'SL'), (216, 'SM'), (217, 'SN'), (218, 'SO'), (219, 'SR'), (220, 'ST'),
(221, 'SV'), (222, 'SY'), (223, 'SZ'), (224, 'TC'), (225, 'TD'), (226, 'TF'), (227, 'TG'), (228, 'TH'), (229, 'TJ'), (230, 'TK'),
(231, 'TL'), (232, 'TM'), (233, 'TN'), (234, 'TO'), (235, 'TR'), (236, 'TT'), (237, 'TV'), (238, 'TW'), (239, 'TZ'), (240, 'UA'),
(241, 'UG'), (242, 'UM'), (243, 'US'), (244, 'UY'), (245, 'UZ'), (246, 'VA'), (247, 'VC'), (248, 'VD'), (249, 'VE'), (250, 'VG'),
(251, 'VI'), (252, 'VN'), (253, 'VU'), (254, 'WF'), (255, 'WK'), (256, 'WS'), (257, 'YD'), (258, 'YE'), (259, 'YT'), (260, 'ZA'),
(261, 'ZM'), (262, 'ZW');

DROP TABLE IF EXISTS `shopping_list_state`;
CREATE TABLE IF NOT EXISTS `shopping_list_state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `shopping_list_state` (`id`, `country`, `state`, `name`) VALUES
(1, 'US', 'AL', 'Alabama'),
(2, 'US', 'AK', 'Alaska'),
(3, 'US', 'AZ', 'Arizona'),
(4, 'US', 'AR', 'Arkansas'),
(5, 'US', 'CA', 'California'),
(6, 'US', 'CO', 'Colorado'),
(7, 'US', 'CT', 'Connecticut'),
(8, 'US', 'DE', 'Delaware'),
(9, 'US', 'DC', 'District Of Columbia'),
(10, 'US', 'FL', 'Florida'),
(11, 'US', 'GA', 'Georgia'),
(12, 'US', 'HI', 'Hawaii'),
(13, 'US', 'ID', 'Idaho'),
(14, 'US', 'IL', 'Illinois'),
(15, 'US', 'IN', 'Indiana'),
(16, 'US', 'IA', 'Iowa'),
(17, 'US', 'KS', 'Kansas'),
(18, 'US', 'KY', 'Kentucky'),
(19, 'US', 'LA', 'Louisiana'),
(20, 'US', 'ME', 'Maine'),
(21, 'US', 'MD', 'Maryland'),
(22, 'US', 'MA', 'Massachusetts'),
(23, 'US', 'MI', 'Michigan'),
(24, 'US', 'MN', 'Minnesota'),
(25, 'US', 'MS', 'Mississippi'),
(26, 'US', 'MO', 'Missouri'),
(27, 'US', 'MT', 'Montana'),
(28, 'US', 'NE', 'Nebraska'),
(29, 'US', 'NV', 'Nevada'),
(30, 'US', 'NH', 'New Hampshire'),
(31, 'US', 'NJ', 'New Jersey'),
(32, 'US', 'NM', 'New Mexico'),
(33, 'US', 'NY', 'New York'),
(34, 'US', 'NC', 'North Carolina'),
(35, 'US', 'ND', 'North Dakota'),
(36, 'US', 'OH', 'Ohio'),
(37, 'US', 'OK', 'Oklahoma'),
(38, 'US', 'OR', 'Oregon'),
(39, 'US', 'PA', 'Pennsylvania'),
(40, 'US', 'RI', 'Rhode Island'),
(41, 'US', 'SC', 'South Carolina'),
(42, 'US', 'SD', 'South Dakota'),
(43, 'US', 'TN', 'Tennessee'),
(44, 'US', 'TX', 'Texas'),
(45, 'US', 'UT', 'Utah'),
(46, 'US', 'VT', 'Vermont'),
(47, 'US', 'VA', 'Virginia'),
(48, 'US', 'WA', 'Washington'),
(49, 'US', 'WV', 'West Virginia'),
(50, 'US', 'WI', 'Wisconsin'),
(51, 'US', 'WY', 'Wyoming'),
(52, 'CA', 'AB', 'Alberta'),
(53, 'CA', 'BC', 'British Columbia'),
(54, 'CA', 'MB', 'Manitoba'),
(55, 'CA', 'NB', 'New Brunswick'),
(56, 'CA', 'NF', 'Newfoundland and Labrador'),
(57, 'CA', 'NT', 'Northwest Territories'),
(58, 'CA', 'NS', 'Nova Scotia'),
(59, 'CA', 'NU', 'Nunavut'),
(60, 'CA', 'ON', 'Ontario'),
(61, 'CA', 'PE', 'Prince Edward Island'),
(62, 'CA', 'QC', 'Quebec'),
(63, 'CA', 'SK', 'Saskatchewan'),
(64, 'CA', 'YT', 'Yukon Territory'),
(65, 'AU', 'ACT', 'Australian Capital Territory'),
(66, 'AU', 'NSW', 'New South Wales'),
(67, 'AU', 'NT', 'Northern Territory'),
(68, 'AU', 'QLD', 'Queensland'),
(69, 'AU', 'SA', 'South Australia'),
(70, 'AU', 'TAS', 'Tasmania'),
(71, 'AU', 'VIC', 'Victoria'),
(72, 'AU', 'WA', 'Western Australia');

DROP TABLE IF EXISTS `shopping_brands`;
CREATE TABLE IF NOT EXISTS `shopping_brands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_tags`;
CREATE TABLE IF NOT EXISTS `shopping_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_config`;
CREATE TABLE IF NOT EXISTS `shopping_config` (
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `shopping_config` (`name`, `value`) VALUES
('address1', '827 Shrader St.'),
('address2', 'Suite 400'),
('cartPlugin', 'cart'),
('city', 'San Francisco'),
('company', 'Demo Store'),
('country', 'US'),
('currency', 'USD'),
('email', 'demostore@example.com'),
('forceSSLCheckout', '0'),
('phone', '415 899 3455'),
('showPriceIncTax', '1'),
('state', '5'),
('weightUnit', 'kg'),
('lengthUnit', 'cm'),
('zip', '94117'),
('noZeroPrice', '1'),
('timezone', 'America/New_York'),
('pickupLocationLinks', 0),
('pickupLocationLinksLimit', 4),
('minimumOrder', '0'),
('fiscalYearStart', '1'),
('version', '2.9.0');

DROP TABLE IF EXISTS `shopping_product`;
CREATE TABLE IF NOT EXISTS `shopping_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `page_id` int(10) unsigned DEFAULT NULL,
  `enabled` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `sku` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `mpn` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` decimal(8,3) DEFAULT NULL,
  `brand_id` int(10) unsigned DEFAULT NULL,
  `photo` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_description` mediumtext COLLATE utf8_unicode_ci,
  `full_description` text COLLATE utf8_unicode_ci,
  `price` decimal(10,4) DEFAULT NULL,
  `tax_class` enum('0','1','2','3') COLLATE utf8_unicode_ci DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `base_price` DECIMAL(10,2) NULL DEFAULT NULL,
  `inventory` VARCHAR(50) NULL DEFAULT NULL COLLATE utf8_unicode_ci,
  `free_shipping` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '0',
  `is_digital` ENUM('0','1') DEFAULT '0',
  `prod_length` DECIMAL(10,2) NULL DEFAULT NULL,
  `prod_depth` DECIMAL(10,2) NULL DEFAULT NULL,
  `prod_width` DECIMAL(10,2) NULL DEFAULT NULL,
  `gtin` VARCHAR (255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wishlist_qty` int(10) unsigned DEFAULT '0',
  `minimum_order` int(3) unsigned DEFAULT '0',
  `negative_stock` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `page_id` (`page_id`),
  KEY `brand_id` (`brand_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_product_has_tag`;
CREATE TABLE IF NOT EXISTS `shopping_product_has_tag` (
  `product_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_product_has_option`;
CREATE TABLE IF NOT EXISTS `shopping_product_has_option` (
  `product_id` int(10) unsigned NOT NULL,
  `option_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`option_id`),
  KEY `fk_shopping_product_has_shopping_product_option_shopping_prod2` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_product_has_related`;
CREATE TABLE IF NOT EXISTS `shopping_product_has_related` (
  `product_id` int(10) unsigned NOT NULL,
  `related_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`related_id`),
  KEY `fk_shopping_product1` (`related_id`),
  KEY `fk_shopping_product2` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_product_option`;
CREATE TABLE IF NOT EXISTS `shopping_product_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('dropdown','radio','text','date','file','textarea', 'additionalpricefield') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `indTitle` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_product_option_selection`;
CREATE TABLE IF NOT EXISTS `shopping_product_option_selection` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `priceSign` enum('+','-') COLLATE utf8_unicode_ci DEFAULT NULL,
  `priceValue` decimal(10,4) DEFAULT NULL,
  `priceType` enum('percent','unit') COLLATE utf8_unicode_ci DEFAULT NULL,
  `weightSign` enum('+','-') COLLATE utf8_unicode_ci DEFAULT NULL,
  `weightValue` decimal(8,3) DEFAULT NULL,
  `isDefault` enum('1','0') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indTitle` (`title`),
  KEY `fk_shopping_product_option_selection_shopping_product_option1` (`option_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_tax`;
CREATE TABLE IF NOT EXISTS `shopping_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zoneId` int(10) unsigned NOT NULL,
  `rate1` decimal(10,3) NOT NULL DEFAULT '0.00',
  `rate2` decimal(10,3) NOT NULL DEFAULT '0.00',
  `rate3` decimal(10,3) NOT NULL DEFAULT '0.00',
  `isDefault` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `zoneId` (`zoneId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_zone`;
CREATE TABLE IF NOT EXISTS `shopping_zone` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_zone_country`;
CREATE TABLE IF NOT EXISTS `shopping_zone_country` (
  `zone_id` int(11) unsigned NOT NULL,
  `country_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`zone_id`,`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_zone_state`;
CREATE TABLE IF NOT EXISTS `shopping_zone_state` (
  `zone_id` int(10) unsigned NOT NULL,
  `state_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`zone_id`,`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_zone_zip`;
CREATE TABLE IF NOT EXISTS `shopping_zone_zip` (
  `zone_id` int(11) NOT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`zone_id`,`zip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_cart_session`;
CREATE TABLE IF NOT EXISTS `shopping_cart_session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `referer` tinytext COLLATE utf8_unicode_ci COMMENT 'Referer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(10) unsigned DEFAULT NULL,
  `shipping_address_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_address_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_price` decimal(10,2) DEFAULT NULL,
  `shipping_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_service` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_tracking_id` tinytext COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Shipping Tracking ID',
  `shipping_tracking_code_id` int(10) unsigned DEFAULT NULL,
  `shipping_service_id` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Shipping service external id',
  `shipping_availability_days` TEXT COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Availability dates. Json format',
  `shipping_service_info` TEXT COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Additional shipping service info. Json format',
  `shipping_label_link` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Shipping label link url',
  `status` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gateway` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount_tax_rate` enum('0','1','2','3') COLLATE utf8_unicode_ci DEFAULT '0',
  `sub_total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Sub Total',
  `shipping_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Shipping Tax',
  `discount_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Discount Tax',
  `sub_total_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Sub total Tax',
  `total_tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Total Tax',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Sub Total + Total Tax + Shipping',
  `notes` text COLLATE utf8_unicode_ci COMMENT 'Comment for order',
  `discount` decimal(10,2) DEFAULT NULL COMMENT 'Order discount',
  `free_cart` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '0',
  `refund_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'Partial or full refund amount',
  `refund_notes` TEXT DEFAULT NULL COMMENT 'Refund info',
  `purchased_on` timestamp NULL,
  `partial_type` ENUM('amount', 'percentage') DEFAULT NULL,
  `partial_percentage` DECIMAL(10,6) DEFAULT '0.00',
  `is_partial` ENUM('0', '1') DEFAULT '0',
  `partial_paid_amount` DECIMAL(10,2) DEFAULT '0.00',
  `partial_purchased_on` timestamp NULL,
  `additional_info` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_gift` enum('0','1') COLLATE 'utf8_unicode_ci' DEFAULT '0',
  `gift_email` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Gift purchase email',
  `order_subtype` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `shipping_address_id` (`shipping_address_id`),
  KEY `billing_address_id` (`billing_address_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_cart_session_content`;
CREATE TABLE IF NOT EXISTS `shopping_cart_session_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` int(10) unsigned DEFAULT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `options` text,
  `price` decimal(10,4) DEFAULT NULL COMMENT 'Price w/o Tax',
  `qty` int(10) unsigned DEFAULT NULL,
  `tax` decimal(10,4) DEFAULT NULL COMMENT 'Tax Price',
  `tax_price` decimal(10,4) DEFAULT NULL COMMENT 'Price + Tax',
  `freebies` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '0',
  `is_digital` ENUM('0','1') DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`,`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_customer_address`;
CREATE TABLE IF NOT EXISTS `shopping_customer_address` (
  `id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `address_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prefix` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobilecountrycode` CHAR(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile_country_code_value` VARCHAR(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phonecountrycode` CHAR(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_country_code_value` VARCHAR(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_notes` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `state` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_customer_info`;
CREATE TABLE IF NOT EXISTS `shopping_customer_info` (
  `user_id` int(10) unsigned NOT NULL,
  `default_shipping_address_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_billing_address_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_shipping_config`;
CREATE TABLE IF NOT EXISTS `shopping_shipping_config` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Shipping plugin name',
  `enabled` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `config` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`name`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `shopping_shipping_config` (`name`, `enabled`, `config`) VALUES
('freeshipping', '1', NULL),
('pickup', '1', NULL);

ALTER TABLE `shopping_product`
  ADD CONSTRAINT `shopping_product_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `shopping_product_has_tag`
  ADD CONSTRAINT `shopping_product_has_tag_ibfk_3` FOREIGN KEY (`tag_id`) REFERENCES `shopping_tags` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `shopping_product_has_tag_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `shopping_product_has_option`
  ADD CONSTRAINT `shopping_product_has_option_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `shopping_product_option` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `shopping_product_has_option_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `shopping_product_has_related`
  ADD CONSTRAINT `shopping_product_has_related_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `shopping_product_option_selection`
  ADD CONSTRAINT `fk_shopping_product_option_selection_shopping_product_option1` FOREIGN KEY (`option_id`) REFERENCES `shopping_product_option` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `shopping_cart_session`
  ADD CONSTRAINT `shopping_cart_session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `shopping_cart_session_content`
  ADD CONSTRAINT `shopping_cart_session_content_ibfk_2` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart_session` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `shopping_customer_address`
  ADD CONSTRAINT `shopping_customer_address_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `shopping_customer_info`
  ADD CONSTRAINT `shopping_customer_info_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `shopping_tax`
  ADD CONSTRAINT `shopping_tax_ibfk_1` FOREIGN KEY (`zoneId`) REFERENCES `shopping_zone` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

INSERT INTO `page_option` (`id`, `title`, `context`, `active`, `option_usage`) VALUES
('option_checkout', 'The cart checkout page', 'Cart and checkout', 1, 'once'),
('option_storethankyou', 'Post purchase "Thank you" page', 'Cart and checkout', 1, 'once'),
('option_storeclientlogin', 'Store client landing page', 'Cart and checkout', 1, 'once'),
('option_storeshippingterms', 'Shipping terms and conditions', 'Cart and checkout', 1, 'once');

INSERT INTO `shopping_zone` (`id`, `name`) VALUES
(1, 'US'),
(2, 'CA'),
(3, 'EU');

INSERT INTO `shopping_zone_country` (`zone_id`, `country_id`) VALUES
(1, 243),
(2, 39),
(3, 13),
(3, 21),
(3, 23),
(3, 57),
(3, 58),
(3, 60),
(3, 62),
(3, 67),
(3, 71),
(3, 73),
(3, 79),
(3, 82),
(3, 94),
(3, 105),
(3, 107),
(3, 115),
(3, 139),
(3, 140),
(3, 141),
(3, 160),
(3, 173),
(3, 189),
(3, 194),
(3, 201),
(3, 209),
(3, 212),
(3, 214);

INSERT INTO `shopping_zone_state` (`zone_id`, `state_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50),
(1, 51),
(2, 52),
(2, 53),
(2, 54),
(2, 55),
(2, 56),
(2, 57),
(2, 58),
(2, 59),
(2, 60),
(2, 61),
(2, 62),
(2, 63),
(2, 64);

INSERT INTO `email_triggers_recipient` (`recipient`) VALUES
('customer'),
('sales person'),
('supplier');

DROP TABLE IF EXISTS `shopping_coupon`;
CREATE TABLE IF NOT EXISTS `shopping_coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Coupon ID',
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Coupon code',
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'discount' COMMENT 'Coupon discount type',
  `scope` enum('order','client') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Coupon usage scope',
  `startDate` date DEFAULT NULL COMMENT 'Coupon start date',
  `endDate` date DEFAULT NULL COMMENT 'Coupon expire date',
  `allowCombination` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Allow combination with other coupons',
  `zoneId` int(10) unsigned DEFAULT NULL,
  `oneTimeUse` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT 'One time use coupon',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_coupon_discount`;
CREATE TABLE IF NOT EXISTS `shopping_coupon_discount` (
  `coupon_id` int(10) unsigned NOT NULL COMMENT 'Coupon ID',
  `minOrderAmount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Allow combination with other coupons',
  `discountAmount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Coupon discount amount',
  `discountUnits` enum('unit','percent') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unit' COMMENT 'Coupon discount units',
  PRIMARY KEY (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_coupon_freeshipping`;
CREATE TABLE IF NOT EXISTS `shopping_coupon_freeshipping` (
  `coupon_id` int(10) unsigned NOT NULL COMMENT 'Coupon ID',
  `minOrderAmount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Minimal order amount',
  PRIMARY KEY (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_coupon_product`;
CREATE TABLE IF NOT EXISTS `shopping_coupon_product` (
  `coupon_id` int(10) unsigned NOT NULL COMMENT 'Coupon ID',
  `product_id` int(10) unsigned NOT NULL COMMENT 'Product ID',
  PRIMARY KEY (`coupon_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_coupon_type`;
CREATE TABLE IF NOT EXISTS `shopping_coupon_type` (
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `label` tinytext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `shopping_coupon_type` (`type`, `label`) VALUES
('discount',	'Discount with min. order'),
('freeshipping',	'Free shipping with min. order');

DROP TABLE IF EXISTS `shopping_coupon_usage`;
CREATE TABLE IF NOT EXISTS `shopping_coupon_usage` (
  `coupon_id` int(10) unsigned NOT NULL COMMENT 'Coupon ID',
  `cart_id` int(10) unsigned NOT NULL COMMENT 'Customer ID',
  PRIMARY KEY (`coupon_id`,`cart_id`),
  KEY `cart_id` (`cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `shopping_coupon`
  ADD CONSTRAINT `shopping_coupon_ibfk_1` FOREIGN KEY (`type`) REFERENCES `shopping_coupon_type` (`type`) ON UPDATE CASCADE;

ALTER TABLE `shopping_coupon_discount`
  ADD CONSTRAINT `shopping_coupon_discount_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `shopping_coupon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `shopping_coupon_freeshipping`
  ADD CONSTRAINT `shopping_coupon_freeshipping_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `shopping_coupon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `shopping_coupon_product`
  ADD CONSTRAINT `shopping_coupon_product_ibfk_3` FOREIGN KEY (`coupon_id`) REFERENCES `shopping_coupon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shopping_coupon_product_ibfk_4` FOREIGN KEY (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `shopping_coupon_usage`
  ADD CONSTRAINT `shopping_coupon_usage_ibfk_4` FOREIGN KEY (`coupon_id`) REFERENCES `shopping_coupon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shopping_coupon_usage_ibfk_5` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

DROP TABLE IF EXISTS `shopping_product_has_part`;
CREATE TABLE IF NOT EXISTS `shopping_product_has_part` (
  `product_id` int(10) unsigned NOT NULL,
  `part_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`part_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `shopping_product_has_part` ADD CONSTRAINT `shopping_product_has_part_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE;

DROP TABLE IF EXISTS `shopping_product_set_settings`;
CREATE TABLE IF NOT EXISTS  `shopping_product_set_settings` (
  `productId` int(10) unsigned NOT NULL,
  `autoCalculatePrice` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_group`;
CREATE TABLE IF NOT EXISTS `shopping_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `priceSign` enum('plus','minus') COLLATE utf8_unicode_ci DEFAULT NULL,
  `priceType` enum('percent','unit') COLLATE utf8_unicode_ci DEFAULT NULL,
  `priceValue` decimal(10,2) DEFAULT NULL,
  `nonTaxable` enum('0','1') COLLATE 'utf8_unicode_ci' DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shopping_group_price`;
CREATE TABLE IF NOT EXISTS `shopping_group_price` (
  `groupId` int(10) unsigned NOT NULL,
  `productId` int(10) unsigned NOT NULL,
  `priceValue` decimal(10,2) DEFAULT NULL,
  `priceSign` enum('plus','minus') COLLATE utf8_unicode_ci DEFAULT NULL,
  `priceType` enum('percent','unit') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`groupId`,`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `template_type` (`id`, `title`) VALUES
('typecheckout', 'Checkout'),
('typeproduct', 'Product page'),
('typelisting', 'Product listing');

CREATE TABLE IF NOT EXISTS `shopping_product_freebies_settings` (
  `prod_id` int(10) unsigned NOT NULL,
  `price_value` decimal(10,4) DEFAULT 0,
  `quantity` int(4) unsigned DEFAULT 0,
  PRIMARY KEY (`prod_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `shopping_product_has_freebies` (
  `product_id` int(10) unsigned NOT NULL,
  `freebies_id` int(10) unsigned NOT NULL,
  `freebies_quantity` int(4) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`freebies_id`),
  FOREIGN KEY(`freebies_id`) REFERENCES `shopping_product`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  UNIQUE KEY `attribute_id_2` (`attribute_id`,`product_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_import_orders` (
  `real_order_id` int(10) unsigned NOT NULL,
  `import_order_id` VARCHAR(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`real_order_id`,`import_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `external_id` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `allowed_to_delete` enum('0','1') COLLATE utf8_unicode_ci DEFAULT '0',
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

CREATE TABLE IF NOT EXISTS `shopping_coupon_sales` (
  `coupon_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Coupon code',
  `cart_id` int(10) unsigned NOT NULL COMMENT 'Cart Id',
  PRIMARY KEY (`coupon_code`,`cart_id`),
  KEY `cart_id` (`cart_id`),
  CONSTRAINT `shopping_coupon_sales_ibfk_3` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart_session` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_shipping_url` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` VARCHAR (255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_status` ENUM('0', '1') DEFAULT '0',
  UNIQUE KEY `name` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `page_types` (`page_type_id`, `page_type_name`) VALUES ('2', 'product');

INSERT INTO `observers_queue` (`observable`, `observer`) VALUES ('Models_Model_Product', 'Tools_GroupPriceObserver');

CREATE TABLE IF NOT EXISTS `shopping_quantity_discount` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `discount_quantity` int(10) unsigned NOT NULL,
  `discount_price_sign` enum('plus','minus') COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount_price_type` enum('percent','unit') COLLATE utf8_unicode_ci DEFAULT NULL,
  `apply_scope` enum('local', 'global') DEFAULT 'local',
  `discount_amount` DECIMAL(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `shopping_quantity_discount_product` (
  `product_id` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `price_sign` enum('plus','minus') COLLATE utf8_unicode_ci DEFAULT NULL,
  `price_type` enum('percent','unit') COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('enabled','disabled') COLLATE utf8_unicode_ci DEFAULT 'enabled',
  `amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`product_id`,`quantity`),
  CONSTRAINT `shopping_quantity_discount_product_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_cart_session_discount` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `discount_type` VARCHAR(255) NOT NULL,
  `price_sign` enum('plus','minus') COLLATE utf8_unicode_ci DEFAULT NULL,
  `price_type` enum('percent','unit') COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `unit_save` decimal(10,2) DEFAULT NULL,
  `order_discount` TINYINT unsigned DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE (`cart_id`, `product_id`, `discount_type`),
  CONSTRAINT `shopping_cart_session_discount_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart_session` (`id`) ON DELETE CASCADE

CREATE TABLE IF NOT EXISTS `shopping_companies`(
  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`company_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_company_suppliers` (
  `supplier_id` INT(10) unsigned NOT NULL,
  `company_id` INT(10) unsigned NOT NULL,
  PRIMARY KEY (`supplier_id`, `company_id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY (`company_id`) REFERENCES `shopping_companies`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_company_products` (
  `product_id` INT(10) unsigned NOT NULL,
  `company_id` INT(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`, `company_id`),
  FOREIGN KEY (`product_id`) REFERENCES `shopping_product`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY (`company_id`) REFERENCES `shopping_companies`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_draggable` (
  `id` CHAR(32) COLLATE 'utf8_unicode_ci' NOT NULL,
  `data` TEXT COLLATE 'utf8_unicode_ci' NOT NULL,
  `updated_at` TIMESTAMP NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `page_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

CREATE TABLE IF NOT EXISTS `shopping_allowance_products` (
  `product_id` INT(10) unsigned NOT NULL,
  `allowance_due` date DEFAULT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `observers_queue` (`observable`, `observer`) VALUES ('Models_Model_Product', 'Tools_AllowanceObserver');

CREATE TABLE IF NOT EXISTS `shopping_wishlist_wished_products` (
  `id` int(10) unsigned AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `product_id` INT(10) unsigned NOT NULL,
  `added_date` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY  (`product_id`) REFERENCES `shopping_product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `observers_queue` (`observable`, `observer`)
SELECT CONCAT('Models_Model_Product'), CONCAT('Tools_GroupPriceObserver') FROM observers_queue WHERE
NOT EXISTS (SELECT `observable`, `observer` FROM `observers_queue`
WHERE `observable` = 'Models_Model_Product' AND `observer` = 'Tools_GroupPriceObserver')
AND EXISTS (SELECT name FROM `plugin` where `name` = 'shopping') LIMIT 1;

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

CREATE TABLE IF NOT EXISTS `shopping_shipping_service_label` (
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Service Name',
  `label` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Service Custom Label',
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

INSERT IGNORE INTO `email_triggers` (`enabled`, `trigger_name`, `observer`) VALUES
('1', 'store_neworder', 'Tools_StoreMailWatchdog'),
('1', 'store_newcustomer', 'Tools_StoreMailWatchdog'),
('1', 'store_trackingnumber', 'Tools_StoreMailWatchdog'),
('1', 'store_newuseraccount', 'Tools_StoreMailWatchdog'),
('1', 'store_refund', 'Tools_StoreMailWatchdog'),
('1', 'store_delivered', 'Tools_StoreMailWatchdog'),
('1', 'store_suppliercompleted', 'Tools_StoreMailWatchdog'),
('1', 'store_suppliershipped', 'Tools_StoreMailWatchdog'),
('1', 'store_giftorder', 'Tools_StoreMailWatchdog'),
('1', 'store_customernotification', 'Tools_StoreMailWatchdog'),
('1', 'store_partialpayment', 'Tools_StoreMailWatchdog'),
('1', 'store_partialpaymentsecond', 'Tools_StoreMailWatchdog');

CREATE TABLE IF NOT EXISTS `plugin_shopping_notification_partial_log` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT NOT NULL,
  `cart_id` INT(10) UNSIGNED NOT NULL,
  `notified_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_newcustomer'),	NULL,	CONCAT('sales person'),	CONCAT('Hi there {customer:fullname}! <br> <br>Thank you for your registration.<br>You are welcome to login to your Client Area. <br><br>Login: {customer:email}<br>Follow this <strong>{customer:passwordLink}</strong> in order to set your password.<br><br>'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('New Customer Registered') FROM email_triggers WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions`
WHERE `service` = 'email' AND `recipient` = 'sales person' AND `trigger` = 'store_newcustomer') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_trackingnumber'),	NULL,	CONCAT('sales person'),	CONCAT('Hello! <br> <br>Your order #{order:id} status shipping tracking code: {order:shippingtrackingid}'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Track Your Order') FROM email_triggers
WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions`
WHERE `service` = 'email' AND `recipient` = 'sales person' AND `trigger` = 'store_trackingnumber') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_newuseraccount'),	NULL,	CONCAT('sales person'),	CONCAT('Hello!  <br> <br> User information has been updated'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('New User Account Information') FROM email_triggers
WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions`
WHERE `service` = 'email' AND `recipient` = 'sales person' AND `trigger` = 'store_newuseraccount') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_refund'),	NULL,	CONCAT('sales person'),	CONCAT('Order with amount: {refund:message} has been refunded. <br>  Admin left a comment: {refund:notes}'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Order Refunded') FROM email_triggers
WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions`
WHERE `service` = 'email' AND `recipient` = 'sales person' AND `trigger` = 'store_refund') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_delivered'),	NULL,	CONCAT('sales person'),	CONCAT('Hello! <br><br> Your order #{order:id} status shipping tracking code: {order:shippingtrackingid} is delivered.'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Order Delivered') FROM email_triggers
WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions`
WHERE `service` = 'email' AND `recipient` = 'sales person' AND `trigger` = 'store_delivered') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_suppliercompleted'),	NULL,	CONCAT('sales person'),	CONCAT('Hello! <br><br> Suppliers order {product:urls} is completed.'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Supplier Order Completed') FROM email_triggers
WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions`
WHERE `service` = 'email' AND `recipient` = 'sales person' AND `trigger` = 'store_suppliercompleted') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_suppliershipped'),	NULL,	CONCAT('sales person'),	CONCAT('Hello! <br><br> Suppliers order {product:urls} is delivered.'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Supplier Order Delivered') FROM email_triggers
WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions`
WHERE `service` = 'email' AND `recipient` = 'sales person' AND `trigger` = 'store_suppliershipped') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_giftorder'),	NULL,	CONCAT('admin'),	CONCAT('Hi there, we have a new order from {customer:fullname}! '),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Gift Order Info') FROM email_triggers
WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions`
WHERE `service` = 'email' AND `recipient` = 'admin' AND `trigger` = 'store_giftorder') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_giftorder'),	NULL,	CONCAT('customer'),	CONCAT('<p>Dear {$postpurchase:shipping:firstname},</p>
<p>{customer:fullname} is sending you the gift  from {store:name}. Look for a shipping notification from {order:shippingservice} to the email address listed here: {order:shippingaddress}. Please contact the customer cervice with any issues or questions.</p><br><p>A personal note is include:<br>"{$postpurchase:notes}"<p><br>'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Gift Order Info') FROM email_triggers WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions` WHERE `service` = 'email' AND `recipient` = 'customer' AND `trigger` = 'store_giftorder') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'),	CONCAT('store_partialpayment'),	NULL,	CONCAT('customer'),	CONCAT("Hello {customer:fullname}!<br/><br/>Welcome to the family. Thanks for your trust, we will now get to work to earn it. We\'ll be in touch soon to kick start your project. For the record, you paid the following towards your project:<br><br>{$postpurchase:partialpercentage}%  ($ {$postpurchase:partialamount}) out of {order:total}<br/>Feel free to contact us should you have any questions or concerns."),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Thank you for your order - We have received your deposit payment') FROM email_triggers WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions` WHERE `service` = 'email' AND `recipient` = 'customer' AND `trigger` = 'store_partialpayment') LIMIT 1;

INSERT IGNORE INTO `email_triggers_actions` (`service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject`)
SELECT CONCAT('email'), CONCAT('store_partialpaymentnotif'),	NULL,	CONCAT('customer'),	CONCAT('Hello {customer:fullname}!<br/><br/>Great news. We have completed another important step in this process, and you have reached the next milestone towards success. Please follow this link and use your credit card <a href=\"{$website:url}{quote:id}.html\"> to securely complete your order</a><br/><br/>Thank you for your business. We appreciate it very much.<br/><br/>Feel free to contact us should you have any questions or concerns.'),	CONCAT('no-reply@{$website:domain}'),	CONCAT('Payment completion stage') FROM email_triggers WHERE NOT EXISTS (SELECT `service`, `trigger`, `template`, `recipient`, `message`, `from`, `subject` FROM `email_triggers_actions` WHERE `service` = 'email' AND `recipient` = 'customer' AND `trigger` = 'store_partialpaymentnotif') LIMIT 1;

UPDATE `plugin` SET `tags`='processphones' WHERE `name` = 'shopping';
UPDATE `plugin` SET `version` = '2.9.0' WHERE `name` = 'shopping';
