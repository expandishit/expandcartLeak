<?php


class ControllerSellerAccountSubscriptions extends ControllerSellerAccount
{
    private $model;

    private function init()
    {
        $this->load->model('multiseller/subscriptions');

        $this->model = $this->model_multiseller_subscriptions;
    }

    public function index()
    {

        $this->document->setTitle($this->language->get('ms_account_subscriptions_heading'));

        $this->document->addStyle('expandish/view/theme/default/template/multiseller/stylesheet/subscriptions.css');
//        $this->document->addScript('expandish/view/javascript/multimerch/account-seller-profile.js');
        $this->document->addScript('expandish/view/javascript/multimerch/plupload/plupload.full.js');
        $this->document->addScript('expandish/view/javascript/multimerch/plupload/jquery.plupload.queue/jquery.plupload.queue.js');

        // colorbox
        $this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox.js');
        $this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');

        $this->load->model('localisation/country');
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        $seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());

        if ($this->MsLoader->MsSeller->getSubscriptionPlan() != 0) {
            $this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
        }

        if (
            !$this->config->get('msconf_enable_subscriptions_plans_system')
            || $this->config->get('msconf_enable_subscriptions_plans_system') == 0
        ) {
            $this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
        }

        $this->init();

        $this->data['subscriptions']['plans'] = $this->model->getPlans();

        $this->data['subscriptions']['payment_method'] = $this->config->get('msconf_subscriptions_payment_method');
        $this->data['subscriptions']['undefined_error_message'] = $this->language->get('undefined_error_message');
        $this->data['subscriptions']['bank_details'] = $this->config->get('msconf_subscriptions_bank_details');

        $this->data['currency'] = $this->config->get('config_currency');
        $this->data['business'] = $this->config->get('msconf_subscriptions_paypal_email');

        if($this->config->get('msconf_subscriptions_mastercard_accesscode') == '' || $this->config->get('msconf_subscriptions_mastercard_merchant') == '' || $this->config->get('msconf_subscriptions_mastercard_secret') == '' ){
            $mastercard = 0;
        }else{
            $this->monthesYears();
            $this->data['mastercard_action'] = $this->url->link('seller/account-subscriptions/mastercardProccess');
            $this->data['errorurl'] = $this->url->link('seller/account-subscriptions');
            $mastercard = 1;
        }

        $this->data['payment_methods'] = [
            'paypal'       => $this->config->get('msconf_subscriptions_paypal_email'),
            'bank'         => $this->config->get('msconf_subscriptions_bank_details'),
            'mastercard'   => $mastercard 
        ];

        $this->data['location'] = $this->url->link('seller/account-subscriptions/updateSeller');
        

