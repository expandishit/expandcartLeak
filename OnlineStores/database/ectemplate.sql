/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ectemplate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CodeName` varchar(255) NOT NULL,
  `NextGenTemplate` smallint(1) NOT NULL DEFAULT 1,
  `ExpandishTemplate` smallint(1) NOT NULL DEFAULT 0,
  `custom_template` smallint(1) NOT NULL DEFAULT 0,
  `external_template_id` int(10) unsigned DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `theme_version` DOUBLE(4,2) NULL DEFAULT NULL,
  `attributes` JSON NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ectemplate`(`id`, `CodeName`, `NextGenTemplate`, `ExpandishTemplate`, `custom_template`, `external_template_id`, `attributes`, `category`) VALUES (17,'aurora',1,1,0,NULL,NULL,'business,electronics,sports'),(18,'bitrex',1,1,0,NULL,NULL,'fashion,business'),(19,'canary',1,1,0,NULL,NULL,'nature,sports'),(20,'clearion',1,1,0,NULL,NULL,'general,business'),(21,'manymore',1,1,0,NULL,NULL,'general,fashion,business,electronics,sports,nature'),(22,'largance',1,1,0,NULL,NULL,'general,electronics,sports,nature'),(23,'widewhite',1,1,0,NULL,NULL,'business,fashion'),(24,'sky',1,1,0,NULL,NULL,'sports,nature'),(25,'welldone',1,1,0,NULL,NULL,'fashion,business,electronics'),(26,'fitness',1,1,0,NULL,NULL,'sports,nature'),(27,'health',1,1,0,NULL,NULL,'sports,nature'),(28,'maldives',1,1,0,NULL,NULL,'general,sports,nature'),(29,'ishop',1,1,0,NULL,NULL,'electronics,business'),(30,'jasmine',1,1,0,NULL,NULL,'electronics,nature'),(31,'fantasy',1,1,0,NULL,NULL,'business,nature'),(32,'demax',1,1,0,NULL,NULL,'fashion,business,electronics'),(33,'elite',1,1,0,NULL,NULL,'general,business'),(34,'fusion',1,1,0,NULL,NULL,'general,sports'),(35,'gravit',1,1,0,NULL,NULL,'general,electronics'),(36,'hermes',1,1,0,NULL,NULL,'general,electronics'),(37,'ivory',1,1,0,NULL,NULL,'business,fashion'),(38,'jarvis',1,1,0,NULL,NULL,'sports,fashion'),(39,'utopia',1,1,0,NULL,NULL,'sports,nature'),(40,'venus',1,1,0,NULL,NULL,'electronics,fashion'),(41,'souq',1,1,0,NULL,NULL,'general,fashion,business,electronics,sports,nature'),(42,'wonder',1,1,1,49,'{\"uses_twig_extends\":1}',NULL);
