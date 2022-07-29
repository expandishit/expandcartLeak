<?php
//==============================================================================
// Stripe Payment Gateway v230.4
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

$version = 'v230.4';

//------------------------------------------------------------------------------
// Heading
//------------------------------------------------------------------------------
$_['heading_title']						= 'بوابة دفع سترايب';
$_['text_stripe']						= '<a target="blank" href="https://stripe.com"><img src="https://stripe.com/img/logo.png" alt="Stripe" title="Stripe" /></a>';
$_['text_success']                      = 'تم التعديل!';

$_['settings'] = 'الاعدادات';
$_['switch_text_enabled'] = 'مفعل';
$_['switch_text_disabled'] = 'غير مفعل';


//------------------------------------------------------------------------------
// Extension Settings
//------------------------------------------------------------------------------
$_['tab_extension_settings']			= 'الإعدادات';
$_['heading_extension_settings']		= 'الإعدادات';
$_['text_payment']       = 'طرق الدفع';

$_['entry_status']						= 'الحالة';
$_['entry_sort_order']					= 'ترتيب الفرز';
$_['entry_title']						= 'العنوان';
$_['entry_button_text']					= 'نص الزر';
$_['entry_button_class']				= 'فئة الزر';
$_['entry_button_styling']				= 'تصميم الزر';

// Payment Page Text
$_['heading_payment_page_text']			= 'نص صفحة الدفع';

$_['entry_text_card_details']			= 'بيانات البطاقة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_use_your_stored_card']	= 'استخدام البطاقة المسجلة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_ending_in']				= 'التي تنتهي بـ: <div class="help-text">تدعم اكود اتش تي ام ال. لإستخدامها للبطاقات المسجلة، مثل "فيزا تنتهي بـ 4242"</div>';
$_['entry_text_use_a_new_card']			= 'إستخدم بطاقة جديدة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_card_name']				= 'الإسم على البطاقة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_card_number']			= 'رقم البطاقة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_card_type']				= 'نوع البطاقة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_card_expiry']			= 'تاريخ انتهاء البطاقة (MM/YY): <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_card_security']			= 'الرقم الأمني للبطاقة (CVC): <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_store_card']				= 'سجل البطاقة للإستعمال في المستقبل: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_please_wait']			= 'برجاء الإنتظار: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_text_to_be_charged']			= 'سيتم الخصم من البطاقة لاحقاً: <div class="help-text">يتم عرض هذا النص للعنصر الموجود في فاتورة الطلب عندما يكون منتج الاشتراك لديه إصدار تجريبي. يطرح بند السطر سعر الاشتراك من الإجمالي، وبالتالي لا يتم تحميل العميل رسوما مزدوجة.</div>';

// Errors
$_['heading_errors']					= 'الأخطاء';

$_['entry_error_customer_required']		= 'العميل مطلوب: <div class="help-text">أدخل النص المعروض عندما يحاول عميل غير مسجل الدخول (أي نزيل) الاطلاع على منتج اشتراك في سلة التسوق. سيتم عرض هذا فقط إذا تم تمكين الإعداد "منع الضيوف من الشراء".</div>';
$_['entry_error_shipping_required']		= 'الشحن مطلوب: <div class="help-text">في حالة استخدام إصدار التضمين في ستريب تشيكوت، أدخل رسالة الخطأ المعروضة إذا حاول العميل التحقق من ذلك بدون تطبيق طريقة الشحن على سلة التسوق.</div>';
$_['entry_error_shipping_mismatch']		= 'عدم تطابق الشحن: <div class="help-text">في حالة استخدام إصدار التضمين من سترايب تشيكوت، أدخل رسالة الخطأ المعروضة إذا كان عنوان الشحن الخاص بالعميل المحدد في إكساند كارت لا يتطابق مع العنوان الذي يعطيه في نافذة سترايب.</div>';

// Stripe Error Codes
$_['heading_stripe_error_codes']		= 'أكواد الخطأ من سترايب';
$_['help_stripe_error_codes']			= 'اترك أي من هذه الحقول فارغا لعرض رسالة الخطأ الافتراضية للشريط لرمز الخطأ هذا. يتم دعم اكواد اتش تي ام ال. لا يتم عرض رسائل الخطأ عند استخدام ستريب تشيكوت.';

