<?php

// Heading Goes here:
$_['heading_title']     = 'WhatsApp Bildirimleri'; 


// Text
$_['text_module']       = 'Uygulamalar'; 
$_['text_success']      = 'Başarı: WhatsApp Bildirimleri modülünü değiştirdiniz!'; 
$_['text_left']         = 'Ayrıldı'; 
$_['text_right']        = 'Sağ'; 
$_['text_home']         = 'Ev'; 

$_['text_yes']          = 'Evet'; 
$_['text_no']           = 'Hayır'; 

// Entry
$_['text_phone_num_filter']  = 'Telefon numarası ile başlar'; 
$_['text_phone_num_filter_help']  = 'Alıcı telefon numaraları için bu kural, WhatsApp sadece telefon numarası buraya girdiğiniz rakamlarla başlıyorsa gönderecektir. 
You able to enter multiple patterns, it must be comma separated (ie: 00971,+971,0971)';

$_['whatsapp_entry_notify_customer_by_WhatsApp_on_registration']  = '<b> Kayıt bildirimleri: </b>'; 
$_['whatsapp_entry_notify_customer_by_WhatsApp_on_registration_help']  = 'WhatsApp, kaydını tamamladıktan sonra müşteriye bir bildirim gönderecektir.'; 

$_['text_cus_reg_temp']  = '<b> Müşteri kayıt mesajı şablonu </b>'; 

$_['activation_message_template']  = '<b> Aktivasyon mesajı şablonu </b>'; 

$_['text_message_header']  = 'Başlık'; 
$_['text_message_body']  = 'Vücut'; 
$_['text_message_footer']  = 'Altbilgi'; 

$_['text_WhatsApp_confirm_per_order']  = '<b> Her yeni sipariş için kodu onayla: </b>'; 
$_['text_WhatsApp_confirm_per_order_help']  = 'Bunu etkinleştirdiğinizde, telefon daha önce doğrulanmış olsa bile müşteri her yeni siparişte onay kodunu almalıdır'; 

$_['text_WhatsApp_confirm']  = '<b> Telefonu yalnızca ilk siparişte onayla: </b>'; 
$_['text_WhatsApp_confirm_help']  = 'Bunu etkinleştirerek, müşteri bir defaya mahsus onay kodunu almalıdır'; 


$_['text_WhatsApp_confirm_trials']  = 'Maksimum onay mesajları'; 

$_['text_WhatsApp_confirm_template']  = 'Mesaj şablonu'; 

$_['text_WhatsApp_confirm_trials_help']  = 'Müşterinin onay kodunu kendisine yeniden göndermesini isteyebileceği maksimum deneme sayısı'; 

$_['whatsapp_entry_notify_customer_by_WhatsApp_on_checkout']  = '<b> Yeni sipariş bildirimi: </b>'; 
$_['whatsapp_entry_notify_customer_by_WhatsApp_on_checkout_help']  = 'Bunu etkinleştirdiğinizde, müşteriler yeni bir siparişi onayladığında mesaj alacaklar'; 

$_['text_cus_order_temp']  = 'Mesaj şablonu'; 

$_['text_admin_order_status']  = '<b> Sipariş durumu bildirimini güncelle </b>'; 
$_['text_WhatsApp_order_status']  = '<b> Sipariş durumu bildirimini güncelle </b>'; 

$_['text_WhatsApp_order_status_help']  = 'Bunu etkinleştirdiğinizde, sipariş durumunu güncellediğinizde müşteri bir mesaj alacak 
                  <br />
                  In addition to the variables listed above, Four other variables can be used here: <b></b> , <b>{order_id}</b> , <b>{order_date}</b>, <b>{comment}</b> 
                  which is the comment you write when you add history.
                  <br />
                  Empty text box will use default template.';

