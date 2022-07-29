<?php
//==============================================================================
// Stripe Payment Gateway v230.4
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

$version = 'v230.4';

//------------------------------------------------------------------------------
// Heading
//------------------------------------------------------------------------------
$_['heading_title']						= 'Stripe Payment Gateway';
$_['text_stripe']						= '<a target="blank" href="https://stripe.com"><img src="https://stripe.com/img/logo.png" alt="Stripe" title="Stripe" /></a>';
$_['text_payment']                      = 'Payment';
$_['text_success']                      = 'Success You have modified settings!';

$_['settings'] = 'Settings';
$_['switch_text_enabled'] = 'Enabled';
$_['switch_text_disabled'] = 'Disabled';

//------------------------------------------------------------------------------
// Extension Settings
//------------------------------------------------------------------------------
$_['tab_extension_settings']			= 'Settings';
$_['heading_extension_settings']		= 'Settings';

$_['entry_status']						= 'Status';
$_['entry_sort_order']					= 'Sort Order';
$_['entry_title']						= 'Title';
$_['entry_button_text']					= 'Button Text';
$_['entry_button_class']				= 'Button Class';
$_['entry_button_styling']				= 'Button Styling';

// Payment Page Text
$_['heading_payment_page_text']			= 'Payment Page Text';

$_['entry_text_card_details']			= 'Card Details';
$_['entry_text_use_your_stored_card']	= 'Use Your Stored Card';
$_['entry_text_ending_in']				= 'ending in';
$_['entry_text_use_a_new_card']			= 'Use a New Card';
$_['entry_text_card_name']				= 'Name on Card';
$_['entry_text_card_number']			= 'Card Number';
$_['entry_text_card_type']				= 'Card Type';
$_['entry_text_card_expiry']			= 'Card Expiry (MM/YY)';
$_['entry_text_card_security']			= 'Card Security Code (CVC)';
$_['entry_text_store_card']				= 'Store Card for Future Use';
$_['entry_text_please_wait']			= 'Please Wait';
$_['entry_text_to_be_charged']			= 'To Be Charged Later: ';

// Errors
$_['heading_errors']					= 'Errors';

$_['entry_error_customer_required']		= 'Customer Required: ';
$_['entry_error_shipping_required']		= 'Shipping Required: ';
$_['entry_error_shipping_mismatch']		= 'Shipping Mismatch: ';

// Stripe Error Codes
$_['heading_stripe_error_codes']		= 'Stripe Error Codes';
$_['help_stripe_error_codes']			= 'Leave any of these fields blank to display Stripe\'s default error message for that error code. HTML is supported. Error messages are not displayed when using Stripe Checkout.';

$_['entry_error_card_declined']			= 'card_declined';
$_['entry_error_expired_card']			= 'expired_card';
$_['entry_error_incorrect_cvc']			= 'incorrect_cvc: ';
$_['entry_error_incorrect_number']		= 'incorrect_number';
$_['entry_error_incorrect_zip']			= 'incorrect_zip: ';
$_['entry_error_invalid_cvc']			= 'invalid_cvc';
$_['entry_error_invalid_expiry_month']	= 'invalid_expiry_month';
$_['entry_error_invalid_expiry_year']	= 'invalid_expiry_year';
$_['entry_error_invalid_number']		= 'invalid_number';
$_['entry_error_missing']				= 'missing: ';
$_['entry_error_processing_error']		= 'processing_error';

// Cards Page Text
$_['heading_cards_page_text']			= 'Cards Page Text';

$_['entry_cards_page_link']				= 'Cards Page Link: ';
$_['entry_cards_page_heading']			= 'Cards Page Heading: ';
$_['entry_cards_page_none']				= 'No Cards Message: ';
$_['entry_cards_page_default_card']		= 'Default Card Text: ';
$_['entry_cards_page_make_default']		= 'Make Default Button';
$_['entry_cards_page_delete']			= 'Delete Button';
$_['entry_cards_page_confirm']			= 'Delete Confirmation';
$_['entry_cards_page_add_card']			= 'Add New Card Button';
$_['entry_cards_page_card_address']		= 'Card Address: ';
$_['entry_cards_page_success']			= 'Success Message';

