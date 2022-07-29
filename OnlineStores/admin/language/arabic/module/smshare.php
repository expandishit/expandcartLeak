<?php

// Heading Goes here:
$_['heading_title']    = 'تنبيهات الرسائل القصيرة';

// Text
$_['text_module']      = 'التطبيقات';
$_['text_success']     = 'تم تعديل الموديل بنجاح';
$_['text_left']        = 'يسار';
$_['text_right']       = 'يمين';
$_['text_home']        = 'الرئيسية';

$_['text_yes']         = 'نعم';
$_['text_no']          = 'لا';


// Entry
$_['smshare_entry_username'] = 'اسم المستخدم';
$_['smshare_entry_passwd']   = 'كلمة المرور';

$_['smshare_entry_status']   = 'الحالة';

$_['smshare_entry_notify_customer_by_sms_on_registration'] = 'تنبيه العميل عند <b>التسجيل</b>: <br />' . 
														     '<span class="help">ارسال رساله قصيرة للعميل عند اكتمال التسجيل</span>';

$_['smshare_entry_cstmr_reg_available_vars']               = 'يمكنك ان تستخدم هذه فقط<br><b>{firstname}</b><br><b>{lastname}</b><br><b>{telephone}</b><br><b>{password}</b>';
															 
$_['smshare_entry_notify_customer_by_sms_on_checkout']     = 'تنبيه العميل عند <b>طلب جديد</b>:   <br />' .
															 '<span class="help">ارسال رساله للعميل عند إتمام طلب جديد</span>';
															 
$_['smshare_entry_ntfy_admin_by_sms_on_reg'] 		       = 'تنبيه صاحب الموقع عند <b>التسجيل</b>: <br />' .
														     '<span class="help">ارسال رسالة عند تسجيل عميل جديد بالموقع</span>';

$_['smshare_entry_notify_admin_by_sms_on_checkout']        = 'تنبيه صاحب الموقع عند <b>طلب جديد</b>:<br />' . 
															 '<span class="help">ارسال رسالة لصاحب الموقع عند انشاء طلب جديد</span>';
															 
$_['smshare_entry_notify_extra_by_sms_on_checkout']        = 'أرقام هواتف إضافية لإرسال تنبيهات لهم:  <br />' .
															 '<span class="help">اي ارقام اضافيه تريد اضافتها (مفصولة بفواصل). ' .
															 '<br />عند اضافة رقم اضافي للتنبيه ستصله رسائل تنبيه حتى إذا كانت غير مفعله لصاحب الموقع</span>';
$_['smshare_entry_order_available_vars']               = 'يمكنك ان تستخدم هذه فقط<br><b>{order_id}</b><br><b>{order_date}</b>';

$_['smshare_entry_notify_seller_on_status_change'] = 'تنبيه التاجر عندما <b>يقوم المدير بتغيير حالتة</b>: <br />
                                                        <span class="help">إرسال رسالة قصيرة للتاجر عند تغيير حالتة</span>';

// Error
$_['error_permission'] = 'تنبيه لا تملك صلاحية لتعديل الموديل!';
$_['text_footer']      = 'الرجاء الرد على هذه الرسالة عند وجود اي استفسار.';


$_['text_gateway_setup'] = 'إعدادات مزود الخدمة';
$_['text_sms_tem'] = 'قوالب الرسائل';
$_['text_customer_notif'] = 'تبيهات العملاء';
$_['text_admin_notif'] = 'تنبيهات الإدارة';
$_['text_seller_notif'] = 'تنبيهات التاجر';
$_['text_sms_filter'] = 'فلاتر الأرقام';
$_['text_number_rewrite'] = 'تعديل الأرقام';
$_['text_logs'] = 'السجل';
$_['text_api_url'] = 'وصلة API';
$_['text_api_http_method'] = 'طريقة HTTP';
$_['text_get'] = 'GET';
$_['text_post_1'] = 'POST multipart/form-data';
$_['text_post_2'] = 'POST application/x-www-form-urlencoded';
$_['text_api_method_help'] = '<p>
                          POST multipart/form-data أم POST application/x-www-form-urlencoded؟
                          كما جرت العادة، تحقق من شرح مزود الخدمة. ولكن هنا بعض النصائح:
                          </p>
                              <ul>
                                  <li>عادة API من بوابات SMS القديمة تستخدم POST multipart/form-data</li>
                                  <li>و API من بوابات SMS الحديثة تستخدم POST application/x-www-form-urlencoded</li>
                              </ul>
                          </p>';
