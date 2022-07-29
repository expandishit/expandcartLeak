<?php

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class ControllerAccountChargeOld extends Controller
{
    protected $withStoreCodeUrl = false;

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);
        if (str_contains($_SERVER['SERVER_NAME'],strtolower(STORECODE) )) {
            $this->withStoreCodeUrl=true;
        }

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }
    }

    public function index(){

        $this->initializer(['account/invoice','account/account', 'account/store_credit']);

        $white_list = CHECKOUT_TEST_STORES;

        //store current plan id in session to compare in case merchant update or downgrade th plan
        $this->session->data['current_plan'] = PRODUCTID;

        /*if (!in_array(STORECODE,$white_list)){
            $this->response->redirect($this->url->link('billingaccount/plans', '', true));

        }*/

        $billingcycle = "Free Account";
        $payment_method="banktransfer";
        $subscription_id=null;
        $product_id=null;
        $account = $this->account->selectByStoreCode(STORECODE);
        
        $clientProducts = $this->invoice->getClientsProducts();
        foreach ($clientProducts->products->product as $product) {
            if ($product->pid == PRODUCTID) {
                $billingcycle=$product->billingcycle;
                $payment_method = $product->paymentmethod;
                $product_id= $product->id;
                $subscription_id= $product->subscriptionid;
            }
        }
        if($account){
            $this->account->updateByStoreCode([
                'plan_type' => strtolower($billingcycle),
                'payment_method' => strtolower($payment_method),
                'service_id'=> $product_id,
                'paypal_subscription_id'=> $subscription_id,
            ]);
        }
        else{
            $accountId = $this->account->insert([
                'storecode' => STORECODE,
                'service_id' => $product_id,
                'payment_method' => strtolower($payment_method),
                'plan_type' => strtolower($billingcycle),
                'paypal_subscription_id'=> $subscription_id,
            ]);
        }

        $this->data['plan_type'] =strtolower($billingcycle);

        $this->language->load('billingaccount/plans');
        $this->language->load('account/charge');
        $this->document->setTitle($this->language->get('review_heading'));

        $geoip2 = new ModulesGarden\Geolocation\Submodules\GeoIP2();

        $pricingCurrency = "USD";
        $langcode="en";

        $whmcs= new whmcs();
        $clientDetails = $whmcs->getClientDetails(WHMCS_USER_ID);
        $currenciesObject = $this->invoice->getCurrencies();

        $clientCurrencyId = $clientDetails['currency'];
        foreach($currenciesObject->currencies->currency as $c){
            if($c->id == $clientCurrencyId){
                $pricingCurrency = $c->code;
                break;
            }
        }

       // $currencies = array('EGP','SAR');
       // if(isset($_GET['currency']) && in_array($_GET['currency'],$currencies)){
       //     $pricingCurrency = $_GET['currency'];
       // }

        $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
        $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);
        $pricingJSON = $pricingJSONAll[$pricingCurrency];

        if ($this->language->get('direction') == 'rtl') {
            $langcode = "ar";
        }

        $pricingJSON = array_merge($pricingJSON, $pricingJSON["strings"][$langcode]);

        // $pricingJSON['enterprise'] = [
        //     'monthly' => ['after' => 299]
        // ];

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['pricingJSON'] = $pricingJSON;

        
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('account/charge', '', 'SSL'),
            'separator' => false
        );

        $this->data['disableBreadcrumb'] = true;

        $this->data['customer'] = $this->user->getFullName();

        $this->data['flash_message'] = null;
        if (isset($this->session->data['charge'])) {
            $this->data['flash_message'] = $this->session->data['charge'];
            unset($this->session->data['charge']);
        }

        $this->data['plans'] = [
            3 => '1',
            53 => '2',
            6 => '3',
            8 => '4'
        ];

        foreach ($this->data['plans'] as $plan_id => $order) {
            foreach (['monthly', 'annually'] as $cycle) {
                $store_credits = $this->store_credit->getAvailableOffers([
                    'product_type' => 'plan',
                    'plan_ids' => [$plan_id],
                    'cycle' => $cycle
                ]);
                if(count($store_credits)) {
                    $this->data['store_credit'][$plan_id][$cycle] = $store_credits[0]['amount'][$pricingCurrency];
                }
            }
        }

        $this->data['plan_features']=array(
            'Free'=>[
                'no_commission',
                'products_num',
                'free_templates',
                'shipping_integration',
                'drop_shipping_integrations',
                'sell_on_facebook_and_instagram',
                'unlimited_orders_and_customers', //Sell On Messenger
                'advanced_e_commerce_reports',
                'free_templates_editor', //Facebook Pixel
                'no_user'
            ],
            'Professional'=>[
                'pro_products',
                'free_premium_templates',
//                'shipping_integration_pro',
                'free_marketing_and_seo_apps',//Ali Express.
                'sell_on_amazon',
                'advanced_inventory_management',//Marketing Tools
                'google_analytics',//Google Analytics
                'marketing_tools_and_integrations',//View Advanced Reports
                'advanced_reports',
                'blogs_and_webpages', //3 User
                'free_migration',
                'chat_support',
                'no_user_pro'
            ],
            'Ultimate'=>[
                'unlimited_products',
                'launch',
                'all_apps_free',
                'free_domain',
                'access_to_all_templates',
                'e_pay_drop_shipping',
//                'inventory_management',
//                'warehouse_management',
                'abandoned_cart_recovery',// Exportable Advanced Reports
                'advanced_loyalty_system',
                'exportable_e_commerce_reports',
                'personalized_marketing_help',
                'priority_support',
//                'unlimited_team_members',
//                'unlimited_branches_and_locations',
                'ten_users', // Unlimited Products
            ],

//            'Enterprise'=>[
//                'multi_merchant_store',
//                'free_mobile_app',
//                'mobile_customized_templates',
//                'free_pos_app',
//                'launch_my_store_plus',
//                'a_personal_account_manager',
//                'priority_in_support',
//                'free_premium_domain',
//                'exclusive_rates_with_our_partners',
//                'data_entry_for_all_products',
//                'promotional_banners_and_designs',
//                'marketing_campaigns_management',
//                'social_media_management',
//            ]
        );

        $this->data['sales_channels'] =array(
            'online_store'=>["1","1","1"],
            'sell_on_facebook_and_instagram'=>["1","1","1"],
            'sell_on_amazon'=>["0","1","1"],
            'free_mobile_app'=>["0","0","yes_on_yearly"],
            'point_of_sale'=>["0","0","1"],
        );

        $this->data['main_features'] =array(
            'products'=>['100','1000','unlimited'],
            'multi_language'=>["1","1","1"],
            'multi_currency'=>["1","1","1"],
            'payment_and_shipping_integrations'=>["1","1","1"],
            'inventory_management'=>["1","1","1"],
            'vat_and_tax_management'=>["1","1","1"],
            'free_ssl_certificate'=>["1","1","1"],
            'free_domain_name'=>["0","1","1"],
            'custom_order_workflow'=>["0","1","1"],
            'order_history_tracking'=>["0","1","1"],
            'customer_groups'=>["0","ten_groups","Unlimited"],
            'excel_export_and_import'=>["0","1","1"],
        );

        $this->data['advanced_features'] =array(
            'free_simple_responsive_templates'=> ["1","1","1"],
            'advanced_template_editor'=>["1","1","1"],
            'one_page_easy_and_secure_checkout'=>["1","1","1"],
            'free_premium_responsive_templates'=>["0","1","1"],
            'blogs'=>["0","1","1"],
            'web_pages'=>["0","1","1"],
            'advanced_product_filters'=>["0","1","1"],
            'live_chat_with_your_customers'=>["0","1","1"],
            'customize_copyrights'=>["0","1","1"],
            'multi_merchant_store'=>["0","0","0"],
        );

        $this->data['marketing_tools'] =array(
            'discounts_coupons'=>["5","unlimited","unlimited"],
            'analytics_integrations'=>["google_analytics_fb_pixel_only","all","all"],
            'gift_vouchers'=>["0","unlimited","unlimited"],
            'newsletters_and_email_campaigns'=>["0","1","1"],
            'customize_notification_emails'=>["0","1","1"],
            'sms_notifications_app'=>["0","1","1"],
            'deal_of_the_Day_sections'=>["0","1","1"],
            'product_rating_and_reviews'=>["0","1","1"],
            'seo_apps_and_friendly_urls'=>["0","1","1"],
            'customer_loyalty_system'=>["0","1","1"],
            'marketing_apps_and_integrations'=>["0","1","1"],
            'affiliates_app'=>["0","0","1"],
            'abandoned_cart_recovery'=>["0","0","1"],
        );

        $this->data['services'] =array(
            'free_data_migration'=>["0","1","1"],
            'personalized_marketing_help'=>["0","0","1"],
            'launch_my_store'=>["0","0","yes_on_yearly"],
            'launch_my_store_plus'=>["0","0","0"],
            'marketing_campaigns_management'=>["0","0","0"],
            'social_media_management'=>["0","0","0"],
        );

        $this->data['analytics'] =array(
            'professional_e_commerce_reports'=>["1","1","1"],
            'export_reports'=>["0","1","1"],
        );

        $this->data['team'] =array(
            'team_members'=>["one_user","10","unlimited"],
            'customizable_user_roles'=>["admin_only","customizable","customizable"],
        );

        $this->data['support'] =array(
            'live_chat_support'=>["0","1","1"],
            'priority_support'=>["0","0","1"],
            'personal_account_manager'=>["0","0","0"],
        );

        $this->data['paypalPlanIds'] = PAYPAL_PLANS_IDS;

        //        if ($this->language->get('direction') == 'rtl') {
        //            $this->template = 'account/charge/plans-ar.expand';
        //        } else {
        //            $this->template = 'account/charge/plans.expand';
        //        }

        $this->data['trial_plan_id'] =$this->plan_id;

        $this->data['withStoreCodeUrl']= $this->withStoreCodeUrl;

        $trials = $this->model_plan_trial->getAllTrials();
        $this->data['trials']= $trials;

        $this->template = 'account/charge/plans-v2.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

