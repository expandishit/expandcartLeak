/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link_description` (
  `link_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` text NOT NULL,
  PRIMARY KEY (`link_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `link_description` VALUES (1,1,'Home'),(1,2,'الصفحة الرئيسية'),(2,1,'Categories'),(2,2,'الأقسام'),(3,1,'Products'),(3,2,'المنتاجات'),(4,1,'Web Pages'),(4,2,'صفحات الموفع'),(5,1,'Customer Account'),(5,2,'حساب العميل'),(6,1,'Newsletter'),(6,2,'النشرة الأخبارية'),(7,1,'Customer Orders'),(7,2,'الطلبات'),(8,1,'Customer Transactions'),(8,2,'تعامﻻت العميل'),(9,1,'Customer Vouchers'),(9,2,'ايصاﻻت العميل'),(10,1,'Customer Wishlist'),(10,2,'سلة امنيات العميل'),(11,1,'Affiliate Account'),(11,2,'حساب افلييت'),(12,1,'Affiliate History'),(12,2,'سجل افلييت'),(13,1,'Affiliate Transaction'),(13,2,'تعامﻻت افلييت'),(14,1,'Blog'),(14,2,'المدونة'),(15,1,'Contact'),(15,2,'التواصل'),(16,1,'Sitemap'),(16,2,'جميع الأقسام'),(17,1,'Product Compare'),(17,2,'مقارنة المنتاجات'),(18,1,'Product Search'),(18,2,'البحث عن منتج');
