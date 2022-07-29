<?php

// Heading Goes here:
$_['heading_title']     = 'Notifications Sms'; 


// Text
$_['text_module']       = 'Apps'; 
$_['text_success']      = 'Serkeftin: We agahdariya SMS-ê module guhertin!'; 
$_['text_left']         = 'Çep'; 
$_['text_right']        = 'Rast'; 
$_['text_home']         = 'Xane'; 

$_['text_yes']          = 'Erê'; 
$_['text_no']           = 'Na'; 


// Entry
$_['smshare_entry_username']  = 'Navê bikarhêner'; 
$_['smshare_entry_passwd']    = 'Şîfre'; 

$_['smshare_entry_status']    = 'Cî'; 

$_['smshare_entry_notify_customer_by_sms_on_registration'] = 'Notify customer on <b>registration</b>: <br />' . 
														     '<span class="help">Send SMS to customer once he completes the registration.</span>';

$_['smshare_entry_notify_seller_on_status_change'] = 'Notify seller on <b>admin changes a status</b>: <br />' . 
														     '<span class="help">Send SMS to seller once admin change his status.</span>';
                                                             
$_['smshare_entry_cstmr_reg_available_vars']               = 'Currently, only <b>{firstname}</b>, <b>{lastname}</b>, <b>{phonenumber}</b> and <b>{password}</b> are available for substitution';
															 
$_['smshare_entry_notify_customer_by_sms_on_checkout']     = 'Notify customer for <b>new order</b>:   <br />' . 
															 '<span class="help">Send SMS to customers once he completes a new order</span>';
															 
$_['smshare_entry_ntfy_admin_by_sms_on_reg'] 		       = 'Notify store owner on <b>registration</b>: <br />' . 
														     '<span class="help">Send SMS to store owner when a customer completes the registration</span>';

$_['smshare_entry_notify_admin_by_sms_on_checkout']        = 'Notify store owner for <b>new order</b>:<br />' . 
															 '<span class="help">Send SMS to the store owner when a new order is created</span>';
															 
$_['smshare_entry_notify_extra_by_sms_on_checkout']        = 'Additional Alert phone numbers:  <br />' . 
															 '<span class="help">Any additional phone numbers you want to receive the alert by sms (comma separated). ' . 
															 '<br />If filled then SMS will be sent even if you disable "Notify store owner for new order"</span>';

$_['smshare_entry_order_available_vars'] = 'Currently, only <b>{order_id}</b>, <b>{order_date}</b> are available for substitution';

// Error
$_['error_permission']  = 'Hişyarî: Hûn destûr nadin ku hûn nermbûna modulê biguherînin!'; 
$_['text_footer']       = 'Heke pirsên we hebin ji kerema xwe vê SMS-ê bersiv bikin.'; 



$_['text_gateway_setup']  = 'Sazkirina Gateway'; 
$_['text_sms_tem']  = 'SMS templating'; 
$_['text_customer_notif']  = 'Alertên Xerîdar'; 
$_['text_admin_notif']  = 'Alertên Admin'; 
$_['text_seller_notif']  = 'Alertên Firotanê'; 
$_['text_sms_filter']  = 'SMS Filter'; 
$_['text_number_rewrite']  = 'Rewitandina Hejmar'; 
$_['text_logs']  = 'Logs'; 
$_['text_api_url']  = 'Url api'; 
$_['text_api_http_method']  = 'Rêbaza HTTP'; 
$_['text_get']  = 'STENDIN'; 
$_['text_post_1']  = 'Post (Pirjimar / Form-Data)'; 
$_['text_post_2']  = 'Post (Serlêdan / X-www-form-urlencoded)'; 
$_['text_api_method_help']  = '<p> 
                          POST (multipart/form-data) or POST (application/x-www-form-urlencoded)?
                          As usual, check the gateway documentation. But here are some hints:
                          </p>
                              <ul>
                                  <li>API from old SMS gateways are used to use POST (multipart/form-data)</li>
                                  <li>Most recent SMS gateway APIs use POST (application/x-www-form-urlencoded)</li>
                              </ul>
                          </p>';
$_['text_dest_field']  = 'Zeviya behrê'; 
$_['text_dest_field_help']  = 'Ev navê guherbar e ku hejmarên armancê temsîl dike.'; 
$_['text_dest_field_placeholder']  = 'Ex: mobîl an betlaneyên'; 
$_['text_msg_field']  = 'Zeviya peyamê'; 
$_['text_msg_field_help']  = 'Ev navê guherbar e ku peyamê temsîl dike.'; 
$_['text_msg_field_placeholder']  = 'Ex: peyam'; 
$_['text_unicode']  = 'Unicode?'; 
$_['text_unicode_help']  = 'Hin deriyê API (ex. Ji bo zimanê erebî) hewce dike ku laşê peyamê ji unicode were veguheztin.'; 
$_['text_unicode_help_2']  = 'Em berî şandina \ u rakirin. Ex: <b> test </ b> dê wekî: <b> 0074006500730074 </ b> '; 
$_['text_additional_fields']  = 'Zeviyên zêde'; 
$_['text_add_new_field']  = 'Zeviya nû zêde bikin'; 
$_['text_name']  = 'Nav'; 
$_['text_field_name']  = 'Navê zeviyê'; 
$_['text_value']  = 'Giranî'; 
$_['text_field_value']  = 'Nirxa zeviyê'; 
$_['text_url_encode']  = 'Encode URL'; 
$_['text_remove_field']  = 'Vê zeviyê rakirin'; 
$_['text_url_encode_help']  = '<p style 
                            <p>URL encoding converts characters into a format that can be sent through internet.</p>
                            <p>We should use urlencode for all GET parameters because POST parameters are automatically encoded.</p>
                            <p>Some API doesn\'t understand some URL encoded fields when sending with GET. If this is the case, disable URL encoding for the concerned fields.</p>';
