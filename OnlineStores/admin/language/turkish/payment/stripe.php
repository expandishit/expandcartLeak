<?php
// = 
// Stripe Payment Gateway v230.4
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
// = 

$version  = 'v230.4'; 

//------------------------------------------------------------------------------
// Heading
//------------------------------------------------------------------------------
$_['heading_title']						 = 'Stripe Payment Gateway'; 
$_['text_stripe']						= '<a target="blank" href="https://stripe.com"><img src="https://stripe.com/img/logo.png" alt="Stripe" title="Stripe" /></a>';
$_['text_payment']                       = 'Ödeme'; 
$_['text_success']                       = 'Başarı Ayarları değiştirdiniz!'; 

$_['settings']  = 'Ayarlar'; 
$_['switch_text_enabled']  = 'Etkin'; 
$_['switch_text_disabled']  = 'Devre Dışı'; 

//------------------------------------------------------------------------------
// Extension Settings
//------------------------------------------------------------------------------
$_['tab_extension_settings']			 = 'Ayarlar'; 
$_['heading_extension_settings']		 = 'Ayarlar'; 

$_['entry_status']						 = 'Durum'; 
$_['entry_sort_order']					 = 'Sıralama düzeni'; 
$_['entry_title']						 = 'Başlık'; 
$_['entry_button_text']					 = 'Düğme Metni'; 
$_['entry_button_class']				 = 'Düğme Sınıfı'; 
$_['entry_button_styling']				 = 'Düğme Şekillendirme'; 

// Payment Page Text
$_['heading_payment_page_text']			 = 'Ödeme Sayfası Metni'; 

$_['entry_text_card_details']			 = 'Kart detayları'; 
$_['entry_text_use_your_stored_card']	 = 'Saklanan Kartınızı Kullanın'; 
$_['entry_text_ending_in']				 = 'ile biten'; 
$_['entry_text_use_a_new_card']			 = 'Yeni Kart Kullanın'; 
$_['entry_text_card_name']				 = 'Karttaki İsim'; 
$_['entry_text_card_number']			 = 'Kart numarası'; 
$_['entry_text_card_type']				 = 'Kart tipi'; 
$_['entry_text_card_expiry']			 = 'Kartın Son Kullanma Tarihi (AA / YY)'; 
$_['entry_text_card_security']			 = 'Kart Güvenlik Kodu (CVC)'; 
$_['entry_text_store_card']				 = 'Gelecekte Kullanım için Mağaza Kartı'; 
$_['entry_text_please_wait']			 = 'Lütfen bekleyin'; 
$_['entry_text_to_be_charged']			 = 'Daha Sonra Ücretlendirilecek:'; 

// Errors
$_['heading_errors']					 = 'Hatalar'; 

$_['entry_error_customer_required']		 = 'Müşteri Gerekli:'; 
$_['entry_error_shipping_required']		 = 'Nakliye Gerekli:'; 
$_['entry_error_shipping_mismatch']		 = 'Gönderim Uyuşmazlığı:'; 

// Stripe Error Codes
$_['heading_stripe_error_codes']		 = 'Stripe Hata Kodları'; 
$_['help_stripe_error_codes']			 = 'Bu hata kodu için Stripe \'ın varsayılan hata mesajını görüntülemek için bu alanlardan herhangi birini boş bırakın. HTML desteklenmektedir. Stripe Checkout kullanılırken hata mesajları görüntülenmiyor. '; 

$_['entry_error_card_declined']			 = 'kart reddedildi'; 
$_['entry_error_expired_card']			 = 'Süresi dolmuş kart'; 
$_['entry_error_incorrect_cvc']			 = 'yanlış_cvc:'; 
$_['entry_error_incorrect_number']		 = 'yanlış numara'; 
$_['entry_error_incorrect_zip']			 = 'yanlış posta kodu:'; 
$_['entry_error_invalid_cvc']			 = 'geçersiz_cvc'; 
$_['entry_error_invalid_expiry_month']	 = 'geçersiz son kullanma ayı'; 
$_['entry_error_invalid_expiry_year']	 = 'geçersiz son kullanma yılı'; 
$_['entry_error_invalid_number']		 = 'geçersiz numara'; 
$_['entry_error_missing']				 = 'eksik: '; 
$_['entry_error_processing_error']		 = 'processing_error'; 

