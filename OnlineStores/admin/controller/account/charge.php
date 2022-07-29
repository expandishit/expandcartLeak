<?php

class ControllerAccountCharge extends Controller
{
    protected $withStoreCodeUrl = false;

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);
        if (strpos($_SERVER['SERVER_NAME'],strtolower(STORECODE)) !== false && strpos($_SERVER['SERVER_NAME'], 'expandcart') !== false) {
            $this->withStoreCodeUrl=true;
        }

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }
    }

    public function index()
    {

        $this->initializer(['account/invoice','account/account']);

        //store current plan id in session to compare in case merchant update or downgrade the plan
        $this->session->data['current_plan'] = PRODUCTID;

        // if (!isset(WHITELIST_STORES[STORECODE]) || !WHITELIST_STORES[STORECODE]){
        //     $this->response->redirect($this->url->link('account/charge', '', true));
        // }

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
        $pricingJSON = $pricingJSONAll[$pricingCurrency];

        if ($this->language->get('direction') == 'rtl') {
            $langcode = "ar";
        }

        $pricingJSON = array_merge($pricingJSON, $pricingJSON["strings"][$langcode]);

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

        $plans_features = json_decode(file_get_contents('json/plans_features_description.json'),true);
        foreach ($plans_features as $features_group_key => $features) {
            $this->data[$features_group_key] = $features;
        }

        $this->data['trial_plan_id'] =$this->plan_id;

        $this->data['withStoreCodeUrl']= $this->withStoreCodeUrl;

        $trials = $this->model_plan_trial->getAllTrials();
        $this->data['trials']= $trials;

        $this->template = 'account/charge/plans-v3.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

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

        if ($_GET['ajax'] == 1){

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

    public function downgrade()
    {
        if ( $this->request->server['REQUEST_METHOD'] == 'POST' ) {

            $currency = $_POST['currency'];
            $planId = $_POST['plan_id'];
            $planType = $_POST['plan_type'];
            if ($_POST['plan_type']=="yearly"){
                $planType = "annually";
            }

            $this->initializer(['account/invoice', 'account/account']);
            $store_account = $this->account->selectByStoreCode(STORECODE);
            $serviceid = $store_account['service_id'];
            $payment_method = $store_account['payment_method'];

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

            $amount;
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
                $this->invoice->update_product([
                    'payment_method' => $payment_method,
                    'serviceid' => $serviceid,
                    'first_payment_amount' => $amount,
                    'recurring_amount' => $amount,
                    'product_id' => $planId,
                    'billing_cycle' => $planType
                ]);
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
                $invoice = $this->invoice->update_product([
                    'payment_method' => $paymentMethod,
                    'serviceid' => $serviceid,
                    'first_payment_amount' => "0.00",
                    'recurring_amount' => "0.00",
                    'product_id' => '3',
                    'billing_cycle' => "Free Account"
                ]);
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

    public function tryPLan()
    {

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

    function trackTrailEvents($event_name, $meta=[])
    {
        // send mixpanel  trail event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent($event_name,$meta);

        // send amplitude trail event
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent($event_name,$meta);
    }
}
