/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `import_type` varchar(40) NOT NULL,
  `import_date` datetime NOT NULL,
  `file_mapping` text NOT NULL,
  `import_status` tinyint(4) NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `error` text DEFAULT NULL,
  `number_of_records` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
