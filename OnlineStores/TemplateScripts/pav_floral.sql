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
  `widget_id` int(11) NOT NULL,
  PRIMARY KEY (`megamenu_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(2, '', 1, 0, NULL, NULL, NULL, 'col1=3, col2=3, col3=6', '20', '3', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(3, '', 1, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(4, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(5, '', 1, 0, NULL, NULL, NULL, '', '33', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(7, '', 1, 0, NULL, NULL, NULL, '', '33', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(8, '', 2, 1, NULL, NULL, NULL, '', '27', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '&lt;p&gt;test&lt;/p&gt;\r\n', 0, 0, 0, 1),
(9, '', 2, 1, NULL, NULL, NULL, '', '26', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(10, '', 8, 0, NULL, NULL, NULL, '', '59', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(11, '', 8, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(12, '', 8, 0, NULL, NULL, NULL, '', '61', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(13, '', 8, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(14, '', 8, 0, NULL, NULL, NULL, '', '63', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(15, '', 8, 0, NULL, NULL, NULL, '', '64', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(16, '', 8, 0, NULL, NULL, NULL, '', '65', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(17, '', 9, 0, NULL, NULL, NULL, '', '66', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(18, '', 9, 0, NULL, NULL, NULL, '', '67', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(19, '', 9, 0, NULL, NULL, NULL, '', '68', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(20, '', 9, 0, NULL, NULL, NULL, '', '71', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(21, '', 9, 0, NULL, NULL, NULL, '', '72', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(22, '', 9, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(23, '', 9, 0, NULL, NULL, NULL, '', '70', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(24, '', 2, 0, NULL, NULL, NULL, '', '', '1', 'html', 1, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '&lt;div class=&quot;pav-menu-video&quot;&gt;&lt;iframe allowfullscreen=&quot;&quot; frameborder=&quot;0&quot; height=&quot;157&quot; src=&quot;http://www.youtube.com/embed/NBuLeA7nNFk&quot; width=&quot;279&quot;&gt;&lt;/iframe&gt;\r\n&lt;h3&gt;Lorem ipsum dolor sit&lt;/h3&gt;\r\n\r\n&lt;p&gt;Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', 0, 0, 0, 0),
(25, '', 3, 0, NULL, NULL, NULL, '', '79', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(26, '', 3, 0, NULL, NULL, NULL, '', '74', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(27, '', 3, 0, NULL, NULL, NULL, '', '73', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(28, '', 3, 0, NULL, NULL, NULL, '', '80', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(29, '', 3, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(30, '', 3, 0, NULL, NULL, NULL, '', '46', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(31, '', 3, 0, NULL, NULL, NULL, '', '75', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(32, '', 3, 0, NULL, NULL, NULL, '', '78', '1', 'category', 0, 1, 'menu', 0, 1, 0, 9, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(33, '', 3, 0, NULL, NULL, NULL, '', '77', '1', 'category', 0, 1, 'menu', 0, 1, 0, 10, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(34, '', 3, 0, NULL, NULL, NULL, '', '77', '1', 'category', 0, 1, 'menu', 0, 1, 0, 11, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(35, '', 3, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 12, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(36, '', 3, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 13, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(37, '', 1, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 0, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(40, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0, 1);

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
(2, 3, 'رومیزی', ''),
(3, 1, 'Laptops &amp; Notebooks', ''),
(4, 1, 'Watches', ''),
(2, 2, 'Electronics', ''),
(4, 2, 'Watches', ''),
(5, 1, 'Cameras', ''),
(37, 3, 'اجزاء', ''),
(7, 1, 'Office', ''),
(8, 2, 'Computers', ''),
(9, 2, 'Mobiles', ''),
(8, 1, 'Computers', ''),
(10, 1, 'Duis tempor', ''),
(11, 1, 'Pellentesque eget ', ''),
(12, 1, 'Nam nunc ante', ''),
(13, 1, 'Condimentum eu', ''),
(14, 1, 'Lehicula lorem', ''),
(15, 1, 'Integer semper', ''),
(16, 1, 'Sollicitudin lacus', ''),
(9, 1, 'Printer', ''),
(17, 1, 'Nam ipsum ', ''),
(18, 1, 'Curabitur turpis ', ''),
(19, 2, 'Molestie eu mattis ', ''),
(19, 1, 'Molestie eu mattis ', ''),
(20, 2, 'Suspendisse eu ', ''),
(20, 1, 'Suspendisse eu ', ''),
(21, 1, 'Nunc imperdiet ', ''),
(21, 2, 'Nunc imperdiet ', ''),
(22, 2, 'Mauris mattis', ''),
(22, 1, 'Mauris mattis', ''),
(23, 2, 'Lacus sed iaculis ', ''),
(23, 1, 'Lacus sed iaculis ', ''),
(24, 2, 'Lorem ipsum dolor sit ', ''),
(24, 1, 'Lorem ipsum dolor sit ', ''),
(37, 2, 'Watches', ''),
(25, 2, 'Aliquam', ''),
(25, 1, 'Aliquam', ''),
(26, 2, 'Claritas', ''),
(26, 1, 'Claritas', ''),
(27, 1, 'Consectetuer', ''),
(28, 2, 'Hendrerit', ''),
(28, 1, 'Hendrerit', ''),
(29, 2, 'Litterarum', ''),
(29, 1, 'Litterarum', ''),
(30, 2, 'Macs', ''),
(30, 1, 'Macs', ''),
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
(40, 2, 'Home', ''),
(40, 1, 'Home', ''),
(2, 1, 'Desktops', ''),
(40, 3, 'صفحه', ''),
(5, 2, 'Books', ''),
(5, 3, 'دوربین', ''),
(7, 2, 'Office', ''),
(7, 3, 'دفتر', ''),
(3, 2, 'Digital', ''),
(3, 3, 'لپ تاپ و نوت بوک', ''),
(37, 1, 'Components', ''),
(8, 3, 'Computers', ''),
(10, 2, 'Duis tempor', ''),
(10, 3, 'Duis tempor', ''),
(11, 2, 'Pellentesque eget', ''),
(11, 3, 'Pellentesque eget', ''),
(13, 2, 'Condimentum eu', ''),
(13, 3, 'Condimentum eu', ''),
(12, 2, 'Nam nunc ante', ''),
(12, 3, 'Nam nunc ante', ''),
(14, 2, 'Lehicula lorem', ''),
(14, 3, 'Lehicula lorem', ''),
(15, 2, 'Integer semper', ''),
(15, 3, 'Integer semper', ''),
(16, 2, 'Sollicitudin lacus', ''),
(16, 3, 'Sollicitudin lacus', ''),
(9, 3, 'Printer', ''),
(17, 2, 'Nam ipsum ', ''),
(17, 3, 'Nam ipsum ', ''),
(19, 3, 'Molestie eu mattis ', ''),
(18, 2, 'Curabitur turpis ', ''),
(18, 3, 'Curabitur turpis ', ''),
(20, 3, 'Suspendisse eu ', ''),
(22, 3, 'Mauris mattis', ''),
(23, 3, 'Lacus sed iaculis ', ''),
(4, 3, 'Watches', ''),
(25, 3, 'Aliquam', ''),
(26, 3, 'Claritas', ''),
(27, 2, 'Consectetuer', ''),
(27, 3, 'Consectetuer', ''),
(28, 3, 'Hendrerit', ''),
(29, 3, 'Litterarum', ''),
(30, 3, 'Macs', '');

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
(2, 'Dermo HTML Sample', 'html', 'a:1:{s:4:"html";a:1:{i:1;s:275:"Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel. Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.";}}', 0),
(3, 'Products Latest', 'product_list', 'a:4:{s:9:"list_type";s:6:"newest";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(4, 'Products In Cat 20', 'product_category', 'a:4:{s:11:"category_id";s:2:"20";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(5, 'Manufactures', 'banner', 'a:4:{s:8:"group_id";s:1:"8";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:5:"limit";s:2:"12";}', 0),
(6, 'expandcart Feed', 'feed', 'a:1:{s:8:"feed_url";s:55:"http://www.expandcart.com/expandcartfeeds.feed?type=rss";}', 0),
(7, 'testimg', 'image', 'a:3:{s:10:"image_path";s:13:"data/adv1.jpg";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";}', 0);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5285 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'carousel', 'carousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}}', 1),
(0, 'featured', 'featured_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"6";s:11:"image_width";s:3:"300";s:12:"image_height";s:3:"225";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"0";s:10:"sort_order";i:3;}}', 1),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'category', 'category_module', 'a:3:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'banner', 'banner_module', 'a:1:{i:0;a:7:{s:9:"banner_id";s:1:"6";s:5:"width";s:3:"182";s:6:"height";s:3:"182";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"0";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'config', 'config_image_cart_height', '150', 0),
(0, 'config', 'config_image_cart_width', '200', 0),
(0, 'config', 'config_image_wishlist_height', '150', 0),
(0, 'config', 'config_image_wishlist_width', '200', 0),
(0, 'config', 'config_image_compare_height', '150', 0),
(0, 'config', 'config_image_compare_width', '200', 0),
(0, 'config', 'config_image_related_height', '225', 0),
(0, 'config', 'config_image_related_width', '300', 0),
(0, 'config', 'config_image_additional_height', '86', 0),
(0, 'config', 'config_image_additional_width', '114', 0),
(0, 'config', 'config_image_product_height', '360', 0),
(0, 'config', 'config_image_product_width', '480', 0),
(0, 'config', 'config_image_popup_height', '450', 0),
(0, 'config', 'config_image_popup_width', '600', 0),
(0, 'config', 'config_image_thumb_height', '375', 0),
(0, 'config', 'config_image_thumb_width', '500', 0),
(0, 'config', 'config_image_category_height', '230', 0),
(0, 'config', 'config_image_category_width', '860', 0),
(0, 'featured', 'featured_product', '43,40,42,49,46,47,28', 0),
(0, 'featured', 'product', '', 0),
(0, 'pavnewsletter', 'pavnewsletter_module', 'a:1:{i:1;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"900";s:15:"general_lheight";s:3:"350";s:14:"general_swidth";s:3:"600";s:15:"general_sheight";s:3:"233";s:14:"general_xwidth";s:3:"200";s:15:"general_xheight";s:2:"78";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"900";s:15:"general_cheight";s:3:"350";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"5";s:22:"cat_leading_image_type";s:1:"l";s:24:"cat_secondary_image_type";s:1:"l";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:12:"100858303516";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'pavblog_frontmodules', 'pavblog_frontmodules', 'a:1:{s:7:"modules";a:3:{i:0;s:15:"pavblogcategory";i:1;s:13:"pavbloglatest";i:2;s:14:"pavblogcomment";}}', 1),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:1:{i:1;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:3:"300";s:6:"height";s:3:"160";s:4:"cols";s:1:"3";s:5:"limit";s:1:"3";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'pavtestimonial', 'pavtestimonial_module', 'a:1:{i:0;a:11:{s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"1";s:13:"text_interval";s:4:"8000";s:5:"width";s:3:"108";s:6:"height";s:3:"108";s:11:"column_item";s:1:"1";s:10:"page_items";s:1:"2";s:16:"testimonial_item";a:2:{i:1;a:4:{s:5:"image";s:21:"data/testimonial1.jpg";s:10:"video_link";s:0:"";s:7:"profile";a:3:{i:1;s:70:"&lt;h3&gt;jane doe&lt;/h3&gt;\r\n\r\n&lt;p&gt;Creative Manager&lt;/p&gt;\r\n";i:2;s:70:"&lt;h3&gt;jane doe&lt;/h3&gt;\r\n\r\n&lt;p&gt;Creative Manager&lt;/p&gt;\r\n";i:3;s:71:"&lt;h3&gt;jane doe&lt;/h3&gt;\r\n\r\n&lt;p&gt;مدیر خلاق&lt;/p&gt;\r\n";}s:11:"description";a:3:{i:1;s:190:"&lt;p&gt;Duis sed odio sit amet nibh vulputa cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non mauris vitae erat&lt;/p&gt;\r\n";i:2;s:190:"&lt;p&gt;Duis sed odio sit amet nibh vulputa cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non mauris vitae erat&lt;/p&gt;\r\n";i:3;s:394:"&lt;p&gt;به نفرت خوش آمدید روند نام تجاری در حال اجرا vulputa منتشر نمی شود کمیسیون است. بیماری محور های بازی در فضای باز ورزشگاه ها. نویسنده فوتبال از توسعه دهندگان زمین در واقع، هرگز نفرت به نفرت. اما زندگی خنده دار نمی&lt;/p&gt;\r\n";}}i:2;a:4:{s:5:"image";s:21:"data/testimonial2.jpg";s:10:"video_link";s:0:"";s:7:"profile";a:3:{i:1;s:67:"&lt;h3&gt;QuynhVT&lt;/h3&gt;\r\n\r\n&lt;p&gt;Suppor Manager&lt;/p&gt;\r\n";i:2;s:67:"&lt;h3&gt;QuynhVT&lt;/h3&gt;\r\n\r\n&lt;p&gt;Suppor Manager&lt;/p&gt;\r\n";i:3;s:80:"&lt;h3&gt;QuynhVT&lt;/h3&gt;\r\n\r\n&lt;p&gt;باربری ماناگوا&lt;/p&gt;\r\n";}s:11:"description";a:3:{i:1;s:252:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat&lt;/p&gt;\r\n";i:2;s:252:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat&lt;/p&gt;\r\n";i:3;s:252:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat&lt;/p&gt;\r\n";}}}}}', 1),
(0, 'pavcustom', 'pavcustom_module', 'a:8:{i:1;a:8:{s:12:"module_title";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:11:"description";a:3:{i:1;s:946:"&lt;div class=&quot;row quickaccess&quot;&gt;\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;fa fa-tags icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n&lt;h3&gt;Best prices&lt;/h3&gt;\r\n\r\n&lt;p&gt;Lorem quis bibendum auctor&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;fa fa-gift icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;Free gift&lt;/h3&gt;\r\n\r\n&lt;p&gt;Bibendum auctor nisi elit ceuat&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;fa fa-lock icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;Secure payment&lt;/h3&gt;\r\n\r\n&lt;p&gt;Misi elit consequat ipsum&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;fa fa-calendar icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;Support 24/7&lt;/h3&gt;\r\n\r\n&lt;p&gt;Consequat ipsum nec sagis sem&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:942:"&lt;div class=&quot;row quickaccess&quot;&gt;\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;icon-tags icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n&lt;h3&gt;Best prices&lt;/h3&gt;\r\n\r\n&lt;p&gt;Lorem quis bibendum auctor&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;icon-gift icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;Free gift&lt;/h3&gt;\r\n\r\n&lt;p&gt;Bibendum auctor nisi elit ceuat&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;icon-lock icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;Secure payment&lt;/h3&gt;\r\n\r\n&lt;p&gt;Misi elit consequat ipsum&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;icon-calendar icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;Support 24/7&lt;/h3&gt;\r\n\r\n&lt;p&gt;Consequat ipsum nec sagis sem&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:3;s:1058:"&lt;div class=&quot;row quickaccess&quot;&gt;\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;icon-tags icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n&lt;h3&gt;بهترین قیمت&lt;/h3&gt;\r\n\r\n&lt;p&gt;در حال نوشیدن یک نویسنده&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;icon-gift icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;هدیه رایگان&lt;/h3&gt;\r\n\r\n&lt;p&gt;نویسنده اما قابلیت های چند رسانه ای&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;icon-lock icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;پرداخت امن&lt;/h3&gt;\r\n\r\n&lt;p&gt;من فرستاده مالزی انجمن&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3&quot;&gt;&lt;span class=&quot;icon-calendar icon&quot;&gt;&amp;nbsp;&lt;/span&gt;\r\n\r\n&lt;h3&gt;پشتیبانی 24/7&lt;/h3&gt;\r\n\r\n&lt;p&gt;فرهنگ پرده ها، یا سالاد&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:8:"cusblock";s:10:"sort_order";s:1:"1";}i:2;a:8:{s:12:"module_title";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:11:"description";a:3:{i:1;s:86:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv.png&quot; /&gt;&lt;/p&gt;\r\n";i:2;s:86:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv.png&quot; /&gt;&lt;/p&gt;\r\n";i:3;s:86:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv.png&quot; /&gt;&lt;/p&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:3;a:8:{s:12:"module_title";a:3:{i:1;s:10:"Contact Us";i:2;s:0:"";i:3;s:18:"تماس با ما";}s:11:"description";a:3:{i:1;s:824:"&lt;p&gt;We have 152 guests and 14 members online&lt;/p&gt;\r\n\r\n&lt;p&gt;Phone: +01 888 (000) 1234&lt;/p&gt;\r\n\r\n&lt;p&gt;Fax: +01 888 (000) 1234&lt;/p&gt;\r\n\r\n&lt;p&gt;Email: www.leotheme.com&lt;/p&gt;\r\n\r\n&lt;ul class=&quot;social&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://facebook.com&quot;&gt;&lt;i class=&quot;fa fa-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://twitter.com&quot;&gt;&lt;i class=&quot;fa fa-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;https://plus.google.com&quot;&gt;&lt;i class=&quot;fa fa-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://youtube.com&quot;&gt;&lt;i class=&quot;fa fa-youtube&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:820:"&lt;p&gt;We have 152 guests and 14 members online&lt;/p&gt;\r\n\r\n&lt;p&gt;Phone: +01 888 (000) 1234&lt;/p&gt;\r\n\r\n&lt;p&gt;Fax: +01 888 (000) 1234&lt;/p&gt;\r\n\r\n&lt;p&gt;Email: www.leotheme.com&lt;/p&gt;\r\n\r\n&lt;ul class=&quot;social&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://facebook.com&quot;&gt;&lt;i class=&quot;icon-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://twitter.com&quot;&gt;&lt;i class=&quot;icon-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;https://plus.google.com&quot;&gt;&lt;i class=&quot;icon-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://youtube.com&quot;&gt;&lt;i class=&quot;icon-youtube&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:861:"&lt;p&gt;ما 152 مهمان و 14 عضو آنلاین داریم&lt;/p&gt;\r\n\r\n&lt;p&gt;تلفن: 01 888 (000) 1234&lt;/p&gt;\r\n\r\n&lt;p&gt;فکس: 01 888 (000) 1234&lt;/p&gt;\r\n\r\n&lt;p&gt;www.leotheme.com : پست الکترونیک&lt;/p&gt;\r\n\r\n&lt;ul class=&quot;social&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://facebook.com&quot;&gt;&lt;i class=&quot;icon-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://twitter.com&quot;&gt;&lt;i class=&quot;icon-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;https://plus.google.com&quot;&gt;&lt;i class=&quot;icon-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;http://youtube.com&quot;&gt;&lt;i class=&quot;icon-youtube&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:4;a:8:{s:12:"module_title";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:11:"description";a:3:{i:1;s:232:"&lt;p style=&quot;margin-bottom:20px&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv2.jpg&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;p style=&quot;&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv3.jpg&quot; /&gt;&lt;/p&gt;\r\n";i:2;s:232:"&lt;p style=&quot;margin-bottom:20px&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv2.jpg&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;p style=&quot;&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv3.jpg&quot; /&gt;&lt;/p&gt;\r\n";i:3;s:232:"&lt;p style=&quot;margin-bottom:23px&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv2.jpg&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;p style=&quot;&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv3.jpg&quot; /&gt;&lt;/p&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}i:5;a:8:{s:12:"module_title";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:11:"description";a:3:{i:1;s:87:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv1.jpg&quot; /&gt;&lt;/p&gt;\r\n";i:2;s:87:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv1.jpg&quot; /&gt;&lt;/p&gt;\r\n";i:3;s:134:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv1.jpg&quot; style=&quot;width: 261px; height: 278px;&quot; /&gt;&lt;/p&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}i:6;a:8:{s:12:"module_title";a:3:{i:1;s:11:"Information";i:2;s:0:"";i:3;s:14:"اطلاعات";}s:11:"description";a:3:{i:1;s:616:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;About Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Delivery Information&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Terms &amp;amp; Conditions&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:616:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;About Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Delivery Information&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Terms &amp;amp; Conditions&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:642:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;درباره ما&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;اطلاعات تحویل&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;سیاست حفظ اسرار&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;شرایط و ضوابط&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"3";}i:7;a:8:{s:12:"module_title";a:3:{i:1;s:16:"Customer Service";i:2;s:0:"";i:3;s:21:"خدمات مشتری";}s:11:"description";a:3:{i:1;s:571:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;Contact Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;Returns&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;Site Map&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;Brands&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Gift Vouchers&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:571:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;Contact Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;Returns&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;Site Map&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;Brands&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Gift Vouchers&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:608:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;تماس با ما&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;بازده&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;نقشه سایت&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;برندها&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;کوپن های هدیه&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"4";}i:8;a:8:{s:12:"module_title";a:3:{i:1;s:10:"My Account";i:2;s:0:"";i:3;s:26:"حساب کاربری من";}s:11:"description";a:3:{i:1;s:560:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;My Account&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/order&quot;&gt;Order History&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;Wish List&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;Newsletter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;Specials&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:560:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;My Account&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/order&quot;&gt;Order History&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;Wish List&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;Newsletter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;Specials&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:639:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;حساب کاربری من&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/order&quot;&gt;تاریخچه سفارش ها&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;فهرست مورد علاقه&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;عضویت در خبرنامه&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;ویژه ها&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";i:5;}}', 1),
(0, 'pavcontentslider', 'pavcontentslider_module', 'a:1:{i:0;a:12:{s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"1";s:13:"text_interval";s:4:"5000";s:5:"width";s:3:"873";s:6:"height";s:3:"380";s:15:"image_navigator";s:1:"0";s:13:"navimg_weight";s:3:"177";s:13:"navimg_height";s:2:"97";s:12:"banner_image";a:3:{i:1;a:7:{s:5:"image";s:15:"data/slide1.jpg";s:4:"link";s:21:"http://expandcart.com";s:5:"title";a:3:{i:1;a:3:{i:0;s:18:"Amazing pink tulip";i:1;s:129:"&lt;a href=&quot;#&quot; class=&quot;button&quot;&gt;Detail&lt;i class=&quot;fa fa-long-arrow-right&quot;&gt;&lt;/i&gt;&lt;/a&gt;";i:2;s:117:"Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagis sem nibh id elit. Duis sed odio";}i:2;a:1:{i:0;s:0:"";}i:3;a:3:{i:0;s:18:"Amazing pink tulip";i:1;s:128:"&lt;a href=&quot;#&quot; class=&quot;button&quot;&gt;Detail&lt;i class=&quot;icon-long-arrow-right&quot;&gt;&lt;/i&gt;&lt;/a&gt;";i:2;s:117:"Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagis sem nibh id elit. Duis sed odio";}}s:6:"effect";a:3:{i:1;a:3:{i:0;s:13:"slideExpandUp";i:1;s:6:"pullUp";i:2;s:8:"expandUp";}i:2;a:1:{i:0;s:7:"slideUp";}i:3;a:3:{i:0;s:7:"slideUp";i:1;s:9:"slideLeft";i:2;s:9:"slideLeft";}}s:5:"class";a:3:{i:1;a:3:{i:0;s:16:"big-caption pink";i:1;s:6:"detail";i:2;s:4:"desc";}i:2;a:1:{i:0;s:0:"";}i:3;a:3:{i:0;s:19:"title default-color";i:1;s:6:"detail";i:2;s:4:"desc";}}s:3:"top";a:3:{i:1;a:3:{i:0;s:2:"85";i:1;s:3:"238";i:2;s:3:"130";}i:2;a:1:{i:0;s:0:"";}i:3;a:3:{i:0;s:2:"85";i:1;s:3:"238";i:2;s:3:"130";}}s:4:"left";a:3:{i:1;a:3:{i:0;s:2:"50";i:1;s:2:"50";i:2;s:2:"50";}i:2;a:1:{i:0;s:0:"";}i:3;a:3:{i:0;s:2:"50";i:1;s:2:"50";i:2;s:2:"50";}}}i:2;a:7:{s:5:"image";s:15:"data/slide2.jpg";s:4:"link";s:21:"http://expandcart.com";s:5:"title";a:3:{i:1;a:3:{i:0;s:20:"Sed ut perspiciatis ";i:1;s:153:"Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt";i:2;s:129:"&lt;a href=&quot;#&quot; class=&quot;button&quot;&gt;Detail&lt;i class=&quot;fa fa-long-arrow-right&quot;&gt;&lt;/i&gt;&lt;/a&gt;";}i:2;a:1:{i:0;s:0:"";}i:3;a:1:{i:0;s:0:"";}}s:6:"effect";a:3:{i:1;a:3:{i:0;s:5:"hatch";i:1;s:10:"expandOpen";i:2;s:11:"bigEntrance";}i:2;a:1:{i:0;s:7:"slideUp";}i:3;a:1:{i:0;s:7:"slideUp";}}s:5:"class";a:3:{i:1;a:3:{i:0;s:19:" big-caption purple";i:1;s:4:"desc";i:2;s:6:"detail";}i:2;a:1:{i:0;s:0:"";}i:3;a:1:{i:0;s:0:"";}}s:3:"top";a:3:{i:1;a:3:{i:0;s:2:"85";i:1;s:3:"130";i:2;s:3:"238";}i:2;a:1:{i:0;s:0:"";}i:3;a:1:{i:0;s:0:"";}}s:4:"left";a:3:{i:1;a:3:{i:0;s:2:"50";i:1;s:2:"50";i:2;s:2:"50";}i:2;a:1:{i:0;s:0:"";}i:3;a:1:{i:0;s:0:"";}}}i:3;a:7:{s:5:"image";s:15:"data/slide3.jpg";s:4:"link";s:21:"http://expandcart.com";s:5:"title";a:3:{i:1;a:3:{i:0;s:14:"Nam libero tem";i:1;s:141:"At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas";i:2;s:129:"&lt;a href=&quot;#&quot; class=&quot;button&quot;&gt;Detail&lt;i class=&quot;fa fa-long-arrow-right&quot;&gt;&lt;/i&gt;&lt;/a&gt;";}i:2;a:1:{i:0;s:0:"";}i:3;a:1:{i:0;s:0:"";}}s:6:"effect";a:3:{i:1;a:3:{i:0;s:8:"pullDown";i:1;s:5:"hatch";i:2;s:10:"expandOpen";}i:2;a:1:{i:0;s:7:"slideUp";}i:3;a:1:{i:0;s:7:"slideUp";}}s:5:"class";a:3:{i:1;a:3:{i:0;s:18:" big-caption white";i:1;s:10:"desc white";i:2;s:6:"detail";}i:2;a:1:{i:0;s:0:"";}i:3;a:1:{i:0;s:0:"";}}s:3:"top";a:3:{i:1;a:3:{i:0;s:2:"85";i:1;s:3:"130";i:2;s:3:"238";}i:2;a:1:{i:0;s:0:"";}i:3;a:1:{i:0;s:0:"";}}s:4:"left";a:3:{i:1;a:3:{i:0;s:2:"50";i:1;s:2:"50";i:2;s:2:"50";}i:2;a:1:{i:0;s:0:"";}i:3;a:1:{i:0;s:0:"";}}}}}}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":1,"subwidth":700,"cols":3,"group":0,"id":2,"rows":[{"cols":[{"colwidth":3,"type":"menu"},{"colwidth":3,"type":"menu"},{"colwidth":6,"type":"menu"}]}]},{"submenu":1,"cols":1,"group":1,"id":8,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":9,"rows":[]},{"submenu":"0","subwidth":300,"id":5,"cols":1,"group":0,"rows":[]},{"submenu":1,"cols":1,"group":0,"id":3,"rows":[{"cols":[{"colwidth":12,"type":"menu"}]}]}]', 0),
(0, 'pavproductcarousel', 'pavproductcarousel_module', 'a:3:{i:1;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:6:"prefix";s:20:"box-product vertical";s:4:"tabs";a:1:{i:0;s:8:"featured";}s:5:"width";s:3:"480";s:6:"height";s:3:"360";s:12:"itemsperpage";s:1:"2";s:4:"cols";s:1:"1";s:5:"limit";s:1:"6";s:8:"interval";s:4:"5000";s:9:"auto_play";s:1:"1";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:6:"prefix";s:27:"box-product pink horizontal";s:4:"tabs";a:1:{i:0;s:10:"mostviewed";}s:5:"width";s:3:"480";s:6:"height";s:3:"360";s:12:"itemsperpage";s:1:"2";s:4:"cols";s:1:"2";s:5:"limit";s:1:"6";s:8:"interval";s:4:"7000";s:9:"auto_play";s:1:"1";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:3;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:6:"prefix";s:29:"box-product yellow horizontal";s:4:"tabs";a:1:{i:0;s:6:"latest";}s:5:"width";s:3:"480";s:6:"height";s:3:"360";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:1:"8";s:8:"interval";s:4:"9000";s:9:"auto_play";s:1:"1";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:4;}}', 1),
(0, 'pavtwitter', 'pavtwitter_module', 'a:1:{i:1;a:15:{s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:10:"sort_order";i:2;s:12:"module_class";s:0:"";s:9:"widget_id";s:18:"366766896986591232";s:5:"count";s:1:"1";s:8:"username";s:10:"expandcart";s:5:"theme";s:4:"dark";s:10:"link_color";s:6:"3ABEC0";s:12:"border_color";s:6:"FFFFFF";s:5:"width";s:0:"";s:6:"height";s:3:"100";s:12:"show_replies";s:1:"1";s:6:"chrome";a:4:{i:0;s:8:"noheader";i:1;s:8:"nofooter";i:2;s:9:"noborders";i:3;s:11:"transparent";}}}', 1),
(0, 'special', 'special_module', 'a:3:{i:0;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:3:"160";s:12:"image_height";s:3:"120";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:1;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"160";s:12:"image_height";s:3:"120";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:65:{s:13:"default_theme";s:10:"pav_floral";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"4";s:30:"listing_products_columns_small";s:1:"4";s:34:"listing_products_columns_minismall";s:1:"2";s:14:"category_pzoom";s:1:"1";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:25:"product_customtab_content";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:16:"location_address";s:44:"79-99 Beaver Street, New York, NY 10005, USA";s:17:"location_latitude";s:9:"40.705423";s:18:"location_longitude";s:10:"-74.008616";s:18:"contact_customhtml";a:3:{i:1;s:478:"&lt;p&gt;&lt;b&gt;This is a CMS block edited from admin panel.&lt;/b&gt;&lt;br /&gt;\r\n&lt;b&gt;You can insert any content here.&lt;/b&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;p&gt;Customer Service:&lt;br /&gt;\r\ninfo@yourstore.com&lt;/p&gt;\r\n\r\n&lt;p&gt;Returns and Refunds:&lt;br /&gt;\r\nreturns@yourstore.com&lt;/p&gt;\r\n";i:2;s:478:"&lt;p&gt;&lt;b&gt;This is a CMS block edited from admin panel.&lt;/b&gt;&lt;br /&gt;\r\n&lt;b&gt;You can insert any content here.&lt;/b&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;p&gt;Customer Service:&lt;br /&gt;\r\ninfo@yourstore.com&lt;/p&gt;\r\n\r\n&lt;p&gt;Returns and Refunds:&lt;br /&gt;\r\nreturns@yourstore.com&lt;/p&gt;\r\n";i:3;s:478:"&lt;p&gt;&lt;b&gt;This is a CMS block edited from admin panel.&lt;/b&gt;&lt;br /&gt;\r\n&lt;b&gt;You can insert any content here.&lt;/b&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;p&gt;Customer Service:&lt;br /&gt;\r\ninfo@yourstore.com&lt;/p&gt;\r\n\r\n&lt;p&gt;Returns and Refunds:&lt;br /&gt;\r\nreturns@yourstore.com&lt;/p&gt;\r\n";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"1";s:23:"enable_development_mode";s:0:"";s:6:"layout";s:9:"fullwidth";s:6:"header";s:0:"";s:19:"widget_contact_data";a:3:{i:1;s:328:"&lt;ul&gt;\r\n	&lt;li class=&quot;members&quot;&gt;We have 152 guests and 14 members online&lt;/li&gt;\r\n	&lt;li class=&quot;phone&quot;&gt;Phone: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;fax&quot;&gt;Fax: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;email&quot;&gt;Email: www.leotheme.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:328:"&lt;ul&gt;\r\n	&lt;li class=&quot;members&quot;&gt;We have 152 guests and 14 members online&lt;/li&gt;\r\n	&lt;li class=&quot;phone&quot;&gt;Phone: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;fax&quot;&gt;Fax: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;email&quot;&gt;Email: www.leotheme.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:328:"&lt;ul&gt;\r\n	&lt;li class=&quot;members&quot;&gt;We have 152 guests and 14 members online&lt;/li&gt;\r\n	&lt;li class=&quot;phone&quot;&gt;Phone: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;fax&quot;&gt;Fax: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;email&quot;&gt;Email: www.leotheme.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:8:"standard";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:19:"enable_compress_css";s:0:"";s:17:"exclude_css_files";s:14:"stylesheet.css";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1);

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
--('module', 'pavmegamenu'),
('module', 'pavcustom'),
('module', 'pavtwitter'),
('module', 'pavblog'),
('module', 'pavblogcategory'),
('module', 'pavblogcomment'),
('module', 'pavbloglatest'),
('module', 'pavtestimonial'),
('module', 'pavcontentslider'),
('module', 'pavproductcarousel'),
('module', 'pavnewsletter'),
('module', 'special'),
('module', 'latest'),
('module', 'bestseller');