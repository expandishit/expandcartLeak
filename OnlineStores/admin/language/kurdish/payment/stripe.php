<?php
// = 
// Stripe Payment Gateway v230.4
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
// = 

$version  = 'V230.4'; 

//------------------------------------------------------------------------------
// Heading
//------------------------------------------------------------------------------
$_['heading_title']						 = 'Gateway Tezmînatê Stripe'; 
$_['text_stripe']						 = '<a target="blank" href="https://stripe.com"><img src="https://stripe.com/img/logo.png" alt="Stripe" title="Stripe" /></a>';
$_['text_payment']                       = 'Diravdanî'; 
$_['text_success']                       = 'Serkeftin we mîhengên guhartinê kiriye!'; 

$_['settings']  = 'Mîhengan'; 
$_['switch_text_enabled']  = 'Enabled'; 
$_['switch_text_disabled']  = 'Bêmecel'; 

//------------------------------------------------------------------------------
// Extension Settings
//------------------------------------------------------------------------------
$_['tab_extension_settings']			 = 'Mîhengan'; 
$_['heading_extension_settings']		 = 'Mîhengan'; 

$_['entry_status']						 = 'Cî'; 
$_['entry_sort_order']					 = 'Rêza rêzê'; 
$_['entry_title']						 = 'Nav'; 
$_['entry_button_text']					 = 'Nivîsa bişkoja'; 
$_['entry_button_class']				 = 'Button Class'; 
$_['entry_button_styling']				 = 'Stûna stûyê'; 

// Payment Page Text
$_['heading_payment_page_text']			 = 'Nivîsa Rûpelê Payment'; 

$_['entry_text_card_details']			 = 'Detayên karta'; 
$_['entry_text_use_your_stored_card']	 = 'Karta xwe ya hilanînê bikar bînin'; 
$_['entry_text_ending_in']				 = 'Dawîn'; 
$_['entry_text_use_a_new_card']			 = 'Karta nû bikar bînin'; 
$_['entry_text_card_name']				 = 'Nav li ser kartê'; 
$_['entry_text_card_number']			 = 'Hejmara karta'; 
$_['entry_text_card_type']				 = 'Tîpa kartê'; 
$_['entry_text_card_expiry']			 = 'Qedandina karta (mm / yy)'; 
$_['entry_text_card_security']			 = 'Koda ewlehiya karta (CVC)'; 
$_['entry_text_store_card']				 = 'Karta Store ji bo karanîna pêşerojê'; 
$_['entry_text_please_wait']			 = 'Ji kerema xwe bisekinin'; 
$_['entry_text_to_be_charged']			 = 'Ji bo ku paşê were berdan:'; 

// Errors
$_['heading_errors']					 = 'Xeletî'; 

$_['entry_error_customer_required']		 = 'Pêdivî ye ku xerîdar:'; 
$_['entry_error_shipping_required']		 = 'Pêdivî ye ku Shippingandina Pêdivî ye:'; 
$_['entry_error_shipping_mismatch']		 = 'Mismatch Shipping:'; 

// Stripe Error Codes
$_['heading_stripe_error_codes']		 = 'Kodên çewtiya stripe'; 
$_['help_stripe_error_codes']			 = 'Ji van qadên vala vala bihêlin da ku ji bo wê kodê çewtiyê peyama çewtiyê ya Stripe-ê nîşan bide. HTML piştgirî ye. Gava ku hûn karanîna stripe bikar tînin peyamên çewtiyê nayê xuyang kirin. '; 

$_['entry_error_card_declined']			 = 'Card_declined'; 
$_['entry_error_expired_card']			 = 'qedirkirî_card'; 
$_['entry_error_incorrect_cvc']			 = 'çewt_cvc:'; 
$_['entry_error_incorrect_number']		 = 'çewt_number'; 
$_['entry_error_incorrect_zip']			 = 'Zipê çewt:'; 
$_['entry_error_invalid_cvc']			 = 'Invalid_cvc'; 
$_['entry_error_invalid_expiry_month']	 = 'meha qedandina nederbasdar'; 
$_['entry_error_invalid_expiry_year']	 = 'Sala qedandina nederbasdar'; 
$_['entry_error_invalid_number']		 = 'Invalid_number'; 
$_['entry_error_missing']				 = 'winda ye:'; 
$_['entry_error_processing_error']		 = 'Pêvajoyê_Error'; 

