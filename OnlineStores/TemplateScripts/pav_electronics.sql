--
-- Table structure for table `megamenu`
--

CREATE TABLE IF NOT EXISTS `megamenu` (
  `megamenu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `is_group` smallint(6) NOT NULL DEFAULT '2',
  `width` varchar(255) DEFAULT NULL,
  `submenu_width` varchar(255) DEFAULT NULL,
  `colum_width` varchar(255) DEFAULT NULL,
  `submenu_colum_width` varchar(255) DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `colums` varchar(255) DEFAULT '1',
  `type` varchar(255) NOT NULL,
  `is_content` smallint(6) NOT NULL DEFAULT '2',
  `show_title` smallint(6) NOT NULL DEFAULT '1',
  `type_submenu` varchar(10) NOT NULL DEFAULT '1',
  `level_depth` smallint(6) NOT NULL DEFAULT '0',
  `published` smallint(6) NOT NULL DEFAULT '1',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `position` int(11) unsigned NOT NULL DEFAULT '0',
  `show_sub` smallint(6) NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT NULL,
  `target` varchar(25) DEFAULT NULL,
  `privacy` smallint(5) unsigned NOT NULL DEFAULT '0',
  `position_type` varchar(25) DEFAULT 'top',
  `menu_class` varchar(25) DEFAULT NULL,
  `description` text,
  `content_text` text,
  `submenu_content` text,
  `level` int(11) NOT NULL,
  `left` int(11) NOT NULL,
  `right` int(11) NOT NULL,
  `widget_id` int(11) DEFAULT '0',
  PRIMARY KEY (`megamenu_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(2, '', 1, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(4, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 0),
(10, '', 8, 0, NULL, NULL, NULL, '', '59', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(11, '', 8, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(12, '', 8, 0, NULL, NULL, NULL, '', '61', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(13, '', 8, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(14, '', 8, 0, NULL, NULL, NULL, '', '63', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(15, '', 8, 0, NULL, NULL, NULL, '', '64', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(16, '', 8, 0, NULL, NULL, NULL, '', '65', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(17, '', 9, 0, NULL, NULL, NULL, '', '66', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(18, '', 9, 0, NULL, NULL, NULL, '', '67', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(19, '', 9, 0, NULL, NULL, NULL, '', '68', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(20, '', 9, 0, NULL, NULL, NULL, '', '71', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(21, '', 9, 0, NULL, NULL, NULL, '', '72', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(22, '', 9, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(23, '', 9, 0, NULL, NULL, NULL, '', '70', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(25, '', 3, 0, NULL, NULL, NULL, '', '79', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(26, '', 3, 0, NULL, NULL, NULL, '', '74', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(27, '', 3, 0, NULL, NULL, NULL, '', '73', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(28, '', 3, 0, NULL, NULL, NULL, '', '80', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(29, '', 3, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(30, '', 3, 0, NULL, NULL, NULL, '', '46', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(31, '', 3, 0, NULL, NULL, NULL, '', '75', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(32, '', 3, 0, NULL, NULL, NULL, '', '78', '1', 'category', 0, 1, 'menu', 0, 1, 0, 9, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(33, '', 3, 0, NULL, NULL, NULL, '', '77', '1', 'category', 0, 1, 'menu', 0, 1, 0, 10, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(34, '', 3, 0, NULL, NULL, NULL, '', '77', '1', 'category', 0, 1, 'menu', 0, 1, 0, 11, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(35, '', 3, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 12, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(36, '', 3, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 13, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(40, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0, 0),
(41, '', 1, 0, NULL, NULL, NULL, 'col1=2, col2=2, col3=2, col4=2, col5=4', '70', '5', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(42, '', 41, 1, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 99, 0, '#', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Dresses&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Day&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Evening&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Sundresses&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Sweater&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Belts&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Blouses and Shirts&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Dresses&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Dresses&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Dresses&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Dresses&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Dresses&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Dresses&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Dresses&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', 0, 0, 0, 0),
(43, '', 41, 1, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 99, 0, '#', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Cocktai&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Hair Accessories&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Hats and Gloves&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Lifestyle&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Bras&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Scarves&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Small Leathers&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', 0, 0, 0, 0),
(44, '', 41, 1, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 99, 0, '#', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Evening&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Long Sleeved&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Short Sleeved&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Sleeveless&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Tanks and Camis&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Clutches&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Cross Body&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', 0, 0, 0, 0),
(45, '', 41, 1, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 99, 0, '#', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Satchels&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Shoulder&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Briefs&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Handbags&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Camis&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Nightwear&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Shapewear&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', 0, 0, 0, 0),
(46, '', 41, 1, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 99, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(47, '', 46, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 0, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '&lt;div class=&quot;margin&quot;&gt;\r\n&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-category.jpg&quot; style=&quot;width: 219px; height:101px;&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Lorem sagittis velit mollis eros adipiscing massa nulla ut augue.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', 0, 0, 0, 0),
(48, '', 1, 0, NULL, NULL, NULL, '', '76', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(49, '', 1, 0, NULL, NULL, NULL, '', '71', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(50, '', 1, 0, NULL, NULL, NULL, '', '72', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(51, '', 1, 0, NULL, NULL, NULL, '', '73', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(52, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 0, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(53, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 99, 0, 'index.php?route=pavblog/blogs', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(54, '', 42, 0, NULL, NULL, NULL, '', '74', '1', 'category', 0, 0, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(55, '', 43, 0, NULL, NULL, NULL, '', '75', '1', 'category', 0, 0, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `megamenu_description`
--

CREATE TABLE IF NOT EXISTS `megamenu_description` (
  `megamenu_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`megamenu_id`,`language_id`),
  KEY `name` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `megamenu_description`
--

INSERT INTO `megamenu_description` (`megamenu_id`, `language_id`, `title`, `description`) VALUES
(2, 1, 'Mobile Phones', ''),
(50, 1, 'Home appliances', ''),
(4, 2, 'Watches', ''),
(4, 1, 'Watches', ''),
(49, 1, 'Cameras &amp; Camcorders', ''),
(47, 1, 'Perspiciatis  sub', ''),
(45, 1, 'Satchels', ''),
(10, 2, 'Duis tempor', ''),
(10, 1, 'Duis tempor', ''),
(11, 2, 'Pellentesque eget', ''),
(11, 1, 'Pellentesque eget ', ''),
(12, 2, 'Nam nunc ante', ''),
(12, 1, 'Nam nunc ante', ''),
(13, 2, 'Condimentum eu', ''),
(13, 1, 'Condimentum eu', ''),
(14, 2, 'Lehicula lorem', ''),
(14, 1, 'Lehicula lorem', ''),
(15, 2, 'Integer semper', ''),
(15, 1, 'Integer semper', ''),
(16, 2, 'Sollicitudin lacus', ''),
(16, 1, 'Sollicitudin lacus', ''),
(46, 1, 'Perspiciatis', ''),
(17, 2, 'Nam ipsum ', ''),
(17, 1, 'Nam ipsum ', ''),
(18, 2, 'Curabitur turpis ', ''),
(18, 1, 'Curabitur turpis ', ''),
(19, 1, 'Molestie eu mattis ', ''),
(19, 2, 'Molestie eu mattis ', ''),
(20, 1, 'Suspendisse eu ', ''),
(20, 2, 'Suspendisse eu ', ''),
(21, 1, 'Nunc imperdiet ', ''),
(21, 2, 'Nunc imperdiet ', ''),
(22, 1, 'Mauris mattis', ''),
(22, 2, 'Mauris mattis', ''),
(23, 1, 'Lacus sed iaculis ', ''),
(23, 2, 'Lacus sed iaculis ', ''),
(43, 1, 'Cocktai', ''),
(44, 1, 'Evening', ''),
(25, 1, 'Aliquam', ''),
(25, 2, 'Aliquam', ''),
(26, 1, 'Claritas', ''),
(26, 2, 'Claritas', ''),
(27, 2, 'Consectetuer', ''),
(27, 1, 'Consectetuer', ''),
(28, 1, 'Hendrerit', ''),
(28, 2, 'Hendrerit', ''),
(29, 1, 'Litterarum', ''),
(29, 2, 'Litterarum', ''),
(30, 1, 'Macs', ''),
(30, 2, 'Macs', ''),
(31, 1, 'Sollemnes', ''),
(31, 2, 'Sollemnes', ''),
(32, 1, 'Tempor', ''),
(32, 2, 'Tempor', ''),
(33, 1, 'Vulputate', ''),
(33, 2, 'Vulputate', ''),
(34, 1, 'Vulputate', ''),
(34, 2, 'Vulputate', ''),
(35, 1, 'Windows', ''),
(35, 2, 'Windows', ''),
(36, 1, 'Windows', ''),
(36, 2, 'Windows', ''),
(48, 1, 'Audio/Video', ''),
(40, 2, 'Home', ''),
(40, 1, 'Home', ''),
(41, 1, 'Televisions', ''),
(42, 3, 'Dresses', ''),
(51, 1, 'Download manuals', ''),
(52, 1, 'Faqs', ''),
(53, 1, 'Blog', ''),
(42, 2, 'Dresses', ''),
(54, 1, 'Dresses1', ''),
(54, 2, 'Dresses1', ''),
(55, 1, 'Cocktai1', ''),
(55, 2, 'Cocktai1', ''),
(55, 3, 'Cocktai1', ''),
(41, 2, 'Televisions', ''),
(41, 3, 'Televisions', ''),
(49, 2, 'Cameras &amp; Camcorders', ''),
(49, 3, 'Cameras &amp; Camcorders', ''),
(50, 2, 'Home appliances', ''),
(50, 3, 'Home appliances', ''),
(51, 2, 'Download manuals', ''),
(51, 3, 'Download manuals', ''),
(42, 1, 'Dresses', ''),
(43, 2, 'Cocktai', ''),
(43, 3, 'Cocktai', ''),
(44, 2, 'Evening', ''),
(44, 3, 'Evening', ''),
(45, 2, 'Satchels', ''),
(45, 3, 'Satchels', ''),
(48, 2, 'Audio/Video', ''),
(48, 3, 'Audio/Video', ''),
(2, 2, 'Mobile Phones', ''),
(2, 3, 'Mobile Phones', '');

-- --------------------------------------------------------

--
-- Table structure for table `megamenu_widgets`
--

CREATE TABLE IF NOT EXISTS `megamenu_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `type` varchar(255) NOT NULL,
  `params` text NOT NULL,
  `store_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `megamenu_widgets`
--

INSERT INTO `megamenu_widgets` (`id`, `name`, `type`, `params`, `store_id`) VALUES
(1, 'Video Opencart Installation', 'video_code', 'a:1:{s:10:"video_code";s:168:"&lt;iframe width=&quot;300&quot; height=&quot;315&quot; src=&quot;//www.youtube.com/embed/M1USNjKKRWk&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;";}', 0),
(2, 'Demo HTML Sample', 'html', 'a:1:{s:4:"html";a:1:{i:1;s:275:"Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel. Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.";}}', 0),
(3, 'Products Latest', 'product_list', 'a:4:{s:9:"list_type";s:6:"newest";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(4, 'Products In Cat 20', 'product_category', 'a:4:{s:11:"category_id";s:2:"20";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(5, 'Manufactures', 'banner', 'a:4:{s:8:"group_id";s:1:"8";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:5:"limit";s:2:"12";}', 0),
(6, 'expandcart Feed', 'feed', 'a:1:{s:8:"feed_url";s:55:"http://www.expandcart.com/expandcartfeeds.feed?type=rss";}', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pavoslidergroups`
--

CREATE TABLE IF NOT EXISTS `pavoslidergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `pavoslidergroups`
--

INSERT INTO `pavoslidergroups` (`id`, `title`, `params`) VALUES
(1, 'Test title', 'a:27:{s:5:"title";s:10:"Test title";s:5:"delay";s:4:"8000";s:5:"width";s:3:"675";s:6:"height";s:3:"292";s:12:"touch_mobile";s:1:"1";s:13:"stop_on_hover";s:1:"1";s:12:"shuffle_mode";s:1:"0";s:14:"image_cropping";s:1:"0";s:11:"shadow_type";s:1:"0";s:14:"show_time_line";s:1:"1";s:18:"time_line_position";s:3:"top";s:16:"background_color";s:7:"#ffffff";s:6:"margin";s:6:"20px 0";s:7:"padding";s:7:"5px 5px";s:16:"background_image";s:1:"0";s:14:"background_url";s:0:"";s:14:"navigator_type";s:6:"bullet";s:16:"navigator_arrows";s:4:"none";s:16:"navigation_style";s:5:"round";s:17:"offset_horizontal";s:1:"0";s:15:"offset_vertical";s:2:"20";s:14:"show_navigator";s:1:"1";s:20:"hide_navigator_after";s:3:"200";s:15:"thumbnail_width";s:3:"100";s:16:"thumbnail_height";s:2:"50";s:16:"thumbnail_amount";s:1:"5";s:17:"hide_screen_width";s:0:"";}');

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

CREATE TABLE IF NOT EXISTS `setting` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL DEFAULT '0',
  `group` varchar(32) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` text NOT NULL,
  `serialized` tinyint(1) NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4190 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
--(0, 'carousel', 'carousel_module', 'a:3:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:4;}i:1;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:2:"99";}i:2;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"8";}}', 1),
(0, 'pavcustom', 'pavcustom_module', 'a:9:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:379:"&lt;div class=&quot;row-fluid&quot;&gt;\r\n&lt;div class=&quot;span6&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/banner-1.jpg&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span6&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/banner-2.jpg&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;";i:2;s:379:"&lt;div class=&quot;row-fluid&quot;&gt;\r\n&lt;div class=&quot;span6&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/banner-1.jpg&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span6&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/banner-2.jpg&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:1003:"&lt;div class=&quot;social pull-left&quot;&gt;\r\n&lt;h4 class=&quot;pull-left&quot;&gt;Follow us on&lt;/h4&gt;\r\n\r\n&lt;div class=&quot;custom_follow&quot;&gt;&lt;a class=&quot;in&quot; href=&quot;#&quot;&gt;in&lt;/a&gt;&lt;a class=&quot;facebook&quot; href=&quot;#&quot;&gt;facebook&lt;/a&gt; &lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;twitter&lt;/a&gt; &lt;a class=&quot;google&quot; href=&quot;#&quot;&gt;google&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;listmenu navbar pull-right&quot;&gt;\r\n&lt;ul class=&quot;nav&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;freeshipping&quot; href=&quot;#&quot;&gt;Free shipping&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;moneyback&quot; href=&quot;#&quot;&gt;Money Back&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;exchange&quot; href=&quot;#&quot;&gt;Exchange In Store &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;securepayment&quot; href=&quot;#&quot;&gt;Secured Payment&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1067:"&lt;div class=&quot;social pull-left&quot;&gt;\r\n&lt;h4 class=&quot;pull-left&quot;&gt;كن على إتصال&lt;/h4&gt;\r\n\r\n&lt;div class=&quot;custom_follow&quot;&gt;&lt;a class=&quot;in&quot; href=&quot;#&quot;&gt;in&lt;/a&gt;&lt;a class=&quot;facebook&quot; href=&quot;#&quot;&gt;facebook&lt;/a&gt; &lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;twitter&lt;/a&gt; &lt;a class=&quot;google&quot; href=&quot;#&quot;&gt;google&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;listmenu navbar pull-right&quot;&gt;\r\n&lt;ul class=&quot;nav&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;freeshipping&quot; href=&quot;#&quot;&gt;شحن مجاني&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;moneyback&quot; href=&quot;#&quot;&gt;إمكانية الإسترجاع&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;exchange&quot; href=&quot;#&quot;&gt;الإستبدال من المتجر&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;securepayment&quot; href=&quot;#&quot;&gt;طرق دفع مؤمنة&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:3;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";i:2;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:4;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";i:2;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:2:"88";}i:5;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";i:2;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"6";}i:6;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";i:2;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"6";}i:7;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";i:2;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"6";}i:8;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";i:2;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"6";}i:9;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";i:2;s:97:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/demo/side-banner.jpg&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"4";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"6";}}', 1),
(0, 'featured', 'featured_product', '34,68,64,78,58', 0),
(0, 'featured', 'product', 's', 0),
(0, 'featured', 'featured_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"185";s:12:"image_height";s:3:"165";s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:4;}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:65:{s:13:"default_theme";s:15:"pav_electronics";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"0";s:30:"listing_products_columns_small";s:1:"2";s:34:"listing_products_columns_minismall";s:1:"1";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"0";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:14:"Hanoi, Vietnam";s:17:"location_latitude";s:10:"21.0333333";s:18:"location_longitude";s:18:"105.85000000000002";s:18:"contact_customhtml";a:2:{i:1;s:677:"&lt;div class=&quot;contact-customhtml&quot;&gt;\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-tasks&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Customer Service&lt;/strong&gt;&lt;br /&gt;\r\n	info@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-share&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Returns and Refunds:&lt;/strong&gt;&lt;br /&gt;\r\n	returns@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:896:"&lt;div class=&quot;contact-customhtml&quot;&gt;\r\n&lt;p&gt;جُل عل أراض غينيا, بها لكون واستمر بـ. عن كلّ وقرى البشريةً. الثقيلة بالإنزال في هذا, أم دول الستار واتّجه. قبل و معقل مكثّفة, هو ضرب بقعة إتفاقية. تم مليون وسمّيت كان, سياسة ليبين التغييرات عن الا.&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-tasks&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;خدمة العملاء&lt;/strong&gt;&lt;br /&gt;\r\n	info@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-share&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;الإستبدال و الإرتجاع:&lt;/strong&gt;&lt;br /&gt;\r\n	returns@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:23:"enable_development_mode";s:0:"";s:19:"widget_contact_data";a:2:{i:1;s:243:"&lt;ul&gt;\r\n	&lt;li class=&quot;fone first&quot;&gt;Phone:+123 456 789&lt;/li&gt;\r\n	&lt;li class=&quot;mail&quot;&gt;Email:leothem@gmail.com&lt;/li&gt;\r\n	&lt;li class=&quot;address last&quot;&gt;88 Do Duc Duc - Ha Noi&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:268:"&lt;ul&gt;\r\n	&lt;li class=&quot;fone first&quot;&gt;الهاتف :+123 456 789&lt;/li&gt;\r\n	&lt;li class=&quot;mail&quot;&gt;البريد :leothem@gmail.com&lt;/li&gt;\r\n	&lt;li class=&quot;address last&quot;&gt;88 شارع الملك فيصل&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:8:"standard";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:19:"enable_compress_css";s:0:"";s:17:"exclude_css_files";s:13:"bootstrap.css";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"6";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'config', 'config_image_cart_height', '45', 0),
(0, 'config', 'config_image_cart_width', '47', 0),
(0, 'config', 'config_image_wishlist_height', '45', 0),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:3:{i:1;a:10:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:3:"700";s:6:"height";s:3:"314";s:4:"cols";s:1:"3";s:5:"limit";s:1:"3";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}i:2;a:10:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";s:8:"featured";s:5:"width";s:2:"70";s:6:"height";s:2:"70";s:4:"cols";s:1:"3";s:5:"limit";s:1:"3";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:3;a:10:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";s:10:"mostviewed";s:5:"width";s:2:"70";s:6:"height";s:2:"70";s:4:"cols";s:1:"3";s:5:"limit";s:1:"3";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"4";}}', 1),
(0, 'config', 'config_image_wishlist_width', '47', 0),
(0, 'config', 'config_image_compare_height', '86', 0),
(0, 'config', 'config_image_compare_width', '90', 0),
(0, 'config', 'config_image_related_height', '176', 0),
(0, 'config', 'config_image_related_width', '185', 0),
(0, 'config', 'config_image_additional_height', '74', 0),
(0, 'config', 'config_image_additional_width', '74', 0),
(0, 'config', 'config_image_product_height', '195', 0),
(0, 'config', 'config_image_product_width', '205', 0),
(0, 'config', 'config_image_popup_height', '476', 0),
(0, 'config', 'config_image_popup_width', '500', 0),
(0, 'config', 'config_image_thumb_height', '476', 0),
(0, 'config', 'config_image_thumb_width', '500', 0),
(0, 'config', 'config_image_category_height', '235', 0),
(0, 'config', 'config_image_category_width', '825', 0),
(0, 'bestseller', 'bestseller_module', 'a:3:{i:0;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"185";s:12:"image_height";s:3:"165";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"6";}i:1;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"185";s:12:"image_height";s:3:"165";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:2;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"185";s:12:"image_height";s:3:"165";s:9:"layout_id";s:1:"4";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"4";}}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":1,"id":41,"subwidth":800,"cols":5,"group":0,"rows":[{"cols":[{"type":"menu"},{"type":"menu"},{"type":"menu"},{"type":"menu"},{"type":"menu"}]}]},{"submenu":1,"cols":1,"group":1,"id":46,"rows":[]}]', 0),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}}', 1),
(0, 'pavcontentslider', 'pavcontentslider_module', 'a:1:{i:0;a:12:{s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"0";s:13:"text_interval";s:4:"8000";s:5:"width";s:3:"852";s:6:"height";s:3:"366";s:15:"image_navigator";s:1:"0";s:13:"navimg_weight";s:3:"177";s:13:"navimg_height";s:2:"97";s:12:"banner_image";a:4:{i:1;a:4:{s:5:"image";s:25:"data/demo/slideshow-1.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:843:"&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideRight normal-caption&quot; style=&quot;top:283px;left:553px&quot;&gt;&lt;input class=&quot;button&quot; type=&quot;button&quot; value=&quot;SHOP NOW!&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideLeft  normal-caption&quot; style=&quot;top:200px;left:490px&quot;&gt;Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare erat consequat auctor eu in per inceptos himenaeos.&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone expandOpen big-caption&quot; style=&quot;top:158px;left:490px&quot;&gt;SALE OFF 50%&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideDown title-big-caption&quot; style=&quot;top:56px;left:490px&quot;&gt;Samsung Galaxy Tab 3&lt;/div&gt;\r\n";i:2;s:940:"&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideRight normal-caption&quot; style=&quot;top:283px;left:553px&quot;&gt;&lt;input class=&quot;button&quot; type=&quot;button&quot; value=&quot;إشتري الأن&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideLeft  dis-big-caption&quot; style=&quot;top:200px;left:490px&quot;&gt;مئات وقرى واعتلاء كل أخر. اليها وسمّيت ما دول, عدد مع دارت والنفيس اقتصادية. ضرب ما شدّت الأرواح. كل ببعض فهرست.&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone expandOpen big-caption&quot; style=&quot;top:158px;left:490px&quot;&gt;تخفيضات تصل ل 50%&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideDown title-big-caption&quot; style=&quot;top:56px;left:490px&quot;&gt;Samsung Galaxy Tab 3&lt;/div&gt;";}}i:2;a:4:{s:5:"image";s:25:"data/demo/slideshow-2.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:782:"&lt;div class=&quot;pav-caption hidden-tablet hidden-phone fadeIn normal-caption&quot; style=&quot;top:244px;left:165px&quot;&gt;&lt;input class=&quot;button&quot; type=&quot;button&quot; value=&quot;SHOP NOW!&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideRight dis-big-caption&quot; style=&quot;top:171px;left:54px&quot;&gt;Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare erat&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone expandOpen  big-caption&quot; style=&quot;top:133px;left:54px&quot;&gt;SALE OFF 50%&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideDown title-big-caption&quot; style=&quot;top:84px;left:54px&quot;&gt;HTC 8X 2013&lt;/div&gt;\r\n";i:2;s:927:"&lt;div class=&quot;pav-caption hidden-tablet hidden-phone fadeIn normal-caption&quot; style=&quot;top:244px;left:165px&quot;&gt;&lt;input class=&quot;button&quot; type=&quot;button&quot; value=&quot;إشتري الأن&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideRight dis-big-caption&quot; style=&quot;top:171px;left:54px&quot;&gt;مئات وقرى واعتلاء كل أخر. اليها وسمّيت ما دول, عدد مع دارت والنفيس اقتصادية. ضرب ما شدّت الأرواح. كل ببعض فهرست.&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone expandOpen  big-caption&quot; style=&quot;top:133px;left:54px&quot;&gt;تخفيضات تصل ل 50%&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideDown title-big-caption&quot; style=&quot;top:84px;left:54px&quot;&gt;HTC 8X 2013&lt;/div&gt;\r\n";}}i:3;a:4:{s:5:"image";s:25:"data/demo/slideshow-3.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:311:"&lt;div class=&quot;pav-caption hidden-tablet hidden-phone floating big-caption&quot; style=&quot;top:105px;left:190px&quot;&gt;SALE OFF 30%&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideUp title-big-caption&quot; style=&quot;top:45px;left:190px&quot;&gt;I Phone 6&lt;/div&gt;\r\n";i:2;s:327:"&lt;div class=&quot;pav-caption hidden-tablet hidden-phone floating big-caption&quot; style=&quot;top:142px;left:129px&quot;&gt;تخفيضات تصل ل 30%&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideUp title-big-caption&quot; style=&quot;top:45px;left:190px&quot;&gt;I Phone 6&lt;/div&gt;\r\n";}}i:4;a:4:{s:5:"image";s:25:"data/demo/slideshow-4.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:318:"&lt;div class=&quot;pav-caption hidden-tablet hidden-phone fadeIn big-caption&quot; style=&quot;top:169px;left:510px&quot;&gt;SALE OFF 30%&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideUp title-big-caption&quot; style=&quot;top:68px;left:510px&quot;&gt;Apple IMac Rentina&lt;/div&gt;\r\n";i:2;s:334:"&lt;div class=&quot;pav-caption hidden-tablet hidden-phone fadeIn big-caption&quot; style=&quot;top:169px;left:472px&quot;&gt;تخفيضات تصل ل 30%&lt;/div&gt;\r\n\r\n&lt;div class=&quot;pav-caption hidden-tablet hidden-phone slideUp title-big-caption&quot; style=&quot;top:39px;left:409px&quot;&gt;Apple IMac Rentina&lt;/div&gt;\r\n";}}}}}', 1),
(0, 'banner', 'banner_module', 'a:1:{i:0;a:7:{s:9:"banner_id";s:1:"6";s:5:"width";s:3:"182";s:6:"height";s:3:"182";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"0";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavproductcarousel', 'pavproductcarousel_module', 'a:2:{i:1;a:13:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";a:1:{i:0;s:7:"special";}s:5:"width";s:3:"185";s:6:"height";s:3:"165";s:12:"itemsperpage";s:1:"3";s:4:"cols";s:1:"3";s:5:"limit";s:1:"6";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"9";}i:2;a:13:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";a:1:{i:0;s:6:"latest";}s:5:"width";s:3:"202";s:6:"height";s:3:"168";s:12:"itemsperpage";s:1:"6";s:4:"cols";s:1:"3";s:5:"limit";s:2:"12";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'pavblog_frontmodules', 'pavblog_frontmodules', 'a:1:{s:7:"modules";a:3:{i:0;s:15:"pavblogcategory";i:1;s:14:"pavblogcomment";i:2;s:13:"pavbloglatest";}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"700";s:15:"general_lheight";s:3:"314";s:14:"general_swidth";s:3:"300";s:15:"general_sheight";s:3:"135";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"36";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"1";s:14:"general_cwidth";s:3:"250";s:15:"general_cheight";s:3:"112";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"6";s:22:"cat_leading_image_type";s:1:"s";s:24:"cat_secondary_image_type";s:1:"s";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"s";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:2:"10";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'category', 'category_module', 'a:5:{i:0;a:4:{s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:3;}i:1;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:3;a:4:{s:9:"layout_id";s:1:"4";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:4;a:4:{s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=449 ;

--
-- Dumping data for table `extension`
--

INSERT INTO `extension` (`type`, `code`) VALUES
('module', 'banner'),
--('module', 'carousel'),
('module', 'category'),
('module', 'affiliate'),
('module', 'account'),
('module', 'themecontrol'),
('module', 'pavcontentslider'),
('module', 'pavblog'),
('module', 'pavcustom'),
--('module', 'pavmegamenu'),
('module', 'pavproductcarousel'),
('module', 'pavblogcategory'),
('module', 'pavblogcomment'),
('module', 'pavbloglatest'),
('module', 'special'),
('module', 'information'),
('module', 'latest'),
('module', 'bestseller'),
('module', 'featured');