$_['text_dest_field'] = 'حقل الوجهة';
$_['text_dest_field_help'] = 'هذا هو اسم المتغير الذي يمثل أرقام الوجهة.';
$_['text_dest_field_placeholder'] = 'مثال: mobiles أو destinations';
$_['text_msg_field'] = 'حقل الرسالة';
$_['text_msg_field_help'] = 'هذا هو اسم المتغير الذي يمثل الرسالة.';
$_['text_msg_field_placeholder'] = 'مثال: message';
$_['text_unicode'] = 'يونيكود؟';
$_['text_unicode_help'] = 'بعض API من مزودي الخدمة (مثلا: للغة العربية) يتطلب نص الرسالة ليتم تحويلها إلى يونيكود.';
$_['text_unicode_help_2'] = 'نقوم بحذف \u قبل الإرسال. مثال: <b>test</b> ستم إرسالها كالتالي: <b>0074006500730074</b>';
$_['text_additional_fields'] = 'حقول إضافية';
$_['text_add_new_field'] = 'إضافة حقل';
$_['text_name'] = 'الإسم';
$_['text_field_name'] = 'إسم الحقل';
$_['text_value'] = 'القيمة';
$_['text_field_value'] = 'قيمة الحقل';
$_['text_url_encode'] = 'ترميز الوصلة';
$_['text_remove_field'] = 'حذف الحقل';
$_['text_url_encode_help'] = '                            <p style="color: black;">ما هو ترميز الوصلة؟</p>
                            <p>ترميز الوصلة هو تحويل الأحرف إلى التنسيق الذي يمكن إرسالها عن طريق الإنترنت.</p>
                            <p>يتعين علينا أن نستخدم ترميز الوصلة لجميع معلومات GET لأن معلومات POST هي المشفرة تلقائيا.</p>
                            <p>بعض API لا تفهم الحقول ذات ترميز الوصلة عند الإرسال عن طريق GET. إذا كانت هذه هي الحالة، قم بإلغاء ترميز الوصلة للحقول التي بها هذه المشكلة.</p>';
$_['text_sms_template_system'] = 'نظام قوالب الرسائل';
$_['text_sms_temp_sys_1'] = 'يمكنك إستخدام قيم معرفة مسبقاً تعمل كمحتوى يتم إستبداله بالمعلومات الحقيقية في وقت الإرسال.';
$_['text_available_var'] = 'القيم المتاحة للإستخدام';
$_['text_arrow'] = '←';
$_['text_firstname'] = 'الإسم الأول';
$_['text_lastname'] = 'الإسم الأخير';
$_['text_phonenumber'] = 'رقم الهاتف';
$_['text_orderid'] = 'رقم الطلب';
$_['text_total'] = 'الإجمالي';
$_['text_storeurl'] = 'وصلة المتجر';
$_['text_shippingadd1'] = 'عنوان الشحن 1';
$_['text_shippingadd2'] = 'عنوان الشحن 2';
$_['text_payadd1'] = 'عنوان الدفع 1';
$_['text_payadd2'] = 'عنوان الدفع 2';
$_['text_paymethod'] = 'طريقة الدفع';
$_['text_shipmethod'] = 'طريقة الشحن';
$_['text_sms_system_example'] = 'مثال:<span class="help">إذا قمت بإدخال التالي: </span>
					<span class="help"><i>مرحبا <b>{firstname}</b>, شكرا لطلبك على mystore.com رقم الطلب <b>{order_id}</b> الإجمالي <b>{total}</b></i></span>
					<br />
					<span class="help">في المرة القادمة التي يقوم فيها عميل (كمثال إسمه أحمد) بعمل طلب. سيقوم بإستلام رسالة قصيرة تحتوي على الأتي: </span>
					<span class="help"><i>مرحبا <b>أحمد</b>, شكرا لطلبك على mystore.com رقم الطلب <b>9999</b> الإجمالي <b>$9999.99</b></i></span>';