// Cards Page Text
$_['heading_cards_page_text']			 = 'Nivîsa rûpelê kartên'; 

$_['entry_cards_page_link']				 = 'Zencîreya Karta:'; 
$_['entry_cards_page_heading']			 = 'Rûpelên Qertên Serokwezîr:'; 
$_['entry_cards_page_none']				 = 'Ne peyamek kartînan:'; 
$_['entry_cards_page_default_card']		 = 'Nivîsa karta xwerû:'; 
$_['entry_cards_page_make_default']		 = 'Bişkojka Default bikin'; 
$_['entry_cards_page_delete']			 = 'Bişkojka jêbirin'; 
$_['entry_cards_page_confirm']			 = 'Pejirandin jêbirin'; 
$_['entry_cards_page_add_card']			 = 'Bişkojka Karta Nû Zêde Bikin'; 
$_['entry_cards_page_card_address']		 = 'Navnîşa karta:'; 
$_['entry_cards_page_success']			 = 'Peyama serkeftinê'; 

// Subscriptions Page Text
$_['heading_subscriptions_page_text']	 = 'Subscription Rûpel Rûpel'; 

$_['entry_subscriptions_page_heading']	 = 'Subscription Subs Sernav:'; 
$_['entry_subscriptions_page_message']	 = 'Peyama karta xwerû:'; 
$_['entry_subscriptions_page_none']		 = 'Ne peyamek abonetiyê:'; 
$_['entry_subscriptions_page_trial']	 = 'Nivîsa Dawîn a Trial:'; 
$_['entry_subscriptions_page_last']		 = 'Nivîsa Dawîn ya Dawîn:'; 
$_['entry_subscriptions_page_next']		 = 'Nivîsa Daxuyaniya Next:'; 
$_['entry_subscriptions_page_charge']	 = 'Nivîsa Zêdetir:'; 
$_['entry_subscriptions_page_cancel']	 = 'Bişkojka betal bike'; 
$_['entry_subscriptions_page_confirm']	 = 'Pejirandina betalkirinê:'; 

//------------------------------------------------------------------------------
// Order Statuses
//------------------------------------------------------------------------------
$_['tab_order_statuses']				 = 'Rewşa fermanê'; 
$_['heading_order_statuses']			 = 'Rewşa fermanê'; 
$_['help_order_statuses']				 = 'Dema ku dayîna her şertê bi hev re hevdîtin dike, statûya fermanê hilbijêrin. Nîşe: Bi rastî <strong> înkar kirin </ strong> Tê payîn ku kontrolên CVC an Zip têkevinê, hûn hewce ne ku di panelê admin a stripe de çalak bikin. tab ji bo fermanê. '; 

$_['entry_success_status_id']			 = 'Dravê serfiraz (dîl girtin)'; 
$_['entry_authorize_status_id']			 = 'Dravê serfiraz (destûrdar)'; 
$_['entry_error_status_id']				 = 'Errorewtiya temamkirina fermanê:'; 
$_['entry_street_status_id']			 = 'Têkçûna kontrolê ya kolanê'; 
$_['entry_zip_status_id']				 = 'Têkçûna zipê zip'; 
$_['entry_cvc_status_id']				 = 'CVC têkçûna kontrolê'; 
$_['entry_refund_status_id']			 = 'Dravê dravî vegerandin'; 
$_['entry_partial_status_id']			 = 'Pêvajoya vegera parçeyî'; 

$_['text_ignore']						 = '--- bêarray ---'; 

//------------------------------------------------------------------------------
// Restrictions
//------------------------------------------------------------------------------
$_['tab_restrictions']					 = 'Sînorkirinan'; 
$_['heading_restrictions']				 = 'Sînorkirinan'; 
$_['help_restrictions']					 = 'Bi tevahî karta hewceyê bicîh bikin û firotgehên bijarte, û komên geo, û komên xerîdar ên ji bo vê rêbazê dayînê hilbijêrin.'; 

$_['entry_min_total']					 = 'Kêmtirîn tevahî:'; 
$_['entry_max_total']					 = 'Bi tevahî herî zêde:'; 

$_['entry_stores']						 = 'Store (s):'; 

$_['entry_geo_zones']					 = 'Zone GEO (S):'; 
$_['text_everywhere_else']				 = '<em> li her deverê din </ em>'; 

