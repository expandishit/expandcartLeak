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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`, `widget_id`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47, 0),
(20, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'homepage', NULL, '', '', 0, 0, 0, 0),
(21, '', 1, 0, NULL, NULL, NULL, '', '17', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, 'Bike parts', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(22, '', 1, 0, NULL, NULL, NULL, 'col1=3, col2=3, col3=6', '61', '3', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(23, '', 1, 0, NULL, NULL, NULL, '', '34', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(24, '', 1, 0, NULL, NULL, NULL, '', '33', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(25, '', 1, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(26, '', 22, 1, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(27, '', 22, 1, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'html', 0, 1, 0, 2, 0, '#', NULL, 0, 'top', '', NULL, '', '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', 0, 0, 0, 0),
(28, '', 22, 1, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 3, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(29, '', 28, 0, NULL, NULL, NULL, '', '', '1', 'html', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', ' module', NULL, '&lt;div class=&quot;margin&quot;&gt;\r\n&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img_category.jpg&quot; style=&quot;width: 425px; height: 135px;&quot; /&gt;&lt;/p&gt;\r\n\r\n&lt;h4&gt;Ante erat sagittis rhoncus diam eget arcu&lt;/h4&gt;\r\n\r\n&lt;p&gt;Ante erat sagittis rhoncus diam eget arcu tempor faucibus fringilla quam vulputate&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', 0, 0, 0, 0),
(30, '', 26, 0, NULL, NULL, NULL, '', '61', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(31, '', 30, 0, NULL, NULL, NULL, '', '17', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(32, '', 26, 0, NULL, NULL, NULL, '', '33', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(33, '', 26, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(34, '', 26, 0, NULL, NULL, NULL, '', '34', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(35, '', 23, 0, NULL, NULL, NULL, '', '24', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(36, '', 23, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(37, '', 23, 0, NULL, NULL, NULL, '', '20', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(38, '', 23, 0, NULL, NULL, NULL, '', '51', '1', 'product', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(39, '', 27, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'html', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur tempus&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n', 0, 0, 0, 0),
(40, '', 30, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(41, '', 35, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '#', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0),
(42, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 99, 0, 'index.php?route=pavblog/blogs', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0, 0);

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
(21, 1, 'Bike parts', ''),
(21, 3, 'Bike parts', ''),
(20, 1, 'Home', ''),
(21, 2, 'Bike parts', ''),
(22, 1, 'Bikes&amp;frames ', ''),
(22, 3, 'Bikes&amp;frames ', ''),
(22, 2, 'Bikes&amp;frames ', ''),
(23, 3, 'Clothing', ''),
(23, 1, 'Clothing', ''),
(23, 2, 'Clothing', ''),
(24, 3, 'Helmets', ''),
(24, 1, 'Helmets', ''),
(24, 2, 'Helmets', ''),
(25, 3, 'Tyres&amp;weels', ''),
(25, 1, 'Tyres&amp;weels', ''),
(25, 2, 'Tyres&amp;weels', ''),
(20, 2, 'Home', ''),
(26, 3, 'Nulla a odio', ''),
(26, 1, 'Nulla a odio', ''),
(26, 2, 'Nulla a odio', ''),
(27, 2, ' Sed quam lore', ''),
(27, 1, ' Sed quam lore', ''),
(28, 2, 'Siam eget arcu', ''),
(28, 1, 'Siam eget arcu', ''),
(29, 3, 'xcvzđfsg', ''),
(29, 2, 'xcvzđfsg', ''),
(29, 1, 'Quam vulputate', ''),
(30, 1, 'Amet luctus libero', ''),
(30, 3, 'Amet luctus libero', ''),
(30, 2, 'Amet luctus libero', ''),
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
(39, 3, 'Mentum quis tincidu', ''),
(39, 1, 'Mentum quis tincidu', ''),
(39, 2, 'Mentum quis tincidu', ''),
(27, 3, ' Sed quam lore', ''),
(28, 3, 'Siam eget arcu', ''),
(40, 1, 'test1', ''),
(40, 3, 'test1', ''),
(41, 1, 'Test2', ''),
(41, 3, 'Test2', ''),
(42, 1, 'Blog', ''),
(42, 3, 'Blog', '');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2916 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'pavcustom', 'pavcustom_module', 'a:4:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:11:"Mauris quam";i:2;s:25:"أفضل المنتجات";}s:11:"description";a:2:{i:1;s:1026:"&lt;div class=&quot;media mauris_quam&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img_cus.jpg&quot; /&gt;&lt;/a&gt;\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h1&gt;Mauris&lt;span&gt; quam&lt;/span&gt;&lt;/h1&gt;\r\n\r\n&lt;div class=&quot;content&quot;&gt;\r\n&lt;p class=&quot;media-text&quot;&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eu ligula velit. Proin molestie, nisi at mollis adipiscing, lorem justo facilisis est, quis lobortis metus ante id turpis. Aliquam volutpat tortor nec sem interdum cursus. Donec eu lectus vel augue consequat ultricies at ut tellus. Aliquam erat volutpat Nunc nec enim sit amet magna volutpat eleifend. Morbi orci lacus, viverra non viverra sed, sagittis eget vitae sem adipiscing vestibulum. Etiam mattis luctus dapibus&lt;/p&gt;\r\n\r\n&lt;p class=&quot;pull-right&quot;&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;Shop now&lt;/a&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:1287:"&lt;div class=&quot;media mauris_quam&quot;&gt;&lt;a class=&quot;pull-left&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img_cus.jpg&quot; /&gt;&lt;/a&gt;\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h1&gt;أفضل&lt;span&gt; المنتجات&lt;/span&gt;&lt;/h1&gt;\r\n\r\n&lt;div class=&quot;content&quot;&gt;\r\n&lt;p class=&quot;media-text&quot;&gt;قد الا الستار بتخصيص ايطاليا،, الصفحة الضغوط ومن أم, عليها العصبة اقتصادية أن حدى. ان بالمحور الأمريكي لبلجيكا، تعد. في تاريخ اليها بحق, ما أسر مسرح عليها لبولندا،, الغالي الإمتعاض لان أن. ليرتفع معارضة دنو مع, فشكّل الشتاء أن تعد. كما عن إعمار واعتلاء والنرويج, و أثره، الربيع، بريطانيا-فرنسا أما, أم بعد كُلفة المضي. كما عن إعمار واعتلاء والنرويج, و أثره، الربيع، بريطانيا-فرنسا أما, أم بعد كُلفة المضي.&lt;/p&gt;\r\n\r\n&lt;p class=&quot;pull-right&quot;&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;إشتري الأن&lt;/a&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"promotion";s:6:"status";s:1:"1";s:12:"module_class";s:13:" hidden-phone";s:10:"sort_order";s:1:"1";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:26:"sign up for our newsletter";i:2;s:47:"إشترك في النشرة الإخبارية";}s:11:"description";a:2:{i:1;s:778:"&lt;div class=&quot;newsletter-service&quot;&gt;\r\n&lt;div class=&quot;row-fluid&quot;&gt;\r\n&lt;div class=&quot;span6 newsletter&quot;&gt;\r\n&lt;h2&gt;sign up for our newsletter&lt;/h2&gt;\r\n\r\n&lt;div class=&quot;newsletter-submit&quot;&gt;&lt;input alt=&quot;username&quot; class=&quot;inputbox&quot; name=&quot;email&quot; size=&quot;31&quot; type=&quot;text&quot; value=&quot;Type your email&quot; /&gt;&lt;input class=&quot;button&quot; name=&quot;Submit&quot; type=&quot;submit&quot; value=&quot;Sign up&quot; /&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span6 service&quot;&gt;\r\n&lt;h2&gt;24/7 Customer Service&lt;/h2&gt;\r\n\r\n&lt;p&gt;Nulla rhoncus blandit lacus in scelerisque leo donec urna lobor tis molestie.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:957:"&lt;div class=&quot;newsletter-service&quot;&gt;\r\n&lt;div class=&quot;row-fluid&quot;&gt;\r\n&lt;div class=&quot;span6 newsletter&quot;&gt;\r\n&lt;h2&gt;إشترك في النشرة الإخبارية&lt;/h2&gt;\r\n\r\n&lt;div class=&quot;newsletter-submit&quot;&gt;&lt;input alt=&quot;username&quot; class=&quot;inputbox&quot; name=&quot;email&quot; size=&quot;31&quot; type=&quot;text&quot; value=&quot;أكتب بريدك الإلكتروني&quot; /&gt;&lt;input class=&quot;button&quot; name=&quot;Submit&quot; type=&quot;submit&quot; value=&quot;Sign up&quot; /&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;span6 service&quot;&gt;\r\n&lt;h2&gt;24/7 خدمة عملاء&lt;/h2&gt;\r\n\r\n&lt;p&gt;تعد من استدعى تزامناً بالسيطرة. فصل يذكر أطراف بالرّغم بل, ان الى فسقط وتنامت الحدود و الأبعاد في الهند و سنغافورة.&lt;/p&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}i:3;a:8:{s:12:"module_title";a:2:{i:1;s:18:"Menu in Footer Top";i:2;s:27:"القائمة السفلى";}s:11:"description";a:2:{i:1;s:540:"&lt;div class=&quot;listmenu navbar&quot;&gt;\r\n&lt;ul class=&quot;nav&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Fusce hen&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Proin imper&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Sed non sem &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Nunc vel&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Curabitur &lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;Fermentum &lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";i:2;s:542:"&lt;div class=&quot;listmenu navbar&quot;&gt;\r\n&lt;ul class=&quot;nav&quot;&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;السعودية&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;العراق&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;اليمن&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;الكويت&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;مصر&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot;&gt;قطر&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:4;a:8:{s:12:"module_title";a:2:{i:1;s:12:"Follow us on";i:2;s:26:"إبقى على إتصال";}s:11:"description";a:2:{i:1;s:450:"&lt;div class=&quot;social&quot;&gt;\r\n&lt;h4 class=&quot;pull-left&quot;&gt;Follow us on&lt;/h4&gt;\r\n\r\n&lt;div class=&quot;custom_follow&quot;&gt;&lt;a class=&quot;facebook&quot; href=&quot;#&quot;&gt;facebook&lt;/a&gt; &lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;twitter&lt;/a&gt; &lt;a class=&quot;mail&quot; href=&quot;#&quot;&gt;Mails&lt;/a&gt; &lt;a class=&quot;rss&quot; href=&quot;#&quot;&gt;rss&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:2;s:464:"&lt;div class=&quot;social&quot;&gt;\r\n&lt;h4 class=&quot;pull-left&quot;&gt;إبقى على إتصال&lt;/h4&gt;\r\n\r\n&lt;div class=&quot;custom_follow&quot;&gt;&lt;a class=&quot;facebook&quot; href=&quot;#&quot;&gt;facebook&lt;/a&gt; &lt;a class=&quot;twitter&quot; href=&quot;#&quot;&gt;twitter&lt;/a&gt; &lt;a class=&quot;mail&quot; href=&quot;#&quot;&gt;Mails&lt;/a&gt; &lt;a class=&quot;rss&quot; href=&quot;#&quot;&gt;rss&lt;/a&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:65:{s:13:"default_theme";s:13:"pav_bikestore";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"0";s:30:"listing_products_columns_small";s:1:"2";s:34:"listing_products_columns_minismall";s:1:"1";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"0";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:44:"79-99 Beaver Street, New York, NY 10005, USA";s:17:"location_latitude";s:9:"40.705423";s:18:"location_longitude";s:10:"-74.008616";s:18:"contact_customhtml";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:23:"enable_development_mode";s:0:"";s:19:"widget_contact_data";a:2:{i:1;s:331:"&lt;ul&gt;\r\n	&lt;li class=&quot;members&quot;&gt;We have 152 guests and 14 members online&lt;/li&gt;\r\n	&lt;li class=&quot;phone&quot;&gt;Phone: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;fax&quot;&gt;Fax: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;email&quot;&gt;Email: example@example.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:350:"&lt;ul&gt;\r\n	&lt;li class=&quot;members&quot;&gt;لدينا 152 زائر و 14 عضو&lt;/li&gt;\r\n	&lt;li class=&quot;phone&quot;&gt;الهاتف: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;fax&quot;&gt;الفاكس: +01 888 (000) 1234&lt;/li&gt;\r\n	&lt;li class=&quot;email&quot;&gt;البريد: example@example.com&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:6:"google";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:19:"enable_compress_css";s:0:"";s:17:"exclude_css_files";s:13:"bootstrap.css";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:0:"";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1),
(0, 'banner', 'banner_module', 'a:4:{i:0;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"371";s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:1;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"371";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"371";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:3;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"371";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'category', 'category_module', 'a:5:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:3;a:4:{s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:4;a:4:{s:9:"layout_id";s:2:"13";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"13";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"3";s:9:"layout_id";s:2:"13";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavcontentslider', 'pavcontentslider_module', 'a:1:{i:0;a:12:{s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"0";s:13:"text_interval";s:4:"8000";s:5:"width";s:3:"940";s:6:"height";s:3:"438";s:15:"image_navigator";s:1:"1";s:13:"navimg_weight";s:3:"176";s:13:"navimg_height";s:2:"81";s:12:"banner_image";a:5:{i:1;a:4:{s:5:"image";s:24:"data/slider/img-demo.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:6:"Mauris";i:2;s:13:"عرض خاص";}s:11:"description";a:2:{i:1;s:409:"&lt;p&gt;Nulla a odio vel urna eleifend scrisque&amp;nbsp;&amp;nbsp;&amp;nbsp; Sed quam lorem ullamcorper vitae conse quat convallis quis odio Curabitur varius urna sed orci imperdiet eget accumsan leo dignissim elementum, ante erat sagittis rhoncus diam eget arcu tempor faucibus fringilla quam vulputate.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;Shop now&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:613:"&lt;p&gt;عن مدن كثيرة الحكومة الإيطالية, لم وكسبت الثالث مدن. بعض للصين اليها والنرويج هو. الصينية المجتمع هو بحق, فقد الذود العظمى النزاع في, و على يقوم لغات السبب. ثم انه لدحر الشطر الفترة, أن وقد هُزم ٢٠٠٤ المتساقطة،. وإعلان بريطانيا قد بها, قِبل الحكومة إذ وصل, كل هذه بسبب إحتار.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;إشتري الأن&lt;/a&gt;&lt;/p&gt;\r\n";}}i:2;a:4:{s:5:"image";s:24:"data/slider/img-demo.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:9:"Curabitur";i:2;s:23:"منتجات مميزة";}s:11:"description";a:2:{i:1;s:409:"&lt;p&gt;Nulla a odio vel urna eleifend scrisque&amp;nbsp;&amp;nbsp;&amp;nbsp; Sed quam lorem ullamcorper vitae conse quat convallis quis odio Curabitur varius urna sed orci imperdiet eget accumsan leo dignissim elementum, ante erat sagittis rhoncus diam eget arcu tempor faucibus fringilla quam vulputate.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;Shop now&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:611:"&lt;p&gt;عن مدن كثيرة الحكومة الإيطالية, لم وكسبت الثالث مدن. بعض للصين اليها والنرويج هو. الصينية المجتمع هو بحق, فقد الذود العظمى النزاع في, و على يقوم لغات السبب. ثم انه لدحر الشطر الفترة, أن وقد هُزم ٢٠٠٤ المتساقطة،. وإعلان بريطانيا قد بها, قِبل الحكومة إذ وصل, كل هذه بسبب إحتار.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;إشتري الأن&lt;/a&gt;&lt;/p&gt;";}}i:3;a:4:{s:5:"image";s:24:"data/slider/img-demo.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:8:"Sagittis";i:2;s:21:"توصيل مجاني";}s:11:"description";a:2:{i:1;s:409:"&lt;p&gt;Nulla a odio vel urna eleifend scrisque&amp;nbsp;&amp;nbsp;&amp;nbsp; Sed quam lorem ullamcorper vitae conse quat convallis quis odio Curabitur varius urna sed orci imperdiet eget accumsan leo dignissim elementum, ante erat sagittis rhoncus diam eget arcu tempor faucibus fringilla quam vulputate.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;Shop now&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:613:"&lt;p&gt;عن مدن كثيرة الحكومة الإيطالية, لم وكسبت الثالث مدن. بعض للصين اليها والنرويج هو. الصينية المجتمع هو بحق, فقد الذود العظمى النزاع في, و على يقوم لغات السبب. ثم انه لدحر الشطر الفترة, أن وقد هُزم ٢٠٠٤ المتساقطة،. وإعلان بريطانيا قد بها, قِبل الحكومة إذ وصل, كل هذه بسبب إحتار.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;إشتري الأن&lt;/a&gt;&lt;/p&gt;\r\n";}}i:4;a:4:{s:5:"image";s:24:"data/slider/img-demo.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:7:"Rhoncus";i:2;s:25:"أحدث المنتجات";}s:11:"description";a:2:{i:1;s:409:"&lt;p&gt;Nulla a odio vel urna eleifend scrisque&amp;nbsp;&amp;nbsp;&amp;nbsp; Sed quam lorem ullamcorper vitae conse quat convallis quis odio Curabitur varius urna sed orci imperdiet eget accumsan leo dignissim elementum, ante erat sagittis rhoncus diam eget arcu tempor faucibus fringilla quam vulputate.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;Shop now&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:613:"&lt;p&gt;عن مدن كثيرة الحكومة الإيطالية, لم وكسبت الثالث مدن. بعض للصين اليها والنرويج هو. الصينية المجتمع هو بحق, فقد الذود العظمى النزاع في, و على يقوم لغات السبب. ثم انه لدحر الشطر الفترة, أن وقد هُزم ٢٠٠٤ المتساقطة،. وإعلان بريطانيا قد بها, قِبل الحكومة إذ وصل, كل هذه بسبب إحتار.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;إشتري الأن&lt;/a&gt;&lt;/p&gt;\r\n";}}i:5;a:4:{s:5:"image";s:24:"data/slider/img-demo.jpg";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:8:"Accumsan";i:2;s:23:"رحلات سياحية";}s:11:"description";a:2:{i:1;s:409:"&lt;p&gt;Nulla a odio vel urna eleifend scrisque&amp;nbsp;&amp;nbsp;&amp;nbsp; Sed quam lorem ullamcorper vitae conse quat convallis quis odio Curabitur varius urna sed orci imperdiet eget accumsan leo dignissim elementum, ante erat sagittis rhoncus diam eget arcu tempor faucibus fringilla quam vulputate.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;Shop now&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:611:"&lt;p&gt;عن مدن كثيرة الحكومة الإيطالية, لم وكسبت الثالث مدن. بعض للصين اليها والنرويج هو. الصينية المجتمع هو بحق, فقد الذود العظمى النزاع في, و على يقوم لغات السبب. ثم انه لدحر الشطر الفترة, أن وقد هُزم ٢٠٠٤ المتساقطة،. وإعلان بريطانيا قد بها, قِبل الحكومة إذ وصل, كل هذه بسبب إحتار.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;a class=&quot;bnt&quot; href=&quot;#&quot;&gt;إشتري الأن&lt;/a&gt;&lt;/p&gt;";}}}}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"700";s:15:"general_lheight";s:3:"350";s:14:"general_swidth";s:3:"300";s:15:"general_sheight";s:3:"150";s:14:"general_xwidth";s:3:"180";s:15:"general_xheight";s:2:"90";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"250";s:15:"general_cheight";s:3:"145";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"3";s:22:"cat_leading_image_type";s:1:"s";s:24:"cat_secondary_image_type";s:1:"s";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:12:"100858303516";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'bestseller', 'bestseller_module', 'a:4:{i:0;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"75";s:12:"image_height";s:2:"62";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:1;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"75";s:12:"image_height";s:2:"62";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:2;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"75";s:12:"image_height";s:2:"62";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:3;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"75";s:12:"image_height";s:2:"62";s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'special', 'special_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:3:"212";s:12:"image_height";s:3:"176";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'config', 'config_image_category_height', '258', 0),
(0, 'config', 'config_image_category_width', '700', 0),
(0, 'pavproductcarousel', 'pavproductcarousel_module', 'a:1:{i:1;a:13:{s:11:"description";a:2:{i:1;s:0:"";i:3;s:0:"";}s:4:"tabs";a:1:{i:0;s:10:"mostviewed";}s:5:"width";s:3:"240";s:6:"height";s:3:"200";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'config', 'config_image_thumb_height', '283', 0),
(0, 'config', 'config_image_thumb_width', '340', 0),
(0, 'config', 'config_image_compare_height', '77', 0),
(0, 'config', 'config_image_compare_width', '92', 0),
(0, 'config', 'config_image_related_height', '168', 0),
(0, 'config', 'config_image_related_width', '202', 0),
(0, 'config', 'config_image_additional_height', '77', 0),
(0, 'config', 'config_image_additional_width', '92', 0),
(0, 'config', 'config_image_product_height', '200', 0),
(0, 'config', 'config_image_product_width', '240', 0),
(0, 'config', 'config_image_popup_height', '416', 0),
(0, 'config', 'config_image_popup_width', '500', 0),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'config', 'config_image_cart_height', '46', 0),
(0, 'config', 'config_image_cart_width', '55', 0),
(0, 'config', 'config_image_wishlist_height', '46', 0),
(0, 'config', 'config_image_wishlist_width', '55', 0),
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=445 ;

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
--('module', 'pavmegamenu'),
('module', 'pavproductcarousel'),
('module', 'pavcustom'),
('module', 'pavcontentslider'),
('module', 'special'),
('module', 'latest'),
('module', 'featured'),
('module', 'bestseller'),
('module', 'pavblog'),
('module', 'pavblogcategory'),
('module', 'pavblogcomment'),
('module', 'pavbloglatest');