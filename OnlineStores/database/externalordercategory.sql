/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `externalordercategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `language` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `externalordercategory` VALUES (1,'Make up / Cosmetics','Make up / Cosmetics','en'),(2,'Mobiles / Tablets','Mobiles / Tablets','en'),(3,'Body Building Supplements','Body Building Supplements','en'),(4,'Bluetooth device','Bluetooth device','en'),(5,'Wireless / Router','Wireless / Router','en'),(6,'Printers','Printers','en'),(7,'منتجات و مستحضرات تجميل','منتجات و مستحضرات تجميل','ar'),(8,'موبايل - تابلت','موبايل - تابلت','ar'),(9,'مكملات غذائية','مكملات غذائية','ar'),(10,'اجهزة بها خاصية البلوتوث','اجهزة بها خاصية البلوتوث','ar'),(11,'راوتر و اجهزة توصيل لاسلكية','راوتر و اجهزة توصيل لاسلكية','ar'),(12,'ماكينات طباعة','ماكينات طباعة','ar');