$_['entry_customer_groups']				 = 'Koma Xerîdar (S):'; 
$_['text_guests']						 = '<em> Mêvan </ em>'; 

// Currency Settings
$_['heading_currency_settings']			 = 'Mîhengên Currency'; 
$_['help_currency_settings']			 = 'Li gorî dravê ferman, diravên ku Stripe dê lê bar bike hilbijêrin. <a target="_blank" href="https://support.stripe.com/questions/which-currencies-does-stripe-support">Bibînin ku welatê we piştgirî dide kîjan diravan</a>';
$_['entry_currencies']					 = 'Gava ku ferman di [Currency] de ye, lêçûn'; 
$_['text_currency_disabled']			 = '--- seqet kirin ---'; 

//------------------------------------------------------------------------------
// Stripe Settings
//------------------------------------------------------------------------------
$_['tab_stripe_settings']				 = 'Mîhengên Stripe'; 
$_['help_stripe_settings']				 = 'Keyeyên API dikarin di panelê admin a stripe de di bin hesabê we de werin dîtin> Mîhengên hesabê> Keysên API'; 

// API Keys
$_['heading_api_keys']					 = 'Keyên Api'; 

$_['entry_test_secret_key']				 = 'Mifteya Secret Test'; 
$_['entry_test_publishable_key']		 = 'Mifteya weşanê ya testê'; 
$_['entry_live_secret_key']				 = 'Bişkojka veşartî ya bijî'; 
$_['entry_live_publishable_key']		 = 'Keysa weşandî ya zindî'; 

// Stripe Settings
$_['heading_stripe_settings']			 = 'Mîhengên Stripe'; 

$_['entry_webhook_url']					 = 'URL Webhook:'; 

$_['entry_transaction_mode']			 = 'Moda danûstendinê:'; 
$_['text_test']							 = 'Îmtîhan'; 
$_['text_live']							 = 'Jîyan'; 

$_['entry_charge_mode']					 = 'MODEA MARESERIY:'; 
$_['text_authorize']					 = 'Ercdan'; 
$_['text_capture']						 = 'Girtin'; 
$_['text_fraud_authorize']				 = 'Heke dibe ku xapînok be, bi rengek din bigire'; 

$_['entry_transaction_description']		 = 'Danasîna danûstendinê:'; 

$_['entry_send_customer_data']			 = 'Daneyên xerîdar bişînin:'; 
$_['text_never']						 = 'Qet'; 
$_['text_customers_choice']				 = 'Customer&apos;s choice';
$_['text_always']						 = 'Herdem'; 

$_['entry_allow_stored_cards']			 = 'Destûrê bide xerîdarên ku kartên hilanîn bikar bînin:'; 

// Apple Pay Settings
$_['heading_apple_pay_settings']		 = 'Mîhengên Pay Apple'; 

$_['entry_applepay']					 = 'Vebijêrin Apple Pay:'; 
$_['entry_applepay_label']				 = 'Labelê dravê dayînê:'; 
$_['entry_applepay_billing']			 = 'Hewceyê navnîşana billing:'; 

//------------------------------------------------------------------------------
// Stripe Checkout
//------------------------------------------------------------------------------
$_['tab_stripe_checkout']				 = 'Kontrolkirina Stripe'; 
$_['heading_stripe_checkout']			 = 'Kontrolkirina Stripe'; 
$_['help_stripe_checkout']				 = 'Stripe Checkout uses Stripe&apos;s pop-up for displaying the credit card inputs, validation, and error handling. You can read more about it and view a demo at <a target="_blank" href="https://stripe.com/docs/checkout">https://stripe.com/docs/checkout</a><br />Note: Stripe Checkout does <strong>not</strong> allow customers to use the billing address entered in Expandcart.';

$_['entry_use_checkout']				 = 'Pop-Up Checkut Stripe bikar bînin:'; 
$_['text_yes_for_desktop_devices']		 = 'Erê, ji bo amûrên sermaseyê tenê'; 

$_['entry_checkout_remember_me']		 = 'Vebijêrin "Ji bîr meke" vebijarkî:'; 

$_['entry_checkout_alipay']				 = 'Alipay çalak bike:'; 
$_['entry_checkout_bitcoin']			 = 'Enable Bitcoin:'; 

