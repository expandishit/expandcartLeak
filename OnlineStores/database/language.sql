/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `code` varchar(5) NOT NULL,
  `locale` varchar(255) NOT NULL,
  `image` varchar(64) NOT NULL,
  `directory` varchar(32) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL,
   `admin` tinyint(1) NOT NULL DEFAULT 1,
   `front` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`language_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `language` VALUES (1,'en','en','en_US.UTF-8,en_US,en-gb,english','gb.png','english','english',1,1,1,1),(2,'ع','ar','ar.UTF-8,ar,ar,arabic','eg.png','arabic','arabic',2,1,1,1);
