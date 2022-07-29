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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(2, '', 1, 0, NULL, NULL, NULL, '', '20', '', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(3, '', 1, 0, NULL, NULL, NULL, '', '17', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(4, '', 3, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0, 1),
(5, '', 1, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(7, '', 1, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(25, '', 3, 0, NULL, NULL, NULL, '', '79', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(26, '', 3, 0, NULL, NULL, NULL, '', '74', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(27, '', 3, 0, NULL, NULL, NULL, '', '73', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(28, '', 3, 0, NULL, NULL, NULL, '', '80', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(29, '', 3, 0, NULL, NULL, NULL, '', '', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(30, '', 3, 0, NULL, NULL, NULL, '', '46', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(37, '', 1, 0, NULL, NULL, NULL, '', '34', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
(38, '', 1, 0, NULL, NULL, NULL, '', '57', '1', 'url', 0, 1, 'menu', 0, 1, 0, 7, 0, '?route=pavblog/blogs', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 1),
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
(3, 3, 'Drinks', ''),
(4, 1, 'Watches', ''),
(4, 3, 'Watches', ''),
(5, 4, 'جوجه', ''),
(37, 1, 'Fragrance', ''),
(7, 4, 'قدرت', ''),
(7, 3, 'Salads', ''),
(40, 3, 'Home', ''),
(2, 4, 'صبحانه', ''),
(2, 3, 'Breakfast', ''),
(2, 1, 'What''s new', ''),
(5, 1, 'Skin care ', ''),
(3, 1, 'Makeup', ''),
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
(7, 1, ' Absolue', ''),
(3, 4, 'نوشیدنی ها', ''),
(37, 4, 'برگر', ''),
(40, 4, 'خانه', '');

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
(1, 'Video Opencart Installation', 'video_code', 'a:1:{s:10:"video_code";s:321:"&lt;iframe width=&quot;100%&quot; height=&quot;210&quot; src=&quot;//www.youtube.com/embed/M1USNjKKRWk&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;\r\n&lt;p&gt;The Quickstart Package consists on a complete Opencart! + Template + Various Extensions + Sample Content, excellent for beginner...&lt;/p&gt;";}', 0),
(2, 'Demo HTML', 'html', 'a:1:{s:4:"html";a:3:{i:1;s:159:"&lt;p&gt;&lt;img src=&quot;image/data/img-menu.jpg&quot; /&gt;&lt;/p&gt;\r\nFusce a congue purus, sit amet sollicitudin libero. In hac habitasse platea dictumst.";i:3;s:159:"&lt;p&gt;&lt;img src=&quot;image/data/img-menu.jpg&quot; /&gt;&lt;/p&gt;\r\nFusce a congue purus, sit amet sollicitudin libero. In hac habitasse platea dictumst.";i:4;s:159:"&lt;p&gt;&lt;img src=&quot;image/data/img-menu.jpg&quot; /&gt;&lt;/p&gt;\r\nFusce a congue purus, sit amet sollicitudin libero. In hac habitasse platea dictumst.";}}', 0),
(3, 'Products Special', 'product_list', 'a:4:{s:9:"list_type";s:10:"bestseller";s:5:"limit";s:1:"4";s:11:"image_width";s:2:"60";s:12:"image_height";s:2:"65";}', 0),
(4, 'Products In Cat 20', 'product_category', 'a:4:{s:11:"category_id";s:2:"20";s:5:"limit";s:1:"6";s:11:"image_width";s:3:"120";s:12:"image_height";s:3:"120";}', 0),
(5, 'Band', 'banner', 'a:4:{s:8:"group_id";s:1:"8";s:11:"image_width";s:3:"120";s:12:"image_height";s:2:"65";s:5:"limit";s:1:"6";}', 0),
(6, 'expandcart Feed', 'feed', 'a:1:{s:8:"feed_url";s:55:"http://www.expandcart.com/expandcartfeeds.feed?type=rss";}', 0),
(11, 'Category', 'html', 'a:1:{s:4:"html";a:3:{i:1;s:1332:"&lt;ul&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=20&quot;&gt;Fragrance&lt;/a&gt;\r\n             &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=18&quot;&gt;Makeup&lt;/a&gt; \r\n              &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=25&quot;&gt;Special offers &lt;/a&gt;\r\n              &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=17&quot;&gt;Skin care&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=24&quot;&gt;Absolue&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=34&quot;&gt;What''s new &lt;/a&gt;\r\n                      &lt;/li&gt;\r\n          &lt;/ul&gt;";i:3;s:1332:"&lt;ul&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=20&quot;&gt;Fragrance&lt;/a&gt;\r\n             &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=18&quot;&gt;Makeup&lt;/a&gt; \r\n              &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=25&quot;&gt;Special offers &lt;/a&gt;\r\n              &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=17&quot;&gt;Skin care&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=24&quot;&gt;Absolue&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=34&quot;&gt;What''s new &lt;/a&gt;\r\n                      &lt;/li&gt;\r\n          &lt;/ul&gt;";i:4;s:1332:"&lt;ul&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=20&quot;&gt;Fragrance&lt;/a&gt;\r\n             &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=18&quot;&gt;Makeup&lt;/a&gt; \r\n              &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=25&quot;&gt;Special offers &lt;/a&gt;\r\n              &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=17&quot;&gt;Skin care&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=24&quot;&gt;Absolue&lt;/a&gt;\r\n                      &lt;/li&gt;\r\n            &lt;li&gt;\r\n                &lt;a href=&quot;http://localhost/opencart-work/dev/pav-cosmetics/index.php?route=product/category&amp;amp;path=34&quot;&gt;What''s new &lt;/a&gt;\r\n                      &lt;/li&gt;\r\n          &lt;/ul&gt;";}}', 0),
(10, 'Latest Produce', 'product_list', 'a:4:{s:9:"list_type";s:6:"newest";s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"236";}', 0),
(12, 'Banner', 'html', 'a:1:{s:4:"html";a:3:{i:1;s:57:"&lt;img src=&quot;image/data/img-category.jpg&quot; /&gt;";i:3;s:57:"&lt;img src=&quot;image/data/img-category.jpg&quot; /&gt;";i:4;s:57:"&lt;img src=&quot;image/data/img-category.jpg&quot; /&gt;";}}', 0);

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
(1, 'Slideshow', 'a:28:{s:5:"title";s:9:"Slideshow";s:5:"delay";s:4:"9000";s:9:"fullwidth";s:9:"fullwidth";s:5:"width";s:4:"1170";s:6:"height";s:3:"457";s:12:"touch_mobile";s:1:"1";s:13:"stop_on_hover";s:1:"1";s:12:"shuffle_mode";s:1:"0";s:14:"image_cropping";s:1:"1";s:11:"shadow_type";s:1:"0";s:14:"show_time_line";s:1:"1";s:18:"time_line_position";s:6:"bottom";s:16:"background_color";s:7:"#d9d9d9";s:6:"margin";s:11:"0px 0px 0px";s:7:"padding";s:1:"0";s:16:"background_image";s:1:"0";s:14:"background_url";s:0:"";s:14:"navigator_type";s:6:"bullet";s:16:"navigator_arrows";s:16:"verticalcentered";s:16:"navigation_style";s:5:"round";s:17:"offset_horizontal";s:1:"0";s:15:"offset_vertical";s:1:"0";s:14:"show_navigator";s:1:"1";s:20:"hide_navigator_after";s:3:"200";s:15:"thumbnail_width";s:3:"100";s:16:"thumbnail_height";s:2:"50";s:16:"thumbnail_amount";s:1:"5";s:17:"hide_screen_width";s:0:"";}');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `pavosliderlayers`
--

INSERT INTO `pavosliderlayers` (`id`, `title`, `parent_id`, `group_id`, `params`, `layersparams`, `image`, `status`, `position`) VALUES
(1, 'image slideshow1', 0, 1, 'a:16:{s:2:"id";s:1:"1";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"image slideshow1";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"457";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:1:"1";s:12:"slider_image";s:22:"data/slider/image7.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:22:"data/slider/image1.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:2:"-4";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:3:"737";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:15:"Curabitur velit";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"148";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1528";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:11:"small_text ";s:13:"layer_caption";s:88:"Velit in leo tempus velit in leo fermentum &lt;br /&gt; congue odio ac nunc sollicitudin";s:15:"layer_animation";s:3:"lfr";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"212";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2174";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:27:"tp-button btn-theme-primary";s:13:"layer_caption";s:8:"Shop Now";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"278";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2887";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:11:"medium_text";s:13:"layer_caption";s:9:"Cosmetics";s:15:"layer_animation";s:3:"lfb";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"103";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2307";}}}', 'data/slider/image7.png', 1, 1),
(11, 'Image slideshow4', 0, 1, 'a:16:{s:2:"id";s:2:"11";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"Image slideshow4";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"700";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"11";s:12:"slider_image";s:22:"data/slider/image6.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:22:"data/slider/image2.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:12:"easeOutQuart";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:1:"0";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2000";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:15:"Curabitur velit";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:12:"easeOutQuart";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"183";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1528";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"small_text";s:13:"layer_caption";s:88:"Velit in leo tempus velit in leo fermentum &lt;br /&gt; congue odio ac nunc sollicitudin";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:10:"easeInSine";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"238";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2174";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:27:"tp-button btn-theme-primary";s:13:"layer_caption";s:19:"Shop Now _ASM_nbsp;";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:13:"easeInOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"330";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2887";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:11:"medium_text";s:13:"layer_caption";s:9:"Cosmetics";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:12:"easeOutQuart";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"130";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}}}', 'data/slider/image6.png', 1, 2),
(15, 'Image slideshow5', 0, 1, 'a:16:{s:2:"id";s:2:"15";s:15:"slider_group_id";s:1:"1";s:12:"slider_title";s:16:"Image slideshow5";s:13:"slider_status";s:1:"1";s:17:"slider_transition";s:6:"random";s:11:"slider_slot";s:1:"7";s:15:"slider_rotation";s:1:"0";s:15:"slider_duration";s:3:"457";s:12:"slider_delay";s:1:"0";s:11:"slider_link";s:0:"";s:16:"slider_thumbnail";s:0:"";s:15:"slider_usevideo";s:1:"0";s:14:"slider_videoid";s:0:"";s:16:"slider_videoplay";s:1:"0";s:9:"slider_id";s:2:"15";s:12:"slider_image";s:22:"data/slider/image6.png";}', 'O:8:"stdClass":1:{s:6:"layers";a:5:{i:0;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:1;s:13:"layer_content";s:22:"data/slider/image3.png";s:10:"layer_type";s:5:"image";s:11:"layer_class";s:0:"";s:13:"layer_caption";s:17:"Your Image Here 5";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:1:"0";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1276";}i:1;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:2;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:10:"large_text";s:13:"layer_caption";s:15:"Curabitur velit";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:12:"easeOutQuart";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"143";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"1528";}i:2;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:3;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:31:"small_text bg_black border_left";s:13:"layer_caption";s:88:"Velit in leo tempus velit in leo fermentum &lt;br /&gt; congue odio ac nunc sollicitudin";s:15:"layer_animation";s:3:"lft";s:12:"layer_easing";s:11:"easeInQuint";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"197";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2174";}i:3;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:4;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:27:"tp-button btn-theme-primary";s:13:"layer_caption";s:8:"Shop Now";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:13:"easeInOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"268";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2887";}i:4;a:20:{s:16:"layer_video_type";s:7:"youtube";s:14:"layer_video_id";s:0:"";s:18:"layer_video_height";s:3:"200";s:17:"layer_video_width";s:3:"300";s:17:"layer_video_thumb";s:0:"";s:8:"layer_id";i:5;s:13:"layer_content";s:0:"";s:10:"layer_type";s:4:"text";s:11:"layer_class";s:11:"medium_text";s:13:"layer_caption";s:9:"Cosmetics";s:15:"layer_animation";s:12:"randomrotate";s:12:"layer_easing";s:11:"easeOutExpo";s:11:"layer_speed";s:3:"350";s:9:"layer_top";s:3:"108";s:10:"layer_left";s:1:"0";s:13:"layer_endtime";s:1:"0";s:14:"layer_endspeed";s:3:"300";s:18:"layer_endanimation";s:4:"auto";s:15:"layer_endeasing";s:7:"nothing";s:10:"time_start";s:4:"2400";}}}', 'data/slider/image6.png', 1, 4);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9186 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'pavcustom', 'pavcustom_module', 'a:5:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:9:"Promotion";i:2;s:12:"العروض";}s:11:"description";a:2:{i:1;s:742:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;hidden-xs&quot;&gt;\r\n&lt;div class=&quot;col-sm-3 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=20&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner7.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-6 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=18&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner8.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-3 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner9.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;";i:2;s:742:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;hidden-xs&quot;&gt;\r\n&lt;div class=&quot;col-sm-3 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=20&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner7.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-6 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=18&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner8.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-3 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner9.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:8:"showcase";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:530:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;hidden-xs&quot;&gt;\r\n&lt;div class=&quot;col-sm-6 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner11.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-6 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner10.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;";i:2;s:530:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;hidden-xs&quot;&gt;\r\n&lt;div class=&quot;col-sm-6 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner11.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-sm-6 image&quot;&gt;&lt;a href=&quot;index.php?route=product/category&amp;amp;path=17&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner10.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}i:3;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:1696:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;col-lg-4 col-md-4 col-sm-12 col-xs-12&quot;&gt;\r\n&lt;div class=&quot;support-id&quot;&gt;\r\n&lt;div class=&quot;pull-left image shipping&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/car.png&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;support-body&quot;&gt;\r\n&lt;h4 class=&quot;support-heading&quot;&gt;&lt;a href=&quot;#&quot;&gt;Free Shipping&lt;/a&gt;&lt;/h4&gt;\r\n\r\n&lt;p&gt;Tellus aodio consequat auctor ornare odio sed&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-4 col-md-4 col-sm-12 col-xs-12&quot;&gt;\r\n&lt;div class=&quot;support-id&quot;&gt;\r\n&lt;div class=&quot;pull-left image order&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/money.png&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;support-body&quot;&gt;\r\n&lt;h4 class=&quot;support-heading&quot;&gt;&lt;a href=&quot;#&quot;&gt;Discount on Orders&lt;/a&gt;&lt;/h4&gt;\r\n\r\n&lt;p&gt;Tellus aodio consequat auctor ornare odio sed&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-4 col-md-4 col-sm-12 col-xs-12&quot;&gt;\r\n&lt;div class=&quot;support-id&quot;&gt;\r\n&lt;div class=&quot;pull-left image help&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/person.png&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;support-body&quot;&gt;\r\n&lt;h4 class=&quot;support-heading&quot;&gt;&lt;a href=&quot;#&quot;&gt;Need on Help&lt;/a&gt;&lt;/h4&gt;\r\n\r\n&lt;p&gt;Tellus aodio consequat auctor ornare odio sed&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1927:"&lt;div class=&quot;row&quot;&gt;\r\n&lt;div class=&quot;col-lg-4 col-md-4 col-sm-12 col-xs-12&quot;&gt;\r\n&lt;div class=&quot;support-id&quot;&gt;\r\n&lt;div class=&quot;pull-left image shipping&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/car.png&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;support-body&quot;&gt;\r\n&lt;h4 class=&quot;support-heading&quot;&gt;&lt;a href=&quot;#&quot;&gt;شحن مجاني&lt;/a&gt;&lt;/h4&gt;\r\n\r\n&lt;p&gt;أضف نقطة وقرى وصغار تم. في يتم تمهيد وحتّى, مسارح الوراء تعد أم.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-4 col-md-4 col-sm-12 col-xs-12&quot;&gt;\r\n&lt;div class=&quot;support-id&quot;&gt;\r\n&lt;div class=&quot;pull-left image order&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/money.png&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;support-body&quot;&gt;\r\n&lt;h4 class=&quot;support-heading&quot;&gt;&lt;a href=&quot;#&quot;&gt;خصم على الطلبات&lt;/a&gt;&lt;/h4&gt;\r\n\r\n&lt;p&gt;أضف نقطة وقرى وصغار تم. في يتم تمهيد وحتّى, مسارح الوراء تعد أم.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-lg-4 col-md-4 col-sm-12 col-xs-12&quot;&gt;\r\n&lt;div class=&quot;support-id&quot;&gt;\r\n&lt;div class=&quot;pull-left image help&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/person.png&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;support-body&quot;&gt;\r\n&lt;h4 class=&quot;support-heading&quot;&gt;&lt;a href=&quot;#&quot;&gt;تحتاج لمساعدة&lt;/a&gt;&lt;/h4&gt;\r\n\r\n&lt;p&gt;أضف نقطة وقرى وصغار تم. في يتم تمهيد وحتّى, مسارح الوراء تعد أم.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:4;a:8:{s:12:"module_title";a:2:{i:1;s:12:"Contact info";i:2;s:15:"إتصل بنا";}s:11:"description";a:2:{i:1;s:607:"&lt;ul&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-map-marker&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;&lt;span&gt;Proin gravida nibh vel velit auctor&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-mobile-phone&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;&lt;span&gt;Phone: +01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-envelope-alt&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;&lt;span&gt;Email: pavcosmetics@gmail.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:663:"&lt;ul&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-map-marker&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;&lt;span&gt;345 شارع الملك عبد العزيز آل سعود&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-mobile-phone&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;&lt;span&gt; الهاتف: 456 4565 5 (012) 2+&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-envelope-alt&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;&lt;span&gt;البريد الإلكتروني: info@yourdomain.com&lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"3";}i:5;a:8:{s:12:"module_title";a:2:{i:1;s:10:"Newsletter";i:2;s:29:"النشرة البريدية";}s:11:"description";a:2:{i:1;s:502:"&lt;p&gt;Make sure you dont miss interesting happenings by joining our newsletter program&lt;/p&gt;\r\n\r\n&lt;div class=&quot;form-group&quot;&gt;&lt;input alt=&quot;username&quot; class=&quot;inputbox form-control&quot; name=&quot;email&quot; size=&quot;25&quot; type=&quot;text&quot; value=&quot;Type your email&quot; /&gt;\r\n&lt;p&gt;&lt;button class=&quot;btn btn-theme-primary pull-right&quot; type=&quot;submit&quot;&gt;SUBSCRIBE&amp;nbsp;&amp;nbsp;&amp;rarr;&lt;/button&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n";i:2;s:554:"&lt;p&gt;لا تفوتوا أخبارنا الجديدة و إنضموا الأن لنشرتنا البريدية&lt;/p&gt;\r\n\r\n&lt;div class=&quot;form-group&quot;&gt;&lt;input alt=&quot;username&quot; class=&quot;inputbox form-control&quot; name=&quot;email&quot; size=&quot;25&quot; type=&quot;text&quot; value=&quot;أكتب بريدك الإلكتروني&quot; /&gt;\r\n&lt;p&gt;&lt;button class=&quot;btn btn-theme-primary pull-right&quot; type=&quot;submit&quot;&gt;إشتراك&amp;nbsp;&amp;nbsp;&amp;larr;&lt;/button&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"5";}}', 1),
(0, 'pavsliderlayer', 'pavsliderlayer_module', 'a:1:{i:0;a:5:{s:8:"group_id";s:1:"1";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:66:{s:13:"default_theme";s:13:"pav_cosmetics";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"3";s:30:"listing_products_columns_small";s:1:"3";s:34:"listing_products_columns_minismall";s:1:"1";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"0";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"3";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:44:"79-99 Beaver Street, New York, NY 10005, USA";s:17:"location_latitude";s:9:"40.705423";s:18:"location_longitude";s:10:"-74.008616";s:18:"contact_customhtml";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:6:"layout";s:9:"fullwidth";s:9:"quickview";s:1:"0";s:21:"widget_contactus_data";a:2:{i:1;s:929:"&lt;div class=&quot;pull-left image hidden-xs&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-footer.jpg&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;box&quot;&gt;\r\n&lt;div class=&quot;box-heading&quot;&gt;\r\n&lt;h4&gt;&lt;span&gt;Magazine&lt;/span&gt;&lt;/h4&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Customer Service&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Subscribe&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Buy this issue&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n\r\n&lt;p&gt;Mauris in erat justo nullam ac sit amet a augue. Sed non neque elit Sed ut imperdiet nisi. Proin per inceptos himenaeos. Mauris in erat justo. Nullam ac urna eu felis dapibus condentum sit nisi. Proin condimentum fecondi mentum ferm nunc. Etiam pharetra, erat sed fermentum feu Suspendisse orci enim.&lt;/p&gt;\r\n&lt;a href=&quot;#&quot;&gt;More ...&lt;/a&gt;&lt;/div&gt;\r\n";i:2;s:1191:"&lt;div class=&quot;pull-left image hidden-xs&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/banner-footer.jpg&quot; /&gt;&lt;/div&gt;\r\n\r\n&lt;div class=&quot;box&quot;&gt;\r\n&lt;div class=&quot;box-heading&quot;&gt;\r\n&lt;h4&gt;&lt;span&gt;مجلتنا&lt;/span&gt;&lt;/h4&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;خدمة العملاء&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;إشتراك&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;إشتري هذا العدد&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n\r\n&lt;p&gt;بل اتفاق الضروري التّحول حيث, إذ بلاده ليركز معاملة لها, كل التبرعات انتصارهم بين. وقرى وقبل اللا عل أما اكتوبر أن به, بهيئة فقامت دول. سابق لإنعدام بريطانيا فعل مع, بعد ٣٠ الشتاء، الحكومة. ألمّ ليتسنّى بولندا، ان الى. قائمة الساحلية كما, هو بقعة بالتوقيع يتم, كل تعد أهّل شواطيء بريطانيا-فرنسا.&lt;/p&gt;\r\n&lt;a href=&quot;#&quot;&gt;المزيد ...&lt;/a&gt;&lt;/div&gt;";}s:22:"widget_newsletter_data";a:2:{i:1;s:2388:"&lt;ul class=&quot;list folow&quot;&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-tumblr&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;Tumblr&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;Tumblr&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;Google-plus&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;Google-plus&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;Facebook&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;Facebook&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;Twitter&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;Twitter&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-youtube&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;Youtube&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;Youtube&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-instagram&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;Instagram&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;Instagram&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-pinterest&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;Pinterest&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;Pinterest&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:2452:"&lt;ul class=&quot;list folow&quot;&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-tumblr&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;تمبلر&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;تمبلر&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-facebook&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;جوجل بلس&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;جوجل بلس&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-google-plus&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;فيسبوك&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;فيسبوك&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-twitter&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;تويتر&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;تويتر&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-youtube&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;يوتيوب&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;يوتيوب&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-instagram&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;إنستجرام&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;إنستجرام&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;span class=&quot;iconbox&quot;&gt;&lt;i class=&quot;icon-pinterest&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt; &lt;a data-original-title=&quot;بينترست&quot; data-placement=&quot;top&quot; data-toggle=&quot;tooltip&quot; href=&quot;#&quot; title=&quot;&quot;&gt; &lt;span&gt;بينترست&lt;/span&gt; &lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:18:"widget_paypal_data";a:2:{i:1;s:87:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/paypal.png&quot; /&gt;&lt;/p&gt;";i:2;s:87:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/paypal.png&quot; /&gt;&lt;/p&gt;";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"1";s:11:"type_fonts1";s:6:"google";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1),
(0, 'pavtestimonial', 'pavtestimonial_module', 'a:1:{i:0;a:11:{s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"0";s:13:"text_interval";s:4:"8000";s:5:"width";s:3:"100";s:6:"height";s:3:"100";s:11:"column_item";s:1:"1";s:10:"page_items";s:1:"2";s:16:"testimonial_item";a:2:{i:1;a:4:{s:5:"image";s:18:"data/avatar-01.png";s:10:"video_link";s:0:"";s:7:"profile";a:2:{i:1;s:150:"&lt;div class=&quot;profile&quot;&gt;\r\n&lt;div&gt;\r\n&lt;h3&gt;jane doe&lt;/h3&gt;\r\n\r\n&lt;p&gt;Creative Manager&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:170:"&lt;div class=&quot;profile&quot;&gt;\r\n&lt;div&gt;\r\n&lt;h3&gt;أحمد الربيع&lt;/h3&gt;\r\n\r\n&lt;p&gt;مدير الإدارة&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:11:"description";a:2:{i:1;s:81:"&lt;p&gt;Duis sed odio sit amet nibh vulputa cursus a sit amet mauris&lt;/p&gt;\r\n";i:2;s:121:"&lt;p&gt;قررت أسابيع مقاطعة بها مع. الهجوم استرجاع واقتصار عرض إذ&lt;/p&gt;";}}i:2;a:4:{s:5:"image";s:18:"data/avatar-02.png";s:10:"video_link";s:0:"";s:7:"profile";a:2:{i:1;s:150:"&lt;div class=&quot;profile&quot;&gt;\r\n&lt;div&gt;\r\n&lt;h3&gt;jane doe&lt;/h3&gt;\r\n\r\n&lt;p&gt;Creative Manager&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:170:"&lt;div class=&quot;profile&quot;&gt;\r\n&lt;div&gt;\r\n&lt;h3&gt;أحمد الربيع&lt;/h3&gt;\r\n\r\n&lt;p&gt;مدير الإدارة&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:11:"description";a:2:{i:1;s:81:"&lt;p&gt;Duis sed odio sit amet nibh vulputa cursus a sit amet mauris&lt;/p&gt;\r\n";i:2;s:121:"&lt;p&gt;قررت أسابيع مقاطعة بها مع. الهجوم استرجاع واقتصار عرض إذ&lt;/p&gt;";}}}}}', 1),
(0, 'banner', 'banner_module', 'a:3:{i:0;a:7:{s:9:"banner_id";s:2:"10";s:5:"width";s:3:"277";s:6:"height";s:3:"254";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:1;a:7:{s:9:"banner_id";s:2:"12";s:5:"width";s:3:"278";s:6:"height";s:3:"466";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:7:{s:9:"banner_id";s:2:"12";s:5:"width";s:3:"278";s:6:"height";s:3:"466";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavmegamenu_params', 'params', '[{"submenu":1,"subwidth":900,"cols":"","group":0,"id":2,"rows":[{"cols":[{"widgets":"wid-10","colwidth":3},{"widgets":"wid-2","colwidth":5,"colclass":""},{"widgets":"wid-3","colwidth":4}]},{"cols":[{"widgets":"wid-5","colclass":"hidden-title","colwidth":12}]}]},{"submenu":1,"subwidth":650,"id":5,"cols":1,"group":0,"rows":[{"cols":[{"widgets":"wid-11","colwidth":4,"colclass":"mega-col-inner"},{"widgets":"wid-1","colwidth":8}]},{"cols":[{"widgets":"wid-12","colclass":"hidden-title","colwidth":12}]}]},{"submenu":1,"id":3,"cols":1,"group":0,"rows":[{"cols":[{"colwidth":12,"type":"menu"}]}]}]', 0),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"4";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'config', 'config_image_additional_height', '79', 0),
(0, 'config', 'config_image_related_width', '245', 0),
(0, 'config', 'config_image_related_height', '250', 0),
(0, 'config', 'config_image_popup_height', '638', 0),
(0, 'config', 'config_image_popup_width', '625', 0),
(0, 'config', 'config_image_thumb_height', '434', 0),
(0, 'config', 'config_image_thumb_width', '425', 0),
(0, 'config', 'config_image_category_height', '317', 0),
(0, 'config', 'config_image_category_width', '873', 0),
(0, 'pavblog_frontmodules', 'pavblog_frontmodules', 'a:1:{s:7:"modules";a:3:{i:0;s:15:"pavblogcategory";i:1;s:14:"pavblogcomment";i:2;s:13:"pavbloglatest";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'config', 'config_image_compare_height', '79', 0),
(0, 'config', 'config_image_compare_width', '79', 0),
(0, 'config', 'config_image_product_width', '245', 0),
(0, 'config', 'config_image_product_height', '250', 0),
(0, 'config', 'config_image_additional_width', '79', 0),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"900";s:15:"general_lheight";s:3:"350";s:14:"general_swidth";s:3:"600";s:15:"general_sheight";s:3:"250";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"80";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"600";s:15:"general_cheight";s:3:"250";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"3";s:22:"cat_leading_image_type";s:1:"l";s:24:"cat_secondary_image_type";s:1:"l";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:12:"100858303516";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'config', 'config_image_cart_height', '79', 0),
(0, 'config', 'config_image_cart_width', '79', 0),
(0, 'config', 'config_image_wishlist_height', '79', 0),
(0, 'pavpopulartags', 'pavpopulartags_module', 'a:1:{i:1;a:10:{s:5:"title";a:3:{i:1;s:12:"Popular tags";i:3;s:12:"Popular tags";i:4;s:12:"Popular tags";}s:10:"limit_tags";s:1:"9";s:13:"min_font_size";s:2:"13";s:13:"max_font_size";s:2:"13";s:11:"font_weight";s:6:"normal";s:6:"prefix";s:13:"prefix_class1";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'pavcarousel', 'pavcarousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:7:"columns";s:1:"5";s:5:"width";s:3:"171";s:6:"height";s:2:"70";s:9:"layout_id";s:5:"99999";s:8:"position";s:11:"mass_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'config', 'config_image_wishlist_width', '79', 0),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:1:{i:1;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:2:"64";s:6:"height";s:2:"70";s:4:"cols";s:1:"1";s:5:"limit";s:1:"3";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"0";s:10:"sort_order";i:4;}}', 1),
(0, 'pavproductcarousel', 'pavproductcarousel_module', 'a:2:{i:1;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}s:6:"prefix";s:8:"featured";s:4:"tabs";a:1:{i:0;s:8:"featured";}s:5:"width";s:3:"247";s:6:"height";s:3:"265";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:3;s:0:"";i:4;s:0:"";}s:6:"prefix";s:6:"latest";s:4:"tabs";a:1:{i:0;s:6:"latest";}s:5:"width";s:3:"247";s:6:"height";s:3:"265";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"12";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:3;}}', 1),
(0, 'pavmodulemanager', 'pavmodulemanager', 'a:3:{s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:7:"modules";a:3:{i:0;s:9:"pavcustom";i:1;s:18:"pavproductcarousel";i:2;s:10:"pavtwitter";}}', 1),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'category', 'category_module', 'a:2:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'special', 'special_module', 'a:4:{i:0;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"95";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:1;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"95";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:2;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"95";s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:3;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:1:"5";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'featured', 'product', 't', 0),
(0, 'featured', 'featured_product', '63,67,75,51,62,31,55,70', 0),
(0, 'featured', 'featured_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"6";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"0";s:10:"sort_order";i:4;}}', 1),
(0, 'carousel', 'carousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"0";s:10:"sort_order";i:3;}}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=473 ;

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
('module', 'pavproductcarousel'),
('module', 'pavpopulartags'),
('module', 'bestseller'),
('module', 'special'),
('module', 'latest'),
('module', 'pavblog'),
('module', 'pavblogcomment'),
('module', 'pavblogcategory'),
('module', 'pavsliderlayer'),
--('module', 'pavmegamenu'),
--('module', 'pavcarousel'),
('module', 'pavtestimonial');