$_['entry_error_card_declined']			= 'card_declined';
$_['entry_error_expired_card']			= 'expired_card';
$_['entry_error_incorrect_cvc']			= 'incorrect_cvc: <div class="help-text">يحدث هذا فقط إذا تم تعيين حساب سترايب الخاص بك إلى رفض المدفوعات التي تفشل التحقق من صحة التحقق من البطاقة.</div>';
$_['entry_error_incorrect_number']		= 'incorrect_number';
$_['entry_error_incorrect_zip']			= 'incorrect_zip: <div class="help-text">يحدث هذا فقط إذا تم تعيين حساب سترايب الخاص بك إلى رفض المدفوعات التي تفشل التحقق من الرمز البريدي.</div>';
$_['entry_error_invalid_cvc']			= 'invalid_cvc';
$_['entry_error_invalid_expiry_month']	= 'invalid_expiry_month';
$_['entry_error_invalid_expiry_year']	= 'invalid_expiry_year';
$_['entry_error_invalid_number']		= 'invalid_number';
$_['entry_error_missing']				= 'missing: <div class="help-text">يحدث هذا عند عدم وجود بطاقة مخزنة للعميل الذي يتم محاسبته.</div>';
$_['entry_error_processing_error']		= 'processing_error';

// Cards Page Text
$_['heading_cards_page_text']			= 'نص صفحة البطاقات';

$_['entry_cards_page_link']				= 'رابط صفحة البطاقات: <div class="help-text">أدخل نص الرابط للصفحة حيث يمكن للعملاء عرض بطاقاتهم المخزنة، كما هو موضح في صفحة الحساب الرئيسية.</div>';
$_['entry_cards_page_heading']			= 'العنوان الرئيسي لصفحة البطاقات: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_cards_page_none']				= 'رسالة عدم وجود بطاقات: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_cards_page_default_card']		= 'النص الافتراضي للبطاقة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_cards_page_make_default']		= 'صنع الزر الافتراضي';
$_['entry_cards_page_delete']			= 'زر الحذف';
$_['entry_cards_page_confirm']			= 'تأكيد الحذف';
$_['entry_cards_page_add_card']			= 'زر اضافة بطاقة جديدة';
$_['entry_cards_page_card_address']		= 'عنوان البطاقة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_cards_page_success']			= 'رسالة النجاح';

// Subscriptions Page Text
$_['heading_subscriptions_page_text']	= 'نص صفحة الاشتراكات';

$_['entry_subscriptions_page_heading']	= 'لعوان الرئيسي لصفحة الاشتراكات: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_subscriptions_page_message']	= 'رسالة البطاقة الافتراضية: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_subscriptions_page_none']		= 'رسالة عدم وجود اشتراكات: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_subscriptions_page_trial']	= 'نص انتهاء الفترة التجريبية: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_subscriptions_page_last']		= 'نص اخر عملية سحب: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_subscriptions_page_next']		= 'نص عملية السحب القادمة: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_subscriptions_page_charge']	= 'نص الرسوم الاضافية: <div class="help-text">تدعم اكود اتش تي ام ال.</div>';
$_['entry_subscriptions_page_cancel']	= 'زر الإلغاء';
$_['entry_subscriptions_page_confirm']	= 'الغاء التأكيد: <div class="help-text">أدخل النص المعروض للعميل لتأكيد إلغاء اشتراكه. سيطلب من العميل كتابة <b>كانسيل</b> لتأكيد إلغائه.</div>';

//------------------------------------------------------------------------------
// Order Statuses
//------------------------------------------------------------------------------
$_['tab_order_statuses']				= 'حالات الطلب';
$_['heading_order_statuses']			= 'حالات الطلب';
$_['help_order_statuses']				= 'اختر حالات الطلب التي يتم تعيينها عند استيفاء الدفعة لكل شرط.';

