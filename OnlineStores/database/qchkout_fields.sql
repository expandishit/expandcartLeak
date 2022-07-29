/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qchkout_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(50) NOT NULL,
  `field_type` varchar(10) DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `field_type_name` varchar(20) DEFAULT NULL,
  `can_delete` char(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
