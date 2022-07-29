<?php 
class ControllerAccountLogin extends Controller {
	private $error = array();
    
    private function logoutCurrentCustomer()
    {
        if ($this->customer->isLogged()) {
            $this->customer->logout();
            $this->cart->clear();
            unset($this->session->data['wishlist']);
            unset($this->session->data['shipping_address_id']);
            unset($this->session->data['shipping_country_id']);
            unset($this->session->data['shipping_zone_id']);
            unset($this->session->data['shipping_postcode']);
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_address_id']);
            unset($this->session->data['payment_country_id']);
            unset($this->session->data['payment_zone_id']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
        }
    }
    
    private function loginWith($customer)
    {
        return $this->identity->isLoginByPhone() ? ['telephone', $customer['telephone']] : ['email', $customer['email']];
    }
    
    private function validateToken(string $token = null)
    {
        if(empty($token)) return false;
        
		$this->load->model('account/customer');
        
		$customer = $this->model_account_customer->getCustomerByToken($token);
        
        if(!$customer) return false;
        
        $this->logoutCurrentCustomer();
        
        if (!$this->customer->attemptLogin($this->loginWith($customer), $customer['expand_id'])) return false;
        
        unset($this->session->data['guest']);
        
        // Default Shipping & payment Address
        $this->load->model('account/address');

        $address = $this->model_account_address->getAddress($this->customer->getAddressId());

        if ($address) {
            if ($this->config->get('config_tax_customer') == 'shipping') {
                $this->session->data['shipping_country_id'] = $address['country_id'];
                $this->session->data['shipping_zone_id'] = $address['zone_id'];
                $this->session->data['shipping_area_id'] = $address['area_id'];
                $this->session->data['shipping_location'] = $address['location'];
                $this->session->data['shipping_postcode'] = $address['postcode'];
            }

            if ($this->config->get('config_tax_customer') == 'payment') {
                $this->session->data['payment_country_id'] = $address['country_id'];
                $this->session->data['payment_zone_id'] = $address['zone_id'];
                $this->session->data['payment_area_id'] = $address['area_id'];
                $this->session->data['payment_location'] = $address['location'];
            }
        } else {
            unset($this->session->data['shipping_country_id'],
            $this->session->data['shipping_zone_id'],
            $this->session->data['shipping_area_id'],
            $this->session->data['shipping_location'],
            $this->session->data['shipping_postcode'],
            $this->session->data['payment_country_id'],
            $this->session->data['payment_zone_id'],
            $this->session->data['payment_location'],
            $this->session->data['payment_area_id']);
        }
        
        // successfully logged in
        return true;
    }
    
    private function handleLoginAction()
    {
        $validateToken = $this->validateToken($this->request->get['token']);
        
        if($validateToken) {
            $this->redirect($this->url->link('account/account', '', 'SSL'));
        } else {
            $this->redirect($this->url->link('common/home', 'sign_in=1', 'SSL'));
        }
        return;
    }
    
	public function index() {
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            return $this->handleLoginAction();
        }
        
		$this->load->model('account/customer');

		$this->load->model('account/signup');
		$this->data['register_login_by_phone_number'] =  $this->model_account_signup->isLoginRegisterByPhonenumber();
		
		// Login override for admin users
		if (!empty($this->request->get['token'])) {
			$this->customer->logout();
			$this->cart->clear();

            unset($this->session->data['stock_forecasting_cart']);
			unset($this->session->data['wishlist']);
			unset($this->session->data['shipping_address_id']);
			unset($this->session->data['shipping_country_id']);
			unset($this->session->data['shipping_zone_id']);
			unset($this->session->data['shipping_postcode']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_address_id']);
			unset($this->session->data['payment_country_id']);
			unset($this->session->data['payment_zone_id']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			
			$customer_info = $this->model_account_customer->getCustomerByToken($this->request->get['token']);
			
			$this->load->model('account/signup');
			$this->data['register_login_by_phone_number'] =  $this->model_account_signup->isLoginRegisterByPhonenumber();
			
			if($this->data['register_login_by_phone_number'])
				$check_override = $this->customer->login($customer_info['telephone'], '', false,true);
			else
				$check_override = $this->customer->login($customer_info['email'], '', true,true);

		 	if ($customer_info && $check_override) {
				// Default Addresses
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
                $this->redirect($this->url->link('account/account', '', 'SSL'));
                //                $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
                //
                //                if($queryMultiseller->num_rows) {
                //                    if ($this->MsLoader->MsSeller->isCustomerSeller($this->customer->getId())) {
                //                        $this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
                //                    } else {
                //                        $this->redirect($this->url->link('account/account', '', 'SSL'));
                //                    }
                //                } else {
                //                    $this->redirect($this->url->link('account/account', '', 'SSL'));
                //                }
			}
		}

        if ($this->customer->getApprovalStatus() > 2) {
            $this->redirect($this->url->link('account/activation/status'));
        }

		if ($this->customer->isLogged()) {

            $this->redirect($this->url->link('account/account', '', 'SSL'));
            //            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
            //
            //            if($queryMultiseller->num_rows) {
            //                if ($this->MsLoader->MsSeller->isCustomerSeller($this->customer->getId())) {
            //                    $this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
            //                } else {
            //                    $this->redirect($this->url->link('account/account', '', 'SSL'));
            //                }
            //            } else {
            //                $this->redirect($this->url->link('account/account', '', 'SSL'));
            //            }
    	}

        $this->initializer([
            'security/throttling',
            'module/google_captcha/settings'
        ]);

        $this->data['recaptcha'] = [
            'status' => $this->settings->isActive(),
            'site-key' => $this->settings->reCaptchaSiteKey(),
            "page_enabled_status"=>$this->settings->getPageStatus("client_login")
        ];


        if ($this->data['recaptcha']['status'] == 1 AND $this->data['recaptcha']['page_enabled_status'] == 1) {

            $this->data['languageCode'] = $this->config->get('config_language');

            $this->data['recaptchaFormSelector'] = 'login';

            $this->data['recaptchaAction'] = 'login';

            $this->document->addInlineScript(function () {
                return $this->renderDefaultTemplate('template/security/recaptcha.expand');
            });
        }

	    if($this->request->get['seller'] == 1){
	        $this->data['accountType'] = 'seller';
            $this->data['action'] = $this->url->link('account/login&seller=1', '', 'SSL');
        }else{
            $this->data['action'] = $this->url->link('account/login', '', 'SSL');
        }

    	$this->language->load_json('account/login',true);

    	$this->document->setTitle($this->language->get('heading_title'));
        
        $country_phone_code_login = $this->data['country_phone_code_login'] = $this->model_account_signup->isCountriesPhonesCodesLogin();
        $login_register_phonenumber_enabled = $this->data['login_register_phonenumber_enabled'] = $this->model_account_signup->isLoginRegisterByPhonenumber();
        $this->data['country_id'] = $this->config->get('config_country_id');
        if($country_phone_code_login && $login_register_phonenumber_enabled){
            $this->document->addScript("expandish/view/javascript/common/country_phone_code.js");
        }
								
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            if(isset($this->request->post['telephone']) && $country_phone_code_login && $login_register_phonenumber_enabled ){
                // custom registration app login by phone -- allaw display countries list in login/signup
                $telephone = $this->request->post['telephoneCode'].$this->request->post['telephone'];
                $this->request->post['email'] = $telephone;
            }

		    if (!$this->validate()) {

		        if ($this->throttling->throttlingStatus() == true) {

                    $throttlingSettings = $this->throttling->getSettings();

                    $throttleCache = [];

                    $user = $this->customer->getRealIp();

                    $resource = 'account/login';

                    $throttleCache[$user][$resource][] = time();

                    if (isset($this->session->data['throttling']) == false) {
                        $this->session->data['throttling'] = [];
                        $this->session->data['throttling']['count'] = 1;
                    } else {
                        $this->session->data['throttling']['count']++;
                    }

                    $this->session->data['throttling']['data'][$user][$resource][] = time();

                    if ($this->session->data['throttling']['count'] >= $throttlingSettings['throttling_limit']) {
                        $bannedData = [
                            'ipv4' => $user,
                            'resource' => $resource,
                            'attempts' => $throttlingSettings['throttling_limit'],
                            'recaptcha_status' => $throttlingSettings['enable_recaptcha'],
                        ];

                        $this->throttling->banIp($bannedData);

                        unset($this->session->data['throttling']);
                    }
                }
            } else {
                unset($this->session->data['guest']);

                if ($this->customer->getApprovalStatus() > 2) {
                    $this->redirect($this->url->link('account/activation/status'));
                }

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

                if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
                    $this->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
                } else {
                  
                    $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

                    if($queryMultiseller->num_rows && $this->MsLoader->MsSeller->isCustomerSeller($this->customer->getId())) {
                       $this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
                    } 
                    $this->redirect($this->url->link('account/account', '', 'SSL'));
                }
            }
    	}

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),       	
        	'separator' => false
      	);
  
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_login'),
			'href'      => $this->url->link('account/login', '', 'SSL'),      	
        	'separator' => $this->language->get('text_separator')
      	);
				

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		//$this->data['register'] = $this->url->link('account/register', '', 'SSL');
		//$this->data['forgotten'] = $this->url->link('account/forgotten', '', 'SSL');

        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            if ($this->config->get('msconf_enable_one_page_seller_registration')) {
                $this->language->load('multiseller/multiseller');
                $this->data['register_seller'] = $this->url->link('seller/register-seller', '', 'SSL');
                //Get Seller title 
                $lng_id = $this->config->get('config_language_id');
                $seller_title = ($this->config->get('msconf_seller_title'))[$lng_id]['single'];
                $this->data['ms_button_register_seller'] = $this->language->get('ms_button_register_seller');
            }
        }

		if (isset($this->request->request['redirect']) && (strpos($this->request->request['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->request['redirect'], $this->config->get('config_ssl')) !== false)) {
			$this->data['redirect'] = $this->request->request['redirect'];
		}elseif (isset($this->session->data['redirectWithParams'])) {
            $this->data['redirect'] = $this->session->data['redirectWithParams'];
            unset($this->session->data['redirectWithParams']);
        }elseif (isset($this->session->data['redirect'])) {
      		$this->data['redirect'] = $this->session->data['redirect'];
	  		
			unset($this->session->data['redirect']);		  	
    	}else {
			$this->data['redirect'] = '';
		}
        if($this->session->data['cart_redirect']){
            $this->data['redirect'] = $this->session->data['cart_redirect'];
            unset($this->session->data['cart_redirect']);	
        }

        if (strpos($this->data['redirect'], 'account/logout') !== false)
            $this->data['redirect'] = $this->url->link('account/account', '', 'SSL');


		if (isset($this->session->data['success'])) {
    		$this->data['success'] = $this->session->data['success'];
    
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		}else {
			$this->data['email'] = '';
		}

		if (isset($this->request->post['password'])) {
			$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}

        $this->data['countries_phones_codes_enabled'] = $this->model_account_signup->isCountriesPhonesCodes();
        $this->data['country_id'] = $this->config->get('config_country_id');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/login.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/login.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/login.expand';
        }

		#######################social login app#########################################
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getExtensions('module');

        foreach ($extensions as $extension) {
            if ($extension['code'] == 'd_social_login') {
                $settings = $this->config->get('d_social_login_settings');

                if ($settings) {
                    if ($settings['status']) {
                        $this->data[$extension['code'] . '_enabled'] = true;
                        $this->data[$extension['code']] = $this->getChild('module/' . $extension['code']);
                        break;
                    }
                }
            }
        }
        //$this->data['d_social_login'] = $this->getChild('module/d_social_login');
        #######################social login app#########################################
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
						
		$this->response->setOutput($this->render_ecwig());
  	}
  
  	protected function validate() {

		
		$this->load->model('account/signup');
		$register_login_by_phone_number =  $this->model_account_signup->isLoginRegisterByPhonenumber();
        $loginData = $this->customer->login($this->request->post['email'], $this->request->post['password'],!$register_login_by_phone_number);

		if ($loginData == false) {
			$error_msg_key = 'error_login';
			if($register_login_by_phone_number)
				$error_msg_key = 'error_login_telephone';
			$this->error['warning'] = $this->language->get($error_msg_key);
    	}else if( is_array($loginData)&& $loginData['activation_status'] == 0){
		    if($loginData['activation_type']  == 1){
                $this->error['warning'] = $this->language->get('error_activated');
            }else{
                $this->error['warning'] = $this->language->get('error_activated').'<a href="'.$this->url->link('account/activation/status&customer_id='.$loginData['customer_id'], '', 'SSL').'">'.$this->language->get('text_activate').' </a>';

            }
        }

		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);


    	if ($customer_info && !$customer_info['approved']) {
      		$this->error['warning'] = $this->language->get('error_approved');
    	}

        /**
         * in seller login form make sure that the user logged in with a seller account.
         */
        if ($this->request->post['accountType'] == 'seller' && !$this->error) {
            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
            if ($queryMultiseller->num_rows && !$this->MsLoader->MsSeller->isCustomerSeller($this->customer->getId()) ) {
                $this->error['warning'] =  $this->language->get('error_seller_account_needed');
                $this->customer->logout();
            }
        }


        if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}  	
  	}
}
?>
