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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=42 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(2, '', 1, 0, NULL, NULL, NULL, 'col1=3, col2=3, col3=6', '20', '3', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '&lt;div class=&quot;pav-menu-video&quot;&gt;&lt;iframe allowfullscreen=&quot;&quot; frameborder=&quot;0&quot; height=&quot;157&quot; src=&quot;http://www.youtube.com/embed/NBuLeA7nNFk&quot; width=&quot;270&quot;&gt;&lt;/iframe&gt;&lt;/div&gt;\r\n\r\n&lt;h3&gt;Lorem ipsum dolor sit&lt;/h3&gt;\r\n\r\n&lt;p&gt;Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.&lt;/p&gt;\r\n', '', 0, 0, 0, 1),
(3, '', 1, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(5, '', 1, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(7, '', 1, 0, NULL, NULL, NULL, '', '17', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(8, '', 2, 1, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '&lt;p&gt;test&lt;/p&gt;\r\n', 0, 0, 0, 1),
(9, '', 2, 1, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(10, '', 8, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, 'index.php?route=product/category&amp;path=20', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(11, '', 8, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, 'index.php?route=product/category&amp;path=20', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(12, '', 8, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, 'index.php?route=product/category&amp;path=20', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(13, '', 8, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(14, '', 8, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(15, '', 8, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(16, '', 8, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(17, '', 9, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(18, '', 9, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(19, '', 9, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(20, '', 9, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(21, '', 9, 0, NULL, NULL, NULL, '', '72', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 0),
(22, '', 9, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(23, '', 9, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0, 1),
(24, '', 2, 0, NULL, NULL, NULL, '', '', '1', 'html', 1, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '&lt;div class=&quot;pav-menu-video&quot;&gt;&lt;iframe allowfullscreen=&quot;&quot; frameborder=&quot;0&quot; height=&quot;157&quot; src=&quot;http://www.youtube.com/embed/NBuLeA7nNFk&quot; width=&quot;279&quot;&gt;&lt;/iframe&gt;\r\n&lt;h3&gt;Lorem ipsum dolor sit&lt;/h3&gt;\r\n\r\n&lt;p&gt;Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', 0, 0, 0, 0),
(25, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(26, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(27, '', 3, 0, NULL, NULL, NULL, '', '73', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(28, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(29, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(31, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(37, '', 1, 0, NULL, NULL, NULL, '', '17', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(40, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0, 1),
(41, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 99, 0, '?route=pavblog/blogs', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1);

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
(2, 3, 'إلكترونيات', ''),
(3, 2, 'Digital', ''),
(25, 3, 'Aliquam', ''),
(31, 3, 'Sollemnes', ''),
(5, 1, 'bedroom', ''),
(37, 2, 'Watches', ''),
(7, 3, 'مكتب', ''),
(8, 2, 'Computers', ''),
(9, 2, 'Mobiles', ''),
(8, 3, 'Computers', ''),
(10, 1, 'Duis tempor', ''),
(11, 3, 'Pellentesque eget ', ''),
(12, 3, 'Nam nunc ante', ''),
(13, 1, 'Condimentum eu', ''),
(14, 1, 'Lehicula lorem', ''),
(15, 1, 'Integer semper', ''),
(16, 1, 'Sollicitudin lacus', ''),
(9, 3, 'Printer', ''),
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
(37, 3, 'الساعات', ''),
(25, 2, 'Aliquam', ''),
(25, 1, 'Aliquam', ''),
(27, 2, 'Consectetuer', ''),
(27, 1, 'Consectetuer', ''),
(28, 2, 'Hendrerit', ''),
(28, 1, 'Hendrerit', ''),
(29, 2, 'Litterarum', ''),
(29, 1, 'Litterarum', ''),
(31, 2, 'Sollemnes', ''),
(31, 1, 'Sollemnes', ''),
(26, 1, 'Claritas', ''),
(26, 2, 'Claritas', ''),
(26, 3, 'Claritas', ''),
(28, 3, 'Hendrerit', ''),
(29, 3, 'Litterarum', ''),
(2, 2, 'Electronics', ''),
(40, 1, 'Home', ''),
(3, 3, 'رقمي', ''),
(7, 2, 'Office', ''),
(7, 1, 'Office', ''),
(5, 2, 'Books', ''),
(40, 2, 'Home', ''),
(40, 3, 'منزل', ''),
(41, 1, 'Blogs', ''),
(41, 2, 'Blogs', ''),
(41, 3, 'Blogs', ''),
(10, 2, 'Duis tempor', ''),
(12, 2, 'Nam nunc ante', ''),
(12, 1, 'Nam nunc ante', ''),
(11, 2, 'Pellentesque eget', ''),
(11, 1, 'Pellentesque eget ', ''),
(8, 1, 'Sofa', ''),
(10, 3, 'Duis tempor', ''),
(13, 2, 'Condimentum eu', ''),
(13, 3, 'Condimentum eu', ''),
(14, 2, 'Lehicula lorem', ''),
(14, 3, 'Lehicula lorem', ''),
(15, 2, 'Integer semper', ''),
(15, 3, 'Integer semper', ''),
(16, 2, 'Sollicitudin lacus', ''),
(16, 3, 'Sollicitudin lacus', ''),
(17, 2, 'Nam ipsum ', ''),
(17, 3, 'Nam ipsum ', ''),
(18, 2, 'Curabitur turpis ', ''),
(18, 3, 'Curabitur turpis ', ''),
(9, 1, 'Chair', ''),
(19, 3, 'Molestie eu mattis ', ''),
(20, 3, 'Suspendisse eu ', ''),
(22, 3, 'Mauris mattis', ''),
(23, 3, 'Lacus sed iaculis ', ''),
(2, 1, 'living room', ''),
(3, 1, 'Dining', ''),
(37, 1, 'Kitchen', ''),
(5, 3, 'كتب', '');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pavoslidergroups`
--

INSERT INTO `pavoslidergroups` (`id`, `title`, `params`) VALUES
(3, 'slide-home', 'a:28:{s:5:"title";s:10:"slide-home";s:5:"delay";s:4:"9000";s:9:"fullwidth";s:0:"";s:5:"width";s:4:"1170";s:6:"height";s:3:"595";s:12:"touch_mobile";s:1:"1";s:13:"stop_on_hover";s:1:"1";s:12:"shuffle_mode";s:1:"0";s:14:"image_cropping";s:1:"0";s:11:"shadow_type";s:1:"2";s:14:"show_time_line";s:1:"1";s:18:"time_line_position";s:6:"bottom";s:16:"background_color";s:7:"#d9d9d9";s:6:"margin";s:12:"0px 0px 20px";s:7:"padding";s:3:"0px";s:16:"background_image";s:1:"0";s:14:"background_url";s:0:"";s:14:"navigator_type";s:6:"bullet";s:16:"navigator_arrows";s:16:"verticalcentered";s:16:"navigation_style";s:5:"round";s:17:"offset_horizontal";s:1:"0";s:15:"offset_vertical";s:2:"20";s:14:"show_navigator";s:1:"1";s:20:"hide_navigator_after";s:3:"200";s:15:"thumbnail_width";s:3:"100";s:16:"thumbnail_height";s:2:"50";s:16:"thumbnail_amount";s:1:"5";s:17:"hide_screen_width";s:0:"";}');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `pavosliderlayers`
--

INSERT INTO `pavosliderlayers` (`id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`) VALUES
(4, 'slider1', 0, 3, 'a:16:{s:2:"id";s:1:"4";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:7:"slider1";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"4";s:12:"slider_image";s:20:"data/Slide/slide.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:0:{}}', 'data/Slide/slide.jpg', 1, 0),
(5, 'slider2', 0, 3, 'a:16:{s:2:"id";s:1:"5";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:7:"slider2";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"5";s:12:"slider_image";s:21:"data/Slide/slide2.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:0:{}}', 'data/Slide/slide2.jpg', 1, 0),
(6, 'slider3', 0, 3, 'a:16:{s:2:"id";s:1:"6";s:15:"slider_group_id";s:1:"3";s:12:"slider_title";s:7:"slider3";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"300";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"6";s:12:"slider_image";s:21:"data/Slide/slide3.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:0:{}}', 'data/Slide/slide3.jpg', 1, 0);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5508 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'category', 'category_module', 'a:2:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'banner', 'banner_module', 'a:3:{i:0;a:7:{s:9:"banner_id";s:1:"6";s:5:"width";s:3:"182";s:6:"height";s:3:"182";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:1;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"279";s:6:"height";s:3:"376";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:2;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"279";s:6:"height";s:3:"376";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":1,"subwidth":700,"id":2,"cols":3,"group":0,"rows":[{"cols":[{"colwidth":3,"type":"menu"},{"type":"menu","colwidth":3},{"colwidth":6,"type":"menu"}]}]},{"submenu":1,"cols":1,"group":1,"id":8,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":9,"rows":[]},{"submenu":1,"subwidth":250,"cols":1,"group":0,"id":3,"rows":[{"cols":[{"colwidth":12,"type":"menu"}]}]}]', 0),
(0, 'config', 'config_image_cart_height', '90', 0),
(0, 'config', 'config_image_cart_width', '90', 0),
(0, 'config', 'config_image_wishlist_height', '90', 0),
(0, 'config', 'config_image_wishlist_width', '90', 0),
(0, 'config', 'config_image_compare_height', '90', 0),
(0, 'config', 'config_image_compare_width', '90', 0),
(0, 'config', 'config_image_related_height', '157', 0),
(0, 'config', 'config_image_related_width', '157', 0),
(0, 'config', 'config_image_additional_height', '75', 0),
(0, 'config', 'config_image_product_height', '230', 0),
(0, 'config', 'config_image_additional_width', '75', 0),
(0, 'config', 'config_image_product_width', '190', 0),
(0, 'config', 'config_image_popup_height', '500', 0),
(0, 'config', 'config_image_popup_width', '500', 0),
(0, 'config', 'config_image_thumb_height', '380', 0),
(0, 'config', 'config_image_category_width', '880', 0),
(0, 'config', 'config_image_thumb_width', '380', 0),
(0, 'config', 'config_image_category_height', '325', 0),
(0, 'pavproducttabs', 'pavproducttabs_module', 'a:1:{i:1;a:12:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:12:"module_class";s:0:"";s:4:"tabs";a:5:{i:0;s:6:"latest";i:1;s:8:"featured";i:2;s:10:"bestseller";i:3;s:7:"special";i:4;s:10:"mostviewed";}s:5:"width";s:3:"200";s:6:"height";s:3:"200";s:12:"itemsperpage";s:1:"5";s:4:"cols";s:1:"5";s:5:"limit";s:1:"5";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"promotion";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavmodulemanager', 'pavmodulemanager', 'a:3:{s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:7:"modules";a:4:{i:0;s:9:"pavcustom";i:1;s:18:"pavproductcarousel";i:2;s:14:"pavproducttabs";i:3;s:10:"pavtwitter";}}', 1),
(0, 'pavsliderlayer', 'pavsliderlayer_module', 'a:1:{i:0;a:5:{s:8:"group_id";s:1:"3";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}}', 1),
--(0, 'pavcarousel', 'pavcarousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:2:"15";s:5:"limit";s:1:"6";s:7:"columns";s:1:"6";s:5:"width";s:3:"152";s:6:"height";s:2:"85";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'featured', 'featured_product', '68,58,78,61,65', 0),
(0, 'pavnewsletter', 'pavnewsletter_module', 'a:1:{i:1;a:5:{s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"0";s:10:"sort_order";i:1;s:11:"description";s:28:"&lt;p&gt;asdffas&lt;/p&gt;\r\n";}}', 1),
(0, 'pavcustom', 'pavcustom_module', 'a:2:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:12:"introduction";i:2;s:12:"introduction";}s:11:"description";a:2:{i:1;s:777:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;hidden-xs&quot;&gt;\r\n&lt;div class=&quot;col-sm-4 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=20&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/Promotion/category.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-4 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=18&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/Promotion/category2.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-4 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/Promotion/category3.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;";i:2;s:777:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;hidden-xs&quot;&gt;\r\n&lt;div class=&quot;col-sm-4 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=20&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/Promotion/category.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-4 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=18&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/Promotion/category2.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-4 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/Promotion/category3.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:12:"module_class";s:9:"hidden-xs";s:10:"sort_order";s:1:"2";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:24:"introduction-	 advertise";i:2;s:24:"introduction-	 advertise";}s:11:"description";a:2:{i:1;s:238:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;hidden-xs&quot;&gt;\r\n&lt;div class=&quot;col-sm-12&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/Promotion/category4.png&quot; /&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:238:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;hidden-xs&quot;&gt;\r\n&lt;div class=&quot;col-sm-12&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/Promotion/category4.png&quot; /&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";i:1;}}', 1),
(0, 'special', 'special_module', 'a:4:{i:0;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"205";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:1;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"205";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"205";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:3;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"205";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"5";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:1:{i:1;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:3:"238";s:6:"height";s:3:"129";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"833";s:15:"general_lheight";s:3:"450";s:14:"general_swidth";s:3:"252";s:15:"general_sheight";s:3:"136";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"80";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"383";s:15:"general_cheight";s:3:"207";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"5";s:22:"cat_leading_image_type";s:1:"l";s:24:"cat_secondary_image_type";s:1:"s";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"0";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:12:"100858303516";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'pavblog_frontmodules', 'pavblog_frontmodules', 'a:1:{s:7:"modules";a:3:{i:0;s:15:"pavblogcategory";i:1;s:14:"pavblogcomment";i:2;s:13:"pavbloglatest";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"8";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'featured', 'product', 's', 0),
(0, 'themecontrol', 'themecontrol', 'a:69:{s:13:"default_theme";s:13:"pav_furniture";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"0";s:30:"listing_products_columns_small";s:1:"0";s:34:"listing_products_columns_minismall";s:1:"0";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"0";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:44:"79-99 Beaver Street, New York, NY 10005, USA";s:17:"location_latitude";s:9:"40.705423";s:18:"location_longitude";s:10:"-74.008616";s:18:"contact_customhtml";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:23:"enable_development_mode";s:0:"";s:6:"layout";s:9:"fullwidth";s:6:"header";s:0:"";s:21:"widget_contactus_data";a:2:{i:1;s:815:"&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;icon&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/icon/icon-telephone.png&quot; /&gt;&amp;nbsp; &lt;/i&gt;&lt;span&gt;+01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;icon&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/icon/icon-phone.png&quot; /&gt;&amp;nbsp; &lt;/i&gt;&lt;span&gt;+01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;icon&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/icon/icon-email.png&quot; /&gt;&amp;nbsp; &lt;/i&gt;&lt;span&gt;Email : sales@expandcart.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;icon&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/icon/icon-skype.png&quot; /&gt;&amp;nbsp; &lt;/i&gt;&lt;span&gt;SkypeAcc&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;";i:2;s:822:"&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;icon&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/icon/icon-telephone.png&quot; /&gt;&amp;nbsp; &lt;/i&gt;&lt;span&gt;+01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;icon&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/icon/icon-phone.png&quot; /&gt;&amp;nbsp; &lt;/i&gt;&lt;span&gt;+01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;icon&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/icon/icon-email.png&quot; /&gt;&amp;nbsp; &lt;/i&gt;&lt;span&gt;البريد : sales@expandcart.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;icon&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/icon/icon-skype.png&quot; /&gt;&amp;nbsp; &lt;/i&gt;&lt;span&gt;SkypeAcc&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;";}s:22:"widget_newsletter_data";a:2:{i:1;s:1763:"&lt;div class=&quot;input-group&quot;&gt;&lt;input alt=&quot;username&quot; class=&quot;inputbox&quot; name=&quot;email&quot; placeholder=&quot;Type your email&quot; size=&quot;31&quot; type=&quot;text&quot; /&gt; &lt;span class=&quot;input-group-btn&quot;&gt;&lt;button class=&quot;btn btn-newletter&quot; type=&quot;button&quot;&gt;Go!&lt;/button&gt; &lt;/span&gt;&lt;/div&gt;\r\n\r\n&lt;p&gt;Sunday when we open from and dions contact page&lt;/p&gt;\r\n\r\n&lt;div class=&quot;box-heading&quot;&gt;Get Social&lt;/div&gt;\r\n\r\n&lt;div class=&quot;social&quot;&gt;\r\n&lt;ul class=&quot;pull-left&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;iconbox pinterest&quot; data-original-title=&quot;pinterest&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;icon-pinterest&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;iconbox google-plus&quot; data-original-title=&quot;google-plus&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;icon-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;iconbox google-plus&quot; data-original-title=&quot;google-plus&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;icon-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;iconbox twitter&quot; data-original-title=&quot;twitter&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;icon-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1827:"&lt;div class=&quot;input-group&quot;&gt;&lt;input alt=&quot;username&quot; class=&quot;inputbox&quot; name=&quot;email&quot; placeholder=&quot;بريدك الإلكتروني&quot; size=&quot;31&quot; type=&quot;text&quot; /&gt; &lt;span class=&quot;input-group-btn&quot;&gt;&lt;button class=&quot;btn btn-newletter&quot; type=&quot;button&quot;&gt;إشتراك&lt;/button&gt; &lt;/span&gt;&lt;/div&gt;\r\n\r\n&lt;p&gt;إتصل بنا في أي وقت، سوف نكون سعداء بذلك&lt;/p&gt;\r\n\r\n&lt;div class=&quot;box-heading&quot;&gt;لنكن على إتصال&lt;/div&gt;\r\n\r\n&lt;div class=&quot;social&quot;&gt;\r\n&lt;ul class=&quot;pull-left&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;iconbox pinterest&quot; data-original-title=&quot;pinterest&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;icon-pinterest&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;iconbox google-plus&quot; data-original-title=&quot;google-plus&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;icon-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;iconbox google-plus&quot; data-original-title=&quot;google-plus&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;icon-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;iconbox twitter&quot; data-original-title=&quot;twitter&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;icon-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:18:"widget_paypal_data";a:2:{i:1;s:142:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/paypal/paypal.png&quot; style=&quot;width: 171px; height: 18px;&quot; /&gt;&lt;/p&gt;\r\n";i:2;s:142:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/paypal/paypal.png&quot; style=&quot;width: 171px; height: 18px;&quot; /&gt;&lt;/p&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:8:"standard";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:19:"enable_compress_css";s:0:"";s:17:"exclude_css_files";s:13:"bootstrap.css";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1);
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
('module', 'category'),
('module', 'affiliate'),
('module', 'account'),
('module', 'featured'),
('module', 'latest'),
('module', 'bestseller'),
('module', 'themecontrol'),
('module', 'pavcustom'),
('module', 'pavblog'),
('module', 'pavproducttabs'),
('module', 'pavblogcomment'),
('module', 'special'),
('module', 'pavsliderlayer'),
--('module', 'pavmegamenu'),
--('module', 'pavcarousel'),
('module', 'pavblogcategory'),
('module', 'pavbloglatest');