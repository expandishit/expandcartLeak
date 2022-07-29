/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_to_store` (
  `news_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`news_id`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
