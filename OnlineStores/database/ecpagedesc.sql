/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecpagedesc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Lang` varchar(5) NOT NULL,
  `PageId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `PageId` (`PageId`),
  CONSTRAINT `ecpagedesc_ibfk_1` FOREIGN KEY (`PageId`) REFERENCES `ecpage` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
