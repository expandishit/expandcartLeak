/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signupkw` (
  `enablemod` tinyint(4) NOT NULL DEFAULT 0,
  `single_box` tinyint(4) NOT NULL DEFAULT 0,
  `newsletter_sub_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `login_register_phonenumber_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `register_phonenumber_unique` tinyint(4) NOT NULL DEFAULT 0,
  `country_phone_code` TINYINT(1) NOT NULL DEFAULT 0,
  `country_phone_code_login` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `signupkw` VALUES (0,0,0,0,0,0,0);
