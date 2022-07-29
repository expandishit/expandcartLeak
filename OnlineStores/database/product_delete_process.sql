CREATE TABLE `product_delete_process` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `products` json DEFAULT NULL,
  `is_done` TINYINT(1) NOT NULL DEFAULT '0', /* 0 for still processing, 1 for done */
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
