CREATE TABLE `ec_store_credit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_credit_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `cycle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;