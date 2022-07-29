/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_to_dropna` (
  `category_id` int(11) NOT NULL,
  `dropna_category_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`category_id`,`dropna_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
