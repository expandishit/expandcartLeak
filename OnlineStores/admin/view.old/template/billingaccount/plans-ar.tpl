<?php include_jsfile($header, $footer, 'view/template/billingaccount/plans-ar.tpl.js'); ?>
<?php include_cssfile($header, $footer, 'view/template/billingaccount/plans-ar.css'); ?>
<?php echo $header; ?>

<script>

</script>

<div class="row">
    <div class="col-lg-12">

        <div class="row" id="expandPackages">
        <div class="col-lg-12">
            <div class="main-box clearfix">
                <header class="main-box-header clearfix">
                    <h2 class="text-center"><?php echo $text_upgradeExpand; ?></h2>
                    <?php if ($billingAccess != "1") { ?>
                    <div class="alert alert-warning" style="margin-bottom: 0px;">
                        <?php echo $text_no_billaccess ; ?>
                    </div>
                    <?php } ?>
                </header>
            </div>
            <div class="main-box clearfix">
                <header class="main-box-header clearfix"  style="min-height: 30px !important;">
                </header>

                <div class="main-box-body clearfix">
                    <div class="col-sm-4 pricing-rtl-column">
                        <div class="pricing-custom">
                            <div class="price-box" data-plan="standard-plan">
                                <span class="radio-select"></span>
                                <span class="plan-name">Stanard</span>
                                <div class="price-text">
                                    <span class="start-at"></span>
                                    <span class="amount-period">
                                        <span class="amount">٢٩</span>
                                        <span class="period">دولار / شهر</span>
                                    </span>
                                    <span class="term"></span>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-custom">
                            <div class="price-box" data-plan="business-plan">
                                <span class="radio-select"></span>
                                <span class="plan-name">Business</span>
                                <div class="price-text">
                                    <span class="amount-period">
                                        <span class="amount">٤٩</span>
                                        <span class="period">دولار / شهر</span>
                                    </span>
                                    <span class="term">تدفع سنوياً</span>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-custom">
                            <div class="price-box selected" data-plan="ultimate-plan">
                                <span class="radio-select"></span>
                                <span class="plan-name">Ultimate</span>
                                <div class="price-text">
                                    <span class="amount-period">
                                        <span class="amount">٩٩</span>
                                        <span class="period">دولار / شهر</span>
                                    </span>
                                    <span class="term">تدفع سنوياً</span>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-custom">
                            <div class="price-box" data-plan="enterprise-plan">
                                <span class="radio-select"></span>
                                <span class="plan-name">Enterprise</span>
                                <div class="price-text">
                                    <span class="amount-period">
                                        <span class="amount">٢٤٩</span>
                                        <span class="period">دولار / شهر</span>
                                    </span>
                                    <span class="term">تدفع سنوياً</span>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-talk-to-sales">
                            <h5>هل انت غير متأكد<br>اي الباقات افضل لك؟</h5>
                            <p class="desc">ما عليك سوى التقاط الهاتف والاتصال بنا الان و سنساعدك في اختيار الباقة المثالية لتناسب احتياجاتك.</p>
                            <div class="numbers">
                                <div class="num">
                                    <i class="fa fa-phone-square transparent num-icon"></i>
                                    <a href="tel:+13022613590" class="num-text">3590 261 302 1+</a>
                                </div>

                                <div class="num">
                                    <i class="fa fa-phone-square transparent num-icon"></i>
                                    <a href="tel:+13022613445" class="num-text">3445 261 302 1+</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-8 pricing-rtl-column">
                        <div class="pricing-details-panel">
                            <div class="standard-plan pricing-plan-details">
                                <div class="plan-name-cta">
                                    <div class="plan-name">
                                        <h1>باقة Standard</h1>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://members.expandcart.com/cart.php?a=add&pid=2&language=Arabic" class="button btn btn-primary" target="">اشترك الان</a>
                                    </div>
                                </div>
                                <div class="plan-desc">شيئ بسيط لتبدء به</div>
                                <div class="plan-features-title">المميزات</div>
                                <div class="clearfix plan-features-list">
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>٥٠٠ منتج</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ترافيك غير محدود</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>٢٠ جيجا مساحة تخزينية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>إسم نطاق مجاني</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>0% عمولة على مبيعاتك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم لغات متعددة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم عملات متعددة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>قوالب إحترافية متجاوبة مجانية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>كوبونات خصم و قسائم هدايا</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مدونة لمتجرك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>صفحة إنهاء الطلب السريعة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ربط ببوابات الدفع و الشحن</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ربط مع Google Analytics و Facebook Pixel</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>نسخ احتياطية يومية</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم متكامل عن طريق الشات فقط</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تقييم المنتجات</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خاصية التصدير و الاستيراد</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>اقسام عرض اليوم</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>محرر قوالب متطور</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تخصيص قوالب البريد لتنبيهات الطلبات</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>خدمة أطلق متجري</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>مدير شخصي لحسابك لدينا</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>فلاتر منتجات متطورة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>تطبيق تنبيهات الرسائل القصيرة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>متجر على الفيسبوك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>انشاء حساب Google Adwords</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>شهادة SSL مجانا (https)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>تطبيقات هواتف مجانية</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>امكانيات تخصيص متطورة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>نقل بيانات مجانا</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>مساعدة شخصية في التسويق</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>روابط صديقة اوتوماتيكية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>منتجات ذات صلة اوتوماتيكية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>وصف و كلمات ميتا اوتوماتيكية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>تطبيق التجار المتعددين</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>نظام ولاء لعملائك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>إدارة حملاتك الإعلانية على جوجل ادوردز و متابعتها بإستمرار</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>جميع تطبيقاتنا مجاناً</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>إدارة حملاتك الإعلانية على فيسبوك و انستاقرام و متابعتها بإستمرار</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>خدمة السيو الإحترافية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>ادارة صفحات التواصل الإجتماعي</p></div>
                                    </div>


                                </div>
                            </div>

                            <div class="business-plan pricing-plan-details">
                                <div class="plan-name-cta">
                                    <div class="plan-name">
                                        <h1>باقة Business</h1>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://members.expandcart.com/cart.php?a=add&pid=4&language=Arabic" class="button btn btn-primary" target="">اشترك الان</a>
                                    </div>
                                </div>
                                <div class="plan-desc">نحن نبني متجرك نيابة عنك و يكون لديك مدير شخصي لحسابك لدينا</div>
                                <div class="plan-features-title">المميزات</div>
                                <div class="clearfix plan-features-list">

                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>١٠٠٠ منتج</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ترافيك غير محدود</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>٥٠ جيجا مساحة تخزينية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>إسم نطاق مجاني</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>0% عمولة على مبيعاتك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم لغات متعددة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم عملات متعددة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>قوالب إحترافية متجاوبة مجانية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>كوبونات خصم و قسائم هدايا</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مدونة لمتجرك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>صفحة إنهاء الطلب السريعة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ربط ببوابات الدفع و الشحن</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ربط مع Google Analytics و Facebook Pixel</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>نسخ احتياطية يومية</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم متكامل عن طريق (الهاتف، البربد الإلكتروني، الشات)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تقييم المنتجات</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خاصية التصدير و الاستيراد</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>اقسام عرض اليوم</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>محرر قوالب متطور</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تخصيص قوالب البريد لتنبيهات الطلبات</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خدمة أطلق متجري</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مدير شخصي لحسابك لدينا</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>فلاتر منتجات متطورة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تطبيق تنبيهات الرسائل القصيرة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>متجر على الفيسبوك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>انشاء حساب Google Adwords</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>شهادة SSL مجانا (https)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>تطبيقات هواتف مجانية</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>امكانيات تخصيص متطورة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>نقل بيانات مجانا</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>مساعدة شخصية في التسويق</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>روابط صديقة اوتوماتيكية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>منتجات ذات صلة اوتوماتيكية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>وصف و كلمات ميتا اوتوماتيكية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>تطبيق التجار المتعددين</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>نظام ولاء لعملائك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>إدارة حملاتك الإعلانية على جوجل ادوردز و متابعتها بإستمرار</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>جميع تطبيقاتنا مجاناً</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>إدارة حملاتك الإعلانية على فيسبوك و انستاقرام و متابعتها بإستمرار</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>خدمة السيو الإحترافية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>ادارة صفحات التواصل الإجتماعي</p></div>
                                    </div>

                                </div>
                                <div class="plan-cta-sep"></div>
                                <div class="plan-name-cta-bottom">
                                    <div class="plan-name">
                                        <h5>هل لديك ايه اسئلة عن الباقات او المميزات؟</h5>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://www.expandcart.com/ar/%D8%AA%D8%AD%D8%AF%D8%AB-%D9%85%D8%B9-%D8%A7%D9%84%D9%85%D8%A8%D9%8A%D8%B9%D8%A7%D8%AA/" class="button btn btn-primary" target="">تحدث مع المبيعات</a>
                                    </div>
                                </div>
                            </div>

                            <div class="ultimate-plan pricing-plan-details selected">
                                <div class="plan-name-cta">
                                    <div class="plan-name">
                                        <h1>باقة Ultimate</h1>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://members.expandcart.com/cart.php?a=add&pid=6&language=Arabic" class="button btn btn-primary" target="">اشترك الان</a>
                                    </div>
                                </div>
                                <div class="plan-desc">نحن نبني متجرك نيابة عنك مع <b>امكانيات تخصيص متطورة</b> و يكون لديك مدير شخصي لحسابك لدينا</div>
                                <div class="plan-features-title">المميزات</div>
                                <div class="clearfix plan-features-list">

                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>منتجات غير محدودة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ترافيك غير محدود</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مساحة تخزينية غير محدودة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>إسم نطاق مجاني</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>0% عمولة على مبيعاتك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم لغات متعددة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم عملات متعددة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>قوالب إحترافية متجاوبة مجانية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>كوبونات خصم و قسائم هدايا</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مدونة لمتجرك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>صفحة إنهاء الطلب السريعة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ربط ببوابات الدفع و الشحن</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ربط مع Google Analytics و Facebook Pixel</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>نسخ احتياطية يومية</p></div>
                                    </div>
                                    <div class="col-sm-4">

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم متكامل عن طريق (الهاتف، البربد الإلكتروني، الشات)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تقييم المنتجات</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خاصية التصدير و الاستيراد</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>اقسام عرض اليوم</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>محرر قوالب متطور</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تخصيص قوالب البريد لتنبيهات الطلبات</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خدمة أطلق متجري</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مدير شخصي لحسابك لدينا</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>فلاتر منتجات متطورة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تطبيق تنبيهات الرسائل القصيرة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>متجر على الفيسبوك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>انشاء حساب Google Adwords</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>شهادة SSL مجانا (https)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خصم 20% على تطبيقات الهواتف</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>امكانيات تخصيص متطورة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>نقل بيانات مجانا</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مساعدة شخصية في التسويق</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>روابط صديقة اوتوماتيكية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>منتجات ذات صلة اوتوماتيكية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>وصف و كلمات ميتا اوتوماتيكية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تطبيق التجار المتعددين</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>نظام ولاء لعملائك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>إدارة حملاتك الإعلانية على جوجل ادوردز و متابعتها بإستمرار</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>جميع تطبيقاتنا مجاناً</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>إدارة حملاتك الإعلانية على فيسبوك و انستاقرام و متابعتها بإستمرار</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>خدمة السيو الإحترافية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>ادارة صفحات التواصل الإجتماعي</p></div>
                                    </div>


                                </div>
                                <div class="plan-cta-sep"></div>
                                <div class="plan-name-cta-bottom">
                                    <div class="plan-name">
                                        <h5>هل لديك ايه اسئلة عن الباقات او المميزات؟</h5>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://www.expandcart.com/ar/%D8%AA%D8%AD%D8%AF%D8%AB-%D9%85%D8%B9-%D8%A7%D9%84%D9%85%D8%A8%D9%8A%D8%B9%D8%A7%D8%AA/" class="button btn btn-primary" target="">تحدث مع المبيعات</a>
                                    </div>
                                </div>
                            </div>

                            <div class="enterprise-plan pricing-plan-details">
                                <div class="plan-name-cta">
                                    <div class="plan-name">
                                        <h1>باقة Enterprise</h1>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://members.expandcart.com/cart.php?a=add&pid=8&language=Arabic" class="button btn btn-primary" target="">اشترك الان</a>
                                    </div>
                                </div>
                                <div class="plan-desc">فريق شخصي من الخبراء لمتجرك لدعم و زيادة نجاحك!</div>
                                <div class="plan-features-title">المميزات</div>
                                <div class="clearfix plan-features-list">
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>منتجات غير محدودة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ترافيك غير محدود</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مساحة تخزينية غير محدودة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>إسم نطاق مجاني</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>0% عمولة على مبيعاتك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم لغات متعددة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>دعم عملات متعددة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>قوالب إحترافية متجاوبة مجانية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>كوبونات خصم و قسائم هدايا</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مدونة لمتجرك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>صفحة إنهاء الطلب السريعة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ربط ببوابات الدفع و الشحن</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ربط مع Google Analytics و Facebook Pixel</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>نسخ احتياطية يومية</p></div>
                                    </div>
                                    <div class="col-sm-4">

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>اولوية في الدعم عن طريق (الهاتف، البربد الإلكتروني، الشات)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تقييم المنتجات</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خاصية التصدير و الاستيراد</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>اقسام عرض اليوم</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>محرر قوالب متطور</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تخصيص قوالب البريد لتنبيهات الطلبات</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خدمة أطلق متجري</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مدير شخصي لحسابك لدينا</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>فلاتر منتجات متطورة</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تطبيق تنبيهات الرسائل القصيرة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>متجر على الفيسبوك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>انشاء حساب Google Adwords</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>شهادة SSL مجانا (https)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تطبيقات هواتف مجانية</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>امكانيات تخصيص متطورة</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>نقل بيانات مجانا</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>مساعدة شخصية في التسويق</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>روابط صديقة اوتوماتيكية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>منتجات ذات صلة اوتوماتيكية</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>وصف و كلمات ميتا اوتوماتيكية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>تطبيق التجار المتعددين</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>نظام ولاء لعملائك</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>إدارة حملاتك الإعلانية على جوجل ادوردز و متابعتها بإستمرار</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>جميع تطبيقاتنا مجاناً</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>إدارة حملاتك الإعلانية على فيسبوك و انستاقرام و متابعتها بإستمرار</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>خدمة السيو الإحترافية</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>ادارة صفحات التواصل الإجتماعي</p></div>
                                    </div>

                                </div>
                                <div class="plan-cta-sep"></div>
                                <div class="plan-name-cta-bottom">
                                    <div class="plan-name">
                                        <h5>هل لديك ايه اسئلة عن الباقات او المميزات؟</h5>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://www.expandcart.com/ar/%D8%AA%D8%AD%D8%AF%D8%AB-%D9%85%D8%B9-%D8%A7%D9%84%D9%85%D8%A8%D9%8A%D8%B9%D8%A7%D8%AA/" class="button btn btn-primary" target="">تحدث مع المبيعات</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>