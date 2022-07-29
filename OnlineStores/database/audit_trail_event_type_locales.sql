/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_trail_event_type_locales` (
  `event_locale_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `event_type_text` varchar(255) NOT NULL,
  PRIMARY KEY (`event_locale_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `audit_trail_event_type_locales` VALUES (1,1,1,'Create a product'),(2,1,2,'أدخال منتج جديد'),(3,2,1,'Update a product'),(4,2,2,'تحديث منتج'),(5,3,1,'Delete a product'),(6,3,2,'حذف منتج');
