<?php

// Heading Goes here:
$_['heading_title']     = 'SMS Bildirimleri'; 


// Text
$_['text_module']       = 'Uygulamalar'; 
$_['text_success']      = 'Başarılı: SMS Bildirimleri modülünü değiştirdiniz!'; 
$_['text_left']         = 'Ayrıldı'; 
$_['text_right']        = 'Sağ'; 
$_['text_home']         = 'Ev'; 

$_['text_yes']          = 'Evet'; 
$_['text_no']           = 'Hayır'; 


// Entry
$_['smshare_entry_username']  = 'Kullanıcı adı'; 
$_['smshare_entry_passwd']    = 'Parola'; 

$_['smshare_entry_status']    = 'Durum'; 

$_['smshare_entry_notify_customer_by_sms_on_registration']  = 'Müşteriye <b> kayıtta </b> bildir: <br />'. 
														     '<span class = "yardım"> Kaydı tamamladıktan sonra müşteriye SMS gönderin. </span> '; 

$_['smshare_entry_notify_seller_on_status_change']  = 'Satıcıya <b> yönetici durumu değiştirdiğinde </b> bildir: <br />'. 
														     '<span class = "yardım"> Yönetici durumunu değiştirdiğinde satıcıya SMS gönder. </span> '; 
                                                             
$_['smshare_entry_cstmr_reg_available_vars']                = 'Şu anda, yalnızca <b> {firstname} </b>, <b> {lastname} </b>, <b> {phonenumber} </b> ve <b> {password} </b> kullanılabilir ikame'; 
															 
$_['smshare_entry_notify_customer_by_sms_on_checkout']      = 'Müşteriye <b> yeni sipariş </b> için bildir: <br />'. 
															 '<span class = "yardım"> Yeni bir siparişi tamamladığında müşterilere SMS gönder </span> '; 
															 
$_['smshare_entry_ntfy_admin_by_sms_on_reg'] 		        = 'Mağaza sahibine <b> kayıtta </b> bildir: <br />'. 
														     '<span class = "yardım"> Bir müşteri kaydı tamamladığında mağaza sahibine SMS gönder </span> '; 

$_['smshare_entry_notify_admin_by_sms_on_checkout']         = 'Mağaza sahibine <b> yeni sipariş </b> için bildir: <br />'. 
															 '<span class = "yardım"> Yeni bir sipariş oluşturulduğunda mağaza sahibine SMS gönder </span> '; 
															 
$_['smshare_entry_notify_extra_by_sms_on_checkout']         = "Ek Uyarı telefon numaraları: <br />". 
															 '<span class = "yardım"> Uyarıyı sms (virgülle ayrılmış) ile almak istediğiniz ek telefon numaraları. '. 
															 '<br />If filled then SMS will be sent even if you disable "Notify store owner for new order"</span>';

$_['smshare_entry_order_available_vars']  = 'Şu anda ikame için yalnızca <b> {order_id} </b>, <b> {order_date} </b> kullanılabilir'; 

// Error
$_['error_permission']  = 'Uyarı: smshare modülünü değiştirme izniniz yok!'; 
$_['text_footer']       = 'Herhangi bir sorunuz varsa lütfen bu SMS\'e cevap verin.'; 



$_['text_gateway_setup']  = 'Ağ Geçidi Kurulumu'; 
$_['text_sms_tem']  = 'SMS Şablonu Oluşturma'; 
$_['text_customer_notif']  = 'Müşteri Uyarıları'; 
$_['text_admin_notif']  = 'Yönetici Uyarıları'; 
$_['text_seller_notif']  = 'Satıcı Uyarıları'; 
$_['text_sms_filter']  = 'SMS Filtreleme'; 
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
                                  <li>API from old SMS gateways are used to use POST (multipart/form-data)</li>
                                  <li>Most recent SMS gateway APIs use POST (application/x-www-form-urlencoded)</li>
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
$_['text_add_new_field']  = 'Yeni alan ekle'; 
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
$_['text_sms_template_system']  = 'SMS şablonlama sistemi'; 
$_['text_sms_temp_sys_1']  = 'Çalışma zamanında gerçek bilgilerle değiştirilecek yer tutucular olan önceden tanımlanmış "değişkenler" kullanacaksınız.'; 
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
$_['text_sms_system_example']  = 'Örnek: <span sınıfı 
					<span class = "yardım"> <i> Sayın <b> {firstname} </b>, mystore.com\'daki siparişiniz için teşekkür ederiz sipariş kimliği <b> {order_id} </b> tutar <b> {toplam} </ b > </i> </span> 
					<br />
					<span class = "yardım"> Bir müşteri bir dahaki sefere sipariş verdiğinde (diyelim Ahmet), aşağıdakileri içeren bir SMS alacaktır: </span> 
					<span class = "yardım"> <i> Sayın <b> Ahmed </b>, mystore.com\'daki siparişiniz için teşekkür ederiz. sipariş numarası <b> 9999 </b> <b> 9999,99 $ </b> </i> < / span> '; 
