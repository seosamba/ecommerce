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
(64, 'CA', 'YT', 'Yukon Territory');

CREATE TABLE IF NOT EXISTS `shopping_brands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_config` (
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `price` decimal(10,2) DEFAULT NULL,
  `tax_class` enum('0','1','2','3') COLLATE utf8_unicode_ci DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_product_has_tag` (
  `product_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_product_has_option` (
  `product_id` int(10) unsigned NOT NULL,
  `option_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`option_id`),
  KEY `fk_shopping_product_has_shopping_product_option_shopping_prod2` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_product_has_related` (
  `product_id` int(10) unsigned NOT NULL,
  `related_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`related_id`),
  KEY `fk_shopping_product1` (`related_id`),
  KEY `fk_shopping_product2` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_product_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('dropdown','radio','text','date','file') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `indTitle` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_product_option_selection` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `option_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `priceSign` enum('+','-') COLLATE utf8_unicode_ci DEFAULT NULL,
  `priceValue` decimal(10,2) DEFAULT NULL,
  `priceType` enum('percent','unit') COLLATE utf8_unicode_ci DEFAULT NULL,
  `weightSign` enum('+','-') COLLATE utf8_unicode_ci DEFAULT NULL,
  `weightValue` decimal(8,3) DEFAULT NULL,
  `isDefault` enum('1','0') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indTitle` (`title`),
  KEY `fk_shopping_product_option_selection_shopping_product_option1` (`option_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zoneId` int(10) unsigned NOT NULL,
  `rate1` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rate2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rate3` decimal(10,2) NOT NULL DEFAULT '0.00',
  `isDefault` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `zoneId` (`zoneId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_zone` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_zone_country` (
  `zone_id` int(11) unsigned NOT NULL,
  `country_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`zone_id`,`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_zone_state` (
  `zone_id` int(10) unsigned NOT NULL,
  `state_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`zone_id`,`state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_zone_zip` (
  `zone_id` int(11) NOT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`zone_id`,`zip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_cart_session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(10) unsigned DEFAULT NULL,
  `shipping_address_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_address_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_price` decimal(10,2) DEFAULT NULL,
  `shipping_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_service` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shipping_tracking_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Shipping Tracking ID',
  `status` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gateway` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sub_total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Sub Total',
  `total_tax` double(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Total Tax',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Sub Total + Total Tax + Shipping',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `shipping_address_id` (`shipping_address_id`),
  KEY `billing_address_id` (`billing_address_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_cart_session_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` int(10) unsigned DEFAULT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `options` text,
  `price` decimal(10,2) DEFAULT NULL COMMENT  'Price w/o Tax',
  `qty` int(10) unsigned DEFAULT NULL,
  `tax` int(11) DEFAULT NULL COMMENT  'Tax Price',
  `tax_price` decimal(10,2) DEFAULT NULL COMMENT  'Price + Tax',
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`,`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopping_customer_address` (
  `id` varchar(32) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `address_type` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `state` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `shopping_customer_info` (
  `user_id` int(10) unsigned NOT NULL,
  `default_shipping_address_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_billing_address_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  ADD CONSTRAINT `shopping_customer_address_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `shopping_customer_info`
  ADD CONSTRAINT `shopping_customer_info_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `shopping_tax`
  ADD CONSTRAINT `shopping_tax_ibfk_1` FOREIGN KEY (`zoneId`) REFERENCES `shopping_zone` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

