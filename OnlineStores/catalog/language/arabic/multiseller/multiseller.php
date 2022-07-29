<?php

// **********
// * Global *
// **********
$_['ms_viewinstore'] = 'مشاهدة في المتجر';
$_['ms_view'] = 'مشاهدة';
$_['ms_publish'] = 'نشر';
$_['ms_unpublish'] = 'إلغاء النشر';
$_['ms_edit'] = 'تحرير';
$_['ms_clone'] = 'إستنساخ';
$_['ms_relist'] = 'إعادة فهرسة';
$_['ms_rate'] = 'تقييم';
$_['ms_download'] = 'تنزيل';
$_['ms_create_product'] = 'إضافة منتج';
$_['ms_delete'] = 'حذف';
$_['ms_update'] = 'تعديل';
$_['ms_type'] = 'النوع';
$_['ms_amount'] = 'الكمية';
$_['ms_status'] = 'الحالة';
$_['ms_date_paid'] = 'تاريخ الدفع';
$_['ms_last_message'] = 'أخر رسالة';
$_['ms_description'] = 'الوصف';
$_['ms_id'] = '#';
$_['ms_by'] = 'بواسطة';
$_['ms_action'] = 'التأثير';
$_['ms_sender'] = 'المرسل';
$_['ms_message'] = 'الرسالة';
$_['ms_text_success'] = 'تم تعديل بيانات بنجاح';
$_['ms_text_add_success'] = 'تم اضافته بنجاح';
$_['ms_text_forced_logout'] = 'سوف يتم تسجبل خروجك الى ان يتم موافقة الادمن على التعديلات التي قمت بها';


$_['ms_date_created'] = 'تاريخ الإنشاء';
$_['ms_date'] = 'التاريخ';

$_['ms_button_submit'] = 'إرسال';
$_['ms_button_add_special'] = 'تعريف أسعار خاصة جديدة';
$_['ms_button_add_discount'] = 'تعريف خصومات كمية جديدة';
$_['ms_button_submit_request'] = 'إرسال الطلب';
$_['ms_button_save'] = 'حفظ';
$_['ms_button_cancel'] = 'إلغاء';
$_['ms_button_select_predefined_avatar'] = 'إختيار صورة معرفة مسبقاً';

$_['ms_button_select_image'] = 'إختيار صورة';
$_['ms_button_select_images'] = 'إختيار صور';
$_['ms_button_select_files'] = 'إختيار ملفات';

$_['ms_transaction_order'] = 'خصم: طلب رقم #%s';
$_['ms_transaction_sale'] = 'خصم: %s (-%s عمولة)';
$_['ms_transaction_refund'] = 'إسترداد: %s';
$_['ms_transaction_listing'] = 'إدراج المنتج: %s (%s)';
$_['ms_transaction_signup']      = 'رسوم إشتراك %s';
$_['ms_request_submitted'] = 'لقد تم إرسال الطلب';

$_['ms_totals_line'] = 'حالياً %s بائع و %s منتجات للبيع!';

$_['ms_text_welcome'] = '<a href="%s">تسجيل دخول</a> | <a href="%s">إنشاء حساب</a> | <a href="%s">إنشاء حساب بائع</a>.';
$_['ms_button_register_seller'] = 'تسجيل كبائع';
$_['ms_register_seller_account'] = 'تسجيل حساب بائع';

// Mails

// Seller
$_['ms_mail_greeting'] = "مرحباً %s,\n\n";
$_['ms_mail_greeting_no_name'] = "مرحباً,\n\n";
$_['ms_mail_ending'] = "\n\nتحياتنا,\n%s";
$_['ms_mail_message'] = "\n\nالرسالة:\n%s";

$_['ms_mail_subject_seller_account_created'] = 'تم إنشاء حساب البائع';
$_['ms_mail_seller_account_created'] = <<<EOT
لقد تم إنشاء حسابك لدى %s كبائع بنجاح!

يمكنك الأن إضافة منتجاتك.
EOT;

$_['ms_mail_subject_seller_account_awaiting_moderation'] = 'حساب البائع ينتظر الإشراف';
$_['ms_mail_seller_account_awaiting_moderation'] = <<<EOT
لقد تم إنشاء حسابك لدى %s كبائع وهو الأن ينتظر التفعيل من المشرف المختص.

سوف تتلقى رسالة بريد إلكتروني بمجرد الموافقة عليه.
EOT;

$_['ms_mail_subject_product_awaiting_moderation'] = 'المنتج ينتظر الإشراف';
$_['ms_mail_product_awaiting_moderation'] = <<<EOT
المنتج %s لدى %s ينتظر الموافقة من المشرف المختص.

سوف تتلقى رسالة بريد إلكتروني بمجرد الموافقة عليه.
EOT;

$_['ms_mail_subject_product_purchased'] = 'طلب جديد';
$_['ms_mail_product_purchased'] = <<<EOT
لقد تم شراء المنتجات التي قمت بطلبها من %s.

إسم العميل: %s (%s)

المنتجات:
%s
الإجمالي: %s
EOT;

$_['ms_mail_product_purchased_no_email'] = <<<EOT
لقد تم شراء المنتجات التي قمت بطلبها من %s.

إسم العميل: %s

المنتجات:
%s
الإجمالي: %s
EOT;

$_['ms_mail_subject_seller_contact'] = 'رسالة جديدة من عميل';
$_['ms_mail_seller_contact'] = <<<EOT
لقد تم إستلام رسالة جديدة من عميل!

إسم العميل: %s

البريد الإلكتروني: %s

المنتج: %s

الرسالة:
%s
EOT;

$_['ms_mail_seller_contact_no_mail'] = <<<EOT
لقد تم إستلام رسالة جديدة من عميل!

إسم العميل: %s

المنتج: %s

الرسالة:
%s
EOT;

