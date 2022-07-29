<?php
//Headings
$_['heading_title']            =  'بيز للخدمات اللوجستية';
$_['create_heading_title']     =  'إنشاء شحنة بيز جديدة';
$_['text_settings']            =  'الإعدادات';
$_['text_extension']           =  'طرق الشحن';

//Messages
$_['text_success']             =  'تم الحفظ';
$_['text_shipment_created']    =  'تم انشاء الشحنة بنجاح';

/* Entries */
$_['entry_api_key']            =  'API Key';
$_['entry_account_number']     =  'رقم الحساب';
$_['entry_tax_class']          =  'نوع الضريبة';
$_['entry_geo_zone']           =  'المنطقه الجغرافيه';
$_['entry_debugging_mode']     =  'نظام التجربه';
$_['entry_display_name']       =  'اسم عرض البوابه';
$_['entry_presentation']       =  'طريقه عرض البوابه للعملاء';
$_['entry_after_creation_status']   = 'حاله الطلب بعد انشاء الشحنه';
$_['entry_contact_beez']       =  'تواصل مع بيز';
$_['text_display_name_help']   =  'يمكنك تركها فارغه و سيتم عرض "بيز" كإسم للبوابه';
$_['text_order_already_exist'] = 'هذا الطلب لديه شحنة بالفعل';

	//Costing 
$_['head_global_settings']     =  'تكلفة الشحن';
$_['entry_rate']       		   =  'سعر الوزن';
$_['entry_rate_help']          =  'مثال: 5:10.00,7:12.00 السعر:الوزن,السعر:الوزن, إلخ..';
$_['entry_general_price_note'] =  'يتم تطبيق هذا السعر في حالة عدم وجود منطقه جغرافيه';
$_['entry_general_price']      =  'السعر العام';

	//Status mapping
$_['beez_statuses_mapping']     = 'ربط حالات طلب بيز بحالات المتجر الحاليه';
$_['beez_statuses_help_text']   = 'لكل حاله طلب في بيز اختر الحاله المقابله لها في المتجر للاستخدام في تعقب حاله الطلب اثناء الشحن <br/>هذا الربط اختياري، و في حاله عدم الربط سيتم انشاء طلب الشحن و لكن لن يتم التعقب من خلال المتجر';
$_['lbl_add_all_statuses']       = 'اضف كل حالات بيز';
$_['text_add_all_statuses_help'] = 'اضافة حالات طلب بيز الي حالات طلب المتجر';
$_['text_add_all_statuses']      = 'أضف';
$_['text_or']                    = ' أو ';

//Create order forms
$_['entry_billing_address']       =  'عنوان الدفع';
$_['text_pickup_details']         =  'تفاصيل استلام الشحنة';
$_['entry_shipping_address']      =  'عنوان الشحن';

$_['entry_pickup_location']       =  'احداثيات موقع الاستلام';
$_['entry_shipment_type']         =  'نوع الشحن';

$_['entry_products_to_ship']      =  'المنتجات المراد شحنها';
$_['entry_customer_note']         =  'ملاحظات العميل';

$_['entry_customer_first_name']   =  'الاسم الاول للعميل';
$_['entry_customer_last_name']    =  'الاسم الاخير للعميل';
$_['entry_customer_email']        =  'البريد الالكتروني للعميل';
$_['entry_customer_phone']        =  'هاتف العميل';
$_['entry_address_line']          =  'العنوان';
$_['entry_country']               =  'الدولة';
$_['entry_province']              =  'المحافظة';
$_['entry_district']              =  'الحي';
$_['entry_postcode']              =  'الرمز البريدي';

$_['entry_lat']                   =  'دائرة العرض';
$_['entry_lng']                   =  'خط الطول';
$_['entry_city']                  =  'المدينة';
$_['text_dry']                    =  'جاف';
$_['text_cold']                   =  'بارد';
$_['entry_shipment_description']  =  'وصف الشحنة';
$_['entry_cod_amount']            =  'قيمه الطلب';

//Errors
$_['error_warning']               =  'أخطاء:';
$_['error_general_rate']          =  'السعر العام مطلوب، و يجب ان يكون ارقام اكبر من صفر';
$_['error_not_installed']         =  'بيز غير مسطبه';

$_['error_lng']                   = 'خط الطول لعنوان الشحن مطلوب';
$_['error_lat']                   = 'دائرة العرض لعنوان الشحن مطلوبة ';
$_['error_cod']                   = 'قيمة الطلب مطلوبة';
$_['error_shipping_customer_phone2'] = 'رقم الهاتف ٢ لعنوان الشحن مطلوب';
$_['error_shipping_customer_phone1'] = 'رقم الهاتف ١ لعنوان الشحن مطلوب';
$_['error_billing_customer_phone2']  = 'رقم الهاتف ٢ لعنوان الدفع مطلوب';
$_['error_billing_customer_phone1']  = 'رقم الهاتف ١ لعنوان الدفع مطلوب';


