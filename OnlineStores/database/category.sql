/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `top` tinyint(1) NOT NULL,
  `column` int(3) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  KEY `category_parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `category` (`category_id`, `image`, `icon`, `parent_id`, `top`, `column`, `sort_order`, `status`, `date_added`, `date_modified`) VALUES
(77, 'data/DemoImages/Categories/mobilesandtablets.png', '', 0, 0, 0, 0, 1, '2017-10-22 13:46:54', '2021-01-21 11:49:35'),
(78, 'data/DemoImages/Categories/apple.jpg', NULL, 77, 1, 1, 0, 1, '2017-10-22 14:09:39', '2017-10-28 17:46:30'),
(79, 'data/DemoImages/Categories/samsung.jpg', NULL, 77, 1, 1, 0, 1, '2017-10-22 14:10:49', '2017-10-28 17:46:49'),
(83, 'data/DemoImages/Categories/fashion.jpg', NULL, 0, 1, 1, 0, 1, '2017-10-22 14:15:30', '2017-10-28 17:47:53'),
(84, 'data/DemoImages/Categories/dresses.png', NULL, 83, 1, 1, 0, 1, '2017-10-22 14:16:49', '2017-10-28 17:49:04'),
(87, 'data/DemoImages/Categories/jackets.png', NULL, 83, 1, 1, 0, 1, '2017-10-22 14:19:58', '2017-10-28 17:48:30'),
(89, 'data/DemoImages/Categories/home.png', '', 0, 0, 0, 0, 1, '2017-10-22 14:22:08', '2021-01-21 11:56:57'),
(91, 'data/DemoImages/Categories/livingroom.png', NULL, 89, 1, 1, 0, 1, '2017-10-22 14:24:48', '2017-10-28 17:44:02'),
(92, 'data/DemoImages/Categories/kitchen.png', NULL, 89, 1, 1, 0, 1, '2017-10-22 14:25:37', '2017-10-28 17:43:15'),
(94, 'data/DemoImages/Categories/tvs.png', '', 77, 0, 0, 0, 1, '2017-10-22 14:27:07', '2021-01-21 11:53:19');