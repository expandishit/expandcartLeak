/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `iso_code_2` varchar(2) NOT NULL,
  `iso_code_3` varchar(3) NOT NULL,
  `address_format` text NOT NULL,
  `postcode_required` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `phonecode` int(5) DEFAULT NULL,
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `country` VALUES (1,'Afghanistan','AF','AFG','',0,1,93),(2,'Albania','AL','ALB','',0,1,355),(3,'Algeria','DZ','DZA','',0,1,213),(4,'American Samoa','AS','ASM','',0,1,1684),(5,'Andorra','AD','AND','',0,1,376),(6,'Angola','AO','AGO','',0,1,244),(7,'Anguilla','AI','AIA','',0,1,1264),(8,'Antarctica','AQ','ATA','',0,1,672),(9,'Antigua and Barbuda','AG','ATG','',0,1,1268),(10,'Argentina','AR','ARG','',0,1,54),(11,'Armenia','AM','ARM','',0,1,374),(12,'Aruba','AW','ABW','',0,1,297),(13,'Australia','AU','AUS','',0,1,61),(14,'Austria','AT','AUT','',0,1,43),(15,'Azerbaijan','AZ','AZE','',0,1,994),(16,'Bahamas','BS','BHS','',0,1,1242),(17,'Bahrain','BH','BHR','',0,1,973),(18,'Bangladesh','BD','BGD','',0,1,880),(19,'Barbados','BB','BRB','',0,1,1246),(20,'Belarus','BY','BLR','',0,1,375),(21,'Belgium','BE','BEL','{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{postcode} {city}\r\n{country}',0,1,32),(22,'Belize','BZ','BLZ','',0,1,501),(23,'Benin','BJ','BEN','',0,1,229),(24,'Bermuda','BM','BMU','',0,1,1441),(25,'Bhutan','BT','BTN','',0,1,975),(26,'Bolivia','BO','BOL','',0,1,591),(27,'Bosnia and Herzegovina','BA','BIH','',0,1,387),(28,'Botswana','BW','BWA','',0,1,267),(29,'Bouvet Island','BV','BVT','',0,1,0),(30,'Brazil','BR','BRA','',0,1,55),(31,'British Indian Ocean Territory','IO','IOT','',0,1,246),(32,'Brunei Darussalam','BN','BRN','',0,1,673),(33,'Bulgaria','BG','BGR','',0,1,359),(34,'Burkina Faso','BF','BFA','',0,1,226),(35,'Burundi','BI','BDI','',0,1,257),(36,'Cambodia','KH','KHM','',0,1,855),(37,'Cameroon','CM','CMR','',0,1,237),(38,'Canada','CA','CAN','',0,1,1),(39,'Cape Verde','CV','CPV','',0,1,238),(40,'Cayman Islands','KY','CYM','',0,1,1345),(41,'Central African Republic','CF','CAF','',0,1,236),(42,'Chad','TD','TCD','',0,1,235),(43,'Chile','CL','CHL','',0,1,56),(44,'China','CN','CHN','',0,1,86),(45,'Christmas Island','CX','CXR','',0,1,61),(46,'Cocos (Keeling) Islands','CC','CCK','',0,1,672),(47,'Colombia','CO','COL','',0,1,57),(48,'Comoros','KM','COM','',0,1,269),(49,'Congo','CG','COG','',0,1,242),(50,'Cook Islands','CK','COK','',0,1,682),(51,'Costa Rica','CR','CRI','',0,1,506),(52,'Cote D\'Ivoire','CI','CIV','',0,1,225),(53,'Croatia','HR','HRV','',0,1,385),(54,'Cuba','CU','CUB','',0,1,53),(55,'Cyprus','CY','CYP','',0,1,357),(56,'Czech Republic','CZ','CZE','',0,1,420),(57,'Denmark','DK','DNK','',0,1,45),(58,'Djibouti','DJ','DJI','',0,1,253),(59,'Dominica','DM','DMA','',0,1,1767),(60,'Dominican Republic','DO','DOM','',0,1,1809),(61,'East Timor','TL','TLS','',0,1,670),(62,'Ecuador','EC','ECU','',0,1,593),(63,'Egypt','EG','EGY','',0,1,20),(64,'El Salvador','SV','SLV','',0,1,503),(65,'Equatorial Guinea','GQ','GNQ','',0,1,240),(66,'Eritrea','ER','ERI','',0,1,291),(67,'Estonia','EE','EST','',0,1,372),(68,'Ethiopia','ET','ETH','',0,1,251),(69,'Falkland Islands (Malvinas)','FK','FLK','',0,1,500),(70,'Faroe Islands','FO','FRO','',0,1,298),(71,'Fiji','FJ','FJI','',0,1,679),(72,'Finland','FI','FIN','',0,1,358),(74,'France, Metropolitan','FR','FRA','{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{postcode} {city}\r\n{country}',1,1,33),(75,'French Guiana','GF','GUF','',0,1,594),(76,'French Polynesia','PF','PYF','',0,1,689),(77,'French Southern Territories','TF','ATF','',0,1,0),(78,'Gabon','GA','GAB','',0,1,241),(79,'Gambia','GM','GMB','',0,1,220),(80,'Georgia','GE','GEO','',0,1,995),(81,'Germany','DE','DEU','{company}\r\n{firstname} {lastname}\r\n{address_1}\r\n{address_2}\r\n{postcode} {city}\r\n{country}',1,1,49),(82,'Ghana','GH','GHA','',0,1,233),(83,'Gibraltar','GI','GIB','',0,1,350),(84,'Greece','GR','GRC','',0,1,30),(85,'Greenland','GL','GRL','',0,1,299),(86,'Grenada','GD','GRD','',0,1,1473),(87,'Guadeloupe','GP','GLP','',0,1,590),(88,'Guam','GU','GUM','',0,1,1671),(89,'Guatemala','GT','GTM','',0,1,502),(90,'Guinea','GN','GIN','',0,1,224),(91,'Guinea-Bissau','GW','GNB','',0,1,245),(92,'Guyana','GY','GUY','',0,1,592),(93,'Haiti','HT','HTI','',0,1,509),(94,'Heard and Mc Donald Islands','HM','HMD','',0,1,0),(95,'Honduras','HN','HND','',0,1,504),(96,'Hong Kong','HK','HKG','',0,1,852),(97,'Hungary','HU','HUN','',0,1,36),(98,'Iceland','IS','ISL','',0,1,354),(99,'India','IN','IND','',0,1,91),(100,'Indonesia','ID','IDN','',0,1,62),(101,'Iran (Islamic Republic of)','IR','IRN','',0,1,98),(102,'Iraq','IQ','IRQ','',0,1,964),(103,'Ireland','IE','IRL','',0,1,353),(104,'Israel','IL','ISR','',0,1,972),(105,'Italy','IT','ITA','',0,1,39),(106,'Jamaica','JM','JAM','',0,1,1876),(107,'Japan','JP','JPN','',0,1,81),(108,'Jordan','JO','JOR','',0,1,962),(109,'Kazakhstan','KZ','KAZ','',0,1,7),(110,'Kenya','KE','KEN','',0,1,254),(111,'Kiribati','KI','KIR','',0,1,686),(112,'North Korea','KP','PRK','',0,1,850),(113,'Korea, Republic of','KR','KOR','',0,1,82),(114,'Kuwait','KW','KWT','',0,1,965),(115,'Kyrgyzstan','KG','KGZ','',0,1,996),(116,'Lao People\'s Democratic Republic','LA','LAO','',0,1,856),(117,'Latvia','LV','LVA','',0,1,371),(118,'Lebanon','LB','LBN','',0,1,961),(119,'Lesotho','LS','LSO','',0,1,266),(120,'Liberia','LR','LBR','',0,1,231),(121,'Libyan Arab Jamahiriya','LY','LBY','',0,1,218),(122,'Liechtenstein','LI','LIE','',0,1,423),(123,'Lithuania','LT','LTU','',0,1,370),(124,'Luxembourg','LU','LUX','',0,1,352),(125,'Macau','MO','MAC','',0,1,853),(126,'FYROM','MK','MKD','',0,1,389),(127,'Madagascar','MG','MDG','',0,1,261),(128,'Malawi','MW','MWI','',0,1,265),(129,'Malaysia','MY','MYS','',0,1,60),(130,'Maldives','MV','MDV','',0,1,960),(131,'Mali','ML','MLI','',0,1,223),(132,'Malta','MT','MLT','',0,1,356),(133,'Marshall Islands','MH','MHL','',0,1,692),(134,'Martinique','MQ','MTQ','',0,1,596),(135,'Mauritania','MR','MRT','',0,1,222),(136,'Mauritius','MU','MUS','',0,1,230),(137,'Mayotte','YT','MYT','',0,1,269),(138,'Mexico','MX','MEX','',0,1,52),(139,'Micronesia, Federated States of','FM','FSM','',0,1,691),(140,'Moldova, Republic of','MD','MDA','',0,1,373),(141,'Monaco','MC','MCO','',0,1,377),(142,'Mongolia','MN','MNG','',0,1,976),(143,'Montserrat','MS','MSR','',0,1,1664),(144,'Morocco','MA','MAR','',0,1,212),(145,'Mozambique','MZ','MOZ','',0,1,258),(146,'Myanmar','MM','MMR','',0,1,95),(147,'Namibia','NA','NAM','',0,1,264),(148,'Nauru','NR','NRU','',0,1,674),(149,'Nepal','NP','NPL','',0,1,977),(150,'Netherlands','NL','NLD','',0,1,31),(151,'Netherlands Antilles','AN','ANT','',0,1,599),(152,'New Caledonia','NC','NCL','',0,1,687),(153,'New Zealand','NZ','NZL','',0,1,64),(154,'Nicaragua','NI','NIC','',0,1,505),(155,'Niger','NE','NER','',0,1,227),(156,'Nigeria','NG','NGA','',0,1,234),(157,'Niue','NU','NIU','',0,1,683),(158,'Norfolk Island','NF','NFK','',0,1,672),(159,'Northern Mariana Islands','MP','MNP','',0,1,1670),(160,'Norway','NO','NOR','',0,1,47),(161,'Oman','OM','OMN','',0,1,968),(162,'Pakistan','PK','PAK','',0,1,92),(163,'Palau','PW','PLW','',0,1,680),(164,'Panama','PA','PAN','',0,1,507),(165,'Papua New Guinea','PG','PNG','',0,1,675),(166,'Paraguay','PY','PRY','',0,1,595),(167,'Peru','PE','PER','',0,1,51),(168,'Philippines','PH','PHL','',0,1,63),(169,'Pitcairn','PN','PCN','',0,1,64),(170,'Poland','PL','POL','',0,1,48),(171,'Portugal','PT','PRT','',0,1,351),(172,'Puerto Rico','PR','PRI','',0,1,1787),(173,'Qatar','QA','QAT','',0,1,974),(174,'Reunion','RE','REU','',0,1,262),(175,'Romania','RO','ROM','',0,1,40),(176,'Russian Federation','RU','RUS','',0,1,70),(177,'Rwanda','RW','RWA','',0,1,250),(178,'Saint Kitts and Nevis','KN','KNA','',0,1,1869),(179,'Saint Lucia','LC','LCA','',0,1,1758),(180,'Saint Vincent and the Grenadines','VC','VCT','',0,1,1784),(181,'Samoa','WS','WSM','',0,1,684),(182,'San Marino','SM','SMR','',0,1,378),(183,'Sao Tome and Principe','ST','STP','',0,1,239),(184,'Saudi Arabia','SA','SAU','',0,1,966),(185,'Senegal','SN','SEN','',0,1,221),(186,'Seychelles','SC','SYC','',0,1,248),(187,'Sierra Leone','SL','SLE','',0,1,232),(188,'Singapore','SG','SGP','',0,1,65),(189,'Slovak Republic','SK','SVK','{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city} {postcode}\r\n{zone}\r\n{country}',0,1,421),(190,'Slovenia','SI','SVN','',0,1,386),(191,'Solomon Islands','SB','SLB','',0,1,677),(192,'Somalia','SO','SOM','',0,1,252),(193,'South Africa','ZA','ZAF','',0,1,27),(194,'South Georgia &amp; South Sandwich Islands','GS','SGS','',0,1,0),(195,'Spain','ES','ESP','',0,1,34),(196,'Sri Lanka','LK','LKA','',0,1,94),(197,'St. Helena','SH','SHN','',0,1,290),(198,'St. Pierre and Miquelon','PM','SPM','',0,1,508),(199,'Sudan','SD','SDN','',0,1,249),(200,'Suriname','SR','SUR','',0,1,597),(201,'Svalbard and Jan Mayen Islands','SJ','SJM','',0,1,47),(202,'Swaziland','SZ','SWZ','',0,1,268),(203,'Sweden','SE','SWE','{company}\r\n{firstname} {lastname}\r\n{address_1}\r\n{address_2}\r\n{postcode} {city}\r\n{country}',1,1,46),(204,'Switzerland','CH','CHE','',0,1,41),(205,'Syrian Arab Republic','SY','SYR','',0,1,963),(206,'Taiwan','TW','TWN','',0,1,886),(207,'Tajikistan','TJ','TJK','',0,1,992),(208,'Tanzania, United Republic of','TZ','TZA','',0,1,255),(209,'Thailand','TH','THA','',0,1,66),(210,'Togo','TG','TGO','',0,1,228),(211,'Tokelau','TK','TKL','',0,1,690),(212,'Tonga','TO','TON','',0,1,676),(213,'Trinidad and Tobago','TT','TTO','',0,1,1868),(214,'Tunisia','TN','TUN','',0,1,216),(215,'Turkey','TR','TUR','',0,1,90),(216,'Turkmenistan','TM','TKM','',0,1,7370),(217,'Turks and Caicos Islands','TC','TCA','',0,1,1649),(218,'Tuvalu','TV','TUV','',0,1,688),(219,'Uganda','UG','UGA','',0,1,256),(220,'Ukraine','UA','UKR','',0,1,380),(221,'United Arab Emirates','AE','ARE','',0,1,971),(222,'United Kingdom','GB','GBR','',1,1,44),(223,'United States','US','USA','{firstname} {lastname}\r\n{company}\r\n{address_1}\r\n{address_2}\r\n{city}, {zone} {postcode}\r\n{country}',0,1,1),(224,'United States Minor Outlying Islands','UM','UMI','',0,1,1),(225,'Uruguay','UY','URY','',0,1,598),(226,'Uzbekistan','UZ','UZB','',0,1,998),(227,'Vanuatu','VU','VUT','',0,1,678),(228,'Vatican City State (Holy See)','VA','VAT','',0,1,39),(229,'Venezuela','VE','VEN','',0,1,58),(230,'Viet Nam','VN','VNM','',0,1,84),(231,'Virgin Islands (British)','VG','VGB','',0,1,1284),(232,'Virgin Islands (U.S.)','VI','VIR','',0,1,1340),(233,'Wallis and Futuna Islands','WF','WLF','',0,1,681),(234,'Western Sahara','EH','ESH','',0,1,212),(235,'Yemen','YE','YEM','',0,1,967),(237,'Democratic Republic of Congo','CD','COD','',0,1,242),(238,'Zambia','ZM','ZMB','',0,1,260),(239,'Zimbabwe','ZW','ZWE','',0,1,263),(240,'Jersey','JE','JEY','',1,1,44),(241,'Guernsey','GG','GGY','',1,1,44),(242,'Montenegro','ME','MNE','',0,1,382),(243,'Serbia','RS','SRB','',0,1,381),(244,'Aaland Islands','AX','ALA','',0,1,358),(245,'Bonaire, Sint Eustatius and Saba','BQ','BES','',0,1,599),(246,'Curacao','CW','CUW','',0,1,599),(247,'Palestinian Territory, Occupied','PS','PSE','',0,1,970),(248,'South Sudan','SS','SSD','',0,1,211),(249,'St. Barthelemy','BL','BLM','',0,1,590),(250,'St. Martin (French part)','MF','MAF','',0,1,590),(251,'Canary Islands','IC','ICA','',0,1,34);