$_['text_sms_template_system']  = 'Pergala pergala şablonan'; 
$_['text_sms_temp_sys_1']  = 'Hûn ê bi karanîna "guherbarên" yên pêşîn ên ku cîh hene hene ku dê li gorî agahdariya rastîn li şûna.'; 
$_['text_available_var']  = 'Guhertoyên berdest'; 
$_['text_arrow']  = '→'; 
$_['text_firstname']  = 'Nav'; 
$_['text_lastname']  = 'Paşnav'; 
$_['text_phonenumber']  = 'Jimare telefon'; 
$_['text_orderid']  = 'ID fermanê'; 
$_['text_total']  = 'Hemî'; 
$_['text_storeurl']  = 'URL Store'; 
$_['text_shippingadd1']  = 'Navnîşana barkirinê 1'; 
$_['text_shippingadd2']  = 'Navnîşa barkirinê 2'; 
$_['text_payadd1']  = 'Navnîşana Payment 1'; 
$_['text_payadd2']  = 'Navnîşana dayînê 2'; 
$_['text_paymethod']  = 'Rêbaza dayînê'; 
$_['text_shipmethod']  = 'Rêbaza barkirinê'; 
$_['text_sms_system_example']  = 'Mînak: <çîna span 
					<span class = "Alîkarî"> <i> delal <b> {SIRTNAME} </ b> > </ i> </ span> 
					<br />
					<span class = "Alîkarî"> Dotira rojê xerîdar fermanek (bila em bêjin Ehmed), ew ê SMS-ya ku li jêr tê de bistîne bistîne: </ span> 
					<span class = "Alîkarî"> <i> delal <b> Ahmed </ Ahmed </ B >> / span> '; 
$_['text_cus_reg_temp']  = 'Mişterî <b> qeydkirin </ b> SMS şablonê'; 
$_['text_cus_order_temp']  = 'Mişterî <b> Order nû </ b> SMS şablon'; 
$_['text_no_send_kw']  = 'Do-Not-Send keywords'; 
$_['text_no_send_help']  = 'SMS-ê li ser <B> Fermana Nû </ B> bişînin </ b> Heke yek ji keywordsên jêrîn di dema kontrolkirinê de di Kupon de tê bikar anîn. 
					<br />
					One keyword per line (a keyword can contain spaces).';
$_['text_sms_order_status']  = 'SMS şablonê li ser guhertina statuya fermanê'; 
$_['text_sms_seller_status']  = 'SMS şablonê li ser guhertina statûya firotanê'; 
$_['text_sms_order_status_help']  = 'Dema ku hûn di rûpelê dîroka fermanê de rewşa fermanê nûve bikin, şablon tê bikar anîn. 
                  (you must have checked the <i>&ldquo;Notify by SMS&rdquo;</i> checkbox).
                  <br />
                  <br />
                  In addition to the variables listed above, Four other variables can be used here: <b>{default_template}</b> , <b>{order_id}</b> , <b>{order_date}</b>, <b>{comment}</b> 
                  which is the comment you write when you add history.
                  <br />
                  <br />
                  Empty text box will use default template.
                  <br />
                  <br />';
$_['text_sms_seller_status_help']  = 'Wateya SMS-ê tê bikar anîn dema ku Rewşa Firotanê ya Kirrûbirra Di Rûpelê Firoşyar de. 
                  <br />
                  <br />
                  In addition to the variables listed above, Five other variables can be used here: <b>{default_template}</b> , <b>{seller_email}</b> , <b>{seller_firstname}</b>, <b>{seller_lastname}</b>, <b>{seller_nickname}</b> 
                  <br />
                  <br />
                  Empty text box will use default template.
                  <br />
                  <br />';
$_['text_add_new_fields']  = 'Zeviyên nû zêde bikin'; 
$_['text_status']  = 'Cî'; 
$_['text_seperator']  = '───────'; 
$_['text_admin_cust_reg']  = 'Xwediyê Store <b> "Li ser qeydkirina xerîdar" </ b> SMS şablon'; 
$_['text_admin_sms_temp']  = 'Xwediyê şablonê SMS-ê hilîne'; 
$_['text_admin_sms_temp_help']  = 'Wekî din guherbarên ku li jor hatine tomarkirin, du guhêrbariyên taybetî hene <b> {| {Compact_Default_Template} </ b> ku hûn bikar bînin ku hûn bikar bînin şablonê default (ya ku mezinahiya SMS kêm dike) '; 
$_['text_admin_order_status']  = 'SMS şablonê li ser guhertina statuya fermanê'; 
$_['text_admin_order_status_help']  = 'Dema ku hûn di rûpelê dîroka fermanê de rewşa fermanê nûve bikin, şablon tê bikar anîn. 
                  <br />
                  <br />
                  In addition to the variables listed above, two other variables can be used here: <b>{default_template}</b> and <b>{comment}</b>
                  which is the comment you write when you add history.
                  <br />
                  <br />
                  Empty text box will use default template.
                  <br />
                  <br />';
