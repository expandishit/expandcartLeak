/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banner_image` (
  `banner_image_id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`banner_image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `banner_image` VALUES (187,11,'','data/Gazal/ADV6-.png'),(188,12,'','data/Gazal/ADV7.png'),(189,13,'','data/Gazal/adv8.png'),(190,14,'','data/Gazal/b12.jpg'),(191,14,'','data/Gazal/B10.jpg'),(192,14,'','data/Gazal/B3.jpg');
