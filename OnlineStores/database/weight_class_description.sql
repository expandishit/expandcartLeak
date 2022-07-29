/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weight_class_description` (
  `weight_class_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `title` varchar(32) NOT NULL,
  `unit` varchar(4) NOT NULL,
  PRIMARY KEY (`weight_class_id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `weight_class_description` VALUES (1,1,'Kilogram','kg'),(1,2,'كيلو جرام','kg'),(2,1,'Gram','g'),(2,2,'جرام','g'),(5,1,'Pound ','lb'),(5,2,'باوند','lb'),(6,1,'Ounce','oz'),(6,2,'اونصة','oz');
