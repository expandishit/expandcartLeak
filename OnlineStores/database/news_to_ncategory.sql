/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_to_ncategory` (
  `news_id` int(11) NOT NULL,
  `ncategory_id` int(11) NOT NULL,
  PRIMARY KEY (`news_id`,`ncategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
