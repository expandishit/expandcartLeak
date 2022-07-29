/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_status` (
  `stock_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `default_color` varchar(7) not null default '#dddddd',
  `current_color` varchar(7) not null default '#dddddd',
  PRIMARY KEY (`stock_status_id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `stock_status`(`stock_status_id`, `language_id`, `name`) VALUES (5,1,'Out Of Stock'),(5,2,'غير متوفر'),(6,1,'2 - 3 Days'),(6,2,'2 - 3 أيام'),(7,1,'In Stock'),(7,2,'متوفر'),(8,1,'Pre-Order'),(8,2,'حجز مسبق');
