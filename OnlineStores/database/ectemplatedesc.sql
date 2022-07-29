/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ectemplatedesc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Image` varchar(255) NOT NULL,
  `Demourl` varchar(255) NOT NULL,
  `Lang` varchar(5) NOT NULL,
  `TemplateId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TemplateId` (`TemplateId`),
  CONSTRAINT `ectemplatedesc_ibfk_1` FOREIGN KEY (`TemplateId`) REFERENCES `ectemplate` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ectemplatedesc` VALUES (33,'Aurora','','','','en',17),(34,'أورورا','','','','ar',17),(35,'Bitrex','','','','en',18),(36,'بيتركس','','','','ar',18),(37,'Canary','','','','en',19),(38,'كاناري','','','','ar',19),(39,'Clearion','','','','en',20),(40,'كلاريون','','','','ar',20),(41,'Manymore','','','','en',21),(42,'مانيمور','','','','ar',21),(43,'Largance','','','','en',22),(44,'لارجانس','','','','ar',22),(45,'Widewhite','','','','en',23),(46,'ويد وايت','','','','ar',23),(47,'Sky','','','','en',24),(48,'سكاي','','','','ar',24),(49,'Welldone','','','','en',25),(50,'ويل دن','','','','ar',25),(51,'Fitness','','','','en',26),(52,'فيتنس','','','','ar',26),(53,'Health','','','','en',27),(54,'هلث','','','','ar',27),(55,'Maldives','','','','en',28),(56,'مالديفز','','','','ar',28),(57,'iShop','','','','en',29),(58,'اي شوب','','','','ar',29),(59,'Jasmine','','','','en',30),(60,'جاسمين','','','','ar',30),(61,'Fantasy','','','','en',31),(62,'فانتزي','','','','ar',31),(63,'Demax','','','','en',32),(64,'ديماكس','','','','ar',32),(65,'Elite','','','','en',33),(66,'إليت','','','','ar',33),(67,'Fusion','','','','en',34),(68,'فيوجن','','','','ar',34),(69,'Gravit','','','','en',35),(70,'جرافيت','','','','ar',35),(71,'Hermes','','','','en',36),(72,'هرمز','','','','ar',36),(73,'Ivory','','','','en',37),(74,'إيفوري','','','','ar',37),(75,'Jarvis','','','','en',38),(76,'جارفيس','','','','ar',38),(77,'Utopia','','','','en',39),(78,'يوتوبيا','','','','ar',39),(79,'Venus','','','','en',40),(80,'فينوس','','','','ar',40),(81,'Storia','','','','en',41),(82,'ستوريا','','','','ar',41),(83,'Wonder','','','','en',42),(84,'ووندر','','','','ar',42);