$_['text_cus_reg_temp'] = 'قالب رسالة تنبيه العميل عند <b>التسجيل</b>';
$_['text_cus_order_temp'] = 'قالب رسالة تنبيه العميل عند <b>طلب جديد</b>';
$_['text_no_send_kw'] = 'الكلمات المفتاحية لعدم الإرسال';
$_['text_no_send_help'] = 'عدم إرسال رسائل للعميل عند <b>طلب جديد</b> إذا تم إستعمال أحد الكلمات التالية في الكوبون عند إتمام الطلب.
					<br />
					كلمة واحدة في كل سطر (الكلمة يمكن أن تحتوي على مسافات).';
$_['text_sms_order_status'] = 'قالب الرسائل عند تغيير حالة الطلب';
$_['text_sms_seller_status'] = 'قالب الرسائل عند تغيير حالة التاجر';
$_['text_sms_order_status_help'] = 'قالب الرسائل الذي سيتم إستخدامه عندما تقوم بتغيير حالة الطلب في صفحة سجل الطلب.
                  (يجب أن تكون قد إخترت حقل <i>&ldquo;تنبيه بالرسائل القصيرة&rdquo;</i>).
                  <br />
                  <br />
                  بالإضافة إلى القيم المعرفة السابقة, يمكنك إستخدام قيمتان معرفتان هنا: <b>{default_template}</b> و  <b>{order_id}</b> و <b>{order_date}</b> و<b>{comment}</b>
                  و هي التعليق الذي تكتبه عند إضافة تغيير حالة الطلب.
                  <br />
                  <br />
                  مربعات الإدخال الفارغة ستستخدم القالب الإفتراضي.
                  <br />
                  <br />';
$_['text_sms_seller_status_help'] = 'قالب الرسائل الذى سيتم إستخدامة عندما يقوم المدير بتغيير حالة التاجر.
                  <br />
                  <br />
                  بالإضافة إالى المعرفات السابقة, يمكنك إستخدام هذة المعرفات: <b>{default_template}</b> , <b>{seller_email}</b> , <b>{seller_firstname}</b>, <b>{seller_lastname}</b>, <b>{seller_nickname}</b> 
                  <br />
                  <br />
                  مربعات الإدخال الفارغة ستستخدم القالب الإفتراضي.
                  <br />
                  <br />';
$_['text_add_new_fields'] = 'أضف حقل جديد';
$_['text_status'] = 'الحالة';
$_['text_seperator'] = '───────';
$_['text_admin_cust_reg'] = 'قالب رسائل تنبيه صاحب المتجر <b>"عند تسجيل عميل"</b>';
$_['text_admin_sms_temp'] = 'قالب رسالة تنبيه صاحب الموقع';
$_['text_admin_sms_temp_help'] = 'بالإضافة للقيم المعرفة السابقة يمكنك إستخدام قيمتان خاصتان <b>{default_template}</b> و <b>{compact_default_template}</b> و التي تستخدمها لوضع الرسالة الإفتراضية أو الرسالة الإفتراضية المصغرة لتقليل مساحة الرسالة';
$_['text_admin_order_status'] = 'قالب الرسائل عند تغيير حالة الطلب';
$_['text_admin_order_status_help'] = 'قالب الرسائل الذي سيتم إستخدامه عندما تقوم بتغيير حالة الطلب في صفحة سجل الطلب.
                  <br />
                  <br />
                  بالإضافة إلى القيم المعرفة السابقة, يمكنك إستخدام قيمتان معرفتان هنا: <b>{default_template}</b> و <b>{comment}</b>
                  و هي التعليق الذي تكتبه عند إضافة تغيير حالة الطلب.
                  <br />
                  <br />
                  مربعات الإدخال الفارغة ستستخدم القالب الإفتراضي.
                  <br />
                  <br />';
