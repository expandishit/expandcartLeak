<?php

$_['heading_title_esnad'] = 'Esnad';
$_['token'] = 'Token';

$_['general_settings'] = 'Settings';
$_['shipping_rates'] = 'Shipping Rates';
$_['client_information'] = 'Client Information';


// __________TEXT_______________

$_['text_shipment_settings'] = 'settings';
$_['text_shipment_details'] = 'Details';
$_['heading_title'] = 'Esnad';
$_['error_warning']   = 'Warning: Please make sure to review incorrect or required fields!';
$_['esnad_error_warning']   = 'Warning: Esnad API Error!';
$_['please_wait'] = 'please wait ...';
$_['text_all_states'] = 'All States';
$_['text_all_cities'] = 'All Cities';
$_['esnad_shipping_cost'] = 'Shipping Cost';
$_['text_all_zones'] = 'All Zones';
$_['text_tracking_shipment'] = 'Track Shipment';
$_['text_all_payment_methods'] = 'All Payment Methods';
$_['text_all_cod_payment_methods'] = 'All COD Payment Methods';
$_['text_all_weight_unit'] = 'Choose Weight Unit';
$_['esnad_success_message'] = 'Success: Order Created Successfully';
$_['text_esnad_status'] = 'Status';
$_['text_date'] = 'Date';
$_['text_description'] = 'Description';
$_['text_remarks'] = 'Notes';
$_['text_location'] = 'Location';


// __________ENTRY______________
$_['entry_environment'] = 'Test mode';

$_['entry_name'] = 'Name';

$_['entry_email'] = 'Email';

$_['entry_mobile'] = 'Mobile';
$_['entry_address'] = 'Address';

$_['entry_delivery_type'] = 'Delivery Type';
$_['entry_delivery_type_note'] =  'Delivery Type of Order';

$_['entry_goods_value'] = 'Goods Value';
$_['entry_goods_value_note'] = 'Order Goods Value (should be in receiver local currency)';

$_['entry_city'] = 'city';
$_['entry_city_note'] = 'city';

$_['entry_geo_zone'] = 'Geo Zone';

$_['entry_service'] = 'Service Type';

$_['entry_order_type'] = 'Order Type';

$_['entry_payment_method'] = 'Payment Method';
$_['entry_payment_method_note'] = 'Payment Method';


$_['esnad_entry_description'] = 'Description';
$_['esnad_entry_description_note'] = 'Description';

$_['entry_amount'] = 'Amount';
$_['entry_amount_note'] = 'Amount Must be in Saudi Riyal';

$_['entry_doller_value'] = 'The value of the dollar in relation to the SAR currency';
$_['entry_doller_value_note'] = 'This value will be used when calculating the customs value.';

$_['entry_declared_value_rate'] = 'Customs Declared Value Rate';
$_['entry_declared_value_rate_note'] = 'This percentage will be calculated on the total value of the Order and the result will be converted to the value in dollars';

$_['entry_country_code'] = 'Country Alpha 2 Code';
$_['entry_country_code_note'] = 'Customer Country code. It should be two digits’ country code';

$_['entry_pcs'] = 'Number of Pieces';
$_['entry_pcs_note'] = 'The Number of Pieces to be Shipped';

$_['entry_awb_number'] = 'Esnad Awb Number';
$_['entry_awb_number_note'] = 'Esnad give AWB number zone to client,such as 1001000000000 to 1001999999999';

// __________BUTTON______________

$_['button_create_shipment'] = 'create Shipment';
$_['button_print_label'] = 'Print Label';
$_['button_print_sticker'] = 'Print Sticker';
$_['button_return'] = 'return to order';
$_['button_track_shipment'] = 'track Shipment';
$_['button_cancel_shipment'] = 'cancel Shipment';

 //___________ERRORS___________
$_['error_esnad_entry_name'] = 'Please insert Name';
$_['error_esnad_entry_mobile'] = 'Please insert Mobile Number';
$_['error_esnad_entry_address'] = 'Address incorrect';
$_['error_esnad_entry_payment_method'] = 'Please Select Payment Method';
$_['error_esnad_entry_delivery_type'] = 'Delivery Type of Order is Required';
$_['error_esnad_entry_description'] = 'Please insert Description';
$_['error_esnad_entry_customer_code'] = 'Customer Code Required';
$_['error_esnad_entry_city'] = 'Please choose the city';
$_['error_esnad_entry_amount'] = 'The Field Of Amount Is Required';
$_['error_esnad_tracking']   = 'error while tracking, Please try again later ';
$_['error_esnad_entry_order_id']   = 'Invalid Order Id';
$_['error_esnad_entry_shipping_cost']   = 'Shipping Cost Required';
$_['error_esnad_entry_token']   = 'Token Required';
$_['error_esnad_entry_country'] = 'Country Alpha 2 Code Required And It should be two digits';
$_['error_esnad_entry_order_type'] = 'The Field Of Order Type Is Required';
$_['error_esnad_recipient_service'] = 'The Field Of Service Is Required';
$_['error_esnad_entry_service'] = 'The Field Of Service Is Required ';
$_['error_esnad_entry_pcs'] = 'The Field Of Number Of Pieces Is Required And Must Contain Only Numbers';
$_['error_esnad_awb_number'] = 'The Field Of awb Number Is Required ';
$_['error_esnad_entry_description'] = 'The Field Of Description Is Required ';
$_['error_esnad_entry_declared_value_rate'] = 'The Field Of Customs Declared Value Is Required ';
$_['error_esnad_entry_doller_value'] = 'The value of the dollar in relation to the SAR currency';



//___________Allowed Services___________

$_['service_exp']  = "Normal Order";
$_['service_ugt']  = "Urgent Order";

//___________Allowed Payment Methods___________

$_['paymentMethod_cod']  = "Cash on Delivery (COD)";
$_['paymentMethod_ppd']  = "Prepaid (PPD)";

//___________Allowed order types___________

$_['order_int']  = "International Client";
$_['order_dom']  = "Domestic Client";
$_['order_tob']  = "B2B Client";