/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remember_tokens_customer`(
    `token_id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(11) NOT NULL,
    `selector` CHAR(12) DEFAULT NULL,
    `validator` CHAR(64) DEFAULT NULL,
    `expires` DATETIME DEFAULT CURRENT_TIMESTAMP(), PRIMARY KEY(`token_id`, `customer_id`)) ENGINE = INNODB DEFAULT CHARSET = utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
