<?php
// = 
// Category & Product-Based Shipping v210.1
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
// = 

$name  = 'Kategori ve Ürün Bazlı Nakliye'; 

//------------------------------------------------------------------------------
// Heading
//------------------------------------------------------------------------------
$_['heading_title']						 = $name; 
$_['text_shipping']                      = 'Nakliye'; 

// Backup/Restore Settings
$_['button_backup_settings']			 = 'Yedekleme ayarları'; 
$_['text_this_will_overwrite_your']		 = 'Bu, önceki yedekleme dosyanızın üzerine yazacaktır. Devam et?'; 
$_['text_backup_saved_to']				 = 'Yedekleme kaydedildi'; 
$_['text_view_backup']					 = 'Yedeği Görüntüle'; 
$_['text_download_backup_file']			 = 'Yedekleme Dosyasını İndir'; 

$_['button_restore_settings']			 = 'Ayarları eski haline getir'; 
$_['text_restore_from_your']			 = 'Sizden geri yükleyin'; 
$_['text_automatic_backup']				 = '<b> Bu sayfa yüklendiğinde oluşturulan Otomatik Yedekleme </b>'; 
$_['text_manual_backup']				 = '<b> Manuel Yedekleme </b>, "Yedekleme Ayarları" tıklandığında oluşturulur'; 
$_['text_backup_file']					 = '<b> Yedekleme Dosyası: </b>'; 
$_['button_restore']					 = 'Onarmak'; 
$_['text_this_will_overwrite_settings']	 = 'Bu, mevcut tüm ayarların üzerine yazacaktır. Devam et?'; 
$_['text_restoring']					 = 'Geri yükleniyor ...'; 
$_['error_invalid_file_data']			 = 'Hata: geçersiz dosya verisi'; 
$_['text_settings_restored']			 = 'Ayarlar başarıyla geri yüklendi'; 

// Buttons
$_['button_expand_all']					 = 'Hepsini genişlet'; 
$_['button_collapse_all']				 = 'Hepsini Daralt'; 
$_['help_expand_all']					 = 'Bu tablodaki tüm satırları genişletmek için tıklayın.'; 
$_['help_collapse_all']					 = 'Bu tablodaki tüm satırları daraltmak için tıklayın.'; 

//------------------------------------------------------------------------------
// Extension Settings
//------------------------------------------------------------------------------
$_['tab_extension_settings']			 = 'Genel Ayarlar'; 
$_['heading_extension_settings']		 = 'Genel Ayarlar'; 

$_['entry_status']						 = 'Durum'; 
$_['help_status']						 = 'Bir bütün olarak yöntemin durumunu ayarlayın.'; 

$_['entry_sort_order']					 = 'Sıralama düzeni'; 
$_['help_sort_order']					 = 'Diğer gönderim yöntemlerine göre yöntemin sıralama düzeni.'; 

$_['entry_heading']						 = 'Başlık'; 
$_['help_heading']						 = 'Bu gönderim seçeneklerinin görüneceği başlık. HTML desteklenir. <br /> <br /> Hesaplanan değeri görüntülemek için kısa kodları [mesafe], [posta kodu], [miktar], [toplam], [hacim] veya [ağırlık] kullanın. <br /> < br /> Bu başlık '; 

$_['entry_tax_class_id']				 = 'Varsayılan Vergi Sınıfı'; 
$_['help_tax_class_id']					 = 'Masraflara uygulanan varsayılan vergi sınıfını ayarlayın. "Vergi Sınıfı" kuralı olmayan herhangi bir ücret, bu vergi sınıfını kullanacaktır. '; 

$_['entry_distance_calculation']		 = 'Mesafe Hesaplaması'; 
$_['help_distance_calculation']			 = 'Mesafelerin hesaplanma şeklini seçin.'; 
$_['text_driving_distance']				 = 'Sürüş mesafesi'; 
$_['text_straightline_distance']		 = 'Düz Hat Mesafesi'; 

$_['entry_distance_units']				 = 'Uzaklık birimleri'; 
$_['help_distance_units']				 = 'Mesafe karşılaştırmaları için birim tipini seçin.'; 
$_['text_miles']						 = 'Miles'; 
$_['text_kilometers']					 = 'Kilometre'; 

$_['entry_testing_mode']				 = 'Test Modu'; 
$_['help_testing_mode']					 = 'Ön uçta işler beklendiği gibi çalışmıyorsa test modunu etkinleştirin.'; 

// Admin Panel Settings
$_['heading_admin_panel_settings']		 = 'Yönetici Paneli Ayarları'; 

$_['entry_autosave']					 = 'Otomatik Kaydetme'; 
$_['help_autosave']						 = 'Ayarların otomatik olarak kaydedilip kaydedilmeyeceğini seçin.'; 

$_['entry_autocomplete_preloading']		 = 'Otomatik Tamamlama Ön Yükleme'; 
$_['help_autocomplete_preloading']		 = 'Sayfa yüklendiğinde otomatik tamamlama veritabanının önceden yüklenip yüklenmeyeceğini veya öğeleri veritabanından dinamik olarak çekip çekilmeyeceğini seçin. Ön yükleme daha hızlıdır, ancak büyük veritabanlarında çok uzun sürebilir. '; 

