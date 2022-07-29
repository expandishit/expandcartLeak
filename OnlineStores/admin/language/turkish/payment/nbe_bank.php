<?php

// Heading
$_['heading_title']  = 'NBE Bank'; 
$_['settings']  = 'Ayarlar'; 
$_['switch_text_enabled']  = 'Etkin'; 
$_['switch_text_disabled']  = 'Devre Dışı'; 
$_['order_statuses']  = 'Sipariş Durumları'; 

// Text
$_['text_payment']  = 'Ödeme'; 
$_['text_success']  = 'Başarılı: SMEOnline hesap ayrıntılarını değiştirdiniz'; 
$_['text_yes']  = 'Evet'; 
$_['text_no']  = 'Hayır'; 
$_['text_purchase']  = 'Satın alma'; 
$_['text_preauth_capture']  = 'Ön Yetkilendirme / Yakalama'; 
$_['text_capture']  = 'ele geçirmek'; 
$_['text_refund']  = 'geri ödeme'; 
$_['text_approved']  = 'onaylandı.'; 
$_['text_declined']  = 'reddedildi.'; 
$_['text_receipt_number']  = 'Makbuz Numarası'; 

// Entry
$_['entry_api_url']  = 'Temel API URL\'si'; 
$_['entry_username']  = 'API Kullanıcı Adı'; 
$_['entry_password']  = 'API Şifresi'; 
$_['entry_merchant_number']  = 'Satıcı Numarası'; 
$_['entry_merchant_number_note']  = 'Banka tarafından sağlanan satıcı numaranız.'; 
$_['entry_payment_action']  = 'Ödeme Eylemi'; 
$_['entry_payment_action_note']  = 'Satın Alma veya Ön Yetkilendirme / Yakalama arasında seçim yapın. Satın alma işlemleri karttan anında ücret alır. Ön provizyonlar, kartta yeterli para olup olmadığını kontrol eder ve belirtilen miktar için karta bir bloke koyar. Yakalar, karttan ödeme alarak bir ön provizyonu tamamlar '; 
$_['entry_test_mode']  = 'Test modu'; 
$_['entry_test_mode_note']  = 'Test modu etkinleştirilirse, tüm işlemler test modunda işlenecektir. Para almayacaksınız '; 
$_['entry_pending_status']  = 'Bekleyen Sipariş Durumu'; 
$_['entry_payment_status']  = 'Onaylanmış Ödeme için Sipariş Durumu'; 
$_['entry_preauth_status']  = 'Bekleyen Yakalama Sipariş Durumu'; 
$_['entry_failed_status']  = 'Reddedilen Ödeme için Sipariş Durumu'; 
$_['entry_captured_status']  = 'Onaylı Yakalama için Sipariş Durumu'; 
$_['entry_refunded_status']  = 'Onaylı Geri Ödeme için Sipariş Durumu'; 
$_['entry_geo_zone']  = 'Geo Zone'; 
$_['entry_total']  = 'Toplam'; 
$_['entry_total_note']  = 'Bu ödeme yöntemi aktif hale gelmeden önce siparişin ulaşması gereken ödeme toplamı.'; 
$_['entry_sort_order']  = 'Sıralama düzeni'; 

//Meza
$_['entry_meeza_active']  = 'Meeza Aktivasyonu'; 
$_['entry_meeza_settings']  = 'Meeza Ayarları'; 
$_['entry_meeza_terminal_id']  = 'Terminal Kimliği'; 
$_['entry_meeza_merchant_id']  = 'Tüccar kimliği'; 
$_['entry_meeza_secret_key']  = 'Gizli anahtar'; 


// Error
$_['error_permission']  = 'Uyarı: SMEOnline ödemesini değiştirme izniniz yok'; 
$_['error_api_url']  = 'Temel API URL\'si Gerekli'; 
$_['error_username']  = 'API Kullanıcı Adı Gerekli'; 
$_['error_password']  = 'API Şifresi Gerekli'; 
$_['error_merchant_number']  = 'Tüccar Numarası Gerekli'; 
$_['error_decline_reason']  = 'Nedeni reddet'; 
$_['error_request']  = 'Uyarı: İsteğiniz işlenirken hata oluştu'; 
$_['error_valid_amount']  = 'Uyarı: Geri ödeme için geçersiz tutar'; 
$_['error_payment_captured']  = 'Uyarı: Ödeme zaten tamamlandı'; 
$_['error_payment_refund']  = 'Uyarı: Ödeme henüz tamamlanmadı'; 
$_['error_payment_preauth']  = 'Uyarı: Bu eylemi gerçekleştirmek için Ön Yetkilendirme tamamlanmalıdır'; 
$_['error_transaction_not_found']  = 'Uyarı: Orijinal işlem bulunamadı'; 
$_['error_maximum_amount_refund']  = 'Uyarı: İade edilebilecek maksimum tutar'; 
$_['error_full_refund']  = 'Uyarı: Ödeme zaten tamamen iade edildi'; 
$_['error_curl']  = 'PHP \' nin curl uzantısı yüklü değil. SMEOnline modülü, sunucunuzda php5-curl\'yi etkinleştirmek için gereklidir. '; 
