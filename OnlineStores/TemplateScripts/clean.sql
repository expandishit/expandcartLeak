DELETE
FROM    `setting`
WHERE   ( `group` NOT IN ( 'autocomplete_address', 'google_talk', 'localization', 'product_option_image_pro', 'socialslides', 'zopim_live_chat', 'custom_email_templates', 'textplode', 'masspdiscoupd', 'reward_points_pro', 'popupwindow', 'signup',
                           'filter', 'special_count_down', 'multiseller', 'quickcheckout', 'd_social_login', 'mega_filter_module', 'mfilter_plus_version', 'mega_filter_settings', 'mega_filter_attribs', 'mfilter_version', 'store_locations', 'smshare', 'mobile_app', 'advanced_deals', 'product_designer', 'ecdeals', 'ecflashsale',

                           'coupon', 'credit', 'handling', 'klarna_fee', 'low_order_fee', 'reward', 'shipping', 'sub_total', 'tax', 'total', 'voucher',

                           'google_base', 'google_sitemap', 'articles_google_base', 'articles_google_sitemap',

                           'pp_standard', 'twocheckout', 'dixipay', 'faturah', 'gate2play', 'innovatepayments', 'paytabs', 'skrill', 'okpay', 'knet', 'my_fatoorah',
                           'cashu', 'onecard', 'bank_transfer', 'cheque', 'cod', 'ccavenuepay',

                           'aramex', 'ups', 'fedex', 'weight', 'flat', 'item', 'free', 'pickup', 'category_product_based',
                          
                           'expand_seo', 'related_products', 'rental_products', 'dedicated_domains', 'auto_meta_tags', 'network_marketing', 'custom_fees_for_payment_method', 'sendstrap',
                           'sofort', 'fastpaycash', 'pp_plus', 'trustrol'
                           ) )
        AND NOT ( `group` = 'config'
                  AND `key` IN ( 'config_error_log', 'config_error_display', 'config_compression', 'config_encryption', 'config_password', 'config_maintenance',
                                 'config_seo_url', 'config_file_extension_allowed', 'config_file_mime_allowed', 'config_secure', 'config_shared',
                                 'config_robots', 'config_fraud_status_id', 'config_fraud_score', 'config_fraud_key', 'config_fraud_detection',
                                 'config_alert_emails', 'config_account_mail', 'config_alert_mail', 'config_smtp_timeout', 'config_smtp_port',
                                 'config_smtp_password', 'config_smtp_username', 'config_smtp_host', 'config_mail_parameter', 'config_mail_protocol',
                                 'config_ftp_status', 'config_ftp_root', 'config_ftp_username', 'config_ftp_password', 'config_error_filename',
                                 'config_google_analytics', 'config_ftp_port', 'config_ftp_host', 'config_icon', 'config_logo', 'config_return_status_id',
                                 'config_return_id', 'config_commission', 'config_affiliate_id', 'config_stock_status_id', 'config_stock_checkout',
                                 'config_stock_warning', 'config_stock_display', 'config_complete_status_id', 'config_order_status_id',
                                 'config_order_shipped_status_id', 'config_order_cod_status_id', 'config_invoice_prefix', 'config_order_popup',
                                 'config_checkout_id', 'config_order_edit', 'config_guest_checkout', 'config_cart_weight', 'config_account_id',
                                 'config_customer_price', 'config_customer_group_display', 'config_customer_group_id', 'config_customer_online',
                                 'config_tax_customer', 'config_tax_default', 'config_vat', 'config_tax', 'config_voucher_max', 'config_voucher_min',
                                 'config_download', 'config_review_status', 'config_product_count', 'config_admin_limit', 'config_catalog_limit',
                                 'config_weight_class_id', 'config_length_class_id', 'config_currency_auto', 'config_currency', 'config_admin_language',
                                 'config_language', 'config_zone_id', 'config_country_id', 'config_layout_id', 'config_template', 'config_meta_description',
                                 'config_fax', 'config_title', 'config_telephone', 'config_email', 'config_address', 'config_owner', 'config_name', 'config_url', 'config_webhook_url' )
                );

DELETE
FROM    `extension`
WHERE   `type` = 'module'
        AND `code` NOT IN ( 'autocomplete_address', 'google_talk', 'localization', 'product_option_image_pro', 'socialslides', 'zopim_live_chat', 'quickcheckout', 'mega_filter', 'd_social_login', 'custom_email_templates', 'textplode', 'masspdiscoupd', 'reward_points_pro', 'popupwindow', 'signup',
                           'filter', 'special_count_down', 'multiseller', 'social', 'total', 'feed', 'payment', 'shipping', 'store_locations', 'smshare', 'mobile_app', 'advanced_deals', 'ecdeals', 'ecflashsale', 'product_designer',

                           'expand_seo', 'related_products', 'rental_products', 'dedicated_domains', 'auto_meta_tags', 'network_marketing', 'custom_fees_for_payment_method', 'sendstrap', 'trustrol'
                           );

DROP TABLE IF EXISTS `megamenu`;
DROP TABLE IF EXISTS `megamenu_description`;
DROP TABLE IF EXISTS `megamenu_widgets`;
DROP TABLE IF EXISTS `pavoslidergroups`;
DROP TABLE IF EXISTS `pavosliderlayers`;
