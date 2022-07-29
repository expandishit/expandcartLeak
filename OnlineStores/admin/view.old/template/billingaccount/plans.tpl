<?php include_jsfile($header, $footer, 'view/template/billingaccount/plans.expand.js'); ?>
<?php include_cssfile($header, $footer, 'view/template/billingaccount/plans.css'); ?>
<?php echo $header; ?>

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
                <header class="main-box-header clearfix" style="min-height: 30px !important;">

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
                                        <span class="amount">29</span>
                                        <span class="period">/month</span>
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
                                        <span class="amount">49</span>
                                        <span class="period">/month</span>
                                    </span>
                                    <span class="term">Billed annually</span>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-custom">
                            <div class="price-box selected" data-plan="ultimate-plan">
                                <span class="radio-select"></span>
                                <span class="plan-name">Ultimate</span>
                                <div class="price-text">
                                    <span class="amount-period">
                                        <span class="amount">99</span>
                                        <span class="period">/month</span>
                                    </span>
                                    <span class="term">Billed annually</span>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-custom">
                            <div class="price-box" data-plan="enterprise-plan">
                                <span class="radio-select"></span>
                                <span class="plan-name">Enterprise</span>
                                <div class="price-text">
                                    <span class="amount-period">
                                        <span class="amount">249</span>
                                        <span class="period">/month</span>
                                    </span>
                                    <span class="term">Billed annually</span>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-talk-to-sales">
                            <h5>Not sure which plan<br>is right for you?</h5>
                            <p class="desc">Just pick up the phone and give us a call. We'll help you choose the perfect plan to suit your needs.</p>
                            <div class="numbers">
                                <div class="num">
                                    <i class="fa fa-phone-square transparent num-icon"></i>
                                    <a href="tel:+13022613590" class="num-text">+1 302 261 3590</a>
                                </div>

                                <div class="num">
                                    <i class="fa fa-phone-square transparent num-icon"></i>
                                    <a href="tel:+13022613445" class="num-text">+1 302 261 3445</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-8 pricing-rtl-column">
                        <div class="pricing-details-panel">
                            <div class="standard-plan pricing-plan-details">
                                <div class="plan-name-cta">
                                    <div class="plan-name">
                                        <h1>Standard Plan</h1>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://members.expandcart.com/cart.php?a=add&pid=2&language=English" class="button btn btn-primary" target="">Sign Up Now</a>
                                    </div>
                                </div>
                                <div class="plan-desc">Something simple to start with</div>
                                <div class="plan-features-title">FEATURES</div>
                                <div class="clearfix plan-features-list">
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>500 products</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Unlimited bandwidth</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>20GB storage</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free domain name</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>0% commission on your sales</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi language support</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi currency support</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free premium responsive templates</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Discount coupons &amp; gift vouchers</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Blog for your store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>One-page checkout</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Payment &amp; shipping gateways integration</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Google Analytics &amp; Facebook Pixel integration</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Daily data backups</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Comprehensive support (chat only)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Product rating &amp; reviews</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Import &amp; export features</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Deal of the day sections</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced template editor</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Customize order notification emails</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Launch My Store service</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Personal account manager</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Advanced product filters</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>SMS notifications app</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Facebook store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Google Adwords account setup</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Free SSL certificate (https)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Free mobile apps</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Advanced customizations</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Free data migration</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Personalized marketing help</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Automatic friendly URLs</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Automatic related products</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Automatic meta keywords &amp; description</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Multi merchant store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Customer loyalty system</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Google Adwords campaign setup &amp; on-going optimization</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>All our apps are FREE</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Facebook &amp; Instagram campaign setup &amp; on-going optimization</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Professional SEO service</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Social media management</p></div>
                                    </div>
                                </div>
                            </div>

                            <div class="business-plan pricing-plan-details">
                                <div class="plan-name-cta">
                                    <div class="plan-name">
                                        <h1>Business Plan</h1>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://members.expandcart.com/cart.php?a=add&pid=4&language=English" class="button btn btn-primary" target="">Sign Up Now</a>
                                    </div>
                                </div>
                                <div class="plan-desc">We will build your online store on your behalf &amp; a Personal Account Manager</div>
                                <div class="plan-features-title">FEATURES</div>
                                <div class="clearfix plan-features-list">
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>1000 products</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Unlimited bandwidth</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>50GB storage</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free domain name</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>0% commission on your sales</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi language support</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi currency support</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free premium responsive templates</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Discount coupons &amp; gift vouchers</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Blog for your store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>One-page checkout</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Payment &amp; shipping gateways integration</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Google Analytics &amp; Facebook Pixel integration</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Daily data backups</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Comprehensive support (phone, emails, chat)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Product rating &amp; reviews</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Import &amp; export features</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Deal of the day sections</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced template editor</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Customize order notification emails</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Launch My Store service</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Personal account manager</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced product filters</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>SMS notifications app</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Facebook store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Google Adwords account setup</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free SSL certificate (https)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Free mobile apps</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Advanced customizations</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Free data migration</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Personalized marketing help</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Automatic friendly URLs</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Automatic related products</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Automatic meta keywords &amp; description</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Multi merchant store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Customer loyalty system</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Google Adwords campaign setup &amp; on-going optimization</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>All our apps are FREE</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Facebook &amp; Instagram campaign setup &amp; on-going optimization</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Professional SEO service</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Social media management</p></div>
                                    </div>
                                </div>
                                <div class="plan-cta-sep"></div>
                                <div class="plan-name-cta-bottom">
                                    <div class="plan-name">
                                        <h5>Have any questions about the plans or the features?</h5>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://www.expandcart.com/en/contact-sales/" class="button btn btn-primary" target="">Talk to Sales</a>
                                    </div>
                                </div>
                            </div>

                            <div class="ultimate-plan pricing-plan-details selected">
                                <div class="plan-name-cta">
                                    <div class="plan-name">
                                        <h1>Ultimate Plan</h1>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://members.expandcart.com/cart.php?a=add&pid=6&language=English" class="button btn btn-primary" target="">Sign Up Now</a>
                                    </div>
                                </div>
                                <div class="plan-desc">We will build your online store on your behalf with <b>advanced customizations</b> &amp; a Personal Account Manager</div>
                                <div class="plan-features-title">FEATURES</div>
                                <div class="clearfix plan-features-list">
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Unlimited products</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Unlimited bandwidth</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Unlimited storage</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free domain name</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>0% commission on your sales</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi language support</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi currency support</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free premium responsive templates</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Discount coupons &amp; gift vouchers</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Blog for your store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>One-page checkout</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Payment &amp; shipping gateways integration</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Google Analytics &amp; Facebook Pixel integration</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Daily data backups</p></div>
                                    </div>
                                    <div class="col-sm-4">

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Comprehensive support (phone, emails, chat)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Product rating &amp; reviews</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Import &amp; export features</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Deal of the day sections</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced template editor</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Customize order notification emails</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Launch My Store service</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Personal account manager</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced product filters</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>SMS notifications app</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Facebook store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Google Adwords account setup</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free SSL certificate (https)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>20% discount on mobile apps</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced customizations</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free data migration</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Personalized marketing help</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Automatic friendly URLs</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Automatic related products</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Automatic meta keywords &amp; description</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi merchant store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Customer loyalty system</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Google Adwords campaign setup &amp; on-going optimization</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>All our apps are FREE</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Facebook &amp; Instagram campaign setup &amp; on-going optimization</p></div>
                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Professional SEO service</p></div>

                                        <div class="q_icon_list"><i class="fa fa-times-circle pull-left transparent"></i><p>Social media management</p></div>
                                    </div>
                                </div>
                                <div class="plan-cta-sep"></div>
                                <div class="plan-name-cta-bottom">
                                    <div class="plan-name">
                                        <h5>Have any questions about the plans or the features?</h5>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://www.expandcart.com/en/contact-sales/" class="button btn btn-primary" target="">Talk to Sales</a>
                                    </div>
                                </div>
                            </div>

                            <div class="enterprise-plan pricing-plan-details">
                                <div class="plan-name-cta">
                                    <div class="plan-name">
                                        <h1>Enterprise Plan</h1>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://members.expandcart.com/cart.php?a=add&pid=8&language=English" class="button btn btn-primary" target="">Sign Up Now</a>
                                    </div>
                                </div>
                                <div class="plan-desc">Personalized staff for your store to boost your success!</div>
                                <div class="plan-features-title">FEATURES</div>
                                <div class="clearfix plan-features-list">
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Unlimited products</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Unlimited bandwidth</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Unlimited storage</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free domain name</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>0% commission on your sales</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi language support</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi currency support</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free premium responsive templates</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Discount coupons &amp; gift vouchers</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Blog for your store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>One-page checkout</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Payment &amp; shipping gateways integration</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Google Analytics &amp; Facebook Pixel integration</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Daily data backups</p></div>
                                    </div>
                                    <div class="col-sm-4">

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Comprehensive priority support (phone, emails, chat)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Product rating &amp; reviews</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Import &amp; export features</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Deal of the day sections</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced template editor</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Customize order notification emails</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Launch My Store service</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Personal account manager</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced product filters</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>SMS notifications app</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Facebook store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Google Adwords account setup</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free SSL certificate (https)</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free mobile apps</p></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Advanced customizations</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Free data migration</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Personalized marketing help</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Automatic friendly URLs</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Automatic related products</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Automatic meta keywords &amp; description</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Multi merchant store</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Customer loyalty system</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Google Adwords campaign setup &amp; on-going optimization</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>All our apps are FREE</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Facebook &amp; Instagram campaign setup &amp; on-going optimization</p></div>
                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Professional SEO service</p></div>

                                        <div class="q_icon_list"><i class="fa fa-check-circle pull-left transparent"></i><p>Social media management</p></div>
                                    </div>
                                </div>
                                <div class="plan-cta-sep"></div>
                                <div class="plan-name-cta-bottom">
                                    <div class="plan-name">
                                        <h5>Have any questions about the plans or the features?</h5>
                                    </div>
                                    <div class="plan-cta">
                                        <a href="https://www.expandcart.com/en/contact-sales/" class="button btn btn-primary" target="">Talk to Sales</a>
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