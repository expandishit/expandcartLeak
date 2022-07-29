<?php
/**
 * @license https://github.com/bitpay/opencart-bitpay/blob/master/LICENSE MIT
 */

$_['heading_title']  = 'BitPay'; 
$_['settings']  = 'Ayarlar'; 
$_['switch_text_enabled']  = 'Etkin'; 
$_['switch_text_disabled']  = 'Devre Dışı'; 
// Text
$_['text_payment']         = 'Ödeme'; 
$_['text_success']         = 'Başarı: BitPay ödeme modülünü değiştirdiniz!'; 
$_['text_changes']         = 'Kaydedilmemiş değişiklikler var.'; 
$_['text_bitpay']          = '<a onclick="window.open(\'https://www.bitpay.com/\');"><img src="view/image/payment/bitpay.png" alt="BitPay" title="bitpay" style="border: 1px solid #EEEEEE;" /></a>'; 
$_['text_bitpay_support']  = '<span>For <strong>24/7 support</strong>, please visit our website <a href="https://support.bitpay.com" target="_blank">https://support.bitpay.com</a> or send an email to <a href="mailto:support@bitpay.com" target="_blank">support@bitpay.com</a> for immediate attention</span>';
$_['text_bitpay_apply']    = '<a href="https://bitpay.com/start" title="Apply for a BitPay account">Start Accepting Bitcoin with BitPay</a>'; 
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


// Entry
$_['entry_api_token']          = 'API Simgesi'; 
$_['entry_api_key']            = 'API Anahtarı'; 
$_['help_api_key']            = '<a href="https://bitpay.com/api-keys" target="_blank">https://bitpay.com/api-keys</a>';
$_['help_api_key_test']       = '<a href="https://test.bitpay.com/api-keys" target="_blank">https://test.bitpay.com/api-keys</a>';
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
$_['entry_complete_status']    = 'Tamamlayınız'; 
$_['help_complete_status']     = 'Satıcının hesabına alacak kaydedilmiş onaylanmış bir fatura'; 
$_['entry_expired_status']     = 'Süresi doldu'; 
$_['help_expired_status']      = 'Tam ödemenin alınmadığı ve 15 dakikalık ödeme süresinin dolduğu bir fatura'; 
$_['entry_invalid_status']     = 'Geçersiz'; 
$_['help_invalid_status']      = 'Tamamen ödenmiş ancak onaylanmamış bir fatura'; 
$_['entry_notify_url']         = 'Bildirim URL\'si'; 
$_['help_notify_url']          = 'BitPay & # 8217; nin IPN\'si fatura durumu güncellemelerini bu URL\'ye gönderecektir'; 
$_['entry_return_url']         = 'Dönüş URL\'si'; 
$_['help_return_url']          = 'BitPay, faturanın başarılı bir şekilde ödenmesinin ardından bu URL için kullanıcıya bir yeniden yönlendirme bağlantısı sağlayacaktır'; 
$_['entry_debug_mode']         = 'Hata ayıklama modu'; 
$_['help_debug_mode']          = 'BitPay günlüğüne ek bilgi kaydeder'; 
$_['entry_default']            = 'Varsayılan'; 

// Error
$_['error_permission']         = 'Uyarı: Bitpay ödeme modülünü değiştirme izniniz yok.'; 

$_['error_api_key']            = 'API Anahtarı gerekli (doğrulanmış ödeme bildirimleri için)'; 
$_['error_notify_url']         = 'Bildirim URL\'si gerekli'; 
$_['error_return_url']         = 'Dönüş URL\'si gereklidir'; 
$_['error_api_key_valid']      = 'API Anahtarının geçerli bir BitPay API Erişim Anahtarı olması gerekir'; 
$_['error_notify_url_valid']   = 'Bildirim URL\'sinin geçerli bir URL kaynağı olması gerekir'; 
$_['error_return_url_valid']   = 'Dönüş URL\'si geçerli bir URL kaynağı olmalıdır'; 