$_['entry_success_status_id']			= 'دفعة ناجحة (تم التحصيل)';
$_['entry_authorize_status_id']			= 'دفعة ناجحة (مصرح بها)';
$_['entry_error_status_id']				= 'خطأ في اتمام الطلب: <div class="help-text">سيتم تطبيق هذه الحالة عند اكتمال عملية الدفع بنجاح، ولكن لا يمكن إكمال الطلب باستخدام وظائف تأكيد أمر إكساند كارت العادية. يحدث هذا عادة عندما تكون قد أدخلت إعدادات بريد غير صحيحة او قمت بتثبيت التعديلات التي تؤثر على طلبات العملاء.</div>';
$_['entry_street_status_id']			= 'فشل فحص الشارع';
$_['entry_zip_status_id']				= 'فشل التحقق من الرمز البريدي';
$_['entry_cvc_status_id']				= 'فشل التحقق من الرقم السري';
$_['entry_refund_status_id']			= 'الدفعة المستردة بالكامل';
$_['entry_partial_status_id']			= 'رد الدفعة جزئيا';

$_['text_ignore']						= '--- تجاهل ---';

//------------------------------------------------------------------------------
// Restrictions
//------------------------------------------------------------------------------
$_['tab_restrictions']					= 'القيود';
$_['heading_restrictions']				= 'القيود';
$_['help_restrictions']					= 'حدد إجمالي سلة التسوق المطلوبة وحدد المتاجر المؤهلة والمناطق الجغرافية ومجموعات العملاء لطريقة الدفع هذه.';

$_['entry_min_total']					= 'الحد الادنى للإجمالي: <div class="help-text">أدخل الحد الأدنى للطلب لتصبح طريقة الدفع هذه نشطة. اتركه فارغا لتظهر بدون قيود.</div>';
$_['entry_max_total']					= 'الحد الاقصى للإجمالي: <div class="help-text">أدخل الحد الأقصى للطلب لتصبح طريقة الدفع هذه غير نشطة. اتركه فارغا لتظهر بدون قيود.</div>';

$_['entry_stores']						= 'المتاجر: <div class="help-text">حدد المتاجر التي يمكنها استخدام طريقة الدفع هذه.</div>';

$_['entry_geo_zones']					= 'المناطق الجغرافية: <div class="help-text">حدد المناطق الجغرافية التي يمكنها استخدام طريقة الدفع هذه. ينطبق مربع الاختيار "في أي مكان آخر" على أي مواقع لا تقع ضمن منطقة جغرافية.</div>';
$_['text_everywhere_else']				= '<em>في أي مكان آخر</em>';

$_['entry_customer_groups']				= 'مجموعات العملاء: <div class="help-text">حدد مجموعات العملاء التي يمكنها استخدام طريقة الدفع هذه. ينطبق مربع الاختيار "الضيوف" على جميع العملاء الذين لم يسجلوا الدخول إلى الحساب.</div>';
$_['text_guests']						= '<em>الضيوف</em>';

// Currency Settings
$_['heading_currency_settings']			= 'اعدادات العملة';
$_['help_currency_settings']			= 'حدد العملات التي سيتم المحاسبه بها من قبل سترايب، استنادا إلى عملة الطلب. <a target="_blank" href="https://support.stripe.com/questions/which-currencies-does-stripe-support">معرفة العملات التي يدعمها بلدك</a>';
$_['entry_currencies']					= 'عندما يكون الطلب بعملة...، قم بالمحاسبة بعملة:';
$_['text_currency_disabled']			= '--- معطلة ---';

//------------------------------------------------------------------------------
// Stripe Settings
//------------------------------------------------------------------------------
$_['tab_stripe_settings']				= 'اعدادات سترايب';
$_['help_stripe_settings']				= 'مفاتيح الواجهة البرمجية يمكن ايجادها في حسابكم لدى سترايب من لوحة التحكم ثم Account Settings ثم API Keys';

// API Keys
$_['heading_api_keys']					= 'مفاتيح الواجهة البرمجية';

$_['entry_test_secret_key']				= 'المفتاح السري الإختباري';
$_['entry_test_publishable_key']		= 'المفتاح القابل للنشر الإختباري';
$_['entry_live_secret_key']				= 'المفتاح السري الحي';
$_['entry_live_publishable_key']		= 'المفتاح القابل للنشر الحي';

// Stripe Settings
$_['heading_stripe_settings']			= 'اعدادات سترايب';

$_['entry_webhook_url']					= 'رابط ويب هوك: <div class="help-text">انسخ و الصق هذا الرابط في حساب سترايب الخاص بك من Account Settings ثم Webhooks.</div>';

