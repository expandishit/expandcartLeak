/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geo_zone` (
  `geo_zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`geo_zone_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `geo_zone` VALUES (3,'Egypt Shipping','Egypt Area','2017-10-29 23:20:55','2009-01-06 23:26:25'),(4,'Gulf Shipping','Gulf Area','2017-10-29 23:20:34','2009-06-23 01:14:53'),(5,'Oman Shipping','Oman Area',NULL,'2017-10-29 23:21:19');
