/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_option_value` (
  `product_option_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_option_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `option_value_id` int(11) NOT NULL,
  `quantity` int(3) NOT NULL,
  `subtract` tinyint(1) NOT NULL,
  `price` decimal(15,4) NOT NULL,
  `price_prefix` varchar(1) NOT NULL,
  `points` int(8) NOT NULL,
  `points_prefix` varchar(1) NOT NULL,
  `weight` decimal(15,8) NOT NULL,
  `weight_prefix` varchar(1) NOT NULL,
  `sort_order` INT(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`product_option_value_id`),
  KEY `pov_product_id_option_value_id` (`product_id`,`option_value_id`),
  KEY `idx_POV_option_value_id` (`option_value_id`,`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=553 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `product_option_value` (`product_option_value_id`, `product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`, `sort_order`) VALUES
(184, 285, 160, 27, 97, 20, 0, '12.0000', '+', 0, '', '0.00000000', '+', 0),
(185, 285, 160, 27, 90, 20, 0, '14.0000', '+', 0, '', '0.00000000', '+', 1),
(186, 285, 160, 27, 96, 20, 0, '20.0000', '+', 0, '', '0.00000000', '+', 2),
(187, 285, 160, 27, 89, 20, 0, '25.0000', '+', 0, '', '0.00000000', '+', 3),
(188, 285, 160, 27, 87, 0, 0, '30.0000', '+', 0, '', '0.00000000', '+', 4),
(414, 409, 347, 29, 104, 15, 0, '0.0000', '+', 0, '', '0.00000000', '+', 0),
(415, 409, 347, 29, 101, 10, 0, '0.0000', '+', 0, '', '0.00000000', '+', 1),
(416, 409, 347, 29, 102, 5, 0, '0.0000', '+', 0, '', '0.00000000', '+', 2),
(417, 409, 347, 29, 103, 20, 0, '0.0000', '+', 0, '', '0.00000000', '+', 3),
(418, 410, 347, 27, 98, 5, 0, '0.0000', '+', 0, '', '0.00000000', '+', 0),
(437, 425, 355, 29, 102, 19, 0, '0.0000', '+', 0, '', '0.00000000', '+', 0),
(438, 426, 355, 27, 90, 10, 0, '0.0000', '+', 0, '', '0.00000000', '+', 0),
(477, 410, 347, 27, 87, 19, 0, '0.0000', '+', 0, '', '0.00000000', '+', 1),
(502, 425, 355, 29, 101, 20, 0, '0.0000', '+', 0, '', '0.00000000', '+', 1),
(503, 426, 355, 27, 87, 12, 0, '0.0000', '+', 0, '', '0.00000000', '+', 1),
(506, 441, 157, 27, 90, 200, 0, '0.0000', '+', 0, '', '0.00000000', '+', 0),
(507, 441, 157, 27, 97, 105, 0, '0.0000', '+', 0, '', '0.00000000', '+', 1);