<?php

class ControllerSellerAccountAds extends ControllerSellerAccount {
		
	public function index() {

		$this->load->model('setting/setting');
		$this->load->model('localisation/language');
		$this->load->model('module/seller_ads');
		$this->language->load_json('module/seller_ads');

		$this->data['link_back'] = $this->url->link('account/account', '', 'SSL');
		
		$this->document->setTitle($this->language->get('ms_account_ads_heading'));
		
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_dashboard_breadcrumbs'), $seller_title ),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),			
			array(
				'text' => $this->language->get('ms_account_ads_breadcrumbs'),
				'href' => $this->url->link('seller/account-ads', '', 'SSL'),
			)
		));

		$this->data['ms_account_ads_heading'] = $this->language->get('ms_account_ads_heading');
		$this->data['ms_account_ads_manage'] = $this->language->get('ms_account_ads_manage');
		$this->data['ms_account_add_ad'] = $this->language->get('ms_account_add_ad');
		$this->data['ms_account_ad_title'] = $this->language->get('ms_account_ad_title');
		$this->data['ms_account_ad_img'] = $this->language->get('ms_account_ad_img');
		$this->data['ms_account_ad_payment_method'] = $this->language->get('ms_account_ad_payment_method');
		$this->data['ms_account_credit_card'] = $this->language->get('ms_account_credit_card');
		$this->data['ms_account_ad_card_name'] = $this->language->get('ms_account_ad_card_name');
		$this->data['ms_account_ad_card_number'] = $this->language->get('ms_account_ad_card_number');
		$this->data['ms_account_ad_exp_date'] = $this->language->get('ms_account_ad_exp_date');
		$this->data['ms_account_ad_exp_year'] = $this->language->get('ms_account_ad_exp_year');
		$this->data['ms_account_ad_exp_month'] = $this->language->get('ms_account_ad_exp_month');
		$this->data['ms_account_ad_ads'] = $this->language->get('ms_account_ad_ads');
		$this->data['ms_account_ad_type'] = $this->language->get('ms_account_ad_type');
		$this->data['ms_account_ad_status'] = $this->language->get('ms_account_ad_status');
		$this->data['ms_account_ad_start_date'] = $this->language->get('ms_account_ad_start_date');
		$this->data['heading'] = $this->language->get('ms_account_ads_manage');
		$this->data['ms_account_ad_title'] = $this->language->get('ms_account_ad_title');
		$this->data['ms_account_ad_link'] = $this->language->get('ms_account_ad_link');
		$this->data['ms_account_ad_img'] = $this->language->get('ms_account_ad_img');
		$this->data['ms_account_ad_payment_method'] = $this->language->get('ms_account_ad_payment_method');
		$this->data['ms_account_ad_package'] = $this->language->get('ms_account_ad_package');
		$this->data['ms_account_ad_my_ads'] = $this->language->get('ms_account_ad_my_ads');
		$this->data['ms_account_ad_active'] = $this->language->get('ms_account_ad_active');
		$this->data['ms_account_ad_expired'] = $this->language->get('ms_account_ad_expired');
		$this->data['ms_account_ad_added'] = $this->language->get('ms_account_ad_added');

		$this->data["seller_ads_settings"] = $this->model_setting_setting->getSetting('seller_ads');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['default_language_id'] = $this->config->get('config_language_id');
		$this->data['default_currency'] = $this->config->get('config_currency');
		$this->data["seller_ads_settings"]['square_ad_price'] = $this->currency->format(
			$this->data["seller_ads_settings"]['square_ad_price'],
			$this->config->get('config_currency'),
			1.00000, 
			false
		);

		$this->data["seller_ads_settings"]['banner_ad_price'] = $this->currency->format(
			$this->data["seller_ads_settings"]['banner_ad_price'],
			$this->config->get('config_currency'),
			1.00000, 
			false
		);
		
		$this->data['form_action'] = $this->url->link('seller/account-ads/saveAd');

		$this->data['seller_ads'] = $this->model_module_seller_ads->getSellerAds($this->customer->getId());
		$this->data['seller_ads'] = array_map(function($ad) {
			$ad['edit_page'] = $this->url->link('seller/account-ads/editAd&ad_id=' . $ad['id']);
			$ad['title'] = unserialize($ad['title']);
			$ad['image'] = \Filesystem::getUrl($ad['image']);
			return $ad;
		}, $this->data['seller_ads']);
		
		$this->document->addScript('expandish/view/javascript/jquery/tabs.js');

		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-ads');
		$this->response->setOutput($this->render());
	}


	public function saveAd() 
	{
		$this->load->model('setting/setting');
		$this->load->model('module/seller_ads');
		$seller_ad_setttings = $this->model_setting_setting->getSetting('seller_ads');
		$request_data = $this->request->post;
		$request_data['ad_img'] = $_FILES['ad_img'];

		$errors = $this->validateSaveAdData($request_data);
		if (!empty($errors)) {
			return $this->response->setOutput(json_encode([
				'status' => false,
				'err_type' => 'validation_err',
				'errors' => $errors,
			]));
		}
		
		$ad_package = $this->model_module_seller_ads->getAdPackage($request_data['ad_type']);
		$ad_package_price = $seller_ad_setttings[$request_data['ad_type'] . '_ad_price'];

		$ad_img = $this->uploadImage($request_data['ad_img']);
		if (!$ad_img) {
			return $this->response->setOutput(json_encode([
				'status' => false,
				'err_type' => 'validation_err',
				'error_ad_img' => $this->language->get('error_ad_img_invalid')
			]));
		}
		$ad_package_price = $this->currency->format($ad_package_price, $this->config->get('config_currency'), 1.00000, false);
		$response = $this->mastercardProccess($ad_package_price, [
			'card_name' => $request_data['card_name'],
			'card_number' => $request_data['card_number'],
			'cvc' => $request_data['cvc'],
			'expire_year' => $request_data['expire_year'],
			'expire_month' => $request_data['expire_month']
		]);
		if (!$response['status']) {
			return $this->response->setOutput(json_encode([
				'status' => false,
				'err_type' => 'payment_err',
				'msg' => $response['msg']
			]));
		}

		$this->model_module_seller_ads->createNewAdSubscription([
			'seller_ads_package_id' => $ad_package['id'],
			'seller_id' => $this->customer->getId(),
			'start_date' => date("Y-m-d"),
			'expire_date' => date('Y-m-d', strtotime(date("Y-m-d") . " + {$seller_ad_setttings[$request_data['ad_type'] . '_ad_display_days']} days")),
			'link' => $request_data['ad_link'],
			'title' => serialize($request_data['languages']),
			'image' => $ad_img['path']
		]);

		return $this->response->setOutput(json_encode([
			'status' => true,
			'msg' => $this->language->get('ms_account_ad_added')
		]));
	}


	private function uploadImage($file)
    {
        $filename = $file["name"];
        $ext = substr(strrchr($filename, "."), 1);
        if (strtolower(trim($ext)) == 'jpeg' || strtolower(trim($ext)) == 'jpg' || strtolower(trim($ext)) == 'png' || strtolower(trim($ext)) == 'gif') {
            $image_name = uniqid();
            $filename = $image_name . '.' . $ext;
			$fullFilename = "image/modules/seller_ads/" . $filename;
			
            return \Filesystem::setPath($fullFilename)->upload($file["tmp_name"]);
        } else {
            return '';
        }
	}

	private function validateSaveAdData($data) 
	{
		$this->language->load_json('module/seller_ads');
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$errors = [];

		foreach ($languages as $language) {
			if (!isset($data['languages'][$language['language_id']]['ad_title']) || empty($data['languages'][$language['language_id']]['ad_title'])) {
				$errors['error_ad_title' . $language['language_id']] = $this->language->get('error_ad_title');
			}
		}
		
		if (!isset($data['ad_link']) || empty($data['ad_link'])) {
			$errors['error_ad_link'] = $this->language->get('error_ad_link');
		}
		
		if (!isset($data['ad_img']) || empty($data['ad_img'])) {
			$errors['error_ad_img'] = $this->language->get('error_ad_img');
		}

		if (!isset($data['ad_type']) || empty($data['ad_type'])) {
			$errors['error_ad_type'] = $this->language->get('error_ad_type');
		}

		if (!isset($data['payment_method']) || empty($data['payment_method'])) {
			$errors['error_ad_payment'] = $this->language->get('error_ad_payment');
		} else { 
			if (!isset($data['card_name']) || empty($data['card_name'])) {
				$errors['error_ad_card_name'] = $this->language->get('error_ad_card_name');
			}
			if (!isset($data['card_number']) || empty($data['card_number'])) {
				$errors['error_ad_card_number'] = $this->language->get('error_ad_card_number');
			}
			if (!isset($data['cvc']) || empty($data['cvc'])) {
				$errors['error_ad_cvc'] = $this->language->get('error_ad_cvc');
			}
			if (!isset($data['expire_year']) || empty($data['expire_year'])) {
				$errors['error_ad_exp_year'] = $this->language->get('error_ad_exp_year');
			}
			if (!isset($data['expire_month']) || empty($data['expire_month'])) {
				$errors['error_ad_exp_month'] = $this->language->get('error_ad_exp_month');
			}
		}

		return $errors;
	}

	public function mastercardProccess($amount, $card_data)
    {
        $url = 'https://migs.mastercard.com.au/vpcdps'; 
        $amount = $this->currency->format($amount, $this->config->get('config_currency'), 1.00000, false);
        $amount = round($amount*100,2);

        $ref = time();

        $request_fields = [
			'vpc_Version'           => "1", 
			'vpc_Command'           => "pay",
			'vpc_AccessCode'        => $this->config->get('msconf_subscriptions_mastercard_accesscode'),
			'vpc_MerchTxnRef'       => $ref,
			'vpc_Merchant'          => $this->config->get('msconf_subscriptions_mastercard_merchant'),                                               

			'vpc_OrderInfo'         => $ref,
			'vpc_Amount'            => $amount,  // Force amount into cents. 
			'vpc_CardNum'           => str_replace(' ', '', $card_data['card_number']),
			'vpc_cardExp'           => substr($card_data['expire_year'],2,4).$card_data['expire_month'],
			'vpc_CardSecurityCode'  => $card_data['cvc'],
			'vpc_SecureHash'        => $this->config->get('msconf_subscriptions_mastercard_secret')
		];

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
        
        $json = array();
        
        if (curl_error($curl)) {
			curl_close($curl);
			return [
				'status' => false,
				'msg' => 'MIGS API CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl)
			];
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
                return [
					'status' => true
				];
            } else {
				return [
					'status' => false,
					'msg' => urldecode($result['vpc_Message'])
				];
            }
        } else {
			curl_close($curl);
			return [
				'status' => false,
				'msg' => 'Empty Gateway Response'
			];
        }
	}
	

	public function editAd() {
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');
		$this->load->model('module/seller_ads');
		$this->language->load_json('module/seller_ads');

		$this->data['link_back'] = $this->url->link('account/account', '', 'SSL');
		
		$this->document->setTitle($this->language->get('ms_account_ads_heading'));
		
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();
		$this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_dashboard_breadcrumbs'), $seller_title ),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),			
			array(
				'text' => $this->language->get('ms_account_ads_breadcrumbs'),
				'href' => $this->url->link('seller/account-ads', '', 'SSL'),
			)
		));

		$this->data['ms_account_ads_heading'] = $this->language->get('ms_account_ads_heading');
		$this->data['ms_account_ads_manage'] = $this->language->get('ms_account_ads_manage');
		$this->data['ms_account_add_ad'] = $this->language->get('ms_account_add_ad');
		$this->data['ms_account_ad_title'] = $this->language->get('ms_account_ad_title');
		$this->data['ms_account_ad_img'] = $this->language->get('ms_account_ad_img');
		$this->data['ms_account_ad_type'] = $this->language->get('ms_account_ad_type');
		$this->data['ms_account_ad_status'] = $this->language->get('ms_account_ad_status');
		$this->data['heading'] = $this->language->get('ms_account_ads_manage');
		$this->data['ms_account_ad_title'] = $this->language->get('ms_account_ad_title');
		$this->data['ms_account_ad_link'] = $this->language->get('ms_account_ad_link');
		$this->data['ms_account_ad_img'] = $this->language->get('ms_account_ad_img');
		$this->data['ms_account_ad_package'] = $this->language->get('ms_account_ad_package');
		$this->data['ms_account_ad_added'] = $this->language->get('ms_account_ad_added');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['default_language_id'] = $this->config->get('config_language_id');

		$this->data['form_action'] = $this->url->link('seller/account-ads/updateAd');

		$this->data['ad'] = $this->model_module_seller_ads->getSellerAd(
			$this->request->get['ad_id']
		);
		$this->data['ad']['title'] = unserialize($this->data['ad']['title']);

		$this->data['ad']['id'] = $this->request->get['ad_id'];

		$this->document->addScript('expandish/view/javascript/jquery/tabs.js');

		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-ads-edit');
		$this->response->setOutput($this->render());
	}

	public function updateAd() {
		$this->load->model('setting/setting');
		$this->load->model('module/seller_ads');
		$request_data = $this->request->post;
		$request_data['ad_img'] = $_FILES['ad_img'];

		$errors = $this->validateUpdateAdData($request_data);
		if (!empty($errors)) {
			return $this->response->setOutput(json_encode([
				'status' => false,
				'err_type' => 'validation_err',
				'errors' => $errors,
			]));
		}
		

		$ad_img = null;
		if (isset($request_data['ad_img']) && !empty($request_data['ad_img'])) {

			$ad_img = $this->uploadImage($request_data['ad_img']);
			if (!$ad_img) {
				return $this->response->setOutput(json_encode([
					'status' => false,
					'err_type' => 'validation_err',
					'error_ad_img' => $this->language->get('error_ad_img_invalid')
				]));
			}
		}	
		$this->model_module_seller_ads->updateAd([
			'id' => $request_data['ad_id'],
			'seller_id' => $this->customer->getId(),
			'link' => $request_data['ad_link'],
			'title' => serialize($request_data['languages']),
			'image' => $ad_img ? $ad_img['path'] : null
		]);

		return $this->response->setOutput(json_encode([
			'status' => true,
			'msg' => $this->language->get('ms_account_ad_added')
		]));
	}

	private function validateUpdateAdData($data) 
	{
		$this->language->load_json('module/seller_ads');
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$errors = [];

		foreach ($languages as $language) {
			if (!isset($data['languages'][$language['language_id']]['ad_title']) || empty($data['languages'][$language['language_id']]['ad_title'])) {
				$errors['error_ad_title' . $language['language_id']] = $this->language->get('error_ad_title');
			}
		}
		
		if (!isset($data['ad_link']) || empty($data['ad_link'])) {
			$errors['error_ad_link'] = $this->language->get('error_ad_link');
		}
		
		return $errors;
	}

}

?>