        $this->data['ms_subscriptions_error'] = '';
        if(isset($this->session->data['ms_subscriptions_error'])){
            $this->data['ms_subscriptions_error'] = $this->session->data['ms_subscriptions_error'];
            unset($this->session->data['ms_subscriptions_error']);
        }

        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-subscriptions');
        $this->response->setOutput($this->render());
    }

    public function updateSeller()
    {

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $sellerData = $this->request->post['seller'];

            $this->initializer([
                'multiseller/subscriptions'
            ]);
            $encoded = $this->subscriptions->encrypt(http_build_query($sellerData));

            $json = [
                'error' => null,
                'redirect' => $this->url->link('seller/account-subscriptions/confirmPayment&seller=' . urlencode(base64_encode($encoded)))
            ];

            return $this->response->setOutput(json_encode($json));
        } else {
            $json['errors'] = [
                'invalid request'
            ];
            return $this->response->setOutput(json_encode($json));
        }
    }

    public function confirmPayment()
    {
        $this->initializer([
            'multiseller/subscriptions'
        ]);

        $seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());

        if (empty($seller)) {
            $json['errors'] = [
                'no user exists'
            ];
            return $this->response->setOutput(json_encode($json));
        }

        $decoded = $this->subscriptions->decrypt(base64_decode(urldecode($this->request->get['seller'])));

        parse_str($decoded, $sellerData);

        $this->MsLoader->MsSeller->updateSellerPlan($this->customer->getId(), $sellerData);

        $seller_validation = $this->config->get("msconf_seller_validation");

        if (isset($sellerData['payment_method']) && $sellerData['payment_method'] != 2){
            // check if admin choose no validation for seller approval
            if($seller_validation == 1){
                $this->MsLoader->MsSeller->updateSellerStatus($this->customer->getId(), MsSeller::STATUS_ACTIVE);
            }
        }

        $this->subscriptions->newSellerPayment($this->customer->getId(), $sellerData);
        $affiliateAppIsActive = (
            \Extension::isInstalled('affiliates') && $this->config->get('affiliates')['status']
        );
        if($sellerData['newSeller'] && $affiliateAppIsActive){
            $this->MsLoader->MsSeller->applySellerAffiliateCommission($sellerData['price']);
        }

        if ($sellerData['payment_method'] == 2 || $seller_validation == 3){
            $this->session->data['success'] = $this->language->get('ms_seller_payment_success') . $this->language->get('ms_account_status_tobeapproved');
            $this->redirect($this->url->link('account/account'));
        }
        $this->redirect($this->url->link('seller/account-dashboard/'));
    }

    public function expiredPlan()
    {
        $this->document->setTitle($this->language->get('ms_account_subscriptions_heading'));

        $subscriptions = $this->load->model('multiseller/subscriptions', ['return' => true]);

        $payment = $subscriptions->getLatestPayment($this->customer->getId());

        $plan = $subscriptions->getPlanById($payment['plan_id']);

        $formats = ['1' => 'days', '2' => 'month', '3' => 'year'];

        if (strtotime(date('Y-m-d h:i:s', strtotime($payment['lastPaymentDate'] . ' +' . $plan['period'] . ' '.$formats[$plan['format']]))) >= strtotime(date('Y-m-d h:i:s', time()))) {
            $this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
        }
        
        $this->data['updatePlan'] = $this->url->link('seller/account-subscriptions/updatePlan&expired=1');

        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('expired-subscriptions');
        $this->response->setOutput($this->render());
    }

    public function updatePlan()
    {
        $this->document->setTitle($this->language->get('ms_account_subscriptions_heading'));

        $this->document->addStyle('expandish/view/theme/default/template/multiseller/stylesheet/subscriptions.css');

        $seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());

        $this->data['current_plan'] = $this->MsLoader->MsSeller->getSubscriptionPlan();

        if (
            !$this->config->get('msconf_enable_subscriptions_plans_system')
            || $this->config->get('msconf_enable_subscriptions_plans_system') == 0
        ) {
            $this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
        }

        $this->init();

        $this->load->model('multiseller/subscriptions');
        $this->data['subscriptions']['plans'] = $this->model_multiseller_subscriptions->getPlans();

        $this->data['subscriptions']['payment_method'] = $this->config->get('msconf_subscriptions_payment_method');
        $this->data['subscriptions']['undefined_error_message'] = $this->language->get('undefined_error_message');
        $this->data['subscriptions']['bank_details'] = $this->config->get('msconf_subscriptions_bank_details');

        $this->data['currency'] = $this->config->get('config_currency');
        $this->data['business'] = $this->config->get('msconf_subscriptions_paypal_email');

        if($this->config->get('msconf_subscriptions_mastercard_accesscode') == '' || $this->config->get('msconf_subscriptions_mastercard_merchant') == '' || $this->config->get('msconf_subscriptions_mastercard_secret') == '' ){
            $mastercard = 0;
        }else{
            $this->monthesYears();
            $this->data['mastercard_action'] = $this->url->link('seller/account-subscriptions/mastercardProccess');
            $this->data['errorurl'] = $this->url->link('seller/account-subscriptions/updatePlan');
            $mastercard = 1;
        }

        $this->data['payment_methods'] = [
            'paypal' => $this->config->get('msconf_subscriptions_paypal_email'),
            'bank' => $this->config->get('msconf_subscriptions_bank_details'),
            'mastercard'   => $mastercard
        ];

        $this->data['location'] = $this->url->link('seller/account-subscriptions/updateSeller');
        $this->data['expired']  = isset($this->request->get['expired'])?1:0;

        $this->data['ms_subscriptions_error'] = '';
        if(isset($this->session->data['ms_subscriptions_error'])){
            $this->data['ms_subscriptions_error'] = $this->session->data['ms_subscriptions_error'];
            unset($this->session->data['ms_subscriptions_error']);
        }

        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('subscriptions-update');
        $this->response->setOutput($this->render());
    }

    public function mastercardProccess()
    {
        $url = 'https://migs.mastercard.com.au/vpcdps'; 
        $amount=$this->currency->format($this->request->post['amount_1'], $this->config->get('config_currency'), 1.00000, false);
        $amount=round($amount*100,2);

        $ref = time();
        $callback  = $this->request->post['return'];
        $errorurl  = $this->request->post['errorurl'];

        $request_fields = array(
                        'vpc_Version'           => "1", 
                        'vpc_Command'           => "pay",
                        'vpc_AccessCode'        => $this->config->get('msconf_subscriptions_mastercard_accesscode'),
                        'vpc_MerchTxnRef'       => $ref,
                        'vpc_Merchant'          => $this->config->get('msconf_subscriptions_mastercard_merchant'),                                               

                        'vpc_OrderInfo'         => $ref,
                        'vpc_Amount'            => $amount,  // Force amount into cents. 
                        'vpc_CardNum'           =>str_replace(' ', '', $this->request->post['vpc_card']),
                        'vpc_cardExp'           =>substr($this->request->post['vpc_year'],2,4).$this->request->post['vpc_month'],
                        'vpc_CardSecurityCode'  =>$this->request->post['vpc_cvv'],
                        'vpc_SecureHash'        => $this->config->get('msconf_subscriptions_mastercard_secret')
        );

        ksort($request_fields);                 
        $md5HashData  = "";     
        foreach( $request_fields as $k => $v ) { $md5HashData .= $v; }
        $post="";
        if (!empty($request_fields)) {
            foreach($request_fields AS $key => $val){
                $post .= urlencode($key) . "=" . urlencode($val) . "&";
            }
            $post_data = substr($post, 0, -1);
        }else {
            $post_data = '';
        }
        
        if(!function_exists('curl_init'))   $json['error'] ='CURL extension is not loaded.';
                
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_PORT, 443);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $response = curl_exec($curl);
        //var_dump($response); die("@");
        
        $json = array();
        
        if (curl_error($curl)) {
            curl_close($curl);
            $this->session->data['ms_subscriptions_error'] = 'MIGS API CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);
            $this->redirect($errorurl); 
        } elseif ($response) {
        
            $output = explode(chr (28), $response); // The ASCII field seperator character is the delimiter
            if( is_array($output)   )    
            foreach ($output as $key_value) {     
                 $value = explode("&", $key_value);          
                 foreach ($value as $_key  => $_value) {
                 
                  $v = explode("=", $_value);
                  $result[ $v[0] ] = str_replace("+", " " , $v[1] );
                
                }

            }
            
            curl_close($curl);

            if (isset($result['vpc_TxnResponseCode']) && $result['vpc_TxnResponseCode'] == '0') {
                $this->redirect($callback);
            } else {
                $this->session->data['ms_subscriptions_error'] = urldecode($result['vpc_Message']);
                $this->redirect($errorurl);
            }
        } else {
            curl_close($curl);
            $this->session->data['ms_subscriptions_error'] = 'Empty Gateway Response';
            $this->redirect($errorurl);
        }
    }

    protected function monthesYears(){
        $this->data['months'] = array();
        for ($i = 1; $i <= 12; $i++) {
            $this->data['months'][] = array(
                'text'  => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)), 
                'value' => sprintf('%02d', $i)
            );
        }
        $today = getdate();
        $this->data['years'] = array();
        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $this->data['years'][] = array(
                'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
                'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)) 
            );
        }
    }
}
