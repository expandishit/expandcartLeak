
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `type` varchar(100) NOT NULL,
  `supported_countries` varchar(500) DEFAULT NULL,
  `featured_in` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `registered_business` tinyint(1) DEFAULT '0',
  `special_rate` tinyint(1) DEFAULT '0',
  `price` decimal(15,4) DEFAULT NULL,
  `published` tinyint(1) DEFAULT '1',
  `is_external` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `payment_methods_description`
--

DROP TABLE IF EXISTS `payment_methods_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods_description` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `short_description` text,
  `image_alt` varchar(255) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `lang` varchar(6) NOT NULL DEFAULT 'en',
  `contact_info` text,
  `individual_requirements` varchar(255) DEFAULT NULL,
  `company_requirements` varchar(255) DEFAULT NULL,
  `account_creation_steps` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`,`language_id`),
  KEY `payment_method_description_payment_method_id` (`payment_method_id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;



--
-- Table structure for table `shipping_methods`
--

DROP TABLE IF EXISTS `shipping_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `type` varchar(100) NOT NULL,
  `supported_countries` varchar(500) DEFAULT NULL,
  `featured_in` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `registered_business` int(11) DEFAULT '0',
  `special_rate` int(11) DEFAULT '0',
  `price` decimal(15,4) DEFAULT NULL,
  `published` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `shipping_methods_description`
--

DROP TABLE IF EXISTS `shipping_methods_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipping_methods_description` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `short_description` text,
  `image_alt` varchar(255) NOT NULL,
  `shipping_method_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `lang` varchar(6) NOT NULL DEFAULT 'en',
  `contact_info` text,
  `individual_requirements` varchar(255) DEFAULT NULL,
  `company_requirements` varchar(255) DEFAULT NULL,
  `account_creation_steps` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`,`language_id`),
  KEY `shipping_method_description_shipping_method_id` (`shipping_method_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `appservice`
--

DROP TABLE IF EXISTS `appservice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appservice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `description` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `whmcsappserviceid` int(11) DEFAULT NULL,
  `link` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `type` smallint(6) DEFAULT NULL,
  `IsQuantity` smallint(6) DEFAULT '0',
  `IsNew` smallint(6) DEFAULT '0',
  `price` decimal(28,8) DEFAULT NULL,
  `AppImage` varchar(500) DEFAULT NULL,
  `CoverImage` varchar(500) DEFAULT NULL,
  `HomeImage` varchar(500) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `recurring` int(11) DEFAULT NULL,
  `freeplan` varchar(11) DEFAULT NULL,
  `freepaymentterm` varchar(11) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `published` tinyint(1) DEFAULT '1',
  `supported_countries` varchar(500) DEFAULT NULL,
  `recommended` int(11) DEFAULT '0',
  `provider_id` int(10) unsigned NOT NULL DEFAULT '1',
  `response_time` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_provider_id` (`provider_id`),
  CONSTRAINT `fk_provider_id` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `appservicedesc`
--

DROP TABLE IF EXISTS `appservicedesc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appservicedesc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appserviceid` int(11) NOT NULL,
  `Name` varchar(500) CHARACTER SET utf8 NOT NULL,
  `MiniDescription` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `Description` text CHARACTER SET utf8,
  `image` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `lang` varchar(6) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ectemplate`
--

DROP TABLE IF EXISTS `ectemplate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ectemplate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CodeName` varchar(255) NOT NULL,
  `NextGenTemplate` smallint(1) NOT NULL DEFAULT '1',
  `ExpandishTemplate` smallint(1) NOT NULL DEFAULT '0',
  `custom_template` smallint(1) NOT NULL DEFAULT '0',
  `attributes` json DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `repository_url` varchar(255) DEFAULT NULL,
  `theme_version` double(4,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `ectemplatedesc`
--

DROP TABLE IF EXISTS `ectemplatedesc`;
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
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


