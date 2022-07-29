/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `externalorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(500) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(15,4) NOT NULL,
  `categoryvalue` varchar(255) NOT NULL,
  `statusvalue` varchar(255) NOT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `createdon` datetime NOT NULL DEFAULT current_timestamp(),
  `customerid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
