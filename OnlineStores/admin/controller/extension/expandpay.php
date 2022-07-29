<?php


class ControllerExtensionExpandPay extends Controller{
    private $base_url = 'http://34.107.96.22/';
    private $secret_key = EXPANDPAY_SECRET_KEY;
    public function index() {
		$result = $this->_canAccess();
        $this->language->load('extension/payment');
        $this->load->model('setting/setting');
        $this->load->model('localisation/zone');
        $this->load->model('extension/expandpay');
        $this->document->setTitle($this->language->get('heading_title_expandpay'));
        if($this->config->get('expandpay_configure') == '1' && $this->config->get('expandpay_merchant_status') == 'active'){
			$this->response->redirect($this->url->link('extension/expandpay/setting', '', 'SSL'));
		}
        $url = sprintf(
            "%s://%s%s",
            isset($this->request->server['HTTPS']) && $this->request->server['HTTPS'] != 'off' ? 'https' : 'http',
            $this->request->server['SERVER_NAME'],
            $this->request->server['REQUEST_URI']
          );
        
        $actionURL = sprintf(
            "%s://%s",
            isset($this->request->server['HTTPS']) && $this->request->server['HTTPS'] != 'off' ? 'https' : 'http',
            $this->request->server['SERVER_NAME']
          );

        // $vendorCategoryList = $this->model_extension_expandpay->getVendorCategoryList();
        $lang = $this->config->get('config_admin_language');
        preg_match('!\d+!', $this->config->get('config_telephone'), $telephone);
        $config_name = unserialize($this->config->get('config_name'));
        $config_address = unserialize($this->config->get('config_address'));
        if($config_address){
            $config_address = $config_address[$lang];
        }else{
            $config_address = $this->config->get('config_address');
            if(is_array($config_address)){
                $config_address = $config_address[$lang];
            }
        }
        if($config_name){
            $config_name = $config_name[$lang];
        }else{
            $config_name = $this->config->get('config_name');
            if(is_array($config_name)){
                $config_name = $config_name[$lang];
            }
        }
        $this->data = [
            'activated' => $this->config->get('expandpay_status') ? 1 : 0,
            // 'vendorCategoryList' => $vendorCategoryList,
            'files_uploaded' => $this->config->get('expandpay_file_upload'),
            'form_submitted' => $this->config->get('expandpay_form_submit'),
            'merchantStatus' => $this->config->get('expandpay_merchant_status'),
            'email' => $this->config->get('config_email'),
            'full_name' => $this->config->get('config_owner'),
            'telephone' => $telephone[0],
            'expandpay_country_code' => $this->config->get('expandpay_country_code'),
            'merchant_data' => $this->config->get('expandpay_data'),
            'merchant_files' => $this->config->get('expandpay_files'),
            'config_name' => $config_name,
            'config_address' => $config_address
        ];
        if (!isset(WHITELIST_STORES[STORECODE])) {
            $this->data['white_list'] = 1;
        }
        $this->template = 'extension/payment/expandpay2.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        
        $this->response->setOutput($this->render_ecwig());

    }

    /** Start add page for UI test **/
    public function expandpayLogin() {
		$this->_canAccess();
        $this->language->load('extension/payment');
        $this->load->model('setting/setting');
        $this->document->setTitle($this->language->get('heading_title_expandpay'));

        if($this->config->get('expandpay_token'))
            $this->response->redirect($this->url->link('extension/expandpay'));

        if($this->request->server['REQUEST_METHOD'] == 'POST'){

            if(isset($this->request->post['login_mail']))
                $this->getVerifyCode($this->request->post['login_mail']);
            else
                $this->sendVerifyCode($this->request->post);

        }else{
            if($this->config->get('expandpay_merchant_status') == 'active' && $this->config->get('expandpay_configure') == '1'){
                $this->response->redirect($this->url->link('extension/expandpay/setting', '', 'SSL'));
            }else if($this->config->get('expandpay_merchant_status') == 'active' && $this->config->get('expandpay_configure') != '1'){
                $this->response->redirect($this->url->link('extension/expandpay', '', 'SSL'));
            }
            $this->template = 'extension/payment/expandpaylogin.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );
            
            $this->response->setOutput($this->render_ecwig());
        }
    }
    /** End add page for UI test **/

