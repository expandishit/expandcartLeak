/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon` (
`coupon_id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(128) NOT NULL,
`code` varchar(20) NOT NULL,
`type` char(1) NOT NULL,
`discount` decimal(15,4) NOT NULL,
`logged` tinyint(1) NOT NULL,
`shipping` tinyint(1) NOT NULL,
`tax_excluded` tinyint(1) NOT NULL DEFAULT 0,
`total` decimal(15,4) NOT NULL,
`minimum_to_apply` decimal(15,2) DEFAULT NULL,
`maximum_limit` decimal(15,2) DEFAULT NULL,
`date_start` date DEFAULT NULL,
`date_end` date DEFAULT NULL,
`uses_total` int(11) NOT NULL,
`uses_customer` varchar(11) NOT NULL,
`status` tinyint(1) NOT NULL,
`notify_mobile` tinyint(1) NOT NULL,
`date_added` datetime NOT NULL DEFAULT current_timestamp(),
`details` JSON DEFAULT NULL,
`notification_status` tinyint(1) NOT NULL DEFAULT 0,
`automatic_apply` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