$_['entry_display']						 = 'Varsayılan Yönetici Ekranı'; 
$_['help_display']						 = 'Sayfa yüklendiğinde tablo satırlarının varsayılan olarak görüntülenme şeklini ayarlayın. Sayfa yükleme hızını artırmak için "Daraltılmış" ı seçin. '; 
$_['text_expanded']						 = 'Genişletilmiş'; 
$_['text_collapsed']					 = 'Daraltılmış'; 

$_['entry_tooltips']					 = 'Araç ipuçları'; 
$_['help_tooltips']						 = 'Her ayar için görüntülenen araç ipuçlarını gizlemeyi devre dışı bırakın.'; 

//------------------------------------------------------------------------------
// Charges
//------------------------------------------------------------------------------
$_['tab_charges']						 = 'Ücretler'; 
$_['help_charges']						 = 'Varsayılan olarak, ücretler her zaman mevcuttur. Bir ücreti coğrafi bölge veya müşteri grubu gibi belirli kriterlere göre kısıtlamak için "Kurallar" ı kullanın. '; 
$_['heading_charges']					 = 'Ücretler'; 

$_['column_action']						 = 'Aksiyon'; 
$_['column_group']						 = 'Grup'; 
$_['column_title']						 = 'Başlık'; 
$_['column_charge']						 = 'Şarj etmek'; 
$_['column_rules']						 = 'Kurallar'; 

$_['text_expand']						 = 'Genişlet (veya daraltılmış bir satırdaki boş alanı çift tıklayın)'; 
$_['text_collapse']						 = 'Çöküş'; 
$_['text_copy']							 = 'Kopyala'; 
$_['text_delete']						 = 'Sil'; 

$_['help_charge_group']					 = 'Bir ücretlendirme grubu ayarlamak için 1 veya 2 harf veya sayı (büyük / küçük harf duyarlı değildir) kullanın. Aynı grubun ücretleri "Ücret Kombinasyonları" sekmesinde birleştirilebilir. <br /> <br /> Negatif bir değer girerseniz, ücret devre dışı bırakılacaktır. '; 

$_['text_admin_reference']				 = 'Yönetici Referansı'; 
$_['help_admin_reference']				 = 'Bu ücret için sadece dahili olarak yönetici tarafından görülebilecek bir referans adı girin.'; 
$_['placeholder_charge_title']			 = 'Başlık'; 
$_['help_charge_title']					 = 'Müşteriye gösterilen başlık. HTML desteklenir. <br /> <br /> Hesaplanan değeri görüntülemek için kısa kodlar [mesafe], [posta kodu], [miktar], [toplam], [hacim] veya [ağırlık] kullanın. <br /> < br /> Bu başlık '; 

$_['text_simple_charges']				 = 'Basit Ücretler'; 
$_['text_flat_charge']					 = 'Sabit Ücret'; 
$_['text_per_item_charge']				 = 'Ürün Başına Ücret'; 
$_['text_bracket_charges']				 = 'Parantez Ücretleri'; 
$_['text_distance']						 = 'Mesafe'; 
$_['text_price']						 = 'Fiyat'; 
$_['text_quantity']						 = 'Miktar'; 
$_['text_total']						 = 'Toplam'; 
$_['text_volume']						 = 'Ses'; 
$_['text_weight']						 = 'Ağırlık'; 
$_['text_other_shipping_methods']		 = 'Diğer Nakliye Yöntemleri'; 
$_['help_charge_type']					 = 'Ödemenin temelini seçin: <br /> <br /> & bull; Basit bir ücret (sabit ücret veya ürün başına ücret). <br /> <br /> & bull; Parantez bazında bir ücret (miktar, toplam, ağırlık vb.). <br /> <br /> & bull; Başka bir gönderim yöntemi kullanılarak hesaplanan ücret. '; 
$_['help_charge_charges']				 = "Basit Masraflar" .'için, sabit ücreti veya öğe başına ücreti (5,00 gibi) girin. <Hr /> "Grup Ücretleri" için, köşeli parantezleri virgülle veya yeni satırlarla ayırın ve bunları şu biçimde girin: <br /> <br /> <b> Nereden - Nereye'; 

$_['button_add_charge']					 = 'Ücret Ekle'; 

//------------------------------------------------------------------------------
// Rules
//------------------------------------------------------------------------------
$_['text_choose_rule_type']				 = '--- Kural türünü seçin ---'; 
$_['help_rules']						 = 'Seçenekler listesinden bir kural türü seçin. Bir kural türü seçtikten sonra, o belirli kural türü hakkında daha fazla bilgi için oluşturulan giriş alanının üzerine gelin. '; 

$_['text_of']							 = 'nın-nin'; 
$_['text_is']							 = 'dır-dir'; 
$_['text_is_not']						 = 'değil'; 
$_['text_is_on_or_after']				 = 'sonra'; 
$_['text_is_on_or_before']				 = 'önce'; 