$_['entry_transaction_mode']			= 'وضع العمليات: <div class="help-text">إستخدم وضع الإختبار لإختبار الدفع عن طريق سترايب. لمزيد من المعلومات، قم بزيارة الرابط التالي <a href="https://stripe.com/docs/testing" target="_blank">https://stripe.com/docs/testing</a>. إستخدم الوضع الحي عندما تكون جاهزاً لإستقبال دفعات حية.</div>';
$_['text_test']							= 'وضع الاختبار';
$_['text_live']							= 'الوضع الحي';

$_['entry_charge_mode']					= 'وضع التحصيل: <div class="help-text">اختر ما إذا كنت تريد تفويض الدفعات والتقاطها يدويا في وقت لاحق، أو لالتقاط الدفعات (أي تحصيل الرسوم بالكامل) عند وضع الطلبات. بالنسبة إلى الدفعات المصرح بها فقط، يمكنك التقاطها باستخدام الرابط المتوفر في علامة التبويب "السجل" للطلب. <br /> <br /> إذا اخترت "تصريح إذا كان احتمال احتيال، تحصيل بخلاف ذلك "، فستستخدم الإضافة الاحتيال لتحديد ما إذا كان أمر قد يكون الاحتيال. إذا تجاوزت درجة الاحتيال الحد الأقصى المسموح به، فسيتم تصريح الرسوم، إذا تحت، سيتم تحصيل المبلغ.</div>';
$_['text_authorize']					= 'تصريح';
$_['text_capture']						= 'تحصيل';
$_['text_fraud_authorize']				= 'تصريح إذا كان احتمال احتيال، تحصيل خلاف ذلك';

$_['entry_transaction_description']		= 'وصف العملية: <div class="help-text">أدخل النص الذي سيتم إرساله بوصف وصف الصفقة لسترايب. يمكنك استخدام الرموز القصيرة التالية لإدخال معلومات حول الطلب: [store], [order_id], [amount], [email], [comment], [products]</div>';

$_['entry_send_customer_data']			= 'إرسال بيانات العميل: <div class="help-text">سيؤدي إرسال بيانات العملاء إلى إنشاء عميل في سترايب عند معالجة الطلب، استنادا إلى عنوان البريد الإلكتروني للطلب. سيتم إرفاق بطاقة الائتمان المستخدمة لهذا العميل، مما يسمح لك بالسحب منها مرة أخرى في المستقبل في سترايب.</div>';
$_['text_never']						= 'مطلقاً';
$_['text_customers_choice']				= 'إختيار العميل';
$_['text_always']						= 'دائماً';

$_['entry_allow_stored_cards']			= 'السماح للعملاء باستخدام البطاقات المخزنة: <div class="help-text">إذا تم تعيينها على "نعم"، سيتمكن العملاء الذين لديهم بطاقات مخزنة في سترايب من استخدام تلك البطاقات لعمليات الشراء المستقبلية في متجرك، دون الحاجة إلى إعادة إدخال المعلومات.</div>';

// Apple Pay Settings
$_['heading_apple_pay_settings']		= 'Apple Pay Settings';

$_['entry_applepay']					= 'Enable Apple Pay: <div class="help-text">If you enable Apple Pay in this extension, make sure you have <a target="_blank" href="https://dashboard.stripe.com/account/apple_pay">enabled Apple Pay</a> for your Stripe account, and uploaded the <a href="https://stripe.com/files/apple-pay/apple-developer-merchantid-domain-association">apple-developer-merchantid-domain-association</a> file to the <a target="_blank" href="https://stripe.com/docs/apple-pay/web#going-live">location that they specify</a>.</div>';
$_['entry_applepay_label']				= 'Payment Sheet Label: <div class="help-text">Enter the text displayed for the Apple Pay payment sheet. For example, if you enter "mydomain.com", the sheet will read "PAY MYDOMAIN.COM" next to the order amount.</div>';
$_['entry_applepay_billing']			= 'Require Billing Address: <div class="help-text">If set to "Yes", the customer will need to enter/choose their billing address in the Apple Pay sheet. If set to "No", the customer\'s card will not be validated against their billing address. (Note: prefilling the billing address in the sheet based on the Expandcart billing address is not possible at this time.)</div>';

