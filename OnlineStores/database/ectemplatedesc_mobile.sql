/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ectemplatedesc_mobile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ectemplate_mobile_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `lang` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ectemplate_mobile_id` (`ectemplate_mobile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;