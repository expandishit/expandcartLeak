/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecobjectfield` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CodeName` varchar(255) NOT NULL,
  `Type` varchar(255) NOT NULL,
  `SortOrder` int(11) NOT NULL,
  `LookUpKey` varchar(255) NOT NULL,
  `IsMultiLang` smallint(6) NOT NULL,
  `ObjectId` int(10) unsigned NOT NULL,
  `ObjectType` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ecobjectfield_ObjectType` (`ObjectType`),
  KEY `ecobjectfield_ObjectId` (`ObjectId`)
) ENGINE=InnoDB AUTO_INCREMENT=734 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
