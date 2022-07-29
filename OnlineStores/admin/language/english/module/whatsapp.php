<?php

// Heading Goes here:
$_['heading_title']    = 'WhatsApp Notifications';
$_['heading_title_whatsapp']    = 'WhatsApp';
$_['heading_title_settings']    = 'WhatsApp Settings';

// Text
$_['text_module']      = 'Apps';
$_['text_success']     = 'Success: You have modified module WhatsApp Notifications!';
$_['text_chat_success'] = 'Success: You have modified  WhatsApp chat settings !';
$_['text_left']        = 'Left';
$_['text_right']       = 'Right';
$_['text_home']        = 'Home';

$_['text_yes']         = 'Yes';
$_['text_no']          = 'No';

// Entry
$_['text_phone_num_filter'] = 'Phone number start with';
$_['text_phone_num_filter_help'] = 'This rule for receiver phone numbers, WhatsApp will send only if the phone number starts with the digits you enter here.
You able to enter multiple patterns, it must be comma separated (ie: 00971,+971,0971)';

$_['whatsapp_entry_notify_customer_by_WhatsApp_on_registration'] = '<b>Registration notifications:</b>';
$_['whatsapp_entry_notify_customer_by_WhatsApp_on_registration_help'] = 'WhatsApp will send a notification to the customer once he completes his registration.';

$_['text_cus_reg_temp'] = '<b>Customer registration message template</b>';

$_['activation_message_template'] = '<b>Activation message template</b>';

$_['text_message_header'] = 'Header';
$_['text_message_body'] = 'Body';
$_['text_message_footer'] = 'Footer';

$_['text_WhatsApp_confirm_per_order'] = '<b>Confirm code for every new order:</b>';
$_['text_WhatsApp_confirm_per_order_help'] = 'By enable this, customer should receive confirmation code with each new order, even if the phone verified before';

$_['text_WhatsApp_confirm'] = '<b>Confirm phone on first order only:</b>';
$_['text_WhatsApp_confirm_help'] = 'By enable this, customer should receive confirmation code with one time';


$_['text_WhatsApp_confirm_trials'] = 'Maximum confirmation messages';

$_['text_WhatsApp_confirm_template'] = 'The message template';

$_['text_WhatsApp_confirm_trials_help'] = 'The maximum trials count which the customer can request resend the confirmation code to him';

$_['whatsapp_entry_notify_customer_by_WhatsApp_on_checkout'] = '<b>New order notification:</b>';
$_['whatsapp_entry_notify_customer_by_WhatsApp_on_checkout_help'] = 'By enable this, customers will receive message once he Confirm a new order';

$_['text_cus_order_temp'] = 'The message template';

$_['text_admin_order_status'] = '<b>Update order status notification</b>';
$_['text_WhatsApp_order_status'] = '<b>Update order status notification</b>';

$_['text_WhatsApp_order_status_help'] = 'by enable this, customer will receive message  when you update his orders status
                  <br />
                  In addition to the variables listed above, Four other variables can be used here: <b></b> , <b>{order_id}</b> , <b>{order_date}</b>, <b>{comment}</b> 
                  which is the comment you write when you add history.
                  <br />
                  Empty text box will use default template.';

$_['text_admin_order_status_help'] = 'by enable this, store owner (you) will receive message  when order status is updated
                <br />
                In addition to the variables listed above, Four other variables can be used here: <b></b> , <b>{order_id}</b> , <b>{order_date}</b>, <b>{comment}</b> 
                which is the comment you write when you add history.
                <br />
                Empty text box will use default template.';

$_['text_add_new_field'] = 'Add new message';

$_['whatsapp_entry_ntfy_admin_by_WhatsApp_on_reg'] = '<b>Registration notification:</b>';
$_['whatsapp_entry_ntfy_admin_by_WhatsApp_on_reg_help'] = 'By enable this, store owner (you) will receive message  when a customer completes the registration';

$_['text_admin_cust_reg'] = 'The message template';


$_['whatsapp_entry_notify_admin_by_WhatsApp_on_checkout'] = '<b>New order notification:</b>';
$_['whatsapp_entry_notify_admin_by_WhatsApp_on_checkout_help'] = 'By enable this, store owner (you) will receive message  when a new order is created';

$_['whatsapp_entry_username'] = 'Username';
$_['whatsapp_entry_passwd']   = 'Password';
$_['whatsapp_entry_status']   = 'Status';

