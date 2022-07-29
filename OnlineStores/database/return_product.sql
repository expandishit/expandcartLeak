/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_product` (
  `return_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `model` varchar(64) NOT NULL,
  `quantity` int(4) NOT NULL,
  `is_product_quantity_added` TINYINT(1)  NOT NULL,
  `is_amount_added_to_customer` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`return_product_id`),
  KEY `idx_rp_rid` (`return_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