//------------------------------------------------------------------------------
// Stripe Checkout
//------------------------------------------------------------------------------
$_['tab_stripe_checkout']				= 'انهاء الدفع عن طريق سترايب';
$_['heading_stripe_checkout']			= 'انهاء الدفع عن طريق سترايب';
$_['help_stripe_checkout']				= 'تستخدم صفحة اتمام الطلب عن طريق سترايب نافذة منبثقة لعرض المدخلات لبطاقة الائتمان، والتحقق من الصحة، ومعالجة الأخطاء. يمكنك قراءة المزيد عن ذلك و مشاهدة عرض توضيحي من الرابط التالي <a target="_blank" href="https://stripe.com/docs/checkout">https://stripe.com/docs/checkout</a><br />ملاحظة: اتمام الطلب عن طريق سترايب لا تسمح للعملاء بإعادة استخدام العنوان المدخل قي صفحة اتمام الطلب السريعة.';

$_['entry_use_checkout']				= 'استخدام نافذة سترايب المنبثقة لإتمام الطلب: <div class="help-text">في حالة استخدام صفحة دفع مخصصة، اتمام الطلب عن طريق سترايب يمكن ان يسبب مشكلات على أجهزة الجوال. قد ترغب في استخدام "نعم، لأجهزة سطح المكتب فقط" في هذه الحالة.</div>';
$_['text_yes_for_desktop_devices']		= 'نعم، لاجهزة الكمبيوتر فقط';

$_['entry_checkout_remember_me']		= 'تمكين خيار "تذكرني": <div class="help-text">سيتيح هذا للعملاء اختيار ما إذا كان ستريب يتذكرهم على مواقع أخرى تستخدم ستريب تشيكوت. ملاحظة: حتى في حالة التمكين، لن يظهر الخيار إذا تم تعيين متصفح العميل لمنع ملفات تعريف ارتباط الجهات الخارجية.</div>';

$_['entry_checkout_alipay']				= 'تمكين علي باي: <div class="help-text">هناك عدد قليل من القيود على استخدام علي باي في سترايب، يمكنك قراءة المزيد عن ذلك <a target="_blank" href="https://stripe.com/docs/alipay#refunds">من هذه الصفحة</a>.</div>';
$_['entry_checkout_bitcoin']			= 'تمكين بيتكوين: <div class="help-text">تأكد من أنك قمت <a target="_blank" href="https://dashboard.stripe.com/account/bitcoin/enable">بتفعيل الواجهة البرمجية لبيتكوين</a> في حسابك لدى سترايب اذا كنت تريد استعمال هذا الخيار.</div>';

$_['entry_checkout_billing']			= 'جعل عنوان إرسال الفواتير مطلوب: <div class="help-text">إذا تم تعيينها على "لا"، فلن يدخل العميل عنوانا في النافذة المنبثقة، مما يعني أنه لن يتم تخزين أي عنوان أو التحقق منه في سترايب.</div>';

$_['entry_checkout_shipping']			= 'جعل عنوان الشحن مطلوب: <div class="help-text">قم بتمكين هذا الإعداد فقط إذا كنت تستخدم صفحة اتمام الطلب السريعة. إذا تم تعيينه على "نعم"، يجب تطبيق طريقة الشحن على العربة أولا. لاحظ أنه إذا طبق العميل طريقة شحن باستخدام سلة الشراء، ثم لا يستخدم عنوان شحن مطابق في نافذة سترايب تشيكوت المنبثقة، فلن تتم معالجة النفقات، وذلك لمنع رسوم الشحن غير الصحيحة.</div>';

$_['entry_checkout_image']				= 'شعار النافذة المنبثقة: <div class="help-text">أدخل مسار الصورة المراد استخدامه لشعار متجرك في اللوحة المنبثقة. الحد الأدنى الموصى به هو 128 × 128 بكسل.</div>';
$_['text_browse']						= 'تصفح';
$_['text_clear']						= 'مسح';
$_['text_image_manager']				= 'مدير الصور';

$_['entry_checkout_title']		 		= 'عنوان النافذة المنبثقة: <div class="help-text">أدخل العنوان الذي يظهر أعلى اللوحة المنبثقة. يمكنك استخدام الرموز القصيرة التالية لإدخال معلومات حول الطلب: [store], [order_id], [amount], [email], [products]</div>';

