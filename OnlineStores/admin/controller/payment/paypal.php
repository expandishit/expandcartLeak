<?php
use ExpandCart\Foundation\Filesystem;

class ControllerPaymentPaypal extends Controller {

    private $expandClientId   = PAYPAL_MERCHANT_CLIENTID,//'Acqck9HX5JN36kU-O63Opr1mrtcZib6i83Q3-GRM5U4a7_YY5AqUr8oftBSgExDcAfMiEuIF4VegrLW7',
            $expandSecret 	  = PAYPAL_MERCHANT_SECRET, //'ECOpqQK1udkc6_TB4pksIq5JkZFCPyk3BWYdMY3-_msgtu5yrKGrbQOqavW-VuGBy-fndFpEuXXhkuo5',
			$expandMerchantId = PAYPAL_MERCHANT_MERCHANTID, //'6SMXGVHE3FCF4',
            $baseUrl 		  = PAYPAL_MERCHANT_BASEURL; //'https://api.sandbox.paypal.com';


    private $error 			  = array();


    /* ===================================================================== */

    public function index() 
	{

        $this->load->model("payment/paypal");
        $this->load->model("setting/setting");

        $this->load->language('payment/paypal');
        $this->document->setTitle($this->language->get('heading_title'));

        $approvedStatus = (!empty($this->config->get("paypal_email"))) ? true : false;
        $isPaypalZero	= (!empty($this->config->get("isPaypalZero"))) ? true : false;


        $this->data["paypal_status"] = $this->config->get("paypal_status");

        $this->data["paypal_account_connected"] = (!empty($this->config->get("paypal_account_connected"))) ? "1" : "";

		//add paypal record to old merchants to  receive webhooks on ectools 
		//from paypal partner account 
		if(
		   (int)$this->config->get("paypal_account_connected")
		   && !$this->config->has('paypal_account_id')
		   )
		{
			$paypal_account_id = $this->model_payment_paypal->addAccountRecord([
												'email' 	   => $this->config->get("paypal_email"),
												'merchant_id'  => $this->config->get("paypal_merchant_id")
											]);
															
			$this->model_setting_setting->insertUpdateSetting('paypal',[ 'paypal_account_id' =>$paypal_account_id]);				
		}

        
		if ($approvedStatus && !$isPaypalZero) {

            $this->load->model('setting/setting');
            $this->load->model('localisation/order_status');

            $this->data['paypal_order_status_id'] = $this->config->get('paypal_order_status_id');
            $this->data['paypal_order_status_failed'] = $this->config->get('paypal_order_status_failed');
            $this->data["paypal_order_status_shipping"] = $this->config->get("paypal_order_status_shipping");
            $this->data["paypal_order_status_pending"] = $this->config->get("paypal_order_status_pending");
            $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
            $this->data["paypal_view_checkout"] = $this->config->get("paypal_view_checkout");

            /* ================= PayPal Buttons Style ================== */

            $this->data['buttons_color'] = ['blue', 'silver', 'gold', 'white', 'black'];

            $this->data['selected_color'] = $this->config->get('paypal_button_color');

            $this->data["paypal_addOnProduct"] = $this->config->get("paypal_addOnProduct");

            $this->data["paypal_email"] = $this->config->get("paypal_email");

            $this->data["paypal_payer_id"] = $this->config->get("paypal_merchant_id");

            $this->data["isApprovedStatus"] = true;

            $this->load->model('setting/extension');

        } else if ($isPaypalZero) {

            $this->data["isPaypalZero"] = true;

            $this->data["paypalZeroLink"] = $this->url->link("payment/paypal/hasAgreedToLinkBankAccount");

        } else {

            $errors = [];

            if ($mailError = $this->config->get("paypal_mail_error")) {
				
				$errors[] = $this->language->get($mailError);
            }

            if ($receivableError = $this->config->get("paypal_receivable_error")) {

                $errors[] = $this->language->get($receivableError);
            }

            if ($oauthError = $this->config->get("paypal_oauth_error")) {

                $errors[] = $this->language->get($oauthError);
            }

            if (!empty($this->config->get("paypal_send_only_error"))) {
                
				$errors[] = $this->language->get("entry_paypal_sendOnly");
            }


            if (count($errors) > 0)
                $this->data["paypalErrorMessage"] = $errors;

            
			$this->data['addSallerUrl'] = $this->createAuthLink();
        }

        
		// ================== breadcrumbs =========================

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/paypal', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/paypal', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('payment/paypal', 'token=' . $this->session->data['token'], 'SSL');

        // ================== /breadcrumbs =========================

        $this->template = 'payment/paypal.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /* ===================================================================== */

    public function checkForShipping() 
	{

        $extensions = $this->model_setting_extension->getInstalled('shipping');

        foreach ($extensions as $key => $value) {

            $settings = $this->config->get($value);
            if ($settings && is_array($settings) == true) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($value . '_status');
            }

            if (!$status) {
                unset($extensions[$key]);
            }
        }

        return count($extensions);
    }