// Subscriptions Page Text
$_['heading_subscriptions_page_text']	= 'Subscriptions Page Text';

$_['entry_subscriptions_page_heading']	= 'Subscriptions Page Heading: ';
$_['entry_subscriptions_page_message']	= 'Default Card Message: ';
$_['entry_subscriptions_page_none']		= 'No Subscriptions Message: ';
$_['entry_subscriptions_page_trial']	= 'Trial End Text: ';
$_['entry_subscriptions_page_last']		= 'Last Charge Text: ';
$_['entry_subscriptions_page_next']		= 'Next Charge Text: ';
$_['entry_subscriptions_page_charge']	= 'Additional Charge Text: ';
$_['entry_subscriptions_page_cancel']	= 'Cancel Button';
$_['entry_subscriptions_page_confirm']	= 'Cancel Confirmation: ';

//------------------------------------------------------------------------------
// Order Statuses
//------------------------------------------------------------------------------
$_['tab_order_statuses']				= 'Order Statuses';
$_['heading_order_statuses']			= 'Order Statuses';
$_['help_order_statuses']				= 'Choose the order statuses set when a payment meets each condition. Note: to actually <strong>deny</strong> payments that fail CVC or Zip Checks, you need to enable the appropriate setting in your Stripe admin panel.<br />You can refund a payment by using the link provided in the History tab for the order.';

$_['entry_success_status_id']			= 'Successful Payment (Captured)';
$_['entry_authorize_status_id']			= 'Successful Payment (Authorized)';
$_['entry_error_status_id']				= 'Order Completion Error: ';
$_['entry_street_status_id']			= 'Street Check Failure';
$_['entry_zip_status_id']				= 'Zip Check Failure';
$_['entry_cvc_status_id']				= 'CVC Check Failure';
$_['entry_refund_status_id']			= 'Fully Refunded Payment';
$_['entry_partial_status_id']			= 'Partially Refunded Payment';

$_['text_ignore']						= '--- Ignore ---';

//------------------------------------------------------------------------------
// Restrictions
//------------------------------------------------------------------------------
$_['tab_restrictions']					= 'Restrictions';
$_['heading_restrictions']				= 'Restrictions';
$_['help_restrictions']					= 'Set the required cart total and select the eligible stores, geo zones, and customer groups for this payment method.';

$_['entry_min_total']					= 'Minimum Total: ';
$_['entry_max_total']					= 'Maximum Total: ';

$_['entry_stores']						= 'Store(s): ';

$_['entry_geo_zones']					= 'Geo Zone(s): ';
$_['text_everywhere_else']				= '<em>Everywhere Else</em>';

$_['entry_customer_groups']				= 'Customer Group(s): ';
$_['text_guests']						= '<em>Guests</em>';

// Currency Settings
$_['heading_currency_settings']			= 'Currency Settings';
$_['help_currency_settings']			= 'Select the currencies that Stripe will charge in, based on the order currency. <a target="_blank" href="https://support.stripe.com/questions/which-currencies-does-stripe-support">See which currencies your country supports</a>';
$_['entry_currencies']					= 'When Orders Are In [currency], Charge In';
$_['text_currency_disabled']			= '--- Disabled ---';

//------------------------------------------------------------------------------
// Stripe Settings
//------------------------------------------------------------------------------
$_['tab_stripe_settings']				= 'Stripe Settings';
$_['help_stripe_settings']				= 'API Keys can be found in your Stripe admin panel under Your Account > Account Settings > API Keys';

// API Keys
$_['heading_api_keys']					= 'API Keys';

$_['entry_test_secret_key']				= 'Test Secret Key';
$_['entry_test_publishable_key']		= 'Test Publishable Key';
$_['entry_live_secret_key']				= 'Live Secret Key';
$_['entry_live_publishable_key']		= 'Live Publishable Key';

// Stripe Settings
$_['heading_stripe_settings']			= 'Stripe Settings';

$_['entry_webhook_url']					= 'Webhook URL: ';

$_['entry_transaction_mode']			= 'Transaction Mode: ';
$_['text_test']							= 'Test';
$_['text_live']							= 'Live';

