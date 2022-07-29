/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `option_value` (
  `option_value_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL,
  PRIMARY KEY (`option_value_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `option_value` VALUES (86,27,'',0),(87,27,'',0),(88,27,'',0),(89,27,'',0),(90,27,'',0),(91,28,'no_image.jpg',0),(92,28,'no_image.jpg',1),(93,28,'no_image.jpg',2),(94,28,'no_image.jpg',3),(95,28,'no_image.jpg',4),(96,27,'',0),(97,27,'',0),(98,27,'',0),(99,27,'',0),(100,29,'no_image.jpg',0),(101,29,'no_image.jpg',1),(102,29,'no_image.jpg',2),(103,29,'no_image.jpg',3),(104,29,'no_image.jpg',4),(105,27,'',0),(106,29,'no_image.jpg',5),(107,29,'no_image.jpg',6),(108,29,'no_image.jpg',7),(109,29,'no_image.jpg',8),(110,29,'no_image.jpg',9),(111,29,'no_image.jpg',10),(112,27,'',0);
