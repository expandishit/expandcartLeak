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
$_['heading_title'] = 'الأحدث';

// Text
$_['text_latest']  = 'الأحدث';
$_['text_mostviewed']  = 'الأكثر تصفحا'; 
$_['text_featured']  = 'المنتجات المميزة';
$_['text_bestseller']  = 'الأكثر مبيعا';
$_['text_special']  = 'العروض الخاصة';
$_['text_bidnow'] = 'المزايدة الأن';

$_['text_sale'] = 'تنزيلات';
$_['text_sale_detail'] = '%s%% خصم';
$_['button_req_quote']      = isset($localizationDic['button_req_quote_ar']) ? $localizationDic['button_req_quote_ar'] : "إتصل للسعر";
?>