$_['entry_checkout_billing']			 = 'Hewceyê navnîşana billing:'; 

$_['entry_checkout_shipping']			 = 'Hewceyê navnîşana barkirinê ye:'; 

$_['entry_checkout_image']				 = 'Logo pop-up:'; 
$_['text_browse']						 = 'Browse'; 
$_['text_clear']						 = 'Zelal'; 
$_['text_image_manager']				 = 'Rêvebirê Wêne'; 

$_['entry_checkout_title']		 		 = 'Sernavê Pop-Up: '; 

$_['entry_checkout_description']		 = 'Danasîna pop-up: '; 

$_['entry_checkout_button']				 = 'Nivîsara bişkoja pop-up: '; 

$_['entry_quick_checkout']				 = 'Pêşkêşkirina Bilez'; 

//------------------------------------------------------------------------------
// Subscription Products
//------------------------------------------------------------------------------
$_['tab_subscription_products']			 = 'Berhemên Berhemên'; 
$_['help_subscription_products']		 = '&ga; Hilberên aboneyê dê gava ku ew bikirin, dê xerîdar ji bo plana stripe ya têkildar re bibin endam. Hûn dikarin bi pêvekek plansaziyê ya ku di zeviya "Cihê" ya ji bo hilberê de derbas bikin, bi plansaziyek re têkildar bikin. <br /> & Bull; Heke abonetiyê tavilê nehatiye dayîn BR /> & Bull; Di her pêşerojê de abonetiyê di pêşerojê de abonetiyê dide, fermanek têkildar dê di berfirehkirinê de were afirandin. <br /> & Bull; Heke di hesabê stripe-yê de weppek hatî saz kirin, hûn dikarin bi karanîna heman kodê kodê û dravê dakêşandinê ve bikirin. Dema ku xerîdar hilberek hilberê bikire û wê kodê kuponê bikar tîne, ew ê kodê derbas bike da ku hûn bi rêkûpêk heqê aboneyê rast bikin. '; 

$_['heading_subscription_products']		 = 'Mîhengên Hilbera Aboneyê'; 

$_['entry_subscriptions']				 = 'Hilberên Berhemên Enable'; 
$_['entry_prevent_guests']				 = 'Pêşkêşkirina mêvanan ji kirînê:'; 
$_['entry_include_shipping']			 = 'Insandina barkirinê:'; 
$_['entry_allow_customers_to_cancel']	 = 'Destûrê bide xerîdaran ji bo betalkirina aboneyan:'; 

// Current Subscription Products
$_['heading_current_subscriptions']		 = 'Berhemên Aboneyên Nû'; 
$_['entry_current_subscriptions']		 = 'Hilberên abonetiya heyî:'; 

$_['text_thead_Expandcart']				 = 'ExpandCart'; 
$_['text_thead_stripe']					 = 'Tîrêj'; 
$_['text_product_name']					 = 'Navê hilberê'; 
$_['text_product_price']				 = 'Buhayê hilberê'; 
$_['text_location_plan_id']				 = 'Cih / PLAY ID'; 
$_['text_plan_name']					 = 'Navê Plan'; 
$_['text_plan_interval']				 = 'Interval plan'; 
$_['text_plan_charge']					 = 'Dozê Plan'; 
$_['text_no_subscription_products']		 = 'Berhemên Aboneyê tune'; 
$_['text_create_one_by_entering']		 = 'Bi navnîşana nasnameya plana Stripe di qada "Cih" de ji bo hilberê yek çêbikin';

// Map Options to Subscriptions
$_['heading_map_options']				 = 'Vebijarkên Nexşeyê li Abonan'; 
$_['help_map_options']					 = 'Heke xerîdar xwedan hilberek bi navê vebijarka têkildar û nirxa vebijarkê ya di kortika xwe de hebe, ew ê ji nasnameya plansaziya têkildar re bibin endam. Ev ê ji bo wê hilberê li qada cîhê li qada Cihê li ser cîhê belav bibe. '; 

$_['column_action']						 = 'Çalakî'; 
$_['column_option_name']				 = 'Navê vebijarkî'; 
$_['column_option_value']				 = 'Nirxa vebijarkî'; 
$_['column_plan_id']					 = 'ID plan'; 

$_['button_add_mapping']				 = 'Nexşe lê zêde bike'; 

