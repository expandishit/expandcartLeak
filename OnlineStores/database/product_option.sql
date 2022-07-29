/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_option` (
  `product_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `option_value` text NOT NULL,
  `required` tinyint(1) NOT NULL,
  `sort_order` int(3) NOT NULL default 0,
  PRIMARY KEY (`product_option_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=456 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

INSERT INTO `product_option` (`product_option_id`, `product_id`, `option_id`, `option_value`, `required`, `sort_order`) VALUES
(285, 160, 27, '', 1, 0),
(409, 347, 29, '', 1, 0),
(410, 347, 27, '', 0, 1),
(425, 355, 29, '', 1, 0),
(426, 355, 27, '', 1, 1),
(441, 157, 27, '', 1, 0);