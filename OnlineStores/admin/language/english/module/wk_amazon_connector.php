<?php
// Heading
$_['heading_title']   				    = 'Amazon Connector';

// Text
$_['text_apps']  				    = 'Apps';
$_['text_success']    				    = 'Success: You have modified Amazon connector module!';
$_['text_edit']       				    = 'Edit Amazon connector Module';
$_['text_default']    				    = 'Default Store';
$_['text_option1']    				    = 'Option 1 : Import all Amazon products (with or without variation)';
$_['text_option2']    				    = 'Option 2 : Import only those Amazon products which do not have any variation.';

// Entry Amazon
$_['entry_status']     				    = 'Status';
$_['entry_default_category']	    = 'Choose default category';
$_['entry_default_quantity']	    = 'Default Product Quantity';
$_['entry_default_weight']		    = 'Amazon Product Weight (in Gram)';
$_['entry_cron_create_product']   = 'Store Un-mapped Product to Amazon(through Cron Job)';
$_['entry_cron_update_product']   = 'Update Mapped Products (through Cron Job)';
$_['entry_default_store']			    = 'Default store for order sync';
$_['entry_order_status']			    = 'Amazon imported order status';
$_['entry_default_product_store']	= 'Default store for product';
$_['entry_variation_options']	    = 'Product Variation(Option) Choice Options';
$_['entry_update_imported']	      = 'Update Imported Products';
$_['entry_update_exported']	      = 'Update Exported Products';
$_['entry_price_rules']	          = 'Apply Price Rules for';
$_['entry_import_quantity_rule']  = 'Apply Quantity Rules for Import Product';
$_['entry_export_quantity_rule']  = 'Apply Quantity Rules for Export Product';

//panel
$_['panel_general_options']       = 'General Options';
$_['panel_order_options']	        = 'Order Options';
$_['panel_product_options']	      = 'Product Options';
$_['panel_real_time_setting']	    = 'Real Time Update Settings  ';

// price rules
$_['panel_price_rules']	          = 'Price/Quantity Rules Settings';

//help amazon
$_['help_default_category'] 	= 'Choose default store category for assigning amazon product.';
$_['help_default_quantity']		= 'Given Quantity will be Amazon/Store default product quantity, If product quantity is zero.';
$_['help_default_weight']			= 'This value will be use when amazon product doesn\'t contain the weight.';
$_['help_default_store']			= 'Select expandcart store for order sync.';
$_['help_order_status']				= 'Set default order status for order which imported from amazon';
$_['help_default_product_store']	= 'Selected store will be assigned to all amazon products by default';
$_['help_variation_options']	= 'You can select option for Product with/without variation/option.';
$_['info_option']             = 'Option 1 : In this case, A new product will always created in expandcart for each amazon product whether that product has variation/option OR not.<br><br>
Option 2 : In this case, Products will import only those have no variation/option. Products with variation/option will not import. In order import case, if order\'s product has variations(options) then product and order related to
that product both will not import..';
$_['entry_update_imported']	  = 'Update imported product on Amazon Store!';
$_['entry_update_exported']	  = 'Update exported product on Amazon Store!';
$_['help_update_imported']	  = 'Update imported product on Amazon store, if we do any update on an expandcart store.';
$_['help_update_exported']	  = 'Update exported product on Amazon store, if we do any update on an expandcart store.';
$_['help_cron_create_product']= 'with the help of this option, you can export the opencart newly added products to first amazon seller account only.';
$_['help_cron_update_product']= 'If enabled, then you can update the product\'s price and quantity fields according to their sync source.';
$_['help_price_rules']       = 'If export is selected, then price rule will applied on the exported products otherwise price rule will applied to imported products .';

//placeholder
$_['placeholder_quantity']	  = 'Enter default product quantity..';
$_['placeholder_weight']			= 'Enter default product weight (in Gram)..';
$_['text_import']			= 'Import';
$_['text_export']			= 'Export';

$_['info_update_imported']    = 'Note: If imported product will update on Store, then only Quantity and Price of that product will automatically update on Amazon Store';
$_['info_update_exported']    = 'Note: If exported product will update on Store, then only Quantity and Price of that product will automatically update on Amazon Store';
$_['info_price_rules']        = 'Note: If export is selected then price rule will apllicable for the exported products and if Import is selected then price rule will apllicable for the Imported products';
$_['info_import_quantity_rule']        = 'Note: If enable then Quantity rule will apllicable for the Imported products.';
$_['info_export_quantity_rule']        = 'Note: If enable then Quantity rule will apllicable for the exported products.';
// Error
$_['error_permission'] = 'Warning: You do not have permission to modify Amazon connector module!';
$_['error_quantity'] = 'Warning:Default Product Quantity should be positive number.';
$_['error_weight'] = 'Warning: Amazon Product Weight should be positive number.';
