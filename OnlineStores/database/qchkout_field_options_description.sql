/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qchkout_field_options_description` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_option_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(191) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX field_option_id_index (`field_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;