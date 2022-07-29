/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banner_image_description` (
  `banner_image_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  PRIMARY KEY (`banner_image_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `banner_image_description` VALUES (187,1,11,'Adv1'),(187,2,11,'Adv1'),(188,1,12,'Adv2'),(188,2,12,'Adv2'),(189,1,13,'Adv big'),(189,2,13,'Adv big'),(190,1,14,'Shoes'),(190,2,14,'Shoes'),(191,1,14,'From Home'),(191,2,14,'FromeHome'),(192,1,14,'Sony Experia'),(192,2,14,'Sony Experia');