    public function storePayPalData() 
	{

        $errors = [];

        $this->load->language("payment/paypal");

        if ( ! $this->user->hasPermission('modify', 'payment/paypal') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }
//
//        if($this->request->post["paypal_order_status_pending"] == -1 ) {
//
//            $this->error["paypal_order_status_pending"] = $this->language->get("pending_status_error") ;
//
//        }   if($this->request->post["paypal_order_status_shipping"] == -1 ) {
//
//            $this->error['paypal_order_status_shipping'] = $this->language->get("shipping_status_error");
//
       // }
        if($this->request->post["paypal_order_status_id"] == -1 ) {

            $this->error['paypal_order_status_id'] = $this->language->get("complete_status_error");

        }   if($this->request->post["paypal_order_status_failed"] == -1 ) {

            $this->error['paypal_order_status_failed'] = $this->language->get("failed_status_error");
        }
//
        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
//
        if(count($this->error) > 0) {
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->load->model('setting/setting');

        $this->model_setting_setting->checkIfExtensionIsExists('payment', 'paypal', true);

        $this->model_setting_setting->insertUpdateSetting('paypal', $this->request->post);
            $this->tracking->updateGuideValue('PAYMENT');
        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

        $result_json['success'] = '1';

        $this->response->setOutput(json_encode($result_json));

        return;
    }

    /* ===================================================================== */

    public function checkIfRegistered() 
	{

        $IsApprovedStatus = (!empty($this->config->get('paypal_merchant_id'))) ? true : false;

        $json = ['IsApproved' => $IsApprovedStatus];
        $this->response->setOutput(json_encode($json));
    }

    /* ===================================================================== */
    public function createAuthLink() 
	{

        $token = $this->createToken()['access_token'];

        $paypalPartnerArray = [
			"tracking_id"=> STORECODE,
            "operations" => [
                [
                    "operation" => "API_INTEGRATION",
                    "api_integration_preference" => [
                        "rest_api_integration" => [
                            "integration_method" => "PAYPAL",
                            "integration_type" => "THIRD_PARTY",
                            "third_party_details" => [
                                "features" => [
                                    "PAYMENT",
                                    "REFUND",
                                    "PARTNER_FEE",
                                    "ACCESS_MERCHANT_INFORMATION",
                                    "TRACKING_SHIPMENT_READWRITE"
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            "partner_config_override" => [
                "partner_logo_url" => "https://cdn.expandcart.com/wp-content/uploads/2015/12/LogoEC.png",
                "return_url" => (string) $this->url->link('payment/paypal/addSaller', '', 'SSL'),
                "return_url_description" => "the url to return the merchant after the paypal onboarding process."
            ],
            "products" => [
                'EXPRESS_CHECKOUT'
            ],
            "legal_consents" => [
                [
                    "type" => "SHARE_DATA_CONSENT",
                    "granted" => true
                ]
            ]
        ];

        $bytes = time() . rand(10, 1000);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . '/v2/customer/partner-referrals',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($paypalPartnerArray),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $token",
                "PayPal-Request-Id: $bytes"
            ),
        ));

        $actionUrlArray = json_decode(curl_exec($curl), true);
        curl_close($curl);


        $actionUrl = $actionUrlArray['links'][1]['href'];

        return $actionUrl;
    }

    /* ===================================================================== */


    public function addSaller() 
	{

        $token = $this->createToken()['access_token'];

		//depreciated here we cant register receiving all webhooks at all stores 
		//will replaced with webhooks receiving at ectools 
        //$this->signUpToWebHook($token);

        $payPalMerchantId = $this->request->get['merchantIdInPayPal'];
		
		$this->initializer([
				'payment/paypal'
				]);
				
        $merchantData = $this->paypal->checkMerchantstatus($payPalMerchantId, $token);
        $checkResponse = $this->validateResponse($merchantData);

		
        $this->load->model('setting/setting');

        if ($checkResponse['status'] == 'ok') {

            $defaultConfig = $this->initPaypalFields($merchantData);

            $deleteConfigs = ["paypal_mail_error", "paypal_receivable_error", "paypal_send_only_error", "paypal_oauth_error"];

        } else {
            
			$defaultConfig["paypal_status"]		 = 0;
			$defaultConfig['paypal_merchant_id'] = $merchantData['merchant_id'];
					
			$deleteConfigs = [];
					
			if($merchantData["primary_email_confirmed"] != 1) {
				$defaultConfig["paypal_mail_error"] = $checkResponse["errors"]["mail_error"];
			} else {
				$deleteConfigs[] = 'paypal_mail_error';
			}

			if($merchantData["payments_receivable"] != 1) {
				$defaultConfig["paypal_receivable_error"] = $checkResponse["errors"]["receivable_error"];
			} else {
				$deleteConfigs[] = 'paypal_receivable_error';
			}
					
			if(empty($merchantData["oauth_integrations"])) {
				$defaultConfig["paypal_oauth_error"] = $checkResponse["errors"]["oauth_error"];
			} else {
				$deleteConfigs[] = 'paypal_oauth_error';
			} 
					
			if(in_array("paypal_send_only_error", array_keys($checkResponse["errors"]))) {
				$defaultConfig["paypal_send_only_error"] = $checkResponse["errors"]["paypal_send_only_error"];
			}else {
				$deleteConfigs[] = 'paypal_oauth_error';
			} 	

        }
		
		if(!empty($deleteConfigs)){
			$this->model_setting_setting->deleteByKeys($deleteConfigs);
		}
		
		if(!$this->config->has('paypal_account_id')){
					
			$defaultConfig['paypal_account_id'] = $this->paypal->addAccountRecord([
															 'email' 		=> $merchantData["primary_email"],
															 'merchant_id'  => $merchantData["merchant_id"]
															]);
															
		}
	
		
		 $this->model_setting_setting->insertUpdateSetting('paypal', $defaultConfig);

        $this->response->redirect($this->url->link('extension/payment/activate?code=paypal&activated=1&payment_company=1', '', true));
    }

    public function initPaypalFields($responseArray) 
	{

		$this->initializer([
				'payment/paypal'
				]);
				
        $data['paypal_account_connected'] = 1;
        $data["paypal_status"] = 0;
        $data['paypal_merchant_id'] = $responseArray['merchant_id'];
        $data['paypal_product'] = $responseArray['products'][0]['name'];
        $data['paypal_merchant_client_id'] = $responseArray['oauth_integrations'][0]['oauth_third_party'][0]['merchant_client_id'];
        $data["paypal_email"] = $responseArray["primary_email"];
        $data["paypal_addOnProduct"] = "1";
        $data["paypal_view_checkout"] = "1";
        
		if($this->paypal->checkIfPaypalZeroCountries($responseArray["country"])) {

            $data["isPaypalZero"] = 1;
            $data["paypal_status"] = 0;

        }

        return $data;
    }

	//depreciated here | moved to model 
    public function checkMerchantstatus($payPalMerchantId, $token) 
	{

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . "/v1/customer/partners/{$this->expandMerchantId}/merchant-integrations/{$payPalMerchantId}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $token"
            ),
        ));

        $responseArray = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $responseArray;
    }

    /* ===================================================================== */

    public function hasAgreedToLinkBankAccount() 
	{

        $this->load->model('setting/setting');

        $this->model_setting_setting->deleteByKeys([
													"isPaypalZero",
													"paypal_mail_error",
													"paypal_receivable_error",
													"paypal_send_only_error",
													"paypal_oauth_error"
													]);
												
        $this->response->redirect($this->url->link('extension/payment/activate?code=paypal&activated=1&payment_company=1', '', true));

    }

    private function validateResponse($responseArray) 
	{

        unset($this->session->data['paypalErrorMessage']);

        $errors = [];

        if($this->checkSendOnlyCountries($responseArray["country"])) {
            $errors["paypal_send_only_error"] = 'entry_paypal_sendOnly';
        }

        if ($responseArray['primary_email_confirmed'] != 1) {
            $errors["mail_error"] = 'error_email';
        }

        if ($responseArray['payments_receivable'] != 1) {
            $errors["receivable_error"] = 'error_payments_receivable';
        }

        if (empty($responseArray['oauth_integrations'])) {
            $errors["oauth_error"] ='error_auth';
        }


        $validationStatus = (empty($errors)) ? 'ok' : 'errors';

        return ['status' => $validationStatus, 'errors' => $errors];
    }

    /* ===================================================================== */
	//any transaction done with PayPal is need token to auth
    private function createToken() 
	{ 
        $this->load->model('payment/paypal');

        return $this->model_payment_paypal->createTokent();
    }

    private function checkSendOnlyCountries($merchantCountry) 
	{

        $blockedCountries = array(
            "AO" =>	"ANGOLA",
            "BF" =>	"BURKINA FASO",
            "BI" =>	"BURUNDI",
            "BJ" =>	 "BENIN",
            "CD" =>	  "CONGO",
            "CI" =>	"COTE DIVOIRE",
            "CK" =>	 "COOK ISLANDS",
            "CM" =>	"CAMEROON",
            "CV" =>	"CAPE VERDE",
            "DJ" =>	"DJIBOUTI",
            "ER" =>	 "ERITREA",
            "ET" =>	 "ETHIOPIA",
            "GA" =>	 "GABON",
            "GM" =>  "GAMBIA",
            "GN" =>	 "GUINEA",
            "GW" =>	 "GUINEA-BISSAU",
            "KM" =>	 "COMOROS",
            "MG" =>	 "MADAGASCAR",
            "ML" =>	 "MALI",
            "MR" =>	 "MAURITANIA",
            "NA" =>	 "NAMIBIA",
            "NE" =>	 "NIGER",
            "NG" =>	 "NIGERIA",
            "RW" =>	 "RWANDA",
            "SH" =>	 "ST. HELENA",
            "SL" =>	 "SIERRA LEONE",
            "SO" =>   "SOMALIA",
            "ST" =>	"SAO TOME",
            "SZ" =>	 "SWAZILAND",
            "TD" =>  "CHAD",
            "TG" =>   "TOGO",
            "TZ" =>	 "TANZANIA",
            "UG" =>	 "UGANDA",
            "ZM" =>   "ZAMBIA",
            "ZW" =>	 "ZIMBABWE",
            "AZ" =>	 "AZERBAIJAN",
            "KG" =>	 "KYRGYZSTAN",
            "TJ" =>	"TAJIKISTAN",
            "TM" =>	 "TURKMENISTAN",
            "TN" =>	 "TUNISIA",
            "YE" =>	 "YEMEN"
        );

        return in_array($merchantCountry, array_keys($blockedCountries));
    }

	public function uninstall()
	{
		$paypal_account_id = $this->config->get('paypal_account_id');
		$this->paypal->updateAccountStatus($paypal_account_id,"uninstalled");
	}
	
    /* ===================================================================== */
	//depreciated here 
	/*
	 *  No need to register webhook every merchant registration 
	 * 
	 */
    private function signUpToWebHook($token) {

        $url = HTTPS_CATALOG . "index.php?route=payment/paypal/callBack";

        $webHooksArray['url'] = $url;

        $webHooksArray['event_types'] = [
            ['name' => 'MERCHANT.ONBOARDING.COMPLETED'],
            ['name' => 'MERCHANT.PARTNER-CONSENT.REVOKED'],
            ['name' => 'CHECKOUT.ORDER.APPROVED'],
            ['name' => 'PAYMENT.CAPTURE.COMPLETED'],
            ['name' => 'PAYMENT.CAPTURE.DENIED'],
            ['name' => 'PAYMENT.CAPTURE.REFUNDED'],
        ];

        $bytes = time() . rand(10, 1000);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . '/v1/notifications/webhooks',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($webHooksArray),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $token",
                "PayPal-Request-Id: $bytes"
            ),
        ));

        $responseJson = curl_exec($curl);

        curl_close($curl);

        $responseArray = json_decode($responseJson, true);

        if (!empty($responseArray['id'])) {

            $this->load->model('setting/setting');

            $this->model_setting_setting->insertUpdateSetting('paypal', ['paypal_webhook_id' => $responseArray['id']]);
        }
    }

    /* ===================================================================== */
}
