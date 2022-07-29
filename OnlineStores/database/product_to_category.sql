/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_to_category` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


INSERT INTO `product_to_category` (`product_id`, `category_id`) VALUES
(124, 77),
(124, 94),
(137, 89),
(137, 91),
(157, 89),
(157, 91),
(160, 77),
(160, 78),
(160, 79),
(161, 89),
(161, 91),
(161, 92),
(191, 89),
(191, 92),
(347, 83),
(347, 87),
(355, 83),
(355, 84);