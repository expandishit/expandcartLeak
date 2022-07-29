<?php
class ControllerApiCustomer extends Controller {
    private $error = array();

	public function index() {
		$this->load->language('api/customer');

		// Delete past customer in case there is an error
		unset($this->session->data['customer']);

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
			// Add keys for missing post vars
			$keys = array(
				'customer_id',
				'customer_group_id',
				'firstname',
				'lastname',
				'email',
				'telephone',
				'fax'
			);

			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}

			// Customer
			if ($this->request->post['customer_id']) {
				$this->load->model('account/customer');

				$customer_info = $this->model_account_customer->getCustomer($this->request->post['customer_id']);

				if (!$customer_info || !$this->customer->login($customer_info['email'], '', true)) {
					$json['error']['warning'] = $this->language->get('error_customer');
				}
			}

			if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
				$json['error']['firstname'] = $this->language->get('error_firstname');
			}

			if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
				$json['error']['lastname'] = $this->language->get('error_lastname');
			}

			if ((utf8_strlen($this->request->post['email']) > 96) || (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email']))) {
				$json['error']['email'] = $this->language->get('error_email');
			}

			if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
				$json['error']['telephone'] = $this->language->get('error_telephone');
			}

			// Customer Group
			if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
				$customer_group_id = $this->request->post['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}

			// Custom field validation
			$this->load->model('account/custom_field');

			$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

			foreach ($custom_fields as $custom_field) {
				if (($custom_field['location'] == 'account') && $custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
					$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
				}
			}

			if (!$json) {
				$this->session->data['customer'] = array(
					'customer_id'       => $this->request->post['customer_id'],
					'customer_group_id' => $customer_group_id,
					'firstname'         => $this->request->post['firstname'],
					'lastname'          => $this->request->post['lastname'],
					'email'             => $this->request->post['email'],
					'telephone'         => $this->request->post['telephone'],
					'fax'               => $this->request->post['fax'],
					'custom_field'      => isset($this->request->post['custom_field']) ? $this->request->post['custom_field'] : array()
				);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function login() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
           
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                ####################################
                $this->load->model('account/customer');
                $this->language->load('account/login');
                if ($this->customer->isLogged()) {
                    $customer_info = $this->model_account_customer->getCustomerByEmail($params->email);
                    if($customer_info){
                        $json['is_logged'] = true;
                        $json['customer'] = $customer_info;    
                    }else{
                        $json['is_logged'] = false;
                    }
                } else {
                   
                    $customer_info = $this->model_account_customer->getCustomerByEmail($params->email);
                    $seller_approved=1;
                    if($customer_info && $this->MsLoader->isInstalled()){
                    $this->load->model('multiseller/seller');
                    $seller_data = $this->model_multiseller_seller->getSeller($customer_info['customer_id']);
                    if($seller_data) $seller_approved=$seller_data['seller_status'];
                    else  $seller_approved=1;
                    }
                    
                    if ($customer_info && (!$customer_info['status'] || $seller_approved!=1)) {
                        $json['error'] = $this->language->get('error_approved');
                    }
                    else{
                        if (!$this->customer->login($params->email, $params->password)) {
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

                        // Default Shipping Address
                        $this->load->model('account/address');

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

                }
                #####################################
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

    public function loginByUsername() {

        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token ?: $this->request->post['token'];
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                http_response_code(400);
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                ####################################
                $this->load->model('account/customer');
                $this->language->load('api/login');
                $this->load->model('module/signup');

                $username = $params->username ?: $this->request->post['username'];
                $password = $params->password ?: $this->request->post['password'];



                if ($this->customer->isLogged()) {
                    $customer_info = $this->model_account_customer->getCustomerByMobileOrEmail($username);
                    $json['is_logged'] = true;
                    $json['customer'] = $customer_info;
                } else {
                    // $loginByMobile = (int)$this->model_module_signup->isLoginRegisterByPhoneNumber();
                    // if ($loginByMobile && !$this->customer->login($username, $password, false)){
                    //     $json['error'] = $this->language->get('error_login_username');
                    // } elseif (!$loginByMobile) {
                    //     if (
                    //         filter_var($username, FILTER_VALIDATE_EMAIL)
                    //         &&
                    //         !$this->customer->login($username, $password, true)
                    //     ) {
                    //         $json['error'] = $this->language->get('error_login_username');
                    //     } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL) && !$this->customer->login($username, $password, false)) {
                    //         $json['error'] = $this->language->get('error_login_username');
                    //     }
                    // }
                    
                    
                    // check if customer try to login by email or phone 
                    if (!$this->customer->login($username, $password, !!filter_var($username, FILTER_VALIDATE_EMAIL))) {
                        $json['error'] = $this->language->get('error_login_username');
                    }
                    
                    if (!array_key_exists('error', $json)) {
                        $customer_info = $this->model_account_customer->getCustomerByMobileOrEmail($username);

                        if ($customer_info && !$customer_info['approved']) {
                            $json['error'] = $this->language->get('error_approved');
                        } else {
                            $json['is_logged'] = true;
                            $json['customer'] = $customer_info;
                        }

                    } else {
                        http_response_code(422);
                        $json['is_logged'] = false;
                    }


                    if ($json['is_logged']) {

                        // Default Shipping Address
                        $this->load->model('account/address');

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
                }
                #####################################
                $this->model_account_api->updateSession($encodedtoken);

            }
        } catch (Exception $ex) {
            http_response_code(500);
            $json['error'] = 'Something Went Wrong!';
        }

        $this->response->setOutput(json_encode($json));

    }

    public function register() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                #####################################
                $this->language->load('account/register');
                $this->load->model('account/customer');
                $this->load->model('account/signup');
                $this->load->model('setting/mobile');
                $this->load->model('multiseller/seller');
                $this->load->model('module/trips');
                $displayPhone = $this->model_setting_mobile->getSetting("phonefielddisplay") == "1";
                $phoneRequired = $this->model_setting_mobile->getSetting("phonefieldrequired") == "1";
                $register_login_by_phone_number = $this->model_account_signup->isLoginRegisterByPhonenumber();

                if ($this->customer->isLogged()) {
                    $customer_info = $this->model_account_customer->getCustomerByMobileOrEmail(
                        ($register_login_by_phone_number)?$params->phone:$params->email
                    );
                    $json['success'] = "logged in";
                    $json['customer'] = $customer_info;
                } else {
                    //Validate email/phone
                    $errors = array();
                    if(empty($params->firstname)) {
                        $errors['firstname']= 'empty';
                    }

                    if (empty($params->lastname) && !$this->model_module_trips->isTripsAppInstalled()) {
                        $errors['lastname']= 'empty';
                    }

                    if (empty($params->email)) {
                        if (!$displayPhone && !$phoneRequired && !$register_login_by_phone_number){
                            $errors['email']= 'empty';
                        }elseif (empty($params->phone)){
                            $errors['phone']= 'empty';
                        }

                    }
                    if (empty($params->password)) {
                        $errors['password']= 'empty';
                    }
                    if ((empty($params->phone))) {
                        if (($displayPhone && $phoneRequired && $register_login_by_phone_number) 
                        || $this->model_module_trips->isTripsAppInstalled()){
                            $errors['phone']= 'empty';
                        }elseif (empty($params->email)){
                            $errors['email']= 'empty';
                        }
                    }

                    if($register_login_by_phone_number && !empty($params->phone)){
                        if ($this->model_account_customer->getTotalCustomersByPhone($params->phone)) {
                            $errors['phone'] = "exists";
                        }
                    }else{
                        $isPhonenumberUnique = $this->model_account_signup->isPhonenumberUnique();
                        if($isPhonenumberUnique){
                            if ($this->model_account_customer->getTotalCustomersByPhone($params->phone)) {
                                $errors['phone'] = 'exists';
                            }
                        }

                        if (!empty($params->email) && $this->model_account_customer->getTotalCustomersByEmail($params->email)) {
                            $errors['email'] = "exists";
                        }
                    }

                    // Check email notifications
                    if ($this->config->get('config_account_mail'))
                    {
                        // Check if protocol is SMTP
                        if ($this->config->get('config_mail_protocol') == "smtp" &&
                            (empty($this->config->get('config_smtp_host')) || empty($this->config->get('config_smtp_username'))))
                        {
                            $errors['mail_server'] = "mail server not configured";
                        }

                    }
                    /////////////////////////

                    if(count($errors)) {
                        $json['error'] = $errors;
                    } else {
                        $data = array();
                        $modData = array();

                        $data['firstname'] = $params->firstname;
                        $data['lastname'] = $params->lastname;
                        $data['email'] = $params->email;
                        $data['telephone'] = $params->phone;
                        $data['password'] = $params->password;
                        $data['customer_group_id'] = $params->customer_group_id;
                        
                        if($register_login_by_phone_number) {
                            $emailOrPhone = $params->phone;
                        }else{
                            $emailOrPhone = $params->email;
                        }

                        $modData['f_name_show'] = true;
                        $modData['l_name_show'] = true;


                        $this->model_account_customer->addCustomer($data, $modData, true);

                        // check for mobile first order coupon

                        // send notification 

                        // the third parameter return boolean true in case login by mobile false in case mail
                        if (!$this->customer->login($emailOrPhone, $params->password , ($register_login_by_phone_number)?false:true)) {
                            $json['error'] = $this->language->get('error_login');
                        }
                        $customer_info = $this->model_account_customer->getCustomerByMobileOrEmail($emailOrPhone);

                        if ($customer_info && !$customer_info['approved']) {
                            $json['error'] = $this->language->get('error_approved');
                        }

                        if (!$json['error']) {
                            $json['is_logged'] = true;
                            $json['customer'] = $customer_info;
                        } else {
                            $json['is_logged'] = false;
                        }

                        if ($json['is_logged']) {
                            unset($this->session->data['guest']);

                            // Default Shipping Address
                            $this->load->model('account/address');

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

                    }
                }
                #####################################

                $this->model_account_api->updateSession($encodedtoken);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {
            $json['error'] = $ex->getMessage();
            $this->response->setOutput(json_encode($json));
        }
    }

    public function edit() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->language->load('account/edit');
                $this->load->model('account/customer');

                if($this->customer->isLogged()) {
                    $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

                    $customer_info['firstname'] = isset($params->firstname) && !empty($params->firstname) ? $params->firstname : $customer_info['firstname'];
                    $customer_info['lastname'] = isset($params->lastname) && !empty($params->lastname) ? $params->lastname : $customer_info['lastname'];
                    $customer_info['email'] = isset($params->email) && !empty($params->email) ? $params->email : $customer_info['email'];
                    $customer_info['telephone'] = isset($params->phone) && !empty($params->phone) ? $params->phone : $customer_info['telephone'];
                    $customer_info['gender'] = isset($params->gender) && !empty($params->gender) ? $params->gender : $customer_info['gender'];
                    $customer_info['dob'] = isset($params->dob) && !empty($params->dob) ? $params->dob : $customer_info['dob'];
                    $customer_info['company'] = isset($params->company) && !empty($params->company) ? $params->company : $customer_info['company'];
                    $customer_info['newsletter'] = isset($params->newsletter) && !empty($params->newsletter) ? $params->newsletter : $customer_info['newsletter'];

                    $this->model_account_customer->editCustomer($customer_info);

                    if(isset($params->password) && !empty($params->password)) {
                        $this->model_account_customer->editPassword($customer_info['email'], $params->password);
                    }

                    $json['success'] = $this->language->get('text_success');
                    $json['customer'] = $customer_info;
                } else {
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

    public function updateFirebaseToken()
    {
        $this->load->language('api/cart');
        $this->load->language('api/coupon');
        $json = array();

        $params = json_decode(file_get_contents('php://input'));
        $encodedtoken = $params->token;
        $this->load->model('account/api');
        $this->language->load('account/edit');

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $token = $params->firebase_token ?: $this->request->post['firebase_token'];
        $type = $params->type ?: $this->request->post['type'];
        $first_time = $params->type ?: $this->request->post['first_time'];

        if ($type != 'ios' && $type != 'android') {
            $this->response->setOutput(json_encode([
                'error' => ['warning' => $this->language->get('invalid_type')]
            ]));
            return;
        }

        if ($this->customer->isLogged()) {
            $this->load->model('account/customer');
            $this->load->model('account/coupon');
            $this->model_account_customer->updateFirebaseToken($this->customer->getId(), $token, $type);
            if($first_time){
                // get notify coupons
                $notifiable_coupons = $this->model_account_coupon->getNotfiableCoupons();
                $data['notification']['customer_id'] = $this->customer->getId();

                if(count($notifiable_coupons) > 1){
                    $data['notification']['title'] = $this->language->get('text_use_coupons');
                }
                else{
                    $data['notification']['title'] = $this->language->get('text_use_coupon');
                }

                foreach ($notifiable_coupons as $coupon) {
                    // send coupon notification
                    $data['notification']['name'] .= $this->language->get('text_coupon_name')." : ".$coupon['name']." , ".$this->language->get('text_coupon_code')." : ".$coupon['code']." , ";
                    $data['notification']['body'] .= $this->language->get('text_coupon_name')." : ".$coupon['name']." , ".$this->language->get('text_coupon_code')." : ".$coupon['code']." , ";
                    $data['notification']['message_type'] .= 'Coupon Code is '.$coupon['code'].' ';
                }

                $push = $this->model_account_coupon->pushNotification(array($token),$data);
                $this->model_account_coupon->insertNotification($data['notification']);
            }

            $json['success'] = $this->language->get('text_success');
        } else {
            $json['error'] = "not logged in";
        }

        $this->model_account_api->updateSession($encodedtoken);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        $this->response->setOutput(json_encode($json));
    }

    public function notifications() {
        $json = array();

        if (!isset($this->session->data['api_id'])) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $params = json_decode(file_get_contents('php://input'));
            $this->load->model('account/customer');
            $json['notifications'] = $this->model_account_customer->getCustomerNotifications($this->customer->getId());
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function changePassword() {
        try {
            $this->language->load('account/password');
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } 
            else {
                $this->load->model('account/customer');
                $this->load->model('account/signup');
                if($this->customer->isLogged()) {
                    $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
                    $isActive2 = $this->model_account_signup->isActiveMod();
                    $isActive1 = $isActive2['enablemod'];
                    $modData1 = $this->model_account_signup->getModData();
                    $isValid = $this->validatePassword($customer_info, $params->current_password, $params->new_password, $params->confirm_new_password, $modData1, $isActive1);
                    if($isValid){
                        $this->model_account_customer->editPasswordById($this->customer->getId(), $params->new_password);
                        $json['success'] = $this->language->get('text_success');
                        $json['customer'] = $customer_info;
                    }
                    else{
                        $json['error'] = $this->error;                        
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

    protected function validatePassword($customer_info, $current_password, $new_password, $confirm_new_password, $modData, $isActive) {
        $this->load->model('account/signup');
        // check if login by email or phone
        $isPhoneLogin = $this->model_account_signup->isLoginRegisterByPhonenumber();
        if($isPhoneLogin)
            $logged = $this->customer->login($customer_info['telephone'], $current_password, false);
        else
            $logged = $this->customer->login($customer_info['email'], $current_password);
        // check current password
        if (!$logged) {
            $this->error['warning'] = $this->language->get('error_current_pass');
        }  

        if ($confirm_new_password != $new_password) {
            $this->error['confirm'] = $this->language->get('error_confirm');
        }

        if($isActive && $modData['pass_fix'] && (utf8_strlen($new_password) != $modData['pass_fix'])) {
            $this->error['password'] = $this->language->get('text_reg_pass_must_be_of') . $modData['pass_fix'] . $this->language->get('text_reg_chars');
        } else if($isActive && !$modData['pass_fix'] && $modData['pass_min'] &&  $modData['pass_max'] && ((utf8_strlen($new_password) < $modData['pass_min']) || (utf8_strlen($new_password) > $modData['pass_max']))) {
            $this->error['password'] = $this->language->get('text_reg_pass_must_be_bet') . $modData['pass_min'] . $this->language->get('text_reg_and') . $modData['pass_max'] . $this->language->get('text_reg_chars');
        } else if($isActive && !$modData['pass_min'] && !$modData['pass_fix'] && !$modData['pass_max'] && ((utf8_strlen($new_password) < 4) || (utf8_strlen($new_password) > 20))) {
            $this->error['password'] = $this->language->get('error_password');
        } else if(!$isActive  && ((utf8_strlen($new_password) < 4) || (utf8_strlen($new_password) > 20))) {
            $this->error['password'] = $this->language->get('error_password');
        }
 
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