$_['button_add_rule']					 = 'Kural Ekle'; 
$_['help_add_rule']						 = 'Ücretlendirmenin etkinleştirilmesi için tüm kuralların doğru olması gerekir. Farklı türdeki kurallar, VE mantığı kullanılarak ve aynı türdeki kurallar VEYA mantığı kullanılarak birleştirilecektir. Örneğin, şu kuralları eklerseniz: <br /> <br /> & bull; Müşteri Grubu Toptan Satış <br /> & bull; Geo Zone, Amerika Birleşik Devletleri <br /> & bull; Geo Zone Kanada <br /> <br /> ise, müşteri Toptan Satış grubunun bir parçası olduğunda <b> VE </b> konum Amerika Birleşik Devletleri\'nde <b> VEYA </b> olduğunda ücret etkinleştirilecektir Kanada.'; 

$_['text_adjustments']					 = 'Ayarlamalar'; 
$_['text_adjust']						 = 'Ayarla'; 
$_['text_charge_adjustment']			 = 'ücret ayarı'; 
$_['text_final_charge']					 = 'son ödeme'; 
$_['text_cart']							 = 'araba'; 
$_['text_item']							 = 'öğe'; 
$_['text_cumulative']					 = 'Kümülatif Parantezler'; 
$_['text_enabled_successive_brackets']	 = 'etkinleştirildi' ;
$_['text_max']							 = 'Maksimum'; 
$_['text_min']							 = 'Minimum'; 
$_['text_round']						 = 'Yuvarlak'; 
$_['text_to_the_nearest']				 = 'en yakınına'; 
$_['text_up_to_the_nearest']			 = 'en yakınına kadar'; 
$_['text_down_to_the_nearest']			 = 'en yakına'; 
$_['text_setting_override']				 = 'Geçersiz Kılmayı Ayarlama'; 
$_['text_tax_class']					 = 'Vergi Sınıfı'; 
$_['text_total_value']					 = 'Toplam değer'; 
$_['text_prediscounted_subtotal']		 = 'Ön İndirimli Alt Toplam'; 
$_['text_nondiscounted_subtotal']		 = 'İskonto Edilmemiş Alt Toplam'; 
$_['text_shipping_cost_subtotal']		 = 'Nakliye maliyeti'; 
$_['text_taxed_subtotal']				 = 'Vergilendirilmiş Alt Toplam'; 
$_['text_total_subtotal']				 = 'Toplam'; 

$_['help_adjust_comparison']			 = '&Boğa; Ayarlanacak değer türünü seçin. Son ücret ayarlamaları, ücret hesaplandıktan sonra ve Maksimum veya Minimum kriterleri kontrol edilmeden önce gerçekleşir. <br /> <br /> & bull; Diğer tüm ayarlamalar, hesaplamalar yapılmadan önce gerçekleşir. "Alışveriş sepeti düzenlemeleri" tüm alışveriş sepetine ve "öğe ayarlamaları" her bir öğeye ayrı ayrı uygulanacaktır. <br /> <br /> & bull; Örneğin, alışveriş sepeti 1 kg olan bir öğe ve 2 kg olan bir öğe içeriyorsa, 1.00 "alışveriş sepeti" ayarlaması, toplam ağırlık şu şekilde sonuçlanır: <br /> <br /> (1 + 2 ) + 1.00 ';
$_['help_adjust']						 = 'Değerin ayarlanacağı pozitif veya negatif bir değer (5,00 veya -3,50 gibi) veya yüzde (  15 veya -  10 gibi) girin.'; 
$_['help_cumulative']					 = 'Kümülatif parantez ücretleri, birbirini izleyen her parantezin birbirine ekleneceği anlamına gelir. Örneğin, 0-1 kg için 2,00 ABD doları ve 1-2 kg için 3,00 ABD doları ücret alırsanız, 1,5 kg olan bir siparişte 2,00 ABD doları + 3,00 ABD doları ücret alınır.'; 
$_['help_max']							 = 'Ücretin her zaman bu maksimum değerden fazla olmaması için sabit bir değer (49.99 gibi) girin.'; 
$_['help_min']							 = 'Ücretin her zaman en azından bu minimum değer olmasını sağlamak için düz bir değer (10.00 gibi) girin.'; 
$_['help_round']						 = 'Hesaplamalar yapıldıktan sonra yükü yuvarlamak için bir değer girin (0,25 gibi). Ayrıca isteğe bağlı olarak her zaman yukarı veya aşağı yuvarlanıp yuvarlanmayacağını seçin. '; 
$_['help_setting_override']				 = 'Geçersiz kılmak için bir mağaza ayarı seçin. Şu anda, yalnızca config_address ayarını geçersiz kılabilirsiniz. Mesafe hesaplamaları için başlangıç noktası olarak kullanılan konumu değiştirmek istiyorsanız bunu kullanın. '; 
$_['help_setting_override_value']		 = 'Ayarı geçersiz kılmak için değeri girin.'; 
$_['help_tax_class']					 = 'Bu ücrete uygulanacak bir vergi sınıfı seçin.'; 
$_['help_total_value']					 = 'Sepetin Alt Toplamı normalde toplamı içeren hesaplamalar için kullanılır. Bunu değiştirmek için aşağıdakilerden birini kullanın: <br /> <br /> & bull; <b> Önceden İndirimli Alt Toplam: </b> tüm ürünlerin alt toplamı & # 39; Özel veya İndirimli fiyatları göz ardı ederek orijinal fiyatlar <br /> <br /> & bull; <b> İndirimli Olmayan Alt Toplam: </b> Özel veya İndirimli fiyatlandırmalı ürünler dahil olmayan alt toplam <br /> <br /> & bull; <b> Vergilendirilmiş Alt Toplam: </b> ürünlerdeki vergiler dahil alt toplam <br /> <br /> & bull; <b> Toplam: </b> "Gönderim" Sipariş Toplamının göreli Sıralama Düzeni\'ndeki toplam <br /> <br /> Nakliye gerektirmeyen ürünler, alt toplama dayalı değerlere dahil DEĞİLDİR. ' ; 

