<?php
set_time_limit(0);
class ControllerAccountRegister extends Controller {
	private $error = array();


	private function validateNetworkMarketing()
    {
        $this->language->load_json('network_marketing/messages');

        $this->load->model('network_marketing/settings');
        $this->load->model('network_marketing/agencies');
        $this->load->model('network_marketing/referrals');

        if ($this->model_network_marketing_settings->appStatus() === false) {
            return true;
        }

        if (isset($this->request->get['refid'])) {
            $refId = $this->request->get['refid'];
        } else if (isset($this->request->post['refid'])) {
            $refId = $this->request->post['refid'];
        } else {
            $refId = null;
        }

        $internalErrors = [];
        $refData = null;

        if (!$refId) {
        	return true;
		}

        if (!$this->model_network_marketing_agencies->validateRefId($refId)) {
            $internalErrors[] = $this->language->get('error_invalid_ref_id');
        }


        if (!($refData = $this->model_network_marketing_agencies->getAgencyByRefId($refId))) {
            $internalErrors[] = $this->language->get('error_ref_id_does_not_exists');
        }

        if ($refData) {
            $referrals = $this->model_network_marketing_referrals->getReferralsByAgency($refData['agency_id']);

            if (count($referrals) >= 2) {
                $internalErrors[] = $this->language->get('maximum_number_of_ref');
            }
        }

        if (count($internalErrors) > 0) {
            $this->error['warning'] = implode('<br />', $internalErrors);
            return false;
        }

        return $refData;
    }

    public function checkRefId()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {
            echo json_encode([
                'status' => 'error',
                'message' => 'unauthorized action'
            ]);
            exit;
        }

        if (!$this->validateNetworkMarketing()) {
            echo json_encode([
                'status' => 'error',
                'message' => $this->error['warning']
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'success'
        ]);
        exit;
    }

    public function index() {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->redirect($this->url->link('common/home', 'sign_in=1', 'SSL'));
            return;
        }
        
		if ($this->customer->isLogged()) {
	  		$this->redirect($this->url->link('account/account', '', 'SSL'));
    	}
		 
    	$this->language->load_json('account/register');

		$this->document->setTitle($this->language->get('heading_title'));
		//$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		//$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');

		$this->load->model('account/customer');
		$this->load->model('account/customer_group');
        $this->load->model('account/signup');
        $this->data['isActive'] = $this->model_account_signup->isActiveMod();
        $this->data['modData'] = $this->model_account_signup->getModData();

        //Show address text if count of all enabled Address fields in Account Signup module app > 0
        $this->data['showAddressText'] = (
	        $this->data['modData']['company_show'] + 
	        $this->data['modData']['address1_show'] + 
	        $this->data['modData']['address2_show'] + 
	        $this->data['modData']['city_show'] + 
	        $this->data['modData']['pin_show'] + 
	        $this->data['modData']['state_show'] + 
	        $this->data['modData']['country_show'] ) > 0 ? true : false;
        
        $modData1 = $this->model_account_signup->getModData();
        $isActive2 = $this->model_account_signup->isActiveMod();
		$isActive1 = $isActive2['enablemod'];
		$register_login_by_phone_number = $this->data['register_phone_number_enabled'] = $this->model_account_signup->isLoginRegisterByPhonenumber();
	        $this->data['countries_phones_codes_enabled'] = $this->model_account_signup->isCountriesPhonesCodes();