$_['whatsapp_entry_notify_seller_on_status_change'] = 'Notify seller on <b>admin changes a status</b>';
$_['whatsapp_entry_notify_seller_on_status_change_help'] = 'Send WhatsApp to seller once admin change his status.';

$_['whatsapp_entry_cstmr_reg_available_vars']               = 'Currently, only <b>{firstname}</b>, <b>{lastname}</b>, <b>{telephone}</b> and <b>{password}</b> are available for substitution';







$_['whatsapp_entry_notify_extra_by_WhatsApp_on_checkout']        = 'Additional Alert phone numbers:  <br />' .
    '<span class="help">Any additional phone numbers you want to receive the alert by WhatsApp (comma separated). ' .
    '<br />If filled then WhatsApp will be sent even if you disable "Notify store owner for new order"</span>';

$_['whatsapp_entry_order_available_vars'] = 'Currently, only <b>{order_id}</b>, <b>{order_date}</b> are available for substitution';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify module whatsapp!';
$_['text_footer']      = 'Please reply to this WhatsApp if you have any questions.';



$_['text_gateway_setup'] = 'Gateway Setup';
$_['text_WhatsApp_tem'] = 'WhatsApp Templating';

$_['text_data_that_will_be_in_DB_only'] = 'data that will be in DB only';
$_['text_integration_status'] = 'WhatsApp Integration status : ';
$_['text_Client_api_status'] = 'Client API status : ';
$_['text_whatsapp_integration'] = 'WhatsApp Integration';
$_['text_whatsapp_phone_number'] = 'WhatsApp phone number';
$_['text_whatsapp_business_account_id'] = 'WhatsApp Business Account Id';
$_['text_template_messages_namespaces'] = 'Template messages namespaces';
$_['text_client_api_url'] = 'client API URL';
$_['text_client_api_username'] = 'client API username';
$_['text_client_api_password'] = 'client API password';

$_['text_customer_notif'] = 'Customer notifications';
$_['text_admin_notif'] = 'Store owner notifications';
$_['text_seller_notif'] = 'Seller Alerts';

$_['text_whatsApp_template_messages'] = 'Template Messages';
$_['text_whatsapp_chat'] = 'WhatsApp Chat';
$_['text_whatsapp'] = 'WhatsApp';
$_['text_language'] = 'language';
$_['text_header'] = 'header';
$_['text_body'] = 'body';
$_['text_footer'] = 'footer';