$_['ms_mail_product_purchased_info'] = <<<EOT
\n
عنوان الشحن:

%s %s
%s
%s
%s
%s %s
%s
%s
EOT;

$_['ms_mail_product_purchased_comment'] = 'ملاحظة: %s';

$_['ms_mail_subject_withdraw_request_submitted'] = 'طلب دفع';
$_['ms_mail_withdraw_request_submitted'] = <<<EOT
لقد تلقينا طلب الدفع. سوف تتلقى أرباحك بعد الموافقة على الطلب مباشرة.
EOT;

$_['ms_mail_subject_withdraw_request_completed'] = 'إخطار إكتمال الدفع';
$_['ms_mail_withdraw_request_completed'] = <<<EOT
طلب الدفع الخاص بكم قد تم الموافقة علية. سوف تتلقى أرباحك الأن.
EOT;

$_['ms_mail_subject_withdraw_request_declined'] = 'طلب الدفع رفض';
$_['ms_mail_withdraw_request_declined'] = <<<EOT
تم رفض طلب الدفع الخاص بكم. وقد عادت أموالك إلى رصيدك في %s.
EOT;

$_['ms_mail_subject_transaction_performed'] = 'معاملة جديدة';
$_['ms_mail_transaction_performed'] = <<<EOT
لقد تم إضافة معاملة جديدة في حسابك في %s.
EOT;

$_['ms_mail_subject_remind_listing'] = 'إدراج المنتج إنتهى';
$_['ms_mail_seller_remind_listing'] = <<<EOT
لقد إنتهى إدراج المنتج %s. يمكنك الذهاب إلى منطقة حساب البائع إذا كنت ترغب في إعادة إدراج المنتج.
EOT;

// *********
// * Admin *
// *********
$_['ms_mail_admin_subject_seller_account_created'] = 'لقد تم إنشاء حساب بائع جديد';
$_['ms_mail_admin_seller_account_created'] = <<<EOT
حساب بائع جديد لدى %s قد تم إنشائه!
إسم البائع: %s (%s)
البريد الإلكتروني: %s
EOT;

$_['ms_mail_admin_subject_seller_account_awaiting_moderation'] = 'حساب بائع جديد ينتظر الموافقة';
$_['ms_mail_admin_seller_account_awaiting_moderation'] = <<<EOT
تم إنشاء حساب بائع جديد في %s وينتظر الآن الموافقة.
إسم البائع: %s (%s)
البريد الإلكتروني: %s

يمكنك معالجة الطلب في منطقة البائعين في لوحة التحكم بالمتجر.
EOT;

$_['ms_mail_admin_subject_product_created'] = 'إضافة منتج جديد';
$_['ms_mail_admin_product_created'] = <<<EOT
لقد تم إضافة المنتج %s إلى %s.

يمكنك مشاهدة أو تعديل المنتج من لوحة التحكم بالمتجر.
EOT;

$_['ms_mail_admin_subject_new_product_awaiting_moderation'] = 'منتج جديد ينتظر الموافقة';
$_['ms_mail_admin_new_product_awaiting_moderation'] = <<<EOT
New product %s has been added to %s and is awaiting moderation.

يمكنك معالجة الطلب في منطقة المنتجات في لوحة التحكم بالمتجر.
EOT;

$_['ms_mail_admin_subject_edit_product_awaiting_moderation'] = 'تعديل في منتج ينتظر الموافقة';
$_['ms_mail_admin_edit_product_awaiting_moderation'] = <<<EOT
المنتج %s لدى %s قد تم تعديله وهو الأن ينتظر الموافقة.

يمكنك معالجة الطلب في منطقة المنتجات في لوحة التحكم بالمتجر.
EOT;

$_['ms_mail_admin_subject_withdraw_request_submitted'] = 'طلب دفع ينتظر الموافقة';
$_['ms_mail_admin_withdraw_request_submitted'] = <<<EOT
لقد تم إستلام طلب دفع جديد.

يمكنك معالجة الطلب في منطقة المالية في لوحة التحكم بالمتجر.
EOT;

// Success
$_['ms_success_product_published'] = 'لقد تم نشر المنتج';
$_['ms_success_product_unpublished'] = 'لقد تم إلغاء نشر المنتج';
$_['ms_success_product_created'] = 'لقد تم إنشاء المنتج';
$_['ms_success_product_updated'] = 'لقد تم تعديل المنتج';
$_['ms_success_product_deleted'] = 'لقد تم حذف المنتج';

