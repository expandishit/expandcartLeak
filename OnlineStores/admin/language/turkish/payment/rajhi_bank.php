<?php

$_['heading_title']              = 'Rajhi Bank'; 
$_['settings']                   = 'Ayarlar'; 
$_['switch_text_enabled']        = 'Etkin'; 
$_['switch_text_disabled']       = 'Devre Dışı'; 
$_['rajhi_bank_contact']         = 'Rajhi Bank ile İletişim'; 


// --------------- TEXT ---------------
$_['text_payment']         = 'Ödeme'; 
$_['text_success']         = 'Başarı: rajhi_bank ödeme modülünü değiştirdiniz!'; 
$_['text_changes']         = 'Kaydedilmemiş değişiklikler var.'; 
$_['text_high']            = 'Yüksek'; 
$_['text_medium']          = 'Orta'; 
$_['text_low']             = 'Düşük'; 
$_['text_live']            = 'Canlı'; 
$_['text_test']            = 'Ölçek'; 
$_['text_all_geo_zones']   = 'Tüm Coğrafi Bölgeler'; 
$_['text_settings']        = 'Ayarlar'; 
$_['text_log']             = 'Günlük'; 
$_['text_general']         = 'Genel'; 
$_['text_statuses']        = 'Sipariş Durumları'; 
$_['text_advanced']        = 'İleri'; 


// --------------- ENTRY ---------------
$_['entry_merchant_code']            = 'Satıcı kodu'; 
$_['entry_merchant_hash_key']       = "Satıcı hash key"; 
$_['entry_iframe_id']           = 'Iframe Kimliği'; 
$_['entry_api_server']         = 'API Sunucusu'; 
$_['help_api_server']         = 'Use the '.$_['text_live'].' or '.$_['text_test'].' server to process transactions';
$_['entry_risk_speed']         = 'Risk / Hız'; 
$_['help_risk_speed']          = '<strong> Yüksek </strong> hız onayları genellikle 5-10 saniye sürer ve dijital ürünler veya düşük riskli öğeler için kullanılabilir <br> <strong> Düşük </strong> hız onayları yaklaşık 1 saat sürer ve yüksek değerli öğeler için kullanılabilir '; 
$_['entry_geo_zone']           = 'Geo Zone'; 
$_['entry_status']             = 'Durum'; 
$_['entry_sort_order']         = 'Sıralama düzeni'; 
$_['entry_new_status']         = 'Yeni'; 
$_['help_new_status']          = 'Tam ödemeyi bekleyen yeni veya kısmen ödenmiş bir fatura'; 
$_['entry_paid_status']        = 'Ücretli'; 
$_['help_paid_status']         = 'Onaylanmayı bekleyen tam olarak ödenmiş fatura'; 
$_['entry_confirmed_status']   = 'Onaylanmış'; 
$_['help_confirmed_status']    = 'Risk / Hız ayarlarına göre onaylanmış bir fatura'; 
$_['entry_complete_status']    = 'Tam sipariş durumu'; 
$_['entry_failed_status']       = 'Başarısız sipariş durumu'; 
$_['entry_refund_status']       = 'Sipariş durumu iadesi'; 
$_['help_complete_status']     = 'Satıcının hesabına alacak kaydedilmiş onaylanmış bir fatura'; 
$_['entry_expired_status']     = 'Süresi doldu'; 
$_['help_expired_status']      = 'Tam ödemenin alınmadığı ve 15 dakikalık ödeme süresinin dolduğu bir fatura'; 
$_['entry_invalid_status']     = 'Geçersiz'; 
$_['help_invalid_status']      = 'Tamamen ödenmiş ancak onaylanmamış bir fatura'; 
$_['entry_notify_url']         = 'Bildirim URL\'si'; 
$_['help_notify_url']          = 'rajhi_bank & # 8217; nin IPN\'si fatura durumu güncellemelerini bu URL\'ye gönderecek'; 
$_['entry_return_url']         = 'Dönüş URL\'si'; 
$_['help_return_url']          = 'rajhi_bank, faturanın başarılı bir şekilde ödenmesinin ardından bu URL için kullanıcıya bir yönlendirme bağlantısı sağlayacaktır'; 
$_['entry_debug_mode']         = 'Hata ayıklama modu'; 
$_['help_debug_mode']          = 'Rajhi_bank günlüğüne ek bilgi kaydeder'; 
$_['entry_default']            = 'Varsayılan'; 
$_['entry_contact_rajhi_bank']     = "Rajhi_bank ile İletişim"; 
$_['rajhi_bank_transportal_id']             = "Taşıma Kimliği"; 
$_['rajhi_bank_transportal_password']     = "Taşıma Şifresi"; 
$_['rajhi_bank_resource_key']             = "Kaynak Anahtarı"; 
$_['rajhi_bank_gatway_endpoint']          = "Ağ Geçidi Uç Noktası"; 
$_['rajhi_bank_support_endpoint']          = "EndPoint'i Destekleme"; 

// --------------- ERRORS ---------------
$_['error_permission']         = 'Uyarı: rajhi_bank ödeme modülünü değiştirme izniniz yok.'; 

$_['error_merchant_code']      = 'Satıcı Kodu gereklidir (doğrulanmış ödeme bildirimleri için)'; 
$_['error_merchant_hash_key']      = 'Satıcı Karma Anahtarı gereklidir (doğrulanmış ödeme bildirimleri için)'; 
$_['error_notify_url']         = 'Bildirim URL\'si gerekli'; 
$_['error_return_url']         = 'Dönüş URL\'si gereklidir'; 
$_['error_api_key_valid']      = 'API Anahtarının geçerli bir rajhi_bank API Erişim Anahtarı olması gerekir'; 
$_['error_notify_url_valid']   = 'Bildirim URL\'sinin geçerli bir URL kaynağı olması gerekir'; 
$_['error_return_url_valid']   = 'Dönüş URL\'si geçerli bir URL kaynağı olmalıdır'; 
$_['error_transportal_id']             = "Taşıma Kimliği"; 
$_['error_transportal_password']     = "Taşıma Şifresi"; 
$_['error_resource_key']             = "Kaynak Anahtarı"; 
$_['error_gatway_endpoint']          = "Ağ Geçidi Uç Noktası desteklenmiyor"; 
$_['error_support_endpoint']          = "EndPoint desteği desteklenmiyor"; 