$_['text_cart_criteria']				 = 'Sepet / Ürün Kriterleri'; 
$_['text_length']						 = 'Uzunluk'; 
$_['text_width']						 = 'Genişlik'; 
$_['text_height']						 = 'Yükseklik'; 
$_['text_stock']						 = 'Stok'; 
$_['text_eligible_item_comparisons']	 = 'uygun ürün karşılaştırmaları'; 
$_['text_of_cart']						 = 'sepet'; 
$_['text_of_any_item']					 = 'herhangi bir öğeden'; 
$_['text_of_every_item']				 = 'her öğeden'; 
$_['text_entire_cart_comparisons']		 = 'tüm alışveriş sepeti karşılaştırmaları'; 
$_['text_of_entire_cart']				 = 'tüm alışveriş sepetinin'; 
$_['text_of_any_item_in_entire_cart']	 = 'Sepetin tamamındaki herhangi bir öğenin'; 
$_['text_of_every_item_in_entire_cart']	 = 'Sepetteki her öğeden'; 
$_['text_items']						 = 'öğeler'; 
$_['help_cart_criteria_comparisons']	 = ' 
	<b>of cart</b>  = değeri bir bütün olarak alışveriş sepetiyle karşılaştırın <br /> <br /> 
	<b>of any item</b>  = değeri her bir öğeyle ayrı ayrı karşılaştırın; uygun olanlar hesaplamaya dahil edilecek ve yok sayılmayacaklar <br /> <br /> 
	<b>of every item</b>  = değeri her bir öğeyle ayrı ayrı karşılaştırın; herhangi biri uygun değilse, ücret devre dışı bırakılacaktır 
	<hr />
	Values are normally compared only against eligible items (i.e. those that qualify for the charge based on other rules). To compare values against all items in the cart, including ineligible ones, choose a comparison containing "entire cart".';
$_['help_cart_criteria']				 ='Sepetin veya tek tek öğelerin karşılaması gereken minimum bir değer (5,00 gibi) veya bir aralık (3,3-10,5 gibi) girin. <br /> <br /> Tek bir değer <b> en az </b> anlamına gelir bu değer. Örneğin, 5,00 ölçütü belirlerseniz, 5,00 veya üzeri herhangi bir değer uygun olacaktır. <br /> <br /> Aralıklar, son değerleri içerir. Birden çok aralığı virgül kullanarak ayırın. Tam bir değer belirtmek için 5,00-5,00 gibi bir aralık kullanın. '; 

$_['text_datetime_criteria']			 = 'Tarih / Saat Kriterleri'; 
$_['text_day']							 = 'Haftanın günü'; 
$_['text_sunday']						 = 'Pazar'; 
$_['text_monday']						 = 'Pazartesi'; 
$_['text_tuesday']						 = 'Salı'; 
$_['text_wednesday']					 = 'Çarşamba'; 
$_['text_thursday']						 = 'Perşembe'; 
$_['text_friday']						 = 'Cuma'; 
$_['text_saturday']						 = 'Cumartesi'; 
$_['text_date']							 = 'Tarih'; 
$_['text_time']							 = 'Zaman'; 
$_['help_day']							 = 'Bu ücretin aktif olduğu haftanın gününü seçin. Birden çok günde aktif olmasını istiyorsanız birden çok kural oluşturun. '; 
$_['help_date']							 = 'YYYY-AA-GG'; 
$_['help_time']							 = 'HH: MM (PM için 12-23)'; 
$_['help_datetime_criteria']			 = 'Şarjın ne zaman başlayacağını veya biteceğini seçin. Tarihler için YYYY-AA-GG biçimini ve saatler için SS: DD biçimini kullanın. PM saatleri için 12-23\'ü kullanın. '; 

