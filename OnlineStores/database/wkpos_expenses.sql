/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wkpos_expenses` (
  `wkpos_expense_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL,
  `outlet_id` int(11) NOT NULL,
     `currency_id`	int(11) null,
 `currency_code` varchar(3) null,
     `currency_value` decimal(15,11) null,
  PRIMARY KEY (`wkpos_expense_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