// Errors
$_['ms_error_sellerinfo_seller_location_empty'] = 'عنوان المتجر علي الخريطه';
$_['ms_error_sellerinfo_nickname_empty'] = 'الإسم المستعار لا يمكن أن يكون فارغا';
$_['ms_error_sellerinfo_nickname_alphanumeric'] = 'الإسم المستعار يمكن أن يحتوي فقط على رموز أبجدية و رقمية';
$_['ms_error_sellerinfo_nickname_utf8'] = 'الإسم المستعار يمكن أن يحتوي فقط على رموز UTF-8 القابلة للطباعة';
$_['ms_error_sellerinfo_nickname_latin'] = 'الإسم المستعار يمكن أن يحتوي فقط على رموز أبجدية و رقمية والتشكيل';
$_['ms_error_sellerinfo_nickname_length'] = 'يجب أن تكون الإسم المستعار بين 4 و 50 حرفا';
$_['ms_error_sellerinfo_nickname_taken'] = 'هذا الإسم المستعار محجوز';
$_['ms_error_sellerinfo_company_length'] = 'اسم الشركة لا يمكن أن تكون أطول من 50 حرفا';
$_['ms_error_sellerinfo_description_length'] = 'الوصف لا يمكن أن يكون أطول من 1000 حرف';
$_['ms_error_sellerinfo_paypal'] = 'عنوان باي بال غير صالح';
$_['ms_error_sellerinfo_terms'] = 'تحذير: يجب أن توافق على %s!';
$_['ms_error_file_extension'] = 'إمتداد غير صالح';
$_['ms_error_file_type'] = 'نوع الملف غير صالح';
$_['ms_error_file_size'] = 'ملف كبير جدا';
$_['ms_error_image_too_small'] = 'أبعاد ملف الصورة هي صغيرة جدا. الحد الأدنى المسموح به لحجم الصورة هو: %s x %s (العرض × الارتفاع)';
$_['ms_error_image_too_big'] = 'أبعاد ملف الصورة هي كبيرة جدا. الحد الأقصى المسموح به لحجم الصورة هو: %s x %s (العرض × الارتفاع)';
$_['ms_error_file_upload_error'] = 'خطأ في تحميل ملف';
$_['ms_error_form_submit_error'] = 'حدث خطأ عند تقديم النموذج. يرجى الاتصال على صاحب المتجر للمزيد من المعلومات.';
$_['ms_error_form_notice'] = 'الرجاء مراجعة كافة علامات التبويب (كل اللغات) لمراجعة الأخطاء.';
$_['ms_error_product_name_empty'] = 'اسم المنتج لا يمكن أن يكون فارغا';
$_['ms_error_product_name_length'] = 'وينبغي أن يكون اسم المنتج بين %s و %s حروف';
$_['ms_error_product_description_empty'] = 'وصف المنتج لا يمكن أن يكون فارغا';
$_['ms_error_product_description_length'] = 'ينبغي أن يكون وصف المنتج بين %s و %s حروف';
$_['ms_error_product_tags_length'] = 'خط طويل جدا';
$_['ms_error_product_price_empty'] = 'الرجاء تحديد سعر المنتج الخاص بك';
$_['ms_error_product_price_invalid'] = 'السعر غير صالح';
$_['ms_error_product_price_low'] = 'سعر منخفض جدا';
$_['ms_error_product_price_high'] = 'السعر مرتفع جدا';
$_['ms_error_product_category_empty'] = 'الرجاء تحديد الفئة';
$_['ms_error_product_model_empty'] = 'طراز المنتج لا يمكن أن يكون فارغا';
$_['ms_error_product_model_length'] = 'ينبغي أن يكون طراز المنتج بين %s و %s حروف';
$_['ms_error_product_image_count'] = 'يرجى تحميل على الأقل %s صورة لمنتجك';
$_['ms_error_product_download_count'] = 'يرجى تقديم ما لا يقل عن %s وصلة تحميل لمنتجك';
$_['ms_error_product_image_maximum'] = 'لا يمكن إضافة أكثر من %s صورة';
$_['ms_error_product_download_maximum'] = 'لا يمكن إضافة أكثر من %s وصلة تحميل';
$_['ms_error_product_message_length'] = 'الرسالة لا يمكن أن تكون أطول من 1000 حرف';
$_['ms_error_product_attribute_required'] = 'هذه الخاصية مطلوبة';
$_['ms_error_product_attribute_long'] = 'لا يمكن أن تكون هذه القيمة أطول من %s رمز';
$_['ms_error_withdraw_amount'] = 'كمية غير صالح';
$_['ms_error_withdraw_balance'] = 'لا يوجد ما يكفي من الأموال على رصيدك';
$_['ms_error_withdraw_minimum'] = 'لا يمكن سحب أقل من الحد الأدنى';
$_['ms_error_contact_email'] = 'يرجى تحديد عنوان بريد إلكتروني صالح';
$_['ms_error_contact_captcha'] = 'كود الكابتشا غير صالح';
$_['ms_error_contact_text'] = 'الرسالة لا يمكن أن تكون أطول من 2000 حرفا';
$_['ms_error_contact_allfields'] = 'يرجى تعبئة جميع الحقول';
$_['ms_error_invalid_quantity_discount_priority'] = 'خطأ في حقل الأولوية - يرجى إدخال القيمة الصحيحة';
$_['ms_error_invalid_quantity_discount_quantity'] = 'يجب أن تكون الكمية 2 أو أكثر';
$_['ms_error_invalid_quantity_discount_price'] = 'كمية غير صالحة لسعر الخصم';
$_['ms_error_invalid_quantity_discount_dates'] = 'يجب ملء حقول التاريخ لتخفيضات الكمية';
$_['ms_error_invalid_special_price_priority'] = 'خطأ في حقل الأولوية - يرجى إدخال القيمة الصحيحة';
$_['ms_error_invalid_special_price_price'] = 'سعر خاص غير صالح';
$_['ms_error_invalid_special_price_dates'] = 'يجب ملء حقول التاريخ للأسعار الخاصة';
$_['ms_error_seller_product'] = 'لا يمكنك إضافة المنتج الخاص بك إلى السلة';
///for Trips App
$_['ms_error_personal_id']='حقل الرقم القومي مطلوب';
$_['ms_error_bank_iban']='حقل رقم الحساب البنكي مطلوب';
$_['ms_error_avatar']='حقل الصورة الشخصية مطلوب';
$_['ms_error_category']='حقل قسم الرحلة مطلوب  ';
$_['ms_error_car_license']='حقل رخصة السيارة مطلوب';
$_['ms_error_driving_license']='حقل رخصة القيادة مطلوب';
$_['ms_error_tourism_license']='حقل رخصة وزارة السياحة مطلوب';
$_['ms_error_car_type']='حقل نوع السيارة مطلوب';


