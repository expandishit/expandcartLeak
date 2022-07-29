<?php

$_['heading_title']             = 'Rajhi Bank';
$_['settings']                  = 'Settings';
$_['switch_text_enabled']       = 'Enabled';
$_['switch_text_disabled']      = 'Disabled';
$_['rajhi_bank_contact']        = 'Contact Rajhi Bank';


// --------------- TEXT ---------------
$_['text_payment']        = 'Payment';
$_['text_success']        = 'Success: You have modified the rajhi_bank payment module!';
$_['text_changes']        = 'There are unsaved changes.';
$_['text_high']           = 'High';
$_['text_medium']         = 'Medium';
$_['text_low']            = 'Low';
$_['text_live']           = 'Live';
$_['text_test']           = 'Test';
$_['text_all_geo_zones']  = 'All Geo Zones';
$_['text_settings']       = 'Settings';
$_['text_log']            = 'Log';
$_['text_general']        = 'General';
$_['text_statuses']       = 'Order Statuses';
$_['text_advanced']       = 'Advanced';


// --------------- ENTRY ---------------
$_['entry_merchant_code']           = 'Merchant code';
$_['entry_merchant_hash_key']      = 'Merchant hash key';
$_['entry_iframe_id']          = 'Iframe ID';
$_['entry_api_server']        = 'API Server';
$_['help_api_server']         = 'Use the '.$_['text_live'].' or '.$_['text_test'].' server to process transactions';
$_['entry_risk_speed']        = 'Risk/Speed';
$_['help_risk_speed']         = '<strong>High</strong> speed confirmations typically take 5-10 seconds, and can be used for digital goods or low-risk items<br><strong>Low</strong> speed confirmations take about 1 hour, and should be used for high-value items';
$_['entry_geo_zone']          = 'Geo Zone';
$_['entry_status']            = 'Status';
$_['entry_sort_order']        = 'Sort Order';
$_['entry_new_status']        = 'New';
$_['help_new_status']         = 'A new or partially paid invoice awaiting full payment';
$_['entry_paid_status']       = 'Paid';
$_['help_paid_status']        = 'A fully paid invoice awaiting confirmation';
$_['entry_confirmed_status']  = 'Confirmed';
$_['help_confirmed_status']   = 'A confirmed invoice per Risk/Speed settings';
$_['entry_complete_status']   = 'Complete order status';
$_['entry_failed_status']      = 'Failed order status';
$_['entry_refund_status']      = 'Refund order status';
$_['help_complete_status']    = 'A confirmed invoice that has been credited to the merchant&#8217;s account';
$_['entry_expired_status']    = 'Expired';
$_['help_expired_status']     = 'An invoice where full payment was not received and the 15 minute payment window has elapsed';
$_['entry_invalid_status']    = 'Invalid';
$_['help_invalid_status']     = 'An invoice that was fully paid but wasn&#8217;t confirmed';
$_['entry_notify_url']        = 'Notification URL';
$_['help_notify_url']         = 'rajhi_bank &#8217;s IPN will post invoice status updates to this URL';
$_['entry_return_url']        = 'Return URL';
$_['help_return_url']         = 'rajhi_bank will provide a redirect link to the user for this URL upon successful payment of the invoice';
$_['entry_debug_mode']        = 'Debug Mode';
$_['help_debug_mode']         = 'Logs additional information to the rajhi_bank log';
$_['entry_default']           = 'Default';
$_['entry_contact_rajhi_bank']    = "Contact With rajhi_bank";
$_['rajhi_bank_transportal_id']            = "Transportal Id";
$_['rajhi_bank_transportal_password']    = "Transportal Password";
$_['rajhi_bank_resource_key']            = "Resource Key";
$_['rajhi_bank_gatway_endpoint']         = "Gateway EndPoint";
$_['rajhi_bank_support_endpoint']         = "Supporting EndPoint";

// --------------- ERRORS ---------------
$_['error_permission']        = 'Warning: You do not have permission to modify rajhi_bank payment module.';

$_['error_merchant_code']     = 'Merchant Code is required (for authenticated payment notices)';
$_['error_merchant_hash_key']     = 'Merchant Hash Key is required (for authenticated payment notices)';
$_['error_notify_url']        = 'Notification URL is required';
$_['error_return_url']        = 'Return URL is required';
$_['error_api_key_valid']     = 'API Key needs to be a valid rajhi_bank API Access Key';
$_['error_notify_url_valid']  = 'Notification URL needs to be a valid URL resource';
$_['error_return_url_valid']  = 'Return URL needs to be a valid URL resource';
$_['error_transportal_id']            = "Transportal Id";
$_['error_transportal_password']    = "Transportal Password";
$_['error_resource_key']            = "Resource Key";
$_['error_gatway_endpoint']         = "Gateway EndPoint not supported";
$_['error_support_endpoint']         = "Supporting EndPoint not supported";