// Map Recurring Profiles to Subscriptions
$_['heading_map_recurring_profiles']	 = 'Nexşeyên dubare ji bo aboneyan'; 
$_['help_map_recurring_profiles']		 = 'Heke xerîdar bi navê profîla dubare ya têkildar di korta xwe de bi navê wan re heye, ew ê ji nasnameya plansaziya têkildar re bibin endam. Ev ê ji bo wê hilberê li qada cîhê li qada cîhê li qada cîhê zêde bike. Frekansa aboneyê û dravê dravê ji hêla plana Stripe ve hatî destnîşankirin, ne mîhengên profîla dubare, ji ber vê yekê piştrast bikin ku ew bi tevahî hevber dikin. '; 

$_['column_profile_name']				 = 'Navê profîla dubare'; 

//------------------------------------------------------------------------------
// Create a Charge
//------------------------------------------------------------------------------
$_['tab_create_a_charge']				 = 'Dozek çêbikin'; 

$_['help_charge_info']					 = 'Agahdariya heqê li jêr binivîse, wê hingê hilbijêrin ka girêdanek dravdanê çêbikin, karta Xerîdar a Mişterî derxînin, an kartek bi destan têkevin.'; 
$_['heading_charge_info']				 = 'Agahdariya Daxuyaniyê'; 

$_['entry_order_id']					 = 'Nasnameya fermanê:'; 
$_['entry_order_status']				 = 'Guhertina rewşa fermanê:'; 
$_['entry_description']					 = 'Danasîn:'; 
$_['entry_statement_descriptor']		 = 'Daxuyaniya daxuyaniyê:'; 
$_['entry_amount']						 = 'Biha'; 

// Create Payment Link
$_['heading_create_payment_link']		 = 'Zencîreya dravdanê çêbikin'; 

$_['help_create_payment_link']			 = ''; 
$_['button_create_payment_link']		 = 'Zencîreya dravdanê çêbikin'; 

// Use a Stored Card
$_['heading_use_a_stored_card']			 = 'Karta hilanînê bikar bînin'; 

$_['entry_customer']					 = 'Miştirî'; 
$_['placeholder_customer']				 = 'Dest pê bikin ku navê mişterî an navnîşana e-nameyê binivîsin'; 
$_['text_customers_stored_cards_will']	 = '(Karta xwerû ya Xerîdar dê li vir xuya bibe)'; 
$_['button_create_charge']				 = 'Berhem biafirîne'; 

// Use a New Card
$_['heading_use_a_new_card']			 = 'Karta nû bikar bînin'; 

//------------------------------------------------------------------------------
// Standard Text
//------------------------------------------------------------------------------
$_['copyright']							 = ''; 

$_['standard_autosaving_enabled']		 = 'Auto-Saving Enabled'; 
$_['standard_confirm']					 = 'Ev operasyon nikare were paşguh kirin. Berdewamkirin?'; 
$_['standard_error']					= '<strong>Error:</strong> You do not have permission to modify ' . $_['heading_title'] . '!';
$_['standard_max_input_vars']			= '<strong>Warning:</strong> Please contact customer service 2.';
$_['standard_please_wait']				 = 'Ji kerema xwe li bendê bin ...'; 
$_['standard_saved']					 = 'Saveduştin!'; 
$_['standard_saving']					 = 'Saving ...'; 
$_['standard_select']					 = '--- Hilbijêrin ---'; 
$_['standard_success']					 = 'Serketinî!'; 
$_['standard_testing_mode']				 = 'Têketina we pir mezin e ku veke! Ewilî zelal bikin, dûv re ceribandina xwe dîsa bi rêve bibin. '; 
$_['standard_vqmod']					 = '<strong> Hişyarî: </ strong> Ji kerema xwe bi karûbarê xerîdar re têkilî daynin.'; 

$_['standard_module']					 = 'Apps'; 
$_['standard_shipping']					 = 'Barkirinê'; 
$_['standard_payment']					 = 'Payments'; 
$_['standard_total']					 = 'Order Total'; 
$_['standard_feed']						 = 'Feeds'; 

// Errors

$_['error_settings']                     = "Hişyarî: Ji kerema xwe Key Bişkojka Testê, Bişkoja Secret, Bişkoja Bijî Zindî û Zeviyên Key ên Secret ên Zindî bikin!"; 
?>