/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qchkout_fields_description` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `field_title` varchar(191) NOT NULL,
  `field_error` varchar(255) NULL,
  `field_tooltip` varchar(191) NULL,
  PRIMARY KEY (`id`),
  INDEX field_id_index (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