// Account - General
$_['ms_account_unread_pm'] = 'لديك رسالة خاصة غير مقروءة';
$_['ms_account_unread_pms'] = 'لديك %s رسائل خاصة غير مقروءة';
$_['ms_account_register_seller'] = 'تسجيل حساب البائع';
$_['ms_account_register_seller_success_heading'] = 'حساب البائع الخاص بك تم إنشاؤه!';
$_['ms_account_register_seller_success_message']  = '<p>مرحبا بكم في %s!</p> <p>تهانينا! تم إنشاء حساب البائع الجديد الخاص بك بنجاح!</p> <p>يمكنك الآن الاستفادة من امتيازات البائع والبدء في بيع المنتجات الخاصة بك معنا.</p> <p>إذا كان لديك أي مشاكل, <a href="%s">اتصل بنا</a>.</p>';
$_['ms_account_register_seller_success_approval'] = '<p>مرحبا بكم في %s!</p> <p>تم تسجيل حساب البائع الخاص وينتظر الموافقة. سيتم إعلامك عن طريق البريد الإلكتروني بمجرد تنشيط حسابك من قبل صاحب متجر.</p><p>إذا كان لديك أي مشاكل, <a href="%s">اتصل بنا</a>.</p>';

$_['ms_seller'] = 'بائع';
$_['ms_seller_forseller'] = 'للبائع';
$_['ms_account_dashboard'] = 'لوحة القيادة';
$_['ms_account_seller_account'] = 'حساب البائع';
$_['ms_account_sellerinfo'] = 'بيانات البائع الشخصية';
$_['ms_account_sellerinfo_new'] = 'حساب بائع جديد';
$_['ms_account_newproduct'] = 'منتج جديد';
$_['ms_account_products'] = 'المنتجات';
$_['ms_account_transactions'] = 'المعاملات';
$_['ms_account_orders'] = 'الطلبات';
$_['ms_account_withdraw'] = 'طلب دفع';
$_['ms_account_stats'] = 'إحصائيات';

// Account - New product
$_['ms_account_newproduct_heading'] = 'منتج جديد';
$_['ms_account_newproduct_breadcrumbs'] = 'منتج جديد';
//General Tab
$_['ms_account_product_tab_general'] = 'عام';
$_['ms_account_product_tab_specials'] = 'أسعار خاصة';
$_['ms_account_product_tab_discounts'] = 'خصومات الكمية';
$_['ms_account_product_name_description'] = 'الاسم والوصف';
$_['ms_account_product_name'] = 'الاسم';
$_['ms_account_product_name_note'] = 'حدد اسما للمنتج الخاص بك';
$_['ms_account_product_description'] = 'الوصف';
$_['ms_account_product_description_note'] = 'وصف المنتج الخاص بك';
$_['ms_account_product_meta_description'] = 'وصف Meta Tag';
$_['ms_account_product_meta_description_note'] = 'تحديد وصف Meta Tag لمنتجك';
$_['ms_account_product_meta_keyword'] = 'كلمات Meta Tag';
$_['ms_account_product_meta_keyword_note'] = 'تحديد كلمات Meta Tag لمنتجك';
$_['ms_account_product_tags'] = 'الكلمات الدليلية';
$_['ms_account_product_tags_note'] = 'حدد علامات دليلية لمنتجك.';
$_['ms_account_product_price_attributes'] = 'الأسعار و الخصائص';
$_['ms_account_product_price'] = 'السعر';
$_['ms_account_product_price_note'] = 'اختر سعر المنتج الخاص بك';
$_['ms_account_product_listing_flat'] = 'رسوم الإدراج لهذا المنتج هي <span>%s</span>';
$_['ms_account_product_listing_percent'] = 'رسوم الإدراج لهذا المنتج تقوم على أساس سعر المنتج. رسوم الإدراج الحالية: <span>%s</span>.';
$_['ms_account_product_listing_balance'] = 'سيتم خصم هذا المبلغ من رصيد البائع الخاص بك.';
$_['ms_account_product_listing_paypal'] = 'سيتم نقلك إلى صفحة دفع باي بال بعد تسجيل المنتج.';
$_['ms_account_product_listing_itemname'] = 'رسوم إدراج المنتج عند %s';
$_['ms_account_product_listing_until'] = 'سيتم إدراج هذا المنتج حتى %s';
$_['ms_account_product_category'] = 'الفئة';
$_['ms_account_product_category_note'] = 'اختر فئة لمنتجك';
$_['ms_account_product_enable_shipping'] = 'تفعيل الشحن';
$_['ms_account_product_enable_shipping_note'] = 'حدد ما إذا كان المنتج الخاص بك يتطلب الشحن';
$_['ms_account_product_quantity'] = 'الكمية';
$_['ms_account_product_quantity_note']    = 'حدد كمية المنتج الخاص بك';
$_['ms_account_product_min_quantity'] = 'الحد الأدنى';
$_['ms_account_product_min_quantity_note'] = 'الحد الأدنى للكمية';
$_['ms_account_product_files'] = 'الملفات';
$_['ms_account_product_download'] = 'التنزيلات';
$_['ms_account_product_download_note'] = 'إيداع الملفات لمنتجك. الملفات المسموحة: %s';
$_['ms_account_product_push'] = 'دفع التحديثات للعملاء السابقين';
$_['ms_account_product_push_note'] = 'الملفات المعدلة أو المضافة حديثاً سوف تكون متاحة للعملاء السابقين';
$_['ms_account_product_image'] = 'الصور';
$_['ms_account_product_image_note'] = 'حدد الصور للمنتج الخاص بك. وسوف تستخدم الصورة الأولى كصورة مصغرة. يمكنك تغيير ترتيب الصور عن طريق سحبها. الملفات المسموحة: %s';
$_['ms_account_product_message_reviewer'] = 'رسالة للمراجع';
$_['ms_account_product_message'] = 'الرسالة';
$_['ms_account_product_message_note'] = 'رسالتك للمراجع';
//Data Tab
$_['ms_account_product_tab_data'] = 'البيانات';
$_['ms_account_product_model'] = 'الطراز';
$_['ms_account_product_sku'] = 'رمز المنتج';
$_['ms_account_product_sku_note'] = 'رمز المنتج';
$_['ms_account_product_upc']  = 'رمز المنتج العالمي';
$_['ms_account_product_upc_note'] = 'رمز المنتج العالمي';
$_['ms_account_product_ean'] = 'رقم المقالة الأوروبي';
$_['ms_account_product_ean_note'] = 'رقم المقالة الأوروبي';
$_['ms_account_product_jan'] = 'رقم المقالة الياباني';
$_['ms_account_product_jan_note'] = 'رقم المقالة الياباني';
$_['ms_account_product_isbn'] = 'رقم الكتاب العالمي القياسي';
$_['ms_account_product_isbn_note'] = 'رقم الكتاب العالمي القياسي';
$_['ms_account_product_mpn'] = 'رقم القطعة لدى المصنع';
$_['ms_account_product_mpn_note'] = 'رقم القطعة لدى المصنع';
$_['ms_account_product_manufacturer'] = 'الشركة المصنعة';
$_['ms_account_product_manufacturer_note'] = '(الإكمال التلقائي)';
$_['ms_account_product_tax_class'] = 'فئة الضريبة';
$_['ms_account_product_date_available'] = 'تاريخ التوفر';
$_['ms_account_product_stock_status'] = 'حالة الإنتهاء من المخزن';
$_['ms_account_product_stock_status_note'] = 'الحالة التي تظهر عندما يكون المنتج منتهي من المخزن';
$_['ms_account_product_subtract'] = 'إخصم من المخزن';

