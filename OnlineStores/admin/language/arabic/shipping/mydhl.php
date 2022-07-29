<?php
//Headings
$_['heading_title']            =  'DHL Express';
$_['create_heading_title']     =  'إنشاء شحنة دي اتش ال اكسبريس جديدة';
$_['text_settings']            =  'الإعدادات';
$_['text_extension']           =  'طرق الشحن';

//Messages
$_['text_success']             =  'تم الحفظ';
$_['text_shipment_created']    =  'تم انشاء الشحنة بنجاح';

/* Entries */
$_['entry_username']           =  'اسم المستخدم';
$_['entry_password']           =  'كلمة السر';
$_['entry_account_number']     =  'رقم الحساب';
$_['entry_tax_class']          =  'نوع الضريبة';
$_['entry_geo_zone']           =  'المنطقه الجغرافيه';
$_['entry_debugging_mode']     =  'نظام التجربه';
$_['entry_display_name']       =  'اسم عرض البوابه';
$_['entry_presentation']       =  'طريقه عرض البوابه للعملاء';
$_['entry_after_creation_status']   = 'حاله الطلب بعد انشاء الشحنه';
$_['entry_contact_mydhl']      =  'تواصل مع دي اتش ال';
$_['text_display_name_help']   =  'يمكنك تركها فارغه و سيتم عرض "دي اتش ال" كإسم للبوابه';
$_['text_order_already_exist'] = 'هذا الطلب لديه شحنة بالفعل';

//Create order forms
$_['text_pickup_details']         =  'تفاصيل استلام الشحنة';
$_['entry_country']               =  'الدولة';
$_['entry_city']                  =  'المدينة';

//Errors 
 #Settings Page
$_['error_warning']              =  'Errors:';
$_['error_not_installed']        =  'Mydhl is not installed';
$_['error_invalid_username']     =  'Username is empty or invalid';
$_['error_invalid_password']     =  'Password is empty or invalid';
$_['error_invalid_account_number']=  'Account Number is empty or invalid';
$_['error_invalid_min_width']    =  'Minimum width must be greater than zero';
$_['error_invalid_min_length']   =  'Minimum length must be greater than zero';
$_['error_invalid_min_height']   =  'Minimum height must be greater than zero';
$_['error_kg_cm']                = 'Please, Add kilogram & centimeter to Measurement Units in settings page >> All products';
$_['error_lb_in']                = 'Please, Add pound & inch to Measurement Units in settings page >> All products';
 #Create shipment Page
$_['error_invalid_planned_date']           = 'تاريخ الشحن غير صحيح';
$_['error_packages_count']                 = 'اقل عدد للطرود ١';
$_['error_invalid_shipper_fullname']       = 'اسم المرسل مطلوب';
$_['error_invalid_shipper_company_name']   = 'اسم شركة المرسل مطلوب';
$_['error_invalid_shipper_phone']          = 'هاتف المرسل مطلوب';
$_['error_invalid_shipper_addressline1']   = 'عنوان المرسل مطلوب';
$_['error_invalid_receiver_fullname']      = 'اسم المستلم مطلوب';
$_['error_invalid_receiver_company_name']  = 'اسم شركة المستلم مطلوب';
$_['error_invalid_receiver_phone']         = 'هاتف المستلم مطلوب';
$_['error_invalid_receiver_addressline1']  = 'عنوان المستلم مطلوب';
$_['error_invalid_content_description']    = 'وصف الشحنة مطلوب';
$_['error_invalid_invoice_date']           = 'تاريخ الفاتورة غير صحيح';
$_['error_invalid_package_width']          = 'عرض الطرد #%s مطلوب';
$_['error_invalid_package_length']         = 'طول الطرد #%s مطلوب';
$_['error_invalid_package_height']         = 'ارتفاع الطرد #%s مطلوب';
$_['error_invalid_package_weight']         = 'وزن الطرد #%s مطلوب';

//Create form
$_['entry_planned_date_time'] = 'تاريخ الشحن';
$_['entry_full_name']         = 'الاسم';
$_['entry_company_name']      = 'اسم الشركة';
$_['entry_phone']             = 'الهاتف';
$_['entry_addressline']       = 'العنوان';
$_['entry_postalcode']        = 'الرقم البريدي';
$_['entry_shipper']           = 'المرسل';
$_['entry_receiver']          = 'المستلم';

$_['text_exworks']                = 'ExWorks';
$_['text_freeـcarrier']           = 'Free Carrier';
$_['text_carriage_paid_to']       = 'Carriage Paid To';
$_['text_delivered_at_place']     = 'Delivered at Place';
$_['text_delivered_duty_paid']    = 'Delivered Duty Paid';
$_['text_free_alongside_ship']    = 'Free Alongside Ship';
$_['text_free_on_board']          = 'Free on Board';
$_['text_cost_freight']           = 'Cost and Freight';
$_['text_cost_insurance_freight'] = 'Cost, Insurance and Freight';
$_['text_carriage_and_insurance_paid_to'] = 'Carriage and Insurance Paid To';
$_['text_delivered_at_place_unloaded']    = 'Delivered at Place Unloaded';

$_['entry_description']           = 'وصف الشحنة';
$_['entry_incoterm']              = "مصطلحات تجارية دولية";
$_['entry_unit_of_measurement']   = 'وحدة القايس';
$_['entry_is_customs_declarable'] = 'يوجد اعلان جمركي؟';
$_['entry_declared_value']        = 'السعر';
$_['entry_weight']                = 'الوزن';
$_['text_kg']                     = 'كجم';

$_['entry_products']              = 'المنتجات';
$_['entry_manufacturer_country']  = 'بلد منشأ المنتجات';
$_['entry_content']               = 'محتوي الشحنة';
$_['entry_invoice']               = 'الفاتورة';
$_['entry_invoice_number']        = 'رقم الفاتورة';
$_['entry_invoice_date']          = 'تاريخ الفاتورة';
$_['entry_signature_name']        = 'التوقيع';
$_['entry_signature_title']       = 'لقب المُوَقِع';
$_['text_metric']                 = 'النظام المتري (كجم، سم)';
$_['text_imperial']               = 'النظام البريطاني الامبراطوري (باوند ، انش)';
$_['entry_width']                 = 'عرض';
$_['entry_height']                = 'ارتفاع';
$_['entry_length']                = 'طول';
$_['entry_packages_count']        = 'عدد الطرود';

$_['packaging_details']           = 'الحد الادني لحجم الطرد';
$_['text_cm']                     = 'سم';
$_['text_in']                     = 'انش';
$_['error_kg_cm']                 = 'من فضلك اضف وحدة الكيلو جرام و السنتيمتر لوحدات قياسك في الاعدادات (كجم kg سم cm)';
$_['error_lb_in']                 = 'من فضلك اضف وحدة الباوند و الانش لوحدات قياسك في الاعدادات (باوند lb انش in)';

$_['entry_packages']              = 'الطرود';
$_['entry_package']              = 'طرد';
