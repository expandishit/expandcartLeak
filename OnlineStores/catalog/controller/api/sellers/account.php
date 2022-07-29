<?php
include_once '../jwt_helper.php';
class ControllerApiSellersAccount extends Controller
{
    public function register() {

        $this->loadModelsAndLangs();
        try {
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;         
            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $register_login_by_phone_number = $this->model_account_signup->isLoginRegisterByPhonenumber();              
                if ($this->customer->isLogged()) {
                    $customer_info = $this->model_account_customer->getCustomerByMobileOrEmail(
                        ($register_login_by_phone_number)?$params->phone:$params->email
                    );
                    $json['success'] = "logged in";
                    $json['customer'] = $customer_info;
                } else {
   
                    $errors = $this->validate();
                    if(count($errors)) {
                        $json['error'] = $errors;
                    } else {
                        $customerData = array();
                        $modData = array();

                        $customerData['firstname'] = $params->firstname;
                        $customerData['lastname'] = $params->lastname;
                        $customerData['email'] = $params->email;
                        $customerData['password'] = $params->password;
                        $customerData['telephone'] = $params->phone;
                        if($register_login_by_phone_number) {
                            $customerData['telephone'] = $params->phone;
                            $modData['mob_show'] = true;
                            $emailOrPhone = $params->phone;
                        }else{
                            $emailOrPhone = $params->email;
                        }
                      
                        $modData['f_name_show'] = true;
                        $modData['l_name_show'] = true;

                        $this->model_account_customer->addCustomer($customerData, $modData, true);
                        $customer_info = $this->model_account_customer->getCustomerByMobileOrEmail($emailOrPhone);
                       
                        $sellerParams=$this->sellerRegisterParameters();
                        if($this->model_module_trips->isTripsAppInstalled()){
                            $sellerParams['nickname']=$params->firstname;
                            $tripsParams=$this->tripsRegisterParameters();
                            $tripsParams['customer_id']= $customer_info['customer_id'];
                            $tripsParams['car_license']= $this->model_multiseller_seller->uploadImagebase64($tripsParams['car_license'],$customer_info['customer_id']);
                            $tripsParams['driving_license']= $this->model_multiseller_seller->uploadImagebase64($tripsParams['driving_license'],$customer_info['customer_id']);
                            $tripsParams['tourism_license']= $this->model_multiseller_seller->uploadImagebase64($tripsParams['tourism_license'],$customer_info['customer_id']);
                            $this->model_module_trips->addTripCustomer($tripsParams);

                        }
                         ///Create Seller Acount      
                         $sellerParams['seller_id']= $customer_info['customer_id'];
                         $sellerParams['avatar_name']= $this->model_multiseller_seller->uploadImagebase64($sellerParams['avatar_name'],$customer_info['customer_id']);
                         $this->createSellerAccount($sellerParams);  

                        if ($customer_info &&(!$customer_info['approved'] || $this->config->get('msconf_seller_validation')==MsSeller::MS_SELLER_VALIDATION_APPROVAL)) {
                            $json['error'] = $this->language->get('error_approved');
                        }
                        else{
                             // the third parameter return boolean true in case login by mobile false in case mail
                        if (!$this->customer->login($emailOrPhone, $params->password , ($register_login_by_phone_number)?false:true)) {
                            $json['error'] = $this->language->get('error_login');
                        }
                            
                        }              
                        if (!$json['error']) {
                            $json['is_logged'] = true;
                            $json['customer'] = $customer_info;
                        } else {
                            $json['is_logged'] = false;
                        }

                        if ($json['is_logged']) {
                            unset($this->session->data['guest']);
                           $this->defaultShippingAdress();

                        }

                    }
                }
    
                $this->model_account_api->updateSession($encodedtoken);
                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');
                $this->response->AddHeader('Content-Type','multipart/form-data');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {

        }
    }
    public function sellerRegisterParameters()
    {
        $params = json_decode(file_get_contents('php://input'));
        $sellerData['nickname']=$params->nickname;
        $sellerData['mobile']=$params->phone;
        $sellerData['tax_card']=$params->tax_card;
        $sellerData['website']=$params->website;
        $sellerData['commercial_reg']=$params->commercial_reg;
        $sellerData['rec_exp_date']=$params->rec_exp_date;
        $sellerData['license_num']=$params->license_num;
        $sellerData['lcn_exp_date']=$params->lcn_exp_date;
        $sellerData['personal_id']=$params->personal_id;
        $sellerData['bank_name']=$params->bank_name;
        $sellerData['bank_iban']=$params->bank_iban;
        $sellerData['bank_transfer']=$params->bank_transfer;
        $sellerData['company']=$params->company;
        $sellerData['description']=$params->description;
        $sellerData['paypal']=$params->paypal;
        $sellerData['avatar_name']=$params->avatar;
        $sellerData['commercial_image_name']=$params->commercial_image_name;
        $sellerData['license_image_name']=$params->license_image_name;
        $sellerData['tax_image_name']=$params->tax_image_name;
        $sellerData['country_id']=$params->country_id;
        $sellerData['zone_id']=$params->zone_id;
        $sellerData['custom_fields']=$params->custom_fields;
        return $sellerData;

    }
    public function tripsRegisterParameters()
    {
        $params = json_decode(file_get_contents('php://input'));
        $tripsData['area_id']=$params->area_id;
        $tripsData['category_id']=$params->category_id;
        $tripsData['car_license']=$params->car_license;
        $tripsData['driving_license']=$params->driving_license;
        $tripsData['tourism_license']=$params->tourism_license;
        $tripsData['car_type']=$params->car_type;    
        return $tripsData;

    }
    public function validate()
    {

        $customerParams = json_decode(file_get_contents('php://input'));
        $sellerParams=$this->sellerRegisterParameters();
        $tripsCustomerParams=$this->tripsRegisterParameters();

        $encodedtoken = $params->token;    
        $displayPhone = $this->model_setting_mobile->getSetting("phonefielddisplay") == "1";
        $phoneRequired = $this->model_setting_mobile->getSetting("phonefieldrequired") == "1";
        $register_login_by_phone_number = $this->model_account_signup->isLoginRegisterByPhonenumber();
        //Validate email/phone
        $errors = array();
      
        if(empty($customerParams->firstname)) {
            $errors['firstname']= $this->language->get('error_firstname');
        }  
        if (empty($customerParams->email)){
            $errors['email']= $this->language->get('error_email');
        }
        if ((empty($customerParams->phone))) {
            
            $errors['phone']= $this->language->get('error_telephone');
        }
       elseif ((utf8_strlen($customerParams->email) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $customerParams->email)) 
        {
            if (!$displayPhone && !$phoneRequired && !$register_login_by_phone_number){      
                $errors['email']= $this->language->get('error_email');
            }elseif (empty($customerParams->phone)){
                $errors['phone']= $this->language->get('error_telephone');
            }
    
        }
        if ((utf8_strlen($customerParams->password) < 4) || (utf8_strlen($customerParams->password) > 20))
         {
            $errors['password']= $this->language->get('error_password');
         }  
         
        if($register_login_by_phone_number && !empty($customerParams->phone)){
            if ($this->model_account_customer->getTotalCustomersByPhone($customerParams->phone)) {
                $errors['phone'] = "exists";
            }
        }else{
            $isPhonenumberUnique = $this->model_account_signup->isPhonenumberUnique();
            if($isPhonenumberUnique){
                if ($this->model_account_customer->getTotalCustomersByPhone($customerParams->phone)) {
                    $errors['phone'] = "exists";
                }
            }
    
            if (!empty($customerParams->email) && $this->model_account_customer->getTotalCustomersByEmail($customerParams->email)) {
                $errors['email'] = $this->language->get('error_exists');
            }
        }
      
        if(!$this->model_module_trips->isTripsAppInstalled())
        {         
            if (empty($customerParams->lastname))
             {
                $errors['lastname']= $this->language->get('error_lastname');
             }
             // VAT Validation 
             $this->load->helper('vat');
             if ($this->config->get('config_vat') && $customerParams->tax_id && (vat_validation($country_info['iso_code_2'], $customerParams->tax_id) == 'invalid')) 
             {
                 $errors['tax_id'] = $this->language->get('error_vat');
             }
           
           if ($customerParams->country_id == '' || !is_numeric($customerParams->country_id))
            {
                  $errors['country_id'] =$this->language->get('error_country');
            }
           
           if (!isset($customerParams->zone_id) || $customerParams->zone_id== '' || !is_numeric($customerParams->zone_id)) 
           {
                   $errors['zone'] = $this->language->get('error_zone');
           }
            if (empty($sellerParams['nickname']) ) {
                $errors['nickname'] = $this->language->get('ms_error_sellerinfo_nickname_empty');
            } else if (mb_strlen($sellerParams['nickname']) < 4 || mb_strlen($sellerParams['nickname']) > 128 ) {
                $errors['nickname'] = $this->language->get('ms_error_sellerinfo_nickname_length');
            } else if ($this->MsLoader->MsSeller->nicknameTaken($sellerParams['nickname'])) {
                $errors['nickname'] = $this->language->get('ms_error_sellerinfo_nickname_taken');
            } else {
			switch($this->config->get('msconf_nickname_rules'))
            {
				case 1:
					// extended latin
					if(!preg_match("/^[a-zA-Z0-9_\-\s\x{00C0}-\x{017F}]+$/u", $sellerParams['nickname'])) {
						$errors['nickname'] = $this->language->get('ms_error_sellerinfo_nickname_latin');
					}
					break;
					
				case 2:
					// utf8
					if(!preg_match("/((?:[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}){1,100})./x", $data['nickname'])) {
						$errors['nickname'] = $this->language->get('ms_error_sellerinfo_nickname_utf8');
					}
					break;
					
				case 0:
				default:
					// alnum
					if(!preg_match("/^[a-zA-Z0-9_\-\s]+$/", $sellerParams['nickname'])) {
						$errors['nickname'] = $this->language->get('ms_error_sellerinfo_nickname_alphanumeric');
					}
					break;
			}
		 } 
            if (mb_strlen($sellerParams['company']) > 50 ) {

                $errors['company'] = $this->language->get('ms_error_sellerinfo_company_length');
            }
            
            if (mb_strlen($sellerParams['description']) > 1500) {
                $errors['description'] = $this->language->get('ms_error_sellerinfo_description_length');
            }
            
            if (($sellerParams['paypal'] != "") && ((utf8_strlen($sellerParams['paypal']) > 128) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $data['paypal']))) {
                $errors['paypal'] = $this->language->get('ms_error_sellerinfo_paypal');
            }
       }
        if ($this->config->get('msconf_seller_terms_page')) {
			$information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));
			if ($information_info && !isset($customerParams->terms)) {
				$errors['terms'] = htmlspecialchars_decode(sprintf($this->language->get('ms_error_sellerinfo_terms'), $information_info['title']));
			}
		}

       if($this->model_module_trips->isTripsAppInstalled()){
        
         if ((empty($sellerParams['personal_id']))) {
           
            $errors['personal_id']= $this->language->get('ms_error_personal_id');
         }
         if ((empty($sellerParams['bank_iban']))) {

            $errors['bank_iban']= $this->language->get('ms_error_bank_iban');     
         }
         if ((empty($sellerParams['avatar_name']))) {
           
            $errors['avatar']= $this->language->get('ms_error_avatar');
         }
        
         if ((empty($tripsCustomerParams['category_id']))) {
           
            $errors['category_id']= $this->language->get('ms_error_category');
         }
         if ((empty($tripsCustomerParams['car_license']))) {
           
            $errors['car_license']= $this->language->get('ms_error_car_license');
         }
         if ((empty($tripsCustomerParams['driving_license']))) {
           
            $errors['driving_license']= $this->language->get('ms_error_driving_license');
         }
         if ((empty($tripsCustomerParams['tourism_license']))) {
           
            $errors['tourism_license']= $this->language->get('ms_error_tourism_license');
         }
         if ((empty($tripsCustomerParams['car_type']))) {
           
            $errors['car_type']= $this->language->get('ms_error_car_type');
         }
        

      }

        return $errors;
    }
    public function defaultShippingAdress()
    {

       $address_info = $this->model_account_address->getAddress($this->customer->getAddressId());

        if ($address_info) {
            if ($this->config->get('config_tax_customer') == 'shipping') {
                $this->session->data['shipping_country_id'] = $address_info['country_id'];
                $this->session->data['shipping_zone_id'] = $address_info['zone_id'];
                $this->session->data['shipping_postcode'] = $address_info['postcode'];
            }

            if ($this->config->get('config_tax_customer') == 'payment') {
                $this->session->data['payment_country_id'] = $address_info['country_id'];
                $this->session->data['payment_zone_id'] = $address_info['zone_id'];
            }
        } else {
            unset($this->session->data['shipping_country_id']);
            unset($this->session->data['shipping_zone_id']);
            unset($this->session->data['shipping_postcode']);
            unset($this->session->data['payment_country_id']);
            unset($this->session->data['payment_zone_id']);
        }
    }
    public function createSellerAccount($sellerParams)
    {   
        
        if(!$this->model_module_trips->isTripsAppInstalled())
        {         
        switch ($this->config->get('msconf_seller_validation')) {
            case MsSeller::MS_SELLER_VALIDATION_APPROVAL:
                $mails[] = array(
                    'type' => MsMail::SMT_SELLER_ACCOUNT_AWAITING_MODERATION
                );
                $mails[] = array(
                    'type' => MsMail::AMT_SELLER_ACCOUNT_AWAITING_MODERATION,
                    'data' => array(
                        'message' => $this->session->data['seller_reviewer_message'],
                        'seller_name' => $seller_data['nickname'],
                        'customer_name' => $this->customer->getFirstname() . ' ' . $this->customer->getLastname(),
                        'customer_email' => $this->MsLoader->MsSeller->getSellerEmail($this->customer->getId())
                    )
                );
               
                break;
            
            case MsSeller::MS_SELLER_VALIDATION_NONE:
            default:
                $mails[] = array(
                    'type' => MsMail::SMT_SELLER_ACCOUNT_CREATED
                );
                $mails[] = array(
                    'type' => MsMail::AMT_SELLER_ACCOUNT_CREATED,
                    'data' => array(
                        'seller_name' => $sellerParams['nickname'],
                        'customer_name' => $params->firstname . ' ' . $params->lastname,
                        'customer_email' => $params->email
                    )
                );

                break;
        }
        $this->MsLoader->MsMail->sendMails($mails);
        }
        $this->MsLoader->MsSeller->createSeller($sellerParams);


        $commissions = $this->MsLoader->MsCommission->calculateCommission(array('seller_group_id' => $this->config->get('msconf_default_seller_group_id')));
        $fee = (float)$commissions[MsCommission::RATE_SIGNUP]['flat'];
        
        if ($fee > 0) {
            switch($commissions[MsCommission::RATE_SIGNUP]['payment_method']) {
                case MsPayment::METHOD_PAYPAL:
                    // initiate paypal payment
                    // set seller status to unpaid
                    $this->MsLoader->MsSeller->changeStatus($this->customer->getId(), MsSeller::STATUS_UNPAID);
                    
                    // unset seller profile creation emails
                    unset($mails[0]);
                    
                    // add payment details
                    $payment_id = $this->MsLoader->MsPayment->createPayment(array(
                        'seller_id' => $this->customer->getId(),
                        'payment_type' => MsPayment::TYPE_SIGNUP,
                        'payment_status' => MsPayment::STATUS_UNPAID,
                        'payment_method' => MsPayment::METHOD_PAYPAL,
                        'amount' => $fee,
                        'currency_id' => $this->currency->getId($this->config->get('config_currency')),
                        'currency_code' => $this->currency->getCode($this->config->get('config_currency')),
                        'description' => sprintf($this->language->get('ms_transaction_signup'), $this->config->get('config_name'))
                    ));
                    
                    // assign payment variables
                    $json['data']['amount'] = $this->currency->format($fee, $this->config->get('config_currency'), '', FALSE);
                    $json['data']['custom'] = $payment_id;
                    $payment['amount'] = $this->currency->format($fee, $this->config->get('config_currency'), '', FALSE);
                    $payment['custom'] = $payment_id;
                    $this->MsLoader->MsMail->sendMails($mails);
                    return $this->response->setOutput(json_encode($json));
                    //return $payment['amount'] . ',' . $payment['custom'];
                    //echo $payment['amount'] . ',' . $payment['custom'];
                    //return;
                    break;

                case MsPayment::METHOD_BALANCE:
                default:
                    // deduct from balance
                    $this->MsLoader->MsBalance->addBalanceEntry($this->customer->getId(),
                        array(
                            'balance_type' => MsBalance::MS_BALANCE_TYPE_SIGNUP,
                            'amount' => -$fee,
                            'description' => sprintf($this->language->get('ms_transaction_signup'), $this->config->get('config_name'))
                        )
                    );
                    
                    $this->MsLoader->MsMail->sendMails($mails);
                    break;
            }
        } else {
            $this->MsLoader->MsMail->sendMails($mails);
            //$this->redirect($this->url->link('seller/seller-success'));
        }

    }
    public function edit() 
    {

        $this->loadModelsAndLangs();
        try {
          
            $json = array();
            $errors = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                
                if($this->customer->isLogged()) {
                    $customer_id=$this->customer->getId();
                    $customer_info = $this->model_account_customer->getCustomer($customer_id);

                    $customer_info['firstname'] = isset($params->firstname) && !empty($params->firstname) ? $params->firstname : $customer_info['firstname'];
                    $customer_info['lastname'] = isset($params->lastname) && !empty($params->lastname) ? $params->lastname : $customer_info['lastname'];
                   ///validate email & password
                    if(isset($params->email) && !empty($params->email))
                    {
                        if ((utf8_strlen($params->email) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $params->email)) 
                        {
                            $errors['email']= $this->language->get('error_email');
                        }elseif($this->model_account_customer->getTotalCustomersByEmail($params->email)) {
                            $errors['email'] = $this->language->get('error_exists');
                        }
                        else {
                            $customer_info['email'] = $params->email;
                        }
                   }
                   if(isset($params->password) && !empty($params->password)) {
                    if ((utf8_strlen($params->password) < 4) || (utf8_strlen($params->password) > 20)) {
                        $errors['password']= $this->language->get('error_password');
                        }
                   }
                     
                    $customer_info['telephone'] = isset($params->phone) && !empty($params->phone) ? $params->phone : $customer_info['telephone'];

                    $seller_info=$this->model_multiseller_seller->getSeller($customer_id);
                    
                    $seller_info['nickname'] = isset($params->nickname) && !empty($params->nickname) ? $params->nickname : $seller_info['nickname'];
                    $seller_info['mobile'] = isset($params->phone) && !empty($params->phone) ? $params->phone : $seller_info['mobile'];
                    $seller_info['tax_card'] = isset($params->tax_card) && !empty($params->tax_card) ? $params->tax_card : $seller_info['tax_card'];
                    $seller_info['commercial_reg'] = isset($params->commercial_reg) && !empty($params->commercial_reg) ? $params->commercial_reg : $seller_info['commercial_reg'];
                    $seller_info['rec_exp_date'] = isset($params->rec_exp_date) && !empty($params->rec_exp_date) ? $params->rec_exp_date : $seller_info['rec_exp_date'];
                    $seller_info['lcn_exp_date'] = isset($params->lcn_exp_date) && !empty($params->lcn_exp_date) ? $params->lcn_exp_date : $seller_info['lcn_exp_date'];
                    $seller_info['personal_id'] = isset($params->personal_id) && !empty($params->personal_id) ? $params->personal_id : $seller_info['personal_id'];
                    $seller_info['bank_name'] = isset($params->bank_name) && !empty($params->bank_name) ? $params->bank_name : $seller_info['bank_name'];
                    $seller_info['bank_iban'] = isset($params->bank_iban) && !empty($params->bank_iban) ? $params->bank_iban : $seller_info['bank_iban'];
                    $seller_info['bank_transfer'] = isset($params->bank_transfer) && !empty($params->bank_transfer) ? $params->bank_transfer : $seller_info['bank_transfer'];
                    $seller_info['company'] = isset($params->company) && !empty($params->company) ? $params->company : $seller_info['company'];
                    $seller_info['description'] = isset($params->description) && !empty($params->description) ? $params->description : $seller_info['description'];
                    $seller_info['paypal'] = isset($params->paypal) && !empty($params->paypal) ? $params->paypal : $seller_info['company'];
                    $seller_info['country_id'] = isset($params->country_id) && !empty($params->country_id) ? $params->country_id : $seller_info['country_id'];
                    $seller_info['zone_id'] = isset($params->zone_id) && !empty($params->zone_id) ? $params->zone_id : $seller_info['zone_id'];
                   if(isset($params->avatar) && !empty($params->avatar)){
                    $seller_info['avatar_name']= $this->model_multiseller_seller->uploadImagebase64($params->avatar, $customer_id);
                   }else{$seller_info['avatar_name']=$seller_info['avatar'];}
                   
                   if($this->model_module_trips->isTripsAppInstalled()){
                    $driver_info=$this->model_module_trips->getDriver($customer_id);

                    $driver_info['area_id'] = isset($params->area_id) && !empty($params->area_id) ? $params->area_id : $driver_info['area_id'];
                    $driver_info['category_id'] = isset($params->category_id) && !empty($params->category_id) ? $params->category_id : $driver_info['category_id'];
                    $driver_info['car_type'] = isset($params->car_type) && !empty($params->car_type) ? $params->car_type : $driver_info['car_type'];

                    if(isset($params->car_license) && !empty($params->car_license)){
                        $driver_info['car_license']= $this->model_multiseller_seller->uploadImagebase64($params->car_license, $customer_id);
                       }else{$driver_info['car_license']=$driver_info['car_license'];}

                    if(isset($params->driving_license) && !empty($params->driving_license)){
                        $driver_info['driving_license']= $this->model_multiseller_seller->uploadImagebase64($params->driving_license, $customer_id);
                        }else{$driver_info['driving_license']=$driver_info['driving_license'];}

                    if(isset($params->tourism_license) && !empty($params->tourism_license)){
                        $driver_info['tourism_license']= $this->model_multiseller_seller->uploadImagebase64($params->tourism_license, $customer_id);
                        }else{$driver_info['tourism_license']=$driver_info['tourism_license'];}
                   }

                    if(count($errors)) {
                        $json['error'] = $errors;
                    } else {
                     $this->model_account_customer->editCustomer($customer_info);
                     $this->MsLoader->MsSeller->editSeller($seller_info);
                    if(isset($params->password) && !empty($params->password)) {
                        $this->model_account_customer->editPassword($customer_info['email'], $params->password);
                    }
                    if($this->model_module_trips->isTripsAppInstalled())
                    {
                    $this->model_module_trips->editDriver($driver_info);
                    $this->model_multiseller_seller->updateSellerStatus($customer_id,0);
                    $json['success'] = $this->language->get('ms_text_forced_logout');
                    }
                    else{ $customer_info=$this->model_multiseller_seller->get($customer_id);
                    $json['success'] = $this->language->get('ms_text_success');
                    $json['customer'] = $customer_info;}
               } 

            }
            else {
                $json['error'] = "not logged in";
            }

                $this->model_account_api->updateSession($encodedtoken);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            
        }
        } catch (Exception $ex) {

        }
    }
    public function get()
    {
        try {
            $this->load->language('api/login');

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $seller_id = $this->request->get['seller_id'];
                $results = [];

                if ($seller_id != null) {
                    $this->load->model('multiseller/seller');
                    $this->load->model('module/trips');
                    if($this->model_module_trips->isTripsAppInstalled())
                    $data = $this->model_module_trips->getTripCustomer($seller_id);
                    else $data = $this->model_multiseller_seller->get($seller_id);
                    
                    if (is_bool($data)) {
                        $results['message'] = 'failed! no such seller';
                        $results['data'] = [];
                        http_response_code(422);
                    }  elseif (is_string($data)) {
                        $results['message'] = 'error happened';
                        $results['data'] = [];
                        http_response_code(500);
                    } else {
                        $results['message'] = 'successful query';
                        $results['data'] = $data;
                    }
                } else {
                    $results['message'] = 'failed! seller_id is required';
                    $results['data'] = [];
                    http_response_code(422);
                }


                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');
                $this->response->setOutput(json_encode($results));
            }
        } catch (Exception $exception) {

        }

    }
    public function getSellerTermsPage()
    {
        try {
            $this->load->language('api/login');
            $json = array();
            $infoPage_id=$this->config->get('msconf_seller_terms_page');
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                if ($infoPage_id != 0) {
                    $this->load->model('catalog/information');

                    $information_info = $this->model_catalog_information->getInformation($infoPage_id);

                    if ($information_info) {
                        $json['InfoPage'] = array(
                            'Title' => $information_info['title'],
                            'Description' => html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8'),
                        );
                    }
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {

        }
    }
    public function loadModelsAndLangs(){
        
        $this->load->language('api/cart');
        $this->load->language('multiseller/multiseller');
        $this->load->model('account/api');
        $this->language->load('account/register');
        $this->load->model('account/customer');
        $this->load->model('account/signup');
        $this->load->model('setting/mobile');
        $this->load->model('account/address');
        $this->load->model('catalog/information');
        $this->load->model('setting/setting');
        $this->load->model('account/customer_group');
        $this->load->model('multiseller/seller');
        $this->load->model('module/trips');
        $this->load->model('localisation/country');
    }
   


}