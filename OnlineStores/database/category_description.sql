/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_description` (
  `category_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`category_id`,`language_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `category_description` (`category_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`, `slug`) VALUES
(77, 1, 'Electronics', '', '', '', 'electronics'),
(77, 2, 'أجهزة إلكترونية', '', '', '', 'أجهزة-إلكترونية'),
(78, 1, 'Apple', '', '', '', ''),
(78, 2, 'ابل', '', '', '', ''),
(79, 1, 'Samsung', '', '', '', ''),
(79, 2, 'سامسونج', '', '', '', ''),
(83, 1, 'Fashion', '', '', '', ''),
(83, 2, 'موضه', '', '', '', ''),
(84, 1, 'Dresses', '', '', '', ''),
(84, 2, 'فساتين', '', '', '', ''),
(87, 1, 'Jackets', '', '', '', ''),
(87, 2, 'جواكت', '', '', '', ''),
(89, 1, 'Home &amp; Kitchen', '', '', '', 'home-kitchen'),
(89, 2, 'المنزل', '', '', '', 'المنزل'),
(91, 1, 'Living Rooms', '', '', '', ''),
(91, 2, 'غرف المعيشة', '', '', '', ''),
(92, 1, 'Kitchen', '', '', '', ''),
(92, 2, 'ادوات المطبخ', '', '', '', ''),
(94, 1, 'TVs', '', '', '', 'tvs'),
(94, 2, 'تلفزيونات', '', '', '', 'تلفزيونات');