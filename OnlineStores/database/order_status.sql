/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status` (
  `order_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `bk_color` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`order_status_id`,`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `order_status` VALUES
(1,1,'Pending', '#FFD608'),
(1,2,'معلق', '#FFD608'),
(2,1,'Processing','#FF9800'),
(2,2,'جاري التجهيز', '#FF9800'),
(3,1,'Shipped', '#155EBC'),
(3,2,'تم شحن الطلب', '#155EBC'),
(5,1,'Complete', '#0IDB84'),
(5,2,'مكتمل', '#0IDB84'),
(7,1,'Canceled', '#E00000'),
(7,2,'ملغي', '#E00000');

/*,(8,1,'Denied', NULL),(8,2,'مرفوض', NULL),(9,1,'Canceled Reversal', NULL),(9,2,'إلغاء عكس الطلب', NULL),(10,1,'Failed', NULL),(10,2,'فشل', NULL),(11,1,'Refunded', NULL),(11,2,'مردود', NULL),(12,1,'Reversed', NULL),(12,2,'تم عكس الطلب', NULL),(13,1,'Chargeback', NULL),(13,2,'إعادة المبلغ', NULL),(14,1,'Expired', NULL),(14,2,'انتهاء الوقت', NULL),(15,1,'Processed', NULL),(15,2,'تم التجهيز', NULL),(16,1,'Voided', NULL),(16,2,'الطلب باطل', NULL);*/
