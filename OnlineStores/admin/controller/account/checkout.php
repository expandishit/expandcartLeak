<?php

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class ControllerAccountCheckout extends Controller
{
    protected $withStoreCodeUrl = false;

    protected $stripe_secret_key;

    public function __construct($registry)
    {
        parent::__construct($registry);
        if (strpos($_SERVER['SERVER_NAME'],strtolower(STORECODE)) !== false && strpos($_SERVER['SERVER_NAME'], 'expandcart') !== false) {
            $this->withStoreCodeUrl=true;
        }

        if (isset(WHITELIST_STORES[STORECODE]) && WHITELIST_STORES[STORECODE]){
            $this->stripe_secret_key = STRIPE_SECRET_KEY;
        } else {
            // $this->response->redirect($this->url->link('account/charge', '', true));
            $this->stripe_secret_key = STRIPE_SECRET_KEY;
        }
    }

    public function index()
    {
        $this->language->load('account/charge');
        $this->language->load('account/checkout');

        $this->initializer([
            'account/invoice', 
            'account/account',
            'marketplace/appservice',
            'setting/extension',
        ]);

        $plan_id = $this->session->data['checkout_pid'];
        $cycle = $this->session->data['checkout_cycle'];
        if(isset($_GET['pid']) && isset($_GET['cycle'])){
            $plan_id = $_GET['pid'];
            $cycle = $_GET['cycle'];
        }

        $app_service_id = $this->session->data['checkout_asid'];
        if(isset($_GET['asid'])){
            $app_service_id = $_GET['asid'];
        }

        if($plan_id && $cycle){
            $plans = [
                '3' => 'free',
                '53' => 'professional',
                '6' => 'ultimate',
                '8' => 'enterprise'
            ];

            $this->data['plan_names'] = $plans;
            $this->data['plan_orders'] = $plansOrder = [
                3 => '1',
                53 => '2',
                6 => '3',
                8 => '4'
            ];

            $this->data['plan_name'] = $plans[$plan_id];
            $this->data['plan_id'] = $plan_id;
            $this->data['cycle'] = $cycle;

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

            if($plansOrder[PRODUCTID] >= $plansOrder[$plan_id]){
                if($billingcycle != 'annually' && $cycle == "annually" && $plan_id != 3){
                    $this->data['hide_monthly'] = true;
                } else {
                    $this->response->redirect($this->url->link('account/charge', '', true));
                }
            }

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

            $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
            $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);
            $pricingJSON = $pricingJSONAll[$pricingCurrency][$plans[$plan_id]];

            if ($this->language->get('direction') == 'rtl') {
                $langcode = "ar";
            }

            $pricingJSON['strings'] = $pricingJSONAll[$pricingCurrency]["strings"][$langcode];

            $this->data['pricingJSON'] = $pricingJSON;
            $this->data['currency'] = $pricingCurrency;

            $plans_features = json_decode(file_get_contents('json/plans_features_description.json'),true);
            $this->data['features'] = $plans_features['plan_features'][ucfirst($plans[$plan_id])];
            $this->data['bundling_groups'] = $this->getBundlingGroups($plan_id);
        }
        elseif(isset($app_service_id)){
            $result =$this->appservice->getAppServiceById($app_service_id);

            $isApp = ($result['type']==1);
            $isService = ($result['type']==2);
            $isPurchased=($result['storeappserviceid'] != -1);
            $isFree=($result['packageappserviceid'] != -1);
            $installedextensions = $this->model_setting_extension->getInstalled('module');
            $isInstalled = in_array($result['CodeName'], $installedextensions);

            if($isPurchased ||
                $isInstalled ||
                $isFree ||
                $result['price'] == 0 ||
                (isset($plansOrder[$result['freeplan']]) && $plansOrder[$result['freeplan']] <= $plansOrder[PRODUCTID])){
                $this->response->redirect($this->url->link('marketplace/app', 'id='.$app_service_id, true,0,$this->withStoreCodeUrl));
            }

            $this->data['show_one_time'] = true;
            $this->data['hide_monthly'] = true;
            $this->data['hide_annually'] = true;
            $this->data['one_time_price'] = ((floor($result['price']) == round($result['price'], 2)) ? number_format($result['price']) : number_format($result['price'], 2));

            if($result['recurring']){
                $this->data['show_one_time'] = false;
                $whmcs_product = $this->invoice->getProducts($result['whmcsappserviceid']);
                if($whmcs_product->products->product[0]->pricing->USD->monthly > 0){
                    $this->data['hide_monthly'] = false;
                    $this->data['cycle'] = 'monthly';
                    $this->data['price']['monthly'] = $whmcs_product->products->product[0]->pricing->USD->monthly;
                }
                if($whmcs_product->products->product[0]->pricing->USD->annually > 0){
                    $this->data['hide_annually'] = false;
                    $this->data['cycle'] = 'annually';
                    $this->data['price']['annually'] = $whmcs_product->products->product[0]->pricing->USD->annually;
                }
            }


            $this->data['app_service_id'] = $app_service_id;
            $this->data['moduleType'] = $result['type'];
            $this->data['name'] = $result['Name'];
            $this->data['minidesc'] = $result['MiniDescription'];
            $this->data['desc'] = $result['Description'];
            $this->data['recurring'] = $result['recurring'];
            $this->data['isapp'] = $isApp;
            $this->data['isservice'] = $isService;
            $this->data['freepaymentterm'] = $result['freepaymentterm'];
            $this->data['extension']  = $result['CodeName'];
        }
        else{
            $this->response->redirect($this->url->link('account/charge', '', true));
        }


        $this->data['withStoreCodeUrl']= $this->withStoreCodeUrl;

        $this->data['customer'] = $this->user->getFullName();

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['disableBreadcrumb'] = true;

        $this->template = 'account/checkout/index.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function stripeCharge()
    {
        // $encrypt = function ($plaintext, $key = null) {
        //     return  base64_encode($plaintext);
        //     $key = $key ?: 'EC_' . STORECODE;
        //     $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        //     $iv = openssl_random_pseudo_bytes($ivlen);
        //     $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        //     $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        //     $ciphertext = bin2hex($iv . $hmac . $ciphertext_raw);
        //     return $ciphertext;
        // };

        // $authToken = $encrypt(json_encode([
        //     'transaction_id' => 'test',
        //     'timestamp' => time(),
        //     'fees' => 0
        // ]), 'EC_' . STORECODE);

        // $response = [
        //     'status' => 'OK',
        //     'charge' => 'stripe',
        //     'location' => $this->url->link(
        //         'account/invoices/store',
        //         sprintf('auth_token=%s', urlencode($authToken)),
        //         true,
        //         0,
        //         $this->withStoreCodeUrl
        //     )->format(),
        // ];

        // $this->response->json($response);
        // return;

        /***************** Start ExpandCartTracking #347698  ****************/

        if($_POST['plan_id']){
            $event_attributes = ['subscription plan'=>$_POST['plan_id'],'payment term'=>$_POST['plan_type']];
        } else {
            $event_attributes = ['app|service'=>$_POST['app_service_id']];
        }
        
        // send mixpanel pay button click event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('Pay Button Clicked', $event_attributes);

        // send mixpanel add product event and update user products count
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('Pay Button Clicked', $event_attributes);

        /***************** End ExpandCartTracking #347698  ****************/

        //$this->session->data['product_id']=PRODUCTID;

        \Stripe\Stripe::setApiKey($this->stripe_secret_key);

        $this->initializer(['account/transaction', 'account/stripe', 'account/account', 'account/invoice', 'marketplace/appservice']);

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
        $amount = null;
        $planId = $_POST['plan_id'];
        $bundlingGroups = []; // $_POST['bundling_groups'];
        $appServiceId = $_POST['app_service_id'];
        $planType = $_POST['plan_type'];
        $pricingPlanType = $planType == 'annually' ? 'yearly' : $planType;
        $discount_code = $_POST['discount_code'] ? $_POST['discount_code'] : null;
        $payment_method="stripe";
        $store_account = $this->account->selectByStoreCode(STORECODE);
        $old_billing_cycle= $store_account['plan_type'];
        $serviceid = $store_account['service_id'];

        if($planId){
            
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

            if ($_POST['plan_type'] == "annually"){
                $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$pricingPlanType]['final'];
            }else{
                $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$pricingPlanType]['after'];
                if (!$amount){
                    $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$pricingPlanType]['before'];
                }
            }
            
            // overwrite pricing amount if merchant chooses a ultimate plan & chooses a bundling items 
            if ($_POST['plan_type'] == "annually" && $planId == 6 && $bundlingGroups && count($bundlingGroups) > 0) {
                $bundle_id = count($bundlingGroups) === 2 ? 5 : $bundlingGroups[0];
            }
            
            if (isset($bundle_id)) {
                $currency = "USD";
                $amount = $this->bundleAmount($this->getBundleData($bundle_id));
            }
            
            $recurring_amount = $amount;

            $discount_data = null;
            if($discount_code){
                $discount_data = $this->check_code($discount_code, $currency, $planId, $planType, isset($bundle_id) ? $bundle_id : null);
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
                                ' (' . date('Y-m-d') . ' - ' . $due_date . ')' .
                                (isset($bundle_id) ? (' + ' . $this->language->get('bundle_' . $bundle_id)) : '');

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

                $invoice = $this->createInvoice($payment_method, $amount, $invoice_desc, $notes);
                $invoiceid = $invoice->invoiceid;
            }
        } else {
            $appService = $this->appservice->getAppServiceById($appServiceId);
            if(!$appService){
                $this->session->data['charge'] = [
                    'status' => 'ERR',
                    'error' => 'UNDEFINED_SERVICE_ID',
                    'errors' => ['service id not found'],
                    'debug_id' => ""
                ];
                $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
            }

            if($appService['recurring']){
                $whmcs_product = $this->model_account_invoice->getProducts($appService['whmcsappserviceid']);
                $amount = $whmcs_product->products->product[0]->pricing->USD->{$planType};
            } else {
                $amount = $appService['price'];
            }
            $recurring_amount = $amount;

            $discount_data = null;
            if($discount_code){
                $discount_data = $this->check_code($discount_code, $currency, $planId, $planType);
                if($discount_data['success'] == true){
                    $amount = $discount_data['discount_price'];
                }
            }

            $order = $this->createOrder($payment_method, $appService['whmcsappserviceid'], $planType);
            $orderid = $order->orderid;
            $serviceid = $order->serviceids;
            $invoiceid = $order->invoiceid;
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

            if($planId){
                $return_url = $this->url->link('', '', true,0,$this->withStoreCodeUrl) . 'account/checkout/stripe_three_d_secure_return?customerid=' . $customerId . '&serviceid=' .$serviceid . '&invoiceid=' . $invoiceid . '&planid=' . $planId . '&plan_type=' . $planType . '&due_date=' . $due_date . '&recurring_amount=' . $recurring_amount . '&discount_code=' . $discount_code . (isset($bundle_id) ? ('&bundle_id=' . $bundle_id) : '');
            } else {
                $return_url = $this->url->link('', '', true,0,$this->withStoreCodeUrl) . 'account/checkout/stripe_three_d_secure_return?customerid=' . $customerId . '&serviceid=' .$serviceid . '&invoiceid=' . $invoiceid . '&orderid=' . $orderid . '&appserviceid=' . $appServiceId . '&plan_type=' . $planType . '&discount_code=' . $discount_code;
            }

            $sourceData = [
                'type' => 'three_d_secure',
                'currency' => $currency,
                'amount' => $amount * 100,
                'three_d_secure' => [
                    'card' => $source->id,
                    'customer' => $customerId
                ],
                'redirect' => [
                    'return_url' => $return_url
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
        }

        $balance_transaction = $this->stripe->retrieveTransaction($charge->balance_transaction);

        $payment_options = [
            'serviceid' => $serviceid,
            'invoiceid' => $invoiceid,
            'orderid' => $orderid,
            'amount' => $amount,
            'recurring_amount' => $recurring_amount,
            'currency' => $currency,
            'planId' => $planId,
            'appServiceId' => $appServiceId,
            'planType' => $planType,
            'due_date' => $due_date,
            'charge_id' => $charge->balance_transaction,
            'fees' => (float)($balance_transaction->fee / 100),
            'net' => (float)($balance_transaction->net / 100),
            'customerId' => $customerId,
            'discount_code' => $discount_code
        ];
        if (isset($bundle_id)) {
            $payment_options['bundle_id'] = $bundle_id;
        }
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

    public function stripe_three_d_secure_return()
    {
        if($_GET['source'] && $_GET['customerid']){

            \Stripe\Stripe::setApiKey($this->stripe_secret_key);

            $this->initializer(['account/stripe', 'account/invoice']);

            $sourceid = $_GET['source'];
            $sourceObject = $this->stripe->retrieveSource($sourceid, ['client_secret' => $_GET['client_secret']]);

            //check 3D secure verevied
            if($sourceObject->status != 'chargeable'){
                $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
            }

            $customerid = $_GET['customerid'];
            $invoiceid = $_GET['invoiceid'];
            $serviceid = $_GET['serviceid'];
            $orderid = $_GET['orderid'];
            $planid = $_GET['planid'];
            $bundle_id = isset($_GET['bundle_id']) ? $_GET['bundle_id'] : null;
            $appserviceid = $_GET['appserviceid'];
            $plan_type = $_GET['plan_type'];
            $due_date = $_GET['due_date'];
            $discount_code = $_GET['discount_code'];
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
                'orderid' => $orderid,
                'amount' => $amount/100,
                'recurring_amount' => $recurring_amount,
                'currency' => $currency,
                'planId' => $planid,
                'appServiceId' => $appserviceid,
                'planType' => $plan_type,
                'due_date' => $due_date,
                'charge_id' => $charge->balance_transaction,
                'fees' => (float)($balance_transaction->fee / 100),
                'net' => (float)($balance_transaction->net / 100),
                'customerId' => $customerid,
                'discount_code' => $discount_code
            ];
            if (!is_null($bundle_id)) {
                $payment_options['bundle_id'] = $bundle_id;
            }
            $authToken = $this->stripe_success_callback($payment_options);

            $this->response->redirect($this->url->link('account/invoices/store',sprintf('auth_token=%s&3dsecure=true', urlencode($authToken)),true,0,$this->withStoreCodeUrl));
        }
        else{
            $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
        }
    }

    private function stripe_success_callback($options)
    {

        $this->initializer(['account/account', 'account/transaction', 'marketplace/appservice', 'setting/extension']);

        $payment_method = 'stripe';
        $serviceid = $options['serviceid'];
        $invoiceid = $options['invoiceid'];
        $orderid = $options['orderid'];
        $amount = $options['amount'];
        $recurring_amount = $options['recurring_amount'];
        $currency = $options['currency'];
        $planId = $options['planId'];
        $bundle_id = (isset($options['bundle_id']) && $options['bundle_id']) ? $options['bundle_id'] : null;
        $appServiceId = $options['appServiceId'];
        $planType = $options['planType'];
        $due_date = $options['due_date'];
        $charge_id = $options['charge_id'];
        $charge_fees = $options['fees'];
        $customerId = $options['customerId'];
        $discount_code = $options['discount_code'];


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


        if($planId){

            // registering to the new plan
            $data = [
                'payment_method' => $payment_method,
                'serviceid' => $serviceid,
                'first_payment_amount' => $amount,
                'recurring_amount' => $recurring_amount,
                'product_id' => $planId,
                'billing_cycle' => $planType,
                'due_date' => $due_date
            ];
            if ($bundle_id) {
                $data['bundle_id'] = $bundle_id;
            }
            $this->invoice->update_product($data);

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
        } else {
            $this->invoice->acceptOrder($orderid);
            $this->invoice->updateInvoice($invoiceid, ['status' => 'Paid']);
            $appservice = $this->appservice->getAppServiceById($appServiceId);
            $installedextensions = $this->extension->getInstalled('module');
            $isInstalled = in_array($appservice['extension'], $installedextensions);
            if($appservice['type'] == 1 && !$isInstalled){
                $this->request->get['extension'] = $appservice['extension'];
                $this->getChild('marketplace/home/install');
            }
        }

        $this->transaction->insert([
            'store_code' => STORECODE,
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'status' => 1,
            'transaction_id' => $charge_id,
            'payment_method' => $payment_method,
            'plan_id' => $planId ? $planId : $appServiceId,
            'plan_type' => $planType,
            'auth_token' => $authToken,
            'account_id' => $accountId,
            'order_id' => $orderid,
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

        return $authToken;
    }

    private function paypalCharge($invoiceid, $orderid, $amount)
    {

        $currency = $_POST['currency'];
        $planId = $_POST['plan_id'];
        $appServiceId = $_POST['app_service_id'];
        $planType = $_POST['plan_type'];
        $pricingPlanType = $planType == 'annually' ? 'yearly' : $planType;

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
            'plan_id' => $planId ? $planId : $appServiceId,
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

        $this->initializer(['account/transaction', 'account/paypal', 'account/account','account/invoice', 'marketplace/appservice', 'setting/extension']);

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

            if(in_array($transaction['plan_id'], ['3', '53', '6', '8'])){
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

                $cp = $this->invoice->update_product([
                    'payment_method' => $transaction['payment_method'],
                    'serviceid' => $serviceid,
                    'first_payment_amount' => $transaction['amount'],
                    'recurring_amount' => $transaction['amount'],
                    'product_id' => $transaction['plan_id'],
                    'billing_cycle' => $transaction['plan_type'],
                    'due_date' => $due_date,
                ]);
            } else {
                $appServiceId = $transaction['plan_id'];
                $appservice = $this->appservice->getAppServiceById($appServiceId);
                $installedextensions = $this->extension->getInstalled('module');
                $isInstalled = in_array($appservice['extension'], $installedextensions);
                if($appservice['type'] == 1 && !$isInstalled){
                    $this->request->get['extension'] = $appservice['extension'];
                    $this->getChild('marketplace/home/install');
                }
            }


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

    protected function createOrder($payment_method,$product_id,$plan_type=null,$serviceid=null)
    {

        $invoice=null;

        $order = $this->invoice->createOrder(
            $product_id,
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

    protected function createInvoice($payment_method,$amount,$desc,$notes)
    {

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

    public function paypal_button()
    {

        /***************** Start ExpandCartTracking #347698  ****************/

        if($_POST['plan_id']){
            $event_attributes = ['subscription plan'=>$_POST['plan_id'],'payment term'=>$_POST['plan_type']];
        } else {
            $event_attributes = ['app|service'=>$_POST['app_service_id']];
        }

        // send mixpanel pay button click event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('Pay Button Clicked', $event_attributes);

        // send mixpanel add product event and update user products count
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('Pay Button Clicked', $event_attributes);

        /***************** End ExpandCartTracking #347698  ****************/


        $this->initializer(['account/transaction','account/invoice','account/account','marketplace/appservice']);

        $currency = $_POST['currency'];
        $amount = $_POST['amount'];
        $planId = $_POST['plan_id'];
        $appServiceId = $_POST['app_service_id'];
        $planType = $_POST['plan_type'];
        $pricingPlanType = $planType == 'annually' ? 'yearly' : $planType;
        $discount_code = $_POST['discount_code'] ? $_POST['discount_code'] : null;
        $payment_method="paypal";
        $store_account = $this->account->selectByStoreCode(STORECODE);
        $old_billing_cycle= $store_account['plan_type'];
        $serviceid = $store_account['service_id'];


        if($planId){
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
            // $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$_POST['plan_type']]['after'];

            if ($_POST['plan_type'] == "annually"){
                $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$pricingPlanType]['final'];
            }else{
                $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$pricingPlanType]['after'];
                if (!$amount){
                    $amount = $pricingJSONAll[$currency][$planIdNameMatches[$planId]][$pricingPlanType]['before'];
                }
            }
            $recurring_amount = $amount;

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

                $invoice = $this->createInvoice($payment_method, $amount, $invoice_desc, $notes);
                $invoiceid = $invoice->invoiceid;

                $this->invoice->updateTypeAndRelForInvoiceItems($serviceid, $invoiceid);
            }
        } else {
            $appService = $this->appservice->getAppServiceById($appServiceId);
            if(!$appService){
                $this->session->data['charge'] = [
                    'status' => 'ERR',
                    'error' => 'UNDEFINED_SERVICE_ID',
                    'errors' => ['service id not found'],
                    'debug_id' => ""
                ];
                $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
            }

            if($appService['recurring']){
                $whmcs_product = $this->model_account_invoice->getProducts($appService['whmcsappserviceid']);
                $amount = $whmcs_product->products->product[0]->pricing->USD->{$planType};
            } else {
                $amount = $appService['price'];
            }
            $recurring_amount = $amount;

            $discount_data = null;
            if($discount_code){
                $discount_data = $this->check_code($discount_code, $currency, $planId, $planType);
                if($discount_data['success'] == true){
                    $amount = $discount_data['discount_price'];
                }
            }

            $order = $this->createOrder($payment_method, $appService['whmcsappserviceid'], $planType);
            $orderid = $order->orderid;
            $serviceid = $order->serviceids;
            $invoiceid = $order->invoiceid;
        }

        $subscriptionId = $this->paypalCharge($invoiceid, $orderid, $amount);

        $whmcs= new whmcs();
        $clientDetails = $whmcs->getClientDetails(WHMCS_USER_ID);

        $params["sandbox"]=PAYPAL_ENV;
        $params["invoiceid"]=$invoiceid;
        $params["serviceid"]=$serviceid;
        $params["orderid"]=$orderid;
        $params["discountCode"]=$discount_code;
        $params["due_date"]=$due_date;
        $params["oldbillingcycle"]=$old_billing_cycle;
        $params["planid"]=$planId;
        $params["appServiceId"]=$appServiceId;
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
        if($planId || (isset($appService) && $appService['recurring'] == 1)){
            $params["description"] ="PayPal Subscription";
            $params['type'] = 'subscription';
        } else {
            $params["description"] ="PayPal One Time Payment";
            $params['type'] = 'one_time_payment';
        }

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
            $params['orderid']
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


        if($params['type'] == 'subscription') {
            $code .= "<td><form action=\"" . $url . "\" method=\"post\">\n<input type=\"hidden\" name=\"cmd\" value=\"_xclick-subscriptions\">\n<input type=\"hidden\" name=\"business\" value=\"" . $paypalemail . "\">";
        } else {
            $code .= "<td><form action=\"" . $url . "\" method=\"post\">\n<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">\n<input type=\"hidden\" name=\"business\" value=\"" . $paypalemail . "\">";
        }
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

        if($params['type'] == 'subscription'){
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
                "\">\n<input type=\"hidden\" name=\"t3\" value=\"" . $recurringcycleunits;

            $code .= "\">\n<input type=\"hidden\" name=\"bn\" value=\"WHMCS_ST";
        } else {
            $code .= "\n<input type=\"hidden\" name=\"amount\" value=\"" . ($firstpaymentamount ? $firstpaymentamount : $recurringamount);
            $code .= "\">\n<input type=\"hidden\" name=\"bn\" value=\"WHMCS_BuyNow_ST";
        }

        $appService = $this->appservice->getAppServiceById($params['appServiceId']);
        if($params['appServiceId']) {
            $code .= "\">\n<input type=\"hidden\" name=\"item_name\" value=\"" . $appService['Name'];
        }

        $code .= "\">\n<input type=\"hidden\" name=\"src\" value=\"1\">\n<input type=\"hidden\" name=\"sra\" value=\"1\">\n";
        $code .= "<input type=\"hidden\" name=\"charset\" value=\"" . "UTF-8" . 
            "\">\n<input type=\"hidden\" name=\"currency_code\" value=\"" . $currency . 
            "\">\n<input type=\"hidden\" name=\"custom\" value=\"" . $custom . 
            "\">\n<input type=\"hidden\" name=\"return\" value=\"" . $params["returnurl"] . ($params['planid'] || $appService['type'] == 2 ? "common/dashboard" : "marketplace/app?id=" . $params['appServiceId']) . 
            "\">\n<input type=\"hidden\" name=\"cancel_return\" value=\"" . $_SERVER['HTTP_REFERER'] . 
            "\">\n<input type=\"hidden\" name=\"notify_url\" value=\"" . "https://api.expandcart.com/paypal_notify.php" . 
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

    public function apply_discount_code()
    {
        $period = $_POST['period'] == 'yearly' ? 'annually' : $_POST['period'];
        $currency = $_POST['currency'] ? $_POST['currency'] : 'USD';
        $json_data = $this->check_code($_POST['code'], $_POST['currency'], $_POST['product_id'], $period);
        $this->response->json($json_data);
        return; 
    }

    private function check_code($code_string, $currency, $product_id, $period, int $bundle_id = null)
    {
        $this->load->model('account/referral');
        $this->language->load('account/referral');
        $json_data = [];
        if(isset($code_string)){
            $json_data = $this->check_referral_code($code_string, $currency, $product_id, $period, $bundle_id);
            if(!$json_data['success']){
                $json_data = $this->check_reward_code($code_string, $currency, $product_id, $period, $bundle_id);
            }
        }else{
            $json_data['success'] = false;
            $json_data['message'] = $this->language->get('error_discount_code_required');
        }

        return $json_data;
    }

    private function check_referral_code($code_string, $currency, $product_id, $period, int $bundle_id = null)
    {
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
                    $amount = 0;
                    if ($period == "annually"){
                        $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['final'];
                    }else{
                        $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['after'];
                        if (!$amount){
                            $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['before'];
                        }
                    }
                    if ($bundle_id) {
                        $amount = $this->bundleAmount($this->getBundleData($bundle_id));  
                    }

                    $json_data['success'] = true;
                    $json_data['code'] = $code['code'];
                    $json_data['code_type'] = 'referral';
                    $json_data['original_price'] = $amount;
                    $json_data['discount_price'] = $amount * (100 - $discount) / 100;
                    $json_data['discount_value'] = $discount;
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

    private function check_reward_code($code_string, $currency, $product_id, $period, int $bundle_id = null)
    {
        $this->load->model('marketplace/appservice', 'account/invoice');
        $code = $this->model_account_referral->get_reward_code($code_string);
        if($code){
            if($code['status'] == 1){
                $json_data['success'] = false;
                $json_data['message'] = $this->language->get('error_code_already_used');
            }else{
                $discount = $code['amount'];

                $planIdNameMatches = [
                    3 => 'free',
                    53 => 'professional',
                    6 => 'ultimate',
                    8 => 'enterprise'
                ];

                if(array_key_exists($product_id, $planIdNameMatches)){
                    $pricingJSONFileName = "https://ectools.expandcart.com/storage/json/pricing.json";
                    $pricingJSONAll = json_decode(file_get_contents($pricingJSONFileName), true);

                    $pricingJSONPeriod = $period == 'annually' ? 'yearly' : 'monthly';
                    $amount = 0;
                    if ($period == "annually"){
                        $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['final'];
                    }else{
                        $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['after'];
                        if (!$amount){
                            $amount = $pricingJSONAll[$currency][$planIdNameMatches[$product_id]][$pricingJSONPeriod]['before'];
                        }
                    }
                    if ($bundle_id) {
                        $amount = $this->bundleAmount($this->getBundleData($bundle_id));  
                    }
                } elseif($app = $this->model_marketplace_appservice->getAppServiceById($product_id)){
                    if($app['recurring']){
                        $whmcs_product = $this->model_account_invoice->getProducts($app['whmcsappserviceid']);
                        $amount = $whmcs_product->products->product[0]->pricing->USD->{$period};
                    } else {
                        $amount = $app['price'];
                    }
                } else {
                    $json_data['success'] = false;
                    $json_data['message'] = $this->language->get('error_product_not_included');
                }

                $json_data['success'] = true;
                $json_data['code'] = $code['code'];
                $json_data['code_type'] = 'reward';
                $json_data['original_price'] = $amount;
                $json_data['discount_price'] = $amount - $discount;
                $json_data['discount_value'] = $discount;
                $json_data['message'] = sprintf($this->language->get('success_code_applied'), $discount, $currency);
            }
        }else{
            $json_data['success'] = false;
            $json_data['message'] = $this->language->get('error_code_does_not_exist');
        }

        return $json_data;
    }

    private function add_referral_history($code_string, $product_id, $period)
    {
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

    private function invalidate_reward_code($code_string)
    {
        $this->load->model('account/referral');
        $this->model_account_referral->invalidate_reward_code($code_string);
        return true;
    }

    private function get_code_type($code_string)
    {

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
    
    private function getBundlingGroups(int $plan_id)
    {
        return [];
        
        $this->language->load('account/charge');
        
        if ($plan_id === 6) {
            return [
                [
                'id' => 3,
                'price' => 250,
                'price_before' => 500,
                'price_prefix' => '$',
                'name' => $this->language->get('bundle_3'),
                'bundle' => json_encode($this->getBundleData(3)),
                ],
                [
                    'id' => 4,
                    'price' => 500,
                    'price_before' => 1500,
                    'price_prefix' => '$',
                    'name' => $this->language->get('bundle_4'),
                    'bundle' => json_encode($this->getBundleData(4)),
                ], 
            ];
        }
    }
    
    private function getBundleData(int $bundle_id)
    {
        $this->load->model('account/invoice');
        $whmcsResult = $this->model_account_invoice->getProducts($bundle_id);
        // validate the bundle
        if (!$whmcsResult || (is_object($whmcsResult) && property_exists($whmcsResult, 'error'))) {       
            $this->session->data['charge'] = [
                'status' => 'ERR',
                'error' => 'UNDEFINED_BUNDLE_ID',
                'errors' => ['bundle id not found'],
                'debug_id' => "",
            ];
            $this->response->redirect($this->url->link('account/charge/failed', '', true,0,$this->withStoreCodeUrl));
        }
        $bundle = $whmcsResult->products->product[0];
        return $bundle;
    }
    
    public function bundleAmount($bundle = null)
    {
        if (!$bundle) return 0;
        // $amount = $bundle->pricing->USD->annually;
        $amount = max(array_values((array)$bundle->pricing->USD));
        return $amount;
    }
}
