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
  `widget_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`megamenu_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(2, '', 1, 0, NULL, NULL, NULL, '', '20', '', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(3, '', 1, 0, NULL, NULL, NULL, '', '17', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(4, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(5, '', 1, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(25, '', 3, 0, NULL, NULL, NULL, '', '79', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(26, '', 3, 0, NULL, NULL, NULL, '', '74', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(27, '', 3, 0, NULL, NULL, NULL, '', '73', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(28, '', 3, 0, NULL, NULL, NULL, '', '80', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(29, '', 3, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(30, '', 3, 0, NULL, NULL, NULL, '', '46', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(37, '', 1, 0, NULL, NULL, NULL, '', '34', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(38, '', 1, 0, NULL, NULL, NULL, '', '57', '1', 'url', 0, 1, 'menu', 0, 1, 0, 6, 0, '?route=pavblog/blogs', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(40, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0, 1),
(42, '', 4, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 0, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=20&quot;&gt;Breakfast&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=18&quot;&gt;Chicken &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=25&quot;&gt;Salads&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;Drinks&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=24&quot;&gt;Cake&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', '', 0, 0, 0, 1);

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
(3, 3, 'Drinks', ''),
(4, 1, 'Watches', ''),
(4, 3, 'Watches', ''),
(5, 1, 'Chicken', ''),
(37, 4, 'برگر', ''),
(42, 1, 'tellus purus', ''),
(40, 3, 'Home', ''),
(2, 1, 'Breakfast', ''),
(2, 3, 'Breakfast', ''),
(2, 4, 'صبحانه', ''),
(5, 4, 'جوجه', ''),
(3, 4, 'نوشیدنی ها', ''),
(38, 1, 'Blog', ''),
(38, 3, 'Blog', ''),
(38, 4, 'وبلاگ', ''),
(4, 4, 'Watches', ''),
(25, 4, 'Aliquam', ''),
(26, 4, 'Claritas', ''),
(27, 3, 'Consectetuer', ''),
(27, 4, 'Consectetuer', ''),
(28, 4, 'Hendrerit', ''),
(29, 4, 'Litterarum', ''),
(30, 4, 'Macs', ''),
(37, 3, 'Burgers', ''),
(25, 3, 'Aliquam', ''),
(25, 1, 'Aliquam', ''),
(26, 3, 'Claritas', ''),
(26, 1, 'Claritas', ''),
(27, 1, 'Consectetuer', ''),
(28, 3, 'Hendrerit', ''),
(28, 1, 'Hendrerit', ''),
(29, 3, 'Litterarum', ''),
(29, 1, 'Litterarum', ''),
(30, 3, 'Macs', ''),
(30, 1, 'Macs', ''),
(40, 1, 'Home', ''),
(5, 3, 'Chicken', ''),
(3, 1, 'Drinks', ''),
(37, 1, 'Burgers', ''),
(40, 4, 'خانه', ''),
(42, 4, 'tellus purus', ''),
(42, 3, 'tellus purus', '');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `megamenu_widgets`
--

INSERT INTO `megamenu_widgets` (`id`, `name`, `type`, `params`, `store_id`) VALUES
(1, 'Video Opencart Installation', 'video_code', 'a:1:{s:10:"video_code";s:288:"&lt;iframe width=&quot;320&quot; height=&quot;220&quot; src=&quot;//www.youtube.com/embed/M1USNjKKRWk&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;\r\n&lt;p&gt;The Quickstart Package consists on a complete Opencart! + Template + Various Extensions + Sample...&lt;/p&gt;";}', 0),
(2, 'Demo HTML', 'html', 'a:1:{s:4:"html";a:3:{i:1;s:159:"&lt;p&gt;&lt;img src=&quot;image/data/img-menu.jpg&quot; /&gt;&lt;/p&gt;\r\nFusce a congue purus, sit amet sollicitudin libero. In hac habitasse platea dictumst.";i:3;s:159:"&lt;p&gt;&lt;img src=&quot;image/data/img-menu.jpg&quot; /&gt;&lt;/p&gt;\r\nFusce a congue purus, sit amet sollicitudin libero. In hac habitasse platea dictumst.";i:4;s:159:"&lt;p&gt;&lt;img src=&quot;image/data/img-menu.jpg&quot; /&gt;&lt;/p&gt;\r\nFusce a congue purus, sit amet sollicitudin libero. In hac habitasse platea dictumst.";}}', 0),
(3, 'Products Special', 'product_list', 'a:4:{s:9:"list_type";s:7:"special";s:5:"limit";s:1:"4";s:11:"image_width";s:2:"60";s:12:"image_height";s:2:"65";}', 0),
(4, 'Products In Cat 20', 'product_category', 'a:4:{s:11:"category_id";s:2:"20";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(6, 'expandcart Feed', 'feed', 'a:1:{s:8:"feed_url";s:55:"http://www.expandcart.com/expandcartfeeds.feed?type=rss";}', 0),
(11, 'Category', 'html', 'a:1:{s:4:"html";a:3:{i:1;s:998:"&lt;ul&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=20&quot;&gt;Breakfast&lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=18&quot;&gt;Chicken &lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=25&quot;&gt;Salads&lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;Drinks&lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=24&quot;&gt;Cake &lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=34&quot;&gt;Burgers&lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n                        &lt;a href=&quot;index.php?route=product/category&amp;amp;path=20_26&quot;&gt; PC&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n&lt;/ul&gt;";i:3;s:998:"&lt;ul&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=20&quot;&gt;Breakfast&lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=18&quot;&gt;Chicken &lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=25&quot;&gt;Salads&lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;Drinks&lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=24&quot;&gt;Cake &lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=34&quot;&gt;Burgers&lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n                        &lt;a href=&quot;index.php?route=product/category&amp;amp;path=20_26&quot;&gt; PC&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n&lt;/ul&gt;";i:4;s:998:"&lt;ul&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=20&quot;&gt;Breakfast&lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=18&quot;&gt;Chicken &lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=25&quot;&gt;Salads&lt;/a&gt;\r\n    &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;Drinks&lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=24&quot;&gt;Cake &lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n      &lt;a href=&quot;index.php?route=product/category&amp;amp;path=34&quot;&gt;Burgers&lt;/a&gt;\r\n            &lt;/li&gt;\r\n  &lt;li&gt;\r\n                        &lt;a href=&quot;index.php?route=product/category&amp;amp;path=20_26&quot;&gt; PC&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n&lt;/ul&gt;";}}', 0),
(10, 'Latest Produce', 'product_list', 'a:4:{s:9:"list_type";s:6:"newest";s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"236";}', 0);

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
(1, 'Slideshow', 'a:28:{s:5:"title";s:9:"Slideshow";s:5:"delay";s:4:"9000";s:9:"fullwidth";s:0:"";s:5:"width";s:3:"873";s:6:"height";s:3:"420";s:12:"touch_mobile";s:1:"1";s:13:"stop_on_hover";s:1:"0";s:12:"shuffle_mode";s:1:"0";s:14:"image_cropping";s:1:"1";s:11:"shadow_type";s:1:"0";s:14:"show_time_line";s:1:"0";s:18:"time_line_position";s:3:"top";s:16:"background_color";s:7:"#d9d9d9";s:6:"margin";s:1:"0";s:7:"padding";s:1:"0";s:16:"background_image";s:1:"0";s:14:"background_url";s:0:"";s:14:"navigator_type";s:6:"bullet";s:16:"navigator_arrows";s:16:"verticalcentered";s:16:"navigation_style";s:5:"round";s:17:"offset_horizontal";s:1:"0";s:15:"offset_vertical";s:1:"0";s:14:"show_navigator";s:1:"1";s:20:"hide_navigator_after";s:3:"200";s:15:"thumbnail_width";s:3:"100";s:16:"thumbnail_height";s:2:"50";s:16:"thumbnail_amount";s:1:"5";s:17:"hide_screen_width";s:0:"";}');

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
(1, 'image slideshow1', 0, 1, 'a:16:{s:2:"id";s:1:"1";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"image slideshow1";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"1";s:12:"slider_image";s:25:"data/slider/slideshow.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:22:"data/slider/image1.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 7";s:15:"layer_animation";s:3:"sfl";s:12:"layer_easing";s:13:"easeInOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"26";s:10:"layer_left";s:2:"62";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"365";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:7:"Biturei";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"66";s:10:"layer_left";s:3:"475";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1528";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:88:"Velit in leo tempus velit in leo fermentum &lt;br /&gt; congue odio ac nunc sollicitudin";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"162";s:10:"layer_left";s:3:"474";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2174";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:18:"red_text bold_text";s:13:"layer_caption";s:5:"$2600";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"229";s:10:"layer_left";s:3:"478";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:20:"medium_text red_text";s:13:"layer_caption";s:10:"Cras purus";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"116";s:10:"layer_left";s:3:"475";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}}}', 'data/slider/slideshow.png', 1, 1),
(16, 'Image slideshow2', 0, 1, 'a:16:{s:2:"id";s:2:"16";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"Image slideshow2";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"16";s:12:"slider_image";s:25:"data/slider/slideshow.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:7:"Curabit";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"66";s:10:"layer_left";s:3:"475";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1528";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:11:"small_text ";s:13:"layer_caption";s:88:"Velit in leo tempus velit in leo fermentum &lt;br /&gt; congue odio ac nunc sollicitudin";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"165";s:10:"layer_left";s:3:"475";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2174";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:18:"red_text bold_text";s:13:"layer_caption";s:5:"$2600";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"229";s:10:"layer_left";s:3:"478";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:20:"medium_text red_text";s:13:"layer_caption";s:10:"Cras purus";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"116";s:10:"layer_left";s:3:"475";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:22:"data/slider/image2.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 6";s:15:"layer_animation";s:3:"sft";s:12:"layer_easing";s:13:"easeInOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"49";s:10:"layer_left";s:1:"8";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}}}', 'data/slider/slideshow.png', 1, 1),
(17, 'Image slideshow3', 0, 1, 'a:16:{s:2:"id";s:2:"17";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"Image slideshow3";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"17";s:12:"slider_image";s:25:"data/slider/slideshow.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:22:"data/slider/image1.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 7";s:15:"layer_animation";s:3:"sfb";s:12:"layer_easing";s:13:"easeInOutQuad";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"26";s:10:"layer_left";s:2:"66";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"365";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:6:"Cuelit";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"66";s:10:"layer_left";s:3:"475";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1528";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:11:"small_text ";s:13:"layer_caption";s:88:"Velit in leo tempus velit in leo fermentum &lt;br /&gt; congue odio ac nunc sollicitudin";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"165";s:10:"layer_left";s:3:"475";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2174";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:18:"red_text bold_text";s:13:"layer_caption";s:5:"$2600";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"229";s:10:"layer_left";s:3:"478";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:20:"medium_text red_text";s:13:"layer_caption";s:10:"Cras purus";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"116";s:10:"layer_left";s:3:"475";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}}}', 'data/slider/slideshow.png', 1, 1);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6350 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'pavsliderlayer', 'pavsliderlayer_module', 'a:1:{i:0;a:5:{s:8:"group_id";s:1:"1";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:1:{i:1;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:2:"64";s:6:"height";s:2:"70";s:4:"cols";s:1:"1";s:5:"limit";s:1:"3";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"900";s:15:"general_lheight";s:3:"350";s:14:"general_swidth";s:3:"600";s:15:"general_sheight";s:3:"250";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"80";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"600";s:15:"general_cheight";s:3:"250";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"3";s:22:"cat_leading_image_type";s:1:"l";s:24:"cat_secondary_image_type";s:1:"l";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:12:"100858303516";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'pavnewsletter', 'pavnewsletter_module', 'a:1:{i:1;a:5:{s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:10:"sort_order";i:4;s:11:"description";s:109:"&lt;p&gt;Get the word out. Share this page with your friends and family. Enter your email address&lt;/p&gt;\r\n";}}', 1),
--(0, 'pavcarousel', 'pavcarousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"8";s:7:"columns";s:1:"7";s:5:"width";s:3:"140";s:6:"height";s:2:"58";s:9:"layout_id";s:5:"99999";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:5;}}', 1),
(0, 'pavcustom', 'pavcustom_module', 'a:7:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:17:"Banner MassBottom";i:2;s:17:"Banner MassBottom";}s:11:"description";a:2:{i:1;s:1342:"&lt;div class=&quot;media-list row &quot;&gt;\r\n&lt;div class=&quot;col-lg-6 col-md-6 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media gray&quot;&gt;&lt;a class=&quot;pull-right&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/banner3.png&quot; /&gt; &lt;/a&gt;\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h3 class=&quot;media-heading text-primary-theme&quot;&gt;Ipad in education&lt;/h3&gt;\r\n\r\n&lt;p&gt;Nulla vel metus scelerisque ante sollicitudin commodo sit amet nibh libero itudin commodoturpis&lt;/p&gt;\r\n\r\n&lt;p class=&quot;readmore&quot;&gt;&lt;a href=&quot;#&quot;&gt;Readmore&lt;/a&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-6 col-md-6 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media gray&quot;&gt;&lt;a class=&quot;pull-right&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/banner4.png&quot; /&gt; &lt;/a&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h3 class=&quot;media-heading&quot;&gt;MacBook Pro&lt;/h3&gt;\r\n\r\n&lt;p&gt;Nulla vel metus scelerisque ante sollicitudin commodo sit amet nibh libero itudin commodoturpis&lt;/p&gt;\r\n\r\n&lt;p class=&quot;readmore&quot;&gt;&lt;a href=&quot;#&quot;&gt;Readmore&lt;/a&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1677:"&lt;div class=&quot;media-list row &quot;&gt;\r\n&lt;div class=&quot;col-lg-6 col-md-6 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media gray&quot;&gt;&lt;a class=&quot;pull-right&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/banner3.png&quot; /&gt; &lt;/a&gt;\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h3 class=&quot;media-heading text-primary-theme&quot;&gt;الأيباد في التعليم&lt;/h3&gt;\r\n\r\n&lt;p&gt;وقد تم حاول وترك كردة. ما تُصب ليرتفع الألمان ربع. يتم بـ بمعارضة البشريةً, وفي الأحمر بقيادة من, وقبل تزامناً بالمحور ربع تم.&lt;/p&gt;\r\n\r\n&lt;p class=&quot;readmore&quot;&gt;&lt;a href=&quot;#&quot;&gt;إقرأ المزيد&lt;/a&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-6 col-md-6 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media gray&quot;&gt;&lt;a class=&quot;pull-right&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/banner4.png&quot; /&gt; &lt;/a&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h3 class=&quot;media-heading&quot;&gt;تكنولوجيا المستقبل&lt;/h3&gt;\r\n\r\n&lt;p&gt;فبعد الإنجليز، بعض مع, حدى الغربي العمليات أم. بقعة الحدود سليمان، لم بال. قد حين الحزب المسرح البلطيق. وبدون للحرب الثالث كل ولم.&lt;/p&gt;\r\n\r\n&lt;p class=&quot;readmore&quot;&gt;&lt;a href=&quot;#&quot;&gt;إقرأ المزيد&lt;/a&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:14:" no-boxshadown";s:10:"sort_order";s:1:"2";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:6:"Social";i:2;s:6:"Social";}s:11:"description";a:2:{i:1;s:660:"&lt;p&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-pinterest&quot;&gt;&amp;nbsp;&lt;/i&gt;Pinterest&lt;/a&gt; &lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;Facebook&lt;/a&gt; &lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;Google+&lt;/a&gt; &lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;Twitter&lt;/a&gt; &lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-youtube&quot;&gt;&amp;nbsp;&lt;/i&gt;Youtube&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:660:"&lt;p&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-pinterest&quot;&gt;&amp;nbsp;&lt;/i&gt;Pinterest&lt;/a&gt; &lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;Facebook&lt;/a&gt; &lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;Google+&lt;/a&gt; &lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;Twitter&lt;/a&gt; &lt;a href=&quot;#&quot; title=&quot;&quot;&gt;&lt;i class=&quot;fa fa-youtube&quot;&gt;&amp;nbsp;&lt;/i&gt;Youtube&lt;/a&gt;&lt;/p&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:6:"social";s:10:"sort_order";s:1:"2";}i:3;a:8:{s:12:"module_title";a:2:{i:1;s:10:"List Order";i:2;s:10:"List Order";}s:11:"description";a:2:{i:1;s:1987:"&lt;div class=&quot;media-list row&quot;&gt;\r\n&lt;div class=&quot;col-lg-3 col-md-3 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media red&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/icon-free.png&quot; /&gt; &lt;/a&gt;\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4 class=&quot;media-heading&quot;&gt;Free Shipping&lt;/h4&gt;\r\n\r\n&lt;p&gt;Nulla vel metus scelerisque ante sollici.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media green&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/icon-money.png&quot; /&gt; &lt;/a&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4 class=&quot;media-heading&quot;&gt;Money back&lt;/h4&gt;\r\n\r\n&lt;p&gt;Cras purus odio, in tempus viverra turpis.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media blue&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/icon-discounts.png&quot; /&gt; &lt;/a&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4 class=&quot;media-heading&quot;&gt;Discounts&lt;/h4&gt;\r\n\r\n&lt;p&gt;Cras sit amet nibh libero itudin commo.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media dark&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/icon-support.png&quot; /&gt; &lt;/a&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4 class=&quot;media-heading&quot;&gt;Support&lt;/h4&gt;\r\n\r\n&lt;p&gt;Cras sit amet nibh libero, in viverra turpis.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:2168:"&lt;div class=&quot;media-list row&quot;&gt;\r\n&lt;div class=&quot;col-lg-3 col-md-3 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media red&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/icon-free.png&quot; /&gt; &lt;/a&gt;\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4 class=&quot;media-heading&quot;&gt;شحن مجاني&lt;/h4&gt;\r\n\r\n&lt;p&gt;لهذه عصبة عل سقط. بل مرجع هزيمة الخاسرة كان.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media green&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/icon-money.png&quot; /&gt; &lt;/a&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4 class=&quot;media-heading&quot;&gt;إسترجاع مجاني&lt;/h4&gt;\r\n\r\n&lt;p&gt;معارضة اسبوعين الثالث، كل حشد. كارثة في.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media blue&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/icon-discounts.png&quot; /&gt; &lt;/a&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4 class=&quot;media-heading&quot;&gt;تنزيلات&lt;/h4&gt;\r\n\r\n&lt;p&gt;لها أن الأجل وإعلان. حلّت المسرح الربيع، دول لم.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-3 col-md-3 hidden-sm hidden-xs&quot;&gt;\r\n&lt;div class=&quot;media dark&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img class=&quot;media-object&quot; src=&quot;image/data/icon-support.png&quot; /&gt; &lt;/a&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4 class=&quot;media-heading&quot;&gt;دعم فني&lt;/h4&gt;\r\n\r\n&lt;p&gt;الأخذ العالم تكتيكاً هذا عل, عن هذا حاول أخرى.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:13:"no-boxshadown";s:10:"sort_order";s:1:"1";}i:4;a:8:{s:12:"module_title";a:2:{i:1;s:18:"Banner mass bottom";i:2;s:18:"Banner mass bottom";}s:11:"description";a:2:{i:1;s:165:"&lt;p class=&quot;hidden-xs&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-mass-bottom.jpg&quot; style=&quot;border-radius: 5px;&quot; /&gt;&lt;/p&gt;";i:2;s:165:"&lt;p class=&quot;hidden-xs&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-mass-bottom.jpg&quot; style=&quot;border-radius: 5px;&quot; /&gt;&lt;/p&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"4";}i:5;a:8:{s:12:"module_title";a:2:{i:1;s:10:"Contact Us";i:2;s:15:"إتصل بنا";}s:11:"description";a:2:{i:1;s:505:"&lt;p&gt;If your question is not answered there, please use one of the contact methods below.&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;span class=&quot;fa fa-map-marker&quot;&gt;&amp;nbsp;&lt;/span&gt;Proin gravida, velit auctor aliquet&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;fa fa-mobile-phone&quot;&gt;&amp;nbsp;&lt;/span&gt;Phone: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;fa fa-envelope&quot;&gt;&amp;nbsp;&lt;/span&gt;Email: pavdigitaltore@gmail.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:537:"&lt;p&gt;إذا كانت لديك أية أسئلة يمكنك الإتصال بنا عن طريق&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;span class=&quot;fa fa-map-marker&quot;&gt;&amp;nbsp;&lt;/span&gt;حلّت المسرح الربيع، دول لم&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;fa fa-mobile-phone&quot;&gt;&amp;nbsp;&lt;/span&gt;الهاتف: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;fa fa-envelope&quot;&gt;&amp;nbsp;&lt;/span&gt;البريد: pavdigitaltore@gmail.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:10:"contact-us";s:10:"sort_order";s:1:"3";}i:6;a:8:{s:12:"module_title";a:2:{i:1;s:16:"Customer Service";i:2;s:23:"خدمة العملاء";}s:11:"description";a:2:{i:1;s:571:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;Contact Us&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;Returns&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;Site Map&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;Brands&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;Gift Vouchers&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:624:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/contact&quot;&gt;إتصل بنا&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/return/insert&quot;&gt;الإسترجاع&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=information/sitemap&quot;&gt;خريطة الموقع&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/manufacturer&quot;&gt;الماركات&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/voucher&quot;&gt;قسائم الهدايا&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:7;a:8:{s:12:"module_title";a:2:{i:1;s:10:"My Account";i:2;s:10:"حسابي";}s:11:"description";a:2:{i:1;s:560:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;My Account&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/order&quot;&gt;Order History&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;Wish List&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;Newsletter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;Specials&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:628:"&lt;ul class=&quot;list&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/account&quot;&gt;حسابي&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/order&quot;&gt;تاريخ الطلبات&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/wishlist&quot;&gt;قائمة الأمنيات&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=account/newsletter&quot;&gt;النشرة الإخبارية&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;index.php?route=product/special&quot;&gt;العروض الخاصة&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'config', 'config_image_wishlist_height', '52', 0),
(0, 'config', 'config_image_wishlist_width', '65', 0),
(0, 'config', 'config_image_compare_height', '52', 0),
(0, 'config', 'config_image_compare_width', '65', 0),
(0, 'config', 'config_image_related_height', '640', 0),
(0, 'config', 'config_image_related_width', '800', 0),
(0, 'config', 'config_image_additional_height', '64', 0),
(0, 'config', 'config_image_product_height', '192', 0),
(0, 'config', 'config_image_additional_width', '80', 0),
(0, 'config', 'config_image_product_width', '240', 0),
(0, 'config', 'config_image_popup_height', '640', 0),
(0, 'config', 'config_image_popup_width', '800', 0),
(0, 'config', 'config_image_category_height', '250', 0),
(0, 'config', 'config_image_thumb_height', '640', 0),
(0, 'config', 'config_image_thumb_width', '800', 0),
(0, 'config', 'config_image_category_width', '873', 0),
(0, 'pavblog_frontmodules', 'pavblog_frontmodules', 'a:1:{s:7:"modules";a:3:{i:0;s:15:"pavblogcategory";i:1;s:14:"pavblogcomment";i:2;s:13:"pavbloglatest";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"4";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":1,"subwidth":720,"cols":1,"group":0,"id":3,"rows":[{"cols":[{"colwidth":3,"type":"menu"},{"widgets":"wid-3","colwidth":5},{"widgets":"wid-10","colwidth":4}]}]},{"submenu":1,"subwidth":550,"id":5,"cols":1,"group":0,"rows":[{"cols":[{"widgets":"wid-11","colwidth":4},{"widgets":"wid-1","colwidth":8}]}]}]', 0),
(0, 'themecontrol', 'themecontrol', 'a:66:{s:13:"default_theme";s:16:"pav_digitalstore";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"3";s:30:"listing_products_columns_small";s:1:"3";s:34:"listing_products_columns_minismall";s:1:"1";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"0";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"3";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:25:"Mễ Trì, Hanoi, Vietnam";s:17:"location_latitude";s:10:"21.0087024";s:18:"location_longitude";s:18:"105.77619800000002";s:18:"contact_customhtml";a:2:{i:1;s:613:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-tasks&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Customer Service&lt;/strong&gt;&lt;br /&gt;\r\n	info@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;i class=&quot;iconbox button fa fa-share&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;span&gt;&lt;strong&gt;Returns and Refunds:&lt;/strong&gt;&lt;br /&gt;\r\n	returns@yourstore.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:0:"";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:23:"enable_development_mode";s:0:"";s:6:"layout";s:9:"fullwidth";s:18:"widget_paypal_data";a:2:{i:1;s:89:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/paypal.png&quot; /&gt;&lt;/p&gt;\r\n";i:2;s:89:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/paypal.png&quot; /&gt;&lt;/p&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"1";s:11:"type_fonts1";s:6:"google";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:19:"enable_compress_css";s:0:"";s:17:"exclude_css_files";s:13:"bootstrap.css";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1),
(0, 'featured', 'featured_product', '40,30,32,48', 0),
(0, 'featured', 'product', '', 0),
(0, 'featured', 'featured_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"6";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"0";s:10:"sort_order";i:3;}}', 1),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'config', 'config_image_cart_height', '52', 0),
(0, 'config', 'config_image_cart_width', '65', 0),
(0, 'banner', 'banner_module', 'a:3:{i:0;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"279";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}i:1;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"279";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"279";s:6:"height";s:3:"420";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}}', 1),
(0, 'pavproducts', 'pavproducts_module', 'a:1:{i:1;a:13:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:5:"width";s:3:"350";s:6:"height";s:3:"280";s:12:"itemsperpage";s:1:"5";s:4:"cols";s:1:"5";s:5:"limit";s:2:"15";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:3;s:13:"category_tabs";a:4:{i:0;s:2:"57";i:1;s:2:"17";i:2;s:2:"73";i:3;s:2:"72";}s:5:"image";a:4:{i:0;s:17:"data/icon-mac.png";i:1;s:18:"data/icon-ipad.png";i:2;s:20:"data/icon-iphone.png";i:3;s:18:"data/icon-ipod.png";}s:5:"class";a:4:{i:0;s:0:"";i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}}}', 1),
(0, 'special', 'special_module', 'a:3:{i:0;a:7:{s:5:"limit";s:1:"2";s:11:"image_width";s:3:"240";s:12:"image_height";s:3:"192";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:1;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"240";s:12:"image_height";s:3:"192";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:2;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"240";s:12:"image_height";s:3:"192";s:9:"layout_id";s:1:"4";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'carousel', 'carousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}}', 1),
(0, 'category', 'category_module', 'a:3:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'account', 'account_module', 'a:2:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"4";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=474 ;

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
('module', 'pavbloglatest'),
('module', 'pavcustom'),
('module', 'pavproducts'),
('module', 'bestseller'),
('module', 'special'),
('module', 'latest'),
('module', 'pavblog'),
('module', 'pavblogcomment'),
('module', 'pavblogcategory'),
('module', 'pavsliderlayer'),
('module', 'information'),
--('module', 'pavcarousel'),
--('module', 'pavmegamenu'),
('module', 'pavnewsletter');