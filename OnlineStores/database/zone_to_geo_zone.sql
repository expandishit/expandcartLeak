/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zone_to_geo_zone` (
  `zone_to_geo_zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL DEFAULT 0,
  `geo_zone_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_modified` datetime DEFAULT NULL,
  `area_id` int DEFAULT NULL,
  PRIMARY KEY (`zone_to_geo_zone_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `zone_to_geo_zone` VALUES (66,184,0,4,'2017-10-29 23:20:34',NULL,NULL),(67,221,0,4,'2017-10-29 23:20:34',NULL,NULL),(68,173,0,4,'2017-10-29 23:20:34',NULL,NULL),(69,114,0,4,'2017-10-29 23:20:34',NULL,NULL),(70,17,0,4,'2017-10-29 23:20:34',NULL,NULL),(71,63,0,3,'2017-10-29 23:20:55',NULL,NULL),(72,161,0,5,'2017-10-29 23:21:19',NULL,NULL);
