<?php

use ExpandCart\Foundation\Support\Hubspot;
use ExpandCart\Foundation\Support\DataWarehouse;
class ControllerCommonInstallation extends Controller
{
    private $errors = array();

    public function index()
    {
        $this->load->model('setting/setting');
        $signup = $this->model_setting_setting->getGuideValue("SIGNUP");

        if ($signup['QUESTIONER']== 1) {
            if (PRODUCTID == 3)
                $this->redirect($this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'));
            else
                $this->redirect($this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'));
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['config_admin_language'])) {
            $postLanguage = $this->request->post['config_admin_language'];
            if ($postLanguage != null &&  $postLanguage != "" && $postLanguage != $this->config->get('config_admin_language')) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->insertUpdateSetting('config', ['config_admin_language' => $postLanguage]);
                $this->config->set('config_admin_language', $postLanguage);
                        /***************** Start ExpandCartTracking #347688  ****************/
                        $this->load->model('setting/mixpanel');
                        $this->load->model('setting/amplitude');
                        $this->model_setting_mixpanel->trackEvent('Onboarding2 - Language Switcher', ['Language' => strtoupper($postLanguage)]);
                        $this->model_setting_amplitude->trackEvent('Onboarding2 - Language Switcher', ['Language' => strtoupper($postLanguage)]);
                        $this->model_setting_mixpanel->incrementProperty('Onboarding2 - Language Switcher');
                        /***************** End ExpandCartTracking #347688  ****************/
                return $this->redirect($this->url->link('common/installation', '', 'SSL'));
            }
        }

        $this->data['WELCOME_PAGE'] = $this->model_setting_setting->getGuideValue("SIGNUP")['WELCOME_PAGE'];

        if ($this->data['WELCOME_PAGE'] != 1){
            $this->model_setting_setting->editGuideValue('SIGNUP', 'WELCOME_PAGE', '1');
        }

        $this->data['logoPath'] = PARTNER_CODE != '' ? 'partners/' . PARTNER_CODE . '/logo-login.png' : 'LogoEC.png';

        $this->language->load('common/installation');

        $this->document->setTitle($this->language->get('heading_title'));


        // Q1 Are you Selling Currently ?
        $this->data['is_selling'] = [
            'Yes' => 'Yes, I sell products',
            'No'  => 'No, I don’t sell'
        ];

        // Q2 How do you currently sell your product?
        $this->data['selling_channel'] = [
            'my_own_website'        => 'My own website',
            'facebook_and_instagram'=> 'Facebook and Instagram',
            'amazon_ebay_jumia'     => 'Amazon, Ebay and Jumia',
            'physical_store'        => 'Physical Store',
            'all_the_above'         => 'All the above',
            'just_explore'          => 'Just Explore'
        ];

        // Q3 How do you source your products?
        $this->data['product_source'] = [
            'own_products'          => 'Own Products',
            'retail'                => 'Retail',
            'dropshipping'          => 'Dropshipping',
            'multi_merchant'        => 'Multi Merchant',
            'do_not_know'           => 'Do Not Know'
        ];

        // Q4 How Many Orders do you receive monthly ?
        $this->data['sales_range'] = [
            'from_0_100'            => '1 to 100',
            'from_101_500'          => '101 to 500',
            'from_501_1000'         => '501 to 1000',
            'more_than_1000'        => '501 to 1000'
        ];

        // Q5 What is your business industry?
        $this->data['product_type'] = [
            'food_drink'            =>'food & drink',
            'clothes_shoes'         =>'clothes & shoes',
            'health_beauty'         =>'health & beauty',
            'tech_products'         =>'Tech Products',
            'kids_and_baby'         =>'Kids & baby',
            'home_living'           =>'Home & living',
            'classes_events'        =>'Classes and events',
            'arts_crafts'           =>'Arts & Crafts',
            'jewelry_accessories'   =>'Jewelry & accessories',
            'fitness_wellness'      =>'Fitness & wellness',
            'sport'                 =>'Sport',
            'pet_supplies'          =>'Pet supplies',
            'others'                =>'others'
        ];

        // Q6 Is your business registered?

        $this->data['registered_business']=[
            [
                'value' => 'Yes',
                'label' =>  $this->language->get('text_registered'),
                'image' =>  'view/assets/images/installation/registered.svg',
            ],
            [
                'value' => 'No',
                'label' =>  $this->language->get('text_not_registered'),
                'image' =>  'view/assets/images/installation/not_registered.svg',
            ],
         
        ];

        // Q7 Store Currency?
        // Q8 Store languages
        $this->data['languages'] = [
            'en'=>'English',
            'ar'=>'عربي',
            'it'=>'Italiano',
            'de'=>'Deutsche',
            'tr'=>'Türk',
            'fr'=>'Français',
            'hi'=>'भारतीय'
        ];

        $this->data['config_admin_language']=[
            'ar'=>'Arabic',
            'en'=>"English"
        ];
				
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->validateForm()) {

                $setting = $this->request->post['setting'];
                $isSelling          = $setting['is_selling']            ?? "Yes";
                $sellingChannels    = $setting['selling_channels']      ?? [];
                $productSources     = $setting['product_sources']       ?? [];
                $productsTypes      = $setting['product_types']         ?? ["others"];
                $salesRange         = $setting['sales_range']           ?? "";
                $registeredBusiness = $setting['registered_business']   ?? 'Yes';
                $previousWebsite    = $setting['previous_website']      ?? "";

                if($previousWebsite == "on") {
                    $previousWebsite= "Yes";
                }else{
                    $previousWebsite="No";
                }
                // 4- set the store language
                $language = $this->setStoreLanguages($setting['languages']);
               // $currency = $this->updateStoreCurrenciesStatus($setting['currencies']);
                
				$whmcs= new whmcs();
				
                if (!isset($setting['telephone']) || empty($setting['telephone'])) {
                    // update the client phone from whmcs api
                    $userId= WHMCS_USER_ID;
                    $phoneNumber = $whmcs->getClientPhone($userId);
                    if ($phoneNumber != null && $phoneNumber != ""){
                        $setting['telephone'] = $phoneNumber;
                    }
                }
				
				
				// paypal payment before onboarding -- merchant country needed to check whether to enable it or not 
				try {
					
					$sessionWhitelist = $this->session->data['paypal_whitelist'] ?? FALSE;
					$emailWhitelist	  = (strpos(BILLING_DETAILS_EMAIL, "testpaypal") !== false);
								
										
					//----  paypal payment before onboarding 
					//----  for test | whitelist ----
					if ($sessionWhitelist || $emailWhitelist)
					{
						
						//------ for test |  simulate merchant countries using query parameters 
						if( 
							isset($this->session->data['paypal_country_whitelist'])
							&& !empty($this->session->data['paypal_country_whitelist'])
							)
						{
							$merchantCountry = $this->session->data['paypal_country_whitelist'];
						} 
						else 
						{
							
							$clientDetails 	 = $whmcs->getClientDetails(WHMCS_USER_ID);
							$merchantCountry = $clientDetails['countrycode'] ?? "";
						}
						
						$this->paypalPaymentEnable($merchantCountry);
					}
				
				} catch (Exception $e){}
				
				
                // store client data...
                $this->model_setting_setting->insertUpdateSetting('config', [
                    'is_selling'            => $isSelling,
                    'selling_channel'       => $sellingChannels,
                    'product_source'        => $productSources,
                    'products_types'        => $productsTypes,
                    'sales_range'           => $salesRange,
                    'registered_business'   => $registeredBusiness,
                    'config_telephone'      => (string)$setting['telephone'],
                    'config_email'          => (string)$setting['email'],
                    'config_address'        => [$this->config->get('config_admin_language') => $setting['address']],
                ]);

                /*
                $this->model_setting_setting->insertUpdateSetting('config', ['selling_channel' => $sellingChannels]);
                $this->model_setting_setting->insertUpdateSetting('config', ['product_source' => $productSources]);
                $this->model_setting_setting->insertUpdateSetting('config', ['products_types' => $productsTypes]);
                $this->model_setting_setting->insertUpdateSetting('config', ['registered_business' => $registeredBusiness]);
                $this->model_setting_setting->insertUpdateSetting('config', ['config_telephone' => $setting['telephone']]);
                $this->model_setting_setting->insertUpdateSetting('config', ['config_email' => $setting['email']]);
                $this->model_setting_setting->insertUpdateSetting('config', ['config_address' => [$this->config->get('config_admin_language') => $setting['address']]]);
                */
                /***************** Start ExpandCartTracking #347689  ****************/

                // send mixpanel complete onboard events and update user properties
                $this->load->model('setting/mixpanel');
                $this->model_setting_mixpanel->updateUser([
                    '$is selling'            => $isSelling,
                    '$selling channel'       => json_encode($sellingChannels),
                    '$product source'        => json_encode($productSources),
                    '$products types'        => json_encode($productsTypes),
                    '$sales range'           => $salesRange,
                    '$registered business'   => $registeredBusiness,
                    '$previous website'      => $previousWebsite,
                    '$store code'            => STORECODE,
                    '$subscription plan'     => PRODUCTID,
                ]);
                $this->model_setting_mixpanel->trackEvent('Complete Onboard');

                // send amplitude  complete onboard events and update user properties
                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->updateUser([
                    'is selling'            => $isSelling,
                    'selling channel'       => json_encode($sellingChannels),
                    'product source'        => json_encode($productSources),
                    'products types'        => json_encode($productsTypes),
                    'sales range'           => $salesRange,
                    'registered business'   => $registeredBusiness,
                    'previous website'      => $previousWebsite,
                    'store code'            => STORECODE,
                    'subscription plan'     => PRODUCTID,
                ]);
                $this->model_setting_amplitude->trackEvent('Complete Onboard');

                /***************** End ExpandCartTracking #347689  ****************/

                // todo check if the data is sent correctly to

                /*//################### AutoPilot Start #####################################
                try {
                    $fields = array();

                    $fields["string--Is--Selling"]          = $isSelling;
                    $fields["string--Selling--Channel"]     = json_encode($sellingChannels);
                    $fields["string--Product--Source"]      = json_encode($productSources);
                    $fields["string--Product--Types"]       = json_encode($productsTypes);
                    $fields["string--Sales--Range"]         = $salesRange;
                    $fields["string--Registered--Company"]  = $registeredBusiness;
                    $fields["string--Previous--Website"]    = $previousWebsite;

                    //new custom fields
                    //$fields["string--Store--Name"] = $store_name;

                    autopilot_UpdateContactCustomFields(BILLING_DETAILS_EMAIL, $fields);
                }
                catch (Exception $e) {}
                //################### AutoPilot End  #####################################*/

                //################### Freshsales Start  ####################################
                try {

                    FreshsalesAnalytics::init(array('domain' => 'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io', 'app_token' => FRESHSALES_TOKEN));

                    $leadData = array(
                        'identifier'        => BILLING_DETAILS_EMAIL,
                        'Is Selling'        => $isSelling,
                        'Selling Channel'   => json_encode($sellingChannels),
                        'Product Source'    => json_encode($productSources),
                        'Product Types'     => json_encode($productsTypes),
                        'Sales Range'       => $salesRange,
                        'Registered Company'=> $registeredBusiness,
                        'Previous Website'  => $previousWebsite,
                        // new custom fields
//                        "custom_field" => array(
////                            'cf_store_name' => $store_name,
////                        'cf_registered_business'=>$registered_business,
//                            'cf_products_types' => $products_types,
//                        )
                    );
                    FreshsalesAnalytics::identify(
                        $leadData
                    );
                }
                catch (Exception $e) {}
                //################### Freshsales END  #####################################



                //################### Hubspot Start #####################################
                $isSelling_MAP = [
                    'Yes, I sell products'  => 'ec_ob_is_yes',
                    'No, I don’t sell'      => 'ec_ob_is_no',
                ];



                $productSource_MAP = [
                    'Own Products' => 'ec_ob_ps_own_products',
                    'Retail' => 'ec_ob_ps_retail',
                    'Dropshipping' =>'ec_ob_ps_dropshipping',
                    'Multi Merchant'=>'ec_ob_ps_multi_merchant',
                    'Do Not Know'=>'ec_ob_ps_do_not_know'
                ];

                /*
                $selling_channel_MAP = [
                    'Website'=>'ec_ob_sc_website',
                    'Social Media' =>'ec_ob_sc_social_media',
                    'Marketplaces'=>'ec_ob_sc_marketplaces',
                    'Retail Store'=>'ec_ob_sc_retail_store',
                    'All Channels'=>'ec_ob_sc_all_channels',
                    'Not Selling'=>'ec_ob_sc_not_selling',
                    'Building for Client'=>'ec_ob_sc_building_for_client',
                    'Research Purposes'=>'ec_ob_sc_research_purposes',
                ];
                */

                $selling_channel_MAP = [
                    'My own website'         => 'ec_ob_sc_my_own_website',
                    'Facebook and Instagram' => 'ec_ob_sc_facebook_and_instagram',
                    'Amazon, Ebay and Jumia' => 'ec_ob_sc_amazon_ebay_and_jumia',
                    'Retail Store'           => 'ec_ob_sc_physical_store',
                    'All the above'          => 'ec_ob_sc_all_the_above',
                    'Just Explore'           => 'ec_ob_sc_just_explore',
                ];


                $sales = [
                    'Yes'       => 'ec_ob_rc_yes',
                    'No'        =>'ec_ob_rc_no',
                    'Not Yet'   =>'ec_ob_rc_not_yet',
                ];

                $registeredCompany_MAP = [
                    'Yes' => 'ec_ob_rc_yes',
                    'No'  =>'ec_ob_rc_no',
                ];

                $salesRange_MAP = [
                    'from_0_100'     => 'ec_ob_sr_from_0_100',
                    'from_101_500'   => 'ec_ob_sr_from_101_500',
                    'from_501_1000'  => 'ec_ob_sr_from_501_1000',
                    'more_than_1000' => 'ec_ob_sr_more_than_1000'
                ];

                $productTypes_MAP = [
                    'food_drink' => 'ec_ob_pt_food_drink',
                    'clothes_shoes' => 'ec_ob_pt_clothes_shoes',
                    'health_beauty' => 'ec_ob_pt_health_beauty',
                    'tech_products' => 'ec_ob_pt_tech_products',
                    'kids_and_baby' => 'ec_ob_pt_kids_and_baby',
                    'home_living' => 'ec_ob_pt_home_living',
                    'classes_events' => 'ec_ob_pt_classes_events',
                    'arts_crafts' => 'ec_ob_pt_arts_crafts',
                    'jewelry_accessories' => 'ec_ob_pt_jewelry_accessories',
                    'fitness_wellness' => 'ec_ob_pt_fitness_wellness',
                    'sport' => 'ec_ob_pt_sport',
                    'pet_supplies' => 'ec_ob_pt_pet_supplies',
                    'others' => 'ec_ob_pt_others'
                ];

                if (empty($sellingChannels)) {
                    $sellingChannels = ['ec_ob_sc_website'];
                }

                if (empty($productSources)) {
                    $productSources = ['ec_ob_ps_own_products'];
                }

                if (empty($productsTypes)) {
                    $productsTypes = ['ec_ob_pt_food_drink'];
                }

                $productTypeValues = [];
                foreach ($productsTypes as $productsType) {
                    if (isset($this->data['product_type'][$productsType])) {
                        $productTypeValues[] = $this->data['product_type'][$productsType];
                    }
                }

                $sales_range_map = [
                    'from_0_100'            => 'From 1 to 100',
                    'from_101_500'          => 'From 101 to 500',
                    'from_501_1000'         => 'From 501 to 1000',
                    'more_than_1000'        => 'More than 1000'
                ];

                Hubspot::tracking('pe25199511_os_questioneer_updated', [
                    "ec_os_qui_is_selling"          => $isSelling,
                    "ec_os_qui_selling_channel_multiple"     => implode(';', $sellingChannels),
                    "ec_os_qui_product_source_multiple"      => implode(';', $productSources),
                    "ec_os_qui_products_types_multiple"      => implode(';', $productTypeValues),
                    "ec_os_qui_sales_range"         => $sales_range_map[$salesRange] ?? 'From 0 to 100',
                    "ec_os_qui_registered_company"  => $registeredBusiness,
                ]);

                $namedSellingChannels = [];
                foreach ($sellingChannels as $sellingChannel) {
                    if (isset($selling_channel_MAP[$sellingChannel])) {
                        $namedSellingChannels[] = $selling_channel_MAP[$sellingChannel];
                    }
                }

                $namedProductSources = [];
                foreach ($productSources as $productSource) {
                    if (isset($productSource_MAP[$productSource])) {
                        $namedProductSources[] = $productSource_MAP[$productSource];
                    }
                }

                $namedProductsTypes = [];
                foreach ($productsTypes as $productsType) {
                    if (isset($productTypes_MAP[$productsType])) {
                        $namedProductsTypes[] = $productTypes_MAP[$productsType];
                    }
                }

                Hubspot::updateContact([
                    'ec_ob_is_selling'             => $isSelling_MAP[$isSelling] ?? 'ec_ob_is_yes',
                    'ec_ob_selling_channel_multiple'        => implode(';', $namedSellingChannels),
                    'ec_ob_product_source_multiple'         => implode(';', $namedProductSources),
                    'ec_ob_products_types_multiple'      => implode(';', $namedProductsTypes),
                    'ec_ob_sales_range'         => $salesRange_MAP[$salesRange] ?? 'ec_ob_sr_from_0_100',
                    'ec_ob_registered_company'  => $registeredCompany_MAP[$registeredBusiness] ?? 'ec_ob_rc_yes',
                    'hs_language' => $language,
                    'primary_email'             => BILLING_DETAILS_EMAIL,
                ]);
                //################### Hubspot End #####################################
				
				
				
                //******************** DataWarehouse tracking  ************************************/
				
				DataWarehouse::tracking('onboarding_finish');
				
			   //******************** #DataWarehouse tracking  ************************************/
               

                $this->applyDefaultTemplate();

                $this->model_setting_setting->editGuideValue("SIGNUP", "QUESTIONER", "1");
                $result_json['success'] = "1";
                $userActivationNewUserParameter =
                    $this->userActivation->getUserActivationParameter($this->userActivation::NEW_USER);
                $result_json['redirect_to'] = (string)$this->url->link('common/dashboard', $userActivationNewUserParameter, 'SSL');
                $this->model_setting_setting->insertUpdateSetting('config',
                    [
                        'store_owner_ip' => $this->request->server['REMOTE_ADDR']

                    ]);
                if(isset($this->session->data['checkout_pid'])){
                    $result_json['redirect_to'] = (string)$this->url->link('account/checkout', '', 'SSL');
                }
                $response=$result_json;

            }else{
                $response= [
                    'hasErrors' => true,
                    'errors' => $this->errors
                ];
            }
            $this->response->setOutput(json_encode($response));

        }
        else{

            /***************** Start ExpandCartTracking #347687  ****************/

            // send mixpanel events and create user (sign up)
            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->createUser();

            // send amplitude events and create user (sign up)
            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->createUser();

            /***************** End ExpandCartTracking #347687  ****************/

        }

        $this->data['heading_title'] = $this->language->get('heading_title');

        //################### Freshsales End #####################################

        //################### Intercom Start #####################################
        try {
            $url = 'https://api.intercom.io/events';
            $authid = INTERCOM_AUTH_ID;

            $cURL = curl_init();
            curl_setopt($cURL, CURLOPT_URL, $url);
            curl_setopt($cURL, CURLOPT_USERPWD, $authid);
            curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($cURL, CURLOPT_POST, true);
            curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json'
            ));
            $intercomData['event_name'] = 'application-installation';
            $intercomData['created_at'] = time();
            $intercomData['user_id'] = STORECODE;
            curl_setopt($cURL, CURLOPT_POSTFIELDS, json_encode($intercomData));
            $result = curl_exec($cURL);
            curl_close($cURL);
        }
        catch (Exception $e) {  }
        //################### Intercom End #######################################


        $this->data['direction'] = $this->language->get('direction');
        $this->data['lang'] = $this->language->get('code');

        if (isset($this->errors['warning'])) {
            $this->data['error_warning'] = $this->errors['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->data['change_language'] = [
            'name' =>   $this->config->get('config_admin_language') == 'ar' ? 'English' : 'العربية',
            'image' => $this->config->get('config_admin_language') == 'ar' ? 'gb.png'   : 'eg.png',
        ];

        $this->template = 'common/installation.expand';
        $this->base = 'common/base';

        if ($this->request->server['REQUEST_METHOD'] != 'POST'  ) {
            $this->response->setOutput($this->render_ecwig());
        }

    }

    private function setStoreLanguages(array $languages = null)
    {
        if (empty($languages)) {
            return;
        }
        $arabicArr = ['ع', 'ar', 'ar.UTF-8,ar,ar,arabic', 'sa.png', 'arabic', 'arabic'];
        $englishArr = ['en', 'en', 'en_US.UTF-8,en_US,en-gb,english', 'gb.png', 'english', 'english'];
        $italianArr = ['it', 'it', 'it', 'it.png', 'italian', 'italian'];
        $germanArr = ['de', 'de', 'de', 'de.png', 'german', 'german'];
        $turkishArr = ['tr', 'tr', 'tr', 'tr.png', 'turkish', 'turkish'];
        $frenchArr = ['fr', 'fr', 'fr', 'fr.png', 'french', 'french'];
        $indianArr = ['भा', 'hi', 'hi', 'in.png', 'hi', 'hi'];

        // language needed data
        $availableLangs = [
            $arabicArr[1] => array(
                "name"=>$arabicArr[0],
                "code"=>$arabicArr[1],
                "locale"=>$arabicArr[2],
                "image"=>$arabicArr[3],
                "directory"=>$arabicArr[4],
                "filename"=>$arabicArr[5],
                "status"=>1
            ),
            $englishArr[1] => array(
                "name"=>$englishArr[0],
                "code"=>$englishArr[1],
                "locale"=>$englishArr[2],
                "image"=>$englishArr[3],
                "directory"=>$englishArr[4],
                "filename"=>$englishArr[5],
                "status"=>1
            ),
            $italianArr[1] => array(
                "name"=>$italianArr[0],
                "code"=>$italianArr[1],
                "locale"=>$italianArr[2],
                "image"=>$italianArr[3],
                "directory"=>$italianArr[4],
                "filename"=>$italianArr[5],
                "status"=>1
            ),
            $germanArr[1] =>array(
                "name"=>$germanArr[0],
                "code"=>$germanArr[1],
                "locale"=>$germanArr[2],
                "image"=>$germanArr[3],
                "directory"=>$germanArr[4],
                "filename"=>$germanArr[5],
                "status"=>1
            ),
            $turkishArr[1] => array(
                "name"=>$turkishArr[0],
                "code"=>$turkishArr[1],
                "locale"=>$turkishArr[2],
                "image"=>$turkishArr[3],
                "directory"=>$turkishArr[4],
                "filename"=>$turkishArr[5],
                "status"=>1
            ),
            $frenchArr[1] =>array(
                "name"=>$frenchArr[0],
                "code"=>$frenchArr[1],
                "locale"=>$frenchArr[2],
                "image"=>$frenchArr[3],
                "directory"=>$frenchArr[4],
                "filename"=>$frenchArr[5],
                "status"=>1
            ),
            $indianArr[1] => array(
                "name"=>$indianArr[0],
                "code"=>$indianArr[1],
                "locale"=>$indianArr[2],
                "image"=>$indianArr[3],
                "directory"=>$indianArr[4],
                "filename"=>$indianArr[5],
                "status"=>1
            )
        ];
        $this->load->model('localisation/language');

        // example:
        // $languages= ['ar','en'];

        $langs= $this->model_localisation_language->getLanguages();
        $count = count($langs);

        foreach ($langs as $key => $lang) {
            if (! in_array($key,$languages)) {
                $this->model_localisation_language->disableLanguage('front', $lang['language_id']);
            }
        }

        $i = (int) $count + 1;
        foreach ($languages as $language){
            if (! isset($langs[$language])) {
                $availableLangs[$language]['sort_order']=$i;
                $this->model_localisation_language->addLanguage($availableLangs[$language]);
                $i++;
            }
        }
        if ( !in_array($this->config->get('config_admin_language'),$languages) ) {
            $data['config_admin_language']=$languages[0];
            $this->language($data);
            $this->config->set('config_admin_language', $data['config_admin_language']);
        }
        return $this->config->get('config_admin_language'); // default language
    }

    private function updateStoreCurrenciesStatus(array $currencies = null)
    {
        if (empty($currencies)) return;
        $this->load->model('localisation/currency');
        $this->model_localisation_currency->setCurrencyStatus("*", 0);
        $this->model_localisation_currency->setCurrencyStatus($currencies, 1);
        $this->currency->set($this->model_localisation_currency->getCurrency(reset($currencies))['code'] ?: $this->currency->getCode());
        return $this->currency->getCode();
    }

    private function validateForm()
    {

//        if (empty($this->request->post['store_name'])){
//            $this->errors['store_name'] = $this->language->get('error_entry_store_name');
//        }

//        if (empty($this->request->post['registered_business']) || !is_array($this->request->post['registered_business'])){
//            $this->errors['registered_business'] = $this->language->get('error_entry_registered_business');
//        }

//        if (empty($this->request->post['products_types']) ){
//            $this->errors['products_types'] = $this->language->get('error_entry_products_types');
//        }

//        if (empty($this->request->post['store_languages']) ){
//            $this->errors['store_languages'] = $this->language->get('error_entry_store_languages');
//        }
        $setting = $this->request->post['setting'] ?? [];

        if (empty($setting['is_selling']??"")){
            $this->errors['is_selling'] = $this->language->get('error_entry_required');
        }
        if (empty($setting['selling_channels']??"")){
            $this->errors['selling_channels'] = $this->language->get('error_entry_required');
        }
        if (empty($setting['product_sources']??"")){
            $this->errors['product_sources'] = $this->language->get('error_entry_required');
        }
        if (empty($setting['product_types']??"")){
            $this->errors['product_types'] = $this->language->get('error_entry_required');
        }
        if (empty($setting['sales_range']??"")){
            $this->errors['sales_range'] = $this->language->get('error_entry_required');
        }
        if (empty($setting['registered_business']??"")){
            $this->errors['registered_business'] = $this->language->get('error_entry_required');
        }

        if ( $this->errors && !isset($this->errors['error']) )
        {
            $this->errors['warning'] = $this->language->get('error_warning');
        }

        return $this->errors ? false : true;
    }

    public function validate()
    {
        $postData = $this->request->post;

//        if (isset($postData['store_name'])){
//            if (empty($postData['store_name']) ){
//
//                $this->errors['store_name'] = $this->language->get('error_entry_store_name');
//            }
//        }

//        if (isset($postData['registered_business'])) {
//
//            if (empty($postData['registered_business']) || !is_array($postData['registered_business'])) {
//                $this->errors['store_name'] = $this->language->get('error_entry_registered_business');
//            }
//        }

//        if ($postData['products_types']) {
//
//            if (empty($postData['products_types'])) {
//                $this->errors['products_types'] = $this->language->get('error_entry_products_types');
//            }
//        }

        if ($postData['store_languages']) {

            if (empty($postData['store_languages']) || !is_array($postData['store_languages'])) {
                $this->errors['store_languages'] = $this->language->get('error_entry_store_languages');
            }
        }
        if ( $this->errors && !isset($this->errors['error']) )
        {
            $this->errors['warning'] = $this->language->get('error_warning');
        }


        if (count($this->errors) > 0 || empty($postData) ) {
            $response= [
                'hasErrors' => true,
                'errors' => $this->errors
            ];
        }else{
            $response =  ['hasErrors' => false];
        }

        $this->response->setOutput(json_encode($response));
    }

    public function language($data = null){
        $response=[];
        if ($this->request->server['REQUEST_METHOD'] == 'POST' ) {
            if ($this->request->post['config_admin_language'] != null &&  $this->request->post['config_admin_language'] != ""){
                $data = $this->request->post;
            }
        }

        if ($data != null){
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting('config', $data);

            /***************** Start ExpandCartTracking #347688  ****************/

            // send mixpanel update user language
            $this->load->model('setting/mixpanel');
            $this->model_setting_mixpanel->updateUser([
                '$preferred language'       => $data['config_admin_language'],
            ]);

            // send amplitude update user language
            $this->load->model('setting/amplitude');
            $this->model_setting_amplitude->updateUser([
                'preferred language'       => $data['config_admin_language'],
            ]);

            Hubspot::updateContact([
                'hs_language' => $data['config_admin_language'],
                'primary_email'             => BILLING_DETAILS_EMAIL,
            ]);

            /***************** End ExpandCartTracking #347688  ****************/


            $result_json['success'] = "1";
            $response=$result_json;
        }
        $this->response->setOutput(json_encode($response));
    }

    private function applyDefaultTemplate(){
        $this->initializer([
            'templates/template',
            'archive' => 'templates/archive',
        ]);
        $codeName = $this->config->get('config_template');
        $template = $this->template->getTemplateByConfigName($codeName);

        if($template['external_template_id']){
            if (file_exists(DIR_CUSTOM_TEMPLATE) == false || is_writable(DIR_CUSTOM_TEMPLATE) == false) {
                mkdir(DIR_CUSTOM_TEMPLATE, 0777);
                chmod(DIR_CUSTOM_TEMPLATE, 0777);
            }
            $theme = EXTERNAL_THEMES_PATH . $codeName . '.zip';
            $this->archive->open($theme);
            $this->archive->extract(DIR_CUSTOM_TEMPLATE)->close();
            rename(DIR_CUSTOM_TEMPLATE . $codeName . '/schema.json', DIR_CUSTOM_TEMPLATE . $codeName . '/' . $codeName .'.json');

            $this->template->applyTemplate($template);
        }
        else {
             $this->load->model('setting/setting');
             $this->model_setting_setting->changeTemplate($codeName);
        }
    }

	private function paypalPaymentEnable($merchantCountry=""){
		
		if(!empty($merchantCountry)){
				
			$this->load->model("payment/paypal");
				
			$isPaypalZero = $this->model_payment_paypal->checkIfPaypalZeroCountries($merchantCountry);
			
			//merchants in zero country list excluded from this flow
			if(!$isPaypalZero){
				$this->model_payment_paypal->enableBeforeOnboardingFlow();
			}
		}
	}
}
