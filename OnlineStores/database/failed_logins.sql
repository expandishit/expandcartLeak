/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipv4` int(10) unsigned NOT NULL,
  `resource` varchar(255) NOT NULL,
  `ban_status` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `attempts` int(10) unsigned DEFAULT NULL,
  `recaptcha_status` tinyint(4) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