// Cards Page Text
$_['heading_cards_page_text']			 = 'Kartlar Sayfası Metni'; 

$_['entry_cards_page_link']				 = 'Kartlar Sayfası Bağlantısı:'; 
$_['entry_cards_page_heading']			 = 'Kartlar Sayfası Başlığı:'; 
$_['entry_cards_page_none']				 = 'Kart Yok Mesajı:'; 
$_['entry_cards_page_default_card']		 = 'Varsayılan Kart Metni:'; 
$_['entry_cards_page_make_default']		 = 'Varsayılan Düğme Yap'; 
$_['entry_cards_page_delete']			 = 'Sil Düğmesi'; 
$_['entry_cards_page_confirm']			 = 'Silme Onayı'; 
$_['entry_cards_page_add_card']			 = 'Yeni Kart Düğmesi Ekle'; 
$_['entry_cards_page_card_address']		 = 'Kart Adresi:'; 
$_['entry_cards_page_success']			 = 'Başarı mesajı'; 

// Subscriptions Page Text
$_['heading_subscriptions_page_text']	 = 'Abonelikler Sayfa Metni'; 

$_['entry_subscriptions_page_heading']	 = 'Abonelikler Sayfası Başlığı:'; 
$_['entry_subscriptions_page_message']	 = 'Varsayılan Kart Mesajı:'; 
$_['entry_subscriptions_page_none']		 = 'Abonelik Yok Mesajı:'; 
$_['entry_subscriptions_page_trial']	 = 'Deneme Bitiş Metni:'; 
$_['entry_subscriptions_page_last']		 = 'Son Ödeme Metni:'; 
$_['entry_subscriptions_page_next']		 = 'Sonraki Ödeme Metni:'; 
$_['entry_subscriptions_page_charge']	 = 'Ek Masraf Metni:'; 
$_['entry_subscriptions_page_cancel']	 = 'İptal Düğmesi'; 
$_['entry_subscriptions_page_confirm']	 = 'Onayı İptal Et:'; 

//------------------------------------------------------------------------------
// Order Statuses
//------------------------------------------------------------------------------
$_['tab_order_statuses']				 = 'Sipariş Durumları'; 
$_['heading_order_statuses']			 = 'Sipariş Durumları'; 
$_['help_order_statuses']				 = 'Bir ödeme her koşulu karşıladığında ayarlanan sipariş durumlarını seçin. Not: CVC veya Zip Kontrollerinde başarısız olan ödemeleri gerçekten <strong> reddetmek </strong> için Stripe yönetici panelinizde uygun ayarı etkinleştirmeniz gerekir. <br /> Geçmişte sağlanan bağlantıyı kullanarak bir ödemeyi iade edebilirsiniz sipariş için sekme. '; 

$_['entry_success_status_id']			 = 'Başarılı Ödeme (Yakalanan)'; 
$_['entry_authorize_status_id']			 = 'Başarılı Ödeme (Yetkili)'; 
$_['entry_error_status_id']				 = 'Sipariş Tamamlama Hatası:'; 
$_['entry_street_status_id']			 = 'Sokak Kontrol Hatası'; 
$_['entry_zip_status_id']				 = 'Zip Kontrolü Hatası'; 
$_['entry_cvc_status_id']				 = 'CVC Kontrol Hatası'; 
$_['entry_refund_status_id']			 = 'Tamamen İade Edilen Ödeme'; 
$_['entry_partial_status_id']			 = 'Kısmen İade Edilen Ödeme'; 

$_['text_ignore']						 = '--- Aldırmamak ---'; 

//------------------------------------------------------------------------------
// Restrictions
//------------------------------------------------------------------------------
$_['tab_restrictions']					 = 'Kısıtlamalar'; 
$_['heading_restrictions']				 = 'Kısıtlamalar'; 
$_['help_restrictions']					 = 'Gerekli alışveriş sepeti toplamını belirleyin ve bu ödeme yöntemi için uygun mağazaları, coğrafi bölgeleri ve müşteri gruplarını seçin.'; 

$_['entry_min_total']					 = 'Minimum Toplam:'; 
$_['entry_max_total']					 = 'Maksimum Toplam:'; 

$_['entry_stores']						 = 'Mağazalar): '; 

$_['entry_geo_zones']					 = 'Coğrafi Bölge (ler):'; 
$_['text_everywhere_else']				 = '<em> Her Yerde </em>'; 