$_['text_add_new_fields_2']  = 'Zeviyên nû zêde bikin'; 
$_['text_status_2']  = 'Cî'; 
$_['text_seperator_2']  = '───────'; 
$_['text_phone_num_filter']  = 'Filter Hejmara Telefonê: <i> <b> dest pê dike-bi </ b> </ i>'; 
$_['text_phone_num_filter_help']  = 'SMS tenê bişînin Heke Hejmara Telefonê bi hejmarên ku hûn li vir têkevin dest pê bikin. <br/> 
                                Multiple patterns must be comma separated. Example: 00336,+336,06';
$_['text_filter_size']  = 'Fîlterkirina Hejmara Telefon: <i> <b> Mezinahiya hejmarê </ b> </ i>'; 
$_['text_filter_size_help']  = 'SMS tenê bişînin heke hejmara têlefonê bi hejmaran x-ê we bikeve vir. Mînakî: Heke hûn nirxa 8-ê saz bikin, dê SMS-ê otomatîk ji 12345678 re bêne şandin lê ne 2345678. '; 
$_['text_phone_rewrite']  = 'Ragihandina Hejmara Telefonê'; 
$_['text_phone_rewrite_help']  = 'Berî şandina SMS-ê li şûna hejmarê têlefonê bikin. 
                            <br />
                            Rewriting is applied only after filtering rules are applied.';
$_['text_replace_1_occ']  = 'Pêşkêşkirina yekem li şûna'; 
$_['text_pattern']  = 'mînak'; 
$_['text_by']  = 'ji'; 
$_['text_substitution']  = 'tespîtkirin'; 
$_['text_enable_logs']  = 'Têketinên têkevin'; 
$_['text_enable_logs_help']  = 'Têketên Verbos dê ji pelê têketinê re bêne çap kirin. Kêrhatî dema ku hûn hewce ne ku fêm bikin ka çi diqewime. '; 

$_['text_sms_confirm']  = 'Telefonê li ser <b> fermana nû piştrast bikin </ b>: <br />'. 
	'<span class = "Alîkarî"> Hejmara Telefonê Xerîdar li ser fermana nû bikar bînin ku SMS </ SPAM> '; 
$_['text_sms_confirm_per_order']  = 'Telefonê bikirtînin <b> Her fermanê </ b>: <br />'. 
    '<span class = "Alîkarî"> Hejmara telefona xerîdar jî heke têlefona têlefonê verast kir </ span> '; 
$_['text_sms_confirm_trials']  = 'SMS Pêvekirina Maximum'; 
$_['text_sms_confirm_trials_help']  = 'Dozên herî zêde hejmartin ku xerîdar dikare daxwaz bike ku piştrast bike ku SMS-yê ji wî re dilsoz be'; 
$_['text_sms_confirm_template']  = 'Şablonê pejirandina SMS'; 
$_['text_sms_confirm_template_help']  = 'Theablonê ku di dema pejirandina têlefonê de tê bikar anîn SMS bikar anîn. Hûn tenê dikarin <b> {SIRTNAME} </ b>, <b> {paşnav} </ b>, <b> {phonenumber} </ b>. <br> you pêdivî ye ku hûn <b> pejirandina pejirandinê </ b> pêdivî ye ku ew pêdivî ye, da ku peyam kodê pejirandinê pêk bîne '; 

$_['text_tab_supported']  = 'Gatewayan'; 
$_['text_supported_providers']  = 'Em piştgiriyê didin gelek parêzerên SMS li hemû welatan, wek'; 
$_['text_supported_providers_help']  = 'Heke hûn hewceyê alîkariyê bi çalakiyek karûbarê ji jorîn, an jî pêşkêşvanek karûbarê din, ji kerema xwe ji yek ji nûnerên karûbarên xerîdar ên xerîdar re bipeyivin.'; 



$_['activation_message_template']  = 'Şablonê peyama çalakbûnê'; 
$_['activation_message_template_note'] = 'You can use<br /><b>{activationToken}</b>';

$_['code_settings']  = 'Mîhengên Koda aktîfkirinê'; 
$_['code_length']  = 'Dirêjahiya kodê'; 
$_['code_type']  = 'Tîpa kodê'; 
$_['code_alphanumeric']  = 'Alphanumeric'; 
$_['code_numeric']  = 'Hejmarên'; 

$_['text_seller_status_notification_header']  = 'Delal,'; 
$_['text_seller_status_notification_body_prefix']  = 'Rewşa hesabê we ye'; 
$_['text_seller_status_notification_footer']  = ''; 