$_['text_admin_order_status_help']  = 'Bunu etkinleştirdiğinizde, mağaza sahibi (siz) sipariş durumu güncellendiğinde mesaj alacaksınız 
                <br />
                In addition to the variables listed above, Four other variables can be used here: <b></b> , <b>{order_id}</b> , <b>{order_date}</b>, <b>{comment}</b> 
                which is the comment you write when you add history.
                <br />
                Empty text box will use default template.';

$_['text_add_new_field']  = 'Yeni mesaj ekle'; 

$_['whatsapp_entry_ntfy_admin_by_WhatsApp_on_reg']  = '<b> Kayıt bildirimi: </b>'; 
$_['whatsapp_entry_ntfy_admin_by_WhatsApp_on_reg_help']  = 'Bunu etkinleştirdiğinizde, mağaza sahibi (siz) bir müşteri kaydı tamamladığında mesaj alacaksınız'; 

$_['text_admin_cust_reg']  = 'Mesaj şablonu'; 


$_['whatsapp_entry_notify_admin_by_WhatsApp_on_checkout']  = '<b> Yeni sipariş bildirimi: </b>'; 
$_['whatsapp_entry_notify_admin_by_WhatsApp_on_checkout_help']  = 'Bunu etkinleştirdiğinizde, mağaza sahibi (siz) yeni bir sipariş oluşturulduğunda mesaj alacaksınız'; 

$_['whatsapp_entry_username']  = 'Kullanıcı adı'; 
$_['whatsapp_entry_passwd']    = 'Parola'; 
$_['whatsapp_entry_status']    = 'Durum'; 

$_['whatsapp_entry_notify_seller_on_status_change']  = 'Satıcıya <b> yönetici durumu değiştirdiğinde </b> bildir'; 
$_['whatsapp_entry_notify_seller_on_status_change_help']  = 'Yönetici durumunu değiştirdiğinde satıcıya WhatsApp gönder.'; 

$_['whatsapp_entry_cstmr_reg_available_vars']                = 'Şu anda, yalnızca <b> {firstname} </b>, <b> {lastname} </b>, <b> {phone} </b> ve <b> {password} </b> şu kişiler için kullanılabilir: ikame'; 







$_['whatsapp_entry_notify_extra_by_WhatsApp_on_checkout']         = "Ek Uyarı telefon numaraları: <br />". 
    '<span class = "yardım"> Uyarıyı WhatsApp tarafından (virgülle ayrılmış) almak istediğiniz ek telefon numaraları. '. 
    '<br />If filled then WhatsApp will be sent even if you disable "Notify store owner for new order"</span>';

$_['whatsapp_entry_order_available_vars']  = 'Şu anda ikame için yalnızca <b> {order_id} </b>, <b> {order_date} </b> kullanılabilir'; 

// Error
$_['error_permission']  = 'Uyarı: whatsapp modülünü değiştirme izniniz yok!'; 
$_['text_footer']       = 'Herhangi bir sorunuz varsa lütfen bu WhatsApp\'a cevap verin.'; 



$_['text_gateway_setup']  = 'Ağ Geçidi Kurulumu'; 
$_['text_WhatsApp_tem']  = 'WhatsApp Şablon Oluşturma'; 

$_['text_data_that_will_be_in_DB_only']  = 'yalnızca DB\'de olacak veriler'; 
$_['text_integration_status']  = 'WhatsApp Entegrasyon durumu:'; 
$_['text_Client_api_status']  = 'İstemci API durumu:'; 
$_['text_whatsapp_integration']  = 'WhatsApp Entegrasyonu'; 
$_['text_whatsapp_phone_number']  = 'WhatsApp telefon numarası'; 
$_['text_whatsapp_business_account_id']  = 'WhatsApp İşletme Hesabı Kimliği'; 
$_['text_template_messages_namespaces']  = 'Şablon mesajları ad alanları'; 
$_['text_client_api_url']  = 'istemci API URL\'si'; 
$_['text_client_api_username']  = 'istemci API kullanıcı adı'; 
$_['text_client_api_password']  = 'istemci API şifresi'; 

