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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=49 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(20, 'data/icon-home.png', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0, 0),
(21, '', 1, 0, NULL, NULL, NULL, '', '17', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, 'Bike parts', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(22, '', 1, 0, NULL, NULL, NULL, 'col=12', '34', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(23, '', 1, 0, NULL, NULL, NULL, '', '61', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(24, '', 1, 0, NULL, NULL, NULL, '', '33', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(26, '', 40, 1, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(27, '', 40, 1, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 2, 0, '#', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', 0, 0, 0, 0),
(28, '', 26, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 5, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(31, '', 26, 0, NULL, NULL, NULL, '', '17', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(32, '', 26, 0, NULL, NULL, NULL, '', '33', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(33, '', 26, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(34, '', 26, 0, NULL, NULL, NULL, '', '34', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(35, '', 23, 0, NULL, NULL, NULL, '', '24', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(36, '', 23, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(37, '', 23, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(38, '', 23, 0, NULL, NULL, NULL, '', '51', '1', 'product', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(40, '', 22, 1, NULL, NULL, NULL, '', '', '4', 'url', 0, 0, 'menu', 0, 1, 0, 1, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(41, '', 22, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 0, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img_menu.jpg&quot; /&gt;&lt;/p&gt;\r\n', '', 0, 0, 0, 0),
(42, '', 27, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', 0, 0, 0, 0),
(44, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 6, 0, 'index.php?route=pavblog/blogs', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(45, '', 40, 1, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 3, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(46, '', 40, 1, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 4, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(47, '', 46, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', 0, 0, 0, 0),
(48, '', 45, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', 0, 0, 0, 0);

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
(1, 1, 'ROOT', 'Menu Root'),
(20, 3, 'Home', ''),
(21, 3, 'Footwear', ''),
(22, 1, 'Men', ''),
(23, 1, 'Women', ''),
(26, 3, 'Nulla a odio', ''),
(26, 1, 'Nulla a odio', ''),
(26, 2, 'Nulla a odio', ''),
(27, 1, ' Sed quam lore', ''),
(28, 3, 'Siam eget arcu', ''),
(40, 3, 'Sodal lacinia', ''),
(40, 1, 'Sodal lacinia', ''),
(41, 3, 'Varius odio', ''),
(31, 1, 'Aenean placerat', ''),
(31, 3, 'Aenean placerat', ''),
(31, 2, 'Aenean placerat', ''),
(32, 3, 'Hendrerit libero', ''),
(32, 1, 'Hendrerit libero', ''),
(32, 2, 'Hendrerit libero', ''),
(33, 1, 'Luctus et eu', ''),
(33, 3, 'Luctus et eu', ''),
(33, 2, 'Luctus et eu', ''),
(34, 1, 'Cliquet lectus rutrum', ''),
(34, 3, 'Cliquet lectus rutrum', ''),
(34, 2, 'Cliquet lectus rutrum', ''),
(35, 1, 'Nullam aliquet ant', ''),
(35, 3, 'Nullam aliquet ant', ''),
(35, 2, 'Nullam aliquet ant', ''),
(36, 1, 'Pellentesque mauris', ''),
(36, 3, 'Pellentesque mauris', ''),
(36, 2, 'Pellentesque mauris', ''),
(37, 1, 'Quisque eu augue', ''),
(37, 3, 'Quisque eu augue', ''),
(37, 2, 'Quisque eu augue', ''),
(38, 2, 'Item page', ''),
(38, 1, 'Item page', ''),
(38, 3, 'Item page', ''),
(42, 3, 'Curabitur tempus', ''),
(27, 3, ' Sed quam lore', ''),
(28, 1, 'Siam eget arcu', ''),
(20, 1, 'Home', ''),
(21, 1, 'Footwear', ''),
(23, 3, 'Women', ''),
(24, 1, 'Jewelry', ''),
(24, 3, 'Jewelry', ''),
(41, 1, 'Varius odio', ''),
(44, 3, 'Blog', ''),
(44, 1, 'Blog', ''),
(45, 1, 'Fermentum quis', ''),
(45, 3, 'Fermentum quis', ''),
(46, 1, 'Tempus velit', ''),
(46, 3, 'Tempus velit', ''),
(42, 1, 'Curabitur tempus', ''),
(47, 1, 'Quis tincidunt', ''),
(48, 1, 'Quis tincidunt', ''),
(48, 3, 'Quis tincidunt', ''),
(47, 3, 'Quis tincidunt', ''),
(22, 3, 'Men', '');

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
(1, 'Video Opencart Installation', 'video_code', 'a:1:{s:10:"video_code";s:168:"&lt;iframe width=&quot;400&quot; height=&quot;300&quot; src=&quot;//www.youtube.com/embed/M1USNjKKRWk&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;";}', 0),
(2, 'Demo HTML Sample', 'html', 'a:1:{s:4:"html";a:1:{i:1;s:275:"Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel. Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.";}}', 0),
(3, 'Products Latest', 'product_list', 'a:4:{s:9:"list_type";s:6:"newest";s:5:"limit";s:1:"3";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"90";}', 0),
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pavoslidergroups`
--

INSERT INTO `pavoslidergroups` (`id`, `title`, `params`) VALUES
(2, 'Slideshow', 'a:28:{s:5:"title";s:9:"Slideshow";s:5:"delay";s:4:"9000";s:9:"fullwidth";s:9:"fullwidth";s:5:"width";s:3:"980";s:6:"height";s:3:"547";s:12:"touch_mobile";s:1:"1";s:13:"stop_on_hover";s:1:"0";s:12:"shuffle_mode";s:1:"0";s:14:"image_cropping";s:1:"1";s:11:"shadow_type";s:1:"0";s:14:"show_time_line";s:1:"0";s:18:"time_line_position";s:3:"top";s:16:"background_color";s:7:"#d9d9d9";s:6:"margin";s:1:"0";s:7:"padding";s:1:"0";s:16:"background_image";s:1:"0";s:14:"background_url";s:0:"";s:14:"navigator_type";s:6:"bullet";s:16:"navigator_arrows";s:16:"verticalcentered";s:16:"navigation_style";s:5:"round";s:17:"offset_horizontal";s:1:"0";s:15:"offset_vertical";s:1:"0";s:14:"show_navigator";s:1:"1";s:20:"hide_navigator_after";s:3:"200";s:15:"thumbnail_width";s:3:"100";s:16:"thumbnail_height";s:2:"50";s:16:"thumbnail_amount";s:1:"5";s:17:"hide_screen_width";s:0:"";}');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `pavosliderlayers`
--

INSERT INTO `pavosliderlayers` (`id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`) VALUES
(3, 'image slideshow1', 0, 2, 'a:16:{s:2:"id";s:1:"3";s:15:"slider_group_id";s:1:"2";s:12:"slider_title";s:16:"image slideshow1";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"3";s:12:"slider_image";s:24:"data/slider/slider01.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:19:"large_text bg_black";s:13:"layer_caption";s:22:"Curabitur tempus velit";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"139";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:42:"Velit in leo tempus velit in leo fermentum";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"218";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:20:"Quis tincidunt lorem";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"261";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1200";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:21:"medium_text bg_orange";s:13:"layer_caption";s:93:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=78&quot;&gt;Shownow&lt;/a&gt;";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"334";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1594";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:19:"data/icon-price.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:13:"easeInOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"139";s:10:"layer_left";s:3:"764";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}}}', 'data/slider/slider01.jpg', 1, 1),
(7, 'image slideshow2', 0, 2, 'a:16:{s:2:"id";s:1:"7";s:15:"slider_group_id";s:1:"2";s:12:"slider_title";s:16:"image slideshow2";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"7";s:12:"slider_image";s:24:"data/slider/slider02.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:19:"large_text bg_black";s:13:"layer_caption";s:22:"Curabitur tempus velit";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"87";s:10:"layer_left";s:3:"534";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:42:"Velit in leo tempus velit in leo fermentum";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"166";s:10:"layer_left";s:3:"650";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:20:"Quis tincidunt lorem";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"212";s:10:"layer_left";s:3:"794";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1200";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:21:"medium_text bg_orange";s:13:"layer_caption";s:93:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=74&quot;&gt;Shownow&lt;/a&gt;";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"275";s:10:"layer_left";s:3:"844";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:19:"data/icon-price.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:13:"easeInOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"158";s:10:"layer_left";s:3:"139";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}}}', 'data/slider/slider02.jpg', 1, 1),
(8, 'image slideshow3', 0, 2, 'a:16:{s:2:"id";s:1:"8";s:15:"slider_group_id";s:1:"2";s:12:"slider_title";s:16:"image slideshow3";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"8";s:12:"slider_image";s:24:"data/slider/slider03.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:19:"large_text bg_black";s:13:"layer_caption";s:22:"Curabitur tempus velit";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"111";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:42:"Velit in leo tempus velit in leo fermentum";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"195";s:10:"layer_left";s:3:"113";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:20:"Quis tincidunt lorem";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"239";s:10:"layer_left";s:3:"261";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1200";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:21:"medium_text bg_orange";s:13:"layer_caption";s:93:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=76&quot;&gt;Shownow&lt;/a&gt;";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"309";s:10:"layer_left";s:3:"308";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:19:"data/icon-price.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:13:"easeInOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"157";s:10:"layer_left";s:3:"772";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}}}', 'data/slider/slider03.jpg', 1, 1),
(9, 'image slideshow4', 0, 2, 'a:16:{s:2:"id";s:1:"9";s:15:"slider_group_id";s:1:"2";s:12:"slider_title";s:16:"image slideshow4";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"9";s:12:"slider_image";s:24:"data/slider/slider04.jpg";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:19:"large_text bg_black";s:13:"layer_caption";s:22:"Curabitur tempus velit";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"139";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"400";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:42:"Velit in leo tempus velit in leo fermentum";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"218";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"800";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:20:"Quis tincidunt lorem";s:15:"layer_animation";s:3:"lfl";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"261";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1200";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:21:"medium_text bg_orange";s:13:"layer_caption";s:93:"&lt;a href=&quot;index.php?route=product/product_ASM_product_id=77&quot;&gt;Shownow&lt;/a&gt;";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"334";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1600";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:19:"data/icon-price.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:13:"easeInOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"169";s:10:"layer_left";s:3:"740";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}}}', 'data/slider/slider04.jpg', 1, 1);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7607 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'affiliate', 'affiliate_module', 'a:2:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:56:{s:13:"default_theme";s:9:"pav_decor";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:20:"cateogry_product_row";s:1:"0";s:14:"category_pzoom";s:1:"1";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"round";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:3;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:3;s:0:"";}s:16:"location_address";s:37:"Mễ Trì, Từ Liêm, Hanoi, Vietnam";s:17:"location_latitude";s:10:"21.0087024";s:18:"location_longitude";s:18:"105.77619800000002";s:18:"contact_customhtml";a:2:{i:1;s:613:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-tasks&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Customer Service&lt;/strong&gt;&lt;br /&gt;\r\n	info@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-share&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Returns and Refunds:&lt;/strong&gt;&lt;br /&gt;\r\n	returns@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:613:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-tasks&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Customer Service&lt;/strong&gt;&lt;br /&gt;\r\n	info@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-share&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Returns and Refunds:&lt;/strong&gt;&lt;br /&gt;\r\n	returns@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"1";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:8:"standard";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"4";s:9:"layout_id";s:2:"13";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'config', 'config_image_cart_height', '77', 0),
(0, 'config', 'config_image_cart_width', '70', 0),
(0, 'featured', 'featured_product', '61,56,68,57,73,74,65,77', 0),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:1:{i:1;a:10:{s:11:"description";a:2:{i:1;s:127:"&lt;p&gt;Lorem ipsum sit amet consectetur adipiscing elit donec iaculis, ipsum convallis bibendum pretium dui massa&lt;/p&gt;\r\n";i:3;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:3:"500";s:6:"height";s:3:"500";s:4:"cols";s:1:"4";s:5:"limit";s:1:"4";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'banner', 'banner_module', 'a:3:{i:0;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"234";s:6:"height";s:3:"217";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:1;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"234";s:6:"height";s:3:"217";s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}i:2;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"234";s:6:"height";s:3:"217";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavsliderlayer', 'pavsliderlayer_module', 'a:1:{i:0;a:5:{s:8:"group_id";s:1:"2";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'config', 'config_image_wishlist_height', '77', 0),
(0, 'config', 'config_image_wishlist_width', '70', 0),
(0, 'config', 'config_image_compare_height', '77', 0),
(0, 'config', 'config_image_compare_width', '70', 0),
(0, 'pavproducttabs', 'pavproducttabs_module', 'a:1:{i:1;a:11:{s:11:"description";a:2:{i:1;s:0:"";i:3;s:0:"";}s:4:"tabs";a:1:{i:0;s:6:"latest";}s:5:"width";s:3:"200";s:6:"height";s:3:"200";s:12:"itemsperpage";s:1:"1";s:4:"cols";s:1:"1";s:5:"limit";s:2:"16";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"0";s:10:"sort_order";s:0:"";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"13";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:0:"";}}', 1),
(0, 'config', 'config_image_related_height', '330', 0),
(0, 'config', 'config_image_related_width', '300', 0),
(0, 'config', 'config_image_additional_height', '100', 0),
(0, 'config', 'config_image_additional_width', '90', 0),
(0, 'config', 'config_image_product_height', '330', 0),
(0, 'config', 'config_image_product_width', '300', 0),
(0, 'config', 'config_image_popup_height', '550', 0),
(0, 'config', 'config_image_popup_width', '500', 0),
(0, 'config', 'config_image_thumb_height', '550', 0),
(0, 'config', 'config_image_thumb_width', '500', 0),
(0, 'config', 'config_image_category_height', '350', 0),
(0, 'config', 'config_image_category_width', '900', 0),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"600";s:15:"general_lheight";s:3:"250";s:14:"general_swidth";s:3:"300";s:15:"general_sheight";s:3:"140";s:14:"general_xwidth";s:3:"120";s:15:"general_xheight";s:2:"70";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"250";s:15:"general_cheight";s:3:"130";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"3";s:22:"cat_leading_image_type";s:1:"s";s:24:"cat_secondary_image_type";s:1:"s";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"s";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:2:"10";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'pavcontentslider', 'pavcontentslider_module', 'a:1:{i:0;a:12:{s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"1";s:13:"text_interval";s:4:"8000";s:5:"width";s:3:"636";s:6:"height";s:3:"467";s:15:"image_navigator";s:1:"0";s:13:"navimg_weight";s:3:"177";s:13:"navimg_height";s:2:"97";s:12:"banner_image";a:5:{i:1;a:4:{s:5:"image";s:27:"data/slider/img-slider1.jpg";s:4:"link";s:0:"";s:5:"title";a:2:{i:1;s:0:"";i:3;s:0:"";}s:11:"description";a:2:{i:1;s:0:"";i:3;s:0:"";}}i:2;a:4:{s:5:"image";s:27:"data/slider/img-slider2.jpg";s:4:"link";s:0:"";s:5:"title";a:2:{i:1;s:0:"";i:3;s:0:"";}s:11:"description";a:2:{i:1;s:0:"";i:3;s:0:"";}}i:3;a:4:{s:5:"image";s:27:"data/slider/img-slider3.jpg";s:4:"link";s:0:"";s:5:"title";a:2:{i:1;s:0:"";i:3;s:0:"";}s:11:"description";a:2:{i:1;s:0:"";i:3;s:0:"";}}i:4;a:4:{s:5:"image";s:27:"data/slider/img-slider4.jpg";s:4:"link";s:0:"";s:5:"title";a:2:{i:1;s:0:"";i:3;s:0:"";}s:11:"description";a:2:{i:1;s:0:"";i:3;s:0:"";}}i:5;a:4:{s:5:"image";s:27:"data/slider/img-slider5.jpg";s:4:"link";s:0:"";s:5:"title";a:2:{i:1;s:0:"";i:3;s:0:"";}s:11:"description";a:2:{i:1;s:0:"";i:3;s:0:"";}}}}}', 1),
(0, 'pavgallery', 'pavgallery_module', 'a:1:{i:0;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:2:"70";s:6:"height";s:2:"54";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:10:"sort_order";i:3;}}', 1),
(0, 'special', 'special_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:3:"300";s:12:"image_height";s:3:"330";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"0";s:10:"sort_order";i:3;}}', 1),
(0, 'category', 'category_module', 'a:6:{i:1;a:4:{s:9:"layout_id";s:1:"4";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:3;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:4;a:4:{s:9:"layout_id";s:1:"8";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:5;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:6;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'bestseller', 'bestseller_module', 'a:6:{i:0;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"220";s:9:"layout_id";s:1:"4";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"220";s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:3;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"220";s:9:"layout_id";s:1:"8";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:4;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"220";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:5;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"220";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:6;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"220";s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavfacebook', 'pavfacebook_module', 'a:1:{i:1;a:13:{s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:8:"page_url";s:35:"https://www.facebook.com/expandcart";s:14:"application_id";s:0:"";s:12:"border_color";s:1:"0";s:11:"colorscheme";s:4:"dark";s:5:"width";s:3:"235";s:6:"height";s:0:"";s:5:"tream";s:1:"0";s:10:"show_faces";s:1:"1";s:6:"header";s:1:"0";}}', 1),
(0, 'featured', 'featured_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"6";s:11:"image_width";s:3:"300";s:12:"image_height";s:3:"330";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavproductcarousel', 'pavproductcarousel_module', 'a:1:{i:1;a:13:{s:11:"description";a:2:{i:1;s:101:"&lt;p&gt;Curabitur tempus velit in leo tempus velit in leo fermentum quis tincidunt lorem&lt;/p&gt;\r\n";i:3;s:0:"";}s:4:"tabs";a:1:{i:0;s:6:"latest";}s:5:"width";s:3:"300";s:6:"height";s:3:"330";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"12";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'featured', 'product', '', 0),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavcustom', 'pavcustom_module', 'a:7:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:12:"Follow us on";i:3;s:12:"Follow us on";}s:11:"description";a:2:{i:1;s:534:"&lt;h4&gt;Follow us on&lt;/h4&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;facebook&quot;&gt;&lt;a href=&quot;http://www.facebook.com/&quot;&gt;facebook&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;twitter&quot;&gt;&lt;a href=&quot;https://twitter.com/&quot;&gt;twitter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;google-plus&quot;&gt;&lt;a href=&quot;https://www.google.com&quot;&gt;google plus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;flickr&quot;&gt;&lt;a href=&quot;http://www.flickr.com/&quot;&gt;flickr&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:534:"&lt;h4&gt;Follow us on&lt;/h4&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;facebook&quot;&gt;&lt;a href=&quot;http://www.facebook.com/&quot;&gt;facebook&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;twitter&quot;&gt;&lt;a href=&quot;https://twitter.com/&quot;&gt;twitter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;google-plus&quot;&gt;&lt;a href=&quot;https://www.google.com&quot;&gt;google plus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;flickr&quot;&gt;&lt;a href=&quot;http://www.flickr.com/&quot;&gt;flickr&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:6:"social";s:10:"sort_order";s:1:"2";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:21:"24/7 Customer Service";i:3;s:21:"24/7 Customer Service";}s:11:"description";a:2:{i:1;s:108:"&lt;h3&gt;24/7 Customer Service&lt;/h3&gt;\r\n\r\n&lt;p&gt;Lorem ipsum amet morbi tristique senectus&lt;/p&gt;\r\n";i:3;s:108:"&lt;h3&gt;24/7 Customer Service&lt;/h3&gt;\r\n\r\n&lt;p&gt;Lorem ipsum amet morbi tristique senectus&lt;/p&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:14:"service  arrow";s:10:"sort_order";s:1:"2";}i:3;a:8:{s:12:"module_title";a:2:{i:1;s:28:"Gift card treta your friends";i:3;s:28:"Gift card treta your friends";}s:11:"description";a:2:{i:1;s:109:"&lt;h3&gt;Gift card treta your friends&lt;/h3&gt;\r\n\r\n&lt;p&gt;Morbi tristique senectus adipiscing&lt;/p&gt;\r\n";i:3;s:109:"&lt;h3&gt;Gift card treta your friends&lt;/h3&gt;\r\n\r\n&lt;p&gt;Morbi tristique senectus adipiscing&lt;/p&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:4:"gift";s:10:"sort_order";s:1:"1";}i:4;a:8:{s:12:"module_title";a:2:{i:1;s:26:"Banner (position Showcase)";i:3;s:26:"Banner (position Showcase)";}s:11:"description";a:2:{i:1;s:578:"&lt;div class=&quot;row-fluid&quot;&gt;\r\n&lt;div class=&quot;span4&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-slider1.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span4&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-slider2.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span4&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-slider3.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:3;s:578:"&lt;div class=&quot;row-fluid&quot;&gt;\r\n&lt;div class=&quot;span4&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-slider1.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span4&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-slider2.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span4&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-slider3.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:12:"module_class";s:12:"hidden-phone";s:10:"sort_order";s:1:"1";}i:5;a:8:{s:12:"module_title";a:2:{i:1;s:27:"image2 (position Promotion)";i:3;s:27:"image2 (position Promotion)";}s:11:"description";a:2:{i:1;s:403:"&lt;div class=&quot;row-fluid&quot;&gt;\r\n&lt;div class=&quot;span6&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-promotion1.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span6&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-promotion2.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:3;s:403:"&lt;div class=&quot;row-fluid&quot;&gt;\r\n&lt;div class=&quot;span6&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-promotion1.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span6&quot;&gt;\r\n&lt;p class=&quot;border&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-promotion2.jpg&quot; /&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"promotion";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:6;a:8:{s:12:"module_title";a:2:{i:1;s:10:"Contact Us";i:3;s:10:"Contact Us";}s:11:"description";a:2:{i:1;s:442:"&lt;h3&gt;Contact Us&lt;/h3&gt;\r\n\r\n&lt;p&gt;Lorem Ipsum is simply dummy text of and typesetting industr&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;phone&quot;&gt;&lt;span&gt;icon&lt;/span&gt;Phone: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;fax&quot;&gt;&lt;span&gt;icon&lt;/span&gt;Fax: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;email&quot;&gt;&lt;span&gt;icon&lt;/span&gt;Email: www.leotheme.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:442:"&lt;h3&gt;Contact Us&lt;/h3&gt;\r\n\r\n&lt;p&gt;Lorem Ipsum is simply dummy text of and typesetting industr&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;phone&quot;&gt;&lt;span&gt;icon&lt;/span&gt;Phone: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;fax&quot;&gt;&lt;span&gt;icon&lt;/span&gt;Fax: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;email&quot;&gt;&lt;span&gt;icon&lt;/span&gt;Email: www.leotheme.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:7:"contact";s:10:"sort_order";s:1:"4";}i:7;a:8:{s:12:"module_title";a:2:{i:1;s:10:"My Account";i:3;s:10:"My Account";}s:11:"description";a:2:{i:1;s:595:"&lt;h3&gt;My Account&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;My Account&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;Wish List&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;Newsletter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;Brands&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;Specials&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:3;s:595:"&lt;h3&gt;My Account&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;My Account&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;Wish List&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;Newsletter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;Brands&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;Specials&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":"1","id":21,"subwidth":700,"cols":2,"group":0,"rows":[{"cols":[{"colwidth":"7","widgets":"wid-1"},{"colwidth":"5","widgets":"wid-3"}]}]},{"submenu":1,"subwidth":940,"cols":1,"group":0,"id":22,"rows":[{"cols":[{"colwidth":12,"type":"menu"}]}]},{"submenu":1,"cols":4,"group":1,"id":40,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":26,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":27,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":45,"rows":[]},{"submenu":1,"cols":1,"group":1,"id":46,"rows":[]},{"submenu":1,"cols":1,"group":0,"id":23,"rows":[{"cols":[{"colwidth":12,"type":"menu"}]}]}]', 0);

-- --------------------------------------------------------

--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=466 ;

--
-- Dumping data for table `extension`
--

INSERT INTO `extension` (`type`, `code`) VALUES
('module', 'banner'),
('module', 'carousel'),
('module', 'category'),
('module', 'affiliate'),
('module', 'account'),
('module', 'information'),
('module', 'themecontrol'),
--('module', 'pavmegamenu'),
('module', 'pavcustom'),
('module', 'bestseller'),
('module', 'special'),
('module', 'featured'),
('module', 'latest'),
('module', 'pavbloglatest'),
('module', 'pavfacebook'),
('module', 'pavblogcomment'),
('module', 'pavblogcategory'),
('module', 'pavblog'),
('module', 'pavsliderlayer'),
('module', 'pavgallery'),
('module', 'pavproductcarousel');