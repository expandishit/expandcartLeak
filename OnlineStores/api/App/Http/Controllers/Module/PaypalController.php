<?php

namespace Api\Http\Controllers\Module;

use Api\Http\Controllers\Controller;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class PaypalController extends Controller
{
 
	private $config, $setting, $registry, $paypal;
	private $errors = [];
	 
	private $zero_countries = ["EG", "DZ", "SC", "LS", "MW"];
	
	protected $logger ;
	
    public function __construct(ContainerInterface $container){
		
        parent::__construct($container);

        $this->config 	= $this->container['config'];
        $this->setting 	= $this->container['setting'];
		$this->registry = $this->container['registry'];
        $this->paypal   = $this->container['paypal'];
		
		$this->logger   = new \Log('paypal_api');
    }

	public function webhook(Request $request, Response $response) {
		
		$body = file_get_contents('php://input');
        
		$this->logger->write("[receive-webhook] payload : ".$body . "\n headers : ".json_encode(getallheaders()));
		
		if(!$this->_validateSignature($body)){
			//return $response->withJson(['status' => 'ERR' , 'error'=> 'INVALID_SIGNATURE']);
		}
		
		
		$data  = $request->getParsedBody();
		$merchant_webhook_prefix = 'MERCHANT';
		$customer_merchant_webhook_prefix = 'CUSTOMER.MERCHANT';
		$payment_webhook_prefix  = 'PAYMENT';
		
		//merchant webhooks
		if(substr($data["event_type"], 0, strlen($merchant_webhook_prefix)) === $merchant_webhook_prefix){
			$result = $this->_merchantWebhooks($data);
		}
		//merchant other events webhooks
		else if(substr($data["event_type"], 0, strlen($customer_merchant_webhook_prefix)) === $customer_merchant_webhook_prefix){
			$result = $this->_merchantWebhooks($data);
		}
		//payment webhooks 
		else if(substr($data["event_type"], 0, strlen($payment_webhook_prefix)) === $payment_webhook_prefix){
			$result = $this->_paymentWebhooks($data);
		}else {
			//TO:DO | other unhandled event received response 
			return $response->withJson([]);
		}
		
		return $response->withJson($result);
    }
	
	private function _paymentWebhooks($data){
		
		$paypal_merchant_id = $this->config->get('paypal_merchant_id');
        $paypal_email 		= $this->config->get("paypal_email");
        $billing_email 		= BILLING_DETAILS_EMAIL;
		
		$handled_payment_events = [
									'PAYMENT.CAPTURE.COMPLETED',
									'PAYMENT.CAPTURE.DENIED'
								];

										
		if (! in_array($data["event_type"],$handled_payment_events)) {
			$errors = ['we currently handle the following payment events only : ' . implode(",",$handled_payment_events)];
			return [
					'status' => 'ERR' ,
					'error'  => 'UNHANDLED_PAYMENT_EVENT',
					'errors' => $errors
					];
		}
	
		$order_id = $data["resource"]["supplementary_data"]["related_ids"]["order_id"]??"";

		if(empty($order_id))
		{
			return [
					'status' => 'ERR' ,
					'error'  => 'INVALID_RESOURCE_ID',
					'errors' => ['no resource id provided ']
					];
		}
		
		$paypal_order = $this->paypal->paypalOrderData($order_id);
		
		if(isset($paypal_order["name"]) && $paypal_order["name"] == "RESOURCE_NOT_FOUND")
		{
			return [
					'status' => 'ERR' ,
					'error'  => 'INVALID_RESOURCE_ID',
					'errors' => ['invalid resource id provided '],
					'debug'  => $paypal_order
					];
		}			
		//order id added at create order method 
		$merchant_order_id 	= explode("_", $paypal_order["purchase_units"][0]["reference_id"]?? "")[0]??"";
		$order_data = $this->paypal->orderData($merchant_order_id);


		//if order is already confirmed ignore this handling 
		//this mean that its already handled at the callback 
		if($order_data["order_status_id"] == $this->config->get('paypal_order_status_id')){
			return [
					'status' => 'ERR' ,
					'error'  => 'ORDER_ALREADY_CONFIRMED',
					'errors' => []
					];
		}
		
		$transaction_id 	= $paypal_order['purchase_units'][0]['payments']['captures'][0]['id']??"";
		$transaction_status = $paypal_order['purchase_units'][0]['payments']['captures'][0]['status']??"";
		
		$transactions = $this->paypal->selectTransactionsByFilters(['transaction_id'=>$transaction_id]);
		$transaction  = $transactions[0]??[];
		$handled_statuses = ["COMPLETED","DECLINED"];
		
		//curently we are handling the complete & decliend
		if(!in_array($transaction_status,$handled_statuses)){
			$error = 'we currently handle the following statuses only : ';
			$error .= implode(",",$handled_statuses).' | status : '.$transaction_status ;
			$errors = [$error];
			
			return [
					'status' => 'ERR' ,
					'error'  => 'UNHANDLED_PAYMENT_STATUS', 
					'errors' => $errors,
					'debug'  => [
								'order_data' => $paypal_order,
								'paypal_order' => $paypal_order,
								]
					];
		}
		
		if($transaction_status == 'COMPLETED'){
			
			//
			$status_to = $this->config->get('paypal_order_status_id');
			$status    = 'Success';
			
			$this->paypal->track_transaction([
					'payment_method' => 'PayPal',
					'amount' 		 => $paypal_order['purchase_units'][0]['amount']['value'],
					'currency' 		 => $paypal_order['purchase_units'][0]['amount']['currency_code'],
					]);
		}
		//else if($transaction_status == 'DECLINED'){		
		else {
			$status_to = $this->config->get('paypal_order_status_failed');
			$status='Failed';
		}
		
		//----- update order & transaction statuses 
		
		$this->paypal->updateOrderStatus($merchant_order_id,['order_status_id' =>$status_to]);
		
		if(!empty($transaction)){
			$transaction["status"] = $status;
			$this->paypal->updateTransaction($transaction["id"],$transaction);
		}else{
			//transaction not exists so we will add it 
			$this->paypal->insertTransaction([
            'order_id' 			=> $merchant_order_id,
            'transaction_id' 	=> $paypal_order['purchase_units'][0]['payments']['captures'][0]['id'] ?? "",
            'payment_gateway_id'=> $this->paypal->getPaymentMethodId(),
            'payment_method' 	=> 'PayPal',
            'status' 			=> $status,
            'amount' 			=> $paypal_order['purchase_units'][0]['amount']['value'] ?? "",
            'currency' 			=> $order_data["currency_code"],
            'details' 			=> json_encode($paypal_order , JSON_INVALID_UTF8_IGNORE),
			]);
		}
		
		return ['status' => 'success'];
	}

	private function _merchantWebhooks($data){
		
		$paypalMerchantId   = $this->config->get('paypal_merchant_id');
        $paypalEmail 		= $this->config->get("paypal_email");
        $billingEmail 		= BILLING_DETAILS_EMAIL;
		
        $merchantId 		= $data["resource"]["merchant_id"];
        $merchantData 		= $this->paypal->checkMerchantstatus($merchantId);

		
		//validate the endpoint errors 
		if(isset($merchantData["error"])){
			return [
					'status' => 'ERR' ,
					'error'=> 'PROVIDER_ERROR',
					'response_debug' => $merchantData //used for internal debuging 
					];
		}
		
		//validate that this webhook related to the existed | installed paypal account
		if (!in_array($merchantData["primary_email"] ,[$paypalEmail,$billingEmail])) {
			return [
					'status' => 'ERR' ,
					'error'=> 'EMAIL_NOT_BELONG_TO_THIS_STORE',
					'allowed_emails' => [$paypalEmail,$billingEmail]
					];
		}
		
			
			
		if ($data["event_type"] == "MERCHANT.PARTNER-CONSENT.REVOKED") {
            
			$this->paypal->uninstall();
			
        } else if (
					$data["event_type"] == "MERCHANT.ONBOARDING.COMPLETED" 
				||  $data["event_type"] == "CUSTOMER.MERCHANT-INTEGRATION.SELLER-EMAIL-CONFIRMED" 
				||  $data["event_type"] == "CUSTOMER.MERCHANT-INTEGRATION.SELLER-CONSENT-GRANTED" 
				)
		{

            $paypalAccountStatus = $this->config->get("paypal_account_connected");
           	
			$defaultConfigs = [];

			$checkResponse  = $this->_validateResponse($merchantData);
				
			if ($checkResponse['status'] == 'ok') {

				$defaultConfigs = $this->_initPaypalFields($merchantData);
				$this->setting->deleteByKeys([
											"isPaypalZero",
											"paypal_mail_error",
											"paypal_receivable_error",
											"paypal_send_only_error",
											"paypal_oauth_error"
											]);
			} else {
					
				$defaultConfigs['paypal_merchant_id'] = $merchantData['merchant_id'];
					
				$deleteConfigs = [];
					
				if($merchantData["primary_email_confirmed"] != 1) {
					$defaultConfigs["paypal_mail_error"] = $checkResponse["errors"]["mail_error"];
				} else {
					$deleteConfigs[] = 'paypal_mail_error';
				}

				if($merchantData["payments_receivable"] != 1) {
						$defaultConfigs["paypal_receivable_error"] = $checkResponse["errors"]["receivable_error"];
				} else {
						$deleteConfigs[] = 'paypal_receivable_error';
				}
					
				if(empty($merchantData["oauth_integrations"])) {
						$defaultConfigs["paypal_oauth_error"] = $checkResponse["errors"]["oauth_error"];
				} else {
						$deleteConfigs[] = 'paypal_oauth_error';
				} 
					
					
				if(in_array("paypal_send_only_error", array_keys($checkResponse["errors"]))) {
					$defaultConfigs["paypal_send_only_error"] = $checkResponse["errors"]["paypal_send_only_error"];
				}else {
					$deleteConfigs[] = 'paypal_oauth_error';
				} 		
			}
				
			if(!$this->config->has('paypal_account_id')){
					
				$defaultConfigs['paypal_account_id'] = $this->paypal->addAccountRecord([
														 'email' 		=> $merchantData["primary_email"],
														 'merchant_id'  => $merchantData["merchant_id"]
														]);
															
			}
						
			if(!empty($deleteConfigs)){
				$this->setting->deleteByKeys($deleteConfigs);
			}
	
				
            $this->setting->insertUpdateSetting('paypal', $defaultConfigs);
        
			return ['status' => 'success'];

		}
		else {
			//TO:DO | decide what to return 
			return;
		}
		
		
	}

    private function _initPaypalFields($merchant_data) {

        $data['paypal_account_connected'] 	= 1;
        $data['paypal_merchant_id'] 		= $merchant_data['merchant_id'];
        $data['paypal_product'] 			= $merchant_data['products'][0]['name'];
        $data['paypal_merchant_client_id'] 	= $merchant_data['oauth_integrations'][0]['oauth_third_party'][0]['merchant_client_id'];
        $data["paypal_email"] 				= $merchant_data["primary_email"];
		//$data["paypal_status"] 			= "1";
        //$data["paypal_addOnProduct"] 		= "1";
        $data["paypal_view_checkout"] 		= "1";
			
        if(in_array($merchant_data["country"], $this->zero_countries)) {
            $data["isPaypalZero"]  = 1;
          //  $data["paypal_status"] = 0;
        }

        return $data;
    }

	
    private function _validateResponse($responseArray) {


        $errors = [];
		if($this->_checkSendOnlyCountries($responseArray["country"])) {
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
		
        $status = (empty($errors)) ? 'ok' : 'errors';

        
		return ['status' => $status, 'errors' => $errors];
    }
	

	private function _validateSignature($payload = ''): bool
    {
        $header_signature =  getallheaders()['X-Ec-Signature'] ?? '';
		 
		if(!defined('ECTOOLS_ENC_KEY')){
			define ('ECTOOLS_ENC_KEY', '8ah3ww72bk4b9agddm2art1gy5h75zhaz4im9gd3');
		}
		
		// Signature matching
		$expected_signature = hash_hmac('sha1', $payload , ECTOOLS_ENC_KEY );

		$signature = '';
		if(
			strlen($header_signature) == 45 &&
			substr($header_signature, 0, 5) == 'sha1='
		  ) {
		  $signature = substr($header_signature, 5);
		}
		
		//validate 
		if (hash_equals($signature, $expected_signature)) {
		 return true;
		}
		
		$this->logger->write(" invalid-signature :". $signature);

		return false;
    }
	
	private function _checkSendOnlyCountries($merchantCountry) {

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

	
}