$_['text_customer_notif']  = 'Müşteri bildirimleri'; 
$_['text_admin_notif']  = 'Mağaza sahibi bildirimleri'; 
$_['text_seller_notif']  = 'Satıcı Uyarıları'; 

$_['text_whatsApp_template_messages']  = 'Şablon Mesajları'; 
$_['text_whatsapp_chat']  = 'WhatsApp Sohbet'; 
$_['text_language']  = 'dil'; 
$_['text_header']  = 'başlık'; 
$_['text_body']  = 'vücut'; 
$_['text_footer']  = 'altbilgi'; 


$_['text_WhatsApp_filter']  = 'Telefonların ülke anahtarları'; 
$_['text_number_rewrite']  = 'Numara Yeniden Yazma'; 
$_['text_logs']  = 'Kütükler'; 
$_['text_api_url']  = 'API URL'; 
$_['text_api_http_method']  = 'HTTP yöntemi'; 
$_['text_get']  = 'ALMAK'; 
$_['text_post_1']  = 'POST (multipart / form-data)'; 
$_['text_post_2']  = 'POST (uygulama / x-www-form-urlencoded)'; 
$_['text_api_method_help']  = '<p> 
                          POST (multipart/form-data) or POST (application/x-www-form-urlencoded)?
                          As usual, check the gateway documentation. But here are some hints:
                          </p>
                              <ul>
                                  <li>API from old WhatsApp gateways are used to use POST (multipart/form-data)</li>
                                  <li>Most recent WhatsApp gateway APIs use POST (application/x-www-form-urlencoded)</li>
                              </ul>
                          </p>';
$_['text_dest_field']  = 'Hedef alan'; 
$_['text_dest_field_help']  = 'Bu, hedef numaralarını temsil eden değişkenin adıdır.'; 
$_['text_dest_field_placeholder']  = "ör: cep telefonları veya hedefler"; 
$_['text_msg_field']  = 'Mesaj alanı'; 
$_['text_msg_field_help']  = 'Bu, mesajı temsil eden değişkenin adıdır.'; 
$_['text_msg_field_placeholder']  = 'ex: mesaj'; 
$_['text_unicode']  = 'Unicode?'; 
$_['text_unicode_help']  = 'Bazı API ağ geçidi (Örn. Arapça için) mesaj gövdesinin Unicode\'a dönüştürülmesini gerektirir.'; 
$_['text_unicode_help_2']  = 'Göndermeden önce \ u kaldırıyoruz. Ör: <b> test </b> şu şekilde gönderilecektir: <b> 0074006500730074 </b> '; 
$_['text_additional_fields']  = 'İlave Alanlar'; 
$_['text_name']  = 'İsim'; 
$_['text_field_name']  = 'alan adı'; 
$_['text_value']  = 'Değer'; 
$_['text_field_value']  = 'alan değeri'; 
$_['text_url_encode']  = 'URL kodlama'; 
$_['text_remove_field']  = 'Bu alanı kaldır'; 
$_['text_url_encode_help']  = '<p tarzı 
                            <p>URL encoding converts characters into a format that can be sent through internet.</p>
                            <p>We should use urlencode for all GET parameters because POST parameters are automatically encoded.</p>
                            <p>Some API doesn\'t understand some URL encoded fields when sending with GET. If this is the case, disable URL encoding for the concerned fields.</p>';