$_['entry_checkout_description']		= 'صف النافذة المنبثقة: <div class="help-text">يمكنك بشكل اختياري إدخال وصف يظهر أسفل العنوان المنبثق. يمكنك استخدام الرموز القصيرة التالية لإدخال معلومات حول الطلب: [store], [order_id], [amount], [email], [products]</div>';

$_['entry_checkout_button']				= 'نص زر النافذة المنبثقة: <div class="help-text">أدخل النص للزر في اللوحة المنبثقة. يمكنك استخدام الرمز القصير [amount] لإدخال مبلغ الطلب.</div>';

$_['entry_quick_checkout']				= 'اتمام الطلب السريع';

//------------------------------------------------------------------------------
// Subscription Products
//------------------------------------------------------------------------------
$_['tab_subscription_products']			= 'المنتجات ذات الإشتراك';
$_['help_subscription_products']		= '&bull; Subscription products will subscribe the customer to the associated Stripe plan when they are purchased. You can associate a product with a plan by entering the Stripe plan ID in the "Location" field for the product.<br />&bull; If the subscription is not set to be charged immediately (i.e. it has a trial period), the amount of the subscription will be taken off their original order, and a new order will be created when the subscription is actually charged to their card.<br />&bull; Any time Stripe charges the subscription in the future, a corresponding order will be created in Expandcart.<br />&bull; If you have a coupon set up in your Stripe account, you can map an Expandcart coupon to it by using the same coupon code and discount amount. When a customer purchases a subscription product and uses that coupon code, it will pass the code to Stripe to properly adjust the subscription charge.';

$_['heading_subscription_products']		= 'اعدادات المنتجات ذات الإشتراك';

$_['entry_subscriptions']				= 'Enable Subscription Products';
$_['entry_prevent_guests']				= 'Prevent Guests From Purchasing: <div class="help-text">If set to "Yes", only customers with accounts in Expandcart will be allowed to checkout if a subscription product is in the cart.</div>';
$_['entry_include_shipping']			= 'Include Shipping: <div class="help-text">If set to "Yes" and there is a shipping cost on the order, a Stripe invoice item for the product\'s shipping cost will be created. Every time the subscription is charged in the future, a new invoice item will be created for the following charge date, with the same shipping cost.</div>';
$_['entry_allow_customers_to_cancel']	= 'Allow Customers to Cancel Subscriptions: <div class="help-text">Choose "Yes" for this setting to display subscriptions in the customer\'s account panel, allowing them to cancel their subscription at any time.</div>';

// Current Subscription Products
$_['heading_current_subscriptions']		= 'المنتجات ذات الإشتراك الحالية';
$_['entry_current_subscriptions']		= 'Current Subscription Products: <div class="help-text">Products with mismatching prices are highlighted. The customer will always be charged the Stripe plan price, not the Expandcart product price, so you should make sure the price in Expandcart corresponds to the price in Stripe.<br /><br />Note: only plans for your Transaction Mode will be listed. You are currently set to "[transaction_mode]" mode.</div>';

$_['text_thead_Expandcart']				= 'Expandcart';
$_['text_thead_stripe']					= 'Stripe';
$_['text_product_name']					= 'Product Name';
$_['text_product_price']				= 'Product Price';
$_['text_location_plan_id']				= 'Location / Plan ID';
$_['text_plan_name']					= 'Plan Name';
$_['text_plan_interval']				= 'Plan Interval';
$_['text_plan_charge']					= 'Plan Charge';
$_['text_no_subscription_products']		= 'No Subscription Products';
$_['text_create_one_by_entering']		= 'Create one by entering the Stripe plan ID in the "Location" field for the product';

// Map Options to Subscriptions
$_['heading_map_options']				= 'Map Options to Subscriptions';
$_['help_map_options']					= 'If the customer has a product with the appropriate option name and option value in their cart, they will be subscribed to the corresponding plan ID. This will override the plan ID in the Location field for that product.';

$_['column_action']						= 'Action';
$_['column_option_name']				= 'Option Name';
$_['column_option_value']				= 'Option Value';
$_['column_plan_id']					= 'Plan ID';

$_['button_add_mapping']				= 'Add Mapping';

