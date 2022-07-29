/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signupkw_attributes` (
  `f_name_show` tinyint(4) NOT NULL DEFAULT 0,
  `f_name_req` tinyint(4) NOT NULL DEFAULT 0,
  `f_name_cstm` varchar(255) NOT NULL DEFAULT '',
  `l_name_show` tinyint(4) NOT NULL DEFAULT 0,
  `l_name_req` tinyint(4) NOT NULL DEFAULT 0,
  `l_name_cstm` varchar(255) NOT NULL DEFAULT '',
  `dob_show` tinyint(4) NOT NULL DEFAULT 0,
  `dob_req` tinyint(4) NOT NULL DEFAULT 0,
  `dob_cstm` date DEFAULT NULL,
  `gender_show` tinyint(4) NOT NULL DEFAULT 0,
  `gender_req` tinyint(4) NOT NULL DEFAULT 0,
  `gender_cstm` varchar(10) NOT NULL DEFAULT '',
  `mob_show` tinyint(4) NOT NULL DEFAULT 0,
  `mob_req` tinyint(4) NOT NULL DEFAULT 0,
  `mob_cstm` varchar(255) NOT NULL DEFAULT '',
  `fax_show` tinyint(4) NOT NULL DEFAULT 0,
  `fax_req` tinyint(4) NOT NULL DEFAULT 0,
  `fax_cstm` varchar(255) NOT NULL DEFAULT '',
  `company_show` tinyint(4) NOT NULL DEFAULT 0,
  `company_req` tinyint(4) NOT NULL DEFAULT 0,
  `company_cstm` varchar(255) NOT NULL DEFAULT '',
  `companyId_show` tinyint(4) NOT NULL DEFAULT 0,
  `companyId_req` tinyint(4) NOT NULL DEFAULT 0,
  `companyId_cstm` varchar(255) NOT NULL DEFAULT '',
  `address1_show` tinyint(4) NOT NULL DEFAULT 0,
  `address1_req` tinyint(4) NOT NULL DEFAULT 0,
  `address1_cstm` varchar(255) NOT NULL DEFAULT '',
  `address2_show` tinyint(4) NOT NULL DEFAULT 0,
  `address2_req` tinyint(4) NOT NULL DEFAULT 0,
  `address2_cstm` varchar(255) NOT NULL DEFAULT '',
  `city_show` tinyint(4) NOT NULL DEFAULT 0,
  `city_req` tinyint(4) NOT NULL DEFAULT 0,
  `city_cstm` varchar(255) NOT NULL DEFAULT '',
  `pin_show` tinyint(4) NOT NULL DEFAULT 0,
  `pin_req` tinyint(4) NOT NULL DEFAULT 0,
  `pin_cstm` varchar(255) NOT NULL DEFAULT '',
  `state_show` tinyint(4) NOT NULL DEFAULT 0,
  `state_req` tinyint(4) NOT NULL DEFAULT 0,
  `state_cstm` varchar(255) NOT NULL DEFAULT '',
  `country_show` tinyint(4) NOT NULL DEFAULT 0,
  `country_req` tinyint(4) NOT NULL DEFAULT 0,
  `country_cstm` varchar(255) NOT NULL DEFAULT '',
  `passconf_show` tinyint(4) NOT NULL DEFAULT 0,
  `passconf_req` tinyint(4) NOT NULL DEFAULT 0,
  `passconf_cstm` varchar(255) NOT NULL DEFAULT '',
  `subsribe_show` tinyint(4) NOT NULL DEFAULT 0,
  `subsribe_req` tinyint(4) NOT NULL DEFAULT 0,
  `subsribe_cstm` varchar(255) NOT NULL DEFAULT '',
  `mob_min` int(11) NOT NULL DEFAULT 0,
  `mob_max` int(11) NOT NULL DEFAULT 0,
  `mob_fix` int(11) NOT NULL DEFAULT 0,
  `pass_min` int(11) NOT NULL DEFAULT 0,
  `pass_max` int(11) NOT NULL DEFAULT 0,
  `pass_fix` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `signupkw_attributes` VALUES (0, 0, '', 0, 0, '', 0, 0, NULL, 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, '', 0, 0, 0, 0, 0, 0);
