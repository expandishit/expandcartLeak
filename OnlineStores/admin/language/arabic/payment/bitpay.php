<?php
/**
 * @license https://github.com/bitpay/opencart-bitpay/blob/master/LICENSE MIT
 */

$_['heading_title'] = 'بيتباى';
$_['settings'] = 'الاعدادات';
$_['switch_text_enabled'] = 'مفعل';
$_['switch_text_disabled'] = 'غير مفعل';
// Text
$_['text_payment']        = 'طرق الدفع';
$_['text_success']       = 'نجاح: لقد تم تعديل معلومات الحساب بنجاح!';
$_['text_changes']        = 'هناك تعديلات غير محفوظة.';
$_['text_bitpay']         = '<a onclick="window.open(\'https://www.bitpay.com/\');"><img src="view/image/payment/bitpay.png" alt="بيتباي" title="بيتباي" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_bitpay_support'] = '<span><strong>للدعم الفني ٧/٢٤ لبيتباي</strong>, برجاء زيارة الموقع <a href="https://support.bitpay.com" target="_blank">https://support.bitpay.com</a> او ارسال بريد الكتروني الى <a href="mailto:support@bitpay.com" target="_blank">support@bitpay.com</a> للتنبيهات العاجلة</span>';
$_['text_bitpay_apply']   = '<a href="https://bitpay.com/start" title="فتح حساب بيتباي">إبدء بقبول البيتكوين عن طريق بيتباي</a>';
$_['text_high']           = 'مرتفعة';
$_['text_medium']         = 'متوسطة';
$_['text_low']            = 'منخفضة';
$_['text_live']           = 'خادم الوضع الحي';
$_['text_test']           = 'خادم وضع الاختبار';
$_['text_all_geo_zones']  = 'كل المناطق الجغرافية';
$_['text_settings']       = 'الاعدادات';
$_['text_log']            = 'السجل';
$_['text_general']        = 'عام';
$_['text_statuses']       = 'حالات الطلب';
$_['text_advanced']       = 'اعدادات متطورة';


// Entry
$_['entry_api_token']         = 'توقن الواجهة البرمجية';
$_['entry_api_key']           = 'مفاح الواجهة البرمجية';
$_['help_api_key']            = '<a href="https://bitpay.com/api-keys" target="_blank">https://bitpay.com/api-keys</a>';
$_['help_api_key_test']       = '<a href="https://test.bitpay.com/api-keys" target="_blank">https://test.bitpay.com/api-keys</a>';
$_['entry_api_server']        = 'خادم الواجهة البرمجية';
$_['help_api_server']         = 'استخدم '.$_['text_live'].' او '.$_['text_test'].' لمعالجة العمليات';
$_['entry_risk_speed']        = 'السرعة / درجة المخاطرة';
$_['help_risk_speed']         = '<strong>السرعة المرتفعة</strong> لتأكيد الدفع تستغرق من ٥ الى ١٠ ثواني, و يمكن استخدامها للسلع الرقمية او السلع منخفضة المخاطرة<br><strong>السرعة المنخفضة</strong> لتأكيد الدفع تستغرق حوالي ساعة و يفضل استعمالها للسلع عالية القيمة';
$_['entry_geo_zone']          = 'المنطقة الجغرافية';
$_['entry_status']            = 'الحالة';
$_['entry_sort_order']        = 'ترتيب الفرز';
$_['entry_new_status']        = 'جديدة';
$_['help_new_status']         = 'فاتورة جديدة أو مدفوعة جزئيا في انتظار الدفع الكامل';
$_['entry_paid_status']       = 'مدفوعة';
$_['help_paid_status']        = 'فاتورة مدفوعة بالكامل في انتظار التأكيد';
$_['entry_confirmed_status']  = 'مؤكدة';
$_['help_confirmed_status']   = 'فاتورة مؤكدة طبقاً لإعدادات السرعة / درجة المخاطرة';
$_['entry_complete_status']   = 'مكتملة';
$_['help_complete_status']    = 'فاتورة مؤكدة تمت إضافتها إلى حساب التاجر';
$_['entry_expired_status']    = 'منتهية الصلاحية';
$_['help_expired_status']     = 'فاتورة لم يتم استلام الدفعة الكاملة فيها، وتم انقضاء نافذة الدفع البالغة 15 دقيقة';
$_['entry_invalid_status']    = 'خاطئة';
$_['help_invalid_status']     = 'فاتورة تم دفعها بالكامل ولكن لم يتم تأكيدها';
$_['entry_notify_url']        = 'رابط التنبيهات';
$_['help_notify_url']         = 'ستنشر خدمة بيتباي تحديثات حالة الفاتورة على هذا الرابط';
$_['entry_return_url']        = 'رابط الرجوع';
$_['help_return_url']         = 'سوف توفير بيتباي رابط إعادة توجيه للمستخدم لهذا الرابط عند الدفع الناجح للفاتورة';
$_['entry_debug_mode']        = 'وضح تصحيح الاخطاء';
$_['help_debug_mode']         = 'تسجل معلومات إضافية إلى سجل بيتباي';
$_['entry_default']           = 'الإفتراضي';

// Error
$_['error_permission']   = 'تحذير: أنت لا تمتلك صلاحيات التعديل!';

$_['error_api_key']           = 'مطلوب مفتاح الواجهة البرمجية (لإشعارات الدفع الموثقة)';
$_['error_notify_url']        = 'رابط التنبيهات مطلوب';
$_['error_return_url']        = 'رابط العودة مطلوب';
$_['error_api_key_valid']     = 'يجب أن يكون مفتاح الواجهة البرمجية مفتاح وصول صالح لواجهة بيتباي البرمجية';
$_['error_notify_url_valid']  = 'يجب أن يكون رابط التنبيهات رابطاً صحيحاً';
$_['error_return_url_valid']  = 'يجب أن يكون رابط الرجوع رابطاً صحيحاً';