$_['text_WhatsApp_filter'] = 'Phones country keys';
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
                                  <li>API from old WhatsApp gateways are used to use POST (multipart/form-data)</li>
                                  <li>Most recent WhatsApp gateway APIs use POST (application/x-www-form-urlencoded)</li>
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
$_['text_WhatsApp_template_system'] = 'WhatsApp templating system';
$_['text_WhatsApp_temp_sys_1'] = 'You will be using predefined "variables" which are place-holders that will be replaced by the real information at runtime.';
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
$_['text_WhatsApp_system_example'] = 'Example:<span class="help">If you enter the following: </span>
					<span class="help"><i>Dear <b>{firstname}</b>, Thank you for your order at mystore.com order ID <b>{order_id}</b> amount <b>{total}</b></i></span>
					<br />
					<span class="help">The next time a customer makes an order (let\'s say Ahmed), he will receive a WhatsApp containing the following: </span>
					<span class="help"><i>Dear <b>Ahmed</b>, Thank you for your order at mystore.com order ID <b>9999</b> amount <b>$9999.99</b></i></span>';
$_['text_no_send_kw'] = 'Do-not-send keywords';
$_['text_no_send_help'] = 'Do not send WhatsApp to customer on <b>new order</b> if one of the following keywords is used in the coupon during checkout.
					<br />
					One keyword per line (a keyword can contain spaces).';
$_['text_WhatsApp_seller_status'] = 'WhatsApp template on seller status change';

$_['text_WhatsApp_seller_status_help'] = 'WhatsApp template to be used when admin update seller status in the seller page.
                  <br />
                  In addition to the variables listed above, Five other variables can be used here: <b></b> , <b>{seller_email}</b> , <b>{seller_firstname}</b>, <b>{seller_lastname}</b>, <b>{seller_nickname}</b> 
                  <br />
                  Empty text box will use default template.';
$_['text_add_new_fields'] = 'Add New message';
$_['text_status'] = 'Status';
$_['text_seperator'] = '───────';
$_['text_admin_WhatsApp_temp'] = 'Store owner WhatsApp template';
$_['text_admin_WhatsApp_temp_help'] = 'you can use the variables listed above';


$_['text_add_new_fields_2'] = 'Add New fields';
$_['text_status_2'] = 'Status';
$_['text_seperator_2'] = '───────';

$_['text_filter_size'] = 'Phone number filtering: <i><b>Number size</b></i>';
$_['text_filter_size_help'] = 'Send WhatsApp only if phone number has x digits you enter here. For example: if you set the value to 8 then, automatic WhatsApp will be sent to 12345678 but not to 2345678.';
$_['text_phone_rewrite'] = 'Phone number rewriting';
$_['text_phone_rewrite_help'] = 'Make replacement to phone number before sending WhatsApp.
                            <br />
                            Rewriting is applied only after filtering rules are applied.';
$_['text_replace_1_occ'] = 'Replace first occurrence of';
$_['text_pattern'] = 'pattern';
$_['text_by'] = 'by';
$_['text_substitution'] = 'substitution';
$_['text_enable_logs'] = 'Enable logs';
$_['text_enable_logs_help'] = 'Verbose logs will be printed to the log file. Useful when you need to figure out what is going on.';



$_['text_WhatsApp_confirm_template_help'] = 'The template used during phone confirmation using WhatsApp. You can only use <b>{firstname}</b>, <b>{lastname}</b>, <b>{telephone}</b>. <br>And you have to add <b>{confirm_code}</b> it is a must, so that the message contains the confirmation code';

$_['text_tab_supported'] = 'Gateways';
$_['text_supported_providers'] = 'We support many WhatsApp service providers in all countries, such as';
$_['text_supported_providers_help'] = 'If you need any help in activating any service provider from the above, or any other service provider, please speak to one of our customer service representatives.';

$_['text_status'] = 'status';


$_['activation_message_template_note'] = 'You can use<br /><b>{activationToken}</b>';

$_['code_settings'] = 'Activation Code Settings';
$_['code_length'] = 'Code Length';
$_['code_type'] = 'Code Type';
$_['code_alphanumeric'] = 'Alpha Numeric';
$_['code_numeric'] = 'Numbers';

$_['text_seller_status_notification_header'] = 'Dear,';
$_['text_seller_status_notification_body_prefix'] = 'Your account status is';
$_['text_seller_status_notification_footer'] = '';

$_['text_insert_your_business_Data'] = 'insert your business Data';
$_['text_in_verification']              = 'in verification';
$_['text_get_confirmation_code']         = 'receive confirmation code';
$_['text_enter_confirmation_code']         = 'Enter confirmation code';
$_['text_verified']                       = 'verified';
$_['entry_business_name']                   = 'Business Name';
$_['entry_whatsapp_business_id']          = 'WhatsApp Business ID';
$_['entry_whatsapp_business_id_help']     = 'you can get this ID from your WhatsApp business manager';
$_['entry_whatsapp_phone_number']          = 'Phone Number For WhatsApp';
$_['entry_whatsapp_phone_number_help']     = 'This number should registered as a business number with whatsapp';
$_['entry_country_code']                  = 'Country code';
$_['entry_phone_number']                  = 'Phone Number';
$_['entry_whatsapp_methods']              = 'Verification method';
$_['text_we_are_reviewing_your_Data_and_will_confirm_soon']          = 'We are reviewing your data and will confirm soon.';
$_['entry_whatsapp_verification_code']    = 'verification code';
$_['text_congratulation']                = 'Congratulation ! <br> Integration proccess finished';
$_['btn_next']                               = 'Next';
$_['btn_previous']                           = 'Previous';
$_['btn_finish']                           = "let\'s start";
$_['heading_steps_title']                  = 'integration proccess';


$_['heading_start_with']                  = 'What Feature You Want To Start With?';
$_['title_whatsapp_notifications']    = 'WhatsApp Notifications';
$_['title_whatsapp_notifications_dec']    = 'Engaged with your customers and get real-time notification about customer actions on your online store';
$_['entry_know_more']    = 'Know More';
$_['entry_manage_notifications']  = 'Manage Notifications';
$_['entry_setup']    = 'Setup';
$_['entry_setting']     = 'Settings';
$_['title_whatsapp_chat']    = 'WhatsApp Chat';
$_['title_whatsapp_chat_dec']    = 'Support & communicate with your customers all time';


$_['heading_connect_business']                  = 'Connect WhatsApp Business Account';
$_['heading_active_with_whatsApp_chat']    = 'Active With WhatsApp Chat';
$_['title_connected_business']                  = 'Your Store Is Already Connected To Your WhatsApp Business Account';
$_['title_setup_business']                  = 'Setup WhatsApp Business Account';
$_['dec_setup_business']                  = "To set up, you'll need to connect your company's Facebook Business Account with your store on ExpandCart.";
$_['dec_store_connected']                 = "your store is already connected to your whatsapp business account";
$_['subtitle_setup_business']                  = 'To connect, you should enter,';
$_['entry_back']                  = 'Back';
$_['entry_start_chatting']                  = 'Start Chatting With Customers';
$_['connect_facebook']                  = 'Connect With Facebook';
$_['step_one_setup']                  = "Your company's legal name and address";
$_['step_two_setup']                  = 'A display name and short business description';
$_['step_three_setup']                  = 'A phone number you have access to, owned by your business';
$_['label_app_status']                  = 'App Status';
$_['entry_chatting']                  = 'Chatting';
$_['text_new_messages_have']                  = 'Communicate with your customers | you have';
$_['text_new_messages']                  = 'new messages';
$_['text_displaying']                  = 'Displaying on the store';
$_['text_display_controls']                  = 'Display controls';
$_['text_all_customers']                  = 'All Customers';
$_['text_specific_customer']                  = 'Specific customer groups';
$_['text_sender_data']                  = 'Sender Data';
$_['text_whatsapp_business_id'] = 'WhatsApp Business ID';
$_['text_sender_whatsapp_number'] = 'Sender WhatsApp Number';
$_['text_no_contact_selected'] = 'No Contact Selected ';
$_['text_chat_start_desc'] = 'Start Chat By Selecting From The Contacts Menu';
$_['text_search']                  = 'Search';
$_['text_type_your_message_here']   = 'Type your message here...';
$_['select_customer_group']                  = 'Select customer group';
$_['error_whatsapp_chat_selected_groups']    = 'you should select one group at least';

$_['text_connect_whatsApp_business_account']    	= 'Connect WhatsApp Business Account';
$_['title_setup_whatsApp_business_account']    		= 'Setup WhatsApp Business Account';
$_['text_setup_whatsApp_business_desc']    			= "To set up, you'll need to connect your company's Facebook Business Account with your store on ExpandCart";
$_['text_to_connect_you_should']    				= 'To connect, you should enter,';
$_['text_your_company_legal_documents']    			= "Your company's legal name and address";
$_['text_display_name_and_short_desc']    			= "A display name and short business description";
$_['text_phone_number_you_have_access_to']    		= "A phone number you have access to, owned by your business";
$_['text_connect_with_facebook']    				= "Connect With Facebook";
$_['text_register_success']    						= "Registration succeeded!";
$_['text_thank_you_for_choosing_whatsapp']    		= 'Thank you for choosing WhatsApp!';
$_['text_expandcart_team_review_your_request']   	= ' By submitting the request, the ExpandCart team will review the request and send your request to whatsApp';
$_['text_whatsapp_team_review_your_request']   		= 'By submitting the request, the WhatsApp team will review the request and send youtheir confirmation';
$_['text_now_you_can_send_notifications']   		= 'Now you can Send notifications to your customers using WhatsApp';
$_['text_verify_your_WhatsApp_business_number']   	= 'Verify your WhatsApp business number to start send notifications';
$_['text_congratulations']   					  	= 'Congratulations';
$_['text_start_sending_notifications']   			= 'Start Sending Notifications';
$_['text_send_test_message']   						= 'Send Test Message ';
$_['text_disconnect_whatsApp_account']   			= 'Disconnect WhatsApp account';
$_['text_message_sent']   							= 'Message Sent!';
$_['text_something_went_wrong']   					= 'Something went wrong , please try again later!';
$_['text_something_went_wrong_please_contact_support'] = 'Something went wrong , please contact support! ';
$_['text_account_review_status']   					= 'account review status : %s';
$_['text_phone_verification']   					= 'Phone Verification';
$_['text_error']   									= 'Error!';
$_['text_send']   									= 'Send';
$_['text_back']   									= 'back';
$_['text_no_chat_found']                            = 'you don\'t have chat yet';


