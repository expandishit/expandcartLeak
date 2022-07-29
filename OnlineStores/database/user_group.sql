/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
`user_group_id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(64) NOT NULL,
`permission` text NOT NULL,
`created_at` datetime DEFAULT NULL,
`description` text DEFAULT NULL,
PRIMARY KEY (`user_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `user_group` (`user_group_id`, `name`, `permission`, `created_at`, `description`) VALUES
(1, 'Top Administrator', 'a:2:{s:6:\"access\";a:248:{i:0;s:19:\"analytics/analytics\";i:1;s:14:\"analytics/live\";i:2;s:11:\"api/clients\";i:3;s:20:\"billingaccount/plans\";i:4;s:13:\"blog/category\";i:5;s:12:\"blog/comment\";i:6;s:9:\"blog/post\";i:7;s:12:\"blog/setting\";i:8;s:17:\"catalog/attribute\";i:9;s:23:\"catalog/attribute_group\";i:10;s:16:\"catalog/category\";i:11;s:16:\"catalog/download\";i:12;s:19:\"catalog/information\";i:13;s:20:\"catalog/manufacturer\";i:14;s:14:\"catalog/option\";i:15;s:15:\"catalog/product\";i:16;s:14:\"catalog/review\";i:17;s:11:\"common/base\";i:18;s:16:\"common/dashboard\";i:19;s:18:\"common/filemanager\";i:20;s:11:\"common/home\";i:21;s:15:\"ecdeals/product\";i:22;s:14:\"extension/feed\";i:23;s:17:\"extension/payment\";i:24;s:18:\"extension/shipping\";i:25;s:15:\"extension/total\";i:26;s:25:\"feed/articles_google_base\";i:27;s:28:\"feed/articles_google_sitemap\";i:28;s:16:\"feed/google_base\";i:29;s:19:\"feed/google_sitemap\";i:30;s:11:\"guide/guide\";i:31;s:25:\"localisation/country_city\";i:32;s:21:\"localisation/currency\";i:33;s:21:\"localisation/geo_zone\";i:34;s:21:\"localisation/language\";i:35;s:25:\"localisation/length_class\";i:36;s:25:\"localisation/order_status\";i:37;s:26:\"localisation/return_action\";i:38;s:26:\"localisation/return_reason\";i:39;s:26:\"localisation/return_status\";i:40;s:21:\"localisation/statuses\";i:41;s:25:\"localisation/stock_status\";i:42;s:22:\"localisation/tax_class\";i:43;s:21:\"localisation/tax_rate\";i:44;s:25:\"localisation/weight_class\";i:45;s:17:\"localisation/zone\";i:46;s:21:\"marketing/integration\";i:47;s:23:\"marketing/mass_mail_sms\";i:48;s:15:\"marketplace/app\";i:49;s:16:\"marketplace/home\";i:50;s:15:\"meditor/meditor\";i:51;s:21:\"module/abandoned_cart\";i:52;s:21:\"module/advanced_deals\";i:53;s:21:\"module/auto_meta_tags\";i:54;s:27:\"module/autocomplete_address\";i:55;s:29:\"module/custom_email_templates\";i:56;s:37:\"module/custom_fees_for_payment_method\";i:57;s:21:\"module/d_social_login\";i:58;s:24:\"module/dedicated_domains\";i:59;s:17:\"module/expand_seo\";i:60;s:17:\"module/fetchr_api\";i:61;s:19:\"module/get_response\";i:62;s:29:\"module/klarna_checkout_module\";i:63;s:26:\"module/knawat_dropshipping\";i:64;s:19:\"module/localization\";i:65;s:16:\"module/mailchimp\";i:66;s:20:\"module/masspdiscoupd\";i:67;s:18:\"module/mega_filter\";i:68;s:17:\"module/mobile_app\";i:69;s:18:\"module/multiseller\";i:70;s:24:\"module/network_marketing\";i:71;s:18:\"module/popupwindow\";i:72;s:22:\"module/price_per_meter\";i:73;s:15:\"module/printful\";i:74;s:24:\"module/printing_document\";i:75;s:29:\"module/product_affiliate_link\";i:76;s:23:\"module/product_designer\";i:77;s:31:\"module/product_option_image_pro\";i:78;s:26:\"module/productsoptions_sku\";i:79;s:24:\"module/qoyod_integration\";i:80;s:20:\"module/quickcheckout\";i:81;s:23:\"module/related_products\";i:82;s:22:\"module/rental_products\";i:83;s:24:\"module/reward_points_pro\";i:84;s:20:\"module/sales_booster\";i:85;s:13:\"module/signup\";i:86;s:14:\"module/smshare\";i:87;s:35:\"module/social_media_contact_buttons\";i:88;s:19:\"module/socialslides\";i:89;s:14:\"module/special\";i:90;s:22:\"module/store_locations\";i:91;s:22:\"module/zopim_live_chat\";i:92;s:21:\"multiseller/attribute\";i:93;s:16:\"multiseller/base\";i:94;s:19:\"multiseller/payment\";i:95;s:19:\"multiseller/product\";i:96;s:24:\"multiseller/seller-group\";i:97;s:18:\"multiseller/seller\";i:98;s:31:\"multiseller/seller_transactions\";i:99;s:25:\"multiseller/subscriptions\";i:100;s:23:\"multiseller/transaction\";i:101;s:26:\"network_marketing/agencies\";i:102;s:27:\"network_marketing/downlines\";i:103;s:24:\"network_marketing/levels\";i:104;s:26:\"network_marketing/settings\";i:105;s:21:\"payment/bank_transfer\";i:106;s:14:\"payment/bitpay\";i:107;s:13:\"payment/cashu\";i:108;s:19:\"payment/ccavenuepay\";i:109;s:14:\"payment/cheque\";i:110;s:11:\"payment/cod\";i:111;s:15:\"payment/dixipay\";i:112;s:19:\"payment/fastpaycash\";i:113;s:15:\"payment/faturah\";i:114;s:13:\"payment/fawry\";i:115;s:21:\"payment/free_checkout\";i:116;s:17:\"payment/gate2play\";i:117;s:15:\"payment/halalah\";i:118;s:24:\"payment/innovatepayments\";i:119;s:28:\"payment/iyzico_checkout_form\";i:120;s:23:\"payment/klarna_checkout\";i:121;s:12:\"payment/knet\";i:122;s:20:\"payment/migscheckout\";i:123;s:15:\"payment/mobipay\";i:124;s:15:\"payment/moyasar\";i:125;s:19:\"payment/my_fatoorah\";i:126;s:16:\"payment/nbe_bank\";i:127;s:13:\"payment/okpay\";i:128;s:15:\"payment/onecard\";i:129;s:20:\"payment/payfort_fort\";i:130;s:25:\"payment/payfort_fort_qpay\";i:131;s:26:\"payment/payfort_fort_sadad\";i:132;s:16:\"payment/payoneer\";i:133;s:15:\"payment/paysera\";i:134;s:15:\"payment/paytabs\";i:135;s:13:\"payment/payza\";i:136;s:18:\"payment/pp_express\";i:137;s:15:\"payment/pp_plus\";i:138;s:19:\"payment/pp_standard\";i:139;s:14:\"payment/skrill\";i:140;s:14:\"payment/sofort\";i:141;s:14:\"payment/stripe\";i:142;s:11:\"payment/tap\";i:143;s:19:\"payment/tnspayments\";i:144;s:19:\"payment/twocheckout\";i:145;s:12:\"payment/upay\";i:146;s:16:\"payment/zaincash\";i:147;s:24:\"product_designer/clipart\";i:148;s:24:\"promotions/reward_points\";i:149;s:21:\"report/abandoned_cart\";i:150;s:27:\"report/affiliate_commission\";i:151;s:22:\"report/customer_credit\";i:152;s:22:\"report/customer_online\";i:153;s:21:\"report/customer_order\";i:154;s:22:\"report/customer_reward\";i:155;s:24:\"report/product_purchased\";i:156;s:32:\"report/product_top_ten_purchased\";i:157;s:21:\"report/product_viewed\";i:158;s:14:\"report/reports\";i:159;s:18:\"report/sale_coupon\";i:160;s:17:\"report/sale_order\";i:161;s:18:\"report/sale_return\";i:162;s:20:\"report/sale_shipping\";i:163;s:15:\"report/sale_tax\";i:164;s:14:\"sale/affiliate\";i:165;s:32:\"sale/aramex_bulk_schedule_pickup\";i:166;s:27:\"sale/aramex_create_shipment\";i:167;s:27:\"sale/aramex_rate_calculator\";i:168;s:27:\"sale/aramex_schedule_pickup\";i:169;s:19:\"sale/aramex_traking\";i:170;s:15:\"sale/collection\";i:171;s:12:\"sale/contact\";i:172;s:11:\"sale/coupon\";i:173;s:13:\"sale/customer\";i:174;s:20:\"sale/customer_ban_ip\";i:175;s:19:\"sale/customer_group\";i:176;s:13:\"sale/download\";i:177;s:18:\"sale/externalorder\";i:178;s:10:\"sale/order\";i:179;s:11:\"sale/return\";i:180;s:16:\"sale/smshare_sms\";i:181;s:12:\"sale/voucher\";i:182;s:18:\"sale/voucher_theme\";i:183;s:26:\"sale/zajil_create_shipment\";i:184;s:19:\"security/throttling\";i:185;s:23:\"setting/advancedsetting\";i:186;s:28:\"setting/audit_trail_settings\";i:187;s:15:\"setting/domains\";i:188;s:14:\"setting/dropna\";i:189;s:19:\"setting/integration\";i:190;s:16:\"setting/language\";i:191;s:15:\"setting/setting\";i:192;s:20:\"setting/setting_mail\";i:193;s:21:\"setting/store_account\";i:194;s:24:\"setting/store_affiliates\";i:195;s:22:\"setting/store_checkout\";i:196;s:21:\"setting/store_general\";i:197;s:19:\"setting/store_items\";i:198;s:22:\"setting/store_products\";i:199;s:21:\"setting/store_returns\";i:200;s:21:\"setting/store_scripts\";i:201;s:19:\"setting/store_stock\";i:202;s:19:\"setting/store_taxes\";i:203;s:19:\"setting/store_units\";i:204;s:22:\"setting/store_vouchers\";i:205;s:16:\"setting/template\";i:206;s:15:\"shipping/aramex\";i:207;s:31:\"shipping/category_product_based\";i:208;s:12:\"shipping/dhl\";i:209;s:20:\"shipping/dhl_express\";i:210;s:14:\"shipping/fedex\";i:211;s:23:\"shipping/fedex_domestic\";i:212;s:13:\"shipping/flat\";i:213;s:13:\"shipping/free\";i:214;s:13:\"shipping/item\";i:215;s:15:\"shipping/pickup\";i:216;s:13:\"shipping/saee\";i:217;s:15:\"shipping/salasa\";i:218;s:13:\"shipping/smsa\";i:219;s:12:\"shipping/ups\";i:220;s:15:\"shipping/weight\";i:221;s:14:\"shipping/zajil\";i:222;s:15:\"teditor/teditor\";i:223;s:19:\"templates/customize\";i:224;s:16:\"templates/import\";i:225;s:18:\"templates/template\";i:226;s:11:\"tool/backup\";i:227;s:14:\"tool/error_log\";i:228;s:19:\"tool/product_export\";i:229;s:19:\"tool/product_import\";i:230;s:18:\"tool/w_export_tool\";i:231;s:18:\"tool/w_import_tool\";i:232;s:12:\"total/coupon\";i:233;s:12:\"total/credit\";i:234;s:16:\"total/earn_point\";i:235;s:14:\"total/handling\";i:236;s:19:\"total/low_order_fee\";i:237;s:18:\"total/redeem_point\";i:238;s:12:\"total/reward\";i:239;s:14:\"total/shipping\";i:240;s:15:\"total/sub_total\";i:241;s:9:\"total/tax\";i:242;s:11:\"total/total\";i:243;s:13:\"total/voucher\";i:244;s:8:\"user/api\";i:245;s:18:\"user/notifications\";i:246;s:9:\"user/user\";i:247;s:20:\"user/user_permission\";}s:6:\"modify\";a:248:{i:0;s:19:\"analytics/analytics\";i:1;s:14:\"analytics/live\";i:2;s:11:\"api/clients\";i:3;s:20:\"billingaccount/plans\";i:4;s:13:\"blog/category\";i:5;s:12:\"blog/comment\";i:6;s:9:\"blog/post\";i:7;s:12:\"blog/setting\";i:8;s:17:\"catalog/attribute\";i:9;s:23:\"catalog/attribute_group\";i:10;s:16:\"catalog/category\";i:11;s:16:\"catalog/download\";i:12;s:19:\"catalog/information\";i:13;s:20:\"catalog/manufacturer\";i:14;s:14:\"catalog/option\";i:15;s:15:\"catalog/product\";i:16;s:14:\"catalog/review\";i:17;s:11:\"common/base\";i:18;s:16:\"common/dashboard\";i:19;s:18:\"common/filemanager\";i:20;s:11:\"common/home\";i:21;s:15:\"ecdeals/product\";i:22;s:14:\"extension/feed\";i:23;s:17:\"extension/payment\";i:24;s:18:\"extension/shipping\";i:25;s:15:\"extension/total\";i:26;s:25:\"feed/articles_google_base\";i:27;s:28:\"feed/articles_google_sitemap\";i:28;s:16:\"feed/google_base\";i:29;s:19:\"feed/google_sitemap\";i:30;s:11:\"guide/guide\";i:31;s:25:\"localisation/country_city\";i:32;s:21:\"localisation/currency\";i:33;s:21:\"localisation/geo_zone\";i:34;s:21:\"localisation/language\";i:35;s:25:\"localisation/length_class\";i:36;s:25:\"localisation/order_status\";i:37;s:26:\"localisation/return_action\";i:38;s:26:\"localisation/return_reason\";i:39;s:26:\"localisation/return_status\";i:40;s:21:\"localisation/statuses\";i:41;s:25:\"localisation/stock_status\";i:42;s:22:\"localisation/tax_class\";i:43;s:21:\"localisation/tax_rate\";i:44;s:25:\"localisation/weight_class\";i:45;s:17:\"localisation/zone\";i:46;s:21:\"marketing/integration\";i:47;s:23:\"marketing/mass_mail_sms\";i:48;s:15:\"marketplace/app\";i:49;s:16:\"marketplace/home\";i:50;s:15:\"meditor/meditor\";i:51;s:21:\"module/abandoned_cart\";i:52;s:21:\"module/advanced_deals\";i:53;s:21:\"module/auto_meta_tags\";i:54;s:27:\"module/autocomplete_address\";i:55;s:29:\"module/custom_email_templates\";i:56;s:37:\"module/custom_fees_for_payment_method\";i:57;s:21:\"module/d_social_login\";i:58;s:24:\"module/dedicated_domains\";i:59;s:17:\"module/expand_seo\";i:60;s:17:\"module/fetchr_api\";i:61;s:19:\"module/get_response\";i:62;s:29:\"module/klarna_checkout_module\";i:63;s:26:\"module/knawat_dropshipping\";i:64;s:19:\"module/localization\";i:65;s:16:\"module/mailchimp\";i:66;s:20:\"module/masspdiscoupd\";i:67;s:18:\"module/mega_filter\";i:68;s:17:\"module/mobile_app\";i:69;s:18:\"module/multiseller\";i:70;s:24:\"module/network_marketing\";i:71;s:18:\"module/popupwindow\";i:72;s:22:\"module/price_per_meter\";i:73;s:15:\"module/printful\";i:74;s:24:\"module/printing_document\";i:75;s:29:\"module/product_affiliate_link\";i:76;s:23:\"module/product_designer\";i:77;s:31:\"module/product_option_image_pro\";i:78;s:26:\"module/productsoptions_sku\";i:79;s:24:\"module/qoyod_integration\";i:80;s:20:\"module/quickcheckout\";i:81;s:23:\"module/related_products\";i:82;s:22:\"module/rental_products\";i:83;s:24:\"module/reward_points_pro\";i:84;s:20:\"module/sales_booster\";i:85;s:13:\"module/signup\";i:86;s:14:\"module/smshare\";i:87;s:35:\"module/social_media_contact_buttons\";i:88;s:19:\"module/socialslides\";i:89;s:14:\"module/special\";i:90;s:22:\"module/store_locations\";i:91;s:22:\"module/zopim_live_chat\";i:92;s:21:\"multiseller/attribute\";i:93;s:16:\"multiseller/base\";i:94;s:19:\"multiseller/payment\";i:95;s:19:\"multiseller/product\";i:96;s:24:\"multiseller/seller-group\";i:97;s:18:\"multiseller/seller\";i:98;s:31:\"multiseller/seller_transactions\";i:99;s:25:\"multiseller/subscriptions\";i:100;s:23:\"multiseller/transaction\";i:101;s:26:\"network_marketing/agencies\";i:102;s:27:\"network_marketing/downlines\";i:103;s:24:\"network_marketing/levels\";i:104;s:26:\"network_marketing/settings\";i:105;s:21:\"payment/bank_transfer\";i:106;s:14:\"payment/bitpay\";i:107;s:13:\"payment/cashu\";i:108;s:19:\"payment/ccavenuepay\";i:109;s:14:\"payment/cheque\";i:110;s:11:\"payment/cod\";i:111;s:15:\"payment/dixipay\";i:112;s:19:\"payment/fastpaycash\";i:113;s:15:\"payment/faturah\";i:114;s:13:\"payment/fawry\";i:115;s:21:\"payment/free_checkout\";i:116;s:17:\"payment/gate2play\";i:117;s:15:\"payment/halalah\";i:118;s:24:\"payment/innovatepayments\";i:119;s:28:\"payment/iyzico_checkout_form\";i:120;s:23:\"payment/klarna_checkout\";i:121;s:12:\"payment/knet\";i:122;s:20:\"payment/migscheckout\";i:123;s:15:\"payment/mobipay\";i:124;s:15:\"payment/moyasar\";i:125;s:19:\"payment/my_fatoorah\";i:126;s:16:\"payment/nbe_bank\";i:127;s:13:\"payment/okpay\";i:128;s:15:\"payment/onecard\";i:129;s:20:\"payment/payfort_fort\";i:130;s:25:\"payment/payfort_fort_qpay\";i:131;s:26:\"payment/payfort_fort_sadad\";i:132;s:16:\"payment/payoneer\";i:133;s:15:\"payment/paysera\";i:134;s:15:\"payment/paytabs\";i:135;s:13:\"payment/payza\";i:136;s:18:\"payment/pp_express\";i:137;s:15:\"payment/pp_plus\";i:138;s:19:\"payment/pp_standard\";i:139;s:14:\"payment/skrill\";i:140;s:14:\"payment/sofort\";i:141;s:14:\"payment/stripe\";i:142;s:11:\"payment/tap\";i:143;s:19:\"payment/tnspayments\";i:144;s:19:\"payment/twocheckout\";i:145;s:12:\"payment/upay\";i:146;s:16:\"payment/zaincash\";i:147;s:24:\"product_designer/clipart\";i:148;s:24:\"promotions/reward_points\";i:149;s:21:\"report/abandoned_cart\";i:150;s:27:\"report/affiliate_commission\";i:151;s:22:\"report/customer_credit\";i:152;s:22:\"report/customer_online\";i:153;s:21:\"report/customer_order\";i:154;s:22:\"report/customer_reward\";i:155;s:24:\"report/product_purchased\";i:156;s:32:\"report/product_top_ten_purchased\";i:157;s:21:\"report/product_viewed\";i:158;s:14:\"report/reports\";i:159;s:18:\"report/sale_coupon\";i:160;s:17:\"report/sale_order\";i:161;s:18:\"report/sale_return\";i:162;s:20:\"report/sale_shipping\";i:163;s:15:\"report/sale_tax\";i:164;s:14:\"sale/affiliate\";i:165;s:32:\"sale/aramex_bulk_schedule_pickup\";i:166;s:27:\"sale/aramex_create_shipment\";i:167;s:27:\"sale/aramex_rate_calculator\";i:168;s:27:\"sale/aramex_schedule_pickup\";i:169;s:19:\"sale/aramex_traking\";i:170;s:15:\"sale/collection\";i:171;s:12:\"sale/contact\";i:172;s:11:\"sale/coupon\";i:173;s:13:\"sale/customer\";i:174;s:20:\"sale/customer_ban_ip\";i:175;s:19:\"sale/customer_group\";i:176;s:13:\"sale/download\";i:177;s:18:\"sale/externalorder\";i:178;s:10:\"sale/order\";i:179;s:11:\"sale/return\";i:180;s:16:\"sale/smshare_sms\";i:181;s:12:\"sale/voucher\";i:182;s:18:\"sale/voucher_theme\";i:183;s:26:\"sale/zajil_create_shipment\";i:184;s:19:\"security/throttling\";i:185;s:23:\"setting/advancedsetting\";i:186;s:28:\"setting/audit_trail_settings\";i:187;s:15:\"setting/domains\";i:188;s:14:\"setting/dropna\";i:189;s:19:\"setting/integration\";i:190;s:16:\"setting/language\";i:191;s:15:\"setting/setting\";i:192;s:20:\"setting/setting_mail\";i:193;s:21:\"setting/store_account\";i:194;s:24:\"setting/store_affiliates\";i:195;s:22:\"setting/store_checkout\";i:196;s:21:\"setting/store_general\";i:197;s:19:\"setting/store_items\";i:198;s:22:\"setting/store_products\";i:199;s:21:\"setting/store_returns\";i:200;s:21:\"setting/store_scripts\";i:201;s:19:\"setting/store_stock\";i:202;s:19:\"setting/store_taxes\";i:203;s:19:\"setting/store_units\";i:204;s:22:\"setting/store_vouchers\";i:205;s:16:\"setting/template\";i:206;s:15:\"shipping/aramex\";i:207;s:31:\"shipping/category_product_based\";i:208;s:12:\"shipping/dhl\";i:209;s:20:\"shipping/dhl_express\";i:210;s:14:\"shipping/fedex\";i:211;s:23:\"shipping/fedex_domestic\";i:212;s:13:\"shipping/flat\";i:213;s:13:\"shipping/free\";i:214;s:13:\"shipping/item\";i:215;s:15:\"shipping/pickup\";i:216;s:13:\"shipping/saee\";i:217;s:15:\"shipping/salasa\";i:218;s:13:\"shipping/smsa\";i:219;s:12:\"shipping/ups\";i:220;s:15:\"shipping/weight\";i:221;s:14:\"shipping/zajil\";i:222;s:15:\"teditor/teditor\";i:223;s:19:\"templates/customize\";i:224;s:16:\"templates/import\";i:225;s:18:\"templates/template\";i:226;s:11:\"tool/backup\";i:227;s:14:\"tool/error_log\";i:228;s:19:\"tool/product_export\";i:229;s:19:\"tool/product_import\";i:230;s:18:\"tool/w_export_tool\";i:231;s:18:\"tool/w_import_tool\";i:232;s:12:\"total/coupon\";i:233;s:12:\"total/credit\";i:234;s:16:\"total/earn_point\";i:235;s:14:\"total/handling\";i:236;s:19:\"total/low_order_fee\";i:237;s:18:\"total/redeem_point\";i:238;s:12:\"total/reward\";i:239;s:14:\"total/shipping\";i:240;s:15:\"total/sub_total\";i:241;s:9:\"total/tax\";i:242;s:11:\"total/total\";i:243;s:13:\"total/voucher\";i:244;s:8:\"user/api\";i:245;s:18:\"user/notifications\";i:246;s:9:\"user/user\";i:247;s:20:\"user/user_permission\";}}', CURRENT_TIMESTAMP, NULL),
(10, 'Demonstration', '', CURRENT_TIMESTAMP, NULL);
