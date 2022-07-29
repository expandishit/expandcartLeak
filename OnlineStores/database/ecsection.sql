/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecsection` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CodeName` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Type` varchar(255) NOT NULL,
  `State` varchar(255) NOT NULL,
  `IsCollection` smallint(6) NOT NULL,
  `SortOrder` int(11) NOT NULL,
  `RegionId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `RegionId` (`RegionId`),
  KEY `ecsection_Type` (`Type`),
  CONSTRAINT `ecsection_ibfk_1` FOREIGN KEY (`RegionId`) REFERENCES `ecregion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