// Map Recurring Profiles to Subscriptions
$_['heading_map_recurring_profiles']	= 'Map Recurring Profiles to Subscriptions';
$_['help_map_recurring_profiles']		= 'If the customer has a product with the appropriate recurring profile name in their cart, they will be subscribed to the corresponding plan ID. This will override the plan ID in the Location field for that product. The subscription frequency and charge amount is determined by the Stripe plan, not the recurring profile settings, so make sure they match exactly.';

$_['column_profile_name']				= 'Recurring Profile Name';

//------------------------------------------------------------------------------
// Create a Charge
//------------------------------------------------------------------------------
$_['tab_create_a_charge']				= 'Create a Charge';

$_['help_charge_info']					= 'Enter the charge info below, then choose whether to generate a payment link, charge a customer\'s card, or enter a card manually.';
$_['heading_charge_info']				= 'Charge Info';

$_['entry_order_id']					= 'Order ID: <div class="help-text">Optional.<br />If filled in, an order history note will be added to the order regarding the payment.</div>';
$_['entry_order_status']				= 'Order Status Change: <div class="help-text">Optional.<br />If set, and an Order ID value is set, then the order\'s status will be changed after the payment is successfully processed.</div>';
$_['entry_description']					= 'Description: <div class="help-text">Optional.<br />This will be shown in your Stripe admin panel, and on the customer receipt if you have Stripe set to send them an e-mail receipt.</div>';
$_['entry_statement_descriptor']		= 'Statement Descriptor: <div class="help-text">Optional.<br />This will be shown on the customer\'s bank statement for the charge. It is a maximum of 22 characters. Note that not all banks respect the value that Stripe passes, so there is no guarantee this will be shown exactly as you\'ve written. The following characters are prohibited: < > " \'</div>';
$_['entry_amount']						= 'Amount';

// Create Payment Link
$_['heading_create_payment_link']		= 'Create Payment Link';

$_['help_create_payment_link']			= '<div class="help-text">Use this to create a payment link to send to your customer. When they visit the link, they will be able to input their payment information to process the payment. Note: the payment page uses Stripe Checkout, regardless of whether Stripe Checkout is enabled for the normal checkout process.</div>';
$_['button_create_payment_link']		= 'Create Payment Link';

// Use a Stored Card
$_['heading_use_a_stored_card']			= 'Use a Stored Card';

$_['entry_customer']					= 'Customer';
$_['placeholder_customer']				= 'Start typing a customer\'s name or e-mail address';
$_['text_customers_stored_cards_will']	= '(Customer\'s Default Card Will Appear Here)';
$_['button_create_charge']				= 'Create Charge';

// Use a New Card
$_['heading_use_a_new_card']			= 'Use a New Card';

//------------------------------------------------------------------------------
// Standard Text
//------------------------------------------------------------------------------
$_['copyright']							= '';

$_['standard_autosaving_enabled']		= 'الحفظ التلقائي مفعل';
$_['standard_confirm']					= 'هذه العملية لا يمكن استرجاعها، هل تريد المتابعة؟';
$_['standard_error']					= '<strong>تحذير:</strong> ليس لديك صلاحيات لتعديل ' . $_['heading_title'] . '!';
$_['standard_max_input_vars']			= '<strong>تحذير:</strong> برجاء الاتصال بخدمة العملاء ٢.';
$_['standard_please_wait']				= 'برجاء الإنتظار...';
$_['standard_saved']					= 'تم الحفظ!';
$_['standard_saving']					= 'جاري الحفظ...';
$_['standard_select']					= '--- إختيار ---';
$_['standard_success']					= 'نجاح!';
$_['standard_testing_mode']				= 'السجل كبير جدا لفتحه! برجاء مسحه اولا، ثم اعد الاختبار مرة اخرى.';
$_['standard_vqmod']					= '<strong>تحذير:</strong> برجاء الاتصال بخدمة العملاء';

$_['standard_module']					= 'التطبيقات';
$_['standard_shipping']					= 'الشحن';
$_['standard_payment']					= 'الدفع';
$_['standard_total']					= 'اجماليات الطلب';
$_['standard_feed']						= 'المستخلاصات';

// Errors

$_['error_settings']                    = "تحذير : من فضلك ادخل المفتاح القابل للنشر الإختباري و المفتاح القابل للنشر الإختباري و المفتاح السري الحي و المفتاح القابل للنشر الحي بشكل كامل !!";

?>