// Options
$_['ms_account_product_tab_options'] = 'الخيارات';
$_['ms_options_add'] = '+ إضافة خيار';
$_['ms_options_add_value'] = '+ إضافة قيمة';
$_['ms_options_required'] = 'إجعل الخيار مطلوب';
$_['ms_options_price_prefix'] = 'تغير رمز السعر';
$_['ms_options_price'] = 'السعر...';
$_['ms_options_quantity'] = 'الكمية...';


$_['ms_account_product_manufacturer'] = 'الشركة المصنعة';
$_['ms_account_product_manufacturer_note'] = '(الإكمال التلقائي)';
$_['ms_account_product_tax_class'] = 'فئة الضريبة';
$_['ms_account_product_date_available'] = 'تاريخ التوفر';
$_['ms_account_product_stock_status'] = 'حالة الإنتهاء من المخزن';
$_['ms_account_product_stock_status_note'] = 'الحالة التي تظهر عندما يكون المنتج منتهي من المخزن';
$_['ms_account_product_subtract'] = 'إخصم من المخزن';

$_['ms_account_product_priority'] = 'الأولوية';
$_['ms_account_product_date_start'] = 'تاريخ البدء';
$_['ms_account_product_date_end'] = 'تاريخ انتهاء';
$_['ms_account_product_sandbox'] = 'تحذير: بوابة الدفع في \'حالة التجربة\'. لن يتم تحصيل رسوم من حسابك.';



// Account - Edit product
$_['ms_account_editproduct_heading'] = 'تحرير المنتج';
$_['ms_account_editproduct_breadcrumbs'] = 'تحرير المنتج';

// Account - Clone product
$_['ms_account_cloneproduct_heading'] = 'استنساخ المنتج';
$_['ms_account_cloneproduct_breadcrumbs'] = 'استنساخ المنتج';

// Account - Relist product
$_['ms_account_relist_product_heading'] = 'اعادة إدراج المنتج';
$_['ms_account_relist_product_breadcrumbs'] = 'اعادة إدراج المنتج';

// Account - Seller
$_['ms_account_sellerinfo_heading'] = 'الملف الشخصي البائع';
$_['ms_account_sellerinfo_breadcrumbs'] = 'الملف الشخصي البائع';
$_['ms_account_sellerinfo_nickname'] = 'الإسم المستعار';
$_['ms_account_sellerinfo_nickname_note'] = 'حدد اسم الشهرة الخاص بك كبائع.';
$_['ms_account_sellerinfo_description'] = 'الوصف';
$_['ms_account_sellerinfo_description_note'] = 'أكتب وصف لنفسك';
$_['ms_account_sellerinfo_company'] = 'الشركة';
$_['ms_account_sellerinfo_company_note'] = 'شركتك (اختياري)';
$_['ms_account_sellerinfo_country'] = 'البلد';
$_['ms_account_sellerinfo_country_dont_display'] = 'لا تعرض البلد';
$_['ms_account_sellerinfo_country_note'] = 'حدد بلدك.';
$_['ms_account_sellerinfo_zone'] = 'المنطقة / الدولة';
$_['ms_account_sellerinfo_zone_select'] = 'اختر المنطقة / الدولة';
$_['ms_account_sellerinfo_zone_not_selected'] = 'لا توجد منطقة / دولة محددة';
$_['ms_account_sellerinfo_zone_note'] = 'حدد المنطقة/الدولة الخاصة بك من القائمة.';
$_['ms_account_sellerinfo_avatar'] = 'الصورة الرمزية';
$_['ms_account_sellerinfo_avatar_note'] = 'اختر صورة رمزية';
$_['ms_account_sellerinfo_paypal'] = 'باي بال';
$_["ms_account_sellerinfo_creditcart"]= "التحويل البنكى";