               if(\Extension::isInstalled('google_captcha')){
		$this->initializer([
			'security/throttling',
			'module/google_captcha/settings'
		]);

		$this->data['recaptcha'] = [
			'status' => $this->settings->isActive(),
			'site-key' => $this->settings->reCaptchaSiteKey(),
			"page_enabled_status"=>$this->settings->getPageStatus("client_registration")
		];


		if ($this->data['recaptcha']['status'] == 1 AND $this->data['recaptcha']['page_enabled_status'] == 1) {

			$this->data['languageCode'] = $this->config->get('config_language');

			$this->data['recaptchaFormSelector'] = 'signup';
			$this->data['querySelector'] = 'input.btn.btn-inline';


			$this->data['recaptchaAction'] = 'login';

			$this->document->addInlineScript(function () {
				return $this->renderDefaultTemplate('template/security/recaptcha.expand');
			});
		}
             }
        // start post
        if (
            ($this->request->server['REQUEST_METHOD'] == 'POST') &&
            $this->validate($modData1, $isActive1,$register_login_by_phone_number) &&
            ($refData = $this->validateNetworkMarketing())
        ) {
			$this->request->post['register_login_by_phone_number'] = $register_login_by_phone_number;
            $this->request->post['telephone'] = (!empty($this->request->post['telephoneCode']) && $this->isCountryCode($this->request->post['telephoneCode'])) ? $this->request->post['telephoneCode'].$this->request->post['telephone'] : $this->request->post['telephone'];
            
			
            // whatsapp  
            if (\Extension::isInstalled('whatsapp') || \Extension::isInstalled('whatsapp_cloud')) {
                //$this->initializer([
                //    'setting/setting'
                //]);
				//$this->setting->insertUpdateSetting('whatsapp', ['whatsapp_config_customer_allow_receive_messages'=> 
                //$this->request->post['whatsapp_allow_receive_messages'] ?? 0]);
				//$this->config->set('whatsapp_config_customer_allow_receive_messages',$this->request->post['whatsapp_allow_receive_messages'] ?? 0); 
				//we cant use config here , as the allow or not per user session 
				//$this->session->data['whatsapp_customer_allow_receive_messages'] = (int)($this->request->post['whatsapp_allow_receive_messages'] ?? 0);
            }
            
			$this->model_account_customer->addCustomer($this->request->post, $modData1, $isActive1);
			unset($this->request->post['register_login_by_phone_number']);

				
            if ($refData && is_array($refData)) {
                $this->model_network_marketing_referrals->newReferral(
                    $this->customer->getId(),
                    $refData['customer_id'],
                    $refData['agency_id']
                );
            }

            $this->initializer([
                'getResponse' => 'module/get_response/settings',
                'mailchimp' => 'module/mailchimp/settings',
            ]);

            if ($this->getResponse->isActive() && $this->getResponse->hasTag('register')) {
            	$this->getResponse->addContact($this->request->post, 'register');
			}

			if ($this->mailchimp->isActive() && $this->mailchimp->hasTag('register')) {

            	$subscriberHash = $this->mailchimp->getSubscriberHash(
            		$this->request->post['email']
				);

            	$this->mailchimp->addNewSubscriber($this->request->post, 'register', $subscriberHash);
			}

			unset($this->session->data['guest']);



			// Default Shipping Address
			if ($this->config->get('config_tax_customer') == 'shipping') {
				$this->session->data['shipping_country_id'] = (int)$this->request->post['country_id'];
				$this->session->data['shipping_zone_id'] = (int)$this->request->post['zone_id'];
				$this->session->data['shipping_area_id'] = (int)$this->request->post['area_id'];
				$this->session->data['shipping_postcode'] = $this->request->post['postcode'];
			}

			// Default Payment Address
			if ($this->config->get('config_tax_customer') == 'payment') {
				$this->session->data['payment_country_id'] = (int)$this->request->post['country_id'];
				$this->session->data['payment_zone_id'] = (int)$this->request->post['zone_id'];
			}

			// check if admin choose admin must approved on customer
			$customerIdAndGroupInfo = $this->model_account_customer->getCustomerIdAndGroupInfo();

            // check if game ball app installed
            if(\Extension::isInstalled('gameball')){
                $this->load->model('module/gameball/settings');
                // check if app status is active
                if($this->model_module_gameball_settings->isActive()){
                    $app_dir = str_replace('system/', 'expandish/', DIR_SYSTEM);
                    if($this->request->get['ReferralCode']){
                        $playerData['referrerCode'] = (string)$this->request->get['ReferralCode'];
                    }
                    $playerData['playerUniqueId'] = (string)$customerIdAndGroupInfo['customer_id'];
                    $playerData['playerAttributes']['displayName'] = $this->request->post['firstname'] ." ".$this->request->post['lastname'];
                    $playerData['playerAttributes']['mobileNumber'] = $this->request->post['telephone'];
                    $playerData['playerAttributes']['email'] = $this->request->post['email'];
                    $this->model_module_gameball_settings->addNewGameballPlayer($playerData);
                }
            }

			if(is_array($customerIdAndGroupInfo['customer_group_info']) && count($customerIdAndGroupInfo['customer_group_info']) > 0 && $customerIdAndGroupInfo['customer_group_info']['approval'] == 1) {
				// check if admin choose customer must activate his account
				if($customerIdAndGroupInfo['customer_group_info']['customer_verified'] == 1) {
					$this->redirect($this->url->link('account/activation/status&customer_id='.$customerIdAndGroupInfo['customer_id']));
				}else{
					if(isset($register_login_by_phone_number) && $register_login_by_phone_number)
						$this->customer->login($this->request->post['telephone'], $this->request->post['password'],false);
					else
						$this->customer->login($this->request->post['email'], $this->request->post['password']);
				}
			}else{
				// check if admin choose customer must activate his account
				if($customerIdAndGroupInfo['customer_group_info']['customer_verified'] == 1) {
					$this->redirect($this->url->link('account/activation/status&customer_id='.$customerIdAndGroupInfo['customer_id']));
				}

				if($customerIdAndGroupInfo['customer_group_info']['email_activation'] == 0){
				    $this->customer->login($this->request->post['email'], $this->request->post['password']);
				}
                                
				if(isset($register_login_by_phone_number) && $register_login_by_phone_number && $customerIdAndGroupInfo['customer_group_info']['sms_activation'] == 0){
				    $this->customer->login($this->request->post['telephone'], $this->request->post['password'],false);
				}
			}

            $data['notification_module']="customers";
            $data['notification_module_code']="customers_registered";
            $data['notification_module_id']=$this->customer->getId();
            $this->notifications->addAdminNotification($data);

	  		$this->redirect($this->url->link('account/success'));
        }
        // end of post

    	$this->data['seller_reg'] = $this->url->link('seller/register-seller', '', 'SSL');

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
        	'text'      => $this->language->get('text_register'),
			'href'      => $this->url->link('account/register', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);

      	$langId = $this->config->get('config_language_id');
      	$cstmLocals = $this->model_account_signup->gatLocals($langId);
      	$entryLocals  = array();
        
        if ( $cstmLocals )
        {
            foreach ($cstmLocals as $cstmLocal)
            {
                $entryLocals[$cstmLocal['key']] = $cstmLocal['value']; 
            }
        }

		
		//$this->data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', '', 'SSL'));

