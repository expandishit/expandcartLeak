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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(4, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 0),
(5, '', 1, 0, NULL, NULL, NULL, '', '17', '4', 'url', 0, 1, 'menu', 0, 1, 0, 8, 0, 'index.php?route=pavblog/blogs', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 0),
(7, '', 1, 0, NULL, NULL, NULL, 'col1=3, col2=3, col3=6', '73', '3', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(8, '', 2, 1, NULL, NULL, NULL, '', '27', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '&lt;p&gt;test&lt;/p&gt;\r\n', 0, 0, 0, 0),
(9, '', 2, 1, NULL, NULL, NULL, '', '26', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
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
(24, '', 2, 0, NULL, NULL, NULL, '', '', '1', 'html', 1, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '&lt;div class=&quot;pav-menu-video&quot;&gt;&lt;iframe allowfullscreen=&quot;&quot; frameborder=&quot;0&quot; height=&quot;157&quot; src=&quot;http://www.youtube.com/embed/NBuLeA7nNFk&quot; width=&quot;279&quot;&gt;&lt;/iframe&gt;\r\n&lt;h3&gt;Lorem ipsum dolor sit&lt;/h3&gt;\r\n\r\n&lt;p&gt;Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', 0, 0, 0, 0),
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
(37, '', 1, 0, NULL, NULL, NULL, '', '74', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(38, '', 1, 0, NULL, NULL, NULL, '', '75', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(40, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0, 0),
(41, '', 1, 0, NULL, NULL, NULL, '', '64', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(42, '', 1, 0, NULL, NULL, NULL, '', '59', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(43, '', 7, 1, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(44, '', 43, 0, NULL, NULL, NULL, '', '67', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(45, '', 43, 0, NULL, NULL, NULL, '', '68', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(46, '', 43, 0, NULL, NULL, NULL, '', '70', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(47, '', 43, 0, NULL, NULL, NULL, '', '72', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(48, '', 43, 0, NULL, NULL, NULL, '', '75', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(49, '', 43, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(50, '', 7, 1, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(51, '', 50, 0, NULL, NULL, NULL, '', '71', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(52, '', 50, 0, NULL, NULL, NULL, '', '73', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(53, '', 50, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(54, '', 50, 0, NULL, NULL, NULL, '', '63', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(55, '', 50, 0, NULL, NULL, NULL, '', '64', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(56, '', 50, 0, NULL, NULL, NULL, '', '75', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(57, '', 37, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(58, '', 37, 0, NULL, NULL, NULL, '', '65', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(59, '', 37, 0, NULL, NULL, NULL, '', '66', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(60, '', 37, 0, NULL, NULL, NULL, '', '67', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(61, '', 37, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(62, '', 37, 0, NULL, NULL, NULL, '', '70', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(63, '', 37, 0, NULL, NULL, NULL, '', '65', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(64, '', 7, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 0, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '&lt;div class=&quot;pav-menu-video&quot;&gt;&lt;iframe allowfullscreen=&quot;&quot; frameborder=&quot;0&quot; height=&quot;200&quot; src=&quot;//www.youtube.com/embed/7XINZM6kMN0&quot; width=&quot;330&quot;&gt;&lt;/iframe&gt;&lt;/div&gt;\r\n', '', 0, 0, 0, 0),
(65, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 99, 0, 'index.php?route=product/special', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(67, '', 66, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 99, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1);

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
(41, 2, 'Outwear   ', ''),
(4, 2, 'Watches', ''),
(4, 1, 'Watches', ''),
(5, 1, 'Blog  ', ''),
(7, 1, 'Accessories ', ''),
(8, 1, 'Computers', ''),
(9, 1, 'Printer', ''),
(8, 2, 'Computers', ''),
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
(9, 2, 'Mobiles', ''),
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
(24, 2, 'Lorem ipsum dolor sit ', ''),
(24, 1, 'Lorem ipsum dolor sit ', ''),
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
(38, 2, 'Casual Shirts     ', ''),
(40, 2, 'Home', ''),
(40, 1, 'Home', ''),
(37, 1, ' Pajamas &amp; Robes  ', ''),
(42, 2, 'Jeans&amp;Pants', ''),
(43, 1, 'Women', ''),
(44, 1, 'Dresses', ''),
(45, 1, 'Day', ''),
(46, 1, 'Evening', ''),
(47, 1, 'Sundresses', ''),
(48, 1, 'Sweater', ''),
(49, 1, 'Blouses and Shirts', ''),
(50, 1, 'Men', ''),
(51, 1, 'Curabitur ', ''),
(52, 1, 'Aenean', ''),
(53, 1, ' Vestibulum', ''),
(54, 1, 'Sed mattis', ''),
(55, 1, 'Nulla mauris', ''),
(56, 1, 'Curabitur sagittis', ''),
(57, 2, 'Kids', ''),
(58, 1, 'Duis turpis ', ''),
(59, 1, 'Praesent erat ', ''),
(60, 2, 'Maecenas ', ''),
(61, 1, 'Quisque mollis', ''),
(62, 1, 'Nulla vitae', ''),
(63, 1, 'Donec sed', ''),
(64, 1, 'Curabitur semper adipiscing', ''),
(38, 1, 'Casual Shirts     ', ''),
(42, 1, 'Jeans&amp;Pants', ''),
(5, 2, 'Blog  ', ''),
(65, 2, 'Special', ''),
(65, 1, 'Special', ''),
(41, 1, 'Outwear   ', ''),
(44, 2, 'Dresses', ''),
(45, 2, 'Day', ''),
(49, 2, 'Blouses and Shirts', ''),
(47, 2, 'Sundresses', ''),
(46, 2, 'Evening', ''),
(51, 2, 'Curabitur ', ''),
(53, 2, ' Vestibulum', ''),
(57, 1, 'Kids', ''),
(7, 2, 'Accessories ', ''),
(37, 2, ' Pajamas &amp; Robes  ', ''),
(48, 2, 'Sweater', ''),
(52, 2, 'Aenean', ''),
(54, 2, 'Sed mattis', ''),
(55, 2, 'Nulla mauris', ''),
(56, 2, 'Curabitur sagittis', ''),
(58, 2, 'Duis turpis ', ''),
(59, 2, 'Praesent erat ', ''),
(60, 1, 'Maecenas ', ''),
(61, 2, 'Quisque mollis', ''),
(62, 2, 'Nulla vitae', ''),
(63, 2, 'Donec sed', ''),
(67, 1, 'Test 4', ''),
(67, 2, 'Test 4', '');

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
(3, 'Products Latest', 'product_list', 'a:4:{s:9:"list_type";s:6:"newest";s:5:"limit";s:1:"4";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `pavoslidergroups`
--

INSERT INTO `pavoslidergroups` (`id`, `title`, `params`) VALUES
(3, 'Slideshow', 'a:28:{s:5:"title";s:9:"Slideshow";s:5:"delay";s:4:"9000";s:9:"fullwidth";s:9:"fullwidth";s:5:"width";s:4:"1170";s:6:"height";s:3:"409";s:12:"touch_mobile";s:1:"1";s:13:"stop_on_hover";s:1:"0";s:12:"shuffle_mode";s:1:"0";s:14:"image_cropping";s:1:"0";s:11:"shadow_type";s:1:"0";s:14:"show_time_line";s:1:"0";s:18:"time_line_position";s:3:"top";s:16:"background_color";s:7:"#b8bcca";s:6:"margin";s:1:"0";s:7:"padding";s:1:"0";s:16:"background_image";s:1:"0";s:14:"background_url";s:0:"";s:14:"navigator_type";s:4:"none";s:16:"navigator_arrows";s:16:"verticalcentered";s:16:"navigation_style";s:5:"round";s:17:"offset_horizontal";s:1:"0";s:15:"offset_vertical";s:1:"0";s:14:"show_navigator";s:1:"0";s:20:"hide_navigator_after";s:3:"200";s:15:"thumbnail_width";s:3:"100";s:16:"thumbnail_height";s:2:"50";s:16:"thumbnail_amount";s:1:"5";s:17:"hide_screen_width";s:0:"";}');

-- --------------------------------------------------------

--
-- Table structure for table `pavosliderlayers`
--

CREATE TABLE IF NOT EXISTS `pavosliderlayers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `params` text NOT NULL,
  `layersparams` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `pavosliderlayers`
--

INSERT INTO `pavosliderlayers` (`id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`) VALUES
(10, 'image slideshow3', 0, 3, 'a:16:{s:2:"id";s:2:"10";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:16:"image slideshow3";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"10";s:12:"slider_image";s:23:"data/slider/slider3.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:4:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"large_black_text";s:13:"layer_caption";s:14:"Turpis egestas";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeInQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"103";s:10:"layer_left";s:2:"57";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:50:"Pellentesque habitant morbi tristique senectus et ";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:10:"easeInCirc";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"163";s:10:"layer_left";s:2:"60";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:202:"Praesent accumsan porttitor bibendum. Fusce vehicula neque id suscipit iaculis.&lt;br&gt; Cras rhoncus lacinia nulla, sed rhoncus tortor ultrices pharetra.&lt;br&gt; Mauris mollis leo et varius aliquam.";s:15:"layer_animation";s:3:"sfl";s:12:"layer_easing";s:11:"easeOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"203";s:10:"layer_left";s:2:"62";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1200";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"bg_orange";s:13:"layer_caption";s:94:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=78&quot;&gt;Show Now&lt;/a&gt;";s:15:"layer_animation";s:4:"fade";s:12:"layer_easing";s:14:"easeInOutQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"308";s:10:"layer_left";s:2:"67";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}}}', 'data/slider/slider3.jpg', 1, 3),
(9, 'image slideshow2', 0, 3, 'a:16:{s:2:"id";s:1:"9";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:16:"image slideshow2";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"9";s:12:"slider_image";s:23:"data/slider/slider2.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:4:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:22:"data/slider/slide4.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:15:"very_large_text";s:13:"layer_caption";s:11:"Morbi vitae";s:15:"layer_animation";s:3:"sft";s:12:"layer_easing";s:12:"easeOutQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"94";s:10:"layer_left";s:2:"71";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1085";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:22:"data/slider/slide4.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"brown_text";s:13:"layer_caption";s:46:"Phasellus non nisi in eros pellentesque tempus";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:12:"easeOutCubic";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"153";s:10:"layer_left";s:2:"73";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1750";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:22:"data/slider/slide4.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:166:"Donec mauris mauris, elementum eget gravida at, tempor eu magna. Nam nisl nisi&lt;br&gt; pellentesque non odio at, mollis interdum eros. Nam euismod molestie faucibus";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:10:"easeInCirc";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"194";s:10:"layer_left";s:2:"74";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2230";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:22:"data/slider/slide4.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"bg_orange";s:13:"layer_caption";s:94:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=78&quot;&gt;Show Now&lt;/a&gt;";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeInQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"272";s:10:"layer_left";s:2:"78";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2669";}}}', 'data/slider/slider2.jpg', 1, 2),
(8, 'image slideshow4', 0, 3, 'a:16:{s:2:"id";s:1:"8";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:16:"image slideshow4";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"8";s:12:"slider_image";s:23:"data/slider/slider4.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:4:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"large_black_text";s:13:"layer_caption";s:13:"Your egestas ";s:15:"layer_animation";s:3:"sft";s:12:"layer_easing";s:11:"easeInQuart";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"90";s:10:"layer_left";s:3:"637";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"769";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:40:"Phasellus tristique enim diam in pulvina";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:12:"easeOutCubic";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"149";s:10:"layer_left";s:3:"640";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1496";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:160:"Maecenas sed eleifend ipsum. Ut porta dapibus elementum. Phasellus fringilla&lt;br&gt; eleifend varius curabitur quis tortor nunc. Aenean pharetra lobortis eros";s:15:"layer_animation";s:3:"sfr";s:12:"layer_easing";s:11:"easeInCubic";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"196";s:10:"layer_left";s:3:"644";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2346";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"bg_orange";s:13:"layer_caption";s:94:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=78&quot;&gt;Show Now&lt;/a&gt;";s:15:"layer_animation";s:4:"fade";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"270";s:10:"layer_left";s:3:"650";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"3134";}}}', 'data/slider/slider4.jpg', 1, 4),
(15, 'image slideshow1', 0, 3, 'a:16:{s:2:"id";s:2:"15";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:16:"image slideshow1";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"15";s:12:"slider_image";s:23:"data/slider/slider1.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:4:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"large_black_text";s:13:"layer_caption";s:7:"NEW MEN";s:15:"layer_animation";s:3:"sft";s:12:"layer_easing";s:11:"easeInQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"80";s:10:"layer_left";s:2:"74";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1126";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:35:"Try to buy it now with 20% discount";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeInCubic";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"136";s:10:"layer_left";s:2:"77";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1832";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:216:"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla mi eros&lt;br&gt; facilisis ac mollis et, ullamcorper non neque.Cum sociis natoque&lt;br&gt; penatibus et magni dis parturient montes Praesent facilisis ";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeInQuart";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"181";s:10:"layer_left";s:2:"81";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2497";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"bg_orange";s:13:"layer_caption";s:94:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=78&quot;&gt;Show Now&lt;/a&gt;";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:10:"easeInExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"290";s:10:"layer_left";s:2:"84";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2846";}}}', 'data/slider/slider1.jpg', 1, 1),
(16, 'image slideshow5', 0, 3, 'a:16:{s:2:"id";s:2:"16";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:16:"image slideshow5";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"16";s:12:"slider_image";s:23:"data/slider/slider5.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:4:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"large_black_text";s:13:"layer_caption";s:12:"Nam ultrices";s:15:"layer_animation";s:3:"sfr";s:12:"layer_easing";s:13:"easeInOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"99";s:10:"layer_left";s:2:"78";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1139";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"brown_text";s:13:"layer_caption";s:44:"Pellentesque sit amet nunc vitae diam mollis";s:15:"layer_animation";s:3:"sft";s:12:"layer_easing";s:12:"easeOutQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"160";s:10:"layer_left";s:2:"80";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1696";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:230:"Proin est velit, pulvinar ut dignissim ut, luctus lobortis purus vestibulum eget dui &lt;br&gt; Pellentesque tincidunt turpis eget elit imperdiet Sed tincidunt vel sem non&lt;br&gt; ultricies vestibulum eget dui dignissim bibendum";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:14:"easeInOutQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"208";s:10:"layer_left";s:2:"81";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2366";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"bg_orange";s:13:"layer_caption";s:94:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=78&quot;&gt;Show Now&lt;/a&gt;";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"318";s:10:"layer_left";s:2:"86";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2743";}}}', 'data/slider/slider5.jpg', 1, 5);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3720 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'featured', 'featured_product', '62,58,56,71', 0),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'carousel', 'carousel_module', 'a:2:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"65";s:6:"height";s:2:"50";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:1;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"65";s:6:"height";s:2:"50";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}}', 1),
(0, 'banner', 'banner_module', 'a:7:{i:0;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"295";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:1;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"295";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:2;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"295";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"5";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:3;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"295";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"6";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:4;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"295";s:6:"height";s:3:"420";s:9:"layout_id";s:2:"10";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:5;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"295";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"7";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:6;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"295";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}}', 1),
(0, 'config', 'config_image_cart_height', '86', 0),
(0, 'config', 'config_image_cart_width', '60', 0),
(0, 'config', 'config_image_wishlist_height', '86', 0),
(0, 'config', 'config_image_wishlist_width', '60', 0),
(0, 'config', 'config_image_compare_height', '185', 0),
(0, 'config', 'config_image_compare_width', '130', 0),
(0, 'config', 'config_image_related_height', '570', 0),
(0, 'config', 'config_image_related_width', '400', 0),
(0, 'config', 'config_image_additional_height', '128', 0),
(0, 'config', 'config_image_additional_width', '90', 0),
(0, 'config', 'config_image_product_height', '570', 0),
(0, 'config', 'config_image_product_width', '400', 0),
(0, 'config', 'config_image_popup_height', '570', 0),
(0, 'config', 'config_image_popup_width', '400', 0),
(0, 'config', 'config_image_thumb_height', '570', 0),
(0, 'config', 'config_image_thumb_width', '400', 0),
(0, 'config', 'config_image_category_height', '280', 0),
(0, 'config', 'config_image_category_width', '870', 0),
(0, 'pavproducttabs', 'pavproducttabs_module', 'a:1:{i:1;a:11:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";a:3:{i:0;s:6:"latest";i:1;s:10:"bestseller";i:2;s:10:"mostviewed";}s:5:"width";s:3:"400";s:6:"height";s:3:"570";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:1:"8";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'featured', 'product', '', 0),
(0, 'pavsliderlayer', 'pavsliderlayer_module', 'a:1:{i:0;a:5:{s:8:"group_id";s:1:"3";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavproductcarousel', 'pavproductcarousel_module', 'a:1:{i:1;a:14:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:6:"prefix";s:0:"";s:4:"tabs";a:1:{i:0;s:10:"mostviewed";}s:5:"width";s:3:"400";s:6:"height";s:3:"570";s:12:"itemsperpage";s:1:"3";s:4:"cols";s:1:"3";s:5:"limit";s:1:"8";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'information', 'information_module', 'a:2:{i:0;a:4:{s:9:"layout_id";s:1:"8";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:1;a:4:{s:9:"layout_id";s:1:"5";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'category', 'category_module', 'a:9:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:3;a:4:{s:9:"layout_id";s:1:"8";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:4;a:4:{s:9:"layout_id";s:1:"9";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:5;a:4:{s:9:"layout_id";s:2:"11";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:6;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:7;a:4:{s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:8;a:4:{s:9:"layout_id";s:1:"5";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:0:"";}}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"6";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:0:"";}}', 1),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:2:{i:1;a:10:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:3:"370";s:6:"height";s:3:"171";s:4:"cols";s:1:"3";s:5:"limit";s:1:"3";s:9:"layout_id";s:5:"99999";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}i:2;a:10:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";s:10:"mostviewed";s:5:"width";s:2:"80";s:6:"height";s:2:"37";s:4:"cols";s:1:"1";s:5:"limit";s:1:"4";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"870";s:15:"general_lheight";s:3:"400";s:14:"general_swidth";s:3:"300";s:15:"general_sheight";s:3:"138";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"37";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"1";s:14:"general_cwidth";s:3:"300";s:15:"general_cheight";s:3:"138";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"4";s:22:"cat_leading_image_type";s:1:"s";s:24:"cat_secondary_image_type";s:1:"s";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:12:"100858303516";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'pavblog_frontmodules', 'pavblog_frontmodules', 'a:1:{s:7:"modules";a:3:{i:0;s:15:"pavblogcategory";i:1;s:14:"pavblogcomment";i:2;s:13:"pavbloglatest";}}', 1),
(0, 'special', 'special_module', 'a:3:{i:0;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"90";s:12:"image_height";s:3:"128";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:1;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"90";s:12:"image_height";s:3:"128";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:2;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"90";s:12:"image_height";s:3:"128";s:9:"layout_id";s:1:"6";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'latest', 'latest_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"90";s:12:"image_height";s:3:"128";s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'pavfacebook', 'pavfacebook_module', 'a:1:{i:1;a:13:{s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:10:"sort_order";i:5;s:8:"page_url";s:35:"https://www.facebook.com/expandcart";s:14:"application_id";s:0:"";s:12:"border_color";s:1:"0";s:11:"colorscheme";s:4:"dark";s:5:"width";s:0:"";s:6:"height";s:0:"";s:5:"tream";s:1:"0";s:10:"show_faces";s:1:"1";s:6:"header";s:1:"0";}}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":1,"subwidth":740,"cols":3,"group":0,"id":7,"rows":[{"cols":[{"colwidth":3,"type":"menu"},{"colwidth":3,"type":"menu"},{"colwidth":6,"type":"menu"}]}]},{"submenu":1,"cols":1,"group":1,"id":43,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":50,"rows":[]},{"submenu":1,"cols":1,"group":0,"id":37,"rows":[{"cols":[{"colwidth":12,"type":"menu"}]}]},{"submenu":"0","subwidth":600,"id":42,"cols":1,"group":0,"rows":[]}]', 0),
(0, 'pavcustom', 'pavcustom_module', 'a:9:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:7:"Welcome";}s:11:"description";a:2:{i:1;s:697:"&lt;div class=&quot;webcome&quot;&gt;\r\n&lt;div class=&quot;image&quot;&gt;&lt;img src=&quot;image/data/demo/product.png&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;web_block&quot;&gt;\r\n&lt;h1&gt;Welcome to Dress Store OpenCart Theme Demo Store&lt;/h1&gt;\r\n\r\n&lt;h2&gt;Perfect shopping cart solution for everyone!&lt;/h2&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla mi eros, facilisis ac mollis et, ullamcorper non neque. Cum sociis natoque penatibus magnis dis parturient montes, nascetur ridiculus mus. Sed ac pretium dui. Praesent arcu dui, convallis eget facilisis in, vulputate ut lacus. ultricies laoreet malesuada.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:697:"&lt;div class=&quot;webcome&quot;&gt;\r\n&lt;div class=&quot;image&quot;&gt;&lt;img src=&quot;image/data/demo/product.png&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;web_block&quot;&gt;\r\n&lt;h1&gt;Welcome to Dress Store OpenCart Theme Demo Store&lt;/h1&gt;\r\n\r\n&lt;h2&gt;Perfect shopping cart solution for everyone!&lt;/h2&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla mi eros, facilisis ac mollis et, ullamcorper non neque. Cum sociis natoque penatibus magnis dis parturient montes, nascetur ridiculus mus. Sed ac pretium dui. Praesent arcu dui, convallis eget facilisis in, vulputate ut lacus. ultricies laoreet malesuada.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:12:"module_class";s:15:"pav_dress_store";s:10:"sort_order";s:1:"1";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:14:"Adv home-right";}s:11:"description";a:2:{i:1;s:115:"&lt;div class=&quot;adv-home&quot;&gt;&lt;img src=&quot;image/data/demo/advhome-right.png&quot; /&gt;&lt;/div&gt;\r\n";i:2;s:115:"&lt;div class=&quot;adv-home&quot;&gt;&lt;img src=&quot;image/data/demo/advhome-right.png&quot; /&gt;&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:3;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:13:"Adv - Shopnow";}s:11:"description";a:2:{i:1;s:267:"&lt;div class=&quot;highlight-image&quot;&gt;&lt;img src=&quot;image/data/demo/shop.png&quot; /&gt;\r\n&lt;h3 class=&quot;highlight-viewall&quot;&gt;&lt;a href=&quot;index.php?route=product&quot;&gt;&lt;span&gt;shop now&lt;/span&gt;&lt;/a&gt;&lt;/h3&gt;\r\n&lt;/div&gt;\r\n";i:2;s:267:"&lt;div class=&quot;highlight-image&quot;&gt;&lt;img src=&quot;image/data/demo/shop.png&quot; /&gt;\r\n&lt;h3 class=&quot;highlight-viewall&quot;&gt;&lt;a href=&quot;index.php?route=product&quot;&gt;&lt;span&gt;shop now&lt;/span&gt;&lt;/a&gt;&lt;/h3&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}i:4;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:13:"Adv home-left";}s:11:"description";a:2:{i:1;s:115:"&lt;div class=&quot;home-left&quot;&gt;&lt;img src=&quot;image/data/demo/advhome-left.png&quot; /&gt;&lt;/div&gt;\r\n";i:2;s:115:"&lt;div class=&quot;home-left&quot;&gt;&lt;img src=&quot;image/data/demo/advhome-left.png&quot; /&gt;&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:5;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:8:"About us";}s:11:"description";a:2:{i:1;s:1387:"&lt;div class=&quot;about-us&quot;&gt;\r\n&lt;h3&gt;About us&lt;/h3&gt;\r\n\r\n&lt;div class=&quot;image&quot;&gt;&lt;img src=&quot;image/data/demo/img-about.png&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet consectetur adipiscing nulla facilisis interdum venenatis nullam vulputate cursus nisi quis volutpat curabitur id mauris a ante volutpat varius.&lt;br /&gt;\r\nLorem ipsum dolor sit amet consectetur adipiscing volutpat varius. Morbi iaculis nisl non arcu suscipit tempor id pretium blandit.&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;social&quot;&gt;\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;pinterest&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;fa fa-pinterest stack&quot;&gt;&lt;span&gt;Printerest&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;google&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;fa fa-google-plus stack&quot;&gt;&lt;span&gt;Google Plus&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;facebook&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;fa fa-facebook stack&quot;&gt;&lt;span&gt;Facebook&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;twitter&quot;&gt;&lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;&lt;span class=&quot;fa fa-twitter stack&quot;&gt;&lt;span&gt;Twitter&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1387:"&lt;div class=&quot;about-us&quot;&gt;\r\n&lt;h3&gt;About us&lt;/h3&gt;\r\n\r\n&lt;div class=&quot;image&quot;&gt;&lt;img src=&quot;image/data/demo/img-about.png&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet consectetur adipiscing nulla facilisis interdum venenatis nullam vulputate cursus nisi quis volutpat curabitur id mauris a ante volutpat varius.&lt;br /&gt;\r\nLorem ipsum dolor sit amet consectetur adipiscing volutpat varius. Morbi iaculis nisl non arcu suscipit tempor id pretium blandit.&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;social&quot;&gt;\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;pinterest&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;fa fa-pinterest stack&quot;&gt;&lt;span&gt;Printerest&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;google&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;fa fa-google-plus stack&quot;&gt;&lt;span&gt;Google Plus&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;facebook&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;fa fa-facebook stack&quot;&gt;&lt;span&gt;Facebook&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;twitter&quot;&gt;&lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;&lt;span class=&quot;fa fa-twitter stack&quot;&gt;&lt;span&gt;Twitter&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:0:"";}i:6;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:11:"Information";}s:11:"description";a:2:{i:1;s:1256:"&lt;div class=&quot;box&quot;&gt;\r\n&lt;h3&gt;Information&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;About Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Delivery Information&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Terms &amp;amp; Conditions&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;Cum sociis&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Phasellus lacinia&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Donec massa&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Vivamus convallis&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1256:"&lt;div class=&quot;box&quot;&gt;\r\n&lt;h3&gt;Information&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;About Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Delivery Information&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Terms &amp;amp; Conditions&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;Cum sociis&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Phasellus lacinia&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Donec massa&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Vivamus convallis&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:7;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:6:"Extras";}s:11:"description";a:2:{i:1;s:960:"&lt;div class=&quot;box&quot;&gt;\r\n&lt;h3&gt;Extras-Account&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;Brands&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Gift Vouchers&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=affiliate/account&quot;&gt;Affiliates&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;Specials&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;My Account&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/order&quot;&gt;Order History&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;Wish List&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;Newsletter&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:960:"&lt;div class=&quot;box&quot;&gt;\r\n&lt;h3&gt;Extras-Account&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;Brands&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Gift Vouchers&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=affiliate/account&quot;&gt;Affiliates&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;Specials&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;My Account&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/order&quot;&gt;Order History&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;Wish List&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;Newsletter&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";i:4;}i:8;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:1462:"&lt;div class=&quot;pav-static&quot;&gt;\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;span3&quot;&gt;\r\n	&lt;p class=&quot;box-1&quot;&gt;&lt;span class=&quot;pv-icon pv-icon-feature&quot;&gt;Fearture&lt;/span&gt;&lt;/p&gt;\r\n\r\n	&lt;div class=&quot;static-text&quot;&gt;\r\n	&lt;h3 class=&quot;title-block&quot;&gt;Fearture&lt;/h3&gt;\r\n\r\n	&lt;p&gt;Lorem ipsum dolor sit amet&lt;/p&gt;\r\n	&lt;/div&gt;\r\n	&lt;/li&gt;\r\n	&lt;li class=&quot;span3&quot;&gt;\r\n	&lt;p class=&quot;box-2&quot;&gt;&lt;span class=&quot;pv-icon pv-con-support&quot;&gt;Support&lt;/span&gt;&lt;/p&gt;\r\n\r\n	&lt;div class=&quot;static-text&quot;&gt;\r\n	&lt;h3 class=&quot;title-block&quot;&gt;Support&lt;/h3&gt;\r\n\r\n	&lt;p&gt;Lorem ipsum dolor sit amet&lt;/p&gt;\r\n	&lt;/div&gt;\r\n	&lt;/li&gt;\r\n	&lt;li class=&quot;span3&quot;&gt;\r\n	&lt;p class=&quot;box-3&quot;&gt;&lt;span class=&quot;pv-icon pv-con-shipping&quot;&gt;Shipping&lt;/span&gt;&lt;/p&gt;\r\n\r\n	&lt;div class=&quot;static-text&quot;&gt;\r\n	&lt;h3 class=&quot;title-block&quot;&gt;Free shipping&lt;/h3&gt;\r\n\r\n	&lt;p&gt;Lorem ipsum dolor sit amet&lt;/p&gt;\r\n	&lt;/div&gt;\r\n	&lt;/li&gt;\r\n	&lt;li class=&quot;span3&quot;&gt;\r\n	&lt;p class=&quot;box-4&quot;&gt;&lt;span class=&quot;pv-icon pv-con-mauris&quot;&gt;Mauris&lt;/span&gt;&lt;/p&gt;\r\n\r\n	&lt;div class=&quot;static-text&quot;&gt;\r\n	&lt;h3 class=&quot;title-block&quot;&gt;Mauris nisi&lt;/h3&gt;\r\n\r\n	&lt;p&gt;Lorem ipsum dolor sit amet&lt;/p&gt;\r\n	&lt;/div&gt;\r\n	&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1462:"&lt;div class=&quot;pav-static&quot;&gt;\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;span3&quot;&gt;\r\n	&lt;p class=&quot;box-1&quot;&gt;&lt;span class=&quot;pv-icon pv-icon-feature&quot;&gt;Fearture&lt;/span&gt;&lt;/p&gt;\r\n\r\n	&lt;div class=&quot;static-text&quot;&gt;\r\n	&lt;h3 class=&quot;title-block&quot;&gt;Fearture&lt;/h3&gt;\r\n\r\n	&lt;p&gt;Lorem ipsum dolor sit amet&lt;/p&gt;\r\n	&lt;/div&gt;\r\n	&lt;/li&gt;\r\n	&lt;li class=&quot;span3&quot;&gt;\r\n	&lt;p class=&quot;box-2&quot;&gt;&lt;span class=&quot;pv-icon pv-con-support&quot;&gt;Support&lt;/span&gt;&lt;/p&gt;\r\n\r\n	&lt;div class=&quot;static-text&quot;&gt;\r\n	&lt;h3 class=&quot;title-block&quot;&gt;Support&lt;/h3&gt;\r\n\r\n	&lt;p&gt;Lorem ipsum dolor sit amet&lt;/p&gt;\r\n	&lt;/div&gt;\r\n	&lt;/li&gt;\r\n	&lt;li class=&quot;span3&quot;&gt;\r\n	&lt;p class=&quot;box-3&quot;&gt;&lt;span class=&quot;pv-icon pv-con-shipping&quot;&gt;Shipping&lt;/span&gt;&lt;/p&gt;\r\n\r\n	&lt;div class=&quot;static-text&quot;&gt;\r\n	&lt;h3 class=&quot;title-block&quot;&gt;Free shipping&lt;/h3&gt;\r\n\r\n	&lt;p&gt;Lorem ipsum dolor sit amet&lt;/p&gt;\r\n	&lt;/div&gt;\r\n	&lt;/li&gt;\r\n	&lt;li class=&quot;span3&quot;&gt;\r\n	&lt;p class=&quot;box-4&quot;&gt;&lt;span class=&quot;pv-icon pv-con-mauris&quot;&gt;Mauris&lt;/span&gt;&lt;/p&gt;\r\n\r\n	&lt;div class=&quot;static-text&quot;&gt;\r\n	&lt;h3 class=&quot;title-block&quot;&gt;Mauris nisi&lt;/h3&gt;\r\n\r\n	&lt;p&gt;Lorem ipsum dolor sit amet&lt;/p&gt;\r\n	&lt;/div&gt;\r\n	&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"promotion";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:0:"";}i:9;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:16:"Customer Service";}s:11:"description";a:2:{i:1;s:990:"&lt;div class=&quot;box&quot;&gt;\r\n&lt;h3&gt;Customer Service&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;Contact Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;Returns&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;Site Map&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Gift Vouchers&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;Suspendisse&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;Quisque lacinia&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;Cras libero&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Proin laoreet&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:990:"&lt;div class=&quot;box&quot;&gt;\r\n&lt;h3&gt;Customer Service&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;Contact Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;Returns&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;Site Map&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Gift Vouchers&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;Suspendisse&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;Quisque lacinia&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;Cras libero&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Proin laoreet&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:57:{s:13:"default_theme";s:15:"pav_dress_store";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:20:"cateogry_product_row";s:1:"0";s:14:"category_pzoom";s:1:"1";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:14:"Hanoi, Vietnam";s:17:"location_latitude";s:10:"21.0333333";s:18:"location_longitude";s:18:"105.85000000000002";s:18:"contact_customhtml";a:2:{i:1;s:691:"&lt;div class=&quot;contact-customhtml&quot;&gt;\r\n            &lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-tasks&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Customer Service&lt;/strong&gt;&lt;br&gt;\r\n	info@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-share&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Returns and Refunds:&lt;/strong&gt;&lt;br&gt;\r\n	returns@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n        &lt;/div&gt;";i:2;s:691:"&lt;div class=&quot;contact-customhtml&quot;&gt;\r\n            &lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-tasks&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Customer Service&lt;/strong&gt;&lt;br&gt;\r\n	info@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-share&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Returns and Refunds:&lt;/strong&gt;&lt;br&gt;\r\n	returns@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n        &lt;/div&gt;";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"1";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:17:"widget_about_data";a:2:{i:1;s:1096:"&lt;div class=&quot;about-us&quot;&gt;\r\n&lt;p&gt;Lorem ipsum dolor sit amet consectetur adipiscing nulla facilisis interdum venenatis nullam vulputate cursus nisi quis volutpat curabitur id mauris a ante volutpat varius.&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;social&quot;&gt;\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;pinterest&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;icon-pinterest stack&quot;&gt;&lt;span&gt;Printerest&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;google&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;icon-google-plus stack&quot;&gt;&lt;span&gt;Google Plus&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;facebook&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;icon-facebook stack&quot;&gt;&lt;span&gt;Facebook&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;twitter&quot;&gt;&lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;&lt;span class=&quot;icon-twitter stack&quot;&gt;&lt;span&gt;Twitter&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1096:"&lt;div class=&quot;about-us&quot;&gt;\r\n&lt;p&gt;Lorem ipsum dolor sit amet consectetur adipiscing nulla facilisis interdum venenatis nullam vulputate cursus nisi quis volutpat curabitur id mauris a ante volutpat varius.&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;social&quot;&gt;\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;pinterest&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;icon-pinterest stack&quot;&gt;&lt;span&gt;Printerest&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;google&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;icon-google-plus stack&quot;&gt;&lt;span&gt;Google Plus&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;facebook&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;span class=&quot;icon-facebook stack&quot;&gt;&lt;span&gt;Facebook&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;twitter&quot;&gt;&lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;&lt;span class=&quot;icon-twitter stack&quot;&gt;&lt;span&gt;Twitter&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:8:"standard";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=448 ;

--
-- Dumping data for table `extension`
--

INSERT INTO `extension` (`type`, `code`) VALUES
('module', 'banner'),
('module', 'carousel'),
('module', 'category'),
('module', 'affiliate'),
('module', 'account'),
('module', 'featured'),
--('module', 'pavmegamenu'),
('module', 'pavcustom'),
('module', 'themecontrol'),
('module', 'pavproductcarousel'),
('module', 'pavsliderlayer'),
('module', 'pavblog'),
('module', 'pavblogcategory'),
('module', 'pavblogcomment'),
('module', 'pavbloglatest'),
('module', 'pavfacebook'),
('module', 'pavproducttabs'),
('module', 'special'),
('module', 'information'),
('module', 'latest'),
('module', 'bestseller');