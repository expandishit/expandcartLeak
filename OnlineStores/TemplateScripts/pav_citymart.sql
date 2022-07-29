--
-- Table structure for table `extension`
--

CREATE TABLE IF NOT EXISTS `extension` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=439 ;

--
-- Dumping data for table `extension`
--

INSERT INTO `extension` (`type`, `code`) VALUES
('module', 'banner'),
--('module', 'carousel'),
('module', 'category'),
('module', 'affiliate'),
('module', 'account'),
('module', 'featured'),
('module', 'themecontrol'),
('module', 'pavcustom'),
('module', 'special'),
('module', 'pavproductcarousel'),
('module', 'pavcontentslider'),
('module', 'pavbloglatest'),
('module', 'pavblog'),
('module', 'pavblogcategory'),
('module', 'pavblogcomment'),
('module', 'pavtestimonial'),
('module', 'bestseller'),
('module', 'latest');

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2227 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`store_id`, `group`, `key`, `value`, `serialized`) VALUES
(0, 'pavproductcarousel', 'pavproductcarousel_module', 'a:3:{i:1;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:6:"prefix";s:3:"hot";s:4:"tabs";a:1:{i:0;s:6:"latest";}s:5:"width";s:3:"142";s:6:"height";s:3:"142";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:6:"prefix";s:3:"new";s:4:"tabs";a:1:{i:0;s:10:"mostviewed";}s:5:"width";s:3:"142";s:6:"height";s:3:"142";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:3;a:14:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:6:"prefix";s:4:"sale";s:4:"tabs";a:1:{i:0;s:7:"special";}s:5:"width";s:3:"142";s:6:"height";s:3:"142";s:12:"itemsperpage";s:1:"4";s:4:"cols";s:1:"4";s:5:"limit";s:2:"16";s:8:"interval";s:4:"8000";s:9:"auto_play";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:10:"sort_order";i:4;}}', 1),
(0, 'pavcontentslider', 'pavcontentslider_module', 'a:1:{i:0;a:12:{s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";i:1;s:9:"auto_play";s:1:"1";s:13:"text_interval";s:4:"8000";s:5:"width";s:3:"859";s:6:"height";s:3:"375";s:15:"image_navigator";s:1:"0";s:13:"navimg_weight";s:3:"177";s:13:"navimg_height";s:2:"97";s:12:"banner_image";a:3:{i:1;a:4:{s:5:"image";s:15:"data/slide1.jpg";s:4:"link";s:0:"";s:5:"title";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}}i:2;a:4:{s:5:"image";s:15:"data/slide2.jpg";s:4:"link";s:0:"";s:5:"title";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}}i:3;a:4:{s:5:"image";s:15:"data/slide3.jpg";s:4:"link";s:0:"";s:5:"title";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}}}}}', 1),
(0, 'themecontrol', 'themecontrol', 'a:65:{s:13:"default_theme";s:12:"pav_citymart";s:9:"layout_id";s:1:"1";s:8:"position";s:1:"1";s:21:"cateogry_display_mode";s:4:"grid";s:24:"listing_products_columns";s:1:"0";s:30:"listing_products_columns_small";s:1:"2";s:34:"listing_products_columns_minismall";s:1:"1";s:14:"category_pzoom";s:1:"1";s:15:"show_swap_image";s:1:"0";s:18:"product_enablezoom";s:1:"1";s:19:"product_zoomgallery";s:6:"slider";s:16:"product_zoommode";s:5:"basic";s:20:"product_zoomlenssize";s:3:"150";s:18:"product_zoomeasing";s:1:"1";s:21:"product_zoomlensshape";s:5:"basic";s:22:"product_related_column";s:1:"0";s:24:"enable_product_customtab";s:1:"0";s:22:"product_customtab_name";a:2:{i:2;s:0:"";i:1;s:0:"";}s:25:"product_customtab_content";a:2:{i:2;s:0:"";i:1;s:0:"";}s:16:"location_address";s:14:"Hanoi, Vietnam";s:17:"location_latitude";s:10:"21.0333333";s:18:"location_longitude";s:18:"105.85000000000002";s:18:"contact_customhtml";a:2:{i:2;s:902:"&lt;div class=&quot;contact-custom&quot;&gt;\r\n&lt;p style=&quot;margin-bottom: 20px&quot;&gt;&lt;strong&gt;هذا نص تجريبي يمكن تحريره من لوحة التحكم.&lt;br /&gt;\r\nيمكنك وضع أي محتوى هنا.&lt;/strong&gt;&lt;/p&gt;\r\n\r\n&lt;p style=&quot;margin-bottom: 15px&quot;&gt;جمعت بأيدي الفترة ان هذا, شيء إذ الجنوب أفريقيا. أفاق وصغار أعمال دار كل. أخر الشمل جزيرتي قد, كلا ارتكبها الإقتصادي الإقتصادية بل.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;strong&gt;&lt;span&gt;خدمة العملاء&lt;/span&gt;:&amp;nbsp;&lt;/strong&gt;&lt;br /&gt;\r\n&lt;span&gt;info@yourstore.com&lt;/span&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;strong&gt;الإرتجاع و الإستبدال:&amp;nbsp;&lt;/strong&gt;&lt;br /&gt;\r\nreturns&lt;span&gt;@yourstore.com&lt;/span&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n";i:1;s:758:"&lt;div class=&quot;contact-custom&quot;&gt;\r\n&lt;p style=&quot;margin-bottom: 20px&quot;&gt;&lt;strong&gt;This is a CMS block edited from admin panel.&lt;br /&gt;\r\nYou can insert any content here.&lt;/strong&gt;&lt;/p&gt;\r\n\r\n&lt;p style=&quot;margin-bottom: 15px&quot;&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque non dui at sapien tempor gravida ut vel arcu. Nullam ac eros eros, et ullamcorper leo.&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;strong&gt;&lt;span&gt;Customer Service&lt;/span&gt;:&amp;nbsp;&lt;/strong&gt;&lt;br /&gt;\r\n&lt;span&gt;info@yourstore.com&lt;/span&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;&lt;strong&gt;Returns and Refunds:&amp;nbsp;&lt;/strong&gt;&lt;br /&gt;\r\nreturns&lt;span&gt;@yourstore.com&lt;/span&gt;&lt;/p&gt;\r\n&lt;/div&gt;\r\n";}s:4:"skin";s:0:"";s:11:"theme_width";s:4:"auto";s:10:"responsive";s:1:"1";s:18:"enable_offsidebars";s:1:"1";s:20:"enable_footer_center";s:1:"1";s:16:"enable_paneltool";s:1:"0";s:15:"advertising_top";a:2:{i:2;s:90:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv-top.png&quot; /&gt;&lt;/p&gt;\r\n";i:1;s:88:"&lt;p&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/adv-top.png&quot; /&gt;&lt;/p&gt;";}s:20:"delivery_data_module";a:2:{i:2;s:1010:"&lt;div class=&quot;box-services&quot;&gt;&lt;span class=&quot;iconbox pull-left&quot;&gt;&lt;i class=&quot;fa fa-truck&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4&gt;شحن مجاني&lt;/h4&gt;\r\n&lt;span&gt;للطلبات أكثر من 150$&lt;/span&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;box-services&quot;&gt;&lt;span class=&quot;iconbox pull-left&quot;&gt;&lt;i class=&quot;fa fa-refresh&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4&gt;الإستبدال و الإرتجاع&lt;/h4&gt;\r\n&lt;span&gt;خلال 3 أيام عمل&lt;/span&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;box-services&quot;&gt;&lt;span class=&quot;iconbox pull-left&quot;&gt;&lt;i class=&quot;fa fa-phone&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4&gt;04 123 456 789&lt;/h4&gt;\r\n&lt;span&gt;إتصل بنا دائما&lt;/span&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n";i:1;s:972:"&lt;div class=&quot;box-services&quot;&gt;&lt;span class=&quot;iconbox pull-left&quot;&gt;&lt;i class=&quot;fa fa-truck&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4&gt;Free shipping&lt;/h4&gt;\r\n&lt;span&gt;all order over $150 &lt;/span&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;box-services&quot;&gt;&lt;span class=&quot;iconbox pull-left&quot;&gt;&lt;i class=&quot;fa fa-refresh&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4&gt;Return &amp;amp; Exchange&lt;/h4&gt;\r\n&lt;span&gt;in 3 working days &lt;/span&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;div class=&quot;box-services&quot;&gt;&lt;span class=&quot;iconbox pull-left&quot;&gt;&lt;i class=&quot;fa fa-phone&quot;&gt;&amp;nbsp;&lt;/i&gt;&lt;/span&gt;\r\n\r\n&lt;div class=&quot;media-body&quot;&gt;\r\n&lt;h4&gt;04 123 456 789&lt;/h4&gt;\r\n&lt;span&gt;Sed ullamcorper mattis sit&lt;/span&gt;&lt;/div&gt;\r\n&lt;/div&gt;\r\n";}s:24:"username_facebook_module";s:34:"http://www.facebook.com/expandcart";s:16:"footer_columns_2";a:2:{i:2;s:619:"&lt;h3&gt;النشرة البريدية&lt;/h3&gt;\r\n\r\n&lt;p&gt;إرسال النشرة البريدية لبريدك الإلكتروني.&lt;/p&gt;\r\n\r\n&lt;div class=&quot;email&quot;&gt;&lt;input name=&quot;email&quot; placeholder=&quot;البريد الإلكتروني&quot; type=&quot;text&quot; value=&quot;&quot; /&gt;\r\n&lt;div class=&quot;button-email&quot;&gt;إرسال&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;h3&gt;أطلب الأن&lt;/h3&gt;\r\n\r\n&lt;p&gt;الهاتف: +123 456 789&lt;/p&gt;\r\n\r\n&lt;p&gt;الفاكس: +123 456 789&lt;/p&gt;\r\n\r\n&lt;p&gt;البريد الإلكتروني: admin@admin.com&lt;/p&gt;\r\n";i:1;s:469:"&lt;h3&gt;Newsletter&lt;/h3&gt;\r\n\r\n&lt;p&gt;Send your email a newsletter.&lt;/p&gt;\r\n\r\n&lt;div class=&quot;email&quot;&gt;&lt;input name=&quot;email&quot; placeholder=&quot;email&quot; type=&quot;text&quot; value=&quot;&quot; /&gt;\r\n&lt;div class=&quot;button-email&quot;&gt;Go&lt;/div&gt;\r\n&lt;/div&gt;\r\n\r\n&lt;h3&gt;Order online&lt;/h3&gt;\r\n\r\n&lt;p&gt;Phone: +123 456 789&lt;/p&gt;\r\n\r\n&lt;p&gt;Fax: +123 456 789&lt;/p&gt;\r\n\r\n&lt;p&gt;Email: admin@admin.com&lt;/p&gt;\r\n";}s:16:"footer_columns_3";a:2:{i:2;s:904:"&lt;h3&gt;إبقى على إتصال&lt;/h3&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;facebook&quot;&gt;&lt;span class=&quot;fa fa-facebook&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;فيسبوك&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;twitter&quot;&gt;&lt;span class=&quot;fa fa-twitter&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;تويتر&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;google&quot;&gt;&lt;span class=&quot;fa fa-google-plus&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;جوجل&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;youtube&quot;&gt;&lt;span class=&quot;fa fa-youtube&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;يوتيوب&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;rss&quot;&gt;&lt;span class=&quot;fa fa-rss&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;أخبار RSS&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";i:1;s:907:"&lt;h3&gt;Stay Connected&lt;/h3&gt;\r\n\r\n&lt;ul&gt;\r\n	&lt;li class=&quot;facebook&quot;&gt;&lt;span class=&quot;fa fa-facebook&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;Triads Facebook&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;twitter&quot;&gt;&lt;span class=&quot;fa fa-twitter&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;Triads Twitter&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;google&quot;&gt;&lt;span class=&quot;fa fa-google-plus&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;Triads Google&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;youtube&quot;&gt;&lt;span class=&quot;fa fa-youtube&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;Triads Youtube&lt;/a&gt;&lt;/li&gt;\r\n	&lt;li class=&quot;rss&quot;&gt;&lt;span class=&quot;fa fa-rss&quot;&gt;&amp;nbsp;&lt;/span&gt;&lt;a href=&quot;#&quot;&gt;Triads RSS Feed&lt;/a&gt;&lt;/li&gt;\r\n&lt;/ul&gt;\r\n";}s:18:"widget_paypal_data";a:2:{i:2;s:132:"&lt;p&gt;&lt;img alt=&quot;paymethods&quot; src=&quot;catalog/view/theme/pav_citymart/image/icon/payment.png&quot; /&gt;&lt;/p&gt;\r\n";i:1;s:132:"&lt;p&gt;&lt;img alt=&quot;paymethods&quot; src=&quot;catalog/view/theme/pav_citymart/image/icon/payment.png&quot; /&gt;&lt;/p&gt;\r\n";}s:8:"fontsize";s:2:"12";s:17:"enable_customfont";s:1:"0";s:11:"type_fonts1";s:8:"standard";s:13:"normal_fonts1";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url1";s:0:"";s:14:"google_family1";s:0:"";s:14:"body_selector1";s:0:"";s:11:"type_fonts2";s:8:"standard";s:13:"normal_fonts2";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url2";s:0:"";s:14:"google_family2";s:0:"";s:14:"body_selector2";s:0:"";s:11:"type_fonts3";s:8:"standard";s:13:"normal_fonts3";s:27:"Verdana, Geneva, sans-serif";s:11:"google_url3";s:0:"";s:14:"google_family3";s:0:"";s:14:"body_selector3";s:0:"";s:14:"block_showcase";s:0:"";s:15:"block_promotion";s:0:"";s:16:"block_footer_top";s:0:"";s:19:"block_footer_center";s:0:"";s:19:"block_footer_bottom";s:0:"";s:15:"customize_theme";s:0:"";s:12:"body_pattern";s:11:"none active";s:12:"use_custombg";s:1:"0";s:8:"bg_image";s:0:"";s:9:"bg_repeat";s:6:"repeat";s:11:"bg_position";s:8:"left top";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}', 1),
(0, 'affiliate', 'affiliate_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:2:"10";s:8:"position";s:12:"column_right";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'account', 'account_module', 'a:1:{i:0;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'banner', 'banner_module', 'a:1:{i:0;a:8:{s:9:"banner_id";s:1:"6";s:5:"width";s:3:"182";s:6:"height";s:3:"182";s:11:"resize_type";s:7:"default";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'config', 'config_image_cart_height', '47', 0),
(0, 'config', 'config_image_cart_width', '47', 0),
(0, 'config', 'config_image_wishlist_height', '47', 0),
(0, 'config', 'config_image_wishlist_width', '47', 0),
(0, 'config', 'config_image_compare_height', '90', 0),
(0, 'config', 'config_image_compare_width', '90', 0),
(0, 'config', 'config_image_related_height', '142', 0),
(0, 'config', 'config_image_additional_height', '89', 0),
(0, 'config', 'config_image_related_width', '142', 0),
(0, 'config', 'config_image_additional_width', '89', 0),
(0, 'config', 'config_image_product_height', '142', 0),
(0, 'config', 'config_image_product_width', '142', 0),
(0, 'config', 'config_image_popup_height', '500', 0),
(0, 'config', 'config_image_popup_width', '500', 0),
(0, 'config', 'config_image_thumb_height', '251', 0),
(0, 'config', 'config_image_thumb_width', '269', 0),
(0, 'config', 'config_image_category_height', '275', 0),
(0, 'config', 'config_image_category_width', '861', 0),
(0, 'featured', 'product', '', 0),
(0, 'pavbloglatest', 'pavbloglatest_module', 'a:1:{i:1;a:10:{s:11:"description";a:3:{i:1;s:0:"";i:2;s:0:"";i:3;s:0:"";}s:4:"tabs";s:6:"latest";s:5:"width";s:3:"351";s:6:"height";s:3:"140";s:4:"cols";s:1:"3";s:5:"limit";s:1:"3";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}}', 1),
(0, 'category', 'category_module', 'a:7:{i:0;a:4:{s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:1;a:4:{s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:2;a:4:{s:9:"layout_id";s:1:"1";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";i:1;}i:3;a:4:{s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:4;a:4:{s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:5;a:4:{s:9:"layout_id";s:1:"5";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}i:6;a:4:{s:9:"layout_id";s:1:"6";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";}}', 1),
(0, 'pavblog', 'pavblog', 'a:42:{s:14:"general_lwidth";s:3:"826";s:15:"general_lheight";s:3:"330";s:14:"general_swidth";s:3:"250";s:15:"general_sheight";s:3:"250";s:14:"general_xwidth";s:2:"80";s:15:"general_xheight";s:2:"80";s:14:"rss_limit_item";s:2:"12";s:26:"keyword_listing_blogs_page";s:5:"blogs";s:16:"children_columns";s:1:"3";s:14:"general_cwidth";s:3:"250";s:15:"general_cheight";s:3:"250";s:22:"cat_limit_leading_blog";s:1:"1";s:24:"cat_limit_secondary_blog";s:1:"6";s:22:"cat_leading_image_type";s:1:"l";s:24:"cat_secondary_image_type";s:1:"l";s:24:"cat_columns_leading_blog";s:1:"1";s:27:"cat_columns_secondary_blogs";s:1:"1";s:14:"cat_show_title";s:1:"1";s:20:"cat_show_description";s:1:"1";s:17:"cat_show_readmore";s:1:"1";s:14:"cat_show_image";s:1:"1";s:15:"cat_show_author";s:1:"1";s:17:"cat_show_category";s:1:"1";s:16:"cat_show_created";s:1:"1";s:13:"cat_show_hits";s:1:"1";s:24:"cat_show_comment_counter";s:1:"1";s:15:"blog_image_type";s:1:"l";s:15:"blog_show_title";s:1:"1";s:15:"blog_show_image";s:1:"1";s:16:"blog_show_author";s:1:"1";s:18:"blog_show_category";s:1:"1";s:17:"blog_show_created";s:1:"1";s:25:"blog_show_comment_counter";s:1:"1";s:14:"blog_show_hits";s:1:"1";s:22:"blog_show_comment_form";s:1:"1";s:14:"comment_engine";s:5:"local";s:14:"diquis_account";s:10:"expandcart";s:14:"facebook_appid";s:2:"10";s:13:"comment_limit";s:2:"10";s:14:"facebook_width";s:3:"600";s:20:"auto_publish_comment";s:1:"0";s:16:"enable_recaptcha";s:1:"1";}', 1),
(0, 'special', 'special_module', 'a:5:{i:0;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";i:2;}i:1;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:1:"3";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:2;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:1:"2";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:3;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:1:"4";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}i:4;a:7:{s:5:"limit";s:1:"5";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"3";}}', 1),
(0, 'pavblog_frontmodules', 'pavblog_frontmodules', 'a:1:{s:7:"modules";a:3:{i:0;s:15:"pavblogcategory";i:1;s:13:"pavbloglatest";i:2;s:14:"pavblogcomment";}}', 1),
(0, 'pavblogcategory', 'pavblogcategory_module', 'a:2:{i:1;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:5:"99999";s:8:"position";s:8:"mainmenu";s:6:"status";s:1:"0";s:10:"sort_order";i:1;}i:2;a:5:{s:11:"category_id";s:1:"1";s:9:"layout_id";s:2:"12";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"2";}}', 1),
(0, 'latest', 'latest_module', 'a:1:{i:0;a:7:{s:5:"limit";s:1:"4";s:11:"image_width";s:2:"80";s:12:"image_height";s:2:"80";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"column_left";s:6:"status";s:1:"0";s:10:"sort_order";i:3;}}', 1),
(0, 'featured', 'featured_product', '43,40,42,49,46,47,28', 0),
(0, 'pavcustom', 'pavcustom_module', 'a:7:{i:1;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:331:"&lt;div class=&quot;advertising row-fluid&quot;&gt;&lt;a class=&quot;span6&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/static2.jpg&quot; /&gt; &lt;/a&gt; &lt;a class=&quot;span6&quot; href=&quot;#&quot;&gt; &lt;img alt=&quot;&quot; src=&quot;image/data/static3.jpg&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n";i:2;s:331:"&lt;div class=&quot;advertising row-fluid&quot;&gt;&lt;a class=&quot;span6&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/static2.jpg&quot; /&gt; &lt;/a&gt; &lt;a class=&quot;span6&quot; href=&quot;#&quot;&gt; &lt;img alt=&quot;&quot; src=&quot;image/data/static3.jpg&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}i:2;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:199:"&lt;div class=&quot;advertising row-fluid&quot;&gt;&lt;a class=&quot;span12&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-adv3.jpg&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n";i:2;s:199:"&lt;div class=&quot;advertising row-fluid&quot;&gt;&lt;a class=&quot;span12&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-adv3.jpg&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"4";}i:3;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:333:"&lt;div class=&quot;advertising row-fluid&quot;&gt;&lt;a class=&quot;span5&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-adv4.jpg&quot; /&gt; &lt;/a&gt; &lt;a class=&quot;span7&quot; href=&quot;#&quot;&gt; &lt;img alt=&quot;&quot; src=&quot;image/data/img-adv5.jpg&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n";i:2;s:333:"&lt;div class=&quot;advertising row-fluid&quot;&gt;&lt;a class=&quot;span5&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-adv4.jpg&quot; /&gt; &lt;/a&gt; &lt;a class=&quot;span7&quot; href=&quot;#&quot;&gt; &lt;img alt=&quot;&quot; src=&quot;image/data/img-adv5.jpg&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:14:"content_bottom";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"2";}i:4;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:468:"&lt;div class=&quot;advertising row-fluid&quot;&gt;&lt;a class=&quot;span4&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-adv8.jpg&quot; /&gt; &lt;/a&gt; &lt;a class=&quot;span4&quot; href=&quot;#&quot;&gt; &lt;img alt=&quot;&quot; src=&quot;image/data/img-adv7.jpg&quot; /&gt; &lt;/a&gt; &lt;a class=&quot;span4&quot; href=&quot;#&quot;&gt; &lt;img alt=&quot;&quot; src=&quot;image/data/img-adv6.jpg&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n";i:2;s:468:"&lt;div class=&quot;advertising row-fluid&quot;&gt;&lt;a class=&quot;span4&quot; href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/img-adv8.jpg&quot; /&gt; &lt;/a&gt; &lt;a class=&quot;span4&quot; href=&quot;#&quot;&gt; &lt;img alt=&quot;&quot; src=&quot;image/data/img-adv7.jpg&quot; /&gt; &lt;/a&gt; &lt;a class=&quot;span4&quot; href=&quot;#&quot;&gt; &lt;img alt=&quot;&quot; src=&quot;image/data/img-adv6.jpg&quot; /&gt; &lt;/a&gt;&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:5:"99999";s:8:"position";s:10:"footer_top";s:6:"status";s:1:"1";s:12:"module_class";s:0:"";s:10:"sort_order";s:1:"1";}i:5;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:159:"&lt;div class=&quot;advleft&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/advleft1.jpg&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n";i:2;s:159:"&lt;div class=&quot;advleft&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/advleft1.jpg&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:12:"module_class";s:9:"no-border";s:10:"sort_order";s:1:"5";}i:6;a:8:{s:12:"module_title";a:2:{i:1;s:17:"Sample block text";i:2;s:19:"مثال لجملة";}s:11:"description";a:2:{i:1;s:166:"&lt;p&gt;Nunc gavida nis utrices borti molis temp tepor quam congue turpis sed psum blan dit donec vitae vestibum&amp;nbsp; congue mauris piscing aliquam.&lt;/p&gt;\r\n";i:2;s:387:"&lt;p&gt;جمعت بأيدي الفترة ان هذا, شيء إذ الجنوب أفريقيا. أفاق وصغار أعمال دار كل. أخر الشمل جزيرتي قد, كلا ارتكبها الإقتصادي الإقتصادية بل. حاملات بالرغم الإنزال دون عل, دفّة الجو الضغوط انه عل, لم أضف أراض تنفّس.&lt;/p&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:12:"module_class";s:6:"orange";s:10:"sort_order";s:1:"6";}i:7;a:8:{s:12:"module_title";a:2:{i:1;s:0:"";i:2;s:0:"";}s:11:"description";a:2:{i:1;s:159:"&lt;div class=&quot;advleft&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/advleft2.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n";i:2;s:159:"&lt;div class=&quot;advleft&quot;&gt;&lt;a href=&quot;#&quot;&gt;&lt;img alt=&quot;&quot; src=&quot;image/data/advleft2.png&quot; /&gt;&lt;/a&gt;&lt;/div&gt;\r\n";}s:10:"show_title";s:1:"0";s:9:"layout_id";s:1:"1";s:8:"position";s:11:"column_left";s:6:"status";s:1:"1";s:12:"module_class";s:9:"no-border";s:10:"sort_order";s:1:"8";}}', 1),
(0, 'pavtestimonial', 'pavtestimonial_module', 'a:1:{i:0;a:11:{s:9:"layout_id";s:1:"1";s:8:"position";s:11:"content_top";s:6:"status";s:1:"1";s:10:"sort_order";s:1:"1";s:9:"auto_play";s:1:"1";s:13:"text_interval";s:4:"8000";s:5:"width";s:2:"60";s:6:"height";s:2:"60";s:11:"column_item";s:1:"1";s:10:"page_items";s:1:"2";s:16:"testimonial_item";a:5:{i:1;a:4:{s:5:"image";s:29:"data/demo/author_original.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:340:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}i:2;a:4:{s:5:"image";s:29:"data/demo/author_original.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:340:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}i:3;a:4:{s:5:"image";s:29:"data/demo/author_original.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:340:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}i:4;a:4:{s:5:"image";s:29:"data/demo/author_original.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:340:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}i:5;a:4:{s:5:"image";s:29:"data/demo/author_original.jpg";s:10:"video_link";s:36:"http://www.youtube.com/v/UZJs6AVj8vY";s:7:"profile";a:2:{i:1;s:319:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;Brad Pitt&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;Designer,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";i:2;s:340:"&lt;p class=&quot;author-name&quot;&gt;&lt;a href=&quot;#&quot; title=&quot;&quot;&gt;أحمد منصور&lt;/a&gt;&lt;/p&gt;\r\n\r\n&lt;p&gt;مصمم مواقع,&lt;br /&gt;\r\n&lt;a class=&quot;author-link&quot; href=&quot;http://www.expandcart.com/&quot; target=&quot;_blank&quot; title=&quot;&quot;&gt;www.expandcart.com&lt;/a&gt;&lt;/p&gt;\r\n";}s:11:"description";a:2:{i:1;s:215:"&lt;p&gt;Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repell bloud seimen oyta.&lt;/p&gt;\r\n";i:2;s:417:"&lt;p&gt;إذ فصل العدّ بأضرار, ثم تلك جديدة لهيمنة. عل لمّ وبالرغم والعتاد باستخدام. العالم، الأوروبية تشيكوسلوفاكيا بحق لم, وقد ٣٠ وانهاء شواطيء المؤلّفة, وبعض وبالتحديد، عن أضف. كما ان والنفيس استراليا،, إذ والحزب وانتهاءً كلا.&lt;/p&gt;\r\n";}}}}}', 1);
