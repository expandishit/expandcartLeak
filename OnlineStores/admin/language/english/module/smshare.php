<?php

// Heading Goes here:
$_['heading_title']    = 'SMS Notifications';


// Text
$_['text_module']      = 'Apps';
$_['text_success']     = 'Success: You have modified module SMS Notifications!';
$_['text_left']        = 'Left';
$_['text_right']       = 'Right';
$_['text_home']        = 'Home';

$_['text_yes']         = 'Yes';
$_['text_no']          = 'No';


// Entry
$_['smshare_entry_username'] = 'Username';
$_['smshare_entry_passwd']   = 'Password';

$_['smshare_entry_status']   = 'Status';

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
$_['error_permission'] = 'Warning: You do not have permission to modify module smshare!';
$_['text_footer']      = 'Please reply to this sms if you have any questions.';



$_['text_gateway_setup'] = 'Gateway Setup';
$_['text_sms_tem'] = 'SMS Templating';
$_['text_customer_notif'] = 'Customer Alerts';
$_['text_admin_notif'] = 'Admin Alerts';
$_['text_seller_notif'] = 'Seller Alerts';
$_['text_sms_filter'] = 'SMS Filtering';
$_['text_number_rewrite'] = 'Number Rewriting';
$_['text_logs'] = 'Logs';
$_['text_api_url'] = 'API URL';
$_['text_api_http_method'] = 'HTTP method';
$_['text_get'] = 'GET';
$_['text_post_1'] = 'POST (multipart/form-data)';
$_['text_post_2'] = 'POST (application/x-www-form-urlencoded)';
$_['text_api_method_help'] = '<p>
                          POST (multipart/form-data) or POST (application/x-www-form-urlencoded)?
                          As usual, check the gateway documentation. But here are some hints:
                          </p>
                              <ul>
                                  <li>API from old SMS gateways are used to use POST (multipart/form-data)</li>
                                  <li>Most recent SMS gateway APIs use POST (application/x-www-form-urlencoded)</li>
                              </ul>
                          </p>';
$_['text_dest_field'] = 'The destination field';
$_['text_dest_field_help'] = 'This is the name of the variable that represents the destination numbers.';
$_['text_dest_field_placeholder'] = 'ex: mobiles or destinations';
$_['text_msg_field'] = 'The message field';
$_['text_msg_field_help'] = 'This is the name of the variable that represents the message.';
$_['text_msg_field_placeholder'] = 'ex: message';
$_['text_unicode'] = 'Unicode?';
$_['text_unicode_help'] = 'Some API gateway (Ex. for Arabic language) requires the message body to be converted to Unicode.';
$_['text_unicode_help_2'] = 'We remove the \u before sending. Ex: <b>test</b> will be sent as: <b>0074006500730074</b>';
$_['text_additional_fields'] = 'Additional fields';
$_['text_add_new_field'] = 'Add New field';
$_['text_name'] = 'Name';
$_['text_field_name'] = 'field name';
$_['text_value'] = 'Value';
$_['text_field_value'] = 'field value';
$_['text_url_encode'] = 'URL encode';
$_['text_remove_field'] = 'Remove this field';
$_['text_url_encode_help'] = '                            <p style="color: black;">What is Url Encoding?</p>
                            <p>URL encoding converts characters into a format that can be sent through internet.</p>
                            <p>We should use urlencode for all GET parameters because POST parameters are automatically encoded.</p>
                            <p>Some API doesn\'t understand some URL encoded fields when sending with GET. If this is the case, disable URL encoding for the concerned fields.</p>';