$_['text_location_criteria']			 = 'Yer Kriterleri'; 
$_['text_city']							 = 'Kent'; 
$_['text_warehouse']					 = 'Depo'; 
$_['text_distance']						 = 'Mesafe'; 
$_['text_geo_zone']						 = 'Geo Zone'; 
$_['text_everywhere_else']				 = 'Başka heryer'; 
$_['text_location_comparison']			 = 'Konum Karşılaştırması'; 
$_['text_payment_address']				 = 'Fatura adresi'; 
$_['text_shipping_address']				 = 'Gönderi Adresi'; 
$_['text_postcode']						 = 'Posta kodu'; 
$_['help_city']							 = 'Tam bir şehir adı girin, örneğin: <br /> <br /> New York <br /> <br /> veya birden çok şehir adı, örneğin <br /> <br /> New York, New York City, Londra <br /> <br /> Müşteri tarafından girilen şehir bu değerlerle karşılaştırılacaktır (büyük / küçük harfe duyarlı değildir). '; 
$_['help_distance']						 = 'Müşterinin mesafesinin mağaza konumundan olması gereken bir maksimum değer (5,00 gibi) veya bir aralık (3,3-10,5 gibi) girin. Örneğin, 5,00 girerseniz ve Mesafe Birimleri "Mil" olarak ayarlandıysa, 5 mil içindeki herhangi bir konum uygun olacaktır. <br /> <br /> Aralıklar son değerleri içerir. Birden çok aralığı virgül kullanarak ayırın. <br /> <br /> Konum belirlemeleri, Google Geocoding API kullanılarak yapılır. Bu API, 24 saatte bir 2.500 istekle sınırlıdır. Bundan daha fazlasına ihtiyacınız varsa, her 24 saatte 100.000 isteğe izin veren İşletmeler için Google Haritalar API\'sine kaydolmayı düşünün. '; 
$_['help_geo_zone']						 = 'Bir coğrafi bölge seçin veya ücreti coğrafi bölge dışında herhangi bir yerle sınırlamak için "Başka Her Yer" seçeneğini seçin.'; 
$_['help_location_comparison']			 = 'Varsayılan olarak, gönderim yöntemleri konum kriterlerini gönderim adresiyle karşılaştırır. Bu davranışı değiştirmek için bu ayarı kullanın. '; 
$_['help_postcode']						 = 'Tek bir posta kodu veya önek (AB1 gibi) veya bir aralık (91000-94499 gibi) girin. Aralıklar, son değerleri içerir. Virgül kullanarak birden çok posta kodunu ayırın. '; 

$_['text_order_criteria']				 = 'Sipariş Kriterleri'; 
$_['text_coupon']						 = 'Kupon'; 
$_['text_currency']						 = 'Para birimi'; 
$_['text_customer']						 = 'Müşteri'; 
$_['text_customer_group']				 = 'Müşteri grubu'; 
$_['text_guests']						 = 'Misafirler'; 
$_['text_language']						 = 'Dil'; 
$_['text_past_orders']					 = 'Geçmiş Siparişler'; 
$_['text_days']							 = 'Günler'; 
$_['text_payment_extension']			 = 'Ödeme şekli'; 
$_['text_shipping_extension']			 = 'Nakliye Yöntemi'; 
$_['text_shipping_rate']				 = 'Nakliye Ücreti'; 
$_['text_store']						 = 'Mağaza'; 
$_['help_coupon']						 = 'Belirli bir kupon kodu girin veya yalnızca bir kuponun varlığını kontrol etmek için bu alanı boş bırakın. <br /> Örneğin: <br /> <br /> <b> Kupon ABC123’dür </b> <br / > Harcama, sepete ABC123 uygulandığında etkin olacaktır <br /> <br /> <b> Kupon ABC123 değildir </b> <br /> ABC123 <b> olmadığında </ b> sepete uygulandı <br /> <br /> <b> Kupon __________ </b> <br /> Alışveriş sepetine herhangi bir kupon uygulandığında ücret aktif olacaktır <br /> <br /> < b> Kupon __________ değil </b> <br /> Ödeme, bir kupon sepete <b> uygulanmadığında </b> '; 
$_['help_currency']						 = 'Bir para birimi seçin. Birden fazla para birimi kuralı eklenirse, ücret, para birimi dönüştürmeleriniz kullanılarak varsayılan para biriminden uygun şekilde dönüştürülür. <br /> <br /> Yabancı para birimi tutarında bir masraf değeri girmek istiyorsanız, tek bir para birimi kuralı ekleyin o para birimi seçilerek. '; 
$_['help_customer']						 = 'Otomatik tamamlama alanına bir müşteri adı girin. Customer_id değerini köşeli parantez [ve] içinde bıraktığınızdan emin olun, çünkü bu karşılaştırma amacıyla kullanıldı. '; 
$_['help_customer_group']				 = 'Bir müşteri grubu seçin veya ödemeyi bir hesapta oturum açmamış müşterilerle sınırlamak için "Misafirler" seçeneğini seçin.'; 
$_['help_language']						 = 'Bir dil seç.'; 
$_['help_past_orders_dropdown']			 = 'Müşterinin geçmiş siparişlerini nasıl karşılaştıracağınızı seçin: Günler, Miktar veya Toplam. Örneğin: <br /> <br /> & bull; Ücreti, son 30 gün içinde sipariş veren müşterilere uygulamak için "Günler" i seçin ve 0-30 girin <br /> <br /> & bull; Ücreti geçmiş siparişi en az 2 olan müşterilere uygulamak için "Miktar" ı seçin ve 2 <br /> <br /> & bull; Ücreti, geçmiş siparişlerinin toplamı 500 ila 1000 ABD Doları arasında olan müşterilere uygulamak için "Toplam" ı seçin ve 500-1000 \'girin'; 
$_['help_past_orders']					 = 'Müşterinin geçmiş siparişlerinin karşılaması gereken minimum bir değer (5 gibi) veya aralığı (50,00-100,00 gibi) girin. Tek bir değer, bu değeri <b> en az </b> gösterir. <br /> <br /> Aralıklar, son değerleri içerir. Birden çok aralığı virgül kullanarak ayırın. Tam bir değer belirtmek için 50,00-50,00 gibi bir aralık kullanın. '; 
$_['help_payment_extension']			 = 'Bu ücretin / indirimin geçerli olduğu bir ödeme yöntemi seçin.'; 
$_['help_shipping_cost']				 = 'Gönderim maliyetinin karşılaması gereken minimum bir değer (5,00 gibi) veya aralığı (30,00-70,00 gibi) girin. Tek bir değer, bu değeri <b> en az </b> gösterir. Örneğin, 5,00 ölçütü belirlerseniz, müşteri maliyeti 5,00 veya daha fazla olan bir gönderim seçeneği seçtiğinde ücret uygulanır. <br /> <br /> Aralıklar, son değerleri içerir. Birden çok aralığı virgül kullanarak ayırın. Tam bir değer belirtmek için 5,00-5,00 \'gibi bir aralık kullanın'; 
$_['help_shipping_extension']			 = 'Bu ücretin / indirimin geçerli olduğu bir gönderim yöntemi seçin.'; 
$_['help_shipping_rate']				 = 'Bir gönderim ücreti başlığı (Öncelikli Posta gibi) veya virgülle ayrılmış birden çok başlık (Öncelikli Posta, Hızlı Posta gibi) girin. Müşteri tarafından seçilen nakliye ücreti bu değerlerle karşılaştırılacaktır (büyük / küçük harfe duyarlı değildir). '; 
$_['help_store']						 = 'Çok mağazalı kurulumunuzdan bir mağaza seçin.'; 

