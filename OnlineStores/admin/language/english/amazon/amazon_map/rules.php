<?php

$_['heading_title']				    = 'Amazon Price/Quantity Rules List';
$_['heading_title_add']			  = 'Add Price/Quantity Rules';
$_['heading_title_edit']		  = 'Edit Price/Quantity Rules';
$_['heading_title_csv']		    = 'Add Rules with CSV file';

//text
$_['text_add']					      = 'Add Price/Quantity Rule';
$_['text_price_type_fixed']	  = 'Fixed Price';
$_['text_price_type_percent'] = 'Percent Price';
$_['text_price_type_inc']	    = '+ (Increment)';
$_['text_price_type_dec']     = '- (Decrement)';
$_['entry_rule_type']         = 'Rule Type';

$_['text_store']					    = 'Store';
$_['btn_add_rule']	          = 'Add Price Rule';
$_['btn_add_csv']	            = 'Upload CSV';
$_['btn_add_csv_tool']	      = 'Add bulk price rule list from CSV upload file';
$_['text_confirm']				    = 'Are you sure to Delete ?';
$_['text_success']				    = 'Success: Price Rule added successfully!';
$_['text_success_add']				= 'Success: Price Rule added successfully!';
$_['text_success_edit']				= 'Success: Price Rule Updated successfully!';
$_['text_success_del']				= 'Success: Price Rule deleted successfully!';
$_['text_list']				        = 'Price/Quantity Rules List';

//entry
$_['entry_price_from']        = 'Price Rule From';
$_['entry_price_to']          = 'Price Rule To';
$_['entry_price_type']        = 'Opration Type';
$_['entry_price_value']       = 'Amount Value';
$_['entry_price_status']      = 'Price Rule Status';
$_['entry_status']      = 'Price Rule Status';
$_['entry_price_store']       = 'Store';
$_['entry_price_opration']    = 'Opration';
$_['entry_csv']               = 'Upload CSV File';
$_['help_csv']                = 'Enter CSV File which contains the proper format as same as demo.';
//column
$_['column_price_from']        = 'Price Rule From';
$_['column_price_to']          = 'Price Rule To';
$_['column_price_type']        = 'Opration Type';
$_['column_price_value']       = 'Amount Value';
$_['column_price_status']      = 'Price Rule Status';
$_['column_status']            = 'Price Rule Status';
$_['column_price_store']       = 'Store';
$_['column_price_opration']    = 'Opration';
$_['column_action']            = 'Action';

//placeholder
$_['placeholder_price_from']  = 'Enter the minimum price for the price rule';
$_['placeholder_price_to']    = 'Enter maximum price for the price rule';
$_['placeholder_price_value'] = 'Enter the Amount Value';


//help
$_['help_price_from']         = 'select the minimum price for the price rule to be active from';
$_['help_price_to']           = 'set the maximum price for the price rule to get inactive';
$_['help_price_type']         = 'Select the operation type as fixed or percentage(Only apply for Price)';
$_['help_price_value']        = 'Enter the Amount Value';
$_['help_price_status']       = 'Set the Price Rule Status';
$_['help_price_store']        = 'Select the store';
$_['help_price_opration']     = 'Select the operation as + (Increment) or - (Decrement)';
$_['help_amazon_store_name']  = 'Enter amazon account store name.';
$_['help_rule_type']          = 'Select Quantity or Price.';
//error
$_['error_field_required']    = 'Please fill the form carefully.';
$_['error_price_to']          = 'Price rule to field is required and must contains value greater than 0.';
$_['error_price_from']        = 'Price rule from field is required and must contains value greater than 0.';
$_['error_price_value']       = 'Amount value field is required and must contains value greater than 0.';
$_['error_range_price_from']  = 'Price range is already used, Please choose a diffrent price which value does not belongs in the range of added price rules.';
$_['error_range_wide_range']  = 'Entered price range for Price from and Price To contains other rule list ranges, So please enter another range for both fileds.';
$_['error_numeric']           = 'Please enter only numeric values.';
$_['error_empty_file']        = 'Please upload a csv file to add Price Rules.';
$_['error_file_type']         = 'Incorrect csv file. Please upload a correct file with csv extension.';
$_['error_csv_keys']          = 'You csv file contains incorrect column names, Please correct them as same as demo csv file.';
$_['error_non_zero']          = 'You csv file contains value 0 for Non zero column values, Please correct them as same as demo csv file.';
$_['error_zero']              = 'this field does not valid doe value 0 , Please correct enter a non zero value.';
$_['error_same_value']        = 'You csv file contains price_to and price_from same value or price_to less than price_from value, Please correct them as same as demo csv file.';
$_['error_range_price_to']     = 'Entered price range for Price from and Price To contains other rule list ranges, So please enter another range for both fileds.';
$_['error_equal']             = 'Price from value must be less than price to value, Please correct both the fields value.';
$_['entry_info']  		        = 'Uploading CSV file allows you to import multiple price rules data from a single file in <a href="http://en.wikipedia.org/wiki/Comma-separated_values" target="_blank">CSV</a> format. You can <a href="demo/demo.zip">download sample</a> file from here.';
