/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remember_tokens` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `selector` char(12) DEFAULT NULL,
  `validator` char(64) DEFAULT NULL,
  `expires` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`token_id`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