$_['text_cus_reg_temp']  = 'Müşteri <b> kaydı </b> SMS şablonu'; 
$_['text_cus_order_temp']  = 'Müşteri <b> yeni sipariş </b> SMS şablonu'; 
$_['text_no_send_kw']  = 'Anahtar kelimeleri göndermeyin'; 
$_['text_no_send_help']  = 'Ödeme sırasında kuponda aşağıdaki anahtar kelimelerden biri kullanılırsa, <b> yeni siparişte </b> müşteriye SMS göndermeyin. 
					<br />
					One keyword per line (a keyword can contain spaces).';
$_['text_sms_order_status']  = 'Sipariş durumu değişikliğinde SMS şablonu'; 
$_['text_sms_seller_status']  = 'Satıcı durumu değişikliğinde SMS şablonu'; 
$_['text_sms_order_status_help']  = 'Sipariş geçmişi sayfasında sipariş durumunu güncellediğinizde kullanılacak SMS şablonu. 
                  (you must have checked the <i>&ldquo;Notify by SMS&rdquo;</i> checkbox).
                  <br />
                  <br />
                  In addition to the variables listed above, Four other variables can be used here: <b>{default_template}</b> , <b>{order_id}</b> , <b>{order_date}</b>, <b>{comment}</b> 
                  which is the comment you write when you add history.
                  <br />
                  <br />
                  Empty text box will use default template.
                  <br />
                  <br />';
$_['text_sms_seller_status_help']  = 'Satıcı sayfasındaki satıcı durumunu güncellediğinde kullanılacak SMS şablonu. 
                  <br />
                  <br />
                  In addition to the variables listed above, Five other variables can be used here: <b>{default_template}</b> , <b>{seller_email}</b> , <b>{seller_firstname}</b>, <b>{seller_lastname}</b>, <b>{seller_nickname}</b> 
                  <br />
                  <br />
                  Empty text box will use default template.
                  <br />
                  <br />';
$_['text_add_new_fields']  = 'Yeni alanlar ekle'; 
$_['text_status']  = 'Durum'; 
$_['text_seperator']  = '───────'; 
$_['text_admin_cust_reg']  = 'Mağaza sahibi <b> "müşteri kaydında" </b> SMS şablonu'; 
$_['text_admin_sms_temp']  = 'Mağaza sahibi SMS şablonu'; 
$_['text_admin_sms_temp_help']  = 'Yukarıda listelenen değişkenlere ek olarak, varsayılan şablonu veya kompakt bir sürümünü enjekte etmek için kullanabileceğiniz iki özel değişken (<b> {default_template} </b> ve <b> {compact_default_template} </b>) vardır. varsayılan şablon (SMS boyutunu azaltır) '; 
$_['text_admin_order_status']  = 'Sipariş durumu değişikliğinde SMS şablonu'; 
$_['text_admin_order_status_help']  = 'Sipariş geçmişi sayfasında sipariş durumunu güncellediğinizde kullanılacak SMS şablonu. 
                  <br />
                  <br />
                  In addition to the variables listed above, two other variables can be used here: <b>{default_template}</b> and <b>{comment}</b>
                  which is the comment you write when you add history.
                  <br />
                  <br />
                  Empty text box will use default template.
                  <br />
                  <br />';
