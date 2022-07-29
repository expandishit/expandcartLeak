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
  PRIMARY KEY (`megamenu_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

--
-- Dumping data for table `megamenu`
--

INSERT INTO `megamenu` (`megamenu_id`, `image`, `parent_id`, `is_group`, `width`, `submenu_width`, `colum_width`, `submenu_colum_width`, `item`, `colums`, `type`, `is_content`, `show_title`, `type_submenu`, `level_depth`, `published`, `store_id`, `position`, `show_sub`, `url`, `target`, `privacy`, `position_type`, `menu_class`, `description`, `content_text`, `submenu_content`, `level`, `left`, `right`) VALUES
(1, '', 0, 2, NULL, NULL, NULL, NULL, NULL, '1', '', 2, 1, '1', 0, 1, 0, 0, 0, NULL, NULL, 0, 'top', NULL, NULL, NULL, NULL, -5, 34, 47),
(2, '', 1, 0, NULL, NULL, NULL, 'col1=3, col2=3, col3=6', '20', '3', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(3, '', 1, 0, NULL, NULL, NULL, '', '25', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0),
(4, '', 3, 0, NULL, NULL, NULL, '', '31', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0),
(5, '', 1, 0, NULL, NULL, NULL, '', '18', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-parrent', NULL, '', '', 0, 0, 0),
(7, '', 1, 0, NULL, NULL, NULL, '', '34', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(8, '', 2, 1, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '&lt;p&gt;test&lt;/p&gt;\r\n', 0, 0, 0),
(9, '', 2, 1, NULL, NULL, NULL, '', '59', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(10, '', 8, 0, NULL, NULL, NULL, '', '61', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(11, '', 8, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(12, '', 8, 0, NULL, NULL, NULL, '', '27', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(13, '', 8, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(14, '', 8, 0, NULL, NULL, NULL, '', '26', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(15, '', 8, 0, NULL, NULL, NULL, '', '27', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(16, '', 8, 0, NULL, NULL, NULL, '', '65', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(17, '', 9, 0, NULL, NULL, NULL, '', '26', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(18, '', 9, 0, NULL, NULL, NULL, '', '27', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(19, '', 9, 0, NULL, NULL, NULL, '', '60', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(20, '', 9, 0, NULL, NULL, NULL, '', '68', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(21, '', 9, 0, NULL, NULL, NULL, '', '63', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(22, '', 9, 0, NULL, NULL, NULL, '', '61', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(23, '', 9, 0, NULL, NULL, NULL, '', '62', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '', '', 0, 0, 0),
(24, '', 2, 0, NULL, NULL, NULL, '', '', '1', 'html', 1, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', 'pav-menu-child', NULL, '&lt;div class=&quot;pav-menu-video&quot;&gt;&lt;embed height=&quot;157&quot; src=&quot;http://www.youtube.com/v/NBuLeA7nNFk&quot; type=&quot;application/x-shockwave-flash&quot; width=&quot;270&quot;&gt;&lt;/embed&gt;\r\n&lt;h3&gt;Lorem ipsum dolor sit&lt;/h3&gt;\r\n\r\n&lt;p&gt;Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.&lt;/p&gt;\r\n&lt;/div&gt;\r\n', '', 0, 0, 0),
(25, '', 3, 0, NULL, NULL, NULL, '', '28', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(29, '', 3, 0, NULL, NULL, NULL, '', '29', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(31, '', 3, 0, NULL, NULL, NULL, '', '69', '1', 'category', 0, 1, 'menu', 0, 1, 0, 99, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(32, '', 3, 0, NULL, NULL, NULL, '', '28', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(33, '', 3, 0, NULL, NULL, NULL, '', '32', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(34, '', 3, 0, NULL, NULL, NULL, '', '30', '1', 'category', 0, 1, 'menu', 0, 1, 0, 7, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(35, '', 3, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 8, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(36, '', 3, 0, NULL, NULL, NULL, '', '36', '1', 'category', 0, 1, 'menu', 0, 1, 0, 9, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(37, '', 1, 0, NULL, NULL, NULL, '', '24', '1', 'url', 0, 1, 'menu', 0, 1, 0, 99, 0, 'index.php?route=pavblog/blogs', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(40, '', 1, 0, NULL, NULL, NULL, '', '', '1', 'url', 0, 1, 'menu', 0, 1, 0, 1, 0, '?route=common/home', NULL, 0, 'top', 'home', NULL, '', '', 0, 0, 0),
(41, '', 5, 0, NULL, NULL, NULL, '', '64', '1', 'category', 0, 1, 'menu', 0, 1, 0, 1, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(42, '', 5, 0, NULL, NULL, NULL, '', '46', '1', 'category', 0, 1, 'menu', 0, 1, 0, 2, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(43, '', 5, 0, NULL, NULL, NULL, '', '67', '1', 'category', 0, 1, 'menu', 0, 1, 0, 3, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(44, '', 5, 0, NULL, NULL, NULL, '', '66', '1', 'category', 0, 1, 'menu', 0, 1, 0, 4, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(45, '', 5, 0, NULL, NULL, NULL, '', '65', '1', 'category', 0, 1, 'menu', 0, 1, 0, 5, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0),
(46, '', 5, 0, NULL, NULL, NULL, '', '45', '1', 'category', 0, 1, 'menu', 0, 1, 0, 6, 0, '', NULL, 0, 'top', '', NULL, '', '', 0, 0, 0);

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
(11, 4, 'Accessories', ''),
(11, 6, 'Accessories', ''),
(24, 6, 'Lorem ipsum dolor sit ', ''),
(41, 6, 'Baby', ''),
(40, 1, 'Home', ''),
(2, 1, 'Men', ''),
(2, 4, 'Men', ''),
(2, 6, 'Men', ''),
(8, 1, 'Bags &amp; business', ''),
(8, 4, 'Bags &amp; business', ''),
(8, 6, 'Bags &amp; business', ''),
(9, 1, 'Belts', ''),
(9, 4, 'Belts', ''),
(9, 6, 'Belts', ''),
(16, 1, 'Formal wear', ''),
(16, 4, 'Formal wear', ''),
(16, 6, 'Formal wear', ''),
(10, 1, 'Watches', ''),
(10, 4, 'Watches', ''),
(10, 6, 'Watches', ''),
(15, 1, 'Silver jewelry', ''),
(15, 4, 'Silver jewelry', ''),
(15, 6, 'Silver jewelry', ''),
(14, 1, 'Formal wear', ''),
(14, 4, 'Formal wear', ''),
(14, 6, 'Formal wear', ''),
(13, 1, 'Shoes', ''),
(13, 4, 'Shoes', ''),
(13, 6, 'Shoes', ''),
(12, 1, 'Silver jewelry', ''),
(12, 4, 'Silver jewelry', ''),
(12, 6, 'Silver jewelry', ''),
(17, 1, 'Formal wear', ''),
(17, 4, 'Formal wear', ''),
(17, 6, 'Formal wear', ''),
(23, 1, 'Bags &amp; business', ''),
(23, 4, 'Bags &amp; business', ''),
(23, 6, 'Bags &amp; business', ''),
(18, 1, 'Silver jewelry', ''),
(18, 4, 'Silver jewelry', ''),
(18, 6, 'Silver jewelry', ''),
(19, 1, 'Accessories', ''),
(19, 4, 'Accessories', ''),
(19, 6, 'Accessories', ''),
(21, 1, 'Shoes', ''),
(21, 4, 'Shoes', ''),
(21, 6, 'Shoes', ''),
(22, 1, 'Watches', ''),
(22, 4, 'Watches', ''),
(22, 6, 'Watches', ''),
(20, 1, 'Wallets', ''),
(20, 4, 'Wallets', ''),
(20, 6, 'Wallets', ''),
(3, 1, 'Women', ''),
(3, 4, 'Women', ''),
(3, 6, 'Women', ''),
(4, 1, 'Scanners', ''),
(4, 4, 'Scanners', ''),
(4, 6, 'Scanners', ''),
(40, 4, 'Home', ''),
(40, 6, 'Home', ''),
(25, 1, 'Shoes', ''),
(25, 4, 'Shoes', ''),
(25, 6, 'Shoes', ''),
(36, 1, 'Dresses', ''),
(36, 4, 'Dresses', ''),
(36, 6, 'Dresses', ''),
(35, 1, 'Evening', ''),
(35, 4, 'Evening', ''),
(35, 6, 'Evening', ''),
(34, 1, 'Sunglasses', ''),
(34, 4, 'Sunglasses', ''),
(34, 6, 'Sunglasses', ''),
(33, 1, 'Web Cameras', ''),
(33, 4, 'Web Cameras', ''),
(33, 6, 'Web Cameras', ''),
(32, 1, 'Shoes', ''),
(32, 4, 'Shoes', ''),
(32, 6, 'Shoes', ''),
(29, 1, 'Handbags', ''),
(29, 4, 'Handbags', ''),
(29, 6, 'Handbags', ''),
(5, 1, 'Kids', ''),
(5, 4, 'Kids', ''),
(5, 6, 'Kids', ''),
(7, 1, 'Footwear', ''),
(7, 4, 'Footwear', ''),
(7, 6, 'Footwear', ''),
(37, 6, 'World of McGregor ', ''),
(37, 4, 'World of McGregor ', ''),
(37, 1, 'Blog', ''),
(24, 4, 'Lorem ipsum dolor sit ', ''),
(41, 4, 'Baby', ''),
(41, 1, 'Baby', ''),
(42, 1, 'Kids accessories', ''),
(42, 4, 'Kids accessories', ''),
(42, 6, 'Kids accessories', ''),
(43, 1, 'Accessories for mom', ''),
(43, 4, 'Accessories for mom', ''),
(43, 6, 'Accessories for mom', ''),
(44, 1, 'Kids shoes', ''),
(44, 4, 'Kids shoes', ''),
(44, 6, 'Kids shoes', ''),
(45, 1, 'Kids travel &amp; school', ''),
(45, 4, 'Kids travel &amp; school', ''),
(45, 6, 'Kids travel &amp; school', ''),
(46, 1, 'Toddler shoes', ''),
(46, 4, 'Toddler shoes', ''),
(46, 6, 'Toddler shoes', ''),
(11, 1, 'Accessories', ''),
(31, 1, 'Sollemnes', ''),
(31, 4, 'Sollemnes', ''),
(31, 6, 'Sollemnes', ''),
(24, 1, 'Lorem ipsum dolor sit ', '');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4422 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'config', 'config_image_cart_height', '80', 0),
(0, 'config', 'config_image_cart_width', '80', 0),
(0, 'config', 'config_image_wishlist_height', '80', 0),
(0, 'config', 'config_image_wishlist_width', '80', 0),
(0, 'config', 'config_image_compare_height', '110', 0),
(0, 'config', 'config_image_compare_width', '110', 0),
(0, 'config', 'config_image_related_height', '245', 0),
(0, 'config', 'config_image_related_width', '220', 0),
(0, 'config', 'config_image_additional_height', '78', 0),
(0, 'config', 'config_image_additional_width', '78', 0),
(0, 'config', 'config_image_product_height', '245', 0),
(0, 'config', 'config_image_product_width', '220', 0),
(0, 'config', 'config_image_popup_height', '500', 0),
(0, 'config', 'config_image_popup_width', '500', 0),
(0, 'config', 'config_image_thumb_height', '270', 0),
(0, 'config', 'config_image_thumb_width', '270', 0),
(0, 'config', 'config_image_category_height', '143', 0),
(0, 'config', 'config_image_category_width', '940', 0),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavmegamenu', 'pavmegamenu_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'bestseller', 'bestseller_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";s:9:"layout_id";s:2:"13";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"680";s:15:"general_lheight";s:3:"383";s:14:"general_swidth";s:3:"250";s:15:"general_sheight";s:3:"250";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"80";s:14:"rss_limit_item";s:188:"&lt;b&gt;Notice&lt;/b&gt;: Undefined index: rss_limit_item in &lt;b&gt;E:\\xampplite\\htdocs\\pav_asenti\\admin\\view\\template\\module\\pavblog\\modules.tpl&lt;/b&gt; on line &lt;b&gt;58&lt;/b&gt;";s:26:"keyword_listing_blogs_page";s:26:"blogs, blog opencart theme";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"683";s:15:"general_cheight";s:3:"200";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"6";s:22:"cat_leading_image_type";s:1:"l";s:24:"cat_secondary_image_type";s:1:"l";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:2:"10";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'pavblogcomment', 'pavblogcomment_module', 'a:1:{i:1;a:5:{s:5:"limit";s:1:"5";s:9:"layout_id";s:2:"14";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'latest', 'latest_module', 'a:2:{i:0;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:1;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:1:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"14";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"4";}}', 1),
(0, 'pavcontentslider', 'pavcontentslider_module', 'a:1:{i:0;a:12:{s:9:"layout_id";s:1:"1";s:8:"position";s:9:"slideshow";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"0";s:13:"text_interval";s:4:"8000";s:5:"width";s:3:"940";s:6:"height";s:3:"439";s:15:"image_navigator";s:1:"0";s:13:"navimg_weight";s:2:"90";s:13:"navimg_height";s:2:"90";s:12:"banner_image";a:5:{i:1;a:4:{s:5:"image";s:22:"data/demo/slide1_1.png";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:88:"Design &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;from our blog&lt;/small&gt;";i:2;s:98:"تصميم &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;من مدونتنا&lt;/small&gt;";}s:11:"description";a:2:{i:1;s:198:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc facilisis fringilla nisi euismod Morbi sed adipiscing eleifend, dolor risus congue mi aliquet dolor tellus et ante.&lt;/p&gt;\r\n";i:2;s:264:"&lt;p&gt;أبجد هوز دولور الجلوس امات، والبحوث. مرضي أداء سد إنقاذ سهلة والآن، وقبل الأرض مرة أخرى، والألم من الألم والضحك من التخطيط الموز بلدي.&lt;/p&gt;\r\n";}}i:2;a:4:{s:5:"image";s:22:"data/demo/slide1_2.png";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:88:"Design &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;from our blog&lt;/small&gt;";i:2;s:98:"تصميم &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;من مدونتنا&lt;/small&gt;";}s:11:"description";a:2:{i:1;s:198:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc facilisis fringilla nisi euismod Morbi sed adipiscing eleifend, dolor risus congue mi aliquet dolor tellus et ante.&lt;/p&gt;\r\n";i:2;s:266:"&lt;p&gt;أبجد هوز دولور الجلوس امات، والبحوث. مرضي أداء سد إنقاذ سهلة والآن، وقبل الأرض مرة أخرى، والألم من الألم والضحك من التخطيط الموز بلدي.&lt;/p&gt;\r\n\r\n";}}i:3;a:4:{s:5:"image";s:22:"data/demo/slide1_3.png";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:88:"Design &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;from our blog&lt;/small&gt;";i:2;s:98:"تصميم &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;من مدونتنا&lt;/small&gt;";}s:11:"description";a:2:{i:1;s:198:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc facilisis fringilla nisi euismod Morbi sed adipiscing eleifend, dolor risus congue mi aliquet dolor tellus et ante.&lt;/p&gt;\r\n";i:2;s:266:"&lt;p&gt;أبجد هوز دولور الجلوس امات، والبحوث. مرضي أداء سد إنقاذ سهلة والآن، وقبل الأرض مرة أخرى، والألم من الألم والضحك من التخطيط الموز بلدي.&lt;/p&gt;\r\n\r\n";}}i:4;a:4:{s:5:"image";s:22:"data/demo/slide1_4.png";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:88:"Design &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;from our blog&lt;/small&gt;";i:2;s:98:"تصميم &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;من مدونتنا&lt;/small&gt;";}s:11:"description";a:2:{i:1;s:198:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc facilisis fringilla nisi euismod Morbi sed adipiscing eleifend, dolor risus congue mi aliquet dolor tellus et ante.&lt;/p&gt;\r\n";i:2;s:264:"&lt;p&gt;أبجد هوز دولور الجلوس امات، والبحوث. مرضي أداء سد إنقاذ سهلة والآن، وقبل الأرض مرة أخرى، والألم من الألم والضحك من التخطيط الموز بلدي.&lt;/p&gt;\r\n";}}i:5;a:4:{s:5:"image";s:22:"data/demo/slide1_5.png";s:4:"link";s:1:"#";s:5:"title";a:2:{i:1;s:88:"Design &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;from our blog&lt;/small&gt;";i:2;s:98:"تصميم &lt;span&gt;2013&lt;/span&gt;&lt;br/&gt; &lt;small&gt;من مدونتنا&lt;/small&gt;";}s:11:"description";a:2:{i:1;s:198:"&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc facilisis fringilla nisi euismod Morbi sed adipiscing eleifend, dolor risus congue mi aliquet dolor tellus et ante.&lt;/p&gt;\r\n";i:2;s:264:"&lt;p&gt;أبجد هوز دولور الجلوس امات، والبحوث. مرضي أداء سد إنقاذ سهلة والآن، وقبل الأرض مرة أخرى، والألم من الألم والضحك من التخطيط الموز بلدي.&lt;/p&gt;\r\n";}}}}}', 1),
(0, 'pavproducttabs', 'pavproducttabs_module', 'a:1:{i:1;a:11:{s:11:"description";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"tabs";a:3:{i:0;s:6:"latest";i:1;s:10:"bestseller";i:2;s:7:"special";}s:5:"width";s:3:"220";s:6:"height";s:3:"245";s:12:"itemsperpage";s:1:"3";s:4:"cols";s:1:"3";s:5:"limit";s:1:"6";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}}', 1),
(0, 'carousel', 'carousel_module', 'a:1:{i:0;a:9:{s:9:"banner_id";s:1:"8";s:5:"limit";s:1:"5";s:6:"scroll";s:1:"3";s:5:"width";s:2:"80";s:6:"height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"0";s:10:"sort_order";i:2;}}', 1),
(0, 'featured', 'product', '', 0),
(0, 'featured', 'featured_product', '44,34,84,71', 0),
(0, 'featured', 'featured_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"6";s:11:"image_width";s:3:"220";s:12:"image_height";s:3:"245";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:3;}}', 1),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:3:{i:1;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:4;s:0:"";i:6;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:3:"250";s:6:"height";s:3:"165";s:4:"cols";s:1:"2";s:5:"limit";s:1:"2";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:4;}i:2;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:4;s:0:"";i:6;s:0:"";}s:4:"tabs";s:8:"featured";s:5:"width";s:2:"70";s:6:"height";s:2:"47";s:4:"cols";s:1:"4";s:5:"limit";s:1:"4";s:9:"layout_id";s:2:"14";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:3;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:4;s:0:"";i:6;s:0:"";}s:4:"tabs";s:10:"mostviewed";s:5:"width";s:2:"70";s:6:"height";s:2:"47";s:4:"cols";s:1:"4";s:5:"limit";s:1:"4";s:9:"layout_id";s:2:"14";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'category', 'category_module', 'a:6:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}i:3;a:4:{s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:4;a:4:{s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:5;a:4:{s:9:"layout_id";s:2:"13";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'special', 'special_module', 'a:3:{i:0;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}i:1;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}i:2;a:7:{s:5:"limit";s:1:"1";s:11:"image_width";s:3:"200";s:12:"image_height";s:3:"200";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'banner', 'banner_module', 'a:7:{i:0;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"540";s:9:"layout_id";s:1:"1";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";i:4;}i:1;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"540";s:9:"layout_id";s:1:"3";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"540";s:9:"layout_id";s:1:"2";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:3;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"540";s:9:"layout_id";s:2:"12";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:4;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"540";s:9:"layout_id";s:1:"5";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:5;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"540";s:9:"layout_id";s:2:"13";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:6;a:7:{s:9:"banner_id";s:1:"9";s:5:"width";s:3:"220";s:6:"height";s:3:"540";s:9:"layout_id";s:1:"6";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavcustom', 'pavcustom_module', 'a:8:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:325:"&lt;article class=&quot;pav-summer&quot;&gt;&lt;img alt=&quot;summer jacket&quot; src=&quot;image/data/demo/banner-top1.png&quot; /&gt;\r\n&lt;hgroup&gt;\r\n	&lt;h3&gt;Summer jacket &lt;span&gt;$129&lt;/span&gt;&lt;/h3&gt;\r\n\r\n	&lt;h5&gt;&lt;a href=&quot;#&quot;&gt;shop now&lt;/a&gt;&lt;/h5&gt;\r\n&lt;/hgroup&gt;\r\n&lt;/article&gt;";i:2;s:346:"&lt;article class=&quot;pav-summer&quot;&gt;&lt;img alt=&quot;summer jacket&quot; src=&quot;image/data/demo/banner-top1.png&quot; /&gt;\r\n&lt;hgroup&gt;\r\n	&lt;h3&gt;جاكيت للصيف &lt;span&gt;$129&lt;/span&gt;&lt;/h3&gt;\r\n\r\n	&lt;h5&gt;&lt;a href=&quot;#&quot;&gt;إشتري الأن&lt;/a&gt;&lt;/h5&gt;\r\n&lt;/hgroup&gt;\r\n&lt;/article&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"promotion";s:6:"status";s:1:"1";s:12:"module_class";s:6:"summer";s:10:"sort_order";s:1:"1";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:406:"&lt;article class=&quot;pav-dapibus&quot;&gt;&lt;img alt=&quot;dapibus sed&quot; src=&quot;image/data/demo/banner-top2.png&quot; /&gt;\r\n&lt;p&gt;Lorem ipsum dolor sit amet, consectetur suscipit tempor diam.&lt;/p&gt;\r\n\r\n&lt;hgroup&gt;\r\n	&lt;h3&gt;Dapibus sed &lt;span&gt;$105&lt;/span&gt;&lt;/h3&gt;\r\n\r\n	&lt;h5&gt;&lt;a href=&quot;#&quot;&gt;shop now&lt;/a&gt;&lt;/h5&gt;\r\n&lt;/hgroup&gt;\r\n&lt;/article&gt;";i:2;s:483:"&lt;article class=&quot;pav-dapibus&quot;&gt;&lt;img alt=&quot;dapibus sed&quot; src=&quot;image/data/demo/banner-top2.png&quot; /&gt;\r\n&lt;p&gt;أبجد هوز دولور الجلوس امات، والبحوث. مرضي أداء سد إنقاذ سهلة والآن.&lt;/p&gt;\r\n\r\n&lt;hgroup&gt;\r\n	&lt;h3&gt;هوز دولور &lt;span&gt;$105&lt;/span&gt;&lt;/h3&gt;\r\n\r\n	&lt;h5&gt;&lt;a href=&quot;#&quot;&gt;إشتري الأن&lt;/a&gt;&lt;/h5&gt;\r\n&lt;/hgroup&gt;\r\n&lt;/article&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:9:"promotion";s:6:"status";s:1:"1";s:12:"module_class";s:7:"dapibus";s:10:"sort_order";s:1:"2";}i:3;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:635:"&lt;article class=&quot;pav-custom-block-three hidden-phone clearfix&quot;&gt;\r\n&lt;div class=&quot;col-left&quot;&gt;\r\n&lt;h3&gt;DUIS SAPIEN &lt;span&gt;50%&lt;/span&gt; off&lt;/h3&gt;\r\n\r\n&lt;p&gt;Lorem ipsum dolor amet consect dutus scrisque.&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-right&quot;&gt;&lt;img alt=&quot;sale 50%&quot; src=&quot;image/data/demo/custom-block03.png&quot; /&gt;\r\n&lt;p&gt;Nullam bibendum egestas odio, mollis dapibus&lt;br /&gt;\r\npretium leo nibh non urna.&lt;/p&gt;\r\n\r\n&lt;h5&gt;&lt;a class=&quot;button&quot; href=&quot;#&quot;&gt;click here&lt;/a&gt;&lt;/h5&gt;\r\n&lt;/div&gt;\r\n&lt;/article&gt;";i:2;s:744:"&lt;article class=&quot;pav-custom-block-three hidden-phone clearfix&quot;&gt;\r\n&lt;div class=&quot;col-left&quot;&gt;\r\n&lt;h3&gt;هوز دولور &lt;span&gt;50%&lt;/span&gt; خصم&lt;/h3&gt;\r\n\r\n&lt;p&gt;أبجد هوز دولور الجلوس امات، والبحوث.&lt;/p&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;col-right&quot;&gt;&lt;img alt=&quot;sale 50%&quot; src=&quot;image/data/demo/custom-block03.png&quot; /&gt;\r\n&lt;p&gt;أبجد هوز دولور الجلوس امات، والبحوث. مرضي أداء سد إنقاذ سهلة والآن، وقبل الأرض مرة أخرى.&lt;/p&gt;\r\n\r\n&lt;h5&gt;&lt;a class=&quot;button&quot; href=&quot;#&quot;&gt;إضغط هنا&lt;/a&gt;&lt;/h5&gt;\r\n&lt;/div&gt;\r\n&lt;/article&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:12:"module_class";s:12:"banner_block";s:10:"sort_order";s:1:"2";}i:4;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:1552:"&lt;div class=&quot;row-fluid custom-footer-top&quot;&gt;\r\n&lt;article class=&quot;span4&quot;&gt;\r\n&lt;h3 class=&quot;hidden-tablet&quot;&gt;Let&amp;#39;s be friends&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;clearfix&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-1&quot; href=&quot;#&quot; title=&quot;facebook&quot;&gt;facebook&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-2&quot; href=&quot;#&quot; title=&quot;twitter&quot;&gt;twitter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-3&quot; href=&quot;#&quot; title=&quot;google plus&quot;&gt;google plus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-4&quot; href=&quot;#&quot; title=&quot;rss&quot;&gt;rss&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-5&quot; href=&quot;#&quot; title=&quot;flickr&quot;&gt;flickr&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-6&quot; href=&quot;#&quot; title=&quot;vimeo&quot;&gt;vimeo&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/article&gt;\r\n\r\n&lt;article class=&quot;span4&quot;&gt;&lt;img alt=&quot;gift card&quot; src=&quot;image/data/demo/gift.png&quot; /&gt;\r\n&lt;h3&gt;Gift card treta your friends&lt;/h3&gt;\r\n\r\n&lt;p class=&quot;hidden-tablet&quot;&gt;Lorem ipsum dolor sit amet.&lt;/p&gt;\r\n&lt;/article&gt;\r\n\r\n&lt;article class=&quot;span4&quot;&gt;&lt;img alt=&quot;customer service&quot; src=&quot;image/data/demo/customer.png&quot; /&gt;\r\n&lt;h3&gt;24/7 Ctutstomer Service&lt;/h3&gt;\r\n\r\n&lt;p class=&quot;hidden-tablet&quot;&gt;Lorem ipsum amet adipiscing etiam.&lt;/p&gt;\r\n&lt;/article&gt;\r\n&lt;/div&gt;";i:2;s:1648:"&lt;div class=&quot;row-fluid custom-footer-top&quot;&gt;\r\n&lt;article class=&quot;span4&quot;&gt;\r\n&lt;h3 class=&quot;hidden-tablet&quot;&gt;دعنا نكون أصدقاء&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;clearfix&quot;&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-1&quot; href=&quot;#&quot; title=&quot;facebook&quot;&gt;facebook&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-2&quot; href=&quot;#&quot; title=&quot;twitter&quot;&gt;twitter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-3&quot; href=&quot;#&quot; title=&quot;google plus&quot;&gt;google plus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-4&quot; href=&quot;#&quot; title=&quot;rss&quot;&gt;rss&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-5&quot; href=&quot;#&quot; title=&quot;flickr&quot;&gt;flickr&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a class=&quot;social-6&quot; href=&quot;#&quot; title=&quot;vimeo&quot;&gt;vimeo&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n&lt;/article&gt;\r\n\r\n&lt;article class=&quot;span4&quot;&gt;&lt;img alt=&quot;gift card&quot; src=&quot;image/data/demo/gift.png&quot; /&gt;\r\n&lt;h3&gt;بطاقات هدايا لأصدقائك&lt;/h3&gt;\r\n\r\n&lt;p class=&quot;hidden-tablet&quot;&gt;تهروا وص غراوات اضغ خدعة حذ توبوجرافي.&lt;/p&gt;\r\n&lt;/article&gt;\r\n\r\n&lt;article class=&quot;span4&quot;&gt;&lt;img alt=&quot;customer service&quot; src=&quot;image/data/demo/customer.png&quot; /&gt;\r\n&lt;h3&gt;خدمة عملاء 24/7&lt;/h3&gt;\r\n\r\n&lt;p class=&quot;hidden-tablet&quot;&gt;تهروا وص غراوات اضغ خدعة حذ توبوجرافي.&lt;/p&gt;\r\n&lt;/article&gt;\r\n&lt;/div&gt;";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:12:"footer_block";s:10:"sort_order";s:1:"1";}i:5;a:8:{s:12:"module_title";a:2:{i:1;s:10:"Contact Us";i:2;s:10:"Contact Us";}s:11:"description";a:2:{i:1;s:826:"&lt;h3&gt;Connect with us&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;contact&quot;&gt;\r\n	&lt;li class=&quot;contact-1 clearfix first&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;We have 152 guests and 14 members online&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-2 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;Phone: +01 888 (000) &lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-3 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;Fax: +01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-4 clearfix last&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt;Email: &lt;a href=&quot;mailto:expandcart@gmail.com&quot; title=&quot;&quot;&gt;expandcart@gmail.com&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:863:"&lt;h3&gt;إتصل بنا&lt;/h3&gt;\r\n\r\n&lt;ul class=&quot;contact&quot;&gt;\r\n	&lt;li class=&quot;contact-1 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;لدينا 152 زائر و 14 عضو متصل&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-2 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;الهاتف: +01 888 (000) &lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-3 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;الفاكس: +01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-4 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt; البريد: &lt;a href=&quot;mailto:mail@example.com&quot; title=&quot;&quot;&gt;mail@example.com&lt;/a&gt; &lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"0";s:12:"module_class";s:14:"pav-contact-us";s:10:"sort_order";s:1:"1";}i:6;a:8:{s:12:"module_title";a:2:{i:1;s:13:"shopping deal";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:819:"&lt;h3&gt;Shopping deal&lt;/h3&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Phasellus purus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Laoreet sed&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Nulla quam&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Morbi odio&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Penatibus magnis&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Elementum faucibus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Amet ibendum&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Tristique turpis&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:892:"&lt;h3&gt;عروض تسويقية&lt;/h3&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أبجد هوز دولور&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;الجلوس امات&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;والبحوث مرضي&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أداء سد إنقاذ&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;سهلة والآن&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;قبل الأرض&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;مرة أخرى&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;الألم من الألم&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"0";s:12:"module_class";s:17:"pav-shopping-deal";s:10:"sort_order";s:1:"2";}i:7;a:8:{s:12:"module_title";a:2:{i:1;s:16:"Shipping payment";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:832:"&lt;h3&gt;Shipping &amp;amp; payment&lt;/h3&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Phasellus purus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Laoreet sed&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Nulla quam&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Morbi odio&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Penatibus magnis&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Elementum faucibus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Amet ibendum&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Tristique turpis&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:893:"&lt;h3&gt;الدفع و الشحن&lt;/h3&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أبجد هوز دولور&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;الجلوس امات&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;والبحوث مرضي&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أداء سد إنقاذ&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;سهلة والآن&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;قبل الأرض&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;مرة أخرى&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;الألم من الألم&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"0";s:12:"module_class";s:20:"pav-shipping-payment";s:10:"sort_order";s:1:"3";}i:8;a:8:{s:12:"module_title";a:2:{i:1;s:13:"New promotion";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:819:"&lt;h3&gt;New promotion&lt;/h3&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Phasellus purus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Laoreet sed&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Nulla quam&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Morbi odio&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Penatibus magnis&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Elementum faucibus&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Amet ibendum&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Tristique turpis&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:888:"&lt;h3&gt;عروض جديدة&lt;/h3&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أبجد هوز دولور&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;الجلوس امات&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;والبحوث مرضي&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أداء سد إنقاذ&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;سهلة والآن&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;قبل الأرض&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;مرة أخرى&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;الألم من الألم&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:13:"footer_center";s:6:"status";s:1:"0";s:12:"module_class";s:17:"pav-new-promotion";s:10:"sort_order";s:1:"4";}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:65:{s:13:"default_theme";s:10:"pav_asenti";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"0";s:30:"listing_products_columns_small";s:1:"2";s:34:"listing_products_columns_minismall";s:1:"1";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"0";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:1;s:0:"";i:2;s:0:"";}s:25:"product_customtab_content";a:2:{i:1;s:0:"";i:2;s:0:"";}s:16:"location_address";s:44:"79-99 Beaver Street, New York, NY 10005, USA";s:17:"location_latitude";s:9:"40.705423";s:18:"location_longitude";s:10:"-74.008616";s:18:"contact_customhtml";a:2:{i:1;s:0:"";i:2;s:0:"";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:23:"enable_custom_copyright";s:1:"0";s:9:"copyright";s:0:"";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:23:"enable_development_mode";s:0:"";s:19:"widget_contact_data";a:2:{i:1;s:795:"&lt;ul class=&quot;contact&quot;&gt;\r\n	&lt;li class=&quot;contact-1 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;We have 152 guests and 14 members online&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-2 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;Phone: +01 888 (000) &lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-3 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;Fax: +01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-4 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt; Email: &lt;a href=&quot;mailto:mail@example.com&quot; title=&quot;&quot;&gt;mail@example.com&lt;/a&gt; &lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:2;s:823:"&lt;ul class=&quot;contact&quot;&gt;\r\n	&lt;li class=&quot;contact-1 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;لدينا 152 زائر و 14 عضو متصل&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-2 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;الهاتف: +01 888 (000) &lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-3 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt;الفاكس: +01 888 (000) 1234&lt;/span&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;contact-4 clearfix&quot;&gt;&lt;i class=&quot;icon&quot;&gt;&amp;nbsp;&lt;/i&gt; &lt;span&gt; البريد: &lt;a href=&quot;mailto:mail@example.com&quot; title=&quot;&quot;&gt;mail@example.com&lt;/a&gt; &lt;/span&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:8:"standard";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:19:"enable_compress_css";s:0:"";s:17:"exclude_css_files";s:13:"bootstrap.css";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:9:"pattern16";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=440 ;

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
('module', 'pavcontentslider'),
('module', 'pavcustom'),
('module', 'pavproducttabs'),
--('module', 'pavmegamenu'),
('module', 'latest'),
('module', 'special'),
('module', 'bestseller'),
('module', 'pavbloglatest'),
('module', 'pavblog'),
('module', 'pavblogcategory'),
('module', 'pavblogcomment');