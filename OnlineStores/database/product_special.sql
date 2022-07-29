/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_special` (
  `product_special_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `priority` int(5) NOT NULL DEFAULT 1,
  `price` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `discount_type`VARCHAR(15) DEFAULT NULL,
  `discount_value` decimal(15,4) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `default` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`product_special_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `product_special` (`product_special_id`, `product_id`, `customer_group_id`, `priority`, `price`, `date_start`, `date_end`, `default`) VALUES
(1, 191, 1, 0, '504.9000', '2017-10-07', '2025-03-14', 1),
(2, 160, 1, 0, '1.7000', '2017-10-07', '2021-12-08', 1),
(3, 137, 1, 0, '339.1500', '2012-10-07', '2022-03-14', 1),
(4, 355, 1, 0, '75.6500', '2017-10-07', '2018-03-14', 1);