$_['text_product_criteria']				 = 'Ürün Kriterleri'; 
$_['text_attribute']					 = 'Öznitelik'; 
$_['text_attribute_group']				 = 'Öznitelik Grubu'; 
$_['text_category']						 = 'Kategori'; 
$_['text_manufacturer']					 = 'Marka'; 
$_['text_option']						 = 'Seçenek'; 
$_['text_product']						 = 'Ürün'; 

$_['help_attribute']					 = 'Otomatik tamamlama alanına bir öznitelik adı girin. Karşılaştırma amacıyla kullanıldığından öznitelik_kimliğini köşeli parantez [ve] içinde bıraktığınızdan emin olun. Daha karmaşık gereksinimler için Ürün Grupları özelliğini kullanın. '; 
$_['help_attribute_group']				 = 'Otomatik tamamlama alanına bir öznitelik grubu adı girin. Karşılaştırma amacıyla kullanıldığından, özellik_grubu_kimliğini köşeli parantez [ve] içinde bıraktığınızdan emin olun. Daha karmaşık gereksinimler için Ürün Grupları özelliğini kullanın. '; 
$_['help_category']						 = 'Otomatik tamamlama alanına bir kategori adı girin. Karşılaştırma amacıyla kullanıldığından, category_id\'yi köşeli parantez [ve] içinde bıraktığınızdan emin olun. Daha karmaşık gereksinimler için Ürün Grupları özelliğini kullanın. '; 
$_['help_manufacturer']					 = 'Otomatik tamamlama alanına bir üretici adı girin. Karşılaştırma amacıyla kullanıldığından, üretici_kimliğini köşeli parantez [ve] içinde bıraktığınızdan emin olun. Daha karmaşık gereksinimler için Ürün Grupları özelliğini kullanın. '; 
$_['help_option']						 = 'Otomatik tamamlama alanına bir seçenek girin. Karşılaştırma amacıyla kullanıldığından, option_id\'yi köşeli parantez [ve] içinde bıraktığınızdan emin olun. '; 
$_['help_option_value']					 = 'Bir değer (Küçük gibi) veya aralık (25-50 gibi) girin. Değer ile veya aralık dahilinde belirtilen seçeneğe sahip ürünler bu ücret için uygun olacaktır. Herhangi bir seçenek değerine izin vermek için bu alanı boş bırakın. <br /> <br /> Seçenek değeri bir tire içerebiliyorsa, aralıklar için - yerine :: kullanın. Aralıklar, son değerleri içerir. Birden çok aralığı virgül kullanarak ayırın. '; 
$_['help_product']						 = 'Otomatik tamamlama alanına bir ürün adı girin. Karşılaştırma amacıyla kullanıldığından, product_id\'yi köşeli parantez [ve] içinde bıraktığınızdan emin olun. Daha karmaşık gereksinimler için Ürün Grupları özelliğini kullanın. '; 

$_['text_product_group']				 = 'Ürün grubu'; 
$_['text_cart_has_items_from']			 = 'Alışveriş sepetinden öğeler var'; 
$_['text_any']							 = 'hiç'; 
$_['text_all']							 = 'herşey'; 
$_['text_not']							 = 'değil'; 
$_['text_only_any']						 = 'yalnızca herhangi biri'; 
$_['text_only_all']						 = "yalnızca tümü"; 
$_['text_none_of_the']					 = 'hiçbiri'; 
$_['text_members_of']					 = 'üyeleri'; 
$_['help_product_group']				 = 'Listeden bir ürün grubu seçin. Birden çok Ürün Grubu kuralı, diğer kuralların aksine AND mantığı kullanılarak birleştirilir. <br /> <br /> Not: "Ürün Grupları" sekmesinde yeni gruplar oluşturulabilir. Ürün Grupları, bir "Ürün Grubu" kuralı eklenmeden <b> önce </b> oluşturulmalıdır. '; 
$_['help_product_group_comparison']		 = ' 
	<b>any</b>  = alışveriş sepetinde grup üyelerinden en az bir öğe var ve üyelerden olmayan öğeler olabilir <br /> <br /> 
	<b>all</b>  = alışveriş sepetinde tüm grup üyelerinden öğeler var ve üyelerden olmayan öğeler olabilir <br /> <br /> 
	<b>not</b>  = alışveriş sepetinde grup üyelerinden <b> olmayan </b> en az bir öğe var ve üyelerin öğeleri olabilir <br /> <br /> 
	<b>only any</b>  = alışveriş sepetinde grup üyelerinden <b> yalnızca </b> öğe var ve üyelerden olmayan öğeler <b> olamaz </b>. <br /> <br /> 
	<b>only all</b>  = alışveriş sepetinde tüm grup üyelerinden <b> yalnızca </b> öğe var ve üyelerden olmayan öğeler <b> olamaz </b> 
	<b>none</b>  = alışveriş sepetinde herhangi bir grup üyesinden <b> hiç </b> öğe yok 