//    public function review()
//    {
//        $this->language->load('account/charge');
//        $this->document->setTitle($this->language->get('review_heading'));
//
//        $products = [
//            3 => 'free',
//            53 => 'professional',
//            6 => 'ultimate',
//            8 => 'enterprise'
//        ];
//
//        if (
//            isset($this->request->get['pid']) == false ||
//            isset($products[$this->request->get['pid']]) == false
//        ) {
//            throw new \Exception('invalid product id');
//        }
//
//        $product = $products[$this->request->get['pid']];
//
//        if (
//            isset($this->request->get['billingcycle']) == false ||
//            in_array($this->request->get['billingcycle'], ['monthly', 'annually']) == false
//        ) {
//            throw new \Exception('invalid billing cycle');
//        }
//
//        $billingCycle = $this->request->get['billingcycle'];
//
//        $productId = $this->request->get['pid'];
//
//        $geoip2 = new ModulesGarden\Geolocation\Submodules\GeoIP2();
//
//        $country = "US";
//        $pricingCurrency = "USD";
//        $langcode="en";
//
//        try {
//            $country = $geoip2->getCountry();
//        }
//        catch(\Exception $ex) { }
//
//        $countryCurrency = array("EG" => "EGP", "SA" => "SAR");
//
//        if (array_key_exists($country, $countryCurrency)) {
//            // $pricingCurrency = $countryCurrency[$country];
//        }
//
//        $pricingJSONFileName = "https://www.expandcart.com/wp-content/themes/bridge/pricing_all.json";
//        $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);
//        $pricingJSON = $pricingJSONAll[$pricingCurrency];
//        $pricingJSON['enterprise'] = [
//            'monthly' => ['after' => 299]
//        ];
//
//        if (isset($pricingJSON[$products[$productId]]) == false) {
//            throw new \Exception('Invalid pricing');
//        }
//
//        $tmpBillingCycle = $billingCycle;
//        if ($tmpBillingCycle == 'annually') {
//            $tmpBillingCycle = 'yearly';
//        }
//
//        if (isset($pricingJSON[$products[$productId]][$tmpBillingCycle]) == false) {
//            throw new \Exception('invalid cycle');
//        }
//
//        $data['breadcrumbs'] = array();
//        $data['breadcrumbs'][] = array(
//            'text' => $this->language->get('text_home'),
//            'href' => $this->url->link('common/home', '', 'SSL'),
//            'separator' => false
//        );
//
//        $data['breadcrumbs'][] = array(
//            'text' => $this->language->get('heading_title'),
//            'href' => $this->url->link('account/charge', '', 'SSL'),
//            'separator' => ' :: '
//        );
//
//        $data['breadcrumbs'][] = array(
//            'text' => $this->language->get('review_heading'),
//            'href' => $this->url->link('account/charge/form', '', 'SSL'),
//            'separator' => ' :: '
//        );
//
//        $data['customer'] = $this->user->getFullName();
//
//        $this->initializer(['account/account']);
//
//        $data['pricing'] = $pricingJSON;
//        $data['product'] = $pricingJSON[$products[$productId]][$tmpBillingCycle];
//        $data['productId'] = $productId;
//        $data['billingCycle'] = $billingCycle;
//        $payPalPlans = PAYPAL_PLANS_IDS;
//
//        //$data['paypalPlanId'] = $data['product']['paypal_plan_id'];
//
//        $data['paypalPlanId'] = $payPalPlans[$this->request->get['billingcycle']][$productId];
//
//        $this->data = $data;
//
//        $this->template = 'account/charge/review-v2.expand';
//
//        $this->children = array(
//            'common/header',
//            'common/footer',
//        );
//
//        $this->response->setOutput($this->render_ecwig());
//    }

    public function success()
    {
        if (!isset($_GET['auth_token']) || strlen($_GET['auth_token']) < 5) {
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'INVALID_TOKEN',
                'errors' => ['Illegal access']
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
        }

        $decrypt = function ($ciphertext, $key = null) {
            $key = $key ?: 'EC_' . STORECODE;
            $c = hex2bin($ciphertext);
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            if (hash_equals($hmac, $calcmac)) {
                return $original_plaintext;
            }

            return false;
        };

        $this->initializer(['account/transaction']);


        $this->language->load('account/charge');
        $this->document->setTitle($this->language->get('success_heading'));

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/charge', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('success_heading'),
            'href' => $this->url->link('account/charge/success', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['transaction'] = "";

        $this->data = $data;

        $this->session->data['charge']['status'] = 'OK';

        if ($_GET['ajax'] ==1){

            $response = [
                'status' => 'OK',
                'message'=> $this->language->get('success_hint'),
                'auth_token' => $_GET['auth_token']
            ];

            $this->response->json($response);
            return;
        }

        $this->template = 'account/charge/success.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function failed()
    {
        $this->language->load('account/charge');
        $this->document->setTitle($this->language->get('failed_heading'));

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/charge', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('failed_heading'),
            'href' => $this->url->link('account/charge/failed', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['charge_errors'] = null;
        if (isset($this->session->data['charge'])) {
            $data['charge_errors'] = $this->session->data['charge'];
            unset($this->session->data['charge']);
        }

        $this->data = $data;

        $response = [
            'status' => 0,
            'message'=>$this->language->get('failed_hint'),
            'charge_errors' => $data['charge_errors']
        ];

        $this->response->json($response);
        return;
    }

    public function stripeCharge()
    {

        /***************** Start ExpandCartTracking #347698  ****************/

        $event_attributes = ['subscription plan'=>$_POST['plan_id'],'payment term'=>$_POST['plan_type'],'amount'=>$_POST['amount']];
        // send mixpanel pay button click event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('Pay Button Clicked', $event_attributes);

        // send mixpanel add product event and update user products count
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('Pay Button Clicked', $event_attributes);

        /***************** End ExpandCartTracking #347698  ****************/

        //$this->session->data['product_id']=PRODUCTID;
if (isset(WHITELIST_STORES[STORECODE])) {
\Stripe\Stripe::setApiKey(STRIPE_TS_SECRET_KEY);
} else {
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
}
        $this->initializer(['account/transaction', 'account/stripe', 'account/account', 'account/invoice', 'account/store_credit']);

        /*if (!$token = $this->stripe->retrieveToken($_POST['stripeToken'])) {
            $response = [
                'status' => 'ERR',
                'error' => 'TOKEN_ERROR',
                'errors' => $this->stripe->getErrors()
            ];

            $this->response->json($response);
            return;
        }*/

        $account = $this->account->selectByStoreCode(STORECODE);

        $customerId = null;
        if (isset($account['stripe_customer_id']) && substr($account['stripe_customer_id'], 0, 3) == "cus") {
            $customerId = $account['stripe_customer_id'];
        }

        $geoipCountryCode = null;
        try {
            $GeoIP2 = new ModulesGarden\Geolocation\Submodules\GeoIP2();
            $geoipCountryCode = $GeoIP2->getCountry();
        } catch(\Exception $ex) { }

        $card = [
            'number' => str_replace(' ', '', $_POST['card_number']),
            'exp_month' => $_POST['month'],
            'exp_year' => '20' . $_POST['year'],
            'cvc' => $_POST['cvc'],
            'address_country' => $geoipCountryCode
        ];

        if (!$this->stripe->validateCard($card)) {
            $response = [
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => $this->stripe->getErrors()
            ];

            $this->response->json($response);
            return;
        }

        if (!$token = $this->stripe->createToken($card)) {
            $response = [
                'status' => 'ERR',
                'error' => 'TOKEN_ERROR',
                'errors' => $this->stripe->getErrors()
            ];

            $this->response->json($response);
            return;
        }

        $sourceData = [
            'type' => 'card',
            'token' => $token->id,
            'usage' => 'reusable'
        ];
        if (!$source = $this->stripe->createSource($sourceData)) {
            $response = [
                'status' => 'ERR',
                'error' => 'SOURCE_ERROR',
                'errors' => $this->stripe->getErrors()
            ];

            $this->response->json($response);
            return;
        }

        if (!$customerId) {
            if ($customer = $this->stripe->createCustomer(WHMCS_USER_ID, $_POST['customer'], EMAIL,$token->card->country)) {
                $customerId = $customer->id;
                $this->invoice->updateClient(['gatewayid' => $customerId]);
            } else {
                $response = [
                    'status' => 'ERR',
                    'error' => 'CUSTOMER_ERROR',
                    'errors' => $this->stripe->getErrors()
                ];

                $this->response->json($response);
                return;
            }
        } else {
            $customer = $this->stripe->retrieveCustomer($customerId);
            $customer->source = $source->id;
            if(method_exists(get_class($customer), "save")){
                try {
                    $customer = $customer->save();
                }catch (\Exception $e){
                    $response = [
                        'status' => 'ERR',
                        'error' => 'CUSTOMER_ERROR',
                        'errors' =>[$e->getMessage()]
                    ];
                    $this->response->json($response);
                    return;
                }

            }else{
                if ($customer = $this->stripe->createCustomer(WHMCS_USER_ID, $_POST['customer'], EMAIL,$token->card->country)) {
                    $customerId = $customer->id;
                    $this->invoice->updateClient(['gatewayid' => $customerId]);
                } else {
                    $response = [
                        'status' => 'ERR',
                        'error' => 'CUSTOMER_ERROR',
                        'errors' => $this->stripe->getErrors()
                    ];
                    $this->response->json($response);
                    return;
                }
            }
        }

        $currency = $_POST['currency'];
        $amount = $_POST['amount'];
        $planId = $_POST['plan_id'];
        $planType = $_POST['plan_type'];
        if ($_POST['plan_type']=="yearly"){
            $planType = "annually";
        }
        $discount_code = $_POST['discount_code'] ? $_POST['discount_code'] : null;
        $payment_method="stripe";
        $store_account = $this->account->selectByStoreCode(STORECODE);
        $old_billing_cycle= $store_account['plan_type'];
        $serviceid = $store_account['service_id'];


        // check if the user does not have a service id

        if (PRODUCTID != 3 && !$serviceid){
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'UNDEFINED_SERVICE_ID',
                'errors' => ['service id not found'],
                'debug_id' => ""
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
        }


        $due_date = null;
        $invoiceid = null;

        $plansOrder = [
            3 => 1,
            53 => 2,
            6 => 3,
            8 => 4
        ];

        $planIdNameMatches = [
            3 => 'free',
            53 => 'professional',
            6 => 'ultimate',
            8 => 'enterprise'
        ];


        // getting plan price from API instead of request body to avoid scam

        $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
        $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);

        if ($_POST['plan_type'] == "yearly"){
            $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['final'];
        }else{
            $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['after'];
            if (!$amount){
                $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['before'];
            }
        }
        $recurring_amount = $amount;

        $store_credit = false;
        $store_credits = $this->store_credit->getAvailableOffers([
            'product_type' => 'plan',
            'plan_ids' => [$planId],
            'cycle' => $planType
        ]);
        if(count($store_credits)){
            $store_credit = $store_credits[0];
            $credit_amount = $store_credit['amount'][$currency];
            $amount -= $credit_amount;
        }

        $discount_data = null;
        if($discount_code){
            $discount_data = $this->check_code($discount_code, $currency, $planId, $planType);
            if($discount_data['success'] == true){
                $amount = $discount_data['discount_price'];
            }
        }

        // creating an order & updating due date check

        if($plansOrder[PRODUCTID] < $plansOrder[$planId] ||
            ($plansOrder[PRODUCTID] >= $plansOrder[$planId] && $old_billing_cycle=='monthly' && $planType=="annually")){

            if($planType=="annually"){
                $due_date = date('Y-m-d', strtotime(date("Y-m-d", mktime()) . " + 1 year"));
            }
            elseif($planType=="monthly"){
                $due_date = date('Y-m-d', strtotime(date("Y-m-d", mktime()) . " + 1 month"));
            }

            $this->language->load('account/charge');
            $invoice_desc = $this->language->get($planIdNameMatches[$planId] . '_title') . ' ' .
                            $this->language->get('plan_title') .
                            ' (' . date('Y-m-d') . ' - ' . $due_date . ')';

            $notes = null;
            if($discount_data){
                switch ($discount_data['code_type']) {
                    case 'referral':
                        $notes ='Used referral code ' . $discount_data['code'];
                        break;
                    case 'reward':
                        $notes ='Used reward code ' . $discount_data['code'];
                        break;
                    default:
                        break;
                }
            }

            if($store_credit){
                if($notes) 
                    $notes .= "\n";
                else
                    $notes = '';
                $notes .= 'Used store credit id' . $store_credit['id'];
            }

            $invoice = $this->createInvoice($payment_method, $amount, $invoice_desc, $notes);
            $invoiceid = $invoice->invoiceid;
        }


        // stripe payment

        if ($source->card->three_d_secure != 'required'){
            if (!$charge = $this->stripe->charge(
                $customerId, $amount*100, $currency, $_POST['customer'], STORECODE,'',(string)$invoiceid )) {

                $response = [
                    'status' => 'ERR',
                    'error' => 'CHARGE_ERROR',
                    'errors' => $this->stripe->getErrors()
                ];
                $this->response->json($response);
                return;
            }
        } elseif ($source->card->three_d_secure == 'required'){

            $sourceData = [
                'type' => 'three_d_secure',
                'currency' => $currency,
                'amount' => $amount * 100,
                'three_d_secure' => [
                    'card' => $source->id,
                    'customer' => $customerId
                ],
                'redirect' => [
                    'return_url' => $this->url->link('', '', true,0,$this->withStoreCodeUrl) . 'account/charge/stripe_three_d_secure_return?customerid=' . $customerId . '&serviceid=' .$serviceid . '&invoiceid=' . $invoiceid . '&planid=' . $planId . '&plan_type=' . $planType . '&due_date=' . $due_date . '&recurring_amount=' . $recurring_amount . '&discount_code=' . $discount_code .'&store_credit_id=' . ($store_credit?$store_credit['id']:false)
                ],
            ];

            $three_D_secure_source = $this->stripe->createSource($sourceData);

            if($three_D_secure_source){
                $response = [
                    'status' => 'redirect',
                    'location' => $three_D_secure_source->redirect->url,
                ];
                $this->response->json($response);
                return;
            }
            else{
                $response = [
                    'status' => 'ERR',
                    'error' => 'CHARGE_ERROR',
                    'errors' => $this->stripe->getErrors()
                ];
                $this->response->json($response);
                return;
            }
        } else{
            // registering to the new plan

            $this->update_product($payment_method, $serviceid, $amount, $recurring_amount, $planId, $planType, $due_date);

            $response = [
                'status' => 'OK',
                'charge' => $payment_method,
                'location' => $this->url->link('account/invoices/store','downgrade=1',true,0,$this->withStoreCodeUrl)->format(),
            ];
            $this->response->json($response);
            return;
        }

        $balance_transaction = $this->stripe->retrieveTransaction($charge->balance_transaction);

        $payment_options = [
            'serviceid' => $serviceid,
            'invoiceid' => $invoiceid,
            'amount' => $amount,
            'recurring_amount' => $recurring_amount,
            'currency' => $currency,
            'planId' => $planId,
            'planType' => $planType,
            'due_date' => $due_date,
            'charge_id' => $charge->balance_transaction,
            'fees' => (float)($balance_transaction->fee / 100),
            'net' => (float)($balance_transaction->net / 100),
            'customerId' => $customerId,
            'discount_code' => $discount_code,
            'store_credit_id' => $store_credit?$store_credit['id']:false
        ];

        $authToken = $this->stripe_success_callback($payment_options);


        // return reponse to user

        $response = [
            'status' => 'OK',
            'charge' => $payment_method,
            'location' => $this->url->link(
                'account/invoices/store',
                sprintf('auth_token=%s', urlencode($authToken)),
                true,
                0,
                $this->withStoreCodeUrl
            )->format(),
        ];

        $this->response->json($response);
    }

    public function stripe_three_d_secure_return(){
        if($_GET['source'] && $_GET['customerid']){
if (isset(WHITELIST_STORES[STORECODE])) {
\Stripe\Stripe::setApiKey(STRIPE_TS_SECRET_KEY);
} else {
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
}
            $this->initializer(['account/stripe']);

            $sourceid = $_GET['source'];
            $sourceObject = $this->stripe->retrieveSource($sourceid, ['client_secret' => $_GET['client_secret']]);

            //check 3D secure verevied
            if($sourceObject->status != 'chargeable'){
                $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
            }

            $customerid = $_GET['customerid'];
            $invoiceid = $_GET['invoiceid'];
            $serviceid = $_GET['serviceid'];
            $planid = $_GET['planid'];
            $plan_type = $_GET['plan_type'];
            $due_date = $_GET['due_date'];
            $discount_code = $_GET['discount_code'];
            $store_credit_id = $_GET['store_credit_id'];
            $recurring_amount = $_GET['recurring_amount'];
            $amount = $sourceObject->amount;
            $currency = $sourceObject->currency;

            if (!$charge = $this->stripe->sourceCharge(
                $sourceid, $customerid, $amount, $currency, STORECODE, (string)$invoiceid )) {

                $response = [
                    'status' => 'ERR',
                    'error' => 'CHARGE_ERROR',
                    'errors' => $this->stripe->getErrors()
                ];
                $this->response->json($response);
                return;
            }

            $balance_transaction = $this->stripe->retrieveTransaction($charge->balance_transaction);

            $payment_options = [
                'serviceid' => $serviceid,
                'invoiceid' => $invoiceid,
                'amount' => $amount/100,
                'recurring_amount' => $recurring_amount,
                'currency' => $currency,
                'planId' => $planid,
                'planType' => $planType,
                'due_date' => $due_date,
                'charge_id' => $charge->balance_transaction,
                'fees' => (float)($balance_transaction->fee / 100),
                'net' => (float)($balance_transaction->net / 100),
                'customerId' => $customerid,
                'discount_code' => $discount_code,
                'store_credit_id' => $store_credit_id
            ];

            $authToken = $this->stripe_success_callback($payment_options);

            $this->response->redirect($this->url->link('account/invoices/store',sprintf('auth_token=%s&3dsecure=true', urlencode($authToken)),true,0,$this->withStoreCodeUrl));
        }
        else{
            $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
        }
    }

    private function stripe_success_callback($options){

        $this->initializer(['account/account', 'account/transaction', 'account/store_credit']);

        $payment_method = 'stripe';
        $serviceid = $options['serviceid'];
        $invoiceid = $options['invoiceid'];
        $amount = $options['amount'];
        $recurring_amount = $options['recurring_amount'];
        $currency = $options['currency'];
        $planId = $options['planId'];
        $planType = $options['planType'];
        $due_date = $options['due_date'];
        $charge_id = $options['charge_id'];
        $charge_fees = $options['fees'];
        $customerId = $options['customerId'];
        $discount_code = $options['discount_code'];
        $store_credit_id = $options['store_credit_id'];

        // registering to the new plan

        $this->update_product($payment_method, $serviceid, $amount, $recurring_amount, $planId, $planType, $due_date);


        // create a payment token

        $encrypt = function ($plaintext, $key = null) {
            return  base64_encode($plaintext);
            $key = $key ?: 'EC_' . STORECODE;
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            $ciphertext = bin2hex($iv . $hmac . $ciphertext_raw);
            return $ciphertext;
        };

        $authToken = $encrypt(json_encode([
            'transaction_id' => $charge_id,
            'timestamp' => time(),
            'fees' => $charge_fees
        ]), 'EC_' . STORECODE);


        // update the related tables

        $account = $this->account->selectByStoreCode(STORECODE);

        if (!$account) {
            $accountId = $this->account->insert([
                'storecode' => STORECODE,
                'stripe_customer_id' => $customerId,
                'service_id' => $serviceid,
                'payment_method' => $payment_method,
                'plan_type' => $planType,
                'stripe_subscription_id' => $customerId
            ]);
        } else {
            $accountId = $account['id'];
            if($account['payment_method'] == 'paypal'){
                $this->initializer(['account/paypal']);
                $this->paypal->cancelSubscription($account['paypal_subscription_id']);
            }
            $this->account->update([
                'stripe_customer_id' => $customerId,
                'payment_method' => $payment_method,
                'plan_type' => $planType,
                'stripe_subscription_id' => $customerId
            ]);
        }

        $this->transaction->insert([
            'store_code' => STORECODE,
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'status' => 1,
            'transaction_id' => $charge_id,
            'payment_method' => $payment_method,
            'plan_id' => $planId,
            'plan_type' => $planType,
            'auth_token' => $authToken,
            'account_id' => $accountId,
            'invoice_id' => $invoiceid,
        ]);

        if($discount_code){
            $type = $this->get_code_type($discount_code);
            if($type == 'referral'){
                $result = $this->add_referral_history($discount_code, $planId, $planType);

                if($result){
                    /***************** Start Subscribed With Referral Code Tracking #347733  ****************/

                    $this->load->model('setting/mixpanel');
                    $this->model_setting_mixpanel->trackEvent('Subscribed With Referral Code',[
                        'code' => $discount_code,
                        'plan_id' => $planId,
                        'plan_type' => $planType
                    ]);

                    $this->load->model('setting/amplitude');
                    $this->model_setting_amplitude->trackEvent('Subscribed With Referral Code',[
                        'code' => $discount_code,
                        'plan_id' => $planId,
                        'plan_type' => $planType
                    ]);

                    /***************** End Subscribed With Referral Code Tracking #347733  ****************/
                }
            }
            elseif($type == 'reward'){
                $this->invalidate_reward_code($discount_code);
            }
        }

        if($store_credit_id){
            $this->store_credit->use_credit($store_credit_id, $planId, $planType);
        }

        return $authToken;
    }

    public function downgrade()
    {
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' ) {

            $currency = $_POST['currency'];
            $amount = $_POST['amount'];
            $planId = $_POST['plan_id'];
            $planType = $_POST['plan_type'];
            if ($_POST['plan_type']=="yearly"){
                $planType = "annually";
            }

            $this->initializer(['account/invoice', 'account/account']);
            $store_account = $this->account->selectByStoreCode(STORECODE);
            $old_billing_cycle= $store_account['plan_type'];
            $serviceid = $store_account['service_id'];

            $payment_method="";
            $clientProducts = $this->invoice->getClientsProducts();

            foreach ($clientProducts->products->product as $product) {
                if ($product->pid == PRODUCTID) {
                    $serviceid = $product->id;
                    $payment_method=$product->paymentmethod;
                }
            }

            $planIdNameMatches = [
                3 => 'free',
                53 => 'professional',
                6 => 'ultimate',
                8 => 'enterprise'
            ];

            $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
            $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);
            if ($_POST['plan_type'] == "yearly"){
                $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['final'];
            }else{
                $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['after'];
                if (!$amount){
                    $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['before'];
                }
            }
            if ($serviceid) {
                $this->update_product($payment_method, $serviceid, $amount, $amount, $planId, $planType);
            }else{
                $this->session->data['charge'] = [
                    'status' => 'ERR',
                    'error' => 'INVALID_REQUEST',
                    'errors' => ['No service id found'],
                    'debug_id' => ""
                ];
                $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
            }

            $store_account = $this->account->selectByStoreCode(STORECODE);
            if($store_account['payment_method'] == 'paypal'){
                $this->initializer(['account/paypal']);
                $this->paypal->cancelSubscription($store_account['paypal_subscription_id']);
            }

            $this->account->update([
                'plan_type' => $planType,
                'service_id' => $serviceid
            ]);

            $this->session->data['charge']['status'] = 'downgrade';
            $response = [
                'status' => 'downgrade',
                'message'=> $this->language->get('success_hint'),
            ];
            $this->response->json($response);
            return;
        }
    }

    public function freeplan()
    {
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' ) {

            $this->initializer(['account/invoice', 'account/account']);

            $serviceid = null;
            $paymentMethod="banktransfer";
            $clientProducts = $this->invoice->getClientsProducts();

            foreach ($clientProducts->products->product as $product) {
                if ($product->pid == PRODUCTID) {
                    $serviceid = $product->id;
                }
            }
            if ($serviceid) {
                $invoice = $this->update_product($paymentMethod, $serviceid, "0.00", "0.00", '3', "Free Account");
                if (!$invoice) {
                    $this->session->data['charge'] = [
                        'status' => 'ERR',
                        'error' => 'INVALID_REQUEST',
                        'errors' => ['unable to create invoice'],
                        'debug_id' => ""
                    ];
                    $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
                }
            }else{
                $this->session->data['charge'] = [
                    'status' => 'ERR',
                    'error' => 'INVALID_REQUEST',
                    'errors' => ['No service id found'],
                    'debug_id' => ""
                ];
                $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
            }

            $store_account = $this->account->selectByStoreCode(STORECODE);
            if($store_account['payment_method'] == 'paypal'){
                $this->initializer(['account/paypal']);
                $this->paypal->cancelSubscription($store_account['paypal_subscription_id']);
            }

            $this->account->update([
                'payment_method' => 'banktransfer',
                'plan_type' => 'Free Plan',
            ]);

            $this->session->data['charge']['status'] = 'downgrade';
            $response = [
                'status' => 'downgrade',
                'message'=> $this->language->get('success_hint'),
            ];
            $this->response->json($response);
            return;
        }
    }

    private function paypalCharge($invoiceid, $orderid, $amount)
    {

        $currency = $_POST['currency'];
        $planId = $_POST['plan_id'];
        $paypalPlanId = $_POST['paypal_plan_id'];
        $planType = $_POST['plan_type'];

        if ($_POST['plan_type']=="yearly"){
            $planType = "annually";
        }
        $this->initializer(['account/transaction', 'account/paypal', 'account/account']);

        $encrypt = function ($plaintext, $key = null) {
            return  base64_encode($plaintext);

            $key = $key ?: 'EC_' . STORECODE;
            $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
            $ciphertext = bin2hex($iv . $hmac . $ciphertext_raw);
            return $ciphertext;
        };

        $subscriptionId = uniqid();

        $authToken = $encrypt(json_encode([
            'transaction_id' => $subscriptionId,
            'timestamp' => time()
        ]), 'EC_' . STORECODE);

        $this->transaction->insert([
            'store_code' => STORECODE,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 0,
            'transaction_id' => $subscriptionId,
            'payment_method' => 'paypal',
            'plan_id' => $_POST['plan_id'],
            'plan_type' =>$planType,
            'auth_token' => $authToken,
            'order_id' => $orderid,
            'invoice_id' => $invoiceid,
            'amount'=> $amount
        ]);

        return $subscriptionId;
    }

    public function logMe($f, $d)
    {
        $p = ONLINE_STORES_PATH . 'Api/logs/' . date('c') . '-' . time() . '-' . $f . '-os-' . rand() . '.log';

        file_put_contents($p, json_encode($d), FILE_APPEND);
    }

    public function paypalReturn()
    {
        if (!isset($_POST['txn_type'])) {
            $this->response->json(['status' => 'error']);
            return;
        }

        $this->initializer(['account/transaction', 'account/paypal', 'account/account','account/invoice', 'account/store_credit']);

        $this->logMe('req1', ['p' => $_POST]);

        $decryptPaypal = function ($ciphertext, $key = null) {
            $key = $key ?: 'EC_' . STORECODE;
            $c = hex2bin($ciphertext);
            $ivlen = openssl_cipher_iv_length($cipher = "AES-256-CTR");
            $iv = substr($c, 0, $ivlen);
            $ciphertext_raw = substr($c, $ivlen);
            $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            return $original_plaintext;
        };

        if ($_POST['txn_type'] == 'subscr_signup') {
            $customData = explode('*', $decryptPaypal($_POST["custom"], BILLING_PAYPAL_SECRET_KEY));
            $_POST['custom'] = $customData[0];
            $cp = $this->paypal->WHMCSCallback($_POST);

            $this->logMe('reqtowhmcs', ['p' => $cp]);

            if ($cp) {
                $this->response->json(['status' => 'OK']);
                return;
            } else {
                $this->response->json(['status' => 'error']);
                return;
            }
        }

        if($_POST['txn_type'] == 'subscr_payment'){
            // $paypalemail = $_POST["receiver_email"];
            // $payment_status = $_POST["payment_status"];
            // $old_subscr_id = $_POST["old_subscr_id"];
            // $txn_type = $_POST["txn_type"];
            // $paypalcurrency = $_REQUEST["mc_currency"];
            $subscr_id = $_POST["subscr_id"];
            $txn_id = $_POST["txn_id"];
            $mc_gross = $_POST["mc_gross"];
            $mc_fee = $_POST["mc_fee"];

            $customData = explode('*', $decryptPaypal($_POST["custom"], BILLING_PAYPAL_SECRET_KEY));
            $_POST['custom'] = $customData[0];

            $this->paypal->WHMCSCallback($_POST);

            $this->session->data['charge']['status'] = 'OK';
            $subscriptionId = $customData[1];
            $serviceid = $customData[3];
            $due_date = $customData[2];
            $discount_code = $customData[5];
            $store_credit_id = $customData[6];

            $transaction = null;
            if (!$transaction = $this->transaction->selectByTransactionId($subscriptionId)) {
                $response = [
                    'status' => 'ERR',
                    'error' => 'UNDEFINED_ROW',
                    'errors' => ['undefined row']
                ];
                $this->response->json($response);
                return;
            }

            $paypal_subscription_id = null;
            $clientProducts = $this->invoice->getClientsProducts();
            foreach ($clientProducts->products->product as $product) {
                if ($product->pid == PRODUCTID) {
                    $paypal_subscription_id = $product->subscriptionid;
                    break;
                }
            }

            $account = $this->account->selectByStoreCode(STORECODE);

            if (!$account) {
                $accountId = $this->account->insert([
                    'storecode' => STORECODE,
                    'paypal_subscription_id' => $paypal_subscription_id,
                    'service_id' => $serviceid,
                    'payment_method' => $transaction['payment_method'],
                    'plan_type' => $transaction['plan_type'],
                ]);
            } else {
                $accountId = $account['id'];
                if($account['payment_method'] == 'paypal'){
                    $this->paypal->cancelSubscription($account['paypal_subscription_id']);
                }
                $this->account->update([
                    'paypal_subscription_id' => $paypal_subscription_id,
                    'payment_method' => $transaction['payment_method'],
                    'plan_type' => $transaction['plan_type'],
                ]);
            }

            $this->transaction->update([
                'status' => 1,
                'account_id' => $accountId,
                'transaction_id' => $txn_id,
                'arguments' => json_encode([
                    'payer_id' => isset($_POST['payer_id']) ? $_POST['payer_id'] : '',
                    'token' => $_GET['token'],
                    'timestamp' => time()
                ]),
            ]);
            $store_account = $this->account->selectByStoreCode(STORECODE);
            $serviceid = $store_account['service_id'];

            $cp = $this->update_product(
                $transaction['payment_method'],
                $serviceid,
                $transaction['amount'],
                $transaction['amount'],
                $transaction['plan_id'],
                $transaction['plan_type'],
                $due_date
            );

            if($discount_code){
                $type = $this->get_code_type($discount_code);
                if($type == 'referral'){
                    $result = $this->add_referral_history($discount_code, $transaction['plan_id'], $transaction['plan_type']);

                    if($result){
                        /***************** Start Subscribed With Referral Code Tracking #347733  ****************/

                        $this->load->model('setting/mixpanel');
                        $this->model_setting_mixpanel->trackEvent('Subscribed With Referral Code',[
                            'code' => $discount_code,
                            'plan_id' => $transaction['plan_id'],
                            'plan_type' => $transaction['plan_type']
                        ]);

                        $this->load->model('setting/amplitude');
                        $this->model_setting_amplitude->trackEvent('Subscribed With Referral Code',[
                            'code' => $discount_code,
                            'plan_id' => $transaction['plan_id'],
                            'plan_type' => $transaction['plan_type']
                        ]);

                        /***************** End Subscribed With Referral Code Tracking #347733  ****************/
                    }
                }
                elseif($type == 'reward'){
                    $this->invalidate_reward_code($discount_code);
                }
            }

            if($store_credit_id){
                $this->store_credit->use_credit($store_credit_id, $transaction['plan_id'], $transaction['plan_type']);
            }

            $this->add_referral_history($discount_code, $transaction['plan_id'], $transaction['plan_type']);

            $this->logMe('reqtowhmcs', ['p' => $cp]);

            if ($cp) {
                $this->response->json(['status' => 'OK']);
                return;
            } else {
                $this->response->json(['status' => 'error']);
                return;
            }

            exit;
        }

    }

    protected function createOrder($payment_method,$plan_id,$plan_type,$serviceid=null){

        $invoice=null;

        $order = $this->invoice->createOrder(
            $plan_id,
            $payment_method,
            $plan_type,
            $serviceid
        );

        $debugId = function () {
            assert(strlen($data) == 16);
            $data = random_bytes(16);

            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            $id = vsprintf('%s%s-%s-%s-%s-%s%s%s-%s', array_merge(str_split(bin2hex($data), 4), [time()]));

            return $id;
        };

        // check if invoice created on WHMCS"
        if ( (!$order || $order->error) && (STAGING_MODE != 1) ) {
            $this->transaction->insert([
                'store_code' => STORECODE,
                'debug_id' => $debugId()
            ]);

            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['unable to create order'],
                'debug_id' => $debugId()
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
        }
        return $order;
    }

    protected function createInvoice($payment_method,$amount,$desc,$notes){

        $invoice = $this->invoice->createInvoice(
            $payment_method,
            $amount,
            $desc,
            $notes
        );

        $debugId = function () {
            assert(strlen($data) == 16);
            $data = random_bytes(16);

            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

            $id = vsprintf('%s%s-%s-%s-%s-%s%s%s-%s', array_merge(str_split(bin2hex($data), 4), [time()]));

            return $id;
        };

        // check if invoice created on WHMCS"
        if ( (!$invoice || $invoice->error) && (STAGING_MODE != 1) ) {
            $this->transaction->insert([
                'store_code' => STORECODE,
                'debug_id' => $debugId()
            ]);

            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['unable to create invoice'],
                'debug_id' => $debugId()
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
        }
        return $invoice;
    }

    public function paypal_button(){

        /***************** Start ExpandCartTracking #347698  ****************/

        $event_attributes = ['subscription plan'=>$_POST['plan_id'],'payment term'=>$_POST['plan_type'],'amount'=>$_POST['amount']];
        // send mixpanel pay button click event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('Pay Button Clicked', $event_attributes);

        // send mixpanel add product event and update user products count
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('Pay Button Clicked', $event_attributes);

        /***************** End ExpandCartTracking #347698  ****************/


        $this->initializer(['account/transaction','account/invoice','account/account', 'account/store_credit']);

        $currency = $_POST['currency'];
        $amount = $_POST['amount'];
        $planId = $_POST['plan_id'];
        $planType = $_POST['plan_type'];
        if ($_POST['plan_type']=="yearly"){
            $planType = "annually";
        }
        $discount_code = $_POST['discount_code'] ? $_POST['discount_code'] : null;
        $payment_method="paypal";
        $store_account = $this->account->selectByStoreCode(STORECODE);
        $old_billing_cycle= $store_account['plan_type'];
        $serviceid = $store_account['service_id'];


        // check if the user does not have a service id

        if (PRODUCTID != 3 && !$serviceid){
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'UNDEFINED_SERVICE_ID',
                'errors' => ['service id not found'],
                'debug_id' => ""
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
        }


        $due_date = null;
        $orderid = null;
        $invoiceid = null;

        $planIdNameMatches = [
            3 => 'free',
            53 => 'professional',
            6 => 'ultimate',
            8 => 'enterprise'
        ];

        $plansOrder = [
            3 => 1,
            53 => 2,
            6 => 3,
            8 => 4
        ];


        // getting plan price from API instead of request body to avoid scam

        $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
        $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);