$_['text_sms_template_system'] = 'SMS templating system';
$_['text_sms_temp_sys_1'] = 'You will be using predefined "variables" which are place-holders that will be replaced by the real information at runtime.';
$_['text_available_var'] = 'Available variables';
$_['text_arrow'] = '→';
$_['text_firstname'] = 'Firstname';
$_['text_lastname'] = 'Lastname';
$_['text_phonenumber'] = 'Phone number';
$_['text_orderid'] = 'Order ID';
$_['text_total'] = 'Total';
$_['text_storeurl'] = 'Store url';
$_['text_shippingadd1'] = 'Shipping address 1';
$_['text_shippingadd2'] = 'Shipping address 2';
$_['text_payadd1'] = 'Payment address 1';
$_['text_payadd2'] = 'Payment address 2';
$_['text_paymethod'] = 'payment method';
$_['text_shipmethod'] = 'shipping method';
$_['text_sms_system_example'] = 'Example:<span class="help">If you enter the following: </span>
					<span class="help"><i>Dear <b>{firstname}</b>, Thank you for your order at mystore.com order ID <b>{order_id}</b> amount <b>{total}</b></i></span>
					<br />
					<span class="help">The next time a customer makes an order (let\'s say Ahmed), he will receive a SMS containing the following: </span>
					<span class="help"><i>Dear <b>Ahmed</b>, Thank you for your order at mystore.com order ID <b>9999</b> amount <b>$9999.99</b></i></span>';
$_['text_cus_reg_temp'] = 'Customer <b>registration</b> SMS template';
$_['text_cus_order_temp'] = 'Customer <b>new order</b> SMS template';
$_['text_no_send_kw'] = 'Do-not-send keywords';
$_['text_no_send_help'] = 'Do not send SMS to customer on <b>new order</b> if one of the following keywords is used in the coupon during checkout.
					<br />
					One keyword per line (a keyword can contain spaces).';
$_['text_sms_order_status'] = 'SMS template on order status change';
$_['text_sms_seller_status'] = 'SMS template on seller status change';
$_['text_sms_order_status_help'] = 'SMS template to be used when you update order status in the order history page.
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
$_['text_sms_seller_status_help'] = 'SMS template to be used when admin update seller status in the seller page.
                  <br />
                  <br />
                  In addition to the variables listed above, Five other variables can be used here: <b>{default_template}</b> , <b>{seller_email}</b> , <b>{seller_firstname}</b>, <b>{seller_lastname}</b>, <b>{seller_nickname}</b> 
                  <br />
                  <br />
                  Empty text box will use default template.
                  <br />
                  <br />';
$_['text_add_new_fields'] = 'Add New fields';
$_['text_status'] = 'Status';
$_['text_seperator'] = '───────';
$_['text_admin_cust_reg'] = 'Store owner <b>"on customer registration"</b> SMS template';
$_['text_admin_sms_temp'] = 'Store owner SMS template';
$_['text_admin_sms_temp_help'] = 'In addition to the variables listed above, there are two special variables <b>{default_template}</b> and <b>{compact_default_template}</b> that you can use to inject the default template or a compact version of the default template (which reduces the SMS size)';
$_['text_admin_order_status'] = 'SMS template on order status change';
$_['text_admin_order_status_help'] = 'SMS template to be used when you update order status in the order history page.
                  <br />
                  <br />
                  In addition to the variables listed above, two other variables can be used here: <b>{default_template}</b> and <b>{comment}</b>
                  which is the comment you write when you add history.
                  <br />
                  <br />
                  Empty text box will use default template.
                  <br />
                  <br />';
$_['text_add_new_fields_2'] = 'Add New fields';
$_['text_status_2'] = 'Status';
$_['text_seperator_2'] = '───────';
$_['text_phone_num_filter'] = 'Phone number filtering: <i><b>Starts-with</b></i>';
$_['text_phone_num_filter_help'] = 'Send SMS only if phone number starts with the digits you enter here.<br/>
                                Multiple patterns must be comma separated. Example: 00336,+336,06';
$_['text_filter_size'] = 'Phone number filtering: <i><b>Number size</b></i>';
$_['text_filter_size_help'] = 'Send SMS only if phone number has x digits you enter here. For example: if you set the value to 8 then, automatic SMS will be sent to 12345678 but not to 2345678.';
$_['text_phone_rewrite'] = 'Phone number rewriting';
$_['text_phone_rewrite_help'] = 'Make replacement to phone number before sending SMS.
                            <br />
                            Rewriting is applied only after filtering rules are applied.';
$_['text_replace_1_occ'] = 'Replace first occurrence of';
$_['text_pattern'] = 'pattern';
$_['text_by'] = 'by';
$_['text_substitution'] = 'substitution';
$_['text_enable_logs'] = 'Enable logs';
$_['text_enable_logs_help'] = 'Verbose logs will be printed to the log file. Useful when you need to figure out what is going on.';
$_['text_api_type'] = 'api Type';
$_['text_json'] = 'json';
$_['text_xml'] = 'xml (vodafone)';
$_['text_vodafone_note'] = 'For Vodafone Please add AccountId And Password And SecretKey And SenderName';

$_['text_sms_confirm'] = 'Confirm phone on <b>new order</b>:   <br />' .
	'<span class="help">Confirm customer phone number on new order using SMS</span>';
$_['text_sms_confirm_per_order'] = 'Confirm phone <b>every order</b>:   <br />' .
    '<span class="help">Confirm customer phone number even if the phone verified before</span>';
$_['text_sms_confirm_trials'] = 'Maximum confirmation SMS';
$_['text_sms_confirm_trials_help'] = 'The maximum trials count the customer can request the confirmation SMS to be resent to him';
$_['text_sms_confirm_template'] = 'SMS Confirmation Template';
$_['text_sms_confirm_template_help'] = 'The template used during phone confirmation using SMS. You can only use <b>{firstname}</b>, <b>{lastname}</b>, <b>{phonenumber}</b>. <br>And you have to add <b>{confirm_code}</b> it is a must, so that the message contains the confirmation code';

$_['text_tab_supported'] = 'Gateways';
$_['text_supported_providers'] = 'We support many SMS service providers in all countries, such as';
$_['text_supported_providers_help'] = 'If you need any help in activating any service provider from the above, or any other service provider, please speak to one of our customer service representatives.';



$_['activation_message_template'] = 'Activation Message Template';
$_['activation_message_template_note'] = 'You can use<br /><b>{activationToken}</b>';

$_['code_settings'] = 'Activation Code Settings';
$_['code_length'] = 'Code Length';
$_['code_type'] = 'Code Type';
$_['code_alphanumeric'] = 'Alpha Numeric';
$_['code_numeric'] = 'Numbers';

$_['text_seller_status_notification_header'] = 'Dear,';
$_['text_seller_status_notification_body_prefix'] = 'Your account status is';
$_['text_seller_status_notification_footer'] = '';