$_["ms_account_sellerinfo_paypal_note"]= "حدد عنوان باي بال الخاص بك";
$_["ms_account_sellerinfo_credit_note"]="أدخل معلومات البنك الخاصة بك";
$_['ms_account_sellerinfo_reviewer_message'] = 'الرسالة للمراجع';
$_['ms_account_sellerinfo_reviewer_message_note'] = 'رسالتك للمراجع';
$_['ms_account_sellerinfo_terms'] = 'قبول شروط';
$_['ms_account_sellerinfo_terms_note'] = 'لقد قرأت ووافقت على <a class="colorbox" href="%s" alt="%s"><b>%s</b></a>';
$_['ms_account_sellerinfo_fee_flat'] = 'هناك رسوم اشتراك بقيمة <span>%s</span> لتصبح بائع عند %s.';
$_['ms_account_sellerinfo_fee_balance'] = 'سيتم خصم هذا المبلغ من الرصيد الأولي الخاص بك.';
$_['ms_account_sellerinfo_fee_paypal'] = 'سيتم نقلك إلى صفحة دفع باي بال بعد تقديم النموذج.';
$_['ms_account_sellerinfo_signup_itemname'] = 'تسجيل حساب البائع عند %s';
$_['ms_account_sellerinfo_saved'] = 'تم حفظ بيانات حساب البائع.';

$_['ms_account_status'] = 'حالة حساب البائع الخاص بك هو: ';
$_['ms_account_status_tobeapproved'] = '<br />سوف تكون قادرا على استخدام حسابك حالما تتم الموافقة عليها من قبل صاحب متجر.';
$_['ms_account_status_please_fill_in'] = 'يرجى ملء النموذج التالي لإنشاء حساب بائع.';

$_['ms_seller_status_' . MsSeller::STATUS_ACTIVE] = 'فعال';
$_['ms_seller_status_' . MsSeller::STATUS_INACTIVE] = 'غير فعال';
$_['ms_seller_status_' . MsSeller::STATUS_DISABLED] = 'معطل';
$_['ms_seller_status_' . MsSeller::STATUS_DELETED] = 'محذوف';
$_['ms_seller_status_' . MsSeller::STATUS_UNPAID] = 'رسوم اشتراك غير مدفوعة';

// Account - Products
$_['ms_account_products_heading'] = 'المنتجات الخاصة بك';
$_['ms_account_products_breadcrumbs'] = 'المنتجات الخاصة بك';
$_['ms_account_products_image'] = 'الصورة';
$_['ms_account_products_product'] = 'المنتج';
$_['ms_account_products_sales'] = 'المبيعات';
$_['ms_account_products_earnings'] = 'الأرباح';
$_['ms_account_products_status'] = 'الحالة';
$_['ms_account_products_date'] = 'تاريخ الإضافة';
$_['ms_account_products_listing_until'] = 'مدرج حتى';
$_['ms_account_products_action'] = 'عمل';
$_['ms_account_products_noproducts'] = 'ليس لديك أي منتجات بعد!';
$_['ms_account_products_confirmdelete'] = 'هل أنت متأكد أنك تريد حذف المنتج الخاص بك?';

$_['ms_not_defined'] = 'غير معرف';

$_['ms_product_status_' . MsProduct::STATUS_ACTIVE] = 'فعال';
$_['ms_product_status_' . MsProduct::STATUS_INACTIVE] = 'غير فعال';
$_['ms_product_status_' . MsProduct::STATUS_DISABLED] = 'معطل';
$_['ms_product_status_' . MsProduct::STATUS_DELETED] = 'محذوف';
$_['ms_product_status_' . MsProduct::STATUS_UNPAID] = 'رسوم اشتراك غير مدفوعة';

// Account - Conversations and Messages
$_['ms_account_conversations'] = 'المحادثات';
$_['ms_account_messages'] = 'الرسائل';

$_['ms_account_conversations_heading'] = 'المحادثات الخاصة بك';
$_['ms_account_conversations_breadcrumbs'] = 'المحادثات الخاصة بك';

$_['ms_account_conversations_status'] = 'الحالة';
$_['ms_account_conversations_date_created'] = 'تاريخ الانشاء';
$_['ms_account_conversations_with'] = 'المحادثة مع';
$_['ms_account_conversations_title'] = 'العنوان';

$_['ms_conversation_title_product'] = 'استفسار حول المنتج: %s';
$_['ms_conversation_title'] = 'استفسار من %s';

$_['ms_account_conversations_read'] = 'قراءة';
$_['ms_account_conversations_unread'] = 'غير مقروءة';

$_['ms_account_messages_heading'] = 'الرسائل';
$_['ms_account_messages_breadcrumbs'] = 'الرسائل';

$_['ms_message_text'] = 'رسالتك';
$_['ms_post_message'] = 'إرسال رسالة';

$_['ms_customer_does_not_exist'] = 'تم حذف حساب العميل';
$_['ms_error_empty_message'] = 'الرسالة لا يمكن أن تترك فارغة';

$_['ms_mail_subject_private_message'] = 'رسالة خاصة جديدة واردة';
$_['ms_mail_private_message'] = <<<EOT
لقد تلقيت رسالة خاصة جديدة من %s!

%s

%s

يمكنك الرد من منطقة الرسائل في حسابك.
EOT;


$_['ms_mail_subject_seller_vote'] = 'التصويت لصالح بائع';
$_['ms_mail_seller_vote_message'] = 'التصويت لصالح بائع';

// Account - Transactions
$_['ms_account_transactions_heading'] = 'المالية الخاصة بك';
$_['ms_account_transactions_breadcrumbs'] = 'المالية الخاصة بك';
$_['ms_account_transactions_balance'] = 'رصيدك الحالي:';
$_['ms_account_transactions_earnings'] = 'أرباحك حتى الآن:';
$_['ms_account_transactions_records'] = 'سجل الميزان';
$_['ms_account_transactions_description'] = 'الوصف';
$_['ms_account_transactions_amount'] = 'الكمية';
$_['ms_account_transactions_notransactions'] = 'ليس لديك أي معاملات بعد!';

