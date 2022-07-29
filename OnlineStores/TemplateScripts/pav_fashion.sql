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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=45 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(2, '', 1, 0, NULL, NULL, NULL, 'col1=3, col2=3, col3=6', '61', '3', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'parent_menu', NULL, '', '', 0, 0, 0, 1),
(3, '', 1, 0, NULL, NULL, NULL, '', '64', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(5, '', 1, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(7, '', 1, 0, NULL, NULL, NULL, '', '63', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(8, '', 2, 1, NULL, NULL, NULL, '', '27', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '&lt;p&gt;test&lt;/p&gt;\r\n', 0, 0, 0, 1),
(9, '', 2, 1, NULL, NULL, NULL, '', '26', '1', 'category', 0, 1, 'menu', 0, 0, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(10, '', 8, 0, NULL, NULL, NULL, '', '59', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(11, '', 8, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(12, '', 8, 0, NULL, NULL, NULL, '', '61', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(13, '', 8, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(14, '', 8, 0, NULL, NULL, NULL, '', '63', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(17, '', 9, 0, NULL, NULL, NULL, '', '66', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(18, '', 9, 0, NULL, NULL, NULL, '', '67', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(19, '', 9, 0, NULL, NULL, NULL, '', '68', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(20, '', 9, 0, NULL, NULL, NULL, '', '71', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(21, '', 9, 0, NULL, NULL, NULL, '', '72', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(22, '', 9, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(23, '', 9, 0, NULL, NULL, NULL, '', '70', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(24, '', 2, 0, NULL, NULL, NULL, '', '', '1', 'html', 1, 1, 'menu', 0, 0, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '&lt;div class=&quot;pav-menu-video&quot;&gt;&lt;iframe allowfullscreen=&quot;&quot; frameborder=&quot;0&quot; height=&quot;157&quot; src=&quot;http://www.youtube.com/embed/NBuLeA7nNFk&quot; width=&quot;279&quot;&gt;&lt;/iframe&gt;\r\n&lt;h3&gt;Lorem ipsum dolor sit&lt;/h3&gt;\r\n\r\n&lt;p&gt;Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', 0, 0, 0, 1),
(28, '', 3, 0, NULL, NULL, NULL, '', '80', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(29, '', 3, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(30, '', 3, 0, NULL, NULL, NULL, '', '46', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(32, '', 3, 0, NULL, NULL, NULL, '', '78', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(33, '', 3, 0, NULL, NULL, NULL, '', '77', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(34, '', 3, 0, NULL, NULL, NULL, '', '77', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(35, '', 3, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(36, '', 3, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(37, '', 1, 0, NULL, NULL, NULL, '', '65', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(38, '', 1, 0, NULL, NULL, NULL, '', '66', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(40, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 10, 0, '?route=pavblog/blogs', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0, 1),
(41, '', 1, 0, NULL, NULL, NULL, '', '67', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(42, '', 1, 0, NULL, NULL, NULL, '', '68', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(43, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 9, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(44, '', 2, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1);

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
(2, 4, 'Electronics', ''),
(3, 4, 'Accessories', ''),
(2, 3, 'Electronics', ''),
(5, 4, ' Dresses', ''),
(37, 4, 'Tops', ''),
(7, 4, 'New', ''),
(8, 3, 'categories', ''),
(9, 3, 'Printer', ''),
(8, 1, 'categories', ''),
(10, 1, 'Jackets / Coats', ''),
(11, 1, 'Jumpsuits / Rompers', ''),
(12, 1, 'Leather', ''),
(13, 1, 'Leggings', ''),
(14, 1, 'Lingerie', ''),
(44, 3, 'categories-test', ''),
(44, 4, 'categories-test', ''),
(9, 1, 'Printer', ''),
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
(24, 1, 'Lorem ipsum dolor sit ', ''),
(37, 3, 'Tops', ''),
(28, 1, 'Hendrerit', ''),
(28, 2, 'Hendrerit', ''),
(29, 1, 'Litterarum', ''),
(29, 2, 'Litterarum', ''),
(30, 1, 'Macs', ''),
(30, 2, 'Macs', ''),
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
(38, 3, 'Bottoms', ''),
(40, 4, 'Blog', ''),
(24, 3, 'Lorem ipsum dolor sit ', ''),
(24, 4, 'Lorem ipsum dolor sit ', ''),
(9, 4, 'Printer', ''),
(2, 1, 'Outerwear ', ''),
(5, 3, ' Dresses', ''),
(3, 3, 'Accessories', ''),
(3, 1, 'Accessories', ''),
(7, 3, 'New', ''),
(7, 1, 'New', ''),
(37, 1, 'Tops', ''),
(38, 4, 'Bottoms', ''),
(41, 4, 'Bags', ''),
(41, 3, 'Bags', ''),
(41, 1, 'Bags', ''),
(42, 4, 'Shoes', ''),
(42, 3, 'Shoes', ''),
(42, 1, 'Shoes', ''),
(43, 1, 'Sale', ''),
(43, 3, 'Sale', ''),
(43, 4, 'Sale', ''),
(8, 4, 'categories', ''),
(10, 3, 'Jackets / Coats', ''),
(10, 4, 'Jackets / Coats', ''),
(11, 3, 'Jumpsuits / Rompers', ''),
(11, 4, 'Jumpsuits / Rompers', ''),
(12, 3, 'Leather', ''),
(12, 4, 'Leather', ''),
(13, 3, 'Leggings', ''),
(13, 4, 'Leggings', ''),
(14, 3, 'Lingerie', ''),
(14, 4, 'Lingerie', ''),
(44, 1, 'categories-test', ''),
(40, 3, 'Blog', ''),
(40, 1, 'Blog', ''),
(5, 1, ' Dresses', ''),
(38, 1, 'Bottoms', '');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `megamenu_widgets`
--

INSERT INTO `megamenu_widgets` (`id`, `name`, `type`, `params`, `store_id`) VALUES
(1, 'Video Opencart Installation', 'video_code', 'a:1:{s:10:"video_code";s:168:"&lt;iframe width=&quot;300&quot; height=&quot;315&quot; src=&quot;//www.youtube.com/embed/M1USNjKKRWk&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;";}', 0),
(2, 'Text wedget', 'html', 'a:1:{s:4:"html";a:3:{i:1;s:196:"This is Photoshop''s version  of Lorem Ipsum. Proin grvida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. ";i:3;s:196:"This is Photoshop''s version  of Lorem Ipsum. Proin grvida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. ";i:4;s:196:"This is Photoshop''s version  of Lorem Ipsum. Proin grvida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. ";}}', 0),
(3, 'Products Latest', 'product_list', 'a:4:{s:9:"list_type";s:6:"newest";s:5:"limit";s:1:"3";s:11:"image_width";s:2:"81";s:12:"image_height";s:3:"117";}', 0),
(4, 'Products In Cat 20', 'product_category', 'a:4:{s:11:"category_id";s:2:"20";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(5, 'Manufactures', 'banner', 'a:4:{s:8:"group_id";s:1:"8";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:5:"limit";s:2:"12";}', 0),
(6, 'expandcart Feed', 'feed', 'a:1:{s:8:"feed_url";s:55:"http://www.expandcart.com/expandcartfeeds.feed?type=rss";}', 0),
(7, 'Sub Category Widget', 'image', 'a:3:{s:10:"image_path";s:28:"data/demo/sub-categories.png";s:11:"image_width";s:3:"340";s:12:"image_height";s:3:"180";}', 0),
(8, 'Information', 'html', 'a:1:{s:4:"html";a:3:{i:1;s:675:"&lt;div class=&quot;box&quot;&gt;\r\n		&lt;ul class=&quot;&quot;&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;About Us&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Delivery Information&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Terms &amp;amp; Conditions&lt;/a&gt;&lt;/li&gt;\r\n		&lt;/ul&gt;				\r\n&lt;/div&gt;";i:3;s:675:"&lt;div class=&quot;box&quot;&gt;\r\n		&lt;ul class=&quot;&quot;&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;About Us&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Delivery Information&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Terms &amp;amp; Conditions&lt;/a&gt;&lt;/li&gt;\r\n		&lt;/ul&gt;				\r\n&lt;/div&gt;";i:4;s:675:"&lt;div class=&quot;box&quot;&gt;\r\n		&lt;ul class=&quot;&quot;&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=4&quot;&gt;About Us&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=6&quot;&gt;Delivery Information&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=3&quot;&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;\r\n			&lt;li&gt;&lt;a href=&quot;index.php?route=information/information&amp;amp;information_id=5&quot;&gt;Terms &amp;amp; Conditions&lt;/a&gt;&lt;/li&gt;\r\n		&lt;/ul&gt;				\r\n&lt;/div&gt;";}}', 0);

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
(1, 'Slideshow', 'a:28:{s:5:"title";s:9:"Slideshow";s:5:"delay";s:4:"9000";s:9:"fullwidth";s:0:"";s:5:"width";s:4:"1170";s:6:"height";s:3:"600";s:12:"touch_mobile";s:1:"1";s:13:"stop_on_hover";s:1:"0";s:12:"shuffle_mode";s:1:"0";s:14:"image_cropping";s:1:"1";s:11:"shadow_type";s:1:"0";s:14:"show_time_line";s:1:"0";s:18:"time_line_position";s:3:"top";s:16:"background_color";s:7:"#d9d9d9";s:6:"margin";s:1:"0";s:7:"padding";s:1:"0";s:16:"background_image";s:1:"0";s:14:"background_url";s:0:"";s:14:"navigator_type";s:6:"bullet";s:16:"navigator_arrows";s:16:"verticalcentered";s:16:"navigation_style";s:5:"round";s:17:"offset_horizontal";s:1:"0";s:15:"offset_vertical";s:1:"0";s:14:"show_navigator";s:1:"1";s:20:"hide_navigator_after";s:3:"200";s:15:"thumbnail_width";s:3:"100";s:16:"thumbnail_height";s:2:"50";s:16:"thumbnail_amount";s:1:"5";s:17:"hide_screen_width";s:0:"";}');

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
(1, 'image slideshow1', 0, 1, 'a:16:{s:2:"id";s:1:"1";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"image slideshow1";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"1";s:12:"slider_image";s:24:"data/slider/slide-01.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"large_white_text";s:13:"layer_caption";s:11:"Introducing";s:15:"layer_animation";s:3:"sft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"151";s:10:"layer_left";s:3:"517";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2487";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:27:"data/slider/bulet-slide.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:3:"sft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"194";s:10:"layer_left";s:3:"473";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2487";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"cus_color";s:13:"layer_caption";s:22:"autumn collection 2013";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"230";s:10:"layer_left";s:3:"295";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2869";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:234:"Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis&lt;br/&gt; bibdum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed &lt;br/&gt;odio sit amet nibh vulputate cursus a sit amet mauris";s:15:"layer_animation";s:3:"sfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"301";s:10:"layer_left";s:3:"364";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:3:"ltb";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"3983";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"tp-button";s:13:"layer_caption";s:8:"shop now";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"405";s:10:"layer_left";s:3:"528";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:15:"randomrotateout";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"5252";}}}', 'data/slider/slide-01.jpg', 1, 1),
(16, 'image slideshow2', 0, 1, 'a:16:{s:2:"id";s:2:"16";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"image slideshow2";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"16";s:12:"slider_image";s:24:"data/slider/slide-02.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"large_white_text";s:13:"layer_caption";s:17:"women’s fashion";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"147";s:10:"layer_left";s:3:"481";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:27:"data/slider/bulet-slide.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"186";s:10:"layer_left";s:3:"469";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"cus_color";s:13:"layer_caption";s:17:"fall _ASM_ winter";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"228";s:10:"layer_left";s:3:"411";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:204:"Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan&lt;br/&gt; ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non &lt;br/&gt; mauris vitae erat consequat";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"298";s:10:"layer_left";s:3:"345";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"tp-button ";s:13:"layer_caption";s:8:"shop now";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"401";s:10:"layer_left";s:3:"512";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}}}', 'data/slider/slide-02.jpg', 1, 2),
(17, 'image slideshow3', 0, 1, 'a:16:{s:2:"id";s:2:"17";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"image slideshow3";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"17";s:12:"slider_image";s:24:"data/slider/slide-03.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:16:"large_white_text";s:13:"layer_caption";s:13:"men’s style";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"138";s:10:"layer_left";s:3:"524";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:27:"data/slider/bulet-slide.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"178";s:10:"layer_left";s:3:"492";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"cus_color";s:13:"layer_caption";s:17:"offer of the week";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"231";s:10:"layer_left";s:3:"360";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:121:"Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos &lt;br/&gt;himenaeos auris in erat justo";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"306";s:10:"layer_left";s:3:"329";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:9:"tp-button";s:13:"layer_caption";s:8:"shop now";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"412";s:10:"layer_left";s:3:"528";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}}}', 'data/slider/slide-03.jpg', 1, 3);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10366 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'pavcarousel', 'pavcarousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:2:"10";s:7:"columns";s:1:"6";s:5:"width";s:3:"180";s:6:"height";s:2:"70";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavnewsletter', 'pavnewsletter_module', 'a:1:{i:1;a:5:{s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:2;s:11:"description";s:75:"&lt;p&gt;Putate cursus a sit amet maurisbi accumsan ipsum velit&lt;/p&gt;\r\n";}}', 1),
(0, 'config', 'config_image_additional_width', '81', 0),
(0, 'config', 'config_image_product_height', '400', 0),
(0, 'config', 'config_image_product_width', '279', 0),
(0, 'config', 'config_image_popup_height', '717', 0),
(0, 'config', 'config_image_popup_width', '500', 0),
(0, 'config', 'config_image_thumb_height', '717', 0),
(0, 'config', 'config_image_thumb_width', '500', 0),
(0, 'config', 'config_image_category_height', '270', 0),
(0, 'config', 'config_image_category_width', '873', 0),
(0, 'pavbannermanager', 'pavbannermanager_module', 'a:1:{i:0;a:7:{s:6:"prefix";s:12:"prefix_class";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:13:"banner_layout";s:1:"1";s:7:"banners";a:5:{i:1;a:5:{s:5:"image";s:26:"data/banner/img-baner1.jpg";s:4:"link";s:1:"#";s:5:"width";s:3:"378";s:6:"height";s:3:"250";s:7:"caption";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}}i:2;a:5:{s:5:"image";s:26:"data/banner/img-baner3.jpg";s:4:"link";s:1:"#";s:5:"width";s:3:"378";s:6:"height";s:3:"170";s:7:"caption";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}}i:3;a:5:{s:5:"image";s:26:"data/banner/img-baner5.jpg";s:4:"link";s:1:"#";s:5:"width";s:3:"378";s:6:"height";s:3:"438";s:7:"caption";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}}i:4;a:5:{s:5:"image";s:26:"data/banner/img-baner2.jpg";s:4:"link";s:1:"#";s:5:"width";s:3:"378";s:6:"height";s:3:"250";s:7:"caption";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}}i:5;a:5:{s:5:"image";s:26:"data/banner/img-baner4.jpg";s:4:"link";s:1:"#";s:5:"width";s:3:"378";s:6:"height";s:3:"170";s:7:"caption";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}}}}}', 1),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:1:{i:1;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:3:"270";s:6:"height";s:3:"200";s:4:"cols";s:1:"3";s:5:"limit";s:1:"3";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavgallery', 'pavgallery_module', 'a:1:{i:0;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'pavcustom', 'pavcustom_module', 'a:1:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:11:"Banner news";i:2;s:10:"إعلان";}s:11:"description";a:2:{i:1;s:139:"&lt;div class=&quot;box box-banner&quot;&gt;&lt;img alt=&quot;banner&quot; src=&quot;image/data/banner/img-news.jpg&quot; /&gt;&lt;/div&gt;";i:2;s:139:"&lt;div class=&quot;box box-banner&quot;&gt;&lt;img alt=&quot;banner&quot; src=&quot;image/data/banner/img-news.jpg&quot; /&gt;&lt;/div&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavproducttabs', 'pavproducttabs_module', 'a:1:{i:1;a:11:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";a:5:{i:0;s:6:"latest";i:1;s:8:"featured";i:2;s:10:"bestseller";i:3;s:7:"special";i:4;s:10:"mostviewed";}s:5:"width";s:3:"500";s:6:"height";s:3:"717";s:12:"itemsperpage";s:1:"8";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'config', 'config_image_cart_height', '77', 0),
(0, 'config', 'config_image_cart_width', '65', 0),
(0, 'config', 'config_image_wishlist_width', '65', 0),
(0, 'config', 'config_image_wishlist_height', '77', 0),
(0, 'config', 'config_image_compare_width', '65', 0),
(0, 'config', 'config_image_compare_height', '77', 0),
(0, 'config', 'config_image_related_height', '717', 0),
(0, 'config', 'config_image_related_width', '500', 0),
(0, 'config', 'config_image_additional_height', '117', 0),
(0, 'pavsliderlayer', 'pavsliderlayer_module', 'a:1:{i:0;a:5:{s:8:"group_id";s:1:"1";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavblog_frontmodules', 'pavblog_frontmodules', 'a:1:{s:7:"modules";a:3:{i:0;s:15:"pavblogcategory";i:1;s:14:"pavblogcomment";i:2;s:13:"pavbloglatest";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"4";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'banner', 'banner_module', 'a:1:{i:1;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"277";s:6:"height";s:3:"254";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":1,"subwidth":1150,"cols":3,"group":0,"id":2,"rows":[{"cols":[{"colwidth":2,"type":"menu"},{"widgets":"wid-8","colwidth":2},{"widgets":"wid-2","colwidth":4},{"widgets":"wid-7","colwidth":4}]}]},{"submenu":1,"cols":1,"group":1,"id":8,"rows":[]},{"submenu":1,"subwidth":350,"id":5,"cols":1,"group":0,"rows":[{"cols":[{"widgets":"wid-3","colclass":"hidden-sub-menu","colwidth":12}]}]},{"submenu":1,"cols":1,"group":0,"id":3,"rows":[{"cols":[{"colwidth":12,"type":"menu"}]}]},{"submenu":1,"cols":1,"group":0,"id":4,"rows":[{"cols":[{"type":"menu","colwidth":12}]}]},{"submenu":1,"cols":1,"group":0,"id":31,"rows":[{"cols":[{"type":"menu","colwidth":12}]}]}]', 0),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'special', 'special_module', 'a:3:{i:0;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"81";s:12:"image_height";s:3:"117";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"81";s:12:"image_height";s:3:"117";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:2;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"81";s:12:"image_height";s:3:"117";s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavmodulemanager', 'pavmodulemanager', 'a:3:{s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:7:"modules";a:3:{i:0;s:9:"pavcustom";i:1;s:18:"pavproductcarousel";i:2;s:10:"pavtwitter";}}', 1),
(0, 'pavtwitter', 'pavtwitter_module', 'a:1:{i:1;a:14:{s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:3;s:9:"widget_id";s:18:"366766896986591232";s:5:"count";s:1:"2";s:8:"username";s:10:"expandcart";s:5:"theme";s:5:"light";s:10:"link_color";s:6:"CC0000";s:12:"border_color";s:6:"FFFFFF";s:5:"width";s:3:"300";s:6:"height";s:3:"500";s:12:"show_replies";s:1:"1";s:6:"chrome";a:4:{i:0;s:8:"noheader";i:1;s:8:"nofooter";i:2;s:9:"noborders";i:3;s:11:"noscrollbar";}}}', 1),
(0, 'pavproductcarousel', 'pavproductcarousel_module', 'a:2:{i:1;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}s:12:"module_class";s:0:"";s:4:"tabs";a:1:{i:0;s:10:"bestseller";}s:5:"width";s:3:"550";s:6:"height";s:3:"650";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}s:12:"module_class";s:0:"";s:4:"tabs";a:1:{i:0;s:10:"mostviewed";}s:5:"width";s:3:"550";s:6:"height";s:3:"650";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"12";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:3;}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"900";s:15:"general_lheight";s:3:"350";s:14:"general_swidth";s:3:"600";s:15:"general_sheight";s:3:"250";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"80";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"600";s:15:"general_cheight";s:3:"250";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"3";s:22:"cat_leading_image_type";s:1:"l";s:24:"cat_secondary_image_type";s:1:"l";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"0";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:12:"100858303516";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'carousel', 'carousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}}', 1),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'category', 'category_module', 'a:2:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:67:{s:13:"default_theme";s:11:"pav_fashion";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"0";s:30:"listing_products_columns_small";s:1:"0";s:34:"listing_products_columns_minismall";s:1:"0";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"1";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:44:"79-99 Beaver Street, New York, NY 10005, USA";s:17:"location_latitude";s:9:"40.705423";s:18:"location_longitude";s:10:"-74.008616";s:18:"contact_customhtml";a:2:{i:1;s:732:"&lt;p&gt;&lt;b&gt;&lt;small&gt;This is a CMS block edited from admin panel.&lt;/small&gt;&lt;br /&gt;\r\n&lt;small&gt;You can insert any content here.&lt;/small&gt;&lt;/b&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;b&gt;&lt;small&gt;Customer Service:&lt;/small&gt;&lt;/b&gt;&lt;br /&gt;\r\n&lt;a href=&quot;mailto:info@yourstore.com&quot;&gt;info@yourstore.com&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;b&gt;&lt;small&gt;Returns and Refunds:&lt;/small&gt;&lt;/b&gt;&lt;br /&gt;\r\n&lt;a href=&quot;mailto:returns@yourstore.com&quot;&gt;returns@yourstore.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:1024:"&lt;p&gt;&lt;b&gt;&lt;small&gt;هذه وحدة محتوى يمكن تعديلها من لوحة التحكم.&lt;/small&gt;&lt;br /&gt;\r\n&lt;small&gt;يمكنك إضافة أي محتوى هنا.&lt;/small&gt;&lt;/b&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;من كما الحرة والحزب, أدنى أوسع حيث من. أما ثم جمعت أواخر اعتداء, بـ مشروط المضي مدن. بين اعلان مسؤولية الشّعبين تم, بال قد جدول تعديل الشرق،, وفي أن سبتمبر وأكثرها. تطوير بالحرب ابتدعها بلا و, لم بعد النفط وتتحمّل اليابان.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;b&gt;&lt;small&gt;خدمة العملاء:&lt;/small&gt;&lt;/b&gt;&lt;br /&gt;\r\n&lt;a href=&quot;mailto:info@yourstore.com&quot;&gt;info@yourstore.com&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;b&gt;&lt;small&gt;المرتجعات:&lt;/small&gt;&lt;/b&gt;&lt;br /&gt;\r\n&lt;a href=&quot;mailto:returns@yourstore.com&quot;&gt;returns@yourstore.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:6:"layout";s:9:"fullwidth";s:9:"logo_type";s:10:"logo-theme";s:9:"quickview";s:1:"1";s:16:"footer_logo_data";a:2:{i:1;s:142:"&lt;div class=&quot;box logo-ft&quot;&gt;&lt;img alt=&quot;logo-footer&quot; src=&quot;image/data/demo/logo_footer.png&quot; /&gt;&lt;/div&gt;";i:2;s:142:"&lt;div class=&quot;box logo-ft&quot;&gt;&lt;img alt=&quot;logo-footer&quot; src=&quot;image/data/demo/logo_footer.png&quot; /&gt;&lt;/div&gt;";}s:10:"about_data";a:2:{i:1;s:266:"&lt;p&gt;Sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio sit amet nibh vulputate cursus a sit amet mauris. Morbi accumsan ipsum velit. Nam nec tellus a odio tincidunt auctor a ornare odio.&lt;/p&gt;\r\n";i:2;s:416:"&lt;p&gt;من كما الحرة والحزب, أدنى أوسع حيث من. أما ثم جمعت أواخر اعتداء, بـ مشروط المضي مدن. بين اعلان مسؤولية الشّعبين تم, بال قد جدول تعديل الشرق،, وفي أن سبتمبر وأكثرها. تطوير بالحرب ابتدعها بلا و, لم بعد النفط وتتحمّل اليابان.&lt;/p&gt;\r\n";}s:11:"social_data";a:2:{i:1;s:939:"&lt;ul class=&quot;social&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;facebook&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-facebook stack&quot;&gt;&lt;span&gt;Facebook&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-twitter stack&quot;&gt;&lt;span&gt;Twitter&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;google&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-google-plus stack&quot;&gt;&lt;span&gt;Google Plus&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;youtube&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-youtube stack&quot;&gt;&lt;span&gt;Youtube&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;skype&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-skype stack&quot;&gt;&lt;span&gt;Skype&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:960:"&lt;ul class=&quot;social&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;facebook&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-facebook stack&quot;&gt;&lt;span&gt;فيسبوك&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-twitter stack&quot;&gt;&lt;span&gt;تويتر&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;google&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-google-plus stack&quot;&gt;&lt;span&gt;جوجل بلس&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;youtube&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-youtube stack&quot;&gt;&lt;span&gt;يوتيوب&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;skype&quot; href=&quot;#&quot;&gt;&lt;i class=&quot;fa fa-skype stack&quot;&gt;&lt;span&gt;سكايب&lt;/span&gt;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:12:"call_us_data";a:2:{i:1;s:524:"&lt;div class=&quot;box-services&quot;&gt;\r\n&lt;p&gt;Monday - Friday .................. 8.00 to 18.00&lt;/p&gt;\r\n\r\n&lt;p&gt;Saturday ......................... 9.00 to 21.00&lt;/p&gt;\r\n\r\n&lt;p&gt;Sunday ........................... 10.00 to 21.00&lt;/p&gt;\r\n&lt;span class=&quot;iconbox pull-left&quot;&gt;&lt;i class=&quot;fa fa-phone&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;&lt;span&gt;Call us: &lt;strong&gt; 0123 456 789&lt;/strong&gt;&lt;/span&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:564:"&lt;div class=&quot;box-services&quot;&gt;\r\n&lt;p&gt;الإثنين - الجمعة .................. 8.00 إلى 18.00&lt;/p&gt;\r\n\r\n&lt;p&gt;السبت ......................... 9.00 إلى 21.00&lt;/p&gt;\r\n\r\n&lt;p&gt;الأحد ........................... 10.00 إلى 21.00&lt;/p&gt;\r\n&lt;span class=&quot;iconbox pull-left&quot;&gt;&lt;i class=&quot;fa fa-phone&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;&lt;span&gt;إتصل بنا: &lt;strong&gt; 0123 456 789&lt;/strong&gt;&lt;/span&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:15:"contact_us_data";a:2:{i:1;s:343:"&lt;address&gt;&lt;strong&gt;Warehouse &amp;amp; Offices&lt;/strong&gt;&lt;br /&gt;\r\n12345 Street name, California, USA&lt;br /&gt;\r\n0123 456 789 / 0123 456 788&lt;/address&gt;\r\n\r\n&lt;address&gt;&lt;strong&gt;Retail Store&lt;/strong&gt;&lt;br /&gt;\r\n54321 Street name, California, USA&lt;br /&gt;\r\n0123 456 789 / 0123 456 788&lt;/address&gt;\r\n";i:2;s:478:"&lt;address&gt;&lt;strong&gt;المكاتب و المخازن&lt;/strong&gt;&lt;br /&gt;\r\n12345 إسم الشارع، الرياض، المملكة العربية السعودية&lt;br /&gt;\r\n0123 456 789 / 0123 456 788&lt;/address&gt;\r\n\r\n&lt;address&gt;&lt;strong&gt;متجر بيع بالتجزئة&lt;/strong&gt;&lt;br /&gt;\r\n54321 إسم الشارع، الرياض، المملكة العربية السعودية&lt;br /&gt;\r\n0123 456 789 / 0123 456 788&lt;/address&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:8:"standard";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1),
(0, 'pavnewsletter_config', 'pavnewsletter_config', 'a:9:{s:19:"custom_email_config";s:1:"0";s:8:"protocal";s:4:"mail";s:5:"email";s:0:"";s:9:"smtp_host";s:0:"";s:13:"smtp_username";s:0:"";s:13:"smtp_password";s:0:"";s:9:"smtp_port";s:0:"";s:12:"smtp_timeout";s:1:"5";s:13:"retries_count";s:1:"3";}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=489 ;

--
-- Dumping data for table `extension`
--

INSERT INTO `extension` (`type`, `code`) VALUES
('module', 'banner'),
('module', 'carousel'),
('module', 'category'),
('module', 'affiliate'),
('module', 'account'),
('module', 'themecontrol'),
('module', 'pavbloglatest'),
('module', 'pavcustom'),
('module', 'pavnewsletter'),
('module', 'special'),
('module', 'latest'),
('module', 'bestseller'),
('module', 'featured'),
('module', 'pavblog'),
('module', 'pavblogcomment'),
('module', 'pavblogcategory'),
--('module', 'pavcarousel'),
('module', 'information'),
('module', 'pavsliderlayer'),
--('module', 'pavmegamenu'),
('module', 'pavproducttabs'),
('module', 'pavbannermanager');