$_['entry_customer_groups']				 = 'Müşteri Grupları:'; 
$_['text_guests']						 = '<em> Misafirler </em>'; 

// Currency Settings
$_['heading_currency_settings']			 = 'Para Birimi Ayarları'; 
$_['help_currency_settings']			 = 'Sipariş para birimine göre Stripe\'ın ücretlendireceği para birimlerini seçin. <a target="_blank" href="https://support.stripe.com/questions/which-currencies-does-stripe-support">See which currencies your country supports</a>'; 
$_['entry_currencies']					 = 'Siparişler [para birimi] İçindeyken Ödeme Al'; 
$_['text_currency_disabled']			 = '--- Devre Dışı ---'; 

//------------------------------------------------------------------------------
// Stripe Settings
//------------------------------------------------------------------------------
$_['tab_stripe_settings']				 = 'Stripe Ayarları'; 
$_['help_stripe_settings']				 = 'API Anahtarları, Stripe yönetici panelinizde Hesabınız> Hesap Ayarları> API Anahtarları altında bulunabilir'; 

// API Keys
$_['heading_api_keys']					 = 'API Anahtarları'; 

$_['entry_test_secret_key']				 = 'Test Gizli Anahtarı'; 
$_['entry_test_publishable_key']		 = 'Yayınlanabilir Anahtarı Test Et'; 
$_['entry_live_secret_key']				 = 'Canlı Gizli Anahtar'; 
$_['entry_live_publishable_key']		 = 'Canlı Yayınlanabilir Anahtar'; 

// Stripe Settings
$_['heading_stripe_settings']			 = 'Stripe Ayarları'; 

$_['entry_webhook_url']					 = 'Webhook URL\'si:'; 

$_['entry_transaction_mode']			 = 'İşlem Modu:'; 
$_['text_test']							 = 'Ölçek'; 
$_['text_live']							 = 'Canlı'; 

$_['entry_charge_mode']					 = 'Şarj Modu:'; 
$_['text_authorize']					 = 'Yetki vermek'; 
$_['text_capture']						 = 'Ele geçirmek'; 
$_['text_fraud_authorize']				 = 'Muhtemelen hileli ise yetkilendirin, Aksi takdirde yakalayın'; 

$_['entry_transaction_description']		 = 'İşlem açıklaması: '; 

$_['entry_send_customer_data']			 = 'Müşteri Verilerini Gönder:'; 
$_['text_never']						 = 'Asla'; 
$_['text_customers_choice']				 = 'Müşterinin seçimi'; 
$_['text_always']						 = 'Her zaman'; 

$_['entry_allow_stored_cards']			 = 'Müşterilerin Saklanan Kartları Kullanmasına İzin Ver:'; 

// Apple Pay Settings
$_['heading_apple_pay_settings']		 = 'Apple Pay Ayarları'; 

$_['entry_applepay']					 = 'Apple Pay\'i Etkinleştir:'; 
$_['entry_applepay_label']				 = 'Ödeme Sayfası Etiketi:'; 
$_['entry_applepay_billing']			 = 'Fatura Adresi İste:'; 

//------------------------------------------------------------------------------
// Stripe Checkout
//------------------------------------------------------------------------------
$_['tab_stripe_checkout']				 = 'Stripe Checkout'; 
$_['heading_stripe_checkout']			 = 'Stripe Checkout'; 
$_['help_stripe_checkout']				 = 'Stripe Checkout, kredi kartı girişlerini, doğrulamasını ve hata işlemeyi görüntülemek için Stripe açılır penceresini kullanır. Bununla ilgili daha fazla bilgi edinebilir ve <a target="_blank" href="https://stripe.com/docs/checkout">https://stripe.com/docs/checkout</a><br />Note: Stripe Checkout does <strong>not</strong> allow customers to use the billing address entered in Expandcart.'; 

$_['entry_use_checkout']				 = 'Çizgili Ödeme Açılır Penceresini Kullan:'; 
$_['text_yes_for_desktop_devices']		 = 'Evet, yalnızca masaüstü cihazlar için'; 

$_['entry_checkout_remember_me']		 = "Beni Hatırla\" Seçeneğini Etkinleştir:"; 

$_['entry_checkout_alipay']				 = 'Alipay\'i Etkinleştir:'; 
$_['entry_checkout_bitcoin']			 = 'Bitcoin\'i Etkinleştir:'; 

