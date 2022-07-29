/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_modification` (
  `order_modification_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status_id` int(5) NOT NULL,
  `user_id` int(10) DEFAULT 0,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_modification_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;