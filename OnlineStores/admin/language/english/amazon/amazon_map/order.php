<?php

$_['heading_title_import'] 			    = 'Import Order From Amazon';
$_['heading_title'] 				        = 'Order Synchronization';
$_['heading_import_order'] 			    = 'Sync Amazon Store Order By Order-Id';


$_['info_about_import_tab'] 	      = 'Information About Order Tab';
$_['sync_order_tab'] 				        = 'Synchronize Amazon Orders';
$_['info_report_id'] 				        = 'Before synchronizing amazon orders, you must generate a Report-Id.';
$_['info_order_id_sync']            = 'Note: If ASIN Product is already synchronized, then your product will update!';

$_['text_tab_info1']				        = 'This tab import amazon orders to Store.';
$_['text_tab_info2']				        = 'Before importing amazon orders, If order\'s product is not synced to opencart store, then it will import automatically.';
$_['text_tab_info3']				        = 'Only Shipped, Unshipped and Partially Shipped orders will import.';
$_['text_order_id']                 = 'Amazon Order Id -';
$_['text_order_list']               = 'Imported Amazon Order List';
$_['text_confirm']                  = 'Comfirm: Do you want to delete Order?';
$_['text_success_delete']           = 'Success: Amazon Order(Id- %s) map entry deleted successfully for Order-Id #';
$_['text_currently_importorder']    = 'Currently, Amazon Order Import processing...';

$_['entry_order_response']          = 'Order Synchronization Result';
$_['entry_order_details']           = 'Enter Order Details';
$_['entry_order_from']              = 'Amazon Order From';
$_['entry_order_to']                = 'Amazon Order To';
$_['entry_order_maximum']           = 'Maximum Record';
$_['entry_order_id']                = 'Enter amazon order Id here...';
$_['entry_s_no']                    = 'S.No.';
$_['entry_amazon_order_id']         = 'Amazon Order-Id';
$_['entry_order_status']            = 'Order Status';
$_['entry_buy_date']                = 'Order Date';


$_['column_map_id']                 = 'Map Id';
$_['column_oc_order_id']            = 'Order-Id';
$_['column_amazon_id']              = 'Amazon Order-Id';
$_['column_buyer_name']             = 'Buyer Name';
$_['column_buyer_email']            = 'Buyer Email';
$_['column_order_total']            = 'Order Total';
$_['column_amazon_order_status']    = 'Amazon Order Status';
$_['column_action']                 = 'Action';
$_['column_created_date']           = 'Date Added';


$_['placeholder_order_from']        = 'Date From';
$_['placeholder_order_to']          = 'Date To';

$_['button_import'] 					      = 'Import Order';
$_['button_import_order'] 					= 'Import Order From Amazon';
$_['button_create_order'] 					= 'Create imported Amazon Order';
$_['button_back'] 					        = 'Back To Imported Order List';
$_['button_report_id']				      = 'Generate Report Id Before Sync Orders';
$_['button_import_order']			      = 'Import Amazon Store Orders';
$_['button_import_order_by_id']	    = 'Import Amazon Store Orders By Order-Id';
$_['button_close']   					      = 'Close';
$_['button_delete']   				      = 'Delete';
$_['button_delete_info'] 			      = 'Delete Order Mapped Entry';
$_['button_view']                   = 'View';
$_['button_view_info']              = 'View Order Details';
$_['button_filter_order']           = ' Filter Order';
$_['button_clear_order']            = ' Clear';



$_['error_not_referesh']			      = 'Warning : Do not refresh/back the page until the process is completed!';
$_['error_fill_from_date']			    = 'Warning : Fill Date-From to fetch the amazon order list!';
$_['error_fill_from_to']			      = 'Warning : Fill Date-To to fetch the amazon order list!';
$_['error_date_from']			          = 'Warning : Invalid Date From, please check for Date-From!';
$_['error_date_to']			            = 'Warning : Invalid Date To, please check for Date-To!';
$_['error_invalid_date']            = 'Warning : Invalid date selection, Date-From can\'t be greater than to Date-To!';
$_['error_maximum_order']           = 'Warning : Maximum record can\'t be greater than 5 digits!';
$_['error_maximum_invalid']         = 'Warning : Invalid Maximum record, use only numeric digit!';
$_['error_lessthan_date']           = 'Warning : Date-From / Date-To should not be greater than current Date!';
$_['error_no_account_details'] 	    = 'Warning: Invalid Account details, Please check for Amazon module configration details!';
$_['error_no_order_found']			    = 'Warning: No order found to import to the store!';
$_['error_order_required']			    = 'Warning: Order Id is required to import, please provide correct order id!';
$_['error_order_status']			      = 'Warning: Amazon Order Id-<b> %s </b>failed to import because of order status, You can import only Shipped, Unshipped and Partially Shipped orders!';
$_['error_customer_notfound']			  = 'Warning: Customer details is missing for Amazon Order Id-<b> %s </b>!';
$_['error_no_product_found']			  = 'Warning: No product found for Amazon Order Id-<b> %s </b>!';
$_['error_already_map']			        = 'Warning: Amazon Order Id-<b> %s </b>is already mapped with opencart order Id - ';
$_['error_order_combinat_product']	= 'Warning: Amazon Order Id-<b> %s </b>failed to mapped with opencart order because Amazon order contains some product with combination/variation/option!';
$_['error_order_delete']	          = 'Warning: No entry found regarding map Id:<b> %s </b>!';
$_['error_wrong_selection']			    = 'Warning: Something went wrong, refresh the page and try again!';
