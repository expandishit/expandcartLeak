/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecobjectfieldval` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Value` text NOT NULL,
  `Lang` varchar(5) NOT NULL,
  `ObjectFieldId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ObjectFieldId` (`ObjectFieldId`),
  CONSTRAINT `ecobjectfieldval_ibfk_1` FOREIGN KEY (`ObjectFieldId`) REFERENCES `ecobjectfield` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1467 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
