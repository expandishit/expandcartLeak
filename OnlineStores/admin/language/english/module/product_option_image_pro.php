<?php
// Heading
$_['heading_title']       = 'Product Option Images';
$_['poip_module_name']    = 'Product Option Images';

// Text
$_['text_module']         = 'Apps';
$_['text_success']        = 'Success: "'.$_['heading_title'] .'" settings changed!';
$_['text_content_top']    = 'Content Top';
$_['text_content_bottom'] = 'Content Bottom';
$_['text_column_left']    = 'Column Left';
$_['text_column_right']   = 'Column Right';

// Entry
$_['entry_settings']                  = 'Module settings';
$_['entry_import']                    = 'Import';
$_['entry_import_description']        = '<b>Notice: all product options images will be automatically deleted before import starts.</b>
<br><br>Import file format: XLS. Import uses only first sheet for getting data.
<br>First table row must contain fields names (head): product_id, option_value_id, image (not product_option_id)
<br>Next table rows must contain related options data in accordance with fields names in first table row.';
$_['PHPExcelNotFound']                = '<a href="http://phpexcel.codeplex.com/" target="_blank">PHPExcel</a> not found. Not found file: ';
$_['button_upload']		                = 'Import file';
$_['button_upload_help']              = 'import starts immediately after selecting the file';
$_['entry_server_response']           = 'Server answer';
$_['entry_import_result']             = 'Processed rows/images/skipped';

$_['entry_layout']        = 'Layout';
$_['entry_position']      = 'Position';
$_['entry_status']        = 'Status';
$_['entry_sort_order']    = 'Sort Order';
$_['entry_sort_order_short']    = 'sort';
$_['entry_settings_default']          = 'global settings';
$_['entry_settings_yes']          = 'On';
$_['entry_settings_no']          = 'Off';


$_['entry_img_use_v0']          = 'Off';
$_['entry_img_use_v1']          = 'On for all';
$_['entry_img_use_v2']          = 'On for selected';

$_['entry_img_first_v0']          = 'As usual';
$_['entry_img_first_v1']          = 'Replace with first option image';
$_['entry_img_first_v2']          = 'Add to option images list';

// Entry Module Settings
$_['entry_img_change']          = 'Change main product image on option select';
$_['entry_img_change_help']     = 'change main product image on product page when option value is selected (use first option image)';
$_['entry_img_hover']           = 'Change main product image on mouse over';
$_['entry_img_hover_help']      = 'change main product image on product page when mouse hover additional image';
$_['entry_img_main_to_additional']           = 'Main image to additional';
$_['entry_img_main_to_additional_help']      = 'add main product image to additional images list';

$_['entry_img_use']             = 'Options images like additional images';
$_['entry_img_use_help']        = 'show options images in additional product images list on product page';

$_['entry_img_limit']           = 'Filter additional images';
$_['entry_img_limit_help']      = 'show in additional product images list on product page only images suitable for selected options values<br>
works only with feature "'.$_['entry_img_use'].'"';
$_['entry_img_gal']             = 'Filter product image gallery';
$_['entry_img_gal_help']        = 'show in product image gallery only visible images from additional product images list on product page <br>
Recommended to be use with features "'.$_['entry_img_use'].'" and "'.$_['entry_img_limit'].'"';

$_['entry_img_option']          = 'Images below option';
$_['entry_img_option_help']     = 'show option value images below option value select/radio/checkbox when option value is selected';

$_['entry_img_category']        = 'Options in products lists';
$_['entry_img_category_help']   = 'show option values images with small preview in product lists like categories pages, brands pages, and in product promotion boxes.';

$_['entry_img_first']           = 'Standard option image';
$_['entry_img_first_help']      = 'usage of standard option images added at options page (menu Catalog - Options - etc)';
$_['entry_img_cart']            = 'Option value image in cart';
$_['entry_img_cart_help']       = 'show selected option value image in cart';

$_['entry_show_settings']       = 'Show current product option settings';
$_['entry_hide_settings']       = 'Hide current product option settings';



// Error
$_['error_permission']    = 'Warning: You do not have permission to modify module "'.$_['heading_title'] .'"!';


$_['module_description']    = '';


$_['module_info'] = '"'.$_['heading_title'] .'"';


?>