$_['entry_charge_mode']					= 'Charge Mode: ';
$_['text_authorize']					= 'Authorize';
$_['text_capture']						= 'Capture';
$_['text_fraud_authorize']				= 'Authorize if possibly fraudulent, Capture otherwise';

$_['entry_transaction_description']		= 'Transaction Description: ';

$_['entry_send_customer_data']			= 'Send Customer Data: ';
$_['text_never']						= 'Never';
$_['text_customers_choice']				= 'Customer&apos;s choice';
$_['text_always']						= 'Always';

$_['entry_allow_stored_cards']			= 'Allow Customers to Use Stored Cards: ';

// Apple Pay Settings
$_['heading_apple_pay_settings']		= 'Apple Pay Settings';

$_['entry_applepay']					= 'Enable Apple Pay: ';
$_['entry_applepay_label']				= 'Payment Sheet Label: ';
$_['entry_applepay_billing']			= 'Require Billing Address: ';

//------------------------------------------------------------------------------
// Stripe Checkout
//------------------------------------------------------------------------------
$_['tab_stripe_checkout']				= 'Stripe Checkout';
$_['heading_stripe_checkout']			= 'Stripe Checkout';
$_['help_stripe_checkout']				= 'Stripe Checkout uses Stripe&apos;s pop-up for displaying the credit card inputs, validation, and error handling. You can read more about it and view a demo at <a target="_blank" href="https://stripe.com/docs/checkout">https://stripe.com/docs/checkout</a><br />Note: Stripe Checkout does <strong>not</strong> allow customers to use the billing address entered in Expandcart.';

$_['entry_use_checkout']				= 'Use Stripe Checkout Pop-up: ';
$_['text_yes_for_desktop_devices']		= 'Yes, for desktop devices only';

$_['entry_checkout_remember_me']		= 'Enable "Remember Me" Option: ';

$_['entry_checkout_alipay']				= 'Enable Alipay: ';
$_['entry_checkout_bitcoin']			= 'Enable Bitcoin: ';

$_['entry_checkout_billing']			= 'Require Billing Address: ';

$_['entry_checkout_shipping']			= 'Require Shipping Address: ';

$_['entry_checkout_image']				= 'Pop-up Logo: ';
$_['text_browse']						= 'Browse';
$_['text_clear']						= 'Clear';
$_['text_image_manager']				= 'Image Manager';

$_['entry_checkout_title']		 		= 'Pop-up Title: ';

$_['entry_checkout_description']		= 'Pop-up Description: ';

$_['entry_checkout_button']				= 'Pop-up Button Text: ';

$_['entry_quick_checkout']				= 'Quick Checkout';

//------------------------------------------------------------------------------
// Subscription Products
//------------------------------------------------------------------------------
$_['tab_subscription_products']			= 'Subscription Products';
$_['help_subscription_products']		= '&bull; Subscription products will subscribe the customer to the associated Stripe plan when they are purchased. You can associate a product with a plan by entering the Stripe plan ID in the "Location" field for the product.<br />&bull; If the subscription is not set to be charged immediately (i.e. it has a trial period), the amount of the subscription will be taken off their original order, and a new order will be created when the subscription is actually charged to their card.<br />&bull; Any time Stripe charges the subscription in the future, a corresponding order will be created in Expandcart.<br />&bull; If you have a coupon set up in your Stripe account, you can map an Expandcart coupon to it by using the same coupon code and discount amount. When a customer purchases a subscription product and uses that coupon code, it will pass the code to Stripe to properly adjust the subscription charge.';

$_['heading_subscription_products']		= 'Subscription Product Settings';

$_['entry_subscriptions']				= 'Enable Subscription Products';
$_['entry_prevent_guests']				= 'Prevent Guests From Purchasing: ';
$_['entry_include_shipping']			= 'Include Shipping: ';
$_['entry_allow_customers_to_cancel']	= 'Allow Customers to Cancel Subscriptions: ';

// Current Subscription Products
$_['heading_current_subscriptions']		= 'Current Subscription Products';
$_['entry_current_subscriptions']		= 'Current Subscription Products: ';