$_['text_WhatsApp_template_system']  = 'WhatsApp şablonlama sistemi'; 
$_['text_WhatsApp_temp_sys_1']  = 'Çalışma zamanında gerçek bilgilerle değiştirilecek yer tutucular olan önceden tanımlanmış "değişkenler" kullanacaksınız.'; 
$_['text_available_var']  = 'Mevcut değişkenler'; 
$_['text_arrow']  = '→'; 
$_['text_firstname']  = 'İsim'; 
$_['text_lastname']  = 'Soyadı'; 
$_['text_phonenumber']  = 'Telefon numarası'; 
$_['text_orderid']  = 'Sipariş Kimliği'; 
$_['text_total']  = 'Toplam'; 
$_['text_storeurl']  = 'Mağaza url\'si'; 
$_['text_shippingadd1']  = 'Sevkiyat adresi 1'; 
$_['text_shippingadd2']  = 'Sevkiyat adresi 2'; 
$_['text_payadd1']  = 'Ödeme adresi 1'; 
$_['text_payadd2']  = 'Ödeme adresi 2'; 
$_['text_paymethod']  = 'Ödeme şekli'; 
$_['text_shipmethod']  = 'Nakliye Yöntemi'; 
$_['text_WhatsApp_system_example']  = 'Örnek: <span sınıfı 
					<span class = "yardım"> <i> Sayın <b> {firstname} </b>, mystore.com\'daki siparişiniz için teşekkür ederiz sipariş kimliği <b> {order_id} </b> tutar <b> {toplam} </ b > </i> </span> 
					<br />
					<span class = "yardım"> Bir müşteri bir dahaki sefere sipariş verdiğinde (diyelim Ahmed), aşağıdakileri içeren bir WhatsApp alacak: </span> 
					<span class = "yardım"> <i> Sayın <b> Ahmed </b>, mystore.com\'daki siparişiniz için teşekkür ederiz. sipariş numarası <b> 9999 </b> <b> 9999,99 $ </b> </i> < / span> '; 
$_['text_no_send_kw']  = 'Anahtar kelimeleri göndermeyin'; 
$_['text_no_send_help']  = 'Ödeme sırasında kuponda aşağıdaki anahtar kelimelerden biri kullanılırsa, müşteriye <b> yeni siparişte </b> WhatsApp göndermeyin. 
					<br />
					One keyword per line (a keyword can contain spaces).';
$_['text_WhatsApp_seller_status']  = 'Satıcı durumu değişikliğinde WhatsApp şablonu'; 

$_['text_WhatsApp_seller_status_help']  = 'Satıcı sayfasında yönetici satıcı durumunu güncellediğinde kullanılacak WhatsApp şablonu. 
                  <br />
                  In addition to the variables listed above, Five other variables can be used here: <b></b> , <b>{seller_email}</b> , <b>{seller_firstname}</b>, <b>{seller_lastname}</b>, <b>{seller_nickname}</b> 
                  <br />
                  Empty text box will use default template.';
$_['text_add_new_fields']  = 'Yeni mesaj ekle'; 
$_['text_status']  = 'Durum'; 
$_['text_seperator']  = '───────'; 
$_['text_admin_WhatsApp_temp']  = 'Mağaza sahibi WhatsApp şablonu'; 
$_['text_admin_WhatsApp_temp_help']  = 'yukarıda listelenen değişkenleri kullanabilirsiniz'; 


$_['text_add_new_fields_2']  = 'Yeni alanlar ekle'; 
$_['text_status_2']  = 'Durum'; 
$_['text_seperator_2']  = '───────'; 

$_['text_filter_size']  = 'Telefon numarası filtreleme: <i> <b> Numara boyutu </b> </i>'; 
$_['text_filter_size_help']  = 'Yalnızca telefon numarasında buraya girdiğiniz x rakam varsa WhatsApp\'ı gönderin. Örneğin: değeri 8 olarak ayarlarsanız, otomatik WhatsApp 12345678\'e gönderilecek ancak 2345678\'e gönderilmeyecektir. '; 
$_['text_phone_rewrite']  = 'Telefon numarası yeniden yazılıyor'; 
$_['text_phone_rewrite_help']  = 'WhatsApp\'ı göndermeden önce telefon numarasını değiştirin. 
                            <br />
                            Rewriting is applied only after filtering rules are applied.';
