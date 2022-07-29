/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr_op_val_to_dropna` (
  `product_option_value_id` int(11) NOT NULL,
  `dropna_pr_op_val_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_option_value_id`,`dropna_pr_op_val_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