$_['entry_checkout_billing']			 = 'Fatura Adresi İste:'; 

$_['entry_checkout_shipping']			 = 'Gönderim Adresi Gerektir:'; 

$_['entry_checkout_image']				 = 'Açılır Logo:'; 
$_['text_browse']						 = 'Araştır'; 
$_['text_clear']						 = 'Açık'; 
$_['text_image_manager']				 = 'Görüntü Yöneticisi'; 

$_['entry_checkout_title']		 		 = 'Açılır Başlık:'; 

$_['entry_checkout_description']		 = 'Pop-up Açıklaması:'; 

$_['entry_checkout_button']				 = 'Açılır Düğme Metni:'; 

$_['entry_quick_checkout']				 = 'Hızlı Ödeme'; 

//------------------------------------------------------------------------------
// Subscription Products
//------------------------------------------------------------------------------
$_['tab_subscription_products']			 = 'Abonelik Ürünleri'; 
$_['help_subscription_products']		 = '&Boğa; Abonelik ürünleri, müşteriyi satın aldıklarında ilgili Stripe planına abone olacaktır. Ürünün "Konum" alanına Şerit plan kimliğini girerek bir ürünü bir planla ilişkilendirebilirsiniz. <br /> & bull; Abonelik hemen ücretlendirilmek üzere ayarlanmadıysa (yani bir deneme süresi varsa), abonelik miktarı orijinal siparişinden çıkarılır ve abonelik kartından fiilen ücretlendirildiğinde yeni bir sipariş oluşturulur. < br /> & bull; Stripe gelecekte abonelikten ücret aldığında, Expandcart\'ta buna karşılık gelen bir sipariş oluşturulacaktır. <br /> & bull; Stripe hesabınızda ayarlanmış bir kuponunuz varsa, aynı kupon kodunu ve indirim miktarını kullanarak bir Expandcart kuponunu ona eşleyebilirsiniz. Bir müşteri bir abonelik ürünü satın aldığında ve bu kupon kodunu kullandığında, abonelik ücretini uygun şekilde ayarlamak için kodu Stripe\'a iletecektir. '; 

$_['heading_subscription_products']		 = 'Abonelik Ürün Ayarları'; 

$_['entry_subscriptions']				 = 'Abonelik Ürünlerini Etkinleştir'; 
$_['entry_prevent_guests']				 = 'Konukların Satın Almasını Engelle:'; 
$_['entry_include_shipping']			 = 'Kargo ücreti dahil: '; 
$_['entry_allow_customers_to_cancel']	 = 'Müşterilerin Abonelikleri İptal Etmesine İzin Ver:'; 

// Current Subscription Products
$_['heading_current_subscriptions']		 = 'Güncel Abonelik Ürünleri'; 
$_['entry_current_subscriptions']		 = 'Mevcut Abonelik Ürünleri:'; 

$_['text_thead_Expandcart']				 = 'Genişlet'; 
$_['text_thead_stripe']					 = 'Şerit'; 
$_['text_product_name']					 = 'Ürün adı'; 
$_['text_product_price']				 = 'Ürün fiyatı'; 
$_['text_location_plan_id']				 = 'Yer / Plan Kimliği'; 
$_['text_plan_name']					 = 'Plan Adı'; 
$_['text_plan_interval']				 = 'Plan Aralığı'; 
$_['text_plan_charge']					 = 'Plan Ücreti'; 
$_['text_no_subscription_products']		 = 'Abonelik Ürünü Yok'; 
$_['text_create_one_by_entering']		 = 'Ürün için "Konum" alanına Şerit plan kimliğini girerek bir tane oluşturun'; 

// Map Options to Subscriptions
$_['heading_map_options']				 = 'Aboneliklere Harita Seçenekleri'; 
$_['help_map_options']					 = 'Müşterinin sepetinde uygun seçenek adı ve seçenek değeri olan bir ürün varsa, ilgili plan kimliğine abone olacaktır. Bu, o ürün için Konum alanındaki plan kimliğini geçersiz kılacaktır. '; 

$_['column_action']						 = 'Aksiyon'; 
$_['column_option_name']				 = 'Seçenek Adı'; 
$_['column_option_value']				 = 'Opsiyon değeri'; 
$_['column_plan_id']					 = 'Plan Kimliği'; 