$_['text_replace_1_occ']  = 'İlk geçtiği yeri değiştirin'; 
$_['text_pattern']  = 'Desen'; 
$_['text_by']  = 'tarafından'; 
$_['text_substitution']  = 'ikame'; 
$_['text_enable_logs']  = 'Günlükleri etkinleştir'; 
$_['text_enable_logs_help']  = 'Ayrıntılı günlükler günlük dosyasına yazdırılacaktır. Neler olup bittiğini anlamanız gerektiğinde kullanışlıdır. '; 



$_['text_WhatsApp_confirm_template_help']  = 'WhatsApp kullanılarak telefon onayı sırasında kullanılan şablon. Yalnızca <b> {firstname} </b>, <b> {lastname} </b>, <b> {phone} </b> kullanabilirsiniz. <br> Ve <b> {confirm_code} </b> eklemelisiniz, bu bir zorunluluktur, böylece mesaj onay kodunu içerir '; 

$_['text_tab_supported']  = 'Ağ geçitleri'; 
$_['text_supported_providers']  = 'Tüm ülkelerdeki birçok WhatsApp hizmet sağlayıcısını destekliyoruz, örneğin'; 
$_['text_supported_providers_help']  = 'Yukarıdakilerden herhangi bir hizmet sağlayıcıyı veya başka bir hizmet sağlayıcıyı etkinleştirmek için yardıma ihtiyacınız olursa, lütfen müşteri hizmetleri temsilcilerimizden biriyle görüşün.'; 

$_['text_status']  = 'durum'; 


$_['activation_message_template_note']  = '<br /> <b> {activationToken} </b> kullanabilirsiniz'; 

$_['code_settings']  = 'Aktivasyon Kodu Ayarları'; 
$_['code_length']  = 'Kod Uzunluğu'; 
$_['code_type']  = 'Kod Türü'; 
$_['code_alphanumeric']  = 'Alfasayısal'; 
$_['code_numeric']  = 'Sayılar'; 

$_['text_seller_status_notification_header']  = 'Sayın,'; 
$_['text_seller_status_notification_body_prefix']  = 'Hesap durumunuz'; 
$_['text_seller_status_notification_footer']  = ''; 

$_['text_insert_your_business_Data']  = 'iş Verilerinizi ekleyin'; 
$_['text_in_verification']               = 'doğrulamada'; 
$_['text_get_confirmation_code']          = 'onay kodunu al'; 
$_['text_enter_confirmation_code']          = 'Onay kodunu girin'; 
$_['text_verified']                        = 'doğrulandı'; 
$_['entry_business_name']                    = 'İş adı'; 
$_['entry_whatsapp_business_id']           = 'WhatsApp İşletme Kimliği'; 
$_['entry_whatsapp_business_id_help']      = 'bu kimliği WhatsApp işletme yöneticinizden alabilirsiniz'; 
$_['entry_whatsapp_phone_number']           = 'WhatsApp İçin Telefon Numarası'; 
$_['entry_whatsapp_phone_number_help']      = 'Bu numara whatsapp ile bir işletme numarası olarak kaydedilmelidir'; 
$_['entry_country_code']                   = 'Ülke kodu'; 
$_['entry_phone_number']                   = 'Telefon numarası'; 
$_['entry_whatsapp_methods']               = 'Doğrulama metodu'; 
$_['text_we_are_reviewing_your_Data_and_will_confirm_soon']           = 'Verilerinizi inceliyoruz ve yakında onaylayacağız.'; 
$_['entry_whatsapp_verification_code']     = 'doğrulama kodu'; 
$_['text_congratulation']                 = 'Tebrikler! <bro> Entegrasyon işlemi bitti '; 
$_['btn_next']                                = 'Sonraki'; 
$_['btn_previous']                            = 'Önceki'; 
$_['btn_finish']                            = "Hadi başlayalım"; 
$_['heading_steps_title']                   = 'entegrasyon süreci'; 
