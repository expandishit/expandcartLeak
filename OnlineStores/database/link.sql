/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link` (
  `link_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `route` text NOT NULL,
  PRIMARY KEY (`link_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `link` VALUES (1,'home','#'),(2,'category','product/category&path={cat_id}'),(3,'product','product/product&path={cat_id}&product_id={p_id}'),(4,'web page','information/information&information_id={info_id}'),(5,'customer_account','account/account'),(6,'customer_newsletter','account/newsletter'),(7,'customer_order','account/order'),(8,'customer transaction','account/transaction'),(9,'customer voucher','account/voucher'),(10,'customer wishlist','account/wishlist'),(11,'affiliate_account','affiliate/account'),(12,'affiliate_history','affiliate/history'),(13,'affiliate_transaction','affiliate/transaction'),(14,'blog','blog/blog'),(15,'contact','information/contact'),(16,'sitemap','information/sitemap'),(17,'product_compare','product/compare'),(18,'product_search','product/search');
