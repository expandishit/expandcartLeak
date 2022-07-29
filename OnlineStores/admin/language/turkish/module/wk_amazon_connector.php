<?php
// Heading
$_['heading_title']   				     = 'Amazon Bağlayıcısı'; 

// Text
$_['text_apps']  				     = 'Uygulamalar'; 
$_['text_success']    				     = 'Başarı: Amazon bağlayıcı modülünü değiştirdiniz!'; 
$_['text_edit']       				     = 'Amazon Bağlayıcı Modülünü Düzenle'; 
$_['text_default']    				     = 'Varsayılan Mağaza'; 
$_['text_option1']    				     = 'Seçenek 1: Tüm Amazon ürünlerini (varyasyonlu veya varyasyonsuz) içe aktarın'; 
$_['text_option2']    				     = 'Seçenek 2: Yalnızca varyasyonu olmayan Amazon ürünlerini içe aktarın.'; 

// Entry Amazon
$_['entry_status']     				     = 'Durum'; 
$_['entry_default_category']	     = 'Varsayılan kategoriyi seçin'; 
$_['entry_default_quantity']	     = 'Varsayılan Ürün Miktarı'; 
$_['entry_default_weight']		     = 'Amazon Ürün Ağırlığı (Gram olarak)'; 
$_['entry_cron_create_product']    = 'Eşlenmemiş Ürünü Amazon\'da Depola (Cron Job aracılığıyla)'; 
$_['entry_cron_update_product']    = 'Eşlenen Ürünleri Güncelle (Cron Job aracılığıyla)'; 
$_['entry_default_store']			     = 'Sipariş senkronizasyonu için varsayılan mağaza'; 
$_['entry_order_status']			     = 'Amazon ithal sipariş durumu'; 
$_['entry_default_product_store']	 = 'Ürün için varsayılan mağaza'; 
$_['entry_variation_options']	     = 'Ürün Varyasyonu (Opsiyon) Seçim Seçenekleri'; 
$_['entry_update_imported']	       = 'İçe Aktarılan Ürünleri Güncelle'; 
$_['entry_update_exported']	       = 'İhraç Edilen Ürünleri Güncelle'; 
$_['entry_price_rules']	           = 'Fiyat Kurallarını Uygula'; 
$_['entry_import_quantity_rule']   = 'İthal Ürün İçin Miktar Kurallarını Uygulayın'; 
$_['entry_export_quantity_rule']   = 'İhraç Edilen Ürün İçin Miktar Kurallarını Uygulayın'; 

//panel
$_['panel_general_options']        = 'Genel seçenekler'; 
$_['panel_order_options']	         = 'Sipariş Seçenekleri'; 
$_['panel_product_options']	       = 'Ürün Seçenekleri'; 
$_['panel_real_time_setting']	     = 'Gerçek Zamanlı Güncelleme Ayarları'; 

// price rules
$_['panel_price_rules']	           = 'Fiyat / Miktar Kuralları Ayarları'; 

//help amazon
$_['help_default_category'] 	 = 'Amazon ürününü atamak için varsayılan mağaza kategorisini seçin.'; 
$_['help_default_quantity']		 = 'Verilen Miktar, ürün miktarı sıfır ise Amazon / Mağaza varsayılan ürün miktarı olacaktır.'; 
$_['help_default_weight']			 = 'Bu değer, amazon ürünü ağırlığı içermediğinde kullanılacaktır.'; 
$_['help_default_store']			 = 'Sipariş senkronizasyonu için expandcart mağazasını seçin.'; 
$_['help_order_status']				 = 'Amazon\'dan ithal edilen sipariş için varsayılan sipariş durumunu ayarla'; 
$_['help_default_product_store']	 = 'Seçilen mağaza varsayılan olarak tüm Amazon ürünlerine atanacaktır'; 
$_['help_variation_options']	 = 'Varyasyonlu / varyasyonsuz / opsiyonsuz ürün için opsiyon seçebilirsiniz.'; 
$_['info_option']              = '1. Seçenek: Bu durumda, ürün varyasyonu / seçeneği olsun veya olmasın, her bir amazon ürünü için her zaman genişleme kartında yeni bir ürün oluşturulur. <br> <br> 
Option 2 : In this case, Products will import only those have no variation/option. Products with variation/option will not import. In order import case, if order\'s product has variations(options) then product and order related to
that product both will not import..';
$_['entry_update_imported']	   = 'Amazon Store\'da içe aktarılan ürünü güncelleyin!'; 
$_['entry_update_exported']	   = 'Amazon Store\'da ihraç edilen ürünü güncelleyin!'; 
$_['help_update_imported']	   = 'Bir expandcart mağazasında herhangi bir güncelleme yaparsak, Amazon mağazasında içe aktarılan ürünü güncelleyin.'; 
$_['help_update_exported']	   = 'Bir expandcart mağazasında herhangi bir güncelleme yaparsak, Amazon mağazasında dışa aktarılan ürünü güncelleyin.'; 
$_['help_cron_create_product'] = 'bu seçeneğin yardımıyla, yeni eklenen ürünleri sadece ilk amazon satıcı hesabına aktarabilirsiniz.'; 
$_['help_cron_update_product'] = 'Etkinleştirilirse, ürünün fiyat ve miktar alanlarını senkronizasyon kaynaklarına göre güncelleyebilirsiniz.'; 
$_['help_price_rules']        = 'İhracat seçilirse, ihraç edilen ürünlere fiyat kuralı uygulanacaktır, aksi takdirde ithal ürünlere fiyat kuralı uygulanacaktır.'; 

//placeholder
$_['placeholder_quantity']	   = 'Varsayılan ürün miktarını girin ..'; 
$_['placeholder_weight']			 = 'Varsayılan ürün ağırlığını (Gram cinsinden) girin ..'; 
$_['text_import']			 = 'İthalat'; 
$_['text_export']			 = 'İhracat'; 

$_['info_update_imported']     = 'Not: İçe aktarılan ürün Mağazada güncellenecekse, o ürünün yalnızca Miktarı ve Fiyatı Amazon Store\'da otomatik olarak güncellenecektir'; 
$_['info_update_exported']     = 'Not: Dışa aktarılan ürün Mağazada güncellenecekse, o ürünün yalnızca Miktarı ve Fiyatı Amazon Store\'da otomatik olarak güncellenecektir'; 
$_['info_price_rules']         = 'Not: İhracat seçilirse, fiyat kuralı ihraç edilen ürünler için geçerli olur ve İthalat seçilirse, İthal edilen ürünler için fiyat kuralı geçerli olur'; 
$_['info_import_quantity_rule']         = 'Not: Etkinleştirilirse, Miktar kuralı İthal edilen ürünler için geçerli olacaktır.'; 
$_['info_export_quantity_rule']         = 'Not: Etkinleştirilirse, Miktar kuralı ihraç edilen ürünler için geçerli olacaktır.'; 
// Error
$_['error_permission']  = 'Uyarı: Amazon bağlayıcı modülünü değiştirme izniniz yok!'; 
$_['error_quantity']  = 'Uyarı: Varsayılan Ürün Miktarı pozitif sayı olmalıdır.'; 
$_['error_weight']  = 'Uyarı: Amazon Ürün Ağırlığı pozitif sayı olmalıdır.'; 
