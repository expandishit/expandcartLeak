/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eclookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `LookUpKey` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Value` varchar(255) NOT NULL,
  `Lang` varchar(5) NOT NULL,
  `SortOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