';

$_['text_other_product_data']			 = 'Diğer Ürün Verileri'; 
$_['help_other_product_data_column']	 = 'Karşılaştırma için hangi veritabanı sütununu kullanacağınızı seçin.'; 
$_['help_other_product_data']			 = 'Bu kuralın iki işlevi vardır: <br /> <br /> 1. Bir değer (ABC001X gibi) veya bir aralık (500-1000 gibi) girin. Bu değerle eşleşen veya bu aralıkta bir değere sahip ürünler, bu ücret için uygun olacaktır. <br /> <br /> Ürün verileri kısa çizgiler içeriyorsa, aralıklar için - yerine :: kullanın. Aralıklar, son değerleri içerir. Birden çok aralığı virgül kullanarak ayırın. <br /> <br /> Örneğin, veritabanı sütunu için "model" i seçip alana "Model XYZ" (tırnak işaretleri olmadan) girerseniz, eşleşen modeli olan tüm ürünler bu ücretin hesaplanması için kullanılabilir. <br /> <br /> 2. Değeri boş bırakırsanız, bu alan her bir ürünün ücretini hesaplamak için kullanılacaktır. Örneğin, Miktar ücret türü kullanıyorsanız ve "sku" yu seçerseniz, her ürün için SKU verileri miktar parantezleri olarak hesaplanacaktır. <br /> <br /> Bu örnekte, bir ürünün değeri Bu alanda 5,00 / 1, ücreti öğe başına 5,00 $ olacaktır. Bu alanda başka bir ürün 7,50\'ye sahipse, ücreti 7,50 dolar olur. Bu masraflar, müşteriye gösterilen son ücret olarak toplanacaktır. '; 

$_['text_rule_sets']					 = 'Kural Kümeleri'; 
$_['text_rule_set']						 = 'Kural Kümesi'; 
$_['help_rule_set']						 = 'Listeden bir kural grubu seçin. "Kural Kümeleri" sekmesinde yeni kural kümeleri oluşturulabilir. <br /> <br /> Kural kümesindeki tüm kurallar tıpkı diğer kurallar gibi uygulanacaktır, bu nedenle farklı türlerdeki kuralların VE mantığı kullanılarak birleştirileceğini unutmayın. ve OR mantığını kullanan aynı türdeki kurallar. <br /> <br /> Not: "Kural Kümesi" kuralı eklemeden önce kural kümeleri oluşturulmalıdır. '; 

//------------------------------------------------------------------------------
// Charge Combinations
//------------------------------------------------------------------------------
$_['tab_charge_combinations']			 = 'Ücret Kombinasyonları'; 
$_['help_charge_combinations']			 = 'Ücret kombinasyonları, "Masraflar" alanında oluşturulan masrafları birleştirmenize olanak tanır. Kullanılırsa, nakliye seçenekleri olarak yalnızca ücret kombinasyonları görüntülenecektir. '; 
$_['heading_charge_combinations']		 = 'Ücret Kombinasyonları'; 
$_['button_add_combination']			 = 'Kombinasyon Ekle'; 

$_['column_sort_order']					 = 'Sıralama düzeni'; 
$_['column_title_combination']			 = 'Başlık Kombinasyonu'; 
$_['column_combination_formula']		 = 'Kombinasyon Formülü'; 

$_['text_single_title']					 = 'Tek Başlık'; 
$_['text_combined_title_no_prices']		 = 'Birleşik Başlık, Fiyat Yok'; 
$_['text_combined_title_with_prices']	 = 'Fiyatlarla Birlikte Başlık'; 

$_['help_combination_sort_order']		 = 'Masraf kombinasyonlarının müşteriye nakliye seçenekleri olarak görüneceği sıralama düzeni.'; 
$_['help_combination_title']			 = '&Boğa; <b> Tek Başlık </b>, ilk geçerli başlığın, gönderim seçeneği başlığı olarak gösterileceği anlamına gelir. Bunu seçerseniz, formüldeki tüm masraflar için aynı başlığı kullanmanız gerekir. <br /> <br /> & bull; <b> Birleşik Başlık </b>, tüm ödeme başlıklarının bir listede birleştirileceği anlamına gelir, bu nedenle gönderim seçeneği "Kategori A Ücreti + Kategori B Ücreti" gibi görünür <br /> <br /> & bull; <b> Fiyatlarla </b>, başlığın her bir ücretin fiyatını içereceği anlamına gelir, bu nedenle gönderim seçeneği "Kategori A Ücreti (5,00 $) + Kategori B Ücreti (7,00 $)" gibi görünecektir'; 
$_['help_combination_formula']			 = 'Masrafların nasıl birleştirileceğine ilişkin bir formül girin. <br /> <br /> <b> SUM </b>'; 
$_['placeholder_formula']				 = 'TOPLA (), ORTALAMA (), MAKS (), MİN ()'; 

