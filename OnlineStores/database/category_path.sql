/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_path` (
  `category_id` int(11) NOT NULL,
  `path_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`category_id`,`path_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `category_path` VALUES (77,77,0),(78,77,0),(78,78,1),(79,77,0),(79,79,1),(80,77,0),(80,80,1),(81,77,0),(81,81,1),(82,77,0),(82,82,1),(83,83,0),(84,83,0),(84,84,1),(85,83,0),(85,85,1),(86,83,0),(86,86,1),(87,83,0),(87,87,1),(88,83,0),(88,88,1),(89,89,0),(90,89,0),(90,90,1),(91,89,0),(91,91,1),(92,89,0),(92,92,1),(93,89,0),(93,93,1),(94,89,0),(94,94,1),(95,95,0),(96,95,0),(96,96,1),(97,95,0),(97,97,1),(98,95,0),(98,98,1),(99,95,0),(99,99,1),(100,95,0),(100,100,1);