$_['text_add_new_fields_2']  = 'Yeni alanlar ekle'; 
$_['text_status_2']  = 'Durum'; 
$_['text_seperator_2']  = '───────'; 
$_['text_phone_num_filter']  = 'Telefon numarası filtreleme: <i><b>Starts-with</b> </i>'; 
$_['text_phone_num_filter_help']  = 'Yalnızca telefon numarası buraya girdiğiniz rakamlarla başlıyorsa SMS gönderin. <br/> 
                                Multiple patterns must be comma separated. Example: 00336,+336,06';
$_['text_filter_size']  = 'Telefon numarası filtreleme: <i> <b> Numara boyutu </b> </i>'; 
$_['text_filter_size_help']  = 'Yalnızca buraya girdiğiniz telefon numarasında x rakam varsa SMS gönderin. Örneğin: değeri 8 olarak ayarlarsanız, otomatik SMS 12345678\'e gönderilir ancak 2345678\'e gönderilmez. '; 
$_['text_phone_rewrite']  = 'Telefon numarası yeniden yazılıyor'; 
$_['text_phone_rewrite_help']  = 'SMS göndermeden önce telefon numarasını değiştirin. 
                            <br />
                            Rewriting is applied only after filtering rules are applied.';
$_['text_replace_1_occ']  = 'İlk geçtiği yeri değiştirin'; 
$_['text_pattern']  = 'Desen'; 
$_['text_by']  = 'tarafından'; 
$_['text_substitution']  = 'ikame'; 
$_['text_enable_logs']  = 'Günlükleri etkinleştir'; 
$_['text_enable_logs_help']  = 'Ayrıntılı günlükler günlük dosyasına yazdırılacaktır. Neler olup bittiğini anlamanız gerektiğinde kullanışlıdır. '; 

$_['text_sms_confirm']  = '<b> Yeni siparişte </b> telefonu onayla: <br />'. 
	'<span class = "yardım"> SMS kullanarak yeni siparişte müşteri telefon numarasını onayla </spam> '; 
$_['text_sms_confirm_per_order']  = 'Telefonu <b> her siparişte </b> onayla: <br />'. 
    '<span class = "yardım"> Telefon daha önce doğrulanmış olsa bile müşterinin telefon numarasını onayla </span> '; 
$_['text_sms_confirm_trials']  = 'Maksimum onay SMS\'i'; 
$_['text_sms_confirm_trials_help']  = 'Maksimum deneme sayısı, müşterinin onay SMS\'in kendisine yeniden gönderilmesini isteyebileceği sayılır'; 
$_['text_sms_confirm_template']  = 'SMS Onay Şablonu'; 
$_['text_sms_confirm_template_help']  = 'SMS kullanarak telefon onayı sırasında kullanılan şablon. Yalnızca <b> {firstname} </b>, <b> {lastname} </b>, <b> {phonenumber} </b> kullanabilirsiniz. <br> Ve <b> {confirm_code} </b> eklemelisiniz, bu bir zorunluluktur, böylece mesaj onay kodunu içerir '; 

$_['text_tab_supported']  = 'Ağ geçitleri'; 
$_['text_supported_providers']  = 'Tüm ülkelerdeki birçok SMS servis sağlayıcısını destekliyoruz, örneğin'; 
$_['text_supported_providers_help']  = 'Yukarıdakilerden herhangi bir hizmet sağlayıcıyı veya başka bir hizmet sağlayıcıyı etkinleştirmek için yardıma ihtiyacınız olursa, lütfen müşteri hizmetleri temsilcilerimizden biriyle görüşün.'; 



$_['activation_message_template']  = 'Aktivasyon Mesajı Şablonu'; 
$_['activation_message_template_note']  = '<br /> <b> {activationToken} </b> kullanabilirsiniz'; 

$_['code_settings']  = 'Aktivasyon Kodu Ayarları'; 
$_['code_length']  = 'Kod Uzunluğu'; 
$_['code_type']  = 'Kod Türü'; 
$_['code_alphanumeric']  = 'Alfasayısal'; 
$_['code_numeric']  = 'Sayılar'; 

$_['text_seller_status_notification_header']  = 'Sayın,'; 
$_['text_seller_status_notification_body_prefix']  = 'Hesap durumunuz'; 
$_['text_seller_status_notification_footer']  = ''; 
