/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `layout_route` (
  `layout_route_id` int(11) NOT NULL AUTO_INCREMENT,
  `layout_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `route` varchar(255) NOT NULL,
  PRIMARY KEY (`layout_route_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `layout_route` VALUES (32,6,0,'account'),(33,10,0,'affiliate/'),(34,3,0,'product/category'),(35,7,0,'checkout/'),(36,8,0,'information/contact'),(38,1,0,'common/home'),(39,2,0,'product/product'),(40,11,0,'information/information'),(41,5,0,'product/manufacturer'),(42,9,0,'information/sitemap');