        $this->data['entry_firstname'] =($isActive1 && $entryLocals['f_name_'.$langId])?$entryLocals['f_name_'.$langId]:$this->language->get('entry_firstname');
        $this->data['entry_lastname'] = ($isActive1 && $entryLocals['l_name_'.$langId])?$entryLocals['l_name_'.$langId]:$this->language->get('entry_lastname');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_telephone'] = ($isActive1 && $entryLocals['mob_'.$langId])?$entryLocals['mob_'.$langId]:$this->language->get('entry_telephone');
        $this->data['entry_fax'] = ($isActive1 && $entryLocals['fax_'.$langId])?$entryLocals['fax_'.$langId]:$this->language->get('entry_fax');
        $this->data['entry_company'] = ($isActive1 && $entryLocals['company_'.$langId])?$entryLocals['company_'.$langId]:$this->language->get('entry_company');
        $this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
        $this->data['entry_company_id'] = ($isActive1 && $entryLocals['companyId_'.$langId])?$entryLocals['companyId_'.$langId]:$this->language->get('entry_company_id');
        $this->data['entry_tax_id'] = $this->language->get('entry_tax_id');
        $this->data['entry_address_1'] = ($isActive1 && $entryLocals['address1_'.$langId])?$entryLocals['address1_'.$langId]:$this->language->get('entry_address_1');
        $this->data['entry_address_2'] = ($isActive1 && $entryLocals['address2_'.$langId])?$entryLocals['address2_'.$langId]:$this->language->get('entry_address_2');
        $this->data['entry_postcode'] = ($isActive1 && $entryLocals['pin_'.$langId])?$entryLocals['pin_'.$langId]:$this->language->get('entry_postcode');
        $this->data['entry_city'] = ($isActive1 && $entryLocals['city_'.$langId])?$entryLocals['city_'.$langId]:$this->language->get('entry_city');
        $this->data['entry_country'] = ($isActive1 && $entryLocals['country_'.$langId])?$entryLocals['country_'.$langId]:$this->language->get('entry_country');
        $this->data['entry_zone'] = ($isActive1 && $entryLocals['state_'.$langId])?$entryLocals['state_'.$langId]:$this->language->get('entry_zone');
        $this->data['entry_area'] = ($isActive1 && $entryLocals['area_'.$langId])?$entryLocals['area_'.$langId]:$this->language->get('entry_area');
        $this->data['entry_newsletter'] = ($isActive1 && $entryLocals['subsribe_'.$langId])?$entryLocals['subsribe_'.$langId]:$this->language->get('entry_newsletter');
        $this->data['entry_password'] = $this->language->get('entry_password');
        $this->data['entry_confirm'] = ($isActive1 && $entryLocals['passconf_'.$langId])?$entryLocals['passconf_'.$langId]:$this->language->get('entry_confirm');
        $this->data['entry_dob'] = ($isActive1 && $entryLocals['dob_'.$langId])?$entryLocals['dob_'.$langId]:$this->language->get('entry_dob');
        $this->data['entry_gender'] = ($isActive1 && $entryLocals['gender_'.$langId])?$entryLocals['gender_'.$langId]:$this->language->get('entry_gender');
        $this->data['entry_gender_m'] = $this->language->get('entry_gender_m');
        $this->data['entry_gender_f'] = $this->language->get('entry_gender_f');

		//$this->data['button_continue'] = $this->language->get('button_continue');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}

		if (isset($this->error['password'])) {
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
		}

 		if (isset($this->error['confirm'])) {
			$this->data['error_confirm'] = $this->error['confirm'];
		} else {
			$this->data['error_confirm'] = '';
		}

        if (isset($this->error['company'])) {
            $this->data['error_company'] = $this->error['company'];
        } else {
            $this->data['error_company'] = '';
        }

  		if (isset($this->error['company_id'])) {
			$this->data['error_company_id'] = $this->error['company_id'];
		} else {
			$this->data['error_company_id'] = '';
		}

  		if (isset($this->error['tax_id'])) {
			$this->data['error_tax_id'] = $this->error['tax_id'];
		} else {
			$this->data['error_tax_id'] = '';
		}

  		if (isset($this->error['address_1'])) {
			$this->data['error_address_1'] = $this->error['address_1'];
		} else {
			$this->data['error_address_1'] = '';
		}

		if (isset($this->error['address_2'])) {
			$this->data['error_address_2'] = $this->error['address_2'];
		} else {
			$this->data['error_address_2'] = '';
		}

		if (isset($this->error['city'])) {
			$this->data['error_city'] = $this->error['city'];
		} else {
			$this->data['error_city'] = '';
		}

		if (isset($this->error['postcode'])) {
			$this->data['error_postcode'] = $this->error['postcode'];
		} else {
			$this->data['error_postcode'] = '';
		}

		if (isset($this->error['country'])) {
			$this->data['error_country'] = $this->error['country'];
		} else {
			$this->data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$this->data['error_zone'] = $this->error['zone'];
		} else {
			$this->data['error_zone'] = '';
		}
		if (isset($this->error['area'])) {
			$this->data['error_area'] = $this->error['area'];
		} else {
			$this->data['error_area'] = '';
		}
                
		if (isset($this->error['dob'])) {
			$this->data['error_dob'] = $this->error['dob'];
		} else {
			$this->data['error_dob'] = '';
		}
                
		if (isset($this->error['gender'])) {
			$this->data['error_gender'] = $this->error['gender'];
		} else {
			$this->data['error_gender'] = '';
		}

        if($this->request->get['ReferralCode']){
            $this->data['action'] = $this->url->link('account/register&ReferralCode='.$this->request->get['ReferralCode'], '', 'SSL');
        }else{
            $this->data['action'] = $this->url->link('account/register', '', 'SSL');
        }


