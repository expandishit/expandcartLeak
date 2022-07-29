-- POS
DROP TABLE IF EXISTS `wkpos_user`;
CREATE TABLE IF NOT EXISTS `wkpos_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `outlet_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(9) NOT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(32) NOT NULL,
  `email` varchar(96) NOT NULL,
  `image` varchar(255) NOT NULL,
  `code` varchar(40) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_outlet`;
CREATE TABLE IF NOT EXISTS `wkpos_outlet` (
      `outlet_id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(64) NOT NULL,
      `address` text NOT NULL,
      `country_id` int(11) NOT NULL,
      `zone_id` int(11) NOT NULL,
      `status` tinyint(1) NOT NULL,
      PRIMARY KEY (`outlet_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_supplier`;
CREATE TABLE IF NOT EXISTS `wkpos_supplier` (
      `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
      `supplier_group_id` int(11) NOT NULL,
      `firstname` varchar(32) NOT NULL,
      `lastname` varchar(32) NOT NULL,
      `company` varchar(40) NOT NULL,
      `email` varchar(96) NOT NULL,
      `telephone` varchar(32) NOT NULL,
      `website` varchar(255) NOT NULL,
      `outlets` text NOT NULL,
      `extra_info` text NOT NULL,
      `status` tinyint(1) NOT NULL,
      `address` varchar(128) NOT NULL,
      `city` varchar(128) NOT NULL,
      `postcode` varchar(10) NOT NULL,
      `country_id` int(11) NOT NULL,
      `zone_id` int(11) NOT NULL,
      `date_added` datetime NOT NULL,
      PRIMARY KEY (`supplier_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_supplier_product`;
CREATE TABLE IF NOT EXISTS `wkpos_supplier_product` (
      `supplier_product_id` int(11) NOT NULL AUTO_INCREMENT,
      `supplier_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      `min_quantity` int(11) NOT NULL,
      `max_quantity` int(11) NOT NULL,
      `status` tinyint(1) NOT NULL,
      PRIMARY KEY (`supplier_product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_barcode`;
CREATE TABLE IF NOT EXISTS `wkpos_barcode` (
      `barcode_id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `barcode` varchar(15) NOT NULL,
      PRIMARY KEY (`barcode_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_supplier_request`;
CREATE TABLE IF NOT EXISTS `wkpos_supplier_request` (
      `request_id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `user_name` varchar(255) NOT NULL,
      `comment` text NOT NULL,
      `status` tinyint(1) NOT NULL,
      `cancel` tinyint(1) NOT NULL,
      `date_added` datetime NOT NULL,
      PRIMARY KEY (`request_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_request_info`;
CREATE TABLE IF NOT EXISTS `wkpos_request_info` (
      `request_info_id` int(11) NOT NULL AUTO_INCREMENT,
      `request_id` int(11) NOT NULL,
      `supplier_id` int(11) NOT NULL,
      `supplier` varchar(255) NOT NULL,
      `product_id` int(11) NOT NULL,
      `quantity` int(11) NOT NULL,
      `comment` text NOT NULL,
      `status` tinyint(1) NOT NULL,
      `cancel` tinyint(1) NOT NULL,
      `date_added` datetime NOT NULL,
      PRIMARY KEY (`request_info_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_user_orders`;
CREATE TABLE IF NOT EXISTS `wkpos_user_orders` (
      `user_order_id` int(11) NOT NULL AUTO_INCREMENT,
      `order_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `user_name` varchar(255) NOT NULL,
      `txn_id` int(11) NOT NULL,
      `order_note` text NOT NULL,
      PRIMARY KEY (`user_order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_products`;
CREATE TABLE IF NOT EXISTS `wkpos_products` (
      `wkpos_products_id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `quantity` int(11) NOT NULL,
      `status` tinyint(1) NOT NULL,
      `outlet_id` int(11) NOT NULL,
      PRIMARY KEY (`wkpos_products_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_return`;
CREATE TABLE IF NOT EXISTS `wkpos_return` (
      `wkpos_return_id` int(11) NOT NULL AUTO_INCREMENT,
      `return_id` int(11) NOT NULL,
      `product_id` int(11) NOT NULL,
      PRIMARY KEY (`wkpos_return_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `wkpos_credit`;
CREATE TABLE IF NOT EXISTS `wkpos_credit` (
      `wkpos_credit_id` int(11) NOT NULL AUTO_INCREMENT,
      `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
      `customer_id` int(11) NOT NULL,
      `order_id` int(11) NOT NULL,
      `amount` decimal(15,4) NOT NULL,
      `date_added` DATETIME NOT NULL,
      PRIMARY KEY (`wkpos_credit_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;