//------------------------------------------------------------------------------
// Product Groups
//------------------------------------------------------------------------------
$_['tab_product_groups']				 = 'Ürün Grupları'; 
$_['help_product_groups']				 = 'Ürün grupları, bir grup kategori, marka, ürün, özellik ve / veya seçenek temelinde ücretleri kısıtlamak için kullanılır.'; 
$_['heading_product_groups']			 = 'Ürün Grupları'; 
$_['button_add_product_group']			 = 'Ürün Grubu Ekle'; 

$_['column_group_members']				 = 'Grup üyeleri'; 
$_['column_']							 = ''; 

$_['text_autocomplete_from']			 = 'Otomatik Tamamlama Yeri'; 
$_['text_all_database_tables']			 = 'Herşey'; 

$_['help_product_group_sort_order']		 = 'Ürün grubunun Kural olarak seçildiğinde görüneceği sıralama düzeni.'; 
$_['help_product_group_name']			 = 'Ürün grubu için Kural olarak seçildiğinde görüntülenen ad.'; 
$_['help_autocomplete_from']			 = 'Otomatik tamamlama alanının öğeleri tüm veritabanı tablolarından mı yoksa belirli olanlardan mı (kategoriler, ürünler, vb.) Çekeceğini seçin.'; 
$_['placeholder_typeahead']				 = 'Bir isim yazmaya başla'; 
$_['help_typeahead']					 = 'Otomatik tamamlama alanına bir ad yazmaya başlayın. 15\'ten fazla giriş bulunursa, liste 100 girişe kadar kaydırılabilir. <br /> <br /> İlk girişi eklemek ve giriş alanını temizlemek için "enter" tuşuna basın veya eklemek için bir girişi tıklayın. listeyi açın ve açılır listeyi açık tutarak listeden hızlı bir şekilde birden çok giriş seçin. <br /> <br /> Karşılaştırma amacıyla kullanıldığından verileri köşeli parantez içinde [ve] tek başına bıraktığınızdan emin olun. '; 

//------------------------------------------------------------------------------
// Rule Sets
//------------------------------------------------------------------------------
$_['tab_rule_sets']						 = 'Kural Kümeleri'; 
$_['help_rule_sets']					 = 'Kural kümeleri, aynı anda tek bir ödemeye birden çok kural uygulamak için kullanılır. İhtiyacınız olan masrafları hızlı bir şekilde oluşturmak için farklı masraflar için aynı kural kümesini yeniden kullanabilirsiniz. '; 
$_['heading_rule_sets']					 = 'Kural Kümeleri'; 
$_['button_add_rule_set']				 = 'Kural Kümesi Ekle'; 

$_['column_name']						 = 'İsim'; 

$_['help_rule_set_sort_order']			 = 'Kural olarak seçildiğinde kural kümesinin görüneceği sıralama düzeni.'; 
$_['help_rule_set_name']				 = 'Kural olarak seçildiğinde kural grubu için görüntülenen ad.'; 

//------------------------------------------------------------------------------
// Standard Text
//------------------------------------------------------------------------------
$_['copyright']							 = ''; 

$_['standard_autosaving_enabled']		 = 'Otomatik Kaydetme Etkin'; 
$_['standard_confirm']					 = 'Bu işlem geri alınamaz. Devam et?'; 
$_['standard_error']					 = '<strong> Hata: </strong> Bu yöntemi değiştirme izniniz yok!'; 
$_['standard_warning']					 = ''; 
$_['standard_please_wait']				 = 'Lütfen bekle...'; 
$_['standard_saving']					 = 'Kaydediliyor ...'; 
$_['standard_saved']					 = 'Kaydedildi!'; 
$_['standard_select']					 = '--- Seç -'; 
$_['standard_success']					 = 'Başarı!'; 

$_['standard_module']					 = 'Uygulamalar'; 
$_['standard_shipping']					 = 'Nakliye'; 
$_['standard_payment']					 = 'Ödemeler'; 
$_['standard_total']					 = 'Emir Toplamları'; 
$_['standard_feed']						 = 'Yemler'; 

//------------------------------------------------------------------------------
// Extension-Specific Text
//------------------------------------------------------------------------------
$_['help_charge_charges']				 = "Basit Masraflar". 'için, sabit ücreti veya öğe başına ücreti (5,00 gibi) girin. <Hr /> "Grup Ücretleri" için, köşeli parantezleri virgülle veya yeni satırlarla ayırın ve bunları şu biçimde girin: <br /> <br /> <b> Nereden - Nereye'; 
$_['help_product_groups']				 = 'Ürün grupları, bir grup kategori, marka ve / veya ürün temelinde ücretleri sınırlandırmak için kullanılır.'; 
?>