    public function register(){
		$this->_canAccess();
		
        $this->language->load('extension/payment');
        $this->load->model('extension/expandpay');
        //call back URL that used in payment
		
		//we will use the subdomain to avoid repeated issue related to store domain change 
		$store_domain = "https://".STORECODE.".expandcart.com/";
       
	    $callback_url = $store_domain . 'api/v1/expandpay/callback';
        $webhook_url  = $store_domain . 'api/v1/expandpay/updatePaymentRegister';
        
		if($this->request->server['REQUEST_METHOD'] == 'POST')
        {
            $data = [];
            
            $custom_fields = [
                "store_code" 		  => STORECODE,
                "bank_name" 		  => $this->request->post['bank_name'],
                "bank_id" 			  => $this->request->post['bank_name'],
                "bank_branch_name" 	  => $this->request->post['bank_branch_name'],
                "account_holder_name" => $this->request->post['account_holder_name'],
                "bank_account" 		  => $this->request->post['bank_account'],
                'iban' 				  => $this->request->post['IBAN']
            ];
			
            // store bank name and id because its different from eg
            if($this->request->post['country'] != 'eg'){
                $bank_info = explode(' ',trim($this->request->post['bank_name']),2);
                $custom_fields['bank_id'] = $bank_info[0];
                $custom_fields['bank_name'] = $bank_info[1];
            }
			
            foreach($this->request->post as $key => $value){
                $data[$key] = $this->db->escape($value);
            }
			
            $data['phone'] 			= str_replace(" ","",$data['phone']);    
            $data['custom_fields']  = $custom_fields;
            $data['success_url'] 	= $callback_url;
            $data['pending_url'] 	= $callback_url;
			$data['fail_url'] 		= $callback_url;
			$data['webhook_url'] 	= $webhook_url;
            
			$errors = [];
            // try to get international number without phone code
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            try {
                $data['phone'] = $phoneUtil->parse($data['phone'], strtoupper($data['expandpay_country_code']));
                $data['phone'] = $phoneUtil->format($data['phone'], \libphonenumber\PhoneNumberFormat::E164);
                $data['phone'] = str_replace('+'.$data['phone_code'],'',$data['phone']);
            
			} catch (\libphonenumber\NumberParseException $e) {
                $errors['phone'] = $this->language->get('entry_invalid');
            }

        }
        $data['business'] = [];
        $result = [];
        // check if data is not empty
        foreach($this->request->post as $key => $value){
            if(trim($value) == ''){
                $errors[$key] = $this->language->get('entry_required_field');
            }
        }
        if(empty($errors)){
            $response = $this->model_extension_expandpay->register($data);
            $result = [];
            if($response->status == 'ERR'){
                $result['success'] = '0';
                $result['errors'] = $response->errors;
                $result['error'] = $response->error;
            }else{

                /***************** Start ExpandCartTracking #347722  ****************/

                // send mixpanel user registered to expandpay
                $this->load->model('setting/mixpanel');
                $this->model_setting_mixpanel->trackEvent('Registered with ExpandPay');

                // send amplitude user registered to expandpay
                $this->load->model('setting/amplitude');
                $this->model_setting_amplitude->trackEvent('Registered with ExpandPay');

                /***************** End ExpandCartTracking #347722  ****************/
                $result['success'] = '1';
            }
            $this->response->setOutput(json_encode($result));
    
        }else{

            $result['success'] = '0';
            $result['errors'] = $errors;
            $this->response->setOutput(json_encode($result));
        }

    }
    // for docs upload
    public function upload(){
        $this->language->load('payment/my_fatoorah');
        $data = array();
        if( $this->request->server['REQUEST_METHOD'] == 'POST' ) {
            $this->load->model('extension/expandpay');
            $this->load->model('setting/setting');
            $token = $this->config->get('expandpay_token');
            $success = 1 ;
            foreach ($this->request->files as $name => $file) {
                    if(empty($file['name']))
                    // file not posted
                    continue;
                $fatoorah_file_type = substr($name, -1);
                // upload files 
                $fn = "/ecdata/stores/".STORECODE."/credentials/expandpay/" . $file['name'];
                $uploaded = \Filesystem::setPath($fn)->upload($file['tmp_name']);
    
                $data[$name] = new CurlFile($file['tmp_name'], $file['type'], $file['name']);
            }
			if(!$uploaded['size']){
				//error
				$result_json['success'] = '0';
				$result_json['fails'] = $this->language->get('error_file_upload');
			}
			else{
				$uploadResponse = $this->model_extension_expandpay->UpdateDocument($token,$data);
				$this->log('upload files',$uploadResponse);
				if($uploadResponse['status'] == 'OK'){
					// success
					$result_json['success_msg'] = $this->language->get('text_confirm_success');
					$result_json['success']  = '1';
					$this->model_setting_setting->editSettingValue('expandpay','expandpay_file_upload',1);
					if($this->config->get('expandpay_merchant_status') == "rejected")
						$this->model_setting_setting->editSettingValue('expandpay','expandpay_merchant_status','pending');

				}
				else{
					//error
					$result_json['success'] = '0';
					$result_json['fails'] = $uploadResponse['errors'];
				}
			}
          $this->response->setOutput(json_encode($result_json));
        }
        else{
          $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    
    }


    public function setting(){
        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');
        $this->language->load('extension/payment');
        $this->document->setTitle($this->language->get('heading_title_expandpay'));
        $merchant_status = $this->config->get('expandpay_merchant_status');
        // prevent merchant accsing setting if not active
        if($merchant_status != 'active')
            $this->response->redirect($this->url->link('extension/expandpay'));
        
        $expandpay_data = $this->config->get('expandpay_data');
        $bank_name = $this->getBankNameSetting('eg',$expandpay_data['custom_fields']['bank_name']);
        $this->data = [
            'country' => $expandpay_data['country'] ? $expandpay_data['country'] : $expandpay_data['country_iso_2'],
            'bank_name' => $bank_name ? $bank_name : $expandpay_data['custom_fields']['bank_name'],
            'bank_account' => $expandpay_data['custom_fields']['bank_account'],
            'account_holder_name' => $expandpay_data['custom_fields']['account_holder_name'],
            'IBAN' => $expandpay_data['custom_fields']['iban'],
            'bank_branch_name' => $expandpay_data['custom_fields']['bank_branch_name'],
            'order_statuses' => $this->model_localisation_order_status->getOrderStatuses(),
            'expandpay_status' => $this->config->get('expandpay_status'),
            'expandpay_order_status_id' => $this->config->get('expandpay_order_status_id'),
            'expandpay_denied_order_status_id' => $this->config->get('expandpay_denied_order_status_id')
        ];
        $this->template = 'extension/payment/expandpayConfigureMyfatoorah.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        
        $this->response->setOutput($this->render_ecwig());

    }

    public function update(){
        $this->language->load('extension/payment');
        $this->load->model('setting/setting');
        $expandpay_data = $this->config->get('expandpay_data');
        
        $expandpay_data['account_number'] = $this->request->post['account_number'];
        $expandpay_data['bank_name'] = $this->request->post['bank_name'];
        $expandpay_data['custom_fields']['account_holder_name'] = $this->request->post['account_holder_name'];
        $expandpay_data['custom_fields']['IBAN'] = $this->request->post['IBAN'];
        $this->model_setting_setting->editSettingValue('expandpay','expandpay_data',$expandpay_data);
        
        $expandpay_order_status = [
            'expandpay_order_status_id' => $this->request->post['expandpay_order_status_id'],
            'expandpay_denied_order_status_id' => $this->request->post['expandpay_denied_order_status_id'],
            'expandpay_status' => $this->request->post['expandpay_status']
        ];

        $this->model_setting_setting->insertUpdateSetting('expandpay',$expandpay_order_status);
        $result = [
            'success' => '1',
            'success_msg' => $this->language->get('text_success')
        ];
        $this->response->setOutput(json_encode($result));
    }
    public function expandpay_uninstall(){
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('expandpay');
        $expandpay_setting = [
            'expandpay_status',
            'expandpay_token',
            'expandpay_merchant_id',
            'expandpay_data',
            'expandpay_file_upload',
            'expandpay_form_submit',
            'expandpay_merchant_status',
            'expandpay_default_currency',
            'expandpay_configure'
        ];
        foreach($expandpay_setting as $setting_key){
            $this->config->delete($setting_key);
        }

        /***************** Start ExpandCartTracking #347724  ****************/

        // send mixpanel Uninstall expandpay event
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->trackEvent('ExpandPay Removed');

        // send amplitude Uninstall expandpay event
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->trackEvent('ExpandPay Removed');

        /***************** End ExpandCartTracking #347724  ****************/

        $this->response->redirect($this->url->link('extension/expandpay/expandpaylogin'));

    }
    public function transactions(){
        $this->language->load('extension/payment');
        $this->load->model('extension/expandpay');
        $this->load->model('setting/setting');

        if(!$this->config->get('expandpay_token'))
            $this->response->redirect($this->url->link('extension/expandpay/expandpayLogin'));
        
        $this->document->setTitle($this->language->get('heading_title_transaction'));

        //$transactions = $this->model_extension_expandpay->getTransactionList();
        if($this->request->server["REQUEST_METHOD"] == 'POST'){
            $start_date = $this->request->post['start_date'];
            $end_date = $this->request->post['end_date'];
            $merchant_balances = $this->model_extension_expandpay->getMerchantBalances($start_date,$end_date);
            $this->response->setOutput(json_encode($merchant_balances));
            return;
        }else{
            $merchant_balances = $this->model_extension_expandpay->getMerchantBalances();
        }

        // $withdraws_list = $this->model_extension_expandpay->getWithdrawsList();
        $country = $this->config->get('expandpay_data')['country_iso_2'] ? $this->config->get('expandpay_data')['country_iso_2'] : $this->config->get('expandpay_data')['country'];
        $this->data = [
            //'transactions' => $transactions,
            'merchant_balances' => $merchant_balances,
            'country' => $country,
            'time_zone' => $this->config->get('config_timezone'),
            'currency' => $this->config->get('expandpay_default_currency')
            // 'withdraws_List' => $withdraws_list
        ];
       $this->template = 'extension/payment/transactions.expand';
          $this->children = array(
              'common/header',
              'common/footer'
          );
      
      $this->response->setOutput($this->render_ecwig());
      
    }
	
	private function _canAccess(){
		
			//hide & show expandPay according to merchant country in whmcs
			$whmcs			= new whmcs();
			$clientDetails 	= $whmcs->getClientDetails(WHMCS_USER_ID);
			//for test 
			//$clientDetails=[];
			//$clientDetails['countrycode'] = 'EG';
			$in_egypt 		= false;
			if(!empty($clientDetails)){
				$in_egypt  = (strtoupper($clientDetails['countrycode']) == 'EG');
			}
			$show_expandpay = false;
			$this->load->model('extension/payment');
			$expandpay_method =  $this->model_extension_payment->getPaymentMethodData("expandpay");			
			if (STAGING_MODE != 1){
				//show expandpay in published case 
			   if( $in_egypt && !empty( $expandpay_method) && isset($expandpay_method['published']) &&  $expandpay_method['published'] == '1') {
				   $show_expandpay = true;
			   }
			} else {
					if($in_egypt) {
						$show_expandpay = true ;
					}
			}
		   
		if(!$show_expandpay){
			$this->response->redirect($this->url->link('extension/payment', '', 'SSL'));
			return false;
		}
		
		return true ;
	}

    public function dtTransactionHandler(){

        if($this->request->server['REQUEST_METHOD'] == 'POST'){
            $this->load->model('extension/expandpay');

            $page = ($this->request->post['start']/$this->request->post['length']) + 1;
            $search_key = $this->request->post['search']['value'];
            $start_date = $this->request->post['start_date'];
            $end_date = $this->request->post['end_date'];
            $statuses = $this->request->post['statuses'];
            $transactions = $this->model_extension_expandpay->getTransactionList($page,$this->request->post['length'],$search_key,$start_date,$end_date,$statuses);
            
            $json_data = array(
                "draw" => $this->request->post['draw'], // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
                "recordsTotal" => $transactions['result'], // total number of records
                "recordsFiltered" => $transactions['filter_result'], // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data" => $transactions['data'],   // total data array
            );
            $this->response->setOutput(json_encode($json_data));
        }

    }

    public function dtWithDrawHandler(){
        if($this->request->server['REQUEST_METHOD'] == 'POST'){
            $this->load->model('extension/expandpay');

            $page = ($this->request->post['start']/$this->request->post['length']) + 1;
            $search_key = $this->request->post['search']['value'];
            $start_date = $this->request->post['start_date'];
            $end_date = $this->request->post['end_date'];
            $statuses = $this->request->post['statuses'];
            $withdraws_list = $this->model_extension_expandpay->getWithdrawsList($page,$this->request->post['length'],$search_key,$start_date,$end_date,$statuses);
            
            $json_data = array(
                "draw" => $this->request->post['draw'], // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
                "recordsTotal" => $withdraws_list['result'], // total number of records
                "recordsFiltered" => $withdraws_list['filter_result'], // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data" => $withdraws_list['data'],   // total data array
            );
            $this->response->setOutput(json_encode($json_data));
        }

    }

    // refund
    public function transactionRefund(){
        if($this->request->server['REQUEST_METHOD'] == 'POST'){

            $this->language->load('extension/payment');
            $this->load->model('setting/setting');

            $data = [];
            $errors = [];
            $result = [];
            foreach($this->request->post as $key => $value){
                if($key == 'description')
                    continue;
                if($value == ''){
                    $errors[$key] = $this->language->get('entry_required_field');;
                }else{
                    $data[$key] = $value;
                }
            }

            // convert currency in case of any country execpt egypt
            $country = $this->config->get('expandpay_data')['country_iso_2'] ? $this->config->get('expandpay_data')['country_iso_2'] : $this->config->get('expandpay_data')['country'];
            // if($country != 'eg'){
            //     $currency = $this->config->get('expandpay_default_currency');
            //     $amount = number_format($this->currency->convert($data['amount'],$data['currency'],$currency),3);
            //     $data['currency'] = $currency;
            //     $data['amount'] = floatval($amount);    
            // }
            if(empty($errors)){
                $data['merchant_id'] = $this->config->get('expandpay_merchant_id');
                $data['currency'] = $this->config->get('expandpay_default_currency');
                $lang = $this->config->get('config_admin_language');
                $url = $this->base_url . 'api/v1/refund';
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));        
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                  'accept-language:'.$lang,
                  'Authorization:Bearer ' . $this->config->get('expandpay_token'),
                  'Content-Type:application/json'
                  ]);
        
