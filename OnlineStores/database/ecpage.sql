/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecpage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CodeName` varchar(255) NOT NULL,
  `Route` varchar(255) NOT NULL,
  `TemplateId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TemplateId` (`TemplateId`),
  CONSTRAINT `ecpage_ibfk_1` FOREIGN KEY (`TemplateId`) REFERENCES `ectemplate` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
