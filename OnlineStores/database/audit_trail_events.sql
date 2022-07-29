/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_trail_events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_user_id` int(11) NOT NULL,
  `event_resource_id` int(11) DEFAULT NULL,
  `event_resource_type` varchar(255) DEFAULT NULL,
  `event_type_id` varchar(50) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_date_time` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `event_user_ip` varchar(255) DEFAULT NULL,
  `event_user_group` varchar(50) NOT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
