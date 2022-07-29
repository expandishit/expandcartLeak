/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecregiondesc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Lang` varchar(5) NOT NULL,
  `RegionId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `RegionId` (`RegionId`),
  CONSTRAINT `ecregiondesc_ibfk_1` FOREIGN KEY (`RegionId`) REFERENCES `ecregion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