                $response = curl_exec($curl);
        
                $resArr = json_decode($response,true);
                $this->log('refund',$resArr);
                if($resArr['status'] == 'ERR'){
                    $result['success'] = '0';
                    $result['error'] = $resArr['error'];
                    $result['errors'] = $resArr['errors'];    
                }else {
                    $result['success'] = '1';

                }

            }else{
                $result['success'] = '0';
                $result['errors'] = $errors;
            }

            $this->response->setOutput(json_encode($result));
        }
    }

    public function requestWithdraw(){
        if($this->request->server['REQUEST_METHOD'] == 'POST'){

            $this->language->load('extension/payment');
            $this->load->model('setting/setting');


            $data = [];
            $errors = [];
            $result = [];
            foreach($this->request->post as $key => $value){
                if($value == ''){
                    $errors[$key] = $this->language->get('entry_required_field');;
                }else{
                    $data[$key] = $value;
                }
            }

            if(empty($errors)){
                $data['merchant_id'] = $this->config->get('expandpay_merchant_id');
                $data['currency'] = $this->config->get('expandpay_default_currency');
                $lang = $this->config->get('config_admin_language');
                $url = $this->base_url . 'api/v1/withdrawal';
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));        
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                  'accept-language:'.$lang,
                  'Authorization:Bearer ' . $this->config->get('expandpay_token'),
                  'Content-Type:application/json'
                  ]);
        
                $response = curl_exec($curl);
        
                $resArr = json_decode($response,true);

                $this->log('withdraw',$resArr);

                if($resArr['status'] == 'ERR'){
                    $result['success'] = '0';
                    $result['error'] = $resArr['error'];
                    $result['errors'] = $resArr['errors'];    
                }else {
                    $result['success'] = '1';

                }

            }else{
                $result['success'] = '0';
                $result['errors'] = $errors;
            }

            $this->response->setOutput(json_encode($result));
        }

    }
    //get bank list and zones depend on country
    public function getBankListAndCities(){

        if($this->request->server['REQUEST_METHOD'] == 'POST'){
            $this->load->model('extension/expandpay');
            // $this->load->model('localisation/country');
            // $this->load->model('localisation/zone');
            $country_code = strtoupper($this->request->post['country']);

            // get zones
            // $country_data = $this->model_localisation_country->getCountryIdByCountryCode([$country_code]);
            // $zones = $this->model_localisation_zone->getZonesByCountryId($country_data[0]['country_id']);
            // $this->data['zones'] = $zones;

            // get bank list

            $banksList = $this->model_extension_expandpay->getBankList($country_code);
            
            $this->data['banksList'] = $banksList;

            $this->response->setOutput(json_encode($this->data));
        }
        
    }

    // get translated bank name in setting depend on lang.
    private function getBankNameSetting($country_code,$bank_name){

        $this->load->model('extension/expandpay');

        $banksList = $this->model_extension_expandpay->getBankList($country_code);
        
        $this->data['banksList'] = $banksList;

        return $banksList[$bank_name];
    }
    private function getVerifyCode($email){
        $result = [];
        $errors = [];
        $this->language->load('setting/setting');
        $lang = $this->config->get('config_admin_language');
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){

            $url = $this->base_url . 'api/v1/sendCode';
            $curl = curl_init($url);

            $data['email'] = $email;
            $data['store_code'] = STORECODE;
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));        
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
              'accept-language:'.$lang,
              'Content-Type:application/json',
              'secret-key:'.$this->secret_key,
              'client-Address:'.$this->request->server['REMOTE_ADDR']
            ]);
    
            $response = curl_exec($curl);
    
            $resArr = json_decode($response,true);

            $this->log('getVerifyCode',$resArr);

            if($resArr['status'] == 'OK')
                $result['success'] = '1';
            else
                $result['success'] = '0';
                if($resArr['error'] == 'UNDEFINED_ROW')
                    $result['redirect'] = '1';
                $result['fails']['login_mail'] = $resArr['errors'];
    
    
            $this->response->setOutput(json_encode($result));

        }else{
            $errors['success'] = '0';
            $errors['fails']['login_mail'] = $this->language->get('entry_required_field_email');
            $this->response->setOutput(json_encode($errors));

        }

    }

    private function sendVerifyCode($data){
        $errors = [];
        $this->language->load('setting/setting');
        $lang = $this->config->get('config_admin_language');
        foreach($data as $key => $value){
            if(!isset($data[$key])){
                $errors[$key] = $this->language->get('entry_required_field_email');
            }
        }
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'error';
        }

        if(empty($errors)){
            $data['code'] = implode($data['pass']);
            $url = $this->base_url . '/api/v1/verifyCode';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));        
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
              'accept-language:'.$lang,
              'Content-Type:application/json',
              'secret-key:'.$this->secret_key,
              'client-Address:'.$this->request->server['REMOTE_ADDR']
            ]);
    
            $response = curl_exec($curl);
    
            $resArr = json_decode($response,true);
            $this->log('sendVerifyCode',$resArr);
            if($resArr['status'] == 'OK'){
                $this->load->model('extension/expandpay');
                $this->model_extension_expandpay->restoreMerchantData($resArr['data']);
                $result['success'] = '1';

            }else{
                $result['success'] = '0';
                $result['fails'] = $resArr['errors'];
            }
        }else{
            $result['success'] = '0';
            $result['fails'] = $errors;
        }

        $this->response->setOutput(json_encode($result));
        
    }
    public function expandpay_configure(){
        if($this->request->server['REQUEST_METHOD'] == "POST"){
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting('expandpay', $this->request->post);
            $result['success'] = '1';
            $this->response->setOutput(json_encode($result));

        }
    }

    private function log($type, $contents , $fileName=false)
    {
        if (!$fileName || empty($fileName))
            $fileName='expandpay.log';

        $log = new \Log($fileName);
        $log->write('[' . $type . '] ' . json_encode($contents));
    }

    // for testing only
    public function expandpay2(){
        $this->template = 'extension/payment/expandpay2.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        
        $this->response->setOutput($this->render_ecwig());

    }

    public function expandpayConfigureMyfatoorah(){
        $this->template = 'extension/payment/expandpayConfigureMyfatoorah.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        
        $this->response->setOutput($this->render_ecwig());

    }

    public function expandpayConfigureFawaterak(){
        $this->template = 'extension/payment/expandpayConfigureFawaterak.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        
        $this->response->setOutput($this->render_ecwig());

    }
}







?>