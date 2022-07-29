/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_to_dropna` (
  `product_id` int(11) NOT NULL,
  `dropna_product_id` int(11) NOT NULL DEFAULT 0,
  `dropna_user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`,`dropna_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
