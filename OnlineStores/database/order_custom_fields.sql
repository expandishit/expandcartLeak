/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `value` varchar(191) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX order_field_id_index (`field_id`, `order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;