$_['text_thead_Expandcart']				= 'Expandcart';
$_['text_thead_stripe']					= 'Stripe';
$_['text_product_name']					= 'Product Name';
$_['text_product_price']				= 'Product Price';
$_['text_location_plan_id']				= 'Location / Plan ID';
$_['text_plan_name']					= 'Plan Name';
$_['text_plan_interval']				= 'Plan Interval';
$_['text_plan_charge']					= 'Plan Charge';
$_['text_no_subscription_products']		= 'No Subscription Products';
$_['text_create_one_by_entering']		= 'Create one by entering the Stripe plan ID in the "Location" field for the product';

// Map Options to Subscriptions
$_['heading_map_options']				= 'Map Options to Subscriptions';
$_['help_map_options']					= 'If the customer has a product with the appropriate option name and option value in their cart, they will be subscribed to the corresponding plan ID. This will override the plan ID in the Location field for that product.';

$_['column_action']						= 'Action';
$_['column_option_name']				= 'Option Name';
$_['column_option_value']				= 'Option Value';
$_['column_plan_id']					= 'Plan ID';

$_['button_add_mapping']				= 'Add Mapping';

// Map Recurring Profiles to Subscriptions
$_['heading_map_recurring_profiles']	= 'Map Recurring Profiles to Subscriptions';
$_['help_map_recurring_profiles']		= 'If the customer has a product with the appropriate recurring profile name in their cart, they will be subscribed to the corresponding plan ID. This will override the plan ID in the Location field for that product. The subscription frequency and charge amount is determined by the Stripe plan, not the recurring profile settings, so make sure they match exactly.';

$_['column_profile_name']				= 'Recurring Profile Name';

//------------------------------------------------------------------------------
// Create a Charge
//------------------------------------------------------------------------------
$_['tab_create_a_charge']				= 'Create a Charge';

$_['help_charge_info']					= 'Enter the charge info below, then choose whether to generate a payment link, charge a customer\'s card, or enter a card manually.';
$_['heading_charge_info']				= 'Charge Info';

$_['entry_order_id']					= 'Order ID: ';
$_['entry_order_status']				= 'Order Status Change: ';
$_['entry_description']					= 'Description: ';
$_['entry_statement_descriptor']		= 'Statement Descriptor: ';
$_['entry_amount']						= 'Amount';

// Create Payment Link
$_['heading_create_payment_link']		= 'Create Payment Link';

$_['help_create_payment_link']			= '';
$_['button_create_payment_link']		= 'Create Payment Link';

// Use a Stored Card
$_['heading_use_a_stored_card']			= 'Use a Stored Card';

$_['entry_customer']					= 'Customer';
$_['placeholder_customer']				= 'Start typing a customer\'s name or e-mail address';
$_['text_customers_stored_cards_will']	= '(Customer\'s Default Card Will Appear Here)';
$_['button_create_charge']				= 'Create Charge';

// Use a New Card
$_['heading_use_a_new_card']			= 'Use a New Card';

//------------------------------------------------------------------------------
// Standard Text
//------------------------------------------------------------------------------
$_['copyright']							= '';

$_['standard_autosaving_enabled']		= 'Auto-Saving Enabled';
$_['standard_confirm']					= 'This operation cannot be undone. Continue?';
$_['standard_error']					= '<strong>Error:</strong> You do not have permission to modify ' . $_['heading_title'] . '!';
$_['standard_max_input_vars']			= '<strong>Warning:</strong> Please contact customer service 2.';
$_['standard_please_wait']				= 'Please wait...';
$_['standard_saved']					= 'Saved!';
$_['standard_saving']					= 'Saving...';
$_['standard_select']					= '--- Select ---';
$_['standard_success']					= 'Success!';
$_['standard_testing_mode']				= 'Your log is too large to open! Clear it first, then run your test again.';
$_['standard_vqmod']					= '<strong>Warning:</strong> Please contact customer service.';

$_['standard_module']					= 'Apps';
$_['standard_shipping']					= 'Shipping';
$_['standard_payment']					= 'Payments';
$_['standard_total']					= 'Order Totals';
$_['standard_feed']						= 'Feeds';

// Errors

$_['error_settings']                    = "Warning: please fill in Test Publishable Key, Test Secret Key, Live Publishable Key and Live Secret Key fields!!";
?>