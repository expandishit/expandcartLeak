<?php
$localizationDic = array();

$localizationQuery=mysqli_query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `group` = 'localization'");

if ($localizationQuery) {
    while ($localizationRow = mysqli_fetch_assoc($localizationQuery))
    {
        $localizationDic[$localizationRow['key']]=$localizationRow['value'];
    }
}

// Heading 
$_['heading_title'] = 'Latest';

// Text
$_['text_latest']  = 'Latest'; 
$_['text_mostviewed']  = 'Most Viewed'; 
$_['text_featured']  = 'Featured'; 
$_['text_bestseller']  = 'Best Seller'; 
$_['text_special']  = 'Special'; 
$_['label_btn_view_more']  = 'View More';
$_['text_bidnow'] = 'Bid Now';

$_['text_sale'] = 'Solde';
$_['text_sale_detail'] = '%s%% OFF';
$_['button_req_quote']      = isset($localizationDic['button_req_quote']) ? $localizationDic['button_req_quote'] : 'Call for Quote';
?>
