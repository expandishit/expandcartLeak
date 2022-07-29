<?php

class ControllerPaymentMyfatoorah extends Controller {
	private $error = array(); 

	public function index() { 

		$this->language->load('payment/my_fatoorah');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$this->load->model('localisation/order_status');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{
			if ( ! $this->validate() )
			{
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
				
				$this->response->setOutput(json_encode($result_json));
				
				return;
			}

			$this->model_setting_setting->checkIfExtensionIsExists('payment', 'my_fatoorah', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('my_fatoorah', $this->request->post);
			
			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

			$result_json['success'] = '1';
			
			$this->response->setOutput(json_encode($result_json));
			
			return;
		}


		// =============== breadcrumbs ===================

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
			);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
			);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/my_fatoorah', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
			);

		$data['action'] = $this->url->link('payment/my_fatoorah', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('payment/my_fatoorah', 'token=' . $this->session->data['token'], 'SSL');	
		

		// =============== breadcrumbs ===================

		$data['my_fatoorah_api'] = $this->config->get('my_fatoorah_api');

		$data['my_fatoorah_merchant_username'] = $this->config->get('my_fatoorah_merchant_username'); 

		$data['my_fatoorah_merchant_password'] = $this->config->get('my_fatoorah_merchant_password');

		$data['my_fatoorah_merchant_country_code'] = $this->config->get('my_fatoorah_merchant_country_code');

        $data['my_fatoorah_gateway_mode'] = $this->config->get('my_fatoorah_gateway_mode');
        
        $data['my_fatoorah_payment_type'] = $this->config->get('my_fatoorah_payment_type');
        
		//$data['my_fatoorah_total'] = $this->config->get('my_fatoorah_total'); 

		$data['my_fatoorah_order_status_id'] = $this->config->get('my_fatoorah_order_status_id');

        $data['my_fatoorah_pending_order_status_id'] = $this->config->get('my_fatoorah_pending_order_status_id');

		$data['my_fatoorah_failed_order_status_id'] = $this->config->get('my_fatoorah_failed_order_status_id'); 

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['my_fatoorah_status'] = $this->config->get('my_fatoorah_status');
                
        $data['my_fatoorah_initiate_payment_status'] = $this->config->get('my_fatoorah_initiate_payment_status');

        $data['my_fatoorah_token'] = $this->config->get('my_fatoorah_token');

        $data['my_fatoorah_version'] = $this->config->get('my_fatoorah_version');

        $data['my_fatoorah_country'] = $this->config->get('my_fatoorah_country') ?: 'kw';

		$data['my_fatoorah_tax'] = $this->config->get('my_fatoorah_tax') ?: 1 ; 

        $data['current_currency_code'] = $this->currency->getCode();

		$data['my_fatoorah_webhook_secret_key']=$this->config->get('my_fatoorah_webhook_secret_key');

		$data['my_fatoorah_sort_order'] = $this->config->get('my_fatoorah_sort_order');

		$data['webhook_endpoint'] = HTTP_CATALOG . 'index.php?route=payment/my_fatoorah/webhook';
		
		$this->load->model('localisation/language');
		$data['languages'] = $languages = $this->model_localisation_language->getLanguages();
       
		foreach ( $languages as $language )
        {
            $data['myfatoorah_field_name_'.$language['language_id']] = $this->config->get('myfatoorah_field_name_'.$language['language_id']);
        }
		$this->data = $data;

        $this->template = 'payment/my_fatoorah.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	
	private function validate()
	{

		if ( ! $this->user->hasPermission('modify', 'payment/my_fatoorah') )
		{
			$this->error['error'] = $this->language->get('error_permission');
		}

		if (
            $this->request->post['my_fatoorah_version'] == "v1" &&
            !$this->request->post['my_fatoorah_merchant_username']
        ) {
			$this->error['my_fatoorah_merchant_username'] = $this->language->get('error_field_cant_be_empty');
		}

		if (
            $this->request->post['my_fatoorah_version'] == "v1" &&
            !$this->request->post['my_fatoorah_merchant_password']
        ) {
			$this->error['my_fatoorah_merchant_password'] = $this->language->get('error_field_cant_be_empty');
		}

        if ( ! $this->request->post['my_fatoorah_version'] )
        {
            $this->error['my_fatoorah_version'] = $this->language->get('error_field_cant_be_empty');
        }

        if ( $this->request->post['my_fatoorah_version'] == "v2" && ! $this->request->post['my_fatoorah_token'] )
        {
            $this->error['my_fatoorah_token'] = $this->language->get('error_field_cant_be_empty');
        }

        if (in_array($this->request->post['my_fatoorah_country'], ['kw', 'ae', 'sa', 'bh', 'qa']) == false) {
            $this->error['my_fatoorah_country'] = $this->language->get('error_my_fatoorah_country');
        }


		if ( $this->error && !isset($this->error['error']) )
		{
		    $this->error['warning'] = $this->language->get('error_warning');
		}
		
		return $this->error ? false : true;
	}


	public function getRegisterationData() {
	    $this->load->model('payment/my_fatoorah');
	    $json = array(
	        'banks' => $this->model_payment_my_fatoorah->getBanks(),
		    'countries' => $this->model_payment_my_fatoorah->getCountiesWithCodes(),
		    'nationalities' => $this->model_payment_my_fatoorah->getNationalityCountries(),
		    'categories' => $this->model_payment_my_fatoorah->getVendorCategories(),
	    );
	    $this->response->setOutput(json_encode($json));
	}


	public function getProfile() {
	    $this->load->model('payment/my_fatoorah');
	    $json = $this->model_payment_my_fatoorah->getProfile($this->config->get('my_fatoorah_token'));
	    //IsApproved
	    $this->response->setOutput(json_encode($json));
	}

    public function register(){
		$this->language->load('payment/my_fatoorah');

	    if( $this->request->server['REQUEST_METHOD'] == 'POST' ) {
	    	//Validate form fields
	      	if ( ! $this->_validateRegisterForm() ){
	        $result_json['success'] = '0';
	        $result_json['errors'] = $this->error;
	       	}
	      	else{
	        $this->load->model('setting/setting');
		    $this->load->model('payment/my_fatoorah');
	        $this->model_setting_setting->insertUpdateSetting('my_fatoorah', $this->request->post );
	        $data = [
			    "FullName"=> $this->request->post['full_name'],
			    "Mobile"=> $this->request->post['mobile'],
			    "CountryId"=> $this->request->post['country_id'],
			    "Email"=> $this->request->post['email'],
			    "Password"=> $this->request->post['password'],
			    "ProfileName"=> $this->request->post['profile_name'],
			    "CompanyNameEn"=>  $this->config->get('config_name')['en'],
			    "CompanyNameAr"=>  $this->config->get('config_name')['ar'],
			    "DefaultLanguage"=> 1,
			    "BankId"=> $this->request->post['bank_id'],
			    "BankAccountHolderName"=> $this->request->post['account_holder_name'],
			    "BankAccount"=> $this->request->post['bank_account'],
			    "VendorCategory"=> $this->request->post['category_id'],
			    "Iban"=> $this->request->post['iban'],
			    "VendorBusinessType"=> $this->request->post['business_id'],
			    "TransactionsNoMonthly"=> (int)$this->request->post['transactions_no'],
			    "TransactionsValueMonthly"=>$this->request->post['transactions_value'],
			    "NationalityCountryId"=> $this->request->post['nationality_id']
	        ];
	        /*
	        success response Example
			    [Id] => 0
			    [IsSuccess] => 1
			    [Message] => You have been registred successfully! Please check your Email address to confirm email, and your SMS Inbox to Confirm your mobile.
			    [FieldsErrors] => 
	        */
			$response = $this->model_payment_my_fatoorah->register($data);

			if($response['IsSuccess'] == 1){
				// get token from my_fatoorah
				$token_data = 'grant_type=password&'.'username='.$this->request->post['email'].'&password='.$this->request->post['password'];
				$tokenResponse = $this->model_payment_my_fatoorah->generateToken($token_data);
				// update token and version in settings table
		        $this->model_setting_setting->insertUpdateSetting('my_fatoorah',[
		        	"my_fatoorah_version"=>"v2",
		        	"my_fatoorah_token"=>$tokenResponse['access_token'],
		        ]);
				// success
		        $result_json['success_msg'] = $this->language->get('text_confirm_success');
		        $result_json['success']  = '1';
			}
			else{
				 // get errors from [FieldsErrors] array
				foreach ($response['FieldsErrors']  as $row) {
				    $error_list[] = $row['Error'];
				}
				//error
				$result_json['success'] = '0';
				$result_json['fails'] = $error_list;
			}

	      }
	      $this->response->setOutput(json_encode($result_json));
	    }
	    else{
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
  }

	public function upload(){
	$this->language->load('payment/my_fatoorah');
    if( $this->request->server['REQUEST_METHOD'] == 'POST' ) {
	    $this->load->model('payment/my_fatoorah');
	    $token = $this->config->get('my_fatoorah_token');
	    $success = 1 ;
	    foreach ($this->request->files as $name => $file) {
	    		if(empty($file['name']))
	    		// file not posted
	    		continue;
	    	$fatoorah_file_type = substr($name, -1);
	    	// upload files 
        	$fn = "/ecdata/stores/".STORECODE."/credentials/my_fatoorah/" . $file['name'];
            $uploaded = \Filesystem::setPath($fn)->upload($file['tmp_name']);

	    	$data = array("FileType" => $fatoorah_file_type, "ExpireDate" => "2021-10-06T15:12:01.974Z", "FileUpload" =>new CurlFile($file['tmp_name'], $file['type'], $file['name']));
			$uploadResponse = $this->model_payment_my_fatoorah->UpdateDocument($token,$data);
			if($uploadResponse['IsSuccess'] == 0){
				$success = 0;
				$error_list[] = $uploadResponse['Message'];
			}
	    }
	   
		if($success){
			// success
	        $result_json['success_msg'] = $this->language->get('text_confirm_success');
	        $result_json['success']  = '1';
		}
		else{
			//error
			$result_json['success'] = '0';
			$result_json['fails'] = $error_list;
		}
      $this->response->setOutput(json_encode($result_json));
    }
    else{
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }
  }

  private function _validateRegisterForm(){
  	
	if ( ! $this->user->hasPermission('modify', 'payment/my_fatoorah') )
	{
		$this->error['error'] = $this->language->get('error_permission');
	}

    if ((utf8_strlen($this->request->post['full_name']) < 3 ) || (utf8_strlen($this->request->post['full_name']) > 25 )) {
      $this->error['full_name'] = $this->language->get('error_full_name');
    }

	if( !isset($this->request->post['mobile']) || !preg_match("/^\+?\d+$/", $this->request->post['mobile']) || (utf8_strlen($this->request->post['mobile']) > 10 ) ){
	    $this->error['mobile'] = $this->language->get('error_mobile');
	}

    if( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $this->request->post['email'])){
        $this->error['email'] = $this->language->get('error_email');
    }
    
	if( !preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d$@$!%*#?&]{8,}$/" , $this->request->post['password'] )) {
      $this->error['password'] = $this->language->get('error_password');
	}
	
    if ((utf8_strlen($this->request->post['category_id']) < 1 ) ) {
      $this->error['category_id'] = $this->language->get('error_category');
    }

    if ( $this->request->post['business_id'] < 1  || $this->request->post['business_id'] > 2) {
      $this->error['business_id'] = $this->language->get('error_business');
    }

    if($this->error && !isset($this->error['error']) ){
      $this->error['warning'] = $this->language->get('error_warning');
    }
    return !$this->error;
  }

  public function inquery() {

		$this->load->model('sale/order');
		$this->language->load('payment/my_fatoorah');

		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

		$url = 'https://apitest.myfatoorah.com';

		if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
			$url = 'https://api.myfatoorah.com';
		}

		$access_token = $this->config->get('my_fatoorah_token');

		if(isset($access_token) && !empty($access_token)){

			$id = $order['payment_trackId'];         
			$post_string = '{
				"Key": "'.$id.'",
				"KeyType": "InvoiceId"
			}';

			$url = "$url/v2/GetPaymentStatus";
			$soap_do1 = curl_init();
			curl_setopt($soap_do1, CURLOPT_URL,$url );
			curl_setopt($soap_do1, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($soap_do1, CURLOPT_TIMEOUT, 10);
			curl_setopt($soap_do1, CURLOPT_RETURNTRANSFER, true );
			curl_setopt($soap_do1, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($soap_do1, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($soap_do1, CURLOPT_POST, 1 );
			// curl_setopt($soap_do1, CURLOPT_POST, 0); // Has to be POST request
			curl_setopt($soap_do1, CURLOPT_HTTPGET, 1);
			curl_setopt($soap_do1, CURLOPT_POSTFIELDS, $post_string);
			curl_setopt($soap_do1, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json','Authorization: Bearer '.$access_token));
			$result_in = curl_exec($soap_do1);
			$err_in = curl_error($soap_do1);
			curl_close($soap_do1);
			$getRecorById = json_decode($result_in, true);

			$this->initializer([
				'paymentTransaction' => 'extension/payment_transaction',
				'paymentMethod' => 'extension/payment',
			]);		
            
			$transaction = $this->getTransactionDependOnStatus($getRecorById);
			$invoiceStatus = $getRecorById['Data']['InvoiceStatus'];
			if (isset($invoiceStatus) && $invoiceStatus) {
				// Check for order status code that maybe set payment
				// doesn't change default value which is = 0
				if (!$this->config->get('my_fatoorah_order_status_id') || $this->config->get('my_fatoorah_order_status_id') == 0) {
					$json['success'] = false;
					$json['response'] = $this->language->get('not_changed_default_order_status');
				} else {

					switch ($invoiceStatus) {
						case 'Paid';
							$this->paymentTransaction->insert([
								'order_id' => $getRecorById['Data']['CustomerReference'],
								'transaction_id' => $transaction['TransactionId'],
								'payment_gateway_id' => $this->paymentMethod->selectByCode('my_fatoorah')['id'],
								'payment_method' => $transaction['PaymentGateway'],
								'status' => $invoiceStatus,
								'amount' => $getRecorById['Data']['InvoiceValue'],
								'currency' => $transaction['Currency'],
								'details' => json_encode($getRecorById),
							]);

							$orderStatusId = $this->config->get('my_fatoorah_order_status_id');
							if($order['order_status_id']==$orderStatusId)
							{
								$json['success'] = true;
								$json['response'] = $this->language->get('transaction_already_paid');
							}
							else
							{
							$this->model_sale_order->confirm($getRecorById['Data']['CustomerReference'], $orderStatusId);
							$json['success'] = true;
							$json['response'] = $this->language->get('transaction_changed_to_paid');
							}
						break;
						case 'Pending';
							$expireDate=$getRecorById['Data']['ExpiryDate'];
							$expireDate = date("Y-m-d\TH:i:s", strtotime($expireDate));
							if(date("Y-m-d\TH:i:s")>$expireDate)
							{
								//free payment_trackId to provide enquiry in next enquiry
								$this->model_sale_order->freeInvoice($getRecorById['Data']['CustomerReference']);
								$json['success'] = true;
								$json['response'] = $this->language->get('transaction_expired');
							}
							else
							{
								$json['success'] = true;
								$json['response'] = $this->language->get('entry_pending_order_status');	
							}
						break;
					}
				}
			}
			else
			{

				$json['success'] = false;
				$json['response'] = $this->language->get('error_message');
			}

		}
		else
		{
			$json['success'] = false;
			$json['response'] = $this->language->get('empty_token');
		}

		$this->response->setOutput(json_encode($json));
		return;
  }

  public function refund() {

	$this->language->load('payment/my_fatoorah');
	$access_token = $this->config->get('my_fatoorah_token');
	$this->load->model('sale/order');
	$order = $this->model_sale_order->getOrder($this->request->post['order_id']);
    if($this->config->get('my_fatoorah_order_status_id')!=$order['order_status_id'])
	{
		$json['success'] = false;
	    $json['response'] = $this->language->get('order_not_paid');
	}
    else if(isset($access_token) && !empty($access_token)){
		$url = 'https://apitest.myfatoorah.com';

		if ($this->config->get('my_fatoorah_gateway_mode') == '1') {
			$url = 'https://api.myfatoorah.com';
		}
	   
       $id = $order['payment_trackId'];
	   $this->initializer([
		'paymentTransaction' => 'extension/payment_transaction',
	   ]);
	   $transaction=$this->paymentTransaction->selectPaidByOrderId($order['order_id']);
	   
       $amount=$transaction['amount'];
	   $RefundChargeOnCustomer=$this->request->post['RefundChargeOnCustomer'];
	   $ServiceChargeOnCustomer=$this->request->post['ServiceChargeOnCustomer'];
        
	   $post_string = '{
			 "Key": "'.$id.'",
			 "KeyType": "InvoiceId",
			 "RefundChargeOnCustomer":"'.$RefundChargeOnCustomer.'",
			 "ServiceChargeOnCustomer":"'.$ServiceChargeOnCustomer.'",
			 "Amount":"'.$amount.'",
			 "Comment":"",
		   }';
	   $url = "$url/v2/MakeRefund";
	   $soap_do1 = curl_init();
	   curl_setopt($soap_do1, CURLOPT_URL,$url );
	   curl_setopt($soap_do1, CURLOPT_CONNECTTIMEOUT, 10);
	   curl_setopt($soap_do1, CURLOPT_TIMEOUT, 10);
	   curl_setopt($soap_do1, CURLOPT_RETURNTRANSFER, true );
	   curl_setopt($soap_do1, CURLOPT_SSL_VERIFYPEER, false);
	   curl_setopt($soap_do1, CURLOPT_SSL_VERIFYHOST, false);
	   curl_setopt($soap_do1, CURLOPT_POST, false );
	   curl_setopt($soap_do1, CURLOPT_POST, 0);
	   curl_setopt($soap_do1, CURLOPT_HTTPGET, 1);
	   curl_setopt($soap_do1, CURLOPT_POSTFIELDS, $post_string);
	   curl_setopt($soap_do1, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json','Authorization: Bearer '.$access_token));
	   $result_in = curl_exec($soap_do1);
	   $err_in = curl_error($soap_do1);
	   curl_close($soap_do1);
	   $result = json_decode($result_in, true);

	   if (isset($result['IsSuccess'])) {
		    
		    if($result['IsSuccess']==true)
			 {
				$json['success'] = true;
				$json['response'] = $result["Message"];
				$data['order_status_id'] = $this->config->get('my_fatoorah_refund_order_status_id');
				$order = $this->model_sale_order->addOrderHistory($order['order_id'], $data);
			 }
			 else
			 {
                $json['success'] = false;
				$json['response'] = $result["ValidationErrors"][0]["Error"];
			 }
		} else {
			$json['success'] = false;
			$json['response'] =  $this->language->get('config_error');
		}
    }
	else
	{
		$json['success'] = false;
	    $json['response'] = $this->language->get('empty_token');
	}

	$this->response->setOutput(json_encode($json));
	return;
}

public function getTransactionDependOnStatus($transactions)
{
	foreach ($transactions as $transaction) {
		if($transaction['TransactionStatus']=="Succss")
		{
			return $transaction;
		}
	}
	// return last failed one
	if(count($transactions)>0)
	return $transactions[count($transactions)-1];

	return false;

}

}