$_['button_add_mapping']				 = 'Eşleme Ekle'; 

// Map Recurring Profiles to Subscriptions
$_['heading_map_recurring_profiles']	 = 'Yinelenen Profilleri Aboneliklerle Eşleştirin'; 
$_['help_map_recurring_profiles']		 = 'Müşterinin sepetinde uygun yinelenen profil adına sahip bir ürünü varsa, ilgili plan kimliğine abone olacaktır. Bu, o ürün için Konum alanındaki plan kimliğini geçersiz kılacaktır. Abonelik sıklığı ve ücret tutarı, yinelenen profil ayarları tarafından değil Stripe planı tarafından belirlenir, bu nedenle bunların tam olarak eşleştiğinden emin olun. '; 

$_['column_profile_name']				 = 'Yinelenen Profil Adı'; 

//------------------------------------------------------------------------------
// Create a Charge
//------------------------------------------------------------------------------
$_['tab_create_a_charge']				 = 'Ödeme Oluşturun'; 

$_['help_charge_info']					 = 'Aşağıya ödeme bilgilerini girin, ardından bir ödeme bağlantısı oluşturmayı, bir müşterinin kartını borçlandırmayı veya manuel olarak bir kart girmeyi seçin.'; 
$_['heading_charge_info']				 = 'Ücret Bilgisi'; 

$_['entry_order_id']					 = 'Sipariş Kimliği: '; 
$_['entry_order_status']				 = 'Sipariş Durumu Değişikliği:'; 
$_['entry_description']					 = 'Açıklama: '; 
$_['entry_statement_descriptor']		 = 'İfade Tanımlayıcı:'; 
$_['entry_amount']						 = 'Miktar'; 

// Create Payment Link
$_['heading_create_payment_link']		 = 'Ödeme Bağlantısı Oluştur'; 

$_['help_create_payment_link']			 = ''; 
$_['button_create_payment_link']		 = 'Ödeme Bağlantısı Oluştur'; 

// Use a Stored Card
$_['heading_use_a_stored_card']			 = 'Saklanan Kartı Kullanın'; 

$_['entry_customer']					 = 'Müşteri'; 
$_['placeholder_customer']				 = 'Bir müşterinin adını veya e-posta adresini yazmaya başlayın'; 
$_['text_customers_stored_cards_will']	 = '(Müşterinin Varsayılan Kartı Burada Görünecektir)'; 
$_['button_create_charge']				 = 'Ücret Oluştur'; 

// Use a New Card
$_['heading_use_a_new_card']			 = 'Yeni Kart Kullanın'; 

//------------------------------------------------------------------------------
// Standard Text
//------------------------------------------------------------------------------
$_['copyright']							 = ''; 

$_['standard_autosaving_enabled']		 = 'Otomatik Kaydetme Etkin'; 
$_['standard_confirm']					 = 'Bu işlem geri alınamaz. Devam et?'; 
$_['standard_error']					 = '<strong> Hata: </strong> Değiştirme izniniz yok'. $_['heading_title']. '!'; 
$_['standard_max_input_vars']			 = '<strong> Uyarı: </strong> Lütfen müşteri hizmetlerine başvurun 2.'; 
$_['standard_please_wait']				 = 'Lütfen bekle...'; 
$_['standard_saved']					 = 'Kaydedildi!'; 
$_['standard_saving']					 = 'Kaydediliyor ...'; 
$_['standard_select']					 = '--- Seç -'; 
$_['standard_success']					 = 'Başarı!'; 
$_['standard_testing_mode']				 = 'Günlüğünüz açılamayacak kadar büyük! Önce temizleyin, ardından testinizi yeniden çalıştırın. '; 
$_['standard_vqmod']					 = '<strong> Uyarı: </strong> Lütfen müşteri hizmetlerine başvurun.'; 

$_['standard_module']					 = 'Uygulamalar'; 
$_['standard_shipping']					 = 'Nakliye'; 
$_['standard_payment']					 = 'Ödemeler'; 
$_['standard_total']					 = 'Emir Toplamları'; 
$_['standard_feed']						 = 'Yemler'; 

// Errors

$_['error_settings']                     = "Uyarı: lütfen Test Yayınlanabilir Anahtar, Test Gizli Anahtarı, Canlı Yayınlanabilir Anahtar ve Canlı Gizli Anahtar alanlarını doldurun !!"; 
?>