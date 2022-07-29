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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=85 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(2, '', 1, 0, NULL, NULL, NULL, 'col1=3, col2=3, col3=6', '25', '3', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(3, '', 1, 0, NULL, NULL, NULL, 'col1=3, col2=3, col3=3,col4=3', '20', '4', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(5, '', 1, 0, NULL, NULL, NULL, '', '33', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'single', NULL, '', '', 0, 0, 0, 0),
(7, '', 1, 0, NULL, NULL, NULL, '', '34', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(8, '', 2, 1, NULL, NULL, NULL, '', '40', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '&lt;p&gt;test&lt;/p&gt;\r\n', 0, 0, 0, 0),
(9, '', 2, 1, NULL, NULL, NULL, '', '43', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(10, '', 8, 0, NULL, NULL, NULL, '', '46', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(11, '', 8, 0, NULL, NULL, NULL, '', '31', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(12, '', 8, 0, NULL, NULL, NULL, '', '66', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(13, '', 8, 0, NULL, NULL, NULL, '', '65', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(14, '', 8, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(15, '', 8, 0, NULL, NULL, NULL, '', '29', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(16, '', 8, 0, NULL, NULL, NULL, '', '70', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(17, '', 9, 0, NULL, NULL, NULL, '', '56', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(18, '', 9, 0, NULL, NULL, NULL, '', '54', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(19, '', 9, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(20, '', 9, 0, NULL, NULL, NULL, '', '68', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(21, '', 9, 0, NULL, NULL, NULL, '', '63', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(22, '', 9, 0, NULL, NULL, NULL, '', '61', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(23, '', 9, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(24, '', 2, 0, NULL, NULL, NULL, '', '', '1', 'html', 1, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '&lt;div class=&quot;pav-menu-video&quot;&gt;\r\n&lt;p&gt;&lt;embed height=&quot;200&quot; src=&quot;http://youtube.com/v/bIxfYHFGX34&quot; type=&quot;application/x-shockwave-flash&quot; width=&quot;400&quot;&gt;&lt;/embed&gt;&lt;/p&gt;\r\n\r\n&lt;h3&gt;Dorem ipsum dolor sit amet consectetur&lt;/h3&gt;\r\n\r\n&lt;p&gt;Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', 0, 0, 0, 0),
(29, '', 56, 0, NULL, NULL, NULL, '', '29', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(31, '', 56, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(32, '', 56, 0, NULL, NULL, NULL, '', '28', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(33, '', 56, 0, NULL, NULL, NULL, '', '32', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(34, '', 56, 0, NULL, NULL, NULL, '', '30', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(35, '', 56, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(36, '', 56, 0, NULL, NULL, NULL, '', '36', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(40, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0, 0),
(41, '', 5, 0, NULL, NULL, NULL, '', '30', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(42, '', 5, 0, NULL, NULL, NULL, '', '47', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(43, '', 5, 0, NULL, NULL, NULL, '', '39', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(44, '', 5, 0, NULL, NULL, NULL, '', '32', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(45, '', 5, 0, NULL, NULL, NULL, '', '48', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(46, '', 5, 0, NULL, NULL, NULL, '', '42', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(48, '', 1, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(49, '', 8, 0, NULL, NULL, NULL, '', '32', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(50, '', 9, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(51, '', 52, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(53, '', 52, 0, NULL, NULL, NULL, '', '35', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(54, '', 52, 0, NULL, NULL, NULL, '', '53', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(55, '', 57, 0, NULL, NULL, NULL, '', '46', '1', 'product', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(56, '', 3, 1, NULL, NULL, NULL, '', '55', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(57, '', 3, 1, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(58, '', 57, 0, NULL, NULL, NULL, '', '35', '1', 'product', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(59, '', 57, 0, NULL, NULL, NULL, '', '43', '1', 'product', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(60, '', 57, 0, NULL, NULL, NULL, '', '51', '1', 'product', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(61, '', 57, 0, NULL, NULL, NULL, '', '28', '1', 'product', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(62, '', 57, 0, NULL, NULL, NULL, '', '42', '1', 'product', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(63, '', 57, 0, NULL, NULL, NULL, '', '45', '1', 'product', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(65, '', 3, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '&lt;p&gt;&lt;img alt=&quot;System Z906&quot; src=&quot;image/data/demo/submenu_01.png&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Assumenda, quos quis labore maxime quasi sed quisquam quibusdam odit minus atque, nec convallis leo erat a nunc&lt;/p&gt;\r\n', '', 0, 0, 0, 0),
(68, '', 67, 0, NULL, NULL, NULL, '', '35', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(69, '', 67, 0, NULL, NULL, NULL, '', '31', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(70, '', 3, 1, NULL, NULL, NULL, '', '70', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(71, '', 70, 0, NULL, NULL, NULL, '', '35', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(72, '', 70, 0, NULL, NULL, NULL, '', '31', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(73, '', 70, 0, NULL, NULL, NULL, '', '26', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(74, '', 70, 0, NULL, NULL, NULL, '', '67', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(75, '', 70, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(76, '', 70, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(77, '', 70, 0, NULL, NULL, NULL, '', '27', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(78, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 7, 0, 'index.php?route=pavblog/blogs', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(79, '', 5, 0, NULL, NULL, NULL, '', '50', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(80, '', 52, 0, NULL, NULL, NULL, '', '58', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(81, '', 52, 0, NULL, NULL, NULL, '', '53', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(82, '', 52, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(83, '', 52, 0, NULL, NULL, NULL, '', '39', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(84, '', 52, 0, NULL, NULL, NULL, '', '50', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0);

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
(11, 1, 'Graphic Novels', ''),
(20, 1, 'Wallets', 'Sale'),
(41, 1, 'Music', ''),
(2, 1, 'Books', ''),
(8, 1, 'Action ', ''),
(8, 7, 'Action &amp; Adventure', ''),
(9, 1, 'Horror', ''),
(16, 7, 'Crafts &amp; Hobbies', ''),
(10, 7, 'Classics', ''),
(10, 1, 'Classics', ''),
(16, 1, 'Crafts &amp; Hobbies', ''),
(15, 1, 'Fantasy', 'New'),
(14, 7, 'Cooking, Food &amp; Wine', ''),
(14, 1, 'Cooking, Food', ''),
(13, 7, 'Erotica', ''),
(13, 1, 'Erotica', ''),
(12, 7, 'Computers &amp; Technology', ''),
(12, 1, 'Computers', ''),
(17, 7, 'Travel', ''),
(17, 1, 'Travel', ''),
(23, 1, 'Bags &amp; business', ''),
(18, 7, 'True Crime', ''),
(18, 1, 'True Crime', ''),
(72, 1, 'Scanners', 'promotion 5%'),
(19, 7, 'Accessories', ''),
(19, 1, 'Accessories', ''),
(23, 7, 'Bags &amp; business', ''),
(21, 7, 'Shoes', ''),
(21, 1, 'Shoes', ''),
(22, 7, 'Watches', 'sale'),
(40, 7, 'Home', ''),
(40, 1, 'Home', ''),
(36, 7, 'Dresses', ''),
(36, 1, 'Dresses', ''),
(35, 7, 'Evening', ''),
(35, 1, 'Evening', ''),
(34, 7, 'Sunglasses', ''),
(34, 1, 'Sunglasses', ''),
(33, 7, 'Web Cameras', ''),
(33, 1, 'Web Cameras', ''),
(32, 7, 'Shoes', ''),
(32, 1, 'Shoes', ''),
(29, 7, 'Handbags', ''),
(29, 1, 'Handbags', ''),
(7, 7, 'Magazines', ''),
(9, 7, 'Horror', ''),
(24, 1, 'Custom HTML', ''),
(41, 7, 'Music', ''),
(79, 1, 'Education', ''),
(42, 7, 'Games', ''),
(42, 1, 'Games', ''),
(43, 7, 'Occult', ''),
(43, 1, 'Occult', ''),
(44, 7, 'Children''s Series', ''),
(44, 1, 'Children''s Series', ''),
(79, 7, 'Education', ''),
(45, 7, 'Anthologies', ''),
(45, 1, 'Anthologies', ''),
(46, 7, 'Study Aids', ''),
(46, 1, 'Study Aids', ''),
(11, 7, 'Graphic Novels', ''),
(31, 7, 'Sollemnes', ''),
(31, 1, 'Sollemnes', ''),
(5, 7, 'Nook Books ', ''),
(7, 1, 'Magazines', ''),
(80, 1, 'Social Science', ''),
(53, 7, 'Evening', ''),
(48, 1, 'Audiobooks', ''),
(48, 7, 'Audiobooks', ''),
(2, 7, 'Books', ''),
(78, 7, 'Blog', ''),
(78, 1, 'Blog', ''),
(49, 1, 'Current Events', ''),
(50, 1, 'Sollemnes', ''),
(50, 7, 'Sollemnes', ''),
(24, 7, 'Custom HTML', ''),
(51, 7, 'Romance', ''),
(51, 1, 'Romance', ''),
(22, 1, 'Watches', ''),
(20, 7, 'Wallets', ''),
(53, 1, 'Evening', ''),
(54, 1, 'Westerns', ''),
(55, 1, 'Sony VAIO', ''),
(55, 7, 'Sony VAIO', ''),
(5, 1, 'Nook Books ', ''),
(80, 7, 'Social Science', ''),
(56, 1, 'Science Fiction', ''),
(57, 1, 'Accessories', ''),
(57, 7, 'Accessories', ''),
(56, 7, 'Science Fiction', ''),
(58, 1, 'Samsung galaxy note 2', ''),
(58, 7, 'Samsung galaxy note 2', ''),
(59, 7, 'MacBook', ''),
(59, 1, 'MacBook', ''),
(60, 1, 'Beatae esse dignissimos', ''),
(60, 7, 'Beatae esse dignissimos', ''),
(15, 7, 'Fantasy', ''),
(61, 7, 'HTC Touch HD', ''),
(61, 1, 'HTC Touch HD', ''),
(62, 7, 'Apple Cinema 30', ''),
(62, 1, 'Apple Cinema 30', ''),
(63, 7, 'MacBook Pro', ''),
(63, 1, 'MacBook Pro', ''),
(65, 1, 'Custom HTML 1', ''),
(65, 7, 'Custom HTML 1', ''),
(3, 7, 'Textbooks', ''),
(70, 7, 'Mobiles', 'New'),
(70, 1, 'Mobiles', ''),
(68, 6, 'Evening', ''),
(68, 4, 'Evening', ''),
(68, 1, 'Evening', ''),
(69, 6, 'Scanners', ''),
(69, 4, 'Scanners', ''),
(69, 1, 'Scanners', ''),
(71, 7, 'Evening', ''),
(71, 1, 'Evening', ''),
(72, 7, 'Scanners', ''),
(73, 1, 'Formal wear', ''),
(73, 7, 'Formal wear', ''),
(74, 7, 'Accessories', ''),
(74, 1, 'Accessories', ''),
(75, 1, 'Sollemnes', ''),
(75, 7, 'Sollemnes', ''),
(76, 1, 'Bags &amp; business', ''),
(76, 7, 'Bags &amp; business', ''),
(77, 1, 'Silver jewelry', ''),
(77, 7, 'Silver jewelry', ''),
(81, 1, 'Westerns', ''),
(49, 7, 'Current Events', ''),
(54, 7, 'Westerns', ''),
(81, 7, 'Westerns', ''),
(82, 1, 'Religious', ''),
(82, 7, 'Religious', ''),
(83, 1, 'Occult', ''),
(83, 7, 'Occult', ''),
(84, 1, 'Education', ''),
(84, 7, 'Education', ''),
(3, 1, 'Textbooks', '');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `megamenu_widgets`
--

INSERT INTO `megamenu_widgets` (`id`, `name`, `type`, `params`, `store_id`) VALUES
(1, 'Video Opencart Installation', 'video_code', 'a:1:{s:10:"video_code";s:168:"&lt;iframe width=&quot;300&quot; height=&quot;315&quot; src=&quot;//www.youtube.com/embed/M1USNjKKRWk&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;";}', 0),
(2, 'Demo HTML Sample', 'html', 'a:1:{s:4:"html";a:1:{i:1;s:275:"Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel. Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.";}}', 0),
(3, 'Products Latest', 'product_list', 'a:4:{s:9:"list_type";s:6:"newest";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(4, 'Products In Cat 20', 'product_category', 'a:4:{s:11:"category_id";s:2:"20";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(5, 'Manufactures', 'banner', 'a:4:{s:8:"group_id";s:1:"8";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:5:"limit";s:2:"12";}', 0),
(6, 'expandcart Feed', 'feed', 'a:1:{s:8:"feed_url";s:55:"http://www.expandcart.com/expandcartfeeds.feed?type=rss";}', 0),
(7, 'Widget Book', 'image', 'a:3:{s:10:"image_path";s:25:"data/demo/widget book.jpg";s:11:"image_width";s:3:"500";s:12:"image_height";s:3:"246";}', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pavoslidergroups`
--

CREATE TABLE IF NOT EXISTS `pavoslidergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pavoslidergroups`
--

INSERT INTO `pavoslidergroups` (`id`, `title`, `params`) VALUES
(3, 'Books Sliders', 'a:28:{s:5:"title";s:13:"Books Sliders";s:5:"delay";s:4:"9000";s:9:"fullwidth";s:9:"fullwidth";s:5:"width";s:3:"940";s:6:"height";s:3:"443";s:12:"touch_mobile";s:1:"1";s:13:"stop_on_hover";s:1:"1";s:12:"shuffle_mode";s:1:"0";s:14:"image_cropping";s:1:"0";s:11:"shadow_type";s:1:"0";s:14:"show_time_line";s:1:"1";s:18:"time_line_position";s:3:"top";s:16:"background_color";s:7:"#604f32";s:6:"margin";s:3:"0px";s:7:"padding";s:3:"0px";s:16:"background_image";s:1:"0";s:14:"background_url";s:0:"";s:14:"navigator_type";s:6:"bullet";s:16:"navigator_arrows";s:4:"none";s:16:"navigation_style";s:5:"round";s:17:"offset_horizontal";s:1:"0";s:15:"offset_vertical";s:2:"20";s:14:"show_navigator";s:1:"1";s:20:"hide_navigator_after";s:3:"200";s:15:"thumbnail_width";s:3:"100";s:16:"thumbnail_height";s:2:"50";s:16:"thumbnail_amount";s:1:"5";s:17:"hide_screen_width";s:0:"";}');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `pavosliderlayers`
--

INSERT INTO `pavosliderlayers` (`id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`) VALUES
(15, 'Banner Book 3', 0, 3, 'a:16:{s:2:"id";s:2:"15";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:13:"Banner Book 3";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"15";s:12:"slider_image";s:23:"data/demo/bg-slider.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:6:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:30:"data/demo/banner-promotion.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 1";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"91";s:10:"layer_left";s:2:"36";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltl";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2045";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:27:"data/demo/banner-book-3.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 2";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"94";s:10:"layer_left";s:3:"266";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltr";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2287";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:27:"data/demo/banner-book-3.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"pav_small";s:13:"layer_caption";s:12:"Donec metus ";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:10:"easeInCirc";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"91";s:10:"layer_left";s:3:"662";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltr";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2659";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:27:"data/demo/banner-book-3.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:7:"pav_big";s:13:"layer_caption";s:5:"OFFER";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeInCubic";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"150";s:10:"layer_left";s:3:"714";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltr";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2747";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:27:"data/demo/banner-book-3.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:8:"pav_desc";s:13:"layer_caption";s:96:"Lorem isum dolor sit amet consectetur dipiscing elituce &lt;br/&gt; non massa sit amet vehicula ";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:10:"easeInQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"236";s:10:"layer_left";s:3:"586";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltr";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}i:5;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:6;s:13:"layer_content";s:27:"data/demo/banner-book-3.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"pav-button-layer";s:13:"layer_caption";s:94:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=52&quot;&gt;Shop now&lt;/a&gt;";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:10:"easeInQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"316";s:10:"layer_left";s:3:"818";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltb";s:15:"layer_endeasing";s:14:"easeInOutQuint";s:10:"time_start";s:4:"2400";}}}', 'data/demo/bg-slider.png', 1, 0),
(8, 'Banner Book 1', 0, 3, 'a:16:{s:2:"id";s:1:"8";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:13:"Banner Book 1";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"600";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"1";s:9:"slider_id";s:1:"8";s:12:"slider_image";s:25:"data/demo/bg-slider-3.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:3:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:27:"data/demo/banner-book-1.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"pav_small";s:13:"layer_caption";s:12:"Donec metus ";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:14:"easeInOutQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"63";s:10:"layer_left";s:3:"660";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltr";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2324";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:30:"data/demo/banner-promotion.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:7:"pav_big";s:13:"layer_caption";s:5:"OFFER";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutBack";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"126";s:10:"layer_left";s:3:"705";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltl";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"3056";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:30:"data/demo/banner-promotion.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:8:"pav_desc";s:13:"layer_caption";s:96:"Lorem isum dolor sit amet consectetur dipiscing elituce &lt;br/&gt; non massa sit amet vehicula ";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeOutBack";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"211";s:10:"layer_left";s:3:"597";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltr";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}}}', 'data/demo/bg-slider-3.png', 1, 0),
(14, 'Banner Book 2', 0, 3, 'a:16:{s:2:"id";s:2:"14";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:13:"Banner Book 2";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"500";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"14";s:12:"slider_image";s:25:"data/demo/bg-slider-2.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:31:"data/demo/banner_digital_01.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 2";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"43";s:10:"layer_left";s:2:"46";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltl";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:27:"data/demo/banner-book-2.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"pav_small";s:13:"layer_caption";s:12:"Donec metus ";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"64";s:10:"layer_left";s:3:"659";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1200";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:27:"data/demo/banner-book-2.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:7:"pav_big";s:13:"layer_caption";s:7:"Caption";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"122";s:10:"layer_left";s:3:"651";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:8:"pav_desc";s:13:"layer_caption";s:95:"Lorem isum dolor sit amet consectetur dipiscing elituce &lt;br&gt; non massa sit amet vehicula ";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"207";s:10:"layer_left";s:3:"602";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltb";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"pav-button-layer";s:13:"layer_caption";s:46:"&lt;a href=&quot;#&quot;&gt;Shop now&lt;/a&gt;";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"290";s:10:"layer_left";s:3:"808";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltl";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1462";}}}', 'data/demo/bg-slider-2.png', 1, 0),
(17, 'Banner Book 5', 0, 3, 'a:16:{s:2:"id";s:2:"17";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:13:"Banner Book 5";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"17";s:12:"slider_image";s:25:"data/demo/bg-slider-5.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:28:"data/demo/banner_sandals.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 1";s:15:"layer_animation";s:4:"fade";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"-2";s:10:"layer_left";s:2:"46";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:28:"data/demo/banner_sandals.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"pav_small";s:13:"layer_caption";s:12:"Donec metus ";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"67";s:10:"layer_left";s:3:"660";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:28:"data/demo/banner_sandals.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:7:"pav_big";s:13:"layer_caption";s:5:"OFFER";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"133";s:10:"layer_left";s:3:"704";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltr";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1200";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:28:"data/demo/banner_sandals.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:14:"pav_desc_white";s:13:"layer_caption";s:96:"Lorem isum dolor sit amet consectetur dipiscing &lt;br/&gt; elituce non massa sit amet vehicula ";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"210";s:10:"layer_left";s:3:"639";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltt";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:28:"data/demo/banner_sandals.png";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"pav-button-layer";s:13:"layer_caption";s:46:"&lt;a href=&quot;#&quot;&gt;Shop now&lt;/a&gt;";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"284";s:10:"layer_left";s:3:"805";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltr";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1546";}}}', 'data/demo/bg-slider-5.png', 1, 0),
(16, 'Banner Book 4', 0, 3, 'a:16:{s:2:"id";s:2:"16";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:13:"Banner Book 4";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"16";s:12:"slider_image";s:25:"data/demo/bg-slider-4.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:3:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"pav_small";s:13:"layer_caption";s:11:"Donec metus";s:15:"layer_animation";s:4:"fade";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"53";s:10:"layer_left";s:2:"84";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:7:"pav_big";s:13:"layer_caption";s:5:"OFFER";s:15:"layer_animation";s:4:"fade";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"113";s:10:"layer_left";s:2:"84";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1200";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:8:"pav_desc";s:13:"layer_caption";s:95:"Lorem isum dolor sit amet consectetur dipiscing&lt;br/&gt; elituce non massa sit amet vehicula ";s:15:"layer_animation";s:4:"fade";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"194";s:10:"layer_left";s:2:"85";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}}}', 'data/demo/bg-slider-4.png', 1, 0);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10844 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'themecontrol', 'themecontrol', 'a:69:{s:13:"default_theme";s:9:"pav_books";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"0";s:30:"listing_products_columns_small";s:1:"2";s:34:"listing_products_columns_minismall";s:1:"1";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"0";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:44:"79-99 Beaver Street, New York, NY 10005, USA";s:17:"location_latitude";s:9:"40.705423";s:18:"location_longitude";s:10:"-74.008616";s:18:"contact_customhtml";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:23:"enable_development_mode";s:0:"";s:13:"about_us_data";a:2:{i:1;s:1513:"&lt;div class=&quot;box pav-about-us m-height is-highlight&quot;&gt;\r\n&lt;h3 class=&quot;box-heading&quot;&gt;&lt;span&gt;About Us&lt;/span&gt;&lt;/h3&gt;\r\n\r\n&lt;section class=&quot;box-content&quot;&gt;\r\n&lt;article&gt;&lt;img alt=&quot;About Us&quot; src=&quot;image/data/demo/about_block.jpg&quot; /&gt;\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nulla libero, vulputate nec bibendum sed, vehicula sit amet mi. Mauris nec urna egestas. Proin mauris ipsum, egestas vel ultrices in, tempus in mauris. Aenean vel sapien sapien, mattis vulputate tellus.&lt;/p&gt;\r\n\r\n&lt;p&gt;Mauris iaculis, nisl in volutpat mollis, ligula orci suscipit magna, sed sagittis orci tortor eget leo. Duis nisi tellus, fermentum vel tincidunt ut, bibendum nec arcu. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Ut eget nunc dui.&lt;/p&gt;\r\n\r\n&lt;p&gt;Curabitur congue rhoncus est, eget mollis massa hendrerit sed. Mauris elit purus, viverra nec gravida ut, vehicula vitae ipsum. Proin diet, nisi at vestibulum bibendum, leo ante fermentum diam, semper congue velit sapien id elit. Integer vehicula porta mattis.&lt;/p&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipisicing elit. Omnis, ducimus, amet porro iusto libero sed eaque suscipit eum a adipisci enim voluptas culpa qui eveniet earum voluptatem quod unde quibusdam facilis quae perferendis laudantium aperiam totam odit dicta ex ab.&lt;/p&gt;\r\n&lt;/article&gt;\r\n&lt;/section&gt;\r\n&lt;/div&gt;";i:2;s:2158:"&lt;div class=&quot;box pav-about-us m-height is-highlight&quot;&gt;\r\n&lt;h3 class=&quot;box-heading&quot;&gt;&lt;span&gt;معلومات عنا&lt;/span&gt;&lt;/h3&gt;\r\n\r\n&lt;section class=&quot;box-content&quot;&gt;\r\n&lt;article&gt;&lt;img alt=&quot;معلومات عنا&quot; src=&quot;image/data/demo/about_block.jpg&quot; /&gt;\r\n&lt;p&gt;يعبأ تسمّى ما دار. ضرب رئيس إستيلاء الأوروبية مع, لعملة الدولارات جعل تم. الأخذ وتنصيب إتفاقية مع حيث, إذ بين ثمّة وعلى العالمي, وفي و وجهان الأوضاع. حدى من يرتبط استمرار, في تلك شاسعة الصينية واقتصار.&lt;/p&gt;\r\n\r\n&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n\r\n&lt;p&gt;أم مما وبعدما وبالرغم بمعارضة, انه أن بشرية المتحدة, تحت وسفن صفحة وقوعها، أم. ولم جورج شرسة تم, وسوء أمّا أساسي غير عل, بحث الوزراء لبولندا، الاندونيسية قد. كل خطّة ماذا لفرنسا لان. ضرب ثم تكبّد وباءت الأرواح, تم قام فاتّبع الدنمارك الأوروبية. كما ما بفرض وتنصيب, مكثّفة وتنصيب الجنوبي تم لكل. أكثر جسيمة ويتّفق و تحت.&lt;/p&gt;\r\n\r\n&lt;p&gt;يعبأ تسمّى ما دار. ضرب رئيس إستيلاء الأوروبية مع, لعملة الدولارات جعل تم. الأخذ وتنصيب إتفاقية مع حيث, إذ بين ثمّة وعلى العالمي, وفي و وجهان الأوضاع. حدى من يرتبط استمرار, في تلك شاسعة الصينية واقتصار.&lt;/p&gt;\r\n&lt;/article&gt;\r\n&lt;/section&gt;\r\n&lt;/div&gt;\r\n";}s:12:"twitter_data";a:2:{i:1;s:835:"&lt;div class=&quot;box pav-twitter-latest m-height is-highlight&quot;&gt;\r\n&lt;h3 class=&quot;box-heading&quot;&gt;&lt;span&gt;Latest tweets&lt;/span&gt;&lt;/h3&gt;\r\n\r\n&lt;section class=&quot;box-content&quot;&gt;&lt;a class=&quot;twitter-timeline&quot; data-tweet-limit=&quot;3&quot; data-widget-id=&quot;551144578171305984&quot; href=&quot;https://twitter.com/ExpandCart&quot;&gt;Tweets by @ExpandCart&lt;/a&gt; &lt;script type=&quot;text/javascript&quot;&gt;!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?&quot;http&quot;:&quot;https&quot;;if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+&quot;://platform.twitter.com/widgets.js&quot;;fjs.parentNode.insertBefore(js,fjs);}}(document,&quot;script&quot;,&quot;twitter-wjs&quot;);&lt;/script&gt;&lt;/section&gt;\r\n&lt;/div&gt;\r\n";i:2;s:847:"&lt;div class=&quot;box pav-twitter-latest m-height is-highlight&quot;&gt;\r\n&lt;h3 class=&quot;box-heading&quot;&gt;&lt;span&gt;أخر التغريدات&lt;/span&gt;&lt;/h3&gt;\r\n\r\n&lt;section class=&quot;box-content&quot;&gt;&lt;a class=&quot;twitter-timeline&quot; data-tweet-limit=&quot;3&quot; data-widget-id=&quot;551144578171305984&quot; href=&quot;https://twitter.com/ExpandCart&quot;&gt;Tweets by @ExpandCart&lt;/a&gt; &lt;script type=&quot;text/javascript&quot;&gt;!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?&quot;http&quot;:&quot;https&quot;;if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+&quot;://platform.twitter.com/widgets.js&quot;;fjs.parentNode.insertBefore(js,fjs);}}(document,&quot;script&quot;,&quot;twitter-wjs&quot;);&lt;/script&gt;&lt;/section&gt;\r\n&lt;/div&gt;\r\n";}s:11:"social_data";a:2:{i:1;s:951:"&lt;ul class=&quot;social clearfix&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-facebook&quot; href=&quot;https://facebook.com/&quot; target=&quot;_blank&quot; title=&quot;Facebook&quot;&gt;Facebook&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-twitter&quot; href=&quot;https://twitter.com/&quot; target=&quot;_blank&quot; title=&quot;Twitter&quot;&gt;Twitter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-google&quot; href=&quot;https://plus.google.com/&quot; target=&quot;_blank&quot; title=&quot;Google Plus&quot;&gt;Google Plus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-youtube&quot; href=&quot;https://youtube.com/&quot; target=&quot;_blank&quot; title=&quot;Youtube&quot;&gt;Youtube&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-pinterest&quot; href=&quot;http://pinterest.com/&quot; target=&quot;_blank&quot; title=&quot;Pinterest&quot;&gt;Pinterest&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:951:"&lt;ul class=&quot;social clearfix&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-facebook&quot; href=&quot;https://facebook.com/&quot; target=&quot;_blank&quot; title=&quot;Facebook&quot;&gt;Facebook&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-twitter&quot; href=&quot;https://twitter.com/&quot; target=&quot;_blank&quot; title=&quot;Twitter&quot;&gt;Twitter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-google&quot; href=&quot;https://plus.google.com/&quot; target=&quot;_blank&quot; title=&quot;Google Plus&quot;&gt;Google Plus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-youtube&quot; href=&quot;https://youtube.com/&quot; target=&quot;_blank&quot; title=&quot;Youtube&quot;&gt;Youtube&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;pavicon-pinterest&quot; href=&quot;http://pinterest.com/&quot; target=&quot;_blank&quot; title=&quot;Pinterest&quot;&gt;Pinterest&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:15:"newsletter_data";a:2:{i:1;s:460:"&lt;div class=&quot;input-box&quot;&gt;&lt;a class=&quot;button button-newsletter&quot; href=&quot;#&quot; title=&quot;subscribe&quot;&gt;&lt;span&gt;subscribe&lt;/span&gt;&lt;/a&gt; &lt;input class=&quot;input-subscribe&quot; name=&quot;email&quot; placeholder=&quot;Enter email address&quot; title=&quot;Enter email address&quot; type=&quot;text&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;h4 class=&quot;t-news hidden-tablet hidden-phone&quot;&gt;Newsletter&lt;/h4&gt;\r\n";i:2;s:541:"&lt;div class=&quot;input-box&quot;&gt;&lt;a class=&quot;button button-newsletter&quot; href=&quot;#&quot; title=&quot;إشترك الأن&quot;&gt;&lt;span&gt;إشترك الأن&lt;/span&gt;&lt;/a&gt; &lt;input class=&quot;input-subscribe&quot; name=&quot;email&quot; placeholder=&quot;أدخل بريدك الإلكتروني&quot; title=&quot;أدخل بريدك الإلكتروني&quot; type=&quot;text&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;h4 class=&quot;t-news hidden-tablet hidden-phone&quot;&gt;النشرة البريدية&lt;/h4&gt;\r\n";}s:13:"shop_map_data";a:2:{i:1;s:231:"&lt;p&gt;&lt;img alt=&quot;shop map&quot; src=&quot;image/data/demo/shopmap.jpg&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;i class=&quot;pavicon-shop&quot;&gt;&amp;nbsp;&lt;/i&gt;Address: Me Tri Ha, Tu Liem, Ha Noi ,Viet Nam&lt;/p&gt;\r\n";i:2;s:229:"&lt;p&gt;&lt;img alt=&quot;shop map&quot; src=&quot;image/data/demo/shopmap.jpg&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;i class=&quot;pavicon-shop&quot;&gt;&amp;nbsp;&lt;/i&gt;العنوان: 17 طريق الملك فهد&lt;/p&gt;";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"1";s:11:"type_fonts1";s:6:"google";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:71:"http://fonts.googleapis.com/css?family=Arvo:400,700,400italic,700italic";s:14:"google_family1";s:14:"''Arvo'', serif;";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:19:"enable_compress_css";s:0:"";s:17:"exclude_css_files";s:13:"bootstrap.css";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:4:"none";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":1,"subwidth":900,"cols":3,"group":0,"id":2,"rows":[{"cols":[{"type":"menu"},{"type":"menu"},{"type":"menu"}]}]},{"submenu":1,"cols":1,"group":1,"id":9,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":8,"rows":[]},{"submenu":1,"cols":1,"group":0,"id":5,"rows":[{"cols":[{"colwidth":12,"type":"menu"}]}]},{"submenu":1,"subwidth":800,"id":48,"cols":1,"group":0,"rows":[{"cols":[{"widgets":"wid-2","colwidth":6},{"widgets":"wid-7","colwidth":6}]}]},{"submenu":1,"cols":4,"group":0,"id":3,"rows":[{"cols":[{"type":"menu"},{"type":"menu"},{"type":"menu"},{"type":"menu"}]}]},{"submenu":1,"cols":1,"group":1,"id":56,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":57,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":70,"rows":[]}]', 0),
(0, 'pavcustom', 'pavcustom_module', 'a:1:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:14:"Banner Pretium";i:2;s:21:"إعلان مدفوع";}s:11:"description";a:2:{i:1;s:116:"&lt;p&gt;&lt;img alt=&quot;Faucibus Pretium&quot; src=&quot;image/data/demo/banner-pretium.png&quot; /&gt;&lt;/p&gt;";i:2;s:121:"&lt;p&gt;&lt;img alt=&quot;إعلان مدفوع&quot; src=&quot;image/data/demo/banner-pretium.png&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:12:"module_class";s:11:"main-banner";s:10:"sort_order";i:2;}}', 1),
(0, 'pavproducts', 'pavproducts_module', 'a:1:{i:1;a:13:{s:11:"description";a:2:{i:1;s:156:"&lt;p&gt;Lorem ipsum dolor sit amet consectetur adipiscing elit non tempor dignissim, velit leo ante condimentum lorem odio cipit ultrices elit.&lt;/p&gt;\r\n";i:2;s:215:"&lt;p&gt;أما والنرويج التجارية تم. إنطلاق العاصمة ومطالبة بـ عدم. من كما وبدأت طوكيو بالرّغم, انه وسفن لغات القوى كل.&lt;/p&gt;\r\n";}s:5:"width";s:3:"200";s:6:"height";s:3:"150";s:12:"itemsperpage";s:1:"6";s:4:"cols";s:1:"3";s:5:"limit";s:2:"12";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:13:"category_tabs";a:5:{i:0;s:2:"57";i:1;s:2:"17";i:2;s:2:"73";i:3;s:2:"65";i:4;s:2:"69";}s:5:"image";a:5:{i:0;s:32:"data/demo/tab-product-bullet.png";i:1;s:32:"data/demo/tab-product-bullet.png";i:2;s:32:"data/demo/tab-product-bullet.png";i:3;s:32:"data/demo/tab-product-bullet.png";i:4;s:32:"data/demo/tab-product-bullet.png";}s:5:"class";a:5:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";i:4;s:0:"";}}}', 1),
(0, 'pavtestimonial', 'pavtestimonial_module', 'a:1:{i:0;a:11:{s:9:"layout_id";s:1:"1";s:8:"position";s:9:"promotion";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"1";s:13:"text_interval";s:4:"8000";s:5:"width";s:2:"60";s:6:"height";s:2:"60";s:11:"column_item";s:1:"1";s:10:"page_items";s:1:"2";s:16:"testimonial_item";a:5:{i:1;a:4:{s:5:"image";s:30:"data/demo/Author-and-books.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:339:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}i:2;a:4:{s:5:"image";s:21:"data/demo/avata-1.png";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:339:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}i:3;a:4:{s:5:"image";s:25:"data/demo/author-jame.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:339:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}i:4;a:4:{s:5:"image";s:36:"data/demo/author_david_henderson.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:339:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}i:5;a:4:{s:5:"image";s:29:"data/demo/author-jamesjpg.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:339:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}}}}', 1),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:1:{i:1;a:10:{s:11:"description";a:2:{i:1;s:0:"";i:7;s:0:"";}s:4:"tabs";s:8:"featured";s:5:"width";s:2:"60";s:6:"height";s:2:"60";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:9:"layout_id";s:2:"14";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"6";s:9:"layout_id";s:2:"14";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'special', 'special_module', 'a:2:{i:0;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"180";s:12:"image_height";s:3:"180";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"4";}i:1;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"180";s:12:"image_height";s:3:"180";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"4";}}', 1),
(0, 'banner', 'banner_module', 'a:22:{i:0;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"222";s:6:"height";s:3:"348";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:1;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"222";s:6:"height";s:3:"348";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:2;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"222";s:6:"height";s:3:"122";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:3;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"222";s:6:"height";s:3:"122";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:4;a:7:{s:9:"banner_id";s:2:"11";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:5;a:7:{s:9:"banner_id";s:2:"11";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:6;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:7;a:7:{s:9:"banner_id";s:2:"11";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:8;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"222";s:6:"height";s:3:"348";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:9;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:2:"13";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:10;a:7:{s:9:"banner_id";s:2:"11";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:2:"13";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:11;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"222";s:6:"height";s:3:"348";s:9:"layout_id";s:2:"13";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:12;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"222";s:6:"height";s:3:"348";s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}i:13;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:14;a:7:{s:9:"banner_id";s:2:"11";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:15;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:16;a:7:{s:9:"banner_id";s:2:"11";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:17;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:18;a:7:{s:9:"banner_id";s:2:"11";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:19;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:1:"7";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:20;a:7:{s:9:"banner_id";s:2:"11";s:5:"width";s:3:"222";s:6:"height";s:3:"123";s:9:"layout_id";s:1:"7";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:21;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"222";s:6:"height";s:3:"348";s:9:"layout_id";s:1:"7";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"5";}}', 1),
(0, 'featured', 'featured_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"180";s:12:"image_height";s:3:"180";s:9:"layout_id";s:1:"7";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'category', 'category_module', 'a:6:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:3;a:4:{s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:4;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:5;a:4:{s:9:"layout_id";s:1:"7";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"680";s:15:"general_lheight";s:3:"390";s:14:"general_swidth";s:3:"250";s:15:"general_sheight";s:3:"250";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"80";s:14:"rss_limit_item";s:188:"&lt;b&gt;Notice&lt;/b&gt;: Undefined index: rss_limit_item in &lt;b&gt;E:\\xampplite\\htdocs\\pav_asenti\\admin\\view\\template\\module\\pavblog\\modules.tpl&lt;/b&gt; on line &lt;b&gt;58&lt;/b&gt;";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"200";s:15:"general_cheight";s:3:"200";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"6";s:22:"cat_leading_image_type";s:1:"l";s:24:"cat_secondary_image_type";s:1:"l";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:2:"10";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"1";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:2:"22";s:9:"layout_id";s:2:"14";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavcontentslider', 'pavcontentslider_module', 'a:1:{i:0;a:10:{s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:5:"width";s:3:"686";s:6:"height";s:3:"344";s:15:"image_navigator";s:1:"0";s:13:"navimg_weight";s:2:"90";s:13:"navimg_height";s:2:"90";s:12:"banner_image";a:4:{i:1;a:4:{s:5:"image";s:23:"data/demo/slider_01.png";s:4:"link";s:1:"#";s:5:"title";a:3:{i:1;s:61:"Special &lt;span&gt;30%&lt;/span&gt; &lt;b&gt;offer&lt;/b&gt;";i:4;s:0:"";i:6;s:0:"";}s:11:"description";a:3:{i:1;s:203:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elitusce non massa sit amet vehicula&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;button&quot; href=&quot;#&quot;&gt;Read More&lt;/a&gt;&lt;/p&gt;\r\n";i:4;s:0:"";i:6;s:0:"";}}i:2;a:4:{s:5:"image";s:23:"data/demo/slider_01.png";s:4:"link";s:1:"#";s:5:"title";a:3:{i:1;s:61:"Special &lt;span&gt;30%&lt;/span&gt; &lt;b&gt;offer&lt;/b&gt;";i:4;s:0:"";i:6;s:0:"";}s:11:"description";a:3:{i:1;s:203:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elitusce non massa sit amet vehicula&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;button&quot; href=&quot;#&quot;&gt;Read More&lt;/a&gt;&lt;/p&gt;\r\n";i:4;s:0:"";i:6;s:0:"";}}i:3;a:4:{s:5:"image";s:23:"data/demo/slider_01.png";s:4:"link";s:1:"#";s:5:"title";a:3:{i:1;s:61:"Special &lt;span&gt;30%&lt;/span&gt; &lt;b&gt;offer&lt;/b&gt;";i:4;s:0:"";i:6;s:0:"";}s:11:"description";a:3:{i:1;s:203:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elitusce non massa sit amet vehicula&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;button&quot; href=&quot;#&quot;&gt;Read More&lt;/a&gt;&lt;/p&gt;\r\n";i:4;s:0:"";i:6;s:0:"";}}i:4;a:4:{s:5:"image";s:23:"data/demo/slider_01.png";s:4:"link";s:1:"#";s:5:"title";a:3:{i:1;s:61:"Special &lt;span&gt;30%&lt;/span&gt; &lt;b&gt;offer&lt;/b&gt;";i:4;s:0:"";i:6;s:0:"";}s:11:"description";a:3:{i:1;s:201:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elitusce non massa sit amet vehicula&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;button&quot; href=&quot;#&quot;&gt;Read More&lt;/a&gt;&lt;/p&gt;";i:4;s:0:"";i:6;s:0:"";}}}}}', 1),
(0, 'config', 'config_image_cart_height', '80', 0),
(0, 'config', 'config_image_cart_width', '80', 0),
(0, 'config', 'config_image_wishlist_height', '80', 0),
(0, 'pavproducttabs', 'pavproducttabs_module', 'a:1:{i:1;a:11:{s:11:"description";a:2:{i:1;s:0:"";i:7;s:0:"";}s:4:"tabs";a:3:{i:0;s:6:"latest";i:1;s:7:"special";i:2;s:10:"mostviewed";}s:5:"width";s:3:"200";s:6:"height";s:3:"200";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:1:"8";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'config', 'config_image_wishlist_width', '80', 0),
(0, 'config', 'config_image_compare_height', '175', 0),
(0, 'config', 'config_image_compare_width', '140', 0),
(0, 'config', 'config_image_related_height', '140', 0),
(0, 'config', 'config_image_related_width', '184', 0),
(0, 'config', 'config_image_additional_height', '80', 0),
(0, 'config', 'config_image_additional_width', '80', 0),
(0, 'config', 'config_image_product_height', '200', 0),
(0, 'config', 'config_image_product_width', '200', 0),
(0, 'config', 'config_image_popup_height', '600', 0),
(0, 'config', 'config_image_popup_width', '600', 0),
(0, 'config', 'config_image_thumb_height', '311', 0),
(0, 'config', 'config_image_thumb_width', '285', 0),
(0, 'config', 'config_image_category_height', '203', 0),
(0, 'config', 'config_image_category_width', '714', 0),
(0, 'featured', 'featured_product', '65,58,70,78', 0),
(0, 'bestseller', 'bestseller_module', 'a:2:{i:0;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";s:9:"layout_id";s:2:"13";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"4";}i:1;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"0";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'latest', 'latest_module', 'a:1:{i:1;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"180";s:12:"image_height";s:3:"180";s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"4";}}', 1),
(0, 'featured', 'product', 'tr', 0),
(0, 'pavsliderlayer', 'pavsliderlayer_module', 'a:1:{i:0;a:5:{s:8:"group_id";s:1:"3";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavtwitter', 'pavtwitter_module', 'a:1:{i:0;a:6:{s:5:"count";s:1:"3";s:8:"username";s:10:"expandcart";s:9:"layout_id";s:5:"99999";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"0";s:10:"sort_order";s:1:"2";}}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=453 ;

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
('module', 'themecontrol'),
('module', 'pavsliderlayer'),
('module', 'pavcustom'),
('module', 'pavproducttabs'),
--('module', 'pavmegamenu'),
('module', 'latest'),
('module', 'special'),
('module', 'bestseller'),
('module', 'pavproducts'),
('module', 'pavblog'),
('module', 'pavblogcategory'),
('module', 'pavblogcomment'),
('module', 'pavbloglatest'),
('module', 'pavtestimonial');