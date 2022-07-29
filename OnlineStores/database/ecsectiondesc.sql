/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecsectiondesc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Image` varchar(255) NOT NULL,
  `Thumbnail` varchar(255) NOT NULL,
  `CollectionName` varchar(255) NOT NULL,
  `CollectionItemName` varchar(255) NOT NULL,
  `CollectionButtonName` varchar(255) NOT NULL,
  `Lang` varchar(5) NOT NULL,
  `SectionId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `SectionId` (`SectionId`),
  CONSTRAINT `ecsectiondesc_ibfk_1` FOREIGN KEY (`SectionId`) REFERENCES `ecsection` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