//        $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['after'];

        if ($_POST['plan_type'] == "yearly"){
            $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['final'];
        }else{
            $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['after'];
            if (!$amount){
                $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['before'];
            }
        }
        $recurring_amount = $amount;

        $store_credit = false;
        $store_credits = $this->store_credit->getAvailableOffers([
            'product_type' => 'plan',
            'plan_ids' => [$planId],
            'cycle' => $planType
        ]);
        if(count($store_credits)){
            $store_credit = $store_credits[0];
            $credit_amount = $store_credit['amount'][$currency];
            $amount -= $credit_amount;
        }

        $discount_data = null;
        if($discount_code){
            $discount_data = $this->check_code($discount_code, $currency, $planId, $planType);
            if($discount_data['success'] == true){
                $amount = $discount_data['discount_price'];
            }
        }

        // creating an order & updating due date check

        if($plansOrder[PRODUCTID] < $plansOrder[$planId] || 
            (PRODUCTID >= $planId && $old_billing_cycle=='monthly' && $planType=="annually")){

            if($planType=="annually"){
                $due_date = date('Y-m-d', strtotime(date("Y-m-d", mktime()) . " + 1 year"));
            }
            elseif($planType=="monthly"){
                $due_date = date('Y-m-d', strtotime(date("Y-m-d", mktime()) . " + 1 month"));
            }

            $this->language->load('account/charge');
            $invoice_desc = $this->language->get($planIdNameMatches[$planId] . '_title') . ' ' . 
                            $this->language->get('plan_title') . 
                            ' (' . date('Y-m-d') . ' - ' . $due_date . ')';

            $notes = null;
            if($discount_data){
                switch ($discount_data['code_type']) {
                    case 'referral':
                        $notes ='Used referral code ' . $discount_data['code'];
                        break;
                    case 'reward':
                        $notes ='Used reward code ' . $discount_data['code'];
                        break;
                    default:
                        break;
                }
            }

            if($store_credit){
                if($notes) 
                    $notes .= "\n";
                else
                    $notes = '';
                $notes .= 'Used store credit id' . $store_credit['id'];
            }

            $invoice = $this->createInvoice($payment_method, $amount, $invoice_desc, $notes);
            $invoiceid = $invoice->invoiceid;

            $this->invoice->updateTypeAndRelForInvoiceItems($serviceid, $invoiceid);
        }

        $subscriptionId = $this->paypalCharge($invoiceid, $orderid, $amount);

        $whmcs= new whmcs();
        $clientDetails = $whmcs->getClientDetails(WHMCS_USER_ID);

        $params["sandbox"]=PAYPAL_ENV;
        $params["invoiceid"]=$invoiceid;
        $params["serviceid"]=$serviceid;
        $params["discountCode"]=$discount_code;
        $params["store_credit_id"]=$store_credit?$store_credit['id']:false;
        $params["due_date"]=$due_date;
        $params["oldbillingcycle"]=$old_billing_cycle;
        $params["planid"]=$planId;
        $params["plantype"]=$planType;
        $params["email"]=PAYPAL_EMAIL;
        $params["currency"]=$currency;
        $params["returnurl"]=$this->url->link('', '', true,0,$this->withStoreCodeUrl);
        $params["clientdetails"]["country"] =$clientDetails['country'];
        $params["clientdetails"]["phonenumber"]=$clientDetails['phonenumber'];
        $params["clientdetails"]["phonecc"]=$clientDetails['phonecc'];
        $params["clientdetails"]["email"]=$clientDetails['email'];
        $params["clientdetails"]["address1"] =$clientDetails['address1'];
        $params["clientdetails"]["city"]=$clientDetails['city'];
        $params["clientdetails"]["state"]=$clientDetails['state'];
        $params["clientdetails"]["postcode"]=$clientDetails['postcode'];
        $params["forceonetime"]=0;
        $params["forcesubscriptions"]=1;
        $params["description"] ="PayPal Subscription";

        $recurrings = [
            'recurringamount' => $recurring_amount,
            'recurringcycleperiod' => 1,
            'recurringcycleunits' => $planType == 'annually' ? 'Y' : 'M'
        ];

        if($recurring_amount != $amount){
            $recurrings['firstpaymentamount'] = $amount;
            $recurrings['firstcycleperiod'] = 1;
            $recurrings['firstcycleunits'] = $planType == 'annually' ? 'Y' : 'M';
        }
        // if(!$invoiceid){
        //     $nextduedate = null;
        //     $clientProducts = $this->invoice->getClientsProducts();
        //     foreach ($clientProducts->products->product as $product) {
        //         if ($product->pid == PRODUCTID) {
        //             $nextduedate=strtotime($product->nextduedate);
        //         }
        //     }

        //     $datediff = $nextduedate - time();
        //     $totaldays round($datediff / (60 * 60 * 24));

        //     if($totaldays >= 7){
        //         $recurrings['firstpaymentamount'] = 0;
        //         $recurrings['firstcycleperiod'] = round($totaldays/7);
        //         $recurrings['firstcycleunits'] = 'W';

        //         if($totaldays%7 != 0){
        //             $recurrings['secondpaymentamount'] = 0;
        //             $recurrings['secondcycleperiod'] = $totaldays%7;
        //             $recurrings['secondcycleunits'] = 'D';
        //         }
        //     }
        //     elseif($totaldays >= 1){
        //         $recurrings['firstpaymentamount'] = 0;
        //         $recurrings['firstcycleperiod'] = $totaldays;
        //         $recurrings['firstcycleunits'] = 'D';
        //     }
        // }

        $this->data['paypal_link'] = $this->paypal_link($params,$recurrings,$subscriptionId);

        $this->template = 'account/charge/paypal.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    protected function paypal_link($params,$recurrings,$subscriptionId): string
    {
        if (isset($params["sandbox"]) && $params["sandbox"]=="sandbox") {
            $url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        } else {
            $url = "https://www.paypal.com/cgi-bin/webscr";
        }
        $paypalemails = $params["email"];
        $paypalemails = explode(",", $paypalemails);
        $paypalemail = trim($paypalemails[0]);

        $plansOrder = [
            3 => 1,
            53 => 2,
            6 => 3,
            8 => 4
        ];
        $primaryserviceid = $params["serviceid"];
        if($plansOrder[PRODUCTID] < $plansOrder[$params['planId']] || 
            (PRODUCTID == $params['planid'] && $params['oldbillingcycle']=='monthly' && $params['plantype']=="annually")){
             $primaryserviceid = "U" . $primaryserviceid;
        }

        $encryptPaypal = function ($plaintext, $key = null) {
            $key = $key ?: 'EC_' . STORECODE;
            $ivlen = openssl_cipher_iv_length($cipher = "AES-256-CTR");
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
            $ciphertext = bin2hex($iv . $ciphertext_raw);
            return $ciphertext;
        };

        $custom = $encryptPaypal(implode('*', [
            $primaryserviceid,
            $subscriptionId,
            $params['due_date'],
            $params['serviceid'],
            STORECODE,
            $params['discountCode'],
            $params['store_credit_id']
        ]), BILLING_PAYPAL_SECRET_KEY);

        $recurringamount = $recurrings["recurringamount"];
        $recurringcycleperiod = $recurrings["recurringcycleperiod"];
        $recurringcycleunits = $recurrings["recurringcycleunits"];

        $firstfreetrial = 0;
        $firstpaymentamount = null;
        $firstcycleperiod = null;
        $firstcycleunits = null;
        if(isset($recurrings["firstpaymentamount"])){
            $firstfreetrial = 1;
            $firstpaymentamount = $recurrings["firstpaymentamount"];
            $firstcycleperiod = $recurrings["firstcycleperiod"];
            $firstcycleunits = $recurrings["firstcycleunits"];
        }

        // $secondfreetrial = 0;
        // $secondpaymentamount = null;
        // $secondcycleperiod = null;
        // $secondcycleunits = null;
        // if(isset($recurrings["secondpaymentamount"])){
        //     $secondfreetrial = 1;
        //     $secondpaymentamount = $recurrings["secondpaymentamount"];
        //     $secondcycleperiod = $recurrings["secondcycleperiod"];
        //     $secondcycleunits = $recurrings["secondcycleunits"];
        // }

        $currency = 'USD';
        if($params["currency"] != 'USD'){
            $currenciesObject = $this->invoice->getCurrencies();
            foreach($currenciesObject->currencies->currency as $c){
                if($c->code == $params["currency"]){
                    $recurringamount = $recurringamount / $c->rate;
                    if($firstfreetrial){
                        $firstpaymentamount = $firstpaymentamount / $c->rate;
                    }
                    break;
                }
            }
        }

        if ($this->paypal_currencyHasNoDecimals($currency)) {
            $recurringamount = $this->paypal_removeDecimal($recurringamount);
            if($firstfreetrial){
                $firstpaymentamount = $this->paypal_removeDecimal($firstpaymentamount);
            }
            // if($secondfreetrial){
            //     $secondpaymentamount = $this->paypal_removeDecimal($secondpaymentamount);
            // }
        }
        if ($params["clientdetails"]["country"] == "US" || $params["clientdetails"]["country"] == "CA") {
            $phonenumber = preg_replace("/[^0-9]/", "", $params["clientdetails"]["phonenumber"]);
            $phone1 = substr($phonenumber, 0, 3);
            $phone2 = substr($phonenumber, 3, 3);
            $phone3 = substr($phonenumber, 6);
        } else {
            $phone1 = $params["clientdetails"]["phonecc"];
            $phone2 = $params["clientdetails"]["phonenumber"];
        }
        $code = "<table style=\"border-collapse: separate; border-spacing: 15px 0;\"><tr>";


        $code .= "<td><form action=\"" . $url . "\" method=\"post\">\n<input type=\"hidden\" name=\"cmd\" value=\"_xclick-subscriptions\">\n<input type=\"hidden\" name=\"business\" value=\"" . $paypalemail . "\">";
        if (isset($params["style"]) && $params["style"]) {
            $code .= "<input type=\"hidden\" name=\"page_style\" value=\"" . $params["style"] . "\">";
        }
        $code .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $params["description"] .
            "\">\n<input type=\"hidden\" name=\"tax\" value=\"0.00" . 
            "\">\n<input type=\"hidden\" name=\"no_note\" value=\"1" . 
            "\">\n<input type=\"hidden\" name=\"no_shipping\" value=\"" . (isset($params["requireshipping"]) && $params["requireshipping"] ? "2" : "1") . 
            "\">\n<input type=\"hidden\" name=\"address_override\" value=\"" . (isset($params["overrideaddress"]) && $params["overrideaddress"] ? "1" : "0") . 
            "\">\n<input type=\"hidden\" name=\"first_name\" value=\"" . $params["clientdetails"]["firstname"] . 
            "\">\n<input type=\"hidden\" name=\"last_name\" value=\"" . $params["clientdetails"]["lastname"] . 
            "\">\n<input type=\"hidden\" name=\"email\" value=\"" . $params["clientdetails"]["email"] . 
            "\">\n<input type=\"hidden\" name=\"address1\" value=\"" . $params["clientdetails"]["address1"] . 
            "\">\n<input type=\"hidden\" name=\"city\" value=\"" . $params["clientdetails"]["city"] . 
            "\">\n<input type=\"hidden\" name=\"state\" value=\"" . $params["clientdetails"]["state"] . 
            "\">\n<input type=\"hidden\" name=\"zip\" value=\"" . $params["clientdetails"]["postcode"] . 
            "\">\n<input type=\"hidden\" name=\"country\" value=\"" . $params["clientdetails"]["country"] . 
            "\">\n<input type=\"hidden\" name=\"night_phone_a\" value=\"" . $phone1 . 
            "\">\n<input type=\"hidden\" name=\"night_phone_b\" value=\"" . $phone2 . "\">";
        if (isset($phone3)) {
            $code .= "<input type=\"hidden\" name=\"night_phone_c\" value=\"" . $phone3 . "\">";
        }

        if ($firstpaymentamount) {
            $code .= "\n<input type=\"hidden\" name=\"a1\" value=\"" . $firstpaymentamount .
                "\">\n<input type=\"hidden\" name=\"p1\" value=\"" . $firstcycleperiod .
                "\">\n<input type=\"hidden\" name=\"t1\" value=\"" . $firstcycleunits . "\">";
        }
        // if ($secondpaymentamount) {
        //     $code .= "\n<input type=\"hidden\" name=\"a2\" value=\"" . $secondpaymentamount .
        //         "\">\n<input type=\"hidden\" name=\"p2\" value=\"" . $secondcycleperiod .
        //         "\">\n<input type=\"hidden\" name=\"t2\" value=\"" . $secondcycleunits . "\">";
        // }

        $code .= "\n<input type=\"hidden\" name=\"a3\" value=\"" . $recurringamount .
            "\">\n<input type=\"hidden\" name=\"p3\" value=\"" . $recurringcycleperiod .
            "\">\n<input type=\"hidden\" name=\"t3\" value=\"" . $recurringcycleunits .
            "\">\n<input type=\"hidden\" name=\"src\" value=\"1\">\n<input type=\"hidden\" name=\"sra\" value=\"1\">\n";

        $code .= "<input type=\"hidden\" name=\"charset\" value=\"" . "UTF-8" . 
            "\">\n<input type=\"hidden\" name=\"currency_code\" value=\"" . $currency . 
            "\">\n<input type=\"hidden\" name=\"custom\" value=\"" . $custom . 
            "\">\n<input type=\"hidden\" name=\"return\" value=\"" . $params["returnurl"] . "account/charge" . 
            "\">\n<input type=\"hidden\" name=\"cancel_return\" value=\"" . $params["returnurl"] . "account/charge" . 
            "\">\n<input type=\"hidden\" name=\"notify_url\" value=\"" . "https://api.expandcart.com/paypal_notify.php" . 
            "\">\n<input type=\"hidden\" name=\"bn\" value=\"WHMCS_ST" . 
            "\">\n<input type=\"hidden\" name=\"rm\" value=\"2" . 
            "\">\n<input type=\"image\" src=\"view/image/paypal.png\" border=\"0\" name=\"submit\" alt=\"Make a subscription payment with PayPal\">\n</form></td>";

        $code .= "</tr></table>";
        return $code;
    }

    private function paypal_currencyHasNoDecimals($currency): bool
    {
        $currenciesWithoutDecimals = array("BYR", "BIF", "CLP", "KMF", "DJF", "HUF", "ISK", "JPY", "MGA", "MZN", "PYG", "RWF", "KRW", "VUV");
        if (in_array($currency, $currenciesWithoutDecimals)) {
            return true;
        }
        return false;
    }

    private function paypal_removeDecimal($amount): int
    {
        if (is_numeric($amount)) {
            $amount = round($amount);
        }
        return $amount;
    }

    private function update_product($payment_method, $serviceid, $first_payment_amount, $recurring_amount, $product_id, $billing_cycle, $due_date=null){
        $this->initializer(['account/invoice']);
        $body = [
            'serviceid'         => $serviceid,
            'status'            => 'Active',
            'paymentmethod'     => $payment_method,
            'firstpaymentamount'=> $first_payment_amount,
            'recurringamount'   => $recurring_amount,
            'pid'               => $product_id,
            'billingcycle'      => $billing_cycle
        ];

        if((int)$product_id == 3){
            $body['unset'] = ['subscriptionid'];
        }

        $newCustomFields = [];
        $clientProducts = $this->invoice->getClientsProducts();
        foreach ($clientProducts->products->product as $product) {
            if ($product->pid == PRODUCTID) {
                $CutsomFields = $product->customfields->customfield;
                $originalFields = $this->invoice->getCustomFields($product_id);
                foreach($CutsomFields as &$customField){
                    foreach($originalFields as $originalField){
                        if($customField->name == $originalField->fieldname){
                            $newCustomFields[$originalField->id] = $customField->value;
                            break;
                        }
                    }
                }

                $body['customfields'] = base64_encode(serialize($newCustomFields));
                break;
            }
        }

        if($due_date){
            $body['regdate'] = date('Y-m-d');
            $body['nextduedate'] = $due_date;
        }

        $product = $this->invoice->updateClientProduct($body);

        $productslimit = [
            '1' => 200,
            '2' => 300,
            '3' => 300,
            '52' => 0,
            '4' => 1000,
            '5' => 5000,
            '6' => 9999999,
            '8' => 9999999,
            '50' => 9999999,
            '53' => 9999999
        ];

        $this->ecusersdb->query(sprintf('UPDATE stores set productid = %s, productlimit = %s where storecode = "%s"', $product_id, $productslimit[(string)$product_id], STORECODE));

        return true;
    }

    public function tryPLan(){

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            $plan_id = $_POST['plan_id'];
            $this->load->model('plan/trial');
            $this->load->model('account/transaction');
            $trial = $this->model_plan_trial->getByPlanId($plan_id);

            if ($trial['total'] != 0){
                $this->error = $this->language->get('error_already_tried');
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            if ($this->plan_id != 3 ){
                $this->load->model('plan/trial');
                $this->model_plan_trial->end($this->plan_id);

                /***************** Start ExpandCartTracking #347735  ****************/
                $this->trackTrailEvents('End Trail',['Plan ID'=>$this->plan_id, "Plan Name"=>$this->model_account_transaction->getPlaneNameByCode($this->plan_id)]);
                /***************** End ExpandCartTracking #347735  ****************/
            }

            $this->model_plan_trial->add($plan_id);

            /***************** Start ExpandCartTracking #347734  ****************/
            $this->trackTrailEvents('Start Trail',['Plan ID'=>$plan_id, "Plan Name"=>$this->model_account_transaction->getPlaneNameByCode($plan_id)]);
            /***************** End ExpandCartTracking #347734  ****************/

            $this->load->model('setting/setting');
            $this->model_setting_setting->deleteSetting('trial');
            $data['trial_is_active']=1;
            $this->model_setting_setting->insertUpdateSetting('trial', $data);

            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->error = $this->language->get('error_try_plan');
        $result_json['success'] = '0';
        $result_json['errors'] = $this->error;
        $this->response->setOutput(json_encode($result_json));
    }

    public function endTrial()
    {
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            $plan_id = $_POST['plan_id'];
            $this->load->model('plan/trial');
            $this->load->model('account/transaction');

            $this->model_plan_trial->end($plan_id);

            /***************** Start ExpandCartTracking #347735  ****************/
            $this->trackTrailEvents('End Trail',['Plan ID'=>$plan_id, "Plan Name"=>$this->model_account_transaction->getPlaneNameByCode($plan_id)]);
            /***************** End ExpandCartTracking #347735  ****************/

            $result_json['success'] = '1';
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->error = $this->language->get('error_try_plan');
        $result_json['success'] = '0';
        $result_json['errors'] = $this->error;
        $this->response->setOutput(json_encode($result_json));
    }

    public function apply_discount_code(){
        $period = $_POST['period'] == 'yearly' ? 'annually' : $_POST['period'];
        $json_data = $this->check_code($_POST['code'], $_POST['currency'], $_POST['product_id'], $period);
        $this->response->json($json_data);
        return; 
    }

    private function check_code($code_string, $currency, $product_id, $period){
        $this->load->model('account/referral');
        $this->language->load('account/referral');
        $json_data;
        if(isset($code_string)){
            $json_data = $this->check_referral_code($code_string, $currency, $product_id, $period);
            if(!$json_data['success']){
                $json_data = $this->check_reward_code($code_string, $currency, $product_id, $period);
            }
        }else{
            $json_data['success'] = false;
            $json_data['message'] = $this->language->get('error_discount_code_required');
        }

        return $json_data;
    }

    private function check_referral_code($code_string, $currency, $product_id, $period){
        $code = $this->model_account_referral->get_referral_code($code_string);
        if($code){
            if($this->model_account_referral->used_code($code['id'])){
                $json_data['success'] = false;
                $json_data['message'] = $this->language->get('error_code_already_used');
            }else{

                $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
                $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);

                $rewards = $pricingJSONAll['referral_rewards'];

                $planIdNameMatches = [
                    3 => 'free',
                    53 => 'professional',
                    6 => 'ultimate',
                    8 => 'enterprise'
                ];

                if(isset($rewards[$planIdNameMatches[$product_id]][$period])){
                    $discount = $rewards[$planIdNameMatches[$product_id]][$period]['discount_percentage'];
                    $reward = $rewards[$planIdNameMatches[$product_id]][$period]['reward_amount'][$currency];

                    $pricingJSONPeriod = $period == 'annually' ? 'yearly' : 'monthly';
                    $amount;
                    if ($period == "annually"){
                        $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['final'];
                    }else{
                        $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['after'];
                        if (!$amount){
                            $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['before'];
                        }
                    }

                    $json_data['success'] = true;
                    $json_data['code'] = $code['code'];
                    $json_data['code_type'] = 'referral';
                    $json_data['original_price'] = $amount;
                    $json_data['discount_price'] = round($amount * (100 - $discount) / 100, 2);
                    $json_data['discount_value'] = round($discount, 2);
                    $json_data['message'] = sprintf($this->language->get('success_code_applied'), $json_data['original_price'] - $json_data['discount_price'], $currency);

                }else{
                    $json_data['success'] = false;
                    $json_data['message'] = $this->language->get('error_product_not_included');
                }
            }
        }else{
            $json_data['success'] = false;
            $json_data['message'] = $this->language->get('error_code_does_not_exist');
        }

        return $json_data;
    }

    private function check_reward_code($code_string, $currency, $product_id, $period){
        $code = $this->model_account_referral->get_reward_code($code_string);
        if($code){
            if($code['status'] == 1){
                $json_data['success'] = false;
                $json_data['message'] = $this->language->get('error_code_already_used');
            }else{

                $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
                $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);

                $planIdNameMatches = [
                    3 => 'free',
                    53 => 'professional',
                    6 => 'ultimate',
                    8 => 'enterprise'
                ];

                $discount = $code['amount'];

                $pricingJSONPeriod = $period == 'annually' ? 'yearly' : 'monthly';
                $amount;
                if ($period == "annually"){
                    $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['final'];
                }else{
                    $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['after'];
                    if (!$amount){
                        $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['before'];
                    }
                }

                $json_data['success'] = true;
                $json_data['code'] = $code['code'];
                $json_data['code_type'] = 'reward';
                $json_data['original_price'] = $amount;
                $json_data['discount_price'] = round($amount - $discount, 2);
                $json_data['discount_value'] = round($discount, 2);
                $json_data['message'] = sprintf($this->language->get('success_code_applied'), $discount, $currency);
            }
        }else{
            $json_data['success'] = false;
            $json_data['message'] = $this->language->get('error_code_does_not_exist');
        }

        return $json_data;
    }

    private function add_referral_history($code_string, $product_id, $period){
        $this->load->model('account/referral');
        $code = $this->model_account_referral->get_referral_code($code_string);
        if(!$code || $this->model_account_referral->used_code($code['id'])){
            return false;
        }

        $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
        $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);

        $rewards = $pricingJSONAll['referral_rewards'];

        $planIdNameMatches = [
            3 => 'free',
            53 => 'professional',
            6 => 'ultimate',
            8 => 'enterprise'
        ];

        if(!isset($rewards[$planIdNameMatches[$product_id]][$period])){
            return false;
        }

        $discount = $rewards[$planIdNameMatches[$product_id]][$period]['discount_percentage'];
        $reward = $rewards[$planIdNameMatches[$product_id]][$period]['reward_amount'][$code['currency']];

        $attributes = array(
            'product_id' => $product_id,
            'period' => $period,
            'store_name' => is_array($this->config->get('config_name')) ? array_values($this->config->get('config_name'))[0] : $this->config->get('config_name'),
            'discount_percentage' => $discount,
        );

        $data = [
            'store_code' => STORECODE,
            'history_type' => 'subscription',
            'referral_code_id' => $code['id'],
            'reward_amount' => $reward,
            'attributes' => $this->db->escape(serialize($attributes))
        ];

        $this->model_account_referral->add_history($data);
       
        return true;
    }

    private function invalidate_reward_code($code_string){
        $this->load->model('account/referral');
        $this->model_account_referral->invalidate_reward_code($code_string);
        return true;
    }

    private function get_code_type($code_string){

        if(!$code_string) return false;

        $this->load->model('account/referral');

        $code = $this->model_account_referral->get_referral_code($code_string);
        if($code){
            return 'referral';
        }

        $code = $this->model_account_referral->get_reward_code($code_string);
        if($code){
            return 'reward';
        }

        return false;
    }

    public function trackTrailEvents($event_name, $meta=[]){

        // send mixpanel  trail event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent($event_name,$meta);

        // send amplitude trail event
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent($event_name,$meta);

    }

}
