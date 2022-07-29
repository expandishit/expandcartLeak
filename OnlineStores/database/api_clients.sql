/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` varchar(50) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `target` varchar(20) DEFAULT NULL,
  `store_code` varchar(50) DEFAULT NULL,
  `client_status` int(2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`,`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
