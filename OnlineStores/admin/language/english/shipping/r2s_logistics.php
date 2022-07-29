<?php

$_['heading_title_r2s_logistics'] = 'R2s Logistics';
$_['secure_key'] = 'Secure Key';

$_['general_settings'] = 'Settings';
$_['shipping_rates'] = 'Shipping Rates';
$_['client_information'] = 'Client Information';


// __________TEXT_______________

$_['text_shipment_settings'] = 'settings';
$_['text_shipment_details'] = 'Details';
$_['heading_title'] = 'R2s Logistics';
$_['error_warning']   = 'Warning: Please make sure to review incorrect or required fields!';
$_['r2s_logistics_error_warning']   = 'Warning: R2s Logistics API Error!';
$_['please_wait'] = 'please wait ...';
$_['text_all_states'] = 'All States';
$_['text_all_cities'] = 'All Cities';
$_['r2s_logistics_shipping_cost'] = 'Shipping Cost';
$_['text_all_zones'] = 'All Zones';
$_['text_tracking_shipment'] = 'Track Shipment';
$_['text_all_payment_methods'] = 'All Payment Methods';
$_['text_all_cod_payment_methods'] = 'All COD Payment Methods';
$_['text_all_weight_unit'] = 'Choose Weight Unit';
$_['r2s_logistics_success_message'] = 'Success: Order Created Successfully';
$_['text_location'] = 'Location';
$_['text_date'] = 'Date';
$_['text_updated_by'] = 'Updated By';
$_['text_waybillStatus'] = 'Waybill Status';


// __________ENTRY______________
$_['entry_environment'] = 'Test mode';

$_['entry_name'] = 'Name';

$_['entry_email'] = 'Email';

$_['entry_mobile'] = 'Mobile';
$_['entry_address'] = 'Address';

$_['entry_customer_code'] = 'Customer Code';
$_['entry_customer_code_note'] = 'Consignor code as available in the ERP, Consignor code can be 00000 as well for unregistered consignor';

$_['entry_consignee_code'] = 'Consignee Code';
$_['entry_consignee_code_note'] = 'Consignee code as available in the ERP, Consignee code can be 00000 as well for unregistered Consignee';

$_['entry_client_code'] = 'Client Code';
$_['entry_client_code_note'] = 'Client code as available in the ERP';


$_['entry_delivery_type'] = 'Delivery Type';
$_['entry_delivery_type_note'] =  'Delivery Type of Order';

$_['entry_goods_value'] = 'Goods Value';
$_['entry_goods_value_note'] = 'Order Goods Value (should be in receiver local currency)';

$_['entry_city'] = 'city';
$_['entry_city_note'] = 'city';

$_['entry_geo_zone'] = 'Geo Zone';

$_['entry_service'] = 'Service Type';

$_['entry_currency'] = 'Currency';
$_['entry_currency_note'] = '3-digit Currency Code (USD, GBP, etc)';

$_['entry_payment_method'] = 'Payment Method';
$_['entry_payment_method_note'] = 'Payment Method';

$_['entry_package_number'] = 'Number of Packages';
$_['entry_package_number_note'] = 'Number of Packages';

$_['entry_actual_weight'] = 'Actual Weight';
$_['entry_actual_weight_note'] = 'Actual Weight';

$_['entry_weight_unit'] = 'Weight Unit';

$_['r2s_logistics_entry_description'] = 'Description';
$_['r2s_logistics_entry_description_note'] = 'Description';

$_['entry_amount'] = 'Amount';
$_['entry_amount_note'] = 'Add Amount In case of Cash On Delivery';

$_['entry_country_code'] = 'Country Alpha 2 Code';
$_['entry_country_code_note'] = 'Customer Country code. It should be two digits’ country code';

$_['entry_state'] = 'State';

$_['entry_reference_number'] = 'Reference Number';

$_['entry_waybill_number'] = 'Waybill Number';

$_['entry_cod_payment_method'] = 'COD Payment Method';
$_['entry_cod_payment_method_note'] = 'Payment Method In case of Cash On Delivery It Will Be Required If You Add Value On Amount Field';

