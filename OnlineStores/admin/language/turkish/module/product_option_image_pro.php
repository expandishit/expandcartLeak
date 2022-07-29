<?php
// Heading
$_['heading_title']        = 'Ürün Seçeneği Resimleri'; 
$_['poip_module_name']     = 'Ürün Seçeneği Resimleri'; 

// Text
$_['text_module']          = 'Uygulamalar'; 
$_['text_success']         = 'Başarılı: "'. $_['heading_title']. '" Ayarları değiştirildi!'; 
$_['text_content_top']     = 'İçerik Başı'; 
$_['text_content_bottom']  = 'İçerik Alt'; 
$_['text_column_left']     = 'Sol Sütun'; 
$_['text_column_right']    = 'Sağ Sütun'; 

// Entry
$_['entry_settings']                   = 'Modül ayarları'; 
$_['entry_import']                     = 'İthalat'; 
$_['entry_import_description']         = '<b> Uyarı: tüm ürün seçeneği resimleri, içe aktarma başlamadan önce otomatik olarak silinecektir. </b> 
<br><br>Import file format: XLS. Import uses only first sheet for getting data.
<br>First table row must contain fields names (head): product_id, option_value_id, image (not product_option_id)
<br>Next table rows must contain related options data in accordance with fields names in first table row.';
$_['PHPExcelNotFound']                = '<a href="http://phpexcel.codeplex.com/" target="_blank">PHPExcel</a> bulunamadı. Dosya bulunamadı:';
$_['button_upload']		                 = 'Önemli dosya'; 
$_['button_upload_help']               = 'dosya seçildikten hemen sonra içe aktarma başlar'; 
$_['entry_server_response']            = 'Sunucu yanıtı'; 
$_['entry_import_result']              = 'İşlenmiş satırlar / resimler / atlandı'; 

$_['entry_layout']         = 'Yerleşim'; 
$_['entry_position']       = 'Durum'; 
$_['entry_status']         = 'Durum'; 
$_['entry_sort_order']     = 'Sıralama düzeni'; 
$_['entry_sort_order_short']     = 'çeşit'; 
$_['entry_settings_default']           = 'Genel Ayarlar'; 
$_['entry_settings_yes']           = 'Açık'; 
$_['entry_settings_no']           = 'Kapalı'; 


$_['entry_img_use_v0']           = 'Kapalı'; 
$_['entry_img_use_v1']           = 'Herkes için açık'; 
$_['entry_img_use_v2']           = 'Seçilen için açık'; 

$_['entry_img_first_v0']           = 'Her zaman oldugu gibi'; 
$_['entry_img_first_v1']           = 'İlk seçenek görüntüsüyle değiştir'; 
$_['entry_img_first_v2']           = 'Seçenek resimleri listesine ekle'; 

// Entry Module Settings
$_['entry_img_change']           = 'Seçenek seçiminde ana ürün resmini değiştir'; 
$_['entry_img_change_help']      = 'Seçenek değeri seçildiğinde ürün sayfasındaki ana ürün görselini değiştirin (ilk seçenek görselini kullanın)'; 
$_['entry_img_hover']            = 'Fare üzerine getirildiğinde ana ürün resmini değiştir'; 
$_['entry_img_hover_help']       = 'fareyle üzerine gelindiğinde ürün sayfasındaki ana ürün resmini değiştir'; 
$_['entry_img_main_to_additional']            = 'Ana görüntüden ek'; 
$_['entry_img_main_to_additional_help']       = 'ek görseller listesine ana ürün görselini ekle'; 

$_['entry_img_use']              = 'Ek resimler gibi seçenekler resimleri'; 
$_['entry_img_use_help']         = 'ürün sayfasındaki ek ürün resimleri listesinde seçenek resimlerini göster'; 

$_['entry_img_limit']            = 'Ek resimleri filtrele'; 
$_['entry_img_limit_help']       = 'ürün sayfasında ek ürün resimleri listesinde sadece seçilen seçenek değerleri için uygun görseller göster <br> 
works only with feature "'.$_['entry_img_use'].'"';
$_['entry_img_gal']              = 'Ürün resim galerisini filtrele'; 
$_['entry_img_gal_help']         = 'ürün resim galerisinde yalnızca ürün sayfasındaki ek ürün resimleri listesinden görünen resimleri göster <br> 
Özelliklerle kullanılması önerilir "'.$_['entry_img_use'].'" and "'.$_['entry_img_limit'].'"';

$_['entry_img_option']           = 'Aşağıdaki resimler seçeneği'; 
$_['entry_img_option_help']      = 'seçenek değeri seçildiğinde seçenek değeri seçim / radyo / onay kutusu altındaki seçenek değeri resimlerini göster'; 

$_['entry_img_category']         = 'Ürün listelerindeki seçenekler'; 
$_['entry_img_category_help']    = 'kategori sayfaları, marka sayfaları gibi ürün listelerinde ve ürün tanıtım kutularında küçük önizleme ile seçenek değerleri resimleri gösterin.'; 

$_['entry_img_first']            = 'Standart seçenek görüntüsü'; 
$_['entry_img_first_help']       = 'seçenekler sayfasına eklenen standart seçenek resimlerinin kullanımı (menü Katalog - Seçenekler - vb.)'; 
$_['entry_img_cart']             = 'Sepetteki seçenek değeri resmi'; 
$_['entry_img_cart_help']        = 'sepette seçilen seçenek değeri resmini göster'; 

$_['entry_show_settings']        = 'Mevcut ürün seçeneği ayarlarını göster'; 
$_['entry_hide_settings']        = 'Mevcut ürün seçeneği ayarlarını gizle'; 



// Error
$_['error_permission']     = 'Uyarı: Modülü değiştirme izniniz yok "'. $_['heading_title']. '"!'; 


$_['module_description']     = ''; 


$_['module_info']  = '"'. $_['heading_title']. '"'; 


?>