		if (isset($this->request->post['firstname'])) {
    		$this->data['firstname'] = $this->request->post['firstname'];
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
    		$this->data['lastname'] = $this->request->post['lastname'];
		} else {
			$this->data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
    		$this->data['email'] = $this->request->post['email'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
    		$this->data['telephone'] = $this->request->post['telephone'];
		} else {
			$this->data['telephone'] = '';
		}

		if (isset($this->request->post['fax'])) {
    		$this->data['fax'] = $this->request->post['fax'];
		} else {
			$this->data['fax'] = '';
		}

		if (isset($this->request->post['company'])) {
    		$this->data['company'] = $this->request->post['company'];
		} else {
			$this->data['company'] = '';
		}
                
		if (isset($this->request->post['dob'])) {
    		$this->data['dob'] = $this->request->post['dob'];
		} else {
			$this->data['dob'] = '';
		}
                
		if (isset($this->request->post['gender'])) {
    		$this->data['gender'] = $this->request->post['gender'];
		} else {
			$this->data['gender'] = '';
		}



		$this->data['customer_groups'] = array();

		if (is_array($this->config->get('config_customer_group_display'))) {
			$customer_groups = $this->model_account_customer_group->getCustomerGroups();

			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$this->data['customer_groups'][] = $customer_group;
				}
			}
		}

		if (isset($this->request->post['customer_group_id'])) {
    		$this->data['customer_group_id'] = $this->request->post['customer_group_id'];
		} else {
			$this->data['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		// Company ID
		if (isset($this->request->post['company_id'])) {
    		$this->data['company_id'] = $this->request->post['company_id'];
		} else {
			$this->data['company_id'] = '';
		}

		// Tax ID
		if (isset($this->request->post['tax_id'])) {
    		$this->data['tax_id'] = $this->request->post['tax_id'];
		} else {
			$this->data['tax_id'] = '';
		}

		if (isset($this->request->post['address_1'])) {
    		$this->data['address_1'] = $this->request->post['address_1'];
		} else {
			$this->data['address_1'] = '';
		}

		if (isset($this->request->post['address_2'])) {
    		$this->data['address_2'] = $this->request->post['address_2'];
		} else {
			$this->data['address_2'] = '';
		}

		if (isset($this->request->post['postcode'])) {
    		$this->data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($this->session->data['shipping_postcode'])) {
			$this->data['postcode'] = $this->session->data['shipping_postcode'];
		} else {
			$this->data['postcode'] = '';
		}

		if (isset($this->request->post['city'])) {
    		$this->data['city'] = $this->request->post['city'];
		} else {
			$this->data['city'] = '';
		}

    	if (isset($this->request->post['country_id'])) {
      		$this->data['country_id'] = (int)$this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_country_id'])) {
			$this->data['country_id'] = $this->session->data['shipping_country_id'];
		} else {
      		$this->data['country_id'] = $this->config->get('config_country_id');
    	}

    	if (isset($this->request->post['zone_id'])) {
      		$this->data['zone_id'] = (int)$this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_zone_id'])) {
			$this->data['zone_id'] = $this->session->data['shipping_zone_id'];
		} else {
      		$this->data['zone_id'] = '';
    	}
		if (isset($this->request->post['area_id'])) {
				$this->data['area_id'] = (int)$this->request->post['area_id'];
		} elseif (isset($this->session->data['shipping_area_id'])) {
			$this->data['area_id'] = $this->session->data['shipping_area_id'];
		} else {
				$this->data['area_id'] = '';
		}

		$this->load->model('localisation/country');

    	$this->data['countries'] = $this->model_localisation_country->getCountries();

		if (isset($this->request->post['password'])) {
    		$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}

		if (isset($this->request->post['confirm'])) {
    		$this->data['confirm'] = $this->request->post['confirm'];
		} else {
			$this->data['confirm'] = '';
		}

		if (isset($this->request->post['newsletter'])) {
    		$this->data['newsletter'] = $this->request->post['newsletter'];
		} else {
			$this->data['newsletter'] = '';
		}

		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
			
			if ($information_info) {
				$this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information', 'information_id=' . trim($this->config->get('config_account_id')), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}

		if (isset($this->request->post['agree'])) {
      		$this->data['agree'] = $this->request->post['agree'];
		} else {
			$this->data['agree'] = false;
        }
        
        // whatsapp 
        if (\Extension::isInstalled('whatsapp') || \Extension::isInstalled('whatsapp_cloud')) {
            //this option currently removed 
			//$this->data['whatsapp_allow_receive_messages'] = (int)($this->config->get('whatsapp_config_customer_allow_receive_messages') ?? 0);
            //$this->data['text_whatsapp_allow_receive_messages'] = $this->language->get('text_whatsapp_allow_receive_messages');
        }

        $template_file_name = 'register.expand';

        if ($isActive1)
            $template_file_name = 'signup.expand';

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/' . $template_file_name)) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/account/' . $template_file_name;
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/account/' . $template_file_name;
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

        ######################################### Network marketing App #########################################
        $modules = array_column($extensions, null, 'code');
        if (isset($modules['network_marketing'])) {
            $networkMarketing['settings'] = $this->config->get('network_marketing');
            if (isset($networkMarketing['settings']) && $networkMarketing['settings']['nm_status'] == 1) {

                if (isset($this->request->post['network_marketing_referral'])) {
                    $this->data['network_marketing_referral'] =
                        $this->request->post['network_marketing_referral'];
                } else {
                    if ($networkMarketing['settings']['register_format'] == 1) {
                        $this->data['network_marketing_referral'] = false;
                    } else {
                        if (isset($this->request->get['refid'])) {
                            $this->data['network_marketing_referral'] = $this->request->get['refid'];
                        } else {
                            $this->data['network_marketing_referral'] = false;
                        }
                    }
                }

                $this->document->addScript('expandish/view/theme/default/js/modules/network_marketing.js');
                $this->document->addStyle('expandish/view/theme/default/css/modules/network_marketing.css');

                $networkMarketing['referralsLink'] = $this->url->link('account/register/checkRefId', '', 'SSL');

                $this->data['networkMarketing'] = $networkMarketing;
            }
        }
        ######################################### Network marketing App #########################################

        $url  = '';
        $url .= (isset($this->request->get['refid']) ? '&refid=' . $this->request->get['refid'] : null);

        $this->data['signupLink'] = $this->url->link('account/register' . $url, '', 'SSL');


		$this->children = array(
			'common/footer',
			'common/header'
		);

		$this->response->setOutput($this->render_ecwig());
  	}

  	protected function validate($modData, $isActive, $login_register_by_phonenumber = false)
	{
		$qc_settings = $this->config->get('quickcheckout');
		$skipLengthValidation = $qc_settings['general']['skip_length_validation'];

        if(\Extension::isInstalled('google_captcha') && $this->data['recaptcha']['status'] == 1 && $this->data['recaptcha']['page_enabled_status'] == 1){
            $recaptcha_url  = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_secret_key = $this->settings->reCaptchaSecreteKey();
            $captcha_token = $_POST['g-recaptcha-response'];
            if($captcha_token){
                $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret_key . '&response=' . $captcha_token);
                $recaptcha = json_decode($recaptcha);
                if($recaptcha->score < 0.5){
                    // Score less than 0.5 indicates suspicious activity. Return an error
                    $this->error['warning'] = $this->language->get('error_suspected_registery');
                }
                // if ($recaptcha->success == true && $recaptcha->score >= 0.5 && $recaptcha->action == 'contact') {
                // 	// This is a human. Insert the message into database OR send a mail
                // 	$success_output = "Your message sent successfully";
                // }
            }else{
                $this->error['warning'] = $this->language->get('error_suspected_registery');
            }
        }

		if($skipLengthValidation) return $this->validate_skip_length_validation($modData, $isActive, $login_register_by_phonenumber);
//		$this->config->load('quickcheckout_settings');
//		$settings = $this->config->get('quickcheckout_settings');
//		var_dump($qc_settings['general']['skip_length_validation']);die();
        if (
        	(!$isActive || ($isActive && $modData['f_name_req'] && $modData['f_name_show']))  &&
			(
				((utf8_strlen($this->request->post['firstname']) < 1) ||
				(utf8_strlen($this->request->post['firstname']) > 32))
				||
				(preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['firstname']) == false)
			)
		) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	/*if (preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['firstname']) == false) {
            $this->error['firstname'] = $this->language->get('error_firstname');
		}*/

        if (
        	(!$isActive || ($isActive && $modData['l_name_req'] && $modData['l_name_show'])) &&
			(
				((utf8_strlen($this->request->post['lastname']) < 1) ||
				(utf8_strlen($this->request->post['lastname']) > 32))
				||
				(preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['lastname']) == false)
			)
		) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}

        /*if (preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['lastname']) == false) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }*/

		if(($login_register_by_phonenumber && $this->request->post['email'] != "")
		|| !$login_register_by_phonenumber){
			if (
				(utf8_strlen($this->request->post['email']) > 96) ||
				!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])
			) {
				  $this->error['email'] = $this->language->get('error_email');
			}

			if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
				  $this->error['warning'] = $this->language->get('error_exists');
			}
		}
		
		// Mobile Phone Validations.
		// Cleaned by Michael.
		if ( 
			$isActive && 
			( $login_register_by_phonenumber ||
			($modData['mob_req'] == "1" && 
			$modData['mob_show'] == "1")) && 
			( !empty($modData['mob_fix']) && $modData['mob_fix'] != "0" ) && 
			utf8_strlen($this->request->post['telephone']) != $modData['mob_fix']
		)
		{
			$this->error['telephone'] = ($modData['mob_cstm'] ? $modData['mob_cstm'] : $this->language->get('text_reg_tel') ) . $this->language->get('text_reg_must_be_of') . $modData['mob_fix'] . $this->language->get('text_reg_chars');

		}
		else if
		(
			$isActive &&
			( $login_register_by_phonenumber ||
			($modData['mob_req'] == "1" &&
			$modData['mob_show'] == "1")) &&
			( empty($modData['mob_fix']) || !isset($modData['mob_fix']) || $modData['mob_fix'] == "0" ) &&
			( (empty($modData['mob_min']) || isset($modData['mob_min'])) || $modData['mob_min'] > 0 ) &&
			utf8_strlen($this->request->post['telephone']) < $modData['mob_min']
		)
		{
			$this->error['telephone'] = ($modData['mob_cstm'] ? $modData['mob_cstm'] : $this->language->get('text_reg_tel')). $this->language->get('text_reg_must_be_bet') . $modData['mob_min'] . $this->language->get('text_reg_and') . $modData['mob_max'] . $this->language->get('text_reg_chars');
		}
		else if
		(
			$isActive &&
			( $login_register_by_phonenumber ||
			($modData['mob_req'] == "1" &&
			$modData['mob_show'] == "1")) &&
			( empty($modData['mob_fix']) || !isset($modData['mob_fix']) || $modData['mob_fix'] == "0" ) &&
			( (empty($modData['mob_max']) || isset($modData['mob_max'])) || $modData['mob_max'] > 0 ) &&
			utf8_strlen($this->request->post['telephone']) > $modData['mob_max']
		)
		{
			$this->error['telephone'] = ($modData['mob_cstm'] ? $modData['mob_cstm'] : $this->language->get('text_reg_tel')). $this->language->get('text_reg_must_be_bet') . $modData['mob_min'] . $this->language->get('text_reg_and') . $modData['mob_max'] . $this->language->get('text_reg_chars');
		}
		else if 
		(
			$isActive && 
			( $login_register_by_phonenumber ||
			($modData['mob_req'] == "1" && 
			$modData['mob_show'] == "1")) && 
			( empty($modData['mob_max']) || !isset($modData['mob_max']) || $modData['mob_max'] == "0" ) && 
			( empty($modData['mob_fix']) || !isset($modData['mob_fix']) || $modData['mob_fix'] == "0" ) && 
			( empty($modData['mob_min']) || !isset($modData['mob_min']) || $modData['mob_min'] == "0" ) && 
			( utf8_strlen($this->request->post['telephone']) < 3 || utf8_strlen($this->request->post['telephone']) > 32 )
		) 
		{
			$this->error['telephone'] = $this->language->get('error_telephone');
		}
		else if
		(!$isActive &&  ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)))
		{
			$this->error['telephone'] = $this->language->get('error_telephone');
		}
		// if the telephone holds any other characters than numbers,
		// throw an error to the users face.
		if (
			!preg_match('/^\+?[0-9]*$/', $this->request->post['telephone']) &&
			($isActive == "1" && $modData['mob_req'] == "1" || $isActive == "0")
		)
		{
			$this->error['telephone'] = $this->language->get('error_telephone_not_numeric');
		}

		// By Bassem, duplicate validation
		// if($login_register_by_phonenumber){
		// 	if ($this->model_account_customer->getTotalCustomersByPhone($this->request->post['telephone'])) {
		// 		$this->error['warning'] = $this->language->get('error_phonenumber_exists');
		//   	}
		// }

		$this->load->model('account/signup');
        // check if merchant set option 'disallow phone Number duplication' to true and not empty telephone field
        // or sign-up type is telephone, if true set uniqueness validation
		$isPhonenumberUnique = (!empty($this->request->post['telephone']) && $this->model_account_signup->isPhonenumberUnique())
                                || (int)$login_register_by_phonenumber > 0;

		if($isPhonenumberUnique){
		    /*
		     * this condition for when found telephone code merge this code with phone to check full telephone number
		     */
            $telephone_check = $this->request->post['telephone'];
		    if (isset($this->request->post['telephone']) && isset($this->request->post['telephoneCode'])){
		        if ($this->isCountryCode($this->request->post['telephoneCode'])){
                    $telephone_check = $this->request->post['telephoneCode'].$this->request->post['telephone'] ;
                }else{
                    $telephone_check = $this->request->post['telephone'] ;
                }
            }
			if ($this->model_account_customer->getTotalCustomersByPhone($telephone_check)) {
				$this->error['warning'] = $this->language->get('error_phonenumber_exists');
			}
		}

		// Customer Group
		$this->load->model('account/customer_group');

		if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$customer_group = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		if ($customer_group) {
			// Company ID
			if ($customer_group['company_id_display'] && $customer_group['company_id_required'] && empty($this->request->post['company_id'])) {
				$this->error['company_id'] = $this->language->get('error_company_id');
			}

			// Tax ID
			if ($customer_group['tax_id_display'] && $customer_group['tax_id_required'] && empty($this->request->post['tax_id'])) {
				$this->error['tax_id'] = $this->language->get('error_tax_id');
			}
		}

        if ((!$isActive || ($isActive && $modData['address1_req'] && $modData['address1_show'])) && ((utf8_strlen($this->request->post['address_1']) < 3) || (utf8_strlen($this->request->post['address_1']) > 128))) {
            $this->error['address_1'] = $this->language->get('error_address_1');
        }

		if (( ($isActive && $modData['address2_req'] && $modData['address2_show'])) && ((utf8_strlen($this->request->post['address_2']) < 3) || (utf8_strlen($this->request->post['address_2']) > 128))) {
			$this->error['address_2'] = $this->language->get('error_address_2');
		}

        if ((!$isActive || ($isActive && $modData['city_req'] && $modData['city_show'])) && ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 128))) {
            $this->error['city'] = $this->language->get('error_city');
        }
        if (( ($isActive && $modData['company_req'] && $modData['company_show'])) && ((utf8_strlen($this->request->post['company']) < 2) || (utf8_strlen($this->request->post['company']) > 128))) {
            $this->error['company'] = $this->language->get('error_company');
        }

		$this->load->model('localisation/country');

        if(!$isActive || ($isActive && $modData['pin_req'] && $modData['pin_show'] && $modData['country_show']))
            $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

        if ((!$isActive || ($isActive && $modData['pin_req'] && $modData['pin_show'] && $modData['country_show'])) && $country_info) {

			if (( ($isActive && $modData['pin_req'] && $modData['pin_show'])) && ((utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 128))) {
					$this->error['postcode'] = $this->language->get('error_postcode');
			}

			// VAT Validation
			$this->load->helper('vat');

			if ($this->config->get('config_vat') && $this->request->post['tax_id'] && (vat_validation($country_info['iso_code_2'], $this->request->post['tax_id']) == 'invalid')) {
				$this->error['tax_id'] = $this->language->get('error_vat');
			}
		}

        if ((!$isActive || ($isActive && $modData['country_req'] && $modData['country_show'])) && $this->request->post['country_id'] == '') {
            $this->error['country'] = $this->language->get('error_country');
        }

        if ((!$isActive || ($isActive && $modData['state_req'] && $modData['state_show'] && $modData['country_show'] )) && (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '')) {
            $this->error['zone'] = $this->language->get('error_zone');
        }

        // area is checked here when custom registeration application is installed $isActive = 1
        // area field will not exist at the regisetration if none of "seller_based and custom registration" is installed
        if ( $isActive && $modData['area_req'] && $modData['area_show'] && $modData['state_show'] && (!isset($this->request->post['area_id']) || $this->request->post['area_id'] == '')) {
            $this->error['area'] = $this->language->get('error_area');
        }


		if (\Extension::isInstalled('seller_based') && $this->config->get('seller_based_status') == 1 && isset($this->request->post['area_id'])){
			if ((!$isActive || ($isActive && $modData['state_req'] && $modData['state_show'] && $modData['country_show'] )) && (!isset($this->request->post['area_id']) || $this->request->post['area_id'] == '')) {
				$this->error['area'] = $this->language->get('error_area');
			}
		}
        
		//this field apply at custom registration only 
		if($isActive){
        if ((!$isActive || ($isActive && $modData['dob_req'] && $modData['dob_show'])) && (!isset($this->request->post['dob']) || $this->request->post['dob'] == '')) {
            $this->error['dob'] = $this->language->get('error_dob');
        }
		}
        
		//this field apply at custom registration only 
		if($isActive){
        if ((!$isActive || ($isActive && $modData['gender_req'] && $modData['gender_show'])) && (!isset($this->request->post['gender']) || $this->request->post['gender'] == '')) {
            $this->error['gender'] = $this->language->get('error_gender');
        }
		}
		
        if($isActive && $modData['pass_fix'] && (utf8_strlen($this->request->post['password']) != $modData['pass_fix'])){
            $this->error['password'] = $this->language->get('text_reg_pass_must_be_of') . $modData['pass_fix'] . $this->language->get('text_reg_chars');
        }else if($isActive && !$modData['pass_fix'] && $modData['pass_min'] &&  $modData['pass_max'] && ((utf8_strlen($this->request->post['password']) < $modData['pass_min']) || (utf8_strlen($this->request->post['password']) > $modData['pass_max']))){
            $this->error['password'] = $this->language->get('text_reg_pass_must_be_bet') . $modData['pass_min'] . $this->language->get('text_reg_and') . $modData['pass_max'] . $this->language->get('text_reg_chars');
        }else if($isActive && !$modData['pass_min'] && !$modData['pass_fix'] && !$modData['pass_max'] && ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20))){
            $this->error['password'] = $this->language->get('error_password');
        }else if(!$isActive  && ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20))){
            $this->error['password'] = $this->language->get('error_password');
        }

        if ((!$isActive || ($isActive && $modData['passconf_show'] )) && $this->request->post['confirm'] != $this->request->post['password']) {
            $this->error['confirm'] = $this->language->get('error_confirm');
        }
        
		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
      			$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

    	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}

	protected function validate_skip_length_validation($modData, $isActive, $login_register_by_phonenumber = false)
	{
//		$qc_settings = $this->config->get('quickcheckout');
//		$this->config->load('quickcheckout_settings');
//		$settings = $this->config->get('quickcheckout_settings');
//		var_dump($qc_settings['general']['skip_length_validation']);die();
		if (
			(!$isActive || ($isActive && $modData['f_name_req'] && $modData['f_name_show']))  &&
			(
				(utf8_strlen($this->request->post['firstname']) < 1)
				||
				(preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['firstname']) == false)
			)
		) {
			$this->error['firstname'] = $this->language->get('error_required_field');
		}

		/*if (preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['firstname']) == false) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }*/

		if (
			(!$isActive || ($isActive && $modData['l_name_req'] && $modData['l_name_show'])) &&
			(
				(utf8_strlen($this->request->post['lastname']) < 1)
				||
				(preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['lastname']) == false)
			)
		) {
			$this->error['lastname'] = $this->language->get('error_required_field');
		}

		/*if (preg_match('#^([\p{Arabic}a-z\-\s]+)$#ui', $this->request->post['lastname']) == false) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }*/

		if(($login_register_by_phonenumber && $this->request->post['email'] != "")
			|| !$login_register_by_phonenumber){
			if (
				(utf8_strlen($this->request->post['email']) > 96) ||
				!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])
			) {
				$this->error['email'] = $this->language->get('error_email');
			}

			if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}

		// Mobile Phone Validations.
		// Cleaned by Michael.
		if (
			$isActive &&
			( $login_register_by_phonenumber ||
				($modData['mob_req'] == "1" &&
					$modData['mob_show'] == "1")) &&
			( !empty($modData['mob_fix']) && $modData['mob_fix'] != "0" ) &&
			utf8_strlen($this->request->post['telephone']) != $modData['mob_fix']
		)
		{
			$this->error['telephone'] = ($modData['mob_cstm'] ? $modData['mob_cstm'] : $this->language->get('text_reg_tel') ) . $this->language->get('text_reg_must_be_of') . $modData['mob_fix'] . $this->language->get('text_reg_chars');

		}
		else if
		(
			$isActive &&
			( $login_register_by_phonenumber ||
				($modData['mob_req'] == "1" &&
					$modData['mob_show'] == "1")) &&
			( empty($modData['mob_fix']) || !isset($modData['mob_fix']) || $modData['mob_fix'] == "0" ) &&
			( (empty($modData['mob_min']) || isset($modData['mob_min'])) && $modData['mob_min'] > 0 ) &&
			utf8_strlen($this->request->post['telephone']) < $modData['mob_min']
		)
		{
			$this->error['telephone'] = $this->language->get('error_required_field');
		}
		else if
		(
			$isActive &&
			( $login_register_by_phonenumber ||
				($modData['mob_req'] == "1" &&
					$modData['mob_show'] == "1")) &&
			( empty($modData['mob_fix']) || !isset($modData['mob_fix']) || $modData['mob_fix'] == "0" ) &&
			( (empty($modData['mob_max']) || isset($modData['mob_max'])) && $modData['mob_max'] > 0 ) &&
			utf8_strlen($this->request->post['telephone']) > $modData['mob_max']
		)
		{
			$this->error['telephone'] = $this->language->get('error_required_field');
		}
		else if
		(
			$isActive &&
			( $login_register_by_phonenumber ||
				($modData['mob_req'] == "1" &&
					$modData['mob_show'] == "1")) &&
			( empty($modData['mob_max']) || !isset($modData['mob_max']) || $modData['mob_max'] == "0" ) &&
			( empty($modData['mob_fix']) || !isset($modData['mob_fix']) || $modData['mob_fix'] == "0" ) &&
			( empty($modData['mob_min']) || !isset($modData['mob_min']) || $modData['mob_min'] == "0" ) &&
			( utf8_strlen($this->request->post['telephone']) == 0 )
		)
		{
			$this->error['telephone'] = $this->language->get('error_required_field');
		}
		// if the telephone holds any other characters than numbers,
		// throw an error to the users face.
		if (
			!preg_match('/^\+?[0-9]*$/', $this->request->post['telephone']) &&
			($isActive == "1" && $modData['mob_req'] == "1" || $isActive == "0")
		)
		{
			$this->error['telephone'] = $this->language->get('error_telephone_not_numeric');
		}

		// By Bassem, duplicate validation
		// if($login_register_by_phonenumber){
		// 	if ($this->model_account_customer->getTotalCustomersByPhone($this->request->post['telephone'])) {
		// 		$this->error['warning'] = $this->language->get('error_phonenumber_exists');
		// 	}
		// }

		$this->load->model('account/signup');
        // check if merchant set option 'disallow phone Number duplication' to true and not empty telephone field
        // or sign-up type is telephone, if true set uniqueness validation
		$isPhonenumberUnique = (!empty($this->request->post['telephone']) && $this->model_account_signup->isPhonenumberUnique())
                                || (int)$login_register_by_phonenumber > 0;

		if($isPhonenumberUnique){
			if ($this->model_account_customer->getTotalCustomersByPhone($this->request->post['telephone'])) {
				$this->error['warning'] = $this->language->get('error_phonenumber_exists');
			}
		}

		// Customer Group
		$this->load->model('account/customer_group');

		if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$customer_group = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		if ($customer_group) {
			// Company ID
			if ($customer_group['company_id_display'] && $customer_group['company_id_required'] && empty($this->request->post['company_id'])) {
				$this->error['company_id'] = $this->language->get('error_company_id');
			}

			// Tax ID
			if ($customer_group['tax_id_display'] && $customer_group['tax_id_required'] && empty($this->request->post['tax_id'])) {
				$this->error['tax_id'] = $this->language->get('error_tax_id');
			}
		}

		if ((!$isActive || ($isActive && $modData['address1_req'] && $modData['address1_show'])) && (utf8_strlen($this->request->post['address_1']) < 1)) {
			$this->error['address_1'] = $this->language->get('error_required_field');
		}

		if (($isActive && $modData['address2_req'] && $modData['address2_show']) && (utf8_strlen($this->request->post['address_2']) < 1)) {
			$this->error['address_2'] = $this->language->get('error_required_field');
		}

		if ((!$isActive || ($isActive && $modData['city_req'] && $modData['city_show'])) && (utf8_strlen($this->request->post['city']) < 1)) {
			$this->error['city'] = $this->language->get('error_required_field');
		}
		if ((!$isActive || ($isActive && $modData['company_req'] && $modData['company_show'])) && (utf8_strlen($this->request->post['company']) < 1)) {
			$this->error['company'] = $this->language->get('error_company');
		}

		$this->load->model('localisation/country');

		if(!$isActive || ($isActive && $modData['pin_req'] && $modData['pin_show'] && $modData['country_show']))
			$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ((!$isActive || ($isActive && $modData['pin_req'] && $modData['pin_show'] && $modData['country_show'])) && $country_info) {

			if (($isActive && $modData['pin_req'] && $modData['pin_show']) && ((utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 128))) {
				$this->error['postcode'] = $this->language->get('error_postcode');
			}

			// VAT Validation
			$this->load->helper('vat');

			if ($this->config->get('config_vat') && $this->request->post['tax_id'] && (vat_validation($country_info['iso_code_2'], $this->request->post['tax_id']) == 'invalid')) {
				$this->error['tax_id'] = $this->language->get('error_vat');
			}
		}

		if ((!$isActive || ($isActive && $modData['country_req'] && $modData['country_show'])) && $this->request->post['country_id'] == '') {
			$this->error['country'] = $this->language->get('error_required_field');
		}

		if ((!$isActive || ($isActive && $modData['state_req'] && $modData['state_show'] && $modData['country_show'] )) && (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '')) {
			$this->error['zone'] = $this->language->get('error_required_field');
		}

        // area is checked here when custom registeration application is installed $isActive = 1
        // area field will not exist at the regisetration if none of "seller_based and custom registration" is installed
        if ( $isActive && $modData['area_req'] && $modData['area_show'] && $modData['state_show'] && (!isset($this->request->post['area_id']) || $this->request->post['area_id'] == '')) {
            $this->error['area'] = $this->language->get('error_area');
        }

		if($isActive && $modData['pass_fix'] && (utf8_strlen($this->request->post['password']) != $modData['pass_fix'])){
			$this->error['password'] = $this->language->get('text_reg_pass_must_be_of') . $modData['pass_fix'] . $this->language->get('text_reg_chars');
		}else if($isActive && !$modData['pass_fix'] && $modData['pass_min'] &&  $modData['pass_max'] && ((utf8_strlen($this->request->post['password']) < $modData['pass_min']) || (utf8_strlen($this->request->post['password']) > $modData['pass_max']))){
			$this->error['password'] = $this->language->get('text_reg_pass_must_be_bet') . $modData['pass_min'] . $this->language->get('text_reg_and') . $modData['pass_max'] . $this->language->get('text_reg_chars');
		}else if($isActive && !$modData['pass_min'] && !$modData['pass_fix'] && !$modData['pass_max'] && ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20))){
			$this->error['password'] = $this->language->get('error_password');
		}else if(!$isActive  && ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20))){
			$this->error['password'] = $this->language->get('error_password');
		}

		if ((!$isActive || ($isActive && $modData['passconf_show'] )) && $this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

    	$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status'],
				'phonecode' 		=> $country_info['phonecode']
			);
		}

		$this->response->setOutput(json_encode($json));
	}
	public function area() {
		$json = array();

		$this->load->model('localisation/zone');

    	$zone_info = $this->model_localisation_zone->getZone($this->request->get['zone_id']);

		if ($zone_info) {
			$this->load->model('localisation/area');

			$json = array(
				'zone_id'        => $zone_info['country_id'],
				'name'              => $zone_info['name'],
				'area'              => $this->model_localisation_area->getAreaByZoneId($this->request->get['zone_id']),
				'status'            => $zone_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}
        
        public function countries(){
            $this->load->model('localisation/country');
            $countriesData =  $this->model_localisation_country->getCountries($this->config->get('config_language_id'));
            $this->response->setOutput(json_encode($countriesData));
        }

        public function zone() {
        $output = '<option value="0">' . $this->language->get('text_all_zones') . '</option>';

        $this->load->model('localisation/zone');

        $results = $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']);

        foreach ($results as $result) {
            $output .= '<option value="' . $result['zone_id'] . '"';

            if ($this->request->get['zone_id'] == $result['zone_id']) {
                $output .= ' selected="selected"';
            }

            $output .= '>' . $result['name'] . '</option>';
        }

        $this->response->setOutput($output);
    }

    public function usersList()
    {
        $this->load->model('network_marketing/referrals');

        $input = $this->request->post['referral'];

        $referrals = $this->model_network_marketing_referrals->referralsList($input);

        $output = [
            'status' => 'success',
            'referrals' => $referrals
        ];

        $this->response->setOutput(json_encode($output));
    }

    /**
     * check if country code has correct format (+int)
     * @param $code
     * @return bool
     */
    private function isCountryCode($code) : bool
    {
        if (preg_match('/^\+?(\d+)/m', $code)) {
            return true;
        }

        return false;

    }
}