$_['entry_hub'] = 'Hub';

$_['entry_rate']       = 'Rates';
$_['entry_rate_help']       = 'Example: 5:10.00,7:12.00 Weight:Cost,Weight:Cost, etc..';

$_['entry_general_price']       = 'General Rate';
$_['entry_general_price_note']       = 'This Price Will Be Used In Case Of Zone Not Found';

$_['entry_contact_r2s']       = 'Contact R2s Logistics';

// __________BUTTON______________

$_['button_create_shipment'] = 'create Shipment';
$_['button_print_label'] = 'Print Label';
$_['button_print_sticker'] = 'Print Sticker';
$_['button_return'] = 'return to order';
$_['button_track_shipment'] = 'track Shipment';
$_['button_cancel_shipment'] = 'cancel Shipment';

 //___________ERRORS___________
$_['error_r2s_logistics_entry_name'] = 'Please insert Name';
$_['error_r2s_logistics_entry_email'] = 'Please insert Email';
$_['error_r2s_logistics_entry_address'] = 'Address incorrect';
$_['error_r2s_logistics_entry_payment_method'] = 'Please Select Payment Method';
$_['error_r2s_logistics_entry_delivery_type'] = 'Delivery Type of Order is Required';
$_['error_r2s_logistics_entry_description'] = 'Please insert Description';
$_['error_r2s_logistics_entry_customer_code'] = 'Customer Code Required';
$_['error_r2s_logistics_entry_city'] = 'Please choose the city';
$_['error_r2s_logistics_amount'] = 'Amount Value Must be 0 In case of Prepaied';
$_['error_r2s_logistics_tracking']   = 'error while tracking, Please try again later ';
$_['error_secure_key_required']   = 'Secure Key Required';
$_['error_r2s_logistics_entry_order_id']   = 'Invalid Order Id';
$_['error_r2s_logistics_entry_shipping_cost']   = 'Shipping Cost Required';

$_['error_r2s_logistics_entry_consignee_code'] = 'Consignee Code Required';
$_['error_r2s_logistics_entry_client_code'] = 'Client Code Required';
$_['error_r2s_logistics_entry_country'] = 'Country Alpha 2 Code Required And It should be two digits';
$_['error_r2s_logistics_recipient_state'] = 'The Field Of State Is Required';
$_['error_r2s_logistics_recipient_service'] = 'The Field Of Service Is Required';
$_['error_r2s_logistics_cod_payment_method'] = 'The Field Of COD Payment Method Is Required In Case Of Cash On Delivery';
$_['error_r2s_logistics_entry_package_number'] = 'The Field Of Number Of Packages Is Required And Must Contain Only Numbers';
$_['error_r2s_logistics_entry_actual_weight'] = 'The Field Of Actual Weight Is Required';
$_['error_r2s_logistics_entry_weight_unit'] = 'The Field Of Weight Unit Is Required';
$_['error_r2s_weight_rate_class_id_required'] = 'General Rate';



//___________Allowed Services___________

$_['service_pud']  = "Pickup & Delivery";
$_['service_paycol']  = "Payment Collection";
$_['service_refund']  = "Refund";
$_['service_exchange']  = "Exchange";
$_['service_dropd']  = "Drop off & Delivery";
$_['service_r2s']  = "Pick up Drop Off Service";
$_['service_intexp']  = "International Express";
$_['service_xpud']  = "SameDay Pickup & Delivery";

//___________Allowed Payment Methods___________

$_['paymentMethod_fod']  = "Freight on Delivery (FOD)";
$_['paymentMethod_tbb']  = "To Be Billed (TBB)";

//___________Allowed COD Payment Methods___________

$_['codPaymentMethods_cash']  = "Cash";
$_['codPaymentMethods_cheque']  = "Cheque";
$_['codPaymentMethods_paymob']  = "PayMob";
$_['codPaymentMethods_mpesa']  = "MPesa";

//___________Allowed weight Units___________

$_['weightUnits_gram']  = "Gram";
$_['weightUnits_kilogram']  = "Kilogram";
$_['weightUnits_tonne']  = "Tonne";
$_['weightUnits_pound']  = "Pound";