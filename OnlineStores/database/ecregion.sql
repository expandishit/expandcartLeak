/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecregion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CodeName` varchar(255) NOT NULL,
  `RowOrder` int(11) NOT NULL,
  `ColOrder` int(11) NOT NULL,
  `ColWidth` int(11) NOT NULL,
  `PageId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `PageId` (`PageId`),
  CONSTRAINT `ecregion_ibfk_1` FOREIGN KEY (`PageId`) REFERENCES `ecpage` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
