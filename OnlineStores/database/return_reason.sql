/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_reason` (
  `return_reason_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`return_reason_id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `return_reason` VALUES (1,1,'Dead On Arrival'),(1,2,'تالف عند الاستلام'),(2,1,'Received Wrong Item'),(2,2,'استلمت منتج بالخطأ'),(3,1,'Order Error'),(3,2,'منتج خاطئ'),(4,1,'Faulty, please supply details'),(4,2,'يوجد خلل، الرجاء التوضيح'),(5,1,'Other, please supply details'),(5,2,'أخرى، الرجاء التوضيح');