// Payments
$_['ms_payment_payments'] = 'المدفوعات';
$_['ms_payment_order'] = 'طلب #%s';
$_['ms_payment_type_' . MsPayment::TYPE_SIGNUP] = 'رسوم الاشتراك';
$_['ms_payment_type_' . MsPayment::TYPE_LISTING] = 'رسوم الإدراج';
$_['ms_payment_type_' . MsPayment::TYPE_PAYOUT] = 'الدفع اليدوي';
$_['ms_payment_type_' . MsPayment::TYPE_PAYOUT_REQUEST] = 'طلب دفع';
$_['ms_payment_type_' . MsPayment::TYPE_RECURRING] = 'دفع متكرر';
$_['ms_payment_type_' . MsPayment::TYPE_SALE] = 'تخفيض';

$_['ms_payment_status_' . MsPayment::STATUS_UNPAID] = 'غير مدفوع';
$_['ms_payment_status_' . MsPayment::STATUS_PAID] = 'مدفوع';

// Account - Orders
$_['ms_account_orders_heading'] = 'طلباتك';
$_['ms_account_orders_breadcrumbs'] = 'طلباتك';
$_['ms_account_orders_id'] = 'طلب رقم #';
$_['ms_account_orders_customer'] = 'العميل';
$_['ms_account_orders_products'] = 'المنتجات';
$_['ms_account_orders_total'] = 'إجمالي الكمية';
$_['ms_account_orders_view'] = 'مشاهدة الطلب';
$_['ms_account_orders_noorders'] = 'ليس لديك اي طلبات الشراء بعد!';
$_['ms_account_orders_change_status']    = 'تغير ترتيب الحالة';

// Account - Dashboard
$_['ms_account_dashboard_heading'] = 'لوحة معلومات البائع';
$_['ms_account_dashboard_breadcrumbs'] = 'لوحة معلومات البائع';
$_['ms_account_dashboard_orders'] = 'أخر طلبات الشراء';
$_['ms_account_dashboard_overview'] = 'نظرة عامة';
$_['ms_account_dashboard_seller_group'] = 'مجموعة البائع';
$_['ms_account_dashboard_listing'] = 'رسوم الإدراج';
$_['ms_account_dashboard_sale'] = 'رسوم بيع';
$_['ms_account_dashboard_royalty'] = 'أتعاب أدبية';
$_['ms_account_dashboard_stats'] = 'احصائيات';
$_['ms_account_dashboard_balance'] = 'الرصيد القائم';
$_['ms_account_dashboard_total_sales'] = 'إجمالي المبيعات';
$_['ms_account_dashboard_total_earnings'] = 'إجمالي الأرباح';
$_['ms_account_dashboard_sales_month'] = 'المبيعات هذا الشهر';
$_['ms_account_dashboard_earnings_month'] = 'الأرباح هذا الشهر';
$_['ms_account_dashboard_nav'] = 'ملاحة سريعة';
$_['ms_account_dashboard_nav_profile'] = 'تعديل الملف الشخصي للبائع';
$_['ms_account_dashboard_nav_product'] = 'إنشاء منتج جديد';
$_['ms_account_dashboard_nav_products'] = 'إدارة المنتجات الخاصة بك';
$_['ms_account_dashboard_nav_orders'] = 'عرض طلباتك';
$_['ms_account_dashboard_nav_balance'] = 'عرض السجلات المالية الخاصة بك';
$_['ms_account_dashboard_nav_payout'] = 'طلب الدفع';

// Account - Request withdrawal
$_['ms_account_withdraw_heading'] = 'طلب الدفع';
$_['ms_account_withdraw_breadcrumbs'] = 'طلب الدفع';
$_['ms_account_withdraw_balance'] = 'رصيدك الحالي:';
$_['ms_account_withdraw_balance_available'] = 'متاح للسحب:';
$_['ms_account_withdraw_minimum'] = 'المبلغ الأدنى للدفع:';
$_['ms_account_balance_reserved_formatted'] = '-%s في انتظار انسحاب';
$_['ms_account_balance_waiting_formatted'] = '-%s فترة الانتظار';
$_['ms_account_withdraw_description'] = 'طلب دفع عن طريق %s (%s) على %s';
$_['ms_account_withdraw_amount'] = 'الكمية:';
$_['ms_account_withdraw_amount_note'] = 'يرجى تحديد مبلغ للدفع';
$_['ms_account_withdraw_method'] = 'طريقة الدفع:';
$_['ms_account_withdraw_method_note'] = 'يرجى اختيار طريقة للدفع';
$_['ms_account_withdraw_method_paypal'] = 'باي بال';
$_['ms_account_withdraw_all'] = 'جميع الأرباح المتاحة حاليا';
$_['ms_account_withdraw_minimum_not_reached'] = 'رصيدك الإجمالي هو أقل من الحد الأدنى لمبلغ الدفع!';
$_['ms_account_withdraw_no_funds'] = 'لا يوجد أي أموال للسحب.';
$_['ms_account_withdraw_no_paypal'] = 'من فضلك <a href="index.php?route=seller/account-profile">حدد عنوان باي بال الخاص بك</a> أولاً!';

