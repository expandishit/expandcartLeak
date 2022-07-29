/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_manufacturer` (
  `coupon_id` int(11) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `manufacturer_excluded` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`manufacturer_id`,`manufacturer_excluded`,`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
