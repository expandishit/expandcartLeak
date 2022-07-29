<?php

// Heading
$_['heading_title'] = 'NBE Bank';
$_['settings'] = 'Settings';
$_['switch_text_enabled'] = 'Enabled';
$_['switch_text_disabled'] = 'Disabled';
$_['order_statuses'] = 'Order Statuses';

// Text
$_['text_payment'] = 'Payment';
$_['text_success'] = 'Success: You have modified SMEOnline account details';
$_['text_yes'] = 'Yes';
$_['text_no'] = 'No';
$_['text_purchase'] = 'Purchase';
$_['text_preauth_capture'] = 'Pre-Auth/Capture';
$_['text_capture'] = 'capture';
$_['text_refund'] = 'refund';
$_['text_approved'] = 'approved.';
$_['text_declined'] = 'declined.';
$_['text_receipt_number'] = 'Receipt number';
$_['text_field_name'] = 'Field Name';
// Entry
$_['entry_api_url'] = 'Base API URL';
$_['entry_username'] = 'API Username';
$_['entry_password'] = 'API Password';
$_['entry_merchant_number'] = 'Merchant Number';
$_['entry_merchant_number_note'] = 'Your merchant number as provided by the bank.';
$_['entry_payment_action'] = 'Payment Action';
$_['entry_payment_action_note'] = 'Choose between Purchase or Pre-Auth/Capture. Purchase transactions charge the card immediately. Pre-auths check if the card has available funds and places a hold on the card for the nominated amount. Captures complete a pre-auth, charging the card';
$_['entry_test_mode'] = 'Test Mode';
$_['entry_test_mode_note'] = 'If test mode is enabled, all transactions will be processed in test mode. You will not receive funds';
$_['entry_pending_status'] = 'Order Status for Pending';
$_['entry_payment_status'] = 'Order Status for Approved Payment';
$_['entry_preauth_status'] = 'Order Status for Pending Capture';
$_['entry_failed_status'] = 'Order Status for Declined Payment';
$_['entry_captured_status'] = 'Order Status for Approved Capture';
$_['entry_refunded_status'] = 'Order Status for Approved Refund';
$_['entry_geo_zone'] = 'Geo Zone';
$_['entry_total'] = 'Total';
$_['entry_total_note'] = 'The checkout total the order must reach before this payment method becomes active.';
$_['entry_sort_order'] = 'Sort order';

//Meza
$_['entry_meeza_active'] =  'Meeza Activation';
$_['entry_meeza_settings'] = 'Meeza Settings';
$_['entry_meeza_terminal_id'] = 'Terminal ID';
$_['entry_meeza_merchant_id'] = 'Merchant ID';
$_['entry_meeza_secret_key'] = 'Secret Key';


// Error
$_['error_permission'] = 'Warning: You do not have permission to modify payment SMEOnline';
$_['error_api_url'] = 'Base API URL Required';
$_['error_username'] = 'API Username Required';
$_['error_password'] = 'API Password Required';
$_['error_merchant_number'] = 'Merchant Number Required';
$_['error_decline_reason'] = 'Decline reason';
$_['error_request'] = 'Warning: Error processing your request';
$_['error_valid_amount'] = 'Warning: Invalid amount for refund';
$_['error_payment_captured'] = 'Warning: Payment has already completed';
$_['error_payment_refund'] = 'Warning: Payment has not already completed';
$_['error_payment_preauth'] = 'Warning: Pre-Authorization should be completed to perform this action';
$_['error_transaction_not_found'] = 'Warning: Original transaction was not found';
$_['error_maximum_amount_refund'] = 'Warning: Maximum amount available to refund is';
$_['error_full_refund'] = 'Warning: Payment already fully refunded';
$_['error_curl'] = 'PHP\'s curl extension is not installed. The SMEOnline module is required to enable php5-curl in your server.';