// Account - Stats
$_['ms_account_stats_heading'] = 'إحصائيات';
$_['ms_account_stats_breadcrumbs'] = 'إحصائيات';
$_['ms_account_stats_tab_summary'] = 'ملخص';
$_['ms_account_stats_tab_by_product'] = 'حسب المنتج';
$_['ms_account_stats_tab_by_year'] = 'حسب السنة';
$_['ms_account_stats_summary_comment'] = 'وفيما يلي ملخص بقيمة المبيعات الخاص بك';
$_['ms_account_stats_sales_data'] = 'بيانات مبيعات';
$_['ms_account_stats_number_of_orders'] = 'عدد الطلبات';
$_['ms_account_stats_total_revenue'] = 'إجمالي الإيرادات';
$_['ms_account_stats_average_order'] = 'متوسط الطلبات';
$_['ms_account_stats_statistics'] = 'إحصائيات';
$_['ms_account_stats_grand_total'] = 'إجمالي المبيعات النهائي';
$_['ms_account_stats_product'] = 'المنتج';
$_['ms_account_stats_sold'] = 'مباع';
$_['ms_account_stats_total'] = 'المجموع';
$_['ms_account_stats_this_year'] = 'هذا العام';
$_['ms_account_stats_year_comment'] = '<span id="sales_num">%s</span> المبيعات لفترة محددة';
$_['ms_account_stats_show_orders'] = 'مشاهدة الطلبات من: ';
$_['ms_account_stats_month'] = 'شهر';
$_['ms_account_stats_num_of_orders'] = 'عدد الطلبات';
$_['ms_account_stats_total_r'] = 'إجمالي الإيرادات';
$_['ms_account_stats_average_order'] = 'متوسط الطلبات';
$_['ms_account_stats_today'] = 'اليوم, ';
$_['ms_account_stats_yesterday'] = 'أمس, ';
$_['ms_account_stats_daily_average'] = 'المتوسط اليومي ل ';
$_['ms_account_stats_date_month_format'] = 'F Y';
$_['ms_account_stats_projected_totals'] = 'المجاميع المتوقعة ل ';
$_['ms_account_stats_grand_total_sales'] = 'إجمالي المبيعات النهائي';

// Product page - Seller information
$_['ms_catalog_product_sellerinfo'] = 'معلومات البائع';
$_['ms_catalog_product_contact'] = 'الاتصال بهذا البائع';

$_['ms_footer'] = '';

// Catalog - Sellers list
$_['ms_catalog_sellers_heading'] = 'الباعة';
$_['ms_catalog_sellers_country'] = 'البلد:';
$_['ms_catalog_sellers_website'] = 'Website:';
$_['ms_catalog_sellers_company'] = 'الشركة:';
$_['ms_catalog_sellers_totalsales'] = 'المبيعات:';
$_['ms_catalog_sellers_totalproducts'] = 'المنتجات:';
$_['ms_sort_country_desc'] = 'البلد (أ - ي)';
$_['ms_sort_country_asc'] = 'البلد (أ - ي)';
$_['ms_sort_nickname_desc'] = 'الاسم (أ - ي)';
$_['ms_sort_nickname_asc'] = 'الاسم (أ - ي)';

// Catalog - Seller profile page
$_['ms_catalog_sellers'] = 'الباعة';
$_['ms_catalog_sellers_empty'] = 'لا يوجد بائعين بعد.';
$_['ms_catalog_seller_profile_heading'] = 's% البيانات الشخصية';
$_['ms_catalog_seller_profile_breadcrumbs'] = 's% البيانات الشخصية';
$_['ms_catalog_seller_profile_about_seller'] = 'عن البائع';
$_['ms_catalog_seller_profile_products'] = 'بعض منتجات البائع';
$_['ms_catalog_seller_profile_tab_products'] = 'المنتجات';

$_['ms_catalog_seller_profile_country'] = 'البلد:';
$_['ms_catalog_seller_profile_zone'] = 'المنطقة / الدولة:';
$_['ms_catalog_seller_profile_website'] = 'الموقع:';
$_['ms_catalog_seller_profile_company'] = 'الشركة:';
$_['ms_catalog_seller_profile_totalsales'] = 'إجمالي المبيعات:';
$_['ms_catalog_seller_profile_totalproducts'] = 'إجمالي المنتجات:';
$_['ms_catalog_seller_profile_view'] = 'عرض جميع منتجات %s';

// Catalog - Seller's products list
$_['ms_catalog_seller_products_heading'] = 's% منتجات';
$_['ms_catalog_seller_products_breadcrumbs'] = 's% منتجات';
$_['ms_catalog_seller_products_empty'] = 'هذا البائع ليس لديه أية منتجات بعد!';

// Catalog - Seller contact dialog
$_['ms_sellercontact_title'] = 'إتصل بالبائع';
$_['ms_sellercontact_name'] = 'اسمك';
$_['ms_sellercontact_email'] = 'البريد الإلكتروني الخاص بك';
$_['ms_sellercontact_text'] = 'رسالتك';
$_['ms_sellercontact_captcha'] = 'Captcha';
$_['ms_sellercontact_sendmessage'] = 'ارسل رسالة الى %s';
$_['ms_sellercontact_success'] = 'تم إرسال رسالتك بنجاح';
$_['products_state_changed_successfully'] = "لقد غيرت حالة منتجاتك بنجاح :)";
$_['products_state_changed_unsuccessfully'] = "حدث خطأ :(";

$_['text_category_name'] = "اسم القسم";
$_['text_value'] = "قيمه العموله";
///for trips App
$_['error_product_name'] = 'حقل الإسم مطلوب';
$_['error_product_description'] = 'حقل الوصف مطلوب';
$_['error_product_price'] = 'حقل السعر مطلوب';
$_['error_pickup_point'] = 'حقل نقطة الإلتقاء مطلوب';
$_['error_destination_point'] = 'حقل نقطة الوجهة مطلوب';
$_['error_min_no_seats'] = 'حقل اقل عدد مقاعد مطلوب';
$_['error_max_no_seats'] = 'حقل اقصى عدد مقاعد مطلوب';
$_['error_from_date'] = 'حقل من تاريخ مطلوب';
$_['error_to_date'] = 'حقل إلى تاريخ مطلوب';
$_['error_area_id'] = 'حقل المنطقة مطلوب';
$_['error_time'] = 'حقل التوقيت مطلوب';
$_['error_images'] = 'حقل الصورة مطلوب';
$_['available_seats'] = 'لا يمكنك حجز أكثر من عدد المقاعد المتاحة: ';
$_['max_seats'] = 'لا يمكنك حجز أكثر من العدد الأقصى للمقاعد: ';
$_['min_seats'] = 'لا يمكنك حجز أقل من العدد الأدنى للمقاعد: ';
?>