$_['text_add_new_fields_2'] = 'أضف حقل جديد';
$_['text_status_2'] = 'الحالة';
$_['text_seperator_2'] = '───────';
$_['text_phone_num_filter'] = 'فلاتر أرقام الهواتف: <i><b>الأرقام التي تبدأ بـ</b></i>';
$_['text_phone_num_filter_help'] = 'إرسالة رسالة قصيرة فقط إذا كان الرقم يبدأ بالأرقام التي تحددها هنا.<br/>
                                البدايات المختلفة يجب أن تكون مفصولة بفواصل. مثال: 00336,+336,06';
$_['text_filter_size'] = 'فلاتر أرقام الهواتف: <i><b>عدد أرقام الهاتف</b></i>';
$_['text_filter_size_help'] = 'إرسال رسائل قصيرة فقط إذا كان رقم الهاتف عدد أرقامه هو المحدد هنا. مثال: إذا قمت بتحديد قيمة 8 فإن الرسائل سترسل لـ 12345678 و ليس لـ 2345678.';
$_['text_phone_rewrite'] = 'إعادة كتابة أرقام الهواتف';
$_['text_phone_rewrite_help'] = 'تغيير في أرقام الهواتف قبل إرسال الرسائل لها.
                            <br />
                            يتم تطبيق قواعد إعادة الكتابة بعد تطبيق فلاتر أرقام الهواتف.';
$_['text_replace_1_occ'] = 'إستبدل أول وقوع لـ';
$_['text_pattern'] = 'الرقم';
$_['text_by'] = 'بالتالي';
$_['text_substitution'] = 'النص المستبدل به';
$_['text_enable_logs'] = 'تفعيل السجلات';
$_['text_enable_logs_help'] = 'سيتم طباعة سجل مطول إلى ملف السجل. مفيدة عند الحاجة إلى معرفة ما يجري.';
$_['text_api_type'] = 'نوع api';
$_['text_json'] = 'json';
$_['text_xml'] = 'xml (vodafone)';
$_['text_vodafone_note'] = 'في حاله فودافون من فضلك أضف AccountId و SenderName و SecretKey و Password';

$_['text_sms_confirm'] = 'تأكيد الهاتف عند <b>طلب جديد</b>:   <br />' .
    '<span class="help">تأكيد هاتف العميل عن طريق الرسائل القصيرة عند عمل طلب جديد</span>';
$_['text_sms_confirm_per_order'] = 'تأكيد الهاتف مع <b>كل طلب جديد</b>:   <br />' .
    '<span class="help">تأكيد هاتف العميل عن طريق الرسائل القصيرة عند كل طلب حتي في حالة تأكيد الرقم من قبل</span>';
$_['text_sms_confirm_trials'] = 'الحد الأقصى للتأكيد عبر الرسائل القصيرة';
$_['text_sms_confirm_trials_help'] = 'الحد الأقص من المحاولات التي يمكن للعميل طلب إعادة إرسالة الرسالة التي تحتوي على كود التأكيد';
$_['text_sms_confirm_template'] = 'قالب رسالة تأكيد رقم الهاتف';
$_['text_sms_confirm_template_help'] = 'يمكنك فقط إستخدام <b>{firstname}</b><br><b>{lastname}</b><br><b>{phonenumber}</b> <br>ويتوجب عليك إستخدام <b>{confirm_code}</b> لكي تحتوي الرسالة على كود التأكيد';

$_['text_tab_supported'] = 'مزودي الخدمة';
$_['text_supported_providers'] = 'ندعم العديد من مزودي خدمة الرسائل القصيرة في جميع الدول، ومنها';
$_['text_supported_providers_help'] = 'إذا أردت المساعدة في تفعيل أي مزود خدمة من الأعلى أو أي مزود خدمة أخر برجاء التحدث إلى أحد ممثلي خدمة العملاء.';


$_['activation_message_template'] = 'قالب رسالة التفعيل';
$_['activation_message_template_note'] = 'يمكنك إستخدام المتغير التالي<br /><b>{activationToken}</b>';

$_['code_settings'] = 'إعدادات كود التفعيل';
$_['code_length'] = 'طول الكود';
$_['code_type'] = 'الكود مكون من:';
$_['code_alphanumeric'] = 'أرقام وحروف';
$_['code_numeric'] = 'أرقام';
