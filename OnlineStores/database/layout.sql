/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `layout` (
  `layout_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`layout_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `layout` VALUES (1,'صفحة البداية'),(2,'صفحة المنتج'),(3,'صفحة أقسام الموقع'),(4,'الصفحة الافتراضية'),(5,'صفحة الشركات'),(6,'صفحة حساب العميل'),(7,'صفحة إتمام الطلب'),(8,'صفحة اتصل بنا'),(9,'صفحة خريطة الموقع'),(10,'صفحة أفليت'),(11,'صفحة المعلومات');
