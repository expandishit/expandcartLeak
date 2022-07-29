<?php
class ControllerSellerRegisterSeller extends Controller {
    
	/**********************
	 * Buyer account part *
	 **********************/

	private $error = array();
	      
  	public function index() {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->indexV2();
		  
		// ***** Buyer account part *****
		if ($this->customer->isLogged()) {
	  		$this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
    	}
		
    	$this->language->load_json('account/register');
        $this->data['msconf_seller_google_api_key'] = $this->config->get('msconf_seller_google_api_key');
		$this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));
		
		//Get Seller title from setting table
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();

		$this->data['ms_account_register_seller'] = sprintf( $this->language->get('ms_account_register_seller') , $seller_title);
		$this->data['ms_account_sellerinfo_heading'] = sprintf( $this->language->get('ms_account_sellerinfo_heading') , $seller_title);
		$this->document->setTitle(
			sprintf( $this->language->get('ms_account_register_seller') , $seller_title)
		);
		$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');
		$this->document->addStyle('expandish/view/theme/default/template/multiseller/stylesheet/multiseller.css');
      
		$msconf_seller_required_fields = $this->data['seller_required_fields'] = $this->config->get('msconf_seller_required_fields');
		$msconf_seller_show_fields = $this->data['seller_show_fields'] = $this->config->get('msconf_seller_show_fields');

		$this->data['is_address_data_enabled'] = $this->config->get('msconf_address_info');

		//////////// Hide country and region as it is already exists in address section,
		//////////// Use Address's Country and Zone in seller data
		$this->data['hide_country_region'] = true;
		////////////

		$this->load->model('account/customer');
		
		$seller_data = $this->request->post['seller'] ? : [];

    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			// Buyer account part

			$this->model_account_customer->addCustomer($this->request->post);

			$this->customer->login($this->request->post['email'], $this->request->post['password']);
			
			unset($this->session->data['guest']);

			// Your service module
			if (\Extension::isInstalled('your_service')) {
				$status = $this->config->get('ys')['status'] ?? 0;
				$ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;
				if ($status == '1' && $ysMsEnabled == '1')
				{
					$this->load->model('module/your_service');
					$this->model_module_your_service->saveServiceSettings($this->customer->getId(), $this->request->post['service_ids']);
				}
			}
			
			// Default Shipping Address
			if ($this->config->get('config_tax_customer') == 'shipping') {
				$this->session->data['shipping_country_id'] = (int)$this->request->post['country_id'];
				$this->session->data['shipping_zone_id'] = (int)$this->request->post['zone_id'];
				$this->session->data['shipping_postcode'] = $this->request->post['postcode'];				
			}
			
			// Default Payment Address
			if ($this->config->get('config_tax_customer') == 'payment') {
				$this->session->data['payment_country_id'] = (int)$this->request->post['country_id'];
				$this->session->data['payment_zone_id'] = (int)$this->request->post['zone_id'];
			}
			
			if (!isset($this->session->data['seller_reviewer_message'])) {
				$this->session->data['seller_reviewer_message'] = NULL;
			}
            if (!isset($this->session->data['seller_creit'])) {
                $this->session->data['seller_credit'] = NULL;
            }

			// Seller account part
			$json = array();
			$json['redirect'] = $this->url->link('seller/account-dashboard');

			$mails = array();
			unset($this->session->data['seller']['commission']);
			$this->session->data['seller']['approved'] = 0;
			// Create new seller
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
					$this->session->data['seller']['status'] = MsSeller::STATUS_INACTIVE;
					if ($this->config->get('msconf_allow_inactive_seller_products')) {
						$json['redirect'] = $this->url->link('account/account');
					} else {
						$json['redirect'] = $this->url->link('seller/account-profile');
					}
					break;
				
				case MsSeller::MS_SELLER_VALIDATION_NONE:
				default:
					$mails[] = array(
						'type' => MsMail::SMT_SELLER_ACCOUNT_CREATED
					);
					$mails[] = array(
						'type' => MsMail::AMT_SELLER_ACCOUNT_CREATED,
						'data' => array(
							'seller_name' => $this->request->post['nickname'],
							'customer_name' => $this->request->post['firstname'] . ' ' . $this->request->post['lastname'],
							'customer_email' => $this->request->post['email']
						)
					);

					if (
						$this->config->get('msconf_enable_subscriptions_plans_system')
						&& $this->config->get('msconf_enable_subscriptions_plans_system') == 1
					) {
                        $this->session->data['seller']['status'] = MsSeller::STATUS_NOPAYMENT;
                    } else {
                        $this->session->data['seller']['status'] = MsSeller::STATUS_ACTIVE;
                    }
					$this->session->data['seller']['approved'] = 1;

					// $this->session->data['seller']['status'] = MsSeller::STATUS_ACTIVE;
					// $this->session->data['seller']['approved'] = 1;
					break;
			}
			
			$this->session->data['seller']['nickname'] = $seller_data['nickname'];
			
			// SEO urls generation for sellers
			if ($this->config->get('msconf_enable_seo_urls_seller')) {
				$latin_check = '/[^\x{0030}-\x{007f}]/u';
				$non_latin_chars = preg_match($latin_check, $this->session->data['seller']['nickname']);
				if ($this->config->get('msconf_enable_non_alphanumeric_seo') && $non_latin_chars) {
					$this->session->data['seller']['keyword'] = implode("-", str_replace("-", "", explode(" ", strtolower($this->session->data['seller']['nickname']))));
				}
				else {
					$this->session->data['seller']['keyword'] = trim(implode("-", str_replace("-", "", explode(" ", preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($this->session->data['seller']['nickname']))))), "-");
				}
			}
			
			if(in_array(ucfirst('mobile'),$msconf_seller_show_fields))
				$this->session->data['seller']['mobile'] = $seller_data['mobile'];

			if(in_array(ucfirst('tax card'),$msconf_seller_show_fields))
				$this->session->data['seller']['tax_card'] = $seller_data['tax_card'];

			if(in_array(ucfirst('website'),$msconf_seller_show_fields))
				$this->session->data['seller']['website'] = $seller_data['website'];

			if(in_array(ucfirst('commercial register'),$msconf_seller_show_fields))
				$this->session->data['seller']['commercial_reg'] = $seller_data['commercial_reg'];

			if(in_array(ucfirst('record expiration date'),$msconf_seller_show_fields))
				$this->session->data['seller']['rec_exp_date'] = $seller_data['rec_exp_date'];

			if(in_array(ucfirst('industrial license number'),$msconf_seller_show_fields))
				$this->session->data['seller']['license_num'] = $seller_data['license_num'];

			if(in_array(ucfirst('license expiration date'),$msconf_seller_show_fields))
				$this->session->data['seller']['lcn_exp_date'] = $seller_data['lcn_exp_date'];

			if(in_array(ucfirst('personal id'),$msconf_seller_show_fields))
				$this->session->data['seller']['personal_id'] = $seller_data['personal_id'];

			if(in_array(ucfirst('bank name'),$msconf_seller_show_fields))
				$this->session->data['seller']['bank_name'] = $seller_data['bank_name'];

			if(in_array(ucfirst('bank iban'),$msconf_seller_show_fields))
				$this->session->data['seller']['bank_iban'] = $seller_data['bank_iban'];

			if(in_array(ucfirst('bank transfer'),$msconf_seller_show_fields))
				$this->session->data['seller']['bank_transfer'] = $seller_data['bank_transfer'];


			$this->session->data['seller']['description'] = $seller_data['description'];
			$this->session->data['seller']['company'] = $seller_data['company'];

			//Use Address's Country and Zone in seller data
			if(in_array(ucfirst('country'),$msconf_seller_show_fields)){
				$this->session->data['seller']['country'] = $this->request->post['country_id'];
			}
			if(in_array(ucfirst('Region'),$msconf_seller_show_fields)){
				$this->session->data['seller']['zone'] = $this->request->post['zone_id'];
			}
			//$this->session->data['seller']['country'] = $seller_data['country'];
			//$this->session->data['seller']['zone'] = $seller_data['zone'];

			$this->session->data['seller']['paypal'] = $seller_data['paypal'];
			
			if (isset($seller_data['avatar_name'])) {
				$this->session->data['seller']['avatar_name'] = $seller_data['avatar_name'];
			}

			if (isset($seller_data['commercial_image_name'])) {
				$this->session->data['seller']['commercial_image_name'] = $seller_data['commercial_image_name'];
			}

			if (isset($seller_data['license_image_name'])) {
				$this->session->data['seller']['license_image_name'] = $seller_data['license_image_name'];
			}

			if (isset($seller_data['tax_image_name'])) {
				$this->session->data['seller']['tax_image_name'] = $seller_data['tax_image_name'];
			}

			if (isset($seller_data['image_id_name'])) {
				$this->session->data['seller']['image_id_name'] = $seller_data['image_id_name'];
			}
			
			$this->session->data['seller']['seller_id'] = $this->customer->getId();
			$this->session->data['seller']['product_validation'] = $this->config->get('msconf_product_validation'); 	
			
			//Custom Fields
			if(count($seller_data['custom_fields'])){
				$custom_fields = $seller_data['custom_fields'];
				$this->session->data['seller']['custom_fields'] = serialize($custom_fields);
			}
			///////////////
            $this->MsLoader->MsSeller->createSeller($this->session->data['seller']);
            $plan = $this->MsLoader->MsSeller->getSubscriptionPlan();
            $this->MsLoader->MsSeller->applySellerAffiliateCommission($plan);

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

			return $this->response->setOutput(json_encode($json));
			exit;
    	}

		
		$this->data['show_dedicated_payment_methods'] = -1; //Not show dedicated payment methods

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
			'href'      => $this->url->link('seller/register-seller', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
		
    	$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', '', 'SSL'));
		$this->data['text_your_details'] = $this->language->get('text_your_details');
    	$this->data['text_your_address'] = $this->language->get('text_your_address');
    	$this->data['text_your_password'] = $this->language->get('text_your_password');
		//$this->data['text_newsletter'] = $this->language->get('text_newsletter');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');
						
    	$this->data['entry_firstname'] = $this->language->get('entry_firstname');
    	$this->data['entry_lastname'] = $this->language->get('entry_lastname');
    	$this->data['entry_email'] = $this->language->get('entry_email');
    	$this->data['entry_telephone'] = $this->language->get('entry_telephone');
    	$this->data['entry_fax'] = $this->language->get('entry_fax');
		$this->data['entry_company'] = $this->language->get('entry_company');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_company_id'] = $this->language->get('entry_company_id');
		$this->data['entry_tax_id'] = $this->language->get('entry_tax_id');
    	$this->data['entry_address_1'] = $this->language->get('entry_address_1');
    	$this->data['entry_address_2'] = $this->language->get('entry_address_2');
    	$this->data['entry_postcode'] = $this->language->get('entry_postcode');
    	$this->data['entry_city'] = $this->language->get('entry_city');
    	$this->data['entry_country'] = $this->language->get('entry_country');
    	$this->data['entry_zone'] = $this->language->get('entry_zone');
		//$this->data['entry_newsletter'] = $this->language->get('entry_newsletter');
    	$this->data['entry_password'] = $this->language->get('entry_password');
    	$this->data['entry_confirm'] = $this->language->get('entry_confirm');
    	
		$this->data['button_continue'] = $this->language->get('button_continue');
		
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

		if (isset($this->error['country_id'])) {
			$this->data['error_country_id'] = $this->error['country_id'];
		} else {
			$this->data['error_country_id'] = '';
		}

		if (isset($this->error['zone'])) {
			$this->data['error_zone'] = $this->error['zone'];
		} else {
			$this->data['error_zone'] = '';
		}
		
		// Seller account field errors
		if (isset($this->error['seller_nickname'])) {
			$this->data['error_seller_nickname'] = $this->error['seller_nickname'];
		} else {
			$this->data['error_seller_nickname'] = '';
		}

		if (isset($this->error['seller_mobile'])) {
			$this->data['error_seller_mobile'] = $this->error['seller_mobile'];
		} else {
			$this->data['error_seller_mobile'] = '';
		}

		if (isset($this->error['seller_tax_card'])) {
			$this->data['error_seller_tax_card'] = $this->error['seller_tax_card'];
		} else {
			$this->data['error_seller_tax_card'] = '';
		}

		if (isset($this->error['google_map_location'])) {
			$this->data['error_google_map_location'] = $this->error['google_map_location'];
		} else {
			$this->data['error_google_map_location'] = '';
		}


		if (isset($this->error['seller_website'])) {
			$this->data['error_seller_website'] = $this->error['seller_website'];
		} else {
			$this->data['error_seller_website'] = '';
		}

		if (isset($this->error['seller_commercial_reg'])) {
			$this->data['error_seller_commercial_reg'] = $this->error['seller_commercial_reg'];
		} else {
			$this->data['error_seller_commercial_reg'] = '';
		}

		if (isset($this->error['seller_rec_exp_date'])) {
			$this->data['error_seller_rec_exp_date'] = $this->error['seller_rec_exp_date'];
		} else {
			$this->data['error_seller_rec_exp_date'] = '';
		}

		if (isset($this->error['seller_license_num'])) {
			$this->data['error_seller_license_num'] = $this->error['seller_license_num'];
		} else {
			$this->data['error_seller_license_num'] = '';
		}

		if (isset($this->error['seller_lcn_exp_date'])) {
			$this->data['error_seller_lcn_exp_date'] = $this->error['seller_lcn_exp_date'];
		} else {
			$this->data['error_seller_lcn_exp_date'] = '';
		}

		if (isset($this->error['seller_personal_id'])) {
			$this->data['error_seller_personal_id'] = $this->error['seller_personal_id'];
		} else {
			$this->data['error_seller_personal_id'] = '';
		}

		if (isset($this->error['seller_bank_name'])) {
			$this->data['error_seller_bank_name'] = $this->error['seller_bank_name'];
		} else {
			$this->data['error_seller_bank_name'] = '';
		}

		if (isset($this->error['seller_bank_iban'])) {
			$this->data['error_seller_bank_iban'] = $this->error['seller_bank_iban'];
		} else {
			$this->data['error_seller_bank_iban'] = '';
		}

        if (isset($this->error['commercial_image'])) {
            $this->data['error_commercial_image'] = $this->error['commercial_image'];
        } else {
            $this->data['error_commercial_image'] = '';
        }

        if (isset($this->error['tax_image'])) {
            $this->data['error_tax_image'] = $this->error['tax_image'];
        } else {
            $this->data['error_tax_image'] = '';
        }

        if (isset($this->error['image_id'])) {
            $this->data['error_image_id'] = $this->error['image_id'];
        } else {
            $this->data['error_image_id'] = '';
        }

		if (isset($this->error['seller_bank_transfer'])) {
			$this->data['error_seller_bank_transfer'] = $this->error['seller_bank_transfer'];
		} else {
			$this->data['error_seller_bank_transfer'] = '';
		}
		
		if (isset($this->error['seller_terms'])) {
			$this->data['error_seller_terms'] = $this->error['seller_terms'];
		} else {
			$this->data['error_seller_terms'] = '';
		}
		
		if (isset($this->error['seller_company'])) {
			$this->data['error_seller_company'] = $this->error['seller_company'];
		} else {
			$this->data['error_seller_company'] = '';
		}
		
		if (isset($this->error['seller_description'])) {
			$this->data['error_seller_description'] = $this->error['seller_description'];
		} else {
			$this->data['error_seller_description'] = '';
		}
		
		if (isset($this->error['seller_paypal'])) {
			$this->data['error_seller_paypal'] = $this->error['seller_paypal'];
		} else {
			$this->data['error_seller_paypal'] = '';
		}

		////Check Validate Custom Fields
		if(count($seller_data['custom_fields'])){
			$custom_fields = $seller_data['custom_fields'];
			foreach ($custom_fields as $key => $value) {
				if (isset($this->error['data_custom_'.$key])) {
					$this->data['error_seller_data_custom_'.$key] = $this->error['data_custom_'.$key];
				} else {
					$this->data['error_seller_data_custom_'.$key] = '';
				}
			}
		}
		////////////////////////
        $this->data['msconf_seller_google_api_key'] = $this->config->get('msconf_seller_google_api_key');
    	$this->data['action'] = $this->url->link('seller/register-seller', '', 'SSL');
		
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

		$this->load->model('account/customer_group');
		
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

    	if (isset($this->request->post['zone'])) {
      		$this->data['zone'] = (int)$this->request->post['zone'];
		} elseif (isset($this->session->data['shipping_zone_id'])) {
			$this->data['zone'] = $this->session->data['shipping_zone_id'];			
		} else {
      		$this->data['zone'] = '';
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
		
		/*if (isset($this->request->post['newsletter'])) {
    		$this->data['newsletter'] = $this->request->post['newsletter'];
		} else {
			$this->data['newsletter'] = '';
		}*/

		// Seller account fields
		if (isset($seller_data['nickname'])) {
    		$this->data['seller']['ms.nickname'] = $seller_data['nickname'];
		} else {
			$this->data['seller']['ms.nickname'] = '';
		}

		if (isset($seller_data['mobile'])) {
    		$this->data['seller']['ms.mobile'] = $seller_data['mobile'];
		} else {
			$this->data['seller']['ms.mobile'] = '';
		}

		if (isset($seller_data['tax_card'])) {
    		$this->data['seller']['ms.tax_card'] = $seller_data['tax_card'];
		} else {
			$this->data['seller']['ms.tax_card'] = '';
		}

		if (isset($seller_data['website'])) {
    		$this->data['seller']['ms.website'] = $seller_data['website'];
		} else {
			$this->data['seller']['ms.website'] = '';
		}

		if (isset($seller_data['commercial_reg'])) {
    		$this->data['seller']['ms.commercial_reg'] = $seller_data['commercial_reg'];
		} else {
			$this->data['seller']['ms.commercial_reg'] = '';
		}

		if (isset($seller_data['rec_exp_date'])) {
    		$this->data['seller']['ms.rec_exp_date'] = $seller_data['rec_exp_date'];
		} else {
			$this->data['seller']['ms.rec_exp_date'] = '';
		}

		if (isset($seller_data['license_num'])) {
    		$this->data['seller']['ms.license_num'] = $seller_data['license_num'];
		} else {
			$this->data['seller']['ms.license_num'] = '';
		}

		if (isset($seller_data['lcn_exp_date'])) {
    		$this->data['seller']['ms.lcn_exp_date'] = $seller_data['lcn_exp_date'];
		} else {
			$this->data['seller']['ms.lcn_exp_date'] = '';
		}

		if (isset($seller_data['personal_id'])) {
    		$this->data['seller']['ms.personal_id'] = $seller_data['personal_id'];
		} else {
			$this->data['seller']['ms.personal_id'] = '';
		}

		if (isset($seller_data['bank_name'])) {
    		$this->data['seller']['ms.bank_name'] = $seller_data['bank_name'];
		} else {
			$this->data['seller']['ms.bank_name'] = '';
		}

		if (isset($seller_data['bank_iban'])) {
    		$this->data['seller']['ms.bank_iban'] = $seller_data['bank_iban'];
		} else {
			$this->data['seller']['ms.bank_iban'] = '';
		}

		if (isset($seller_data['bank_transfer'])) {
    		$this->data['seller']['ms.bank_transfer'] = $seller_data['bank_transfer'];
		} else {
			$this->data['seller']['ms.bank_transfer'] = '';
		}
		
		if (isset($seller_data['description'])) {
    		$this->data['seller']['ms.description'] = $seller_data['description'];
		} else {
			$this->data['seller']['ms.description'] = '';
		}
		
		if (isset($seller_data['company'])) {
    		$this->data['seller']['ms.company'] = $seller_data['company'];
		} else {
			$this->data['seller']['ms.company'] = '';
		}
		
		//Use Address's Country and Zone in seller data

		/*if (isset($seller_data['country'])) {
			$this->data['seller']['ms.country'] = $seller_data['country'];
		} else {
      		$this->data['seller']['ms.country'] = $this->config->get('config_country_id');
    	}
		
		if (isset($seller_data['zone'])) {
    		$this->data['seller']['ms.zone'] = $seller_data['zone'];
		} else {
			$this->data['seller']['ms.zone'] = '';
		}*/
		
		if (isset($seller_data['paypal'])) {
    		$this->data['seller']['ms.paypal'] = $seller_data['paypal'];
		} else {
			$this->data['seller']['ms.paypal'] = '';
		}
		
		if (isset($seller_data['avatar'])) {
    		$this->data['seller']['ms.avatar'] = $seller_data['avatar'];
		} else {
			$this->data['seller']['ms.avatar'] = '';
		}
		
		if (isset($seller_data['avatar_name'])) {
    		$this->data['seller']['ms.avatar_name'] = $seller_data['avatar_name'];
		} else {
			$this->data['seller']['ms.avatar_name'] = '';
		}

		///Custom fields
		if (isset($seller_data['custom_fields'])) {
    		$this->data['seller']['ms.custom_fields'] = $seller_data['custom_fields'];
		} else {
			$this->data['seller']['ms.custom_fields'] = '';
		}
		///////////////

		if (isset($this->request->post['seller_reviewer_message'])) {
    		$this->data['seller_reviewer_message'] = $this->request->post['seller_reviewer_message'];
		} else {
			$this->data['seller_reviewer_message'] = '';
		}

        if (isset($this->request->post['seller_credit'])) {
            $this->data['seller_credit'] = $this->request->post['seller_credit'];
        } else {
            $this->data['seller_credit'] = '';
        }
		
		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
			
			if ($information_info) {
				$this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_account_id'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}

		/*if (isset($this->request->post['agree'])) {
      		$this->data['agree'] = $this->request->post['agree'];
		} else {
			$this->data['agree'] = false;
		}*/

        if (isset($this->request->get['sellerTracking']) && !empty($this->request->get['sellerTracking'])) {
            setcookie('sellerTracking', $this->request->get['sellerTracking'], time() + 3600 * 24 * 1000, '/');
        }

		// ***** Seller account part *****
		$this->document->addScript('expandish/view/javascript/multimerch/one-page-seller-account.js');
		$this->document->addScript('expandish/view/javascript/multimerch/plupload/plupload.full.js');
		$this->document->addScript('expandish/view/javascript/multimerch/plupload/jquery.plupload.queue/jquery.plupload.queue.js');
		
		// ckeditor
		if($this->config->get('msconf_enable_rte'))
			$this->document->addScript('expandish/view/javascript/multimerch/ckeditor/ckeditor.js');

		// colorbox
		$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox.js');
		$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');
		
		$this->load->model('localisation/country');
		$this->data['countries'] = $this->model_localisation_country->getCountries();
		
		$this->session->data['multiseller']['files'] = array();
		
		if ($this->config->get('msconf_seller_terms_page')) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));
			
			if ($information_info) {
				$this->data['ms_account_sellerinfo_terms_note'] = sprintf($this->language->get('ms_account_sellerinfo_terms_note'), $this->url->link('information/information', 'information_id=' . $this->config->get('msconf_seller_terms_page'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['ms_account_sellerinfo_terms_note'] = '';
			}
		} else {
			$this->data['ms_account_sellerinfo_terms_note'] = '';
		}
		
		$this->data['group_commissions'] = $this->MsLoader->MsCommission->calculateCommission(array('seller_group_id' => $this->config->get('msconf_default_seller_group_id')));
		switch($this->data['group_commissions'][MsCommission::RATE_SIGNUP]['payment_method']) {
			case MsPayment::METHOD_PAYPAL:
				$this->data['ms_commission_payment_type'] = $this->language->get('ms_account_sellerinfo_fee_paypal');
				$this->data['payment_data'] = array(
					'sandbox' => $this->config->get('msconf_paypal_sandbox'),
					'action' => $this->config->get('msconf_paypal_sandbox') ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr",
					'business' => $this->config->get('msconf_paypal_address'),
					'item_name' => sprintf($this->language->get('ms_account_sellerinfo_signup_itemname'), $this->config->get('config_name')),
					'item_number' => isset($this->request->get['seller_id']) ? (int)$this->request->get['seller_id'] : '',
					'amount' => '',
					'currency_code' => $this->config->get('config_currency'),
					'return' => $this->url->link('seller/account-dashboard'),
					'cancel_return' => $this->url->link('account/account'),
					'notify_url' => $this->url->link('payment/multimerch-paypal/signupIPN'),
					'custom' => 'custom'
				);
				
				list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('payment-paypal');
				$this->data['payment_form'] = $this->render();
				break;
				
			case MsPayment::METHOD_BALANCE:
			default:
				$this->data['ms_commission_payment_type'] = $this->language->get('ms_account_sellerinfo_fee_balance');
				break;
		}

		// Get avatars
		if ($this->config->get('msconf_avatars_for_sellers') == 1 || $this->config->get('msconf_avatars_for_sellers') == 2) {
			$this->data['predefined_avatars'] = $this->MsLoader->MsFile->getPredefinedAvatars();
		}

		////// Custom fields
		$this->data['seller_custom_fields'] = $this->config->get('msconf_seller_data_custom');
		$this->data['config_language_id'] = $this->config->get('config_language_id');
		////////////////////

		$this->data['seller_validation'] = $this->config->get('msconf_seller_validation');

		// Your service module
		if (\Extension::isInstalled('your_service')) {
			$status = $this->config->get('ys')['status'] ?? 0;
			$ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;
			if ($status == '1' && $ysMsEnabled == '1')
			{
				$this->language->load_json('module/your_service');
				$this->load->model('module/your_service');
				$this->data['ys_enabled'] = 1;
				$this->data['ys_service_settings'] = $this->language->get('ys_service_settings');
				$this->data['ys_services'] = $this->model_module_your_service->getServicesWithSubServices();
				if (isset($this->error['ys_services_error']))
				{
					$this->data['ys_services_error'] = $this->error['ys_services_error'];
				} else 
				{
					$this->data['ys_services_error'] = '';
				}
			}
        }
		// init pluploader in multiseller file uploader custom fields
		$data_custom_fields = $this->config->get('msconf_seller_data_custom'); 
		$files_indecies = array();
		foreach($data_custom_fields as $k=>$v){
				if($v['field_type']['name'] == 'file_uploader'){
					array_push($files_indecies,$k); //to set indecies array to init pluploader in view
				}
		}
		$this->data['seller']['files_indecies'] = $files_indecies;
		$this->data['ms_images_files_upload_note'] = sprintf($this->language->get('ms_images_files_upload_note'), $this->config->get('msconf_seller_allowed_files_types'));

		///////
        $this->template = 'default/template/multiseller/register-seller.tpl';
		
		$this->children = array(
			'seller/column_left',
			'seller/column_right',
			'seller/content_top',
			'seller/content_bottom',
			'seller/footer',
			'seller/header'
		);

		$this->response->setOutput($this->render());
  	}

  	protected function validate() {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) return $this->validateV2();
        
		$isAddressDataEnabled = $this->config->get('msconf_address_info');

		// ***** Buyer account part *****
		
    	if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}

    	if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
      		$this->error['email'] = $this->language->get('error_email');
    	}

    	if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
      		$this->error['warning'] = $this->language->get('error_exists');
    	}
		
    	if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
      		$this->error['telephone'] = $this->language->get('error_telephone');
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
		
		if ($isAddressDataEnabled) {

			if ((utf8_strlen($this->request->post['address_1']) < 3) || (utf8_strlen($this->request->post['address_1']) > 128)) {
				  $this->error['address_1'] = $this->language->get('error_address_1');
			}
	
			/*if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 128)) {
				  $this->error['city'] = $this->language->get('error_city');
			}*/
	
			$this->load->model('localisation/country');
			
			$country_info = $this->model_localisation_country->getCountry((int)$this->request->post['country_id']);
			
			if ($country_info) {
				if ($country_info['postcode_required'] && (utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 10)) {
					$this->error['postcode'] = $this->language->get('error_postcode');
				}
				
				// VAT Validation
				$this->load->helper('vat');
				
				if ($this->config->get('config_vat') && $this->request->post['tax_id'] && (vat_validation($country_info['iso_code_2'], $this->request->post['tax_id']) == 'invalid')) {
					$this->error['tax_id'] = $this->language->get('error_vat');
				}
			}
	
			if ($this->request->post['country_id'] == '' || !is_numeric($this->request->post['country_id'])) {
				  $this->error['country_id'] = $this->language->get('error_country');
			}
			
			if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
				  $this->error['zone'] = $this->language->get('error_zone');
			}

		}


    	if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
      		$this->error['password'] = $this->language->get('error_password');
    	}

    	if ($this->request->post['confirm'] != $this->request->post['password']) {
      		$this->error['confirm'] = $this->language->get('error_confirm');
    	}
		
		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
			
			/*if ($information_info && !isset($this->request->post['agree'])) {
      			$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}*/
		}
		
		// ***** Seller account part *****
		$this->load->model('setting/setting');
        
		$set = $this->model_setting_setting->getSetting('multiseller');
		
		$required_fields = $set['msconf_seller_required_fields'];
		$show_fields     = $set['msconf_seller_show_fields'];

		$data = $this->request->post['seller'];

		if (empty($data['nickname'])) {
			//$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_empty');
			$this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_empty');
		} else if (mb_strlen($data['nickname']) < 4 || mb_strlen($data['nickname']) > 128 ) {
			//$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_length');
			$this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_length');
		} else if ($this->MsLoader->MsSeller->nicknameTaken($data['nickname'])) {
			//$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_taken');
			$this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_taken');
		} else {
			switch($this->config->get('msconf_nickname_rules')) {
				case 1:
					// extended latin
					if(!preg_match("/^[a-zA-Z0-9_\-\s\x{00C0}-\x{017F}]+$/u", $data['nickname'])) {
						//$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_latin');
						$this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_latin');
					}
					break;
					
				case 2:
					// utf8
					if(!preg_match("/((?:[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}){1,100})./x", $data['nickname'])) {
						//$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_utf8');
						$this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_utf8');
					}
					break;
					
				case 0:
				default:
					// alnum
					if(!preg_match("/^[a-zA-Z0-9_\-\s]+$/", $data['nickname'])) {
						//$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_alphanumeric');
						$this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_alphanumeric');
					}
					break;
			}
		}
		
		if ($this->config->get('msconf_seller_terms_page')) {
			$this->load->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));
			
			if ($information_info && !isset($data['terms'])) {
				//$json['errors']['seller[terms]'] = htmlspecialchars_decode(sprintf($this->language->get('ms_error_sellerinfo_terms'), $information_info['title']));
				$this->error['seller_terms'] = htmlspecialchars_decode(sprintf($this->language->get('ms_error_sellerinfo_terms'), $information_info['title']));
			}
		}

		if(in_array(ucfirst('mobile'),$show_fields)){
			if(in_array(ucfirst('mobile'),$required_fields)){
				if(empty($data['mobile'])){
					$this->error['seller_mobile'] = $this->language->get('ms_error_sellerinfo_mobile_empty');			
				}
			}
		}

		if(in_array(ucfirst('tax card'),$show_fields)){
			if(in_array(ucfirst('tax card'),$required_fields)){
				if(empty($data['tax_card'])){
					$this->error['seller_tax_card'] = $this->language->get('ms_error_sellerinfo_tax_card_empty');			
				}
			}
		}

		if(in_array(ucfirst('website'),$show_fields)){
			if(in_array(ucfirst('website'),$required_fields)){
				if(empty($data['website'])){
					$this->error['seller_website'] = $this->language->get('ms_error_sellerinfo_website_empty');			
				}
			}
		}

		if(in_array(ucfirst('commercial register'),$show_fields)){
			if(in_array(ucfirst('commercial register'),$required_fields)){
				if(empty($data['commercial_reg'])){
					$this->error['seller_commercial_reg'] = $this->language->get('ms_error_sellerinfo_commercial_reg_empty');			
				}
			}
		}

		if(in_array(ucfirst('record expiration date'),$show_fields)){
			if(in_array(ucfirst('record expiration date'),$required_fields)){
				if(empty($data['rec_exp_date'])){
					$this->error['seller_rec_exp_date'] = $this->language->get('ms_error_sellerinfo_rec_exp_date_empty');			
				}
			}
		}

		if(in_array(ucfirst('industrial license number'),$show_fields)){
			if(in_array(ucfirst('industrial license number'),$required_fields)){
				if(empty($data['license_num'])){
					$this->error['seller_license_num'] = $this->language->get('ms_error_sellerinfo_license_num_empty');			
				}
			}
		}

		if(in_array(ucfirst('license expiration date'),$show_fields)){
			if(in_array(ucfirst('license expiration date'),$required_fields)){
				if(empty($data['lcn_exp_date'])){
					$this->error['seller_lcn_exp_date'] = $this->language->get('ms_error_sellerinfo_lcn_exp_date_empty');			
				}
			}
		}

		if(in_array(ucfirst('personal id'),$show_fields)){
			if(in_array(ucfirst('personal id'),$required_fields)){
				if(empty($data['personal_id'])){
					$this->error['seller_personal_id'] = $this->language->get('ms_error_sellerinfo_personal_id_empty');			
				}
			}
		}

		if(in_array(ucfirst('bank name'),$show_fields)){
			if(in_array(ucfirst('bank name'),$required_fields)){
				if(empty($data['bank_name'])){
					$this->error['seller_bank_name'] = $this->language->get('ms_error_sellerinfo_bank_name_empty');			
				}
			}
		}

		if(in_array(ucfirst('bank iban'),$show_fields)){
			if(in_array(ucfirst('bank iban'),$required_fields)){
				if(empty($data['bank_iban'])){
					$this->error['seller_bank_iban'] = $this->language->get('ms_error_sellerinfo_bank_iban_empty');			
				}
			}
		}

		if(in_array(ucfirst('bank transfer'),$show_fields)){
			if(in_array(ucfirst('bank transfer'),$required_fields)){
				if(empty($data['bank_transfer'])){
					$this->error['seller_bank_transfer'] = $this->language->get('ms_error_sellerinfo_bank_transfer_empty');			
				}
			}
		}

        if(in_array(ucfirst('Commercial record image'),$show_fields)){
            if(in_array(ucfirst('Commercial record image'),$required_fields)){
                if(empty($data['commercial_image_name'])){
                    $this->error['commercial_image'] = $this->language->get('ms_error_sellerinfo_commercial_image_name');
                }
            }
        }

        if(in_array(ucfirst('Tax card image'),$show_fields)){
            if(in_array(ucfirst('Tax card image'),$required_fields)){
                if(empty($data['tax_image_name'])){
                    $this->error['tax_image'] = $this->language->get('ms_error_sellerinfo_tax_image_name');
                }
            }
        }

        if(in_array(ucfirst('Image id'),$show_fields)){
            if(in_array(ucfirst('Image id'),$required_fields)){
                if(empty($data['image_id_name'])){
                    $this->error['image_id'] = $this->language->get('ms_error_sellerinfo_commercial_image_id');
                }
            }
        }

		if (mb_strlen($data['company']) > 50 ) {
			//$json['errors']['seller[company]'] = $this->language->get('ms_error_sellerinfo_company_length');
			$this->error['seller_company'] = $this->language->get('ms_error_sellerinfo_company_length');
		}
		
		if (mb_strlen($data['description']) > 1500) {
			//$json['errors']['seller[description]'] = $this->language->get('ms_error_sellerinfo_description_length');
			$this->error['seller_description'] = $this->language->get('ms_error_sellerinfo_description_length');
		}
		
		if (($data['paypal'] != "") && ((utf8_strlen($data['paypal']) > 128) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $data['paypal']))) {
			//$json['errors']['seller[paypal]'] = $this->language->get('ms_error_sellerinfo_paypal');
			$this->error['seller_paypal'] = $this->language->get('ms_error_sellerinfo_paypal');
		}
		
		if (isset($data['avatar_name']) && !empty($data['avatar_name'])) {
			if ($this->config->get('msconf_avatars_for_sellers') == 2 && !$this->MsLoader->MsFile->checkPredefinedAvatar($data['avatar_name'])) {
				$this->error['seller_avatar'] = $this->language->get('ms_error_file_upload_error');
			} elseif ($this->config->get('msconf_avatars_for_sellers') == 1 && !$this->MsLoader->MsFile->checkPredefinedAvatar($data['avatar_name']) && !$this->MsLoader->MsFile->checkFileAgainstSession($data['avatar_name'])) {
				$this->error['seller_avatar'] = $this->language->get('ms_error_file_upload_error');
			} elseif ($this->config->get('msconf_avatars_for_sellers') == 0 && !$this->MsLoader->MsFile->checkFileAgainstSession($data['avatar_name'])) {
				$this->error['seller_avatar'] = $this->language->get('ms_error_file_upload_error');
			}
		}
		
		//Validate Custom Fields
		$data_custom_fields = $this->config->get('msconf_seller_data_custom');
		$config_language_id = $this->config->get('config_language_id');

		if(count($data['custom_fields'])){
			$custom_fields = $data['custom_fields'];
			foreach ($custom_fields as $key => $value) {

				if($data_custom_fields[$key]['required'] == '1'){
					if(empty($custom_fields[$key])){
						$this->error["data_custom_$key"] = $data_custom_fields[$key]['title'][$config_language_id].' '.$this->language->get('ms_required');			
					}
				}
			}

		}
		////////////////////////

		// strip disallowed tags in description
		if ($this->config->get('msconf_enable_rte')) {
			if ($this->config->get('msconf_rte_whitelist') != '') {		
				$allowed_tags = explode(",", $this->config->get('msconf_rte_whitelist'));
				$allowed_tags_ready = "";
				foreach($allowed_tags as $tag) {
					$allowed_tags_ready .= "<".trim($tag).">";
				}
				$data['description'] = htmlspecialchars(strip_tags(htmlspecialchars_decode($data['description'], ENT_COMPAT), $allowed_tags_ready), ENT_COMPAT, 'UTF-8');
			}
		} else {
			$data['description'] = htmlspecialchars(nl2br($data['description']), ENT_COMPAT, 'UTF-8');
		}

		// Your service module
		if (\Extension::isInstalled('your_service')) {
			$status = $this->config->get('ys')['status'] ?? 0;
			$ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;
			if ($status == '1' && $ysMsEnabled == '1')
			{
				if (empty($this->request->post['service_ids']))
				{
					$this->language->load_json('module/your_service');
					$this->error['ys_services_error'] = $this->language->get('ys_services_error');
				}
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
				'status'            => $country_info['status']		
			);
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	/***********************
	 * Seller account part *
	 ***********************/
	 
	public function jxUploadSellerAvatar() {
		$json = array();
		$file = array();
		
		$this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));
		
		$json['errors'] = $this->MsLoader->MsFile->checkPostMax($_POST, $_FILES);

		if ($json['errors']) {
			return $this->response->setOutput(json_encode($json));
		}

		foreach ($_FILES as $file) {
			$errors = $this->MsLoader->MsFile->checkImage($file);
			
			if ($errors) {
				$json['errors'] = array_merge($json['errors'], $errors);
			} else {
				$fileName = $this->MsLoader->MsFile->uploadImage($file);
				$thumbUrl = $this->MsLoader->MsFile->resizeImage($fileName, $this->config->get('msconf_preview_seller_avatar_image_width'), $this->config->get('msconf_preview_seller_avatar_image_height'));
				$json['files'][] = array(
					'name' => $fileName,
					'thumb' => $thumbUrl
				);
			}
		}
		
		return $this->response->setOutput(json_encode($json));
	}

	//Duplicated Function as Expected to be customized later
	public function jxUploadSellerImage() {
		$json = array();
		$file = array();
		
		$this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));

		$json['errors'] = $this->MsLoader->MsFile->checkPostMax($_POST, $_FILES);

		if ($json['errors']) {
			return $this->response->setOutput(json_encode($json));
		}
		
		$msconf_temp_image_path       = $this->config->get('msconf_temp_image_path');
		$preview_avatar_image_width   = $this->config->get('msconf_preview_seller_avatar_image_width');
		$preview_avatar_image_height  = $this->config->get('msconf_preview_seller_avatar_image_height');

		foreach ($_FILES as $file) {
			$errors = $this->MsLoader->MsFile->checkImage($file);
			
			if ($errors) {
				$json['errors'] = array_merge($json['errors'], $errors);
			} else {
				
				$fileName = $this->MsLoader->MsFile->uploadImage($file);

				$thumbUrl = $this->MsLoader->MsFile->resizeImage(
														$fileName,
														250, 
														250
													);
				$json['files'][] = array(
					'name' => $fileName,
					'thumb' => $thumbUrl
				);
			}
		}
		
		return $this->response->setOutput(json_encode($json));
	}
	////////////////////
	public function jxUploadFiels(){
		$json = array();
		$file = array();
		$json['errors'] = $this->MsLoader->MsFile->checkPostMax($_POST, $_FILES);

		if ($json['errors']) {
			return $this->response->setOutput(json_encode($json));
		}

		$msconf_images_limits = $this->config->get('msconf_images_limits');

		foreach ($_FILES as $file) {
			if ($msconf_images_limits[1] > 0 && $this->request->post['fileCount'] >= $msconf_images_limits[1]) {
				$json['errors'][] = sprintf($this->language->get('ms_error_product_image_maximum'),$msconf_images_limits[1]);
				$json['cancel'] = 1;
				$this->response->setOutput(json_encode($json));
				return;
			} else {
				$errors = $this->MsLoader->MsFile->checkImage($file,$this->config->get('msconf_seller_allowed_files_types'));
				if ($errors) {
					$json['errors'] = array_merge($json['errors'], $errors);
				} else {
					$ext = explode('.', $file['name']);
					$ext = end($ext);
					$images_ext =  array('gif','png' ,'jpg','jpeg');
					if(in_array($ext,$images_ext)){ //images
						$fileName = $this->MsLoader->MsFile->uploadImage($file);
						$thumbUrl = $this->MsLoader->MsFile->resizeImage($fileName, $this->config->get('msconf_preview_product_image_width'), $this->config->get('msconf_preview_product_image_height'));
						$json['files'][] = array(
							'name' => $fileName,
							'thumb' => $thumbUrl
						);
					}else if($ext == 'pdf'){ //pdf files
						$fileData = $this->MsLoader->MsFile->uploadDownload($file);
						$json['files'][] = array(
							'name' => $fileData['fileName'],
							'fileMask' => $fileData['fileMask']
						);
					}
					
				}
			}
		}
		return $this->response->setOutput(json_encode($json));
	}

    public function indexV2()
    {
        // ***** Buyer account part *****
        if ($this->customer->isLogged()) {
            $this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
        }

        $this->load->model('account/customer');
        $this->load->model('account/address');

        $this->language->load_json('account/register');
        $this->language->load_json('account/identity', true);
        $this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));

        //Get Seller title from setting table
        $this->load->model('seller/seller');
        $seller_title = $this->model_seller_seller->getSellerTitle();

        $this->data['ms_account_register_seller'] = sprintf($this->language->get('ms_account_register_seller'), $seller_title);
        $this->data['ms_account_sellerinfo_heading'] = sprintf($this->language->get('ms_account_sellerinfo_heading'), $seller_title);
        $this->document->setTitle(
            sprintf($this->language->get('ms_account_register_seller'), $seller_title)
        );
        $this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
        $this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');
        $this->document->addStyle('expandish/view/theme/default/template/multiseller/stylesheet/multiseller.css');

        $msconf_seller_required_fields = $this->data['seller_required_fields'] = $this->config->get('msconf_seller_required_fields');
        $msconf_seller_show_fields = $this->data['seller_show_fields'] = $this->config->get('msconf_seller_show_fields');

        $this->data['is_address_data_enabled'] = $this->config->get('msconf_address_info');
        $this->data['msconf_seller_google_api_key'] = $this->config->get('msconf_seller_google_api_key');

        //////////// Hide country and region as it is already exists in address section,
        //////////// Use Address's Country and Zone in seller data
        $this->data['hide_country_region'] = true;
        ////////////
        
        $this->load->model('account/customer');
        
        $seller_data = $this->request->post['seller'] ?: [];
        
        $currentSeller = null;
        
        if (isset($this->session->data['guest_customer_id']) ) {
            $customer = $this->model_account_customer->getCustomer((int)$this->session->data['guest_customer_id']);
            $currentSeller = $this->MsLoader->MsSeller->getSeller((int)$this->session->data['guest_customer_id']);
           if ($currentSeller) {
                $currenSellerData = [
                    'nickname' => $currentSeller['ms.nickname'],
                    'mobile' => $currentSeller['ms.mobile'],
                    'tax_card' => $currentSeller['ms.tax_card'],
                    'website' => $currentSeller['ms.website'],
                    'commercial_reg' => $currentSeller['ms.commercial_reg'],
                    'rec_exp_date' => $currentSeller['ms.rec_exp_date'],
                    'license_num' => $currentSeller['ms.license_num'],
                    'lcn_exp_date' => $currentSeller['ms.lcn_exp_date'],
                    'personal_id' => $currentSeller['ms.personal_id'],
                    'bank_name' => $currentSeller['ms.bank_name'],
                    'bank_iban' => $currentSeller['ms.bank_iban'],
                    'bank_transfer' => $currentSeller['ms.bank_transfer'],
                    'description' => $currentSeller['ms.description'],
                    'company' => $currentSeller['ms.company'],
                    'paypal' => $currentSeller['ms.paypal'],
                    'avatar_name' => $currentSeller['ms.avatar'],
                    'commercial_image_name' => $currentSeller['ms.commercial_image'],
                    'license_image_name' => $currentSeller['ms.license_image'],
                    'tax_image_name' => $currentSeller['ms.tax_image'],
                    'image_id_name' => $currentSeller['ms.image_id'],
                    'image_id_name' => $currentSeller['ms.image_id'],
                ];
                $seller_data = array_merge($currenSellerData, $seller_data);
           }
        }

        

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateV2($currentSeller)) {
            // Buyer account part
            // create or update customer 
            $customer_id = (int)$this->request->post['customer_id'];
            $customer = $this->model_account_customer->getCustomer($customer_id);
            if ($customer) {
                $expandId = (int)$customer['expand_id'] ? (int)$customer['expand_id'] : (int)$this->session->data['guest_expand_id'];
                
                if ($expandId) {
                    $result = $this->identity->updateCustomer([
                        'id' => $expandId,
                        'email' => $this->request->post['email'],
                        'telephone' => $this->request->post['telephone'],
                        'dob' => $this->request->post['dob'],
                        'gender' => $this->request->post['gender'],
                        'name' => $this->request->post['firstname'],
                    ]);

                    if ($result['success']) {
                        $this->request->post['expand_id'] = $result['data']['id'];
                    }
                } else {
                    $result = $this->identity->addCustomer([
                        'email' => $this->request->post['email'],
                        'telephone' => $this->request->post['telephone'],
                        'dob' => $this->request->post['dob'],
                        'gender' => $this->request->post['gender'],
                        'name' => $this->request->post['firstname'],
                    ]);

                    if ($result['success']) {
                        $this->request->post['expand_id'] = $result['data']['id'];
                    }
                }

                $this->model_account_customer->updateCustomerBasicData($this->request->post);
                $customer = $this->model_account_customer->getCustomer($customer_id);
            } else {
                $customerData = [
                    'email' => $this->request->post['email'],
                    'telephone' => $this->request->post['telephone'],
                    'dob' => $this->request->post['dob'],
                    'gender' => $this->request->post['gender'],
                    'name' => $this->request->post['firstname'],
                ];

                if (isset($this->session->data['guest_expand_id'])) {
                    $result = $this->identity->updateCustomer(array_merge($customerData, ['id' => $this->session->data['guest_expand_id']]));
                } else {
                    $result = $this->identity->addCustomer($customerData);
                }

                if ($result['success']) {
                    $this->request->post['expand_id'] = $result['data']['id'];
                }

                $customer_id = $this->model_account_customer->addCustomer($this->request->post);
                $customer = $this->model_account_customer->getCustomer($customer_id);
            }

            $this->identity->attemptLogin(array_merge($customer, ['id' => $customer['expand_id']]));

            if ($this->data['is_address_data_enabled']) {
                // create or update address
                $address_id = isset($this->request->post['address_id']) ? $this->request->post['address_id'] : false;
    
                $address = $address_id ? $this->model_account_address->getAddress($this->request->post['address_id']) : false;
    
                $addressData = array_merge($this->request->post, ['telephone' => $this->request->post['shipping_telephone']]);
    
                if ($address) {
                    $this->identity->updateAddress(array_merge($addressData, $address));
                } else {
                    $this->identity->addAddress(array_merge($addressData, ['default' => true]));
                }
            }

            unset($this->session->data['guest']);

            // Your service module
            if (\Extension::isInstalled('your_service')) {
                $status = $this->config->get('ys')['status'] ?? 0;
                $ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;
                if ($status == '1' && $ysMsEnabled == '1') {
                    $this->load->model('module/your_service');
                    $this->model_module_your_service->saveServiceSettings($this->customer->getId(), $this->request->post['service_ids']);
                }
            }

            if (!isset($this->session->data['seller_reviewer_message'])) {
                $this->session->data['seller_reviewer_message'] = NULL;
            }
            if (!isset($this->session->data['seller_creit'])) {
                $this->session->data['seller_credit'] = NULL;
            }

            // Seller account part
            $json = array();
            $json['redirect'] = $this->url->link('seller/account-dashboard');

            $mails = array();
            unset($this->session->data['seller']['commission']);
            $this->session->data['seller']['approved'] = 0;
            // Create new seller
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
                    $this->session->data['seller']['status'] = MsSeller::STATUS_INACTIVE;
                    if ($this->config->get('msconf_allow_inactive_seller_products')) {
                        $json['redirect'] = $this->url->link('account/account');
                    } else {
                        $json['redirect'] = $this->url->link('seller/account-profile');
                    }
                    break;

                case MsSeller::MS_SELLER_VALIDATION_NONE:
                default:
                    $mails[] = array(
                        'type' => MsMail::SMT_SELLER_ACCOUNT_CREATED
                    );
                    $mails[] = array(
                        'type' => MsMail::AMT_SELLER_ACCOUNT_CREATED,
                        'data' => array(
                            'seller_name' => $this->request->post['nickname'],
                            'customer_name' => $this->request->post['firstname'] . ' ' . $this->request->post['lastname'],
                            'customer_email' => $this->request->post['email']
                        )
                    );

                    if (
                        $this->config->get('msconf_enable_subscriptions_plans_system')
                        && $this->config->get('msconf_enable_subscriptions_plans_system') == 1
                    ) {
                        $this->session->data['seller']['status'] = MsSeller::STATUS_NOPAYMENT;
                    } else {
                        $this->session->data['seller']['status'] = MsSeller::STATUS_ACTIVE;
                    }
                    $this->session->data['seller']['approved'] = 1;

                    break;
            }

            $this->session->data['seller']['nickname'] = $seller_data['nickname'];

            // SEO urls generation for sellers
            if ($this->config->get('msconf_enable_seo_urls_seller')) {
                $latin_check = '/[^\x{0030}-\x{007f}]/u';
                $non_latin_chars = preg_match($latin_check, $this->session->data['seller']['nickname']);
                if ($this->config->get('msconf_enable_non_alphanumeric_seo') && $non_latin_chars) {
                    $this->session->data['seller']['keyword'] = implode("-", str_replace("-", "", explode(" ", strtolower($this->session->data['seller']['nickname']))));
                } else {
                    $this->session->data['seller']['keyword'] = trim(implode("-", str_replace("-", "", explode(" ", preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($this->session->data['seller']['nickname']))))), "-");
                }
            }

            if (in_array(ucfirst('mobile'), $msconf_seller_show_fields))
                $this->session->data['seller']['mobile'] = $seller_data['mobile'];

            if (in_array(ucfirst('tax card'), $msconf_seller_show_fields))
                $this->session->data['seller']['tax_card'] = $seller_data['tax_card'];

            if (in_array(ucfirst('website'), $msconf_seller_show_fields))
                $this->session->data['seller']['website'] = $seller_data['website'];

            if (in_array(ucfirst('commercial register'), $msconf_seller_show_fields))
                $this->session->data['seller']['commercial_reg'] = $seller_data['commercial_reg'];

            if (in_array(ucfirst('record expiration date'), $msconf_seller_show_fields))
                $this->session->data['seller']['rec_exp_date'] = $seller_data['rec_exp_date'];

            if (in_array(ucfirst('industrial license number'), $msconf_seller_show_fields))
                $this->session->data['seller']['license_num'] = $seller_data['license_num'];

            if (in_array(ucfirst('license expiration date'), $msconf_seller_show_fields))
                $this->session->data['seller']['lcn_exp_date'] = $seller_data['lcn_exp_date'];

            if (in_array(ucfirst('personal id'), $msconf_seller_show_fields))
                $this->session->data['seller']['personal_id'] = $seller_data['personal_id'];

            if (in_array(ucfirst('bank name'), $msconf_seller_show_fields))
                $this->session->data['seller']['bank_name'] = $seller_data['bank_name'];

            if (in_array(ucfirst('bank iban'), $msconf_seller_show_fields))
                $this->session->data['seller']['bank_iban'] = $seller_data['bank_iban'];

            if (in_array(ucfirst('bank transfer'), $msconf_seller_show_fields))
                $this->session->data['seller']['bank_transfer'] = $seller_data['bank_transfer'];

            $this->session->data['seller']['description'] = $seller_data['description'];
            $this->session->data['seller']['company'] = $seller_data['company'];

            //Use Address's Country and Zone in seller data
            if (in_array(ucfirst('country'), $msconf_seller_show_fields)) {
                $this->session->data['seller']['country'] = $this->request->post['country_id'];
            }
            if (in_array(ucfirst('Region'), $msconf_seller_show_fields)) {
                $this->session->data['seller']['zone'] = $this->request->post['zone_id'];
            }

            $this->session->data['seller']['paypal'] = $seller_data['paypal'];

            if (isset($seller_data['avatar_name'])) {
                $this->session->data['seller']['avatar_name'] = $seller_data['avatar_name'];
            }

            if (isset($seller_data['commercial_image_name'])) {
                $this->session->data['seller']['commercial_image_name'] = $seller_data['commercial_image_name'];
            }

            if (isset($seller_data['license_image_name'])) {
                $this->session->data['seller']['license_image_name'] = $seller_data['license_image_name'];
            }

            if (isset($seller_data['tax_image_name'])) {
                $this->session->data['seller']['tax_image_name'] = $seller_data['tax_image_name'];
            }

            if (isset($seller_data['image_id_name'])) {
                $this->session->data['seller']['image_id_name'] = $seller_data['image_id_name'];
            }

            $this->session->data['seller']['seller_id'] = $this->customer->getId();
            $this->session->data['seller']['product_validation'] = $this->config->get('msconf_product_validation');

            //Custom Fields
            if (count($seller_data['custom_fields'])) {
                $custom_fields = $seller_data['custom_fields'];
                $this->session->data['seller']['custom_fields'] = serialize($custom_fields);
            }

            ///////////////
            if ($this->MsLoader->MsSeller->getSellerBasic((int)$this->session->data['seller']['seller_id'])) {
                $this->MsLoader->MsSeller->editSeller($this->session->data['seller']);
            } else {
                $this->MsLoader->MsSeller->createSeller($this->session->data['seller']);
            }

            $plan = $this->MsLoader->MsSeller->getSubscriptionPlan();
            $this->MsLoader->MsSeller->applySellerAffiliateCommission($plan);

            $commissions = $this->MsLoader->MsCommission->calculateCommission(array('seller_group_id' => $this->config->get('msconf_default_seller_group_id')));
            $fee = (float)$commissions[MsCommission::RATE_SIGNUP]['flat'];

            if ($fee > 0) {
                switch ($commissions[MsCommission::RATE_SIGNUP]['payment_method']) {
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
                        $this->MsLoader->MsBalance->addBalanceEntry(
                            $this->customer->getId(),
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
            }

            return $this->response->setOutput(json_encode($json));
            exit;
        }
        // end of post

        $this->data['show_dedicated_payment_methods'] = -1; //Not show dedicated payment methods

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
            'href'      => $this->url->link('seller/register-seller', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', '', 'SSL'));
        $this->data['text_your_details'] = $this->language->get('text_your_details');
        $this->data['text_your_address'] = $this->language->get('text_your_address');
        $this->data['text_your_password'] = $this->language->get('text_your_password');
        //$this->data['text_newsletter'] = $this->language->get('text_newsletter');
        $this->data['text_yes'] = $this->language->get('text_yes');
        $this->data['text_no'] = $this->language->get('text_no');
        $this->data['text_select'] = $this->language->get('text_select');
        $this->data['text_none'] = $this->language->get('text_none');

        $this->data['entry_firstname'] = $this->language->get('entry_firstname');
        $this->data['entry_lastname'] = $this->language->get('entry_lastname');
        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_telephone'] = $this->language->get('entry_telephone');
        $this->data['entry_fax'] = $this->language->get('entry_fax');
        $this->data['entry_company'] = $this->language->get('entry_company');
        $this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
        $this->data['entry_company_id'] = $this->language->get('entry_company_id');
        $this->data['entry_tax_id'] = $this->language->get('entry_tax_id');
        $this->data['entry_address_1'] = $this->language->get('entry_address_1');
        $this->data['entry_address_2'] = $this->language->get('entry_address_2');
        $this->data['entry_shipping_telephone'] = $this->language->get('entry_shipping_telephone');
        $this->data['entry_postcode'] = $this->language->get('entry_postcode');
        $this->data['entry_city'] = $this->language->get('entry_city');
        $this->data['entry_country'] = $this->language->get('entry_country');
        $this->data['entry_zone_id'] = $this->language->get('entry_zone_id');
        $this->data['entry_area_id'] = $this->language->get('entry_area_id');
        $this->data['entry_password'] = $this->language->get('entry_password');
        $this->data['entry_confirm'] = $this->language->get('entry_confirm');

        $this->data['button_continue'] = $this->language->get('button_continue');

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

        if (isset($this->error['gender'])) {
            $this->data['error_gender'] = $this->error['gender'];
        } else {
            $this->data['error_gender'] = '';
        }

        if (isset($this->error['dob'])) {
            $this->data['error_dob'] = $this->error['dob'];
        } else {
            $this->data['error_dob'] = '';
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

        if (isset($this->error['shipping_telephone'])) {
            $this->data['error_shipping_telephone'] = $this->error['shipping_telephone'];
        } else {
            $this->data['error_shipping_telephone'] = '';
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

        if (isset($this->error['country_id'])) {
            $this->data['error_country_id'] = $this->error['country_id'];
        } else {
            $this->data['error_country_id'] = '';
        }

        if (isset($this->error['zone_id'])) {
            $this->data['error_zone_id'] = $this->error['zone_id'];
        } else {
            $this->data['error_zone_id'] = '';
        }

        if (isset($this->error['area_id'])) {
            $this->data['error_area_id'] = $this->error['area_id'];
        } else {
            $this->data['error_area_id'] = '';
        }

        if (isset($this->error['city'])) {
            $this->data['error_city'] = $this->error['city'];
        } else {
            $this->data['error_city'] = '';
        }

        if (isset($this->error['location'])) {
            $this->data['error_location'] = $this->error['location'];
        } else {
            $this->data['error_location'] = '';
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

        if (isset($this->error['shipping_telephone'])) {
            $this->data['error_shipping_telephone'] = $this->error['shipping_telephone'];
        } else {
            $this->data['error_shipping_telephone'] = '';
        }

        if (isset($this->error['postcode'])) {
            $this->data['error_postcode'] = $this->error['postcode'];
        } else {
            $this->data['error_postcode'] = '';
        }

        if (isset($this->error['company'])) {
            $this->data['error_company'] = $this->error['company'];
        } else {
            $this->data['error_company'] = '';
        }

        // Seller account field errors
        if (isset($this->error['seller_nickname'])) {
            $this->data['error_seller_nickname'] = $this->error['seller_nickname'];
        } else {
            $this->data['error_seller_nickname'] = '';
        }

        if (isset($this->error['seller_mobile'])) {
            $this->data['error_seller_mobile'] = $this->error['seller_mobile'];
        } else {
            $this->data['error_seller_mobile'] = '';
        }

        if (isset($this->error['seller_tax_card'])) {
            $this->data['error_seller_tax_card'] = $this->error['seller_tax_card'];
        } else {
            $this->data['error_seller_tax_card'] = '';
        }

        if (isset($this->error['google_map_location'])) {
            $this->data['error_google_map_location'] = $this->error['google_map_location'];
        } else {
            $this->data['error_google_map_location'] = '';
        }


        if (isset($this->error['seller_website'])) {
            $this->data['error_seller_website'] = $this->error['seller_website'];
        } else {
            $this->data['error_seller_website'] = '';
        }

        if (isset($this->error['seller_commercial_reg'])) {
            $this->data['error_seller_commercial_reg'] = $this->error['seller_commercial_reg'];
        } else {
            $this->data['error_seller_commercial_reg'] = '';
        }

        if (isset($this->error['seller_rec_exp_date'])) {
            $this->data['error_seller_rec_exp_date'] = $this->error['seller_rec_exp_date'];
        } else {
            $this->data['error_seller_rec_exp_date'] = '';
        }

        if (isset($this->error['seller_license_num'])) {
            $this->data['error_seller_license_num'] = $this->error['seller_license_num'];
        } else {
            $this->data['error_seller_license_num'] = '';
        }

        if (isset($this->error['seller_lcn_exp_date'])) {
            $this->data['error_seller_lcn_exp_date'] = $this->error['seller_lcn_exp_date'];
        } else {
            $this->data['error_seller_lcn_exp_date'] = '';
        }

        if (isset($this->error['seller_personal_id'])) {
            $this->data['error_seller_personal_id'] = $this->error['seller_personal_id'];
        } else {
            $this->data['error_seller_personal_id'] = '';
        }

        if (isset($this->error['seller_bank_name'])) {
            $this->data['error_seller_bank_name'] = $this->error['seller_bank_name'];
        } else {
            $this->data['error_seller_bank_name'] = '';
        }

        if (isset($this->error['seller_bank_iban'])) {
            $this->data['error_seller_bank_iban'] = $this->error['seller_bank_iban'];
        } else {
            $this->data['error_seller_bank_iban'] = '';
        }

        if (isset($this->error['commercial_image'])) {
            $this->data['error_commercial_image'] = $this->error['commercial_image'];
        } else {
            $this->data['error_commercial_image'] = '';
        }

        if (isset($this->error['tax_image'])) {
            $this->data['error_tax_image'] = $this->error['tax_image'];
        } else {
            $this->data['error_tax_image'] = '';
        }

        if (isset($this->error['image_id'])) {
            $this->data['error_image_id'] = $this->error['image_id'];
        } else {
            $this->data['error_image_id'] = '';
        }

        if (isset($this->error['seller_bank_transfer'])) {
            $this->data['error_seller_bank_transfer'] = $this->error['seller_bank_transfer'];
        } else {
            $this->data['error_seller_bank_transfer'] = '';
        }

        if (isset($this->error['seller_terms'])) {
            $this->data['error_seller_terms'] = $this->error['seller_terms'];
        } else {
            $this->data['error_seller_terms'] = '';
        }

        if (isset($this->error['seller_company'])) {
            $this->data['error_seller_company'] = $this->error['seller_company'];
        } else {
            $this->data['error_seller_company'] = '';
        }

        if (isset($this->error['seller_description'])) {
            $this->data['error_seller_description'] = $this->error['seller_description'];
        } else {
            $this->data['error_seller_description'] = '';
        }

        if (isset($this->error['seller_paypal'])) {
            $this->data['error_seller_paypal'] = $this->error['seller_paypal'];
        } else {
            $this->data['error_seller_paypal'] = '';
        }

        ////Check Validate Custom Fields
        if (count($seller_data['custom_fields'])) {
            $custom_fields = $seller_data['custom_fields'];
            foreach ($custom_fields as $key => $value) {
                if (isset($this->error['data_custom_' . $key])) {
                    $this->data['error_seller_data_custom_' . $key] = $this->error['data_custom_' . $key];
                } else {
                    $this->data['error_seller_data_custom_' . $key] = '';
                }
            }
        }
        ////////////////////////

        $this->data['action'] = $this->url->link('seller/register-seller', '', 'SSL');

        $this->load->model('account/customer');
        $this->load->model('account/address');
        // set current guest customer
        if (isset($this->session->data['guest_customer_id'])) {
            $customer = $this->model_account_customer->getCustomer((int)$this->session->data['guest_customer_id']);
            $this->data['customer_id'] = $customer['customer_id'];
            $this->data['customer_group_id'] = ($customer && isset($customer['customer_group_id'])) ? $customer['customer_group_id'] : $this->config->get('config_customer_group_id');
            $this->data['newsletter'] = ($customer && isset($customer['newsletter'])) ? $customer['newsletter'] : 0;
            // address
            $address = $this->model_account_address->getAddress($customer['address_id'], $customer['customer_id']);
            $address && ($this->data['address_id'] = $address['address_id']);
            $this->data['customer_fields'] = $this->config->get('config_customer_fields')['registration'];
            $this->data['customer_fields']['email'] = $this->identity->isLoginByPhone() ? 0 : 1;
            $this->data['customer_fields']['telephone'] = $this->identity->isLoginByPhone() ? 1 : 0;
        }

        $this->data['address_fields'] = $this->config->get('config_customer_fields')['address'];
        $this->load->model("module/google_map");

        $this->data['address_fields']['map'] = $this->model_module_google_map->getSettings();

        if (isset($this->request->post['firstname'])) {
            $this->data['firstname'] = $this->request->post['firstname'];
        } elseif (isset($customer) && $customer) {
            $this->data['firstname'] = $customer['firstname'];
        } elseif (isset($this->session->data['guest_name'])) {
            $this->data['firstname'] = $this->session->data['guest_name'];
        } else {
            $this->data['firstname'] = '';
        }

        if (isset($this->request->post['company'])) {
            $this->data['company'] = $this->request->post['company'];
        } elseif (isset($customer) && $customer) {
            $this->data['company'] = $customer['company'];
        } else {
            $this->data['company'] = '';
        }

        if (isset($this->request->post['email'])) {
            $this->data['email'] = $this->request->post['email'];
        } elseif (isset($customer) && $customer) {
            $this->data['email'] = $customer['email'];
        } elseif (isset($this->session->data['guest_email'])) {
            $this->data['email'] = $this->session->data['guest_email'];
        } else {
            $this->data['email'] = '';
        }

        if (isset($this->request->post['telephone'])) {
            $this->data['telephone'] = $this->request->post['telephone'];
        } elseif (isset($customer) && $customer) {
            $this->data['telephone'] = $customer['telephone'];
        } elseif (isset($this->session->data['guest_telephone'])) {
            $this->data['telephone'] = $this->session->data['guest_telephone'];
        } else {
            $this->data['telephone'] = '';
        }

        if (isset($this->request->post['gender'])) {
            $this->data['gender'] = $this->request->post['gender'];
        } elseif (isset($customer) && $customer) {
            $this->data['gender'] = $customer['gender'];
        } elseif (isset($this->session->data['guest_gender'])) {
            $this->data['gender'] = $this->session->data['guest_gender'];
        } else {
            $this->data['gender'] = '';
        }

        if (isset($this->request->post['dob'])) {
            $this->data['dob'] = $this->request->post['dob'];
        } elseif (isset($customer) && $customer) {
            $this->data['dob'] = $customer['dob'];
        } elseif (isset($this->session->data['guest_dob'])) {
            $this->data['dob'] = $this->session->data['guest_dob'];
        } else {
            $this->data['dob'] = '';
        }

        if (isset($this->request->post['fax'])) {
            $this->data['fax'] = $this->request->post['fax'];
        } else {
            $this->data['fax'] = '';
        }

        $this->load->model('account/customer_group');

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
        } elseif (isset($address) && !empty($address)) {
            $this->data['company_id'] = $address['company_id'];
        } else {
            $this->data['company_id'] = '';
        }

        // Tax ID
        if (isset($this->request->post['tax_id'])) {
            $this->data['tax_id'] = $this->request->post['tax_id'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['tax_id'] = $address['tax_id'];
        } else {
            $this->data['tax_id'] = '';
        }

        if (isset($this->request->post['address_1'])) {
            $this->data['address_1'] = $this->request->post['address_1'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['address_1'] = $address['address_1'];
        } else {
            $this->data['address_1'] = '';
        }

        if (isset($this->request->post['address_2'])) {
            $this->data['address_2'] = $this->request->post['address_2'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['address_2'] = $address['address_2'];
        } else {
            $this->data['address_2'] = '';
        }

        if (isset($this->request->post['shipping_telephone'])) {
            $this->data['shipping_telephone'] = $this->request->post['shipping_telephone'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['shipping_telephone'] = $address['telephone'];
        } elseif (isset($this->session->data['shipping_telephone'])) {
            $this->data['shipping_telephone'] = $this->session->data['shipping_telephone'];
        } else {
            $this->data['shipping_telephone'] = '';
        }

        if (isset($this->request->post['postcode'])) {
            $this->data['postcode'] = $this->request->post['postcode'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['postcode'] = $address['postcode'];
        } elseif (isset($this->session->data['shipping_postcode'])) {
            $this->data['postcode'] = $this->session->data['shipping_postcode'];
        } else {
            $this->data['postcode'] = '';
        }

        if (isset($this->request->post['city'])) {
            $this->data['city'] = $this->request->post['city'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['city'] = $address['city'];
        } else {
            $this->data['city'] = '';
        }

        if (isset($this->request->post['country_id'])) {
            $this->data['country_id'] = (int)$this->request->post['country_id'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['country_id'] = $address['country_id'];
        } elseif (isset($this->session->data['shipping_country_id'])) {
            $this->data['country_id'] = $this->session->data['shipping_country_id'];
        } else {
            $this->data['country_id'] = $this->config->get('config_country_id');
        }

        if (isset($this->request->post['zone_id'])) {
            $this->data['zone_id'] = (int)$this->request->post['zone_id'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['zone_id'] = $address['zone_id'];
        } elseif (isset($this->session->data['shipping_zone_id'])) {
            $this->data['zone_id'] = $this->session->data['shipping_zone_id'];
        } else {
            $this->data['zone_id'] = '';
        }

        if (isset($this->request->post['area_id'])) {
            $this->data['area_id'] = (int)$this->request->post['area_id'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['area_id'] = $address['area_id'];
        } elseif (isset($this->session->data['shipping_area_id'])) {
            $this->data['area_id'] = $this->session->data['shipping_area_id'];
        } else {
            $this->data['area_id'] = '';
        }

        if (isset($this->request->post['location'])) {
            $this->data['location'] = $this->request->post['location'];
        } elseif (isset($address) && !empty($address)) {
            $this->data['location'] = $address['location'];
        } else {
            $this->data['location'] = '';
        }
        $this->load->model('localisation/country');

        $this->data['countries'] = $this->model_localisation_country->getCountries();

        // Seller account fields
        if (isset($seller_data['nickname'])) {
            $this->data['seller']['ms.nickname'] = $seller_data['nickname'];
        } else {
            $this->data['seller']['ms.nickname'] = '';
        }

        if (isset($seller_data['mobile'])) {
            $this->data['seller']['ms.mobile'] = $seller_data['mobile'];
        } else {
            $this->data['seller']['ms.mobile'] = '';
        }

        if (isset($seller_data['tax_card'])) {
            $this->data['seller']['ms.tax_card'] = $seller_data['tax_card'];
        } else {
            $this->data['seller']['ms.tax_card'] = '';
        }

        if (isset($seller_data['website'])) {
            $this->data['seller']['ms.website'] = $seller_data['website'];
        } else {
            $this->data['seller']['ms.website'] = '';
        }

        if (isset($seller_data['commercial_reg'])) {
            $this->data['seller']['ms.commercial_reg'] = $seller_data['commercial_reg'];
        } else {
            $this->data['seller']['ms.commercial_reg'] = '';
        }

        if (isset($seller_data['rec_exp_date'])) {
            $this->data['seller']['ms.rec_exp_date'] = $seller_data['rec_exp_date'];
        } else {
            $this->data['seller']['ms.rec_exp_date'] = '';
        }

        if (isset($seller_data['license_num'])) {
            $this->data['seller']['ms.license_num'] = $seller_data['license_num'];
        } else {
            $this->data['seller']['ms.license_num'] = '';
        }

        if (isset($seller_data['lcn_exp_date'])) {
            $this->data['seller']['ms.lcn_exp_date'] = $seller_data['lcn_exp_date'];
        } else {
            $this->data['seller']['ms.lcn_exp_date'] = '';
        }

        if (isset($seller_data['personal_id'])) {
            $this->data['seller']['ms.personal_id'] = $seller_data['personal_id'];
        } else {
            $this->data['seller']['ms.personal_id'] = '';
        }

        if (isset($seller_data['bank_name'])) {
            $this->data['seller']['ms.bank_name'] = $seller_data['bank_name'];
        } else {
            $this->data['seller']['ms.bank_name'] = '';
        }

        if (isset($seller_data['bank_iban'])) {
            $this->data['seller']['ms.bank_iban'] = $seller_data['bank_iban'];
        } else {
            $this->data['seller']['ms.bank_iban'] = '';
        }

        if (isset($seller_data['bank_transfer'])) {
            $this->data['seller']['ms.bank_transfer'] = $seller_data['bank_transfer'];
        } else {
            $this->data['seller']['ms.bank_transfer'] = '';
        }

        if (isset($seller_data['description'])) {
            $this->data['seller']['ms.description'] = $seller_data['description'];
        } else {
            $this->data['seller']['ms.description'] = '';
        }

        if (isset($seller_data['company'])) {
            $this->data['seller']['ms.company'] = $seller_data['company'];
        } else {
            $this->data['seller']['ms.company'] = '';
        }

        if (isset($seller_data['paypal'])) {
            $this->data['seller']['ms.paypal'] = $seller_data['paypal'];
        } else {
            $this->data['seller']['ms.paypal'] = '';
        }

        if (isset($seller_data['avatar'])) {
            $this->data['seller']['ms.avatar'] = $seller_data['avatar'];
        } else {
            $this->data['seller']['ms.avatar'] = '';
        }

        if (isset($seller_data['avatar_name'])) {
            $this->data['seller']['ms.avatar_name'] = $seller_data['avatar_name'];
        } else {
            $this->data['seller']['ms.avatar_name'] = '';
        }

        ///Custom fields
        if (isset($seller_data['custom_fields'])) {
            $this->data['seller']['ms.custom_fields'] = $seller_data['custom_fields'];
        } else {
            $this->data['seller']['ms.custom_fields'] = '';
        }
        ///////////////

        if (isset($this->request->post['seller_reviewer_message'])) {
            $this->data['seller_reviewer_message'] = $this->request->post['seller_reviewer_message'];
        } else {
            $this->data['seller_reviewer_message'] = '';
        }

        if (isset($this->request->post['seller_credit'])) {
            $this->data['seller_credit'] = $this->request->post['seller_credit'];
        } else {
            $this->data['seller_credit'] = '';
        }

        if ($this->config->get('config_account_id')) {
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

            if ($information_info) {
                $this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_account_id'), 'SSL'), $information_info['title'], $information_info['title']);
            } else {
                $this->data['text_agree'] = '';
            }
        } else {
            $this->data['text_agree'] = '';
        }

        if (isset($this->request->get['sellerTracking']) && !empty($this->request->get['sellerTracking'])) {
            setcookie('sellerTracking', $this->request->get['sellerTracking'], time() + 3600 * 24 * 1000, '/');
        }

        // ***** Seller account part *****
        $this->document->addScript('expandish/view/javascript/multimerch/one-page-seller-account.js');
        $this->document->addScript('expandish/view/javascript/multimerch/plupload/plupload.full.js');
        $this->document->addScript('expandish/view/javascript/multimerch/plupload/jquery.plupload.queue/jquery.plupload.queue.js');

        // ckeditor
        if ($this->config->get('msconf_enable_rte'))
            $this->document->addScript('expandish/view/javascript/multimerch/ckeditor/ckeditor.js');

        // colorbox
        $this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox.js');
        $this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');

        $this->load->model('localisation/country');
        $this->data['countries'] = $this->model_localisation_country->getCountries();

        $this->session->data['multiseller']['files'] = array();

        if ($this->config->get('msconf_seller_terms_page')) {
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));

            if ($information_info) {
                $this->data['ms_account_sellerinfo_terms_note'] = sprintf($this->language->get('ms_account_sellerinfo_terms_note'), $this->url->link('information/information', 'information_id=' . $this->config->get('msconf_seller_terms_page'), 'SSL'), $information_info['title'], $information_info['title']);
            } else {
                $this->data['ms_account_sellerinfo_terms_note'] = '';
            }
        } else {
            $this->data['ms_account_sellerinfo_terms_note'] = '';
        }

        $this->data['group_commissions'] = $this->MsLoader->MsCommission->calculateCommission(array('seller_group_id' => $this->config->get('msconf_default_seller_group_id')));
        switch ($this->data['group_commissions'][MsCommission::RATE_SIGNUP]['payment_method']) {
            case MsPayment::METHOD_PAYPAL:
                $this->data['ms_commission_payment_type'] = $this->language->get('ms_account_sellerinfo_fee_paypal');
                $this->data['payment_data'] = array(
                    'sandbox' => $this->config->get('msconf_paypal_sandbox'),
                    'action' => $this->config->get('msconf_paypal_sandbox') ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr",
                    'business' => $this->config->get('msconf_paypal_address'),
                    'item_name' => sprintf($this->language->get('ms_account_sellerinfo_signup_itemname'), $this->config->get('config_name')),
                    'item_number' => isset($this->request->get['seller_id']) ? (int)$this->request->get['seller_id'] : '',
                    'amount' => '',
                    'currency_code' => $this->config->get('config_currency'),
                    'return' => $this->url->link('seller/account-dashboard'),
                    'cancel_return' => $this->url->link('account/account'),
                    'notify_url' => $this->url->link('payment/multimerch-paypal/signupIPN'),
                    'custom' => 'custom'
                );

                list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('payment-paypal');
                $this->data['payment_form'] = $this->render();
                break;

            case MsPayment::METHOD_BALANCE:
            default:
                $this->data['ms_commission_payment_type'] = $this->language->get('ms_account_sellerinfo_fee_balance');
                break;
        }

        // Get avatars
        if ($this->config->get('msconf_avatars_for_sellers') == 1 || $this->config->get('msconf_avatars_for_sellers') == 2) {
            $this->data['predefined_avatars'] = $this->MsLoader->MsFile->getPredefinedAvatars();
        }

        ////// Custom fields
        $this->data['seller_custom_fields'] = $this->config->get('msconf_seller_data_custom');
        $this->data['config_language_id'] = $this->config->get('config_language_id');
        ////////////////////

        $this->data['seller_validation'] = $this->config->get('msconf_seller_validation');

        // Your service module
        if (\Extension::isInstalled('your_service')) {
            $status = $this->config->get('ys')['status'] ?? 0;
            $ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;
            if ($status == '1' && $ysMsEnabled == '1') {
                $this->language->load_json('module/your_service');
                $this->load->model('module/your_service');
                $this->data['ys_enabled'] = 1;
                $this->data['ys_service_settings'] = $this->language->get('ys_service_settings');
                $this->data['ys_services'] = $this->model_module_your_service->getServicesWithSubServices();
                if (isset($this->error['ys_services_error'])) {
                    $this->data['ys_services_error'] = $this->error['ys_services_error'];
                } else {
                    $this->data['ys_services_error'] = '';
                }
            }
        }
        // init pluploader in multiseller file uploader custom fields
        $data_custom_fields = $this->config->get('msconf_seller_data_custom');
        $files_indecies = array();
        foreach ($data_custom_fields as $k => $v) {
            if ($v['field_type']['name'] == 'file_uploader') {
                array_push($files_indecies, $k); //to set indecies array to init pluploader in view
            }
        }
        $this->data['seller']['files_indecies'] = $files_indecies;
        $this->data['ms_images_files_upload_note'] = sprintf($this->language->get('ms_images_files_upload_note'), $this->config->get('msconf_seller_allowed_files_types'));

        ///////
        $this->template = 'default/template/multiseller/register-seller-v2.tpl';

        $this->children = array(
            'seller/column_left',
            'seller/column_right',
            'seller/content_top',
            'seller/content_bottom',
            'seller/footer',
            'seller/header'
        );
        
        $this->initializeLogin();

        $this->response->setOutput($this->render());
    }
    
    /**
     * This method called in every request to add login js plugin scripts
     * and sync the customer profile data
     * based on expand_updated_at column in customer table
     *
     * @return false
     */
    public function initializeLogin()
    {
        $this->load->model('localisation/country');
        $this->load->model("module/google_map");
        $this->load->model('setting/setting');
        
        $storeCode = STORECODE;

        $customer = json_encode($this->customer->isLogged() ? ['id' => $this->customer->getExpandId(), 'logged_in' => true] : ['logged_in' => false]);
        $lang = in_array($this->config->get('config_language'), ['ar', 'en']) ? $this->config->get('config_language') : 'en';
        $countryId = $this->session->data['shipping_country_id'] ?? $this->config->get('config_country_id');
        $countries = json_encode($this->model_localisation_country->getCountries());
		//$whatsAppAgree = (int)(\Extension::isInstalled('whatsapp') && $this->config->get('whatsapp_config_customer_allow_receive_messages')) ? 1 : 0;
		$whatsAppAgree = true;//this option currently removed 
		$enableMultiseller = (int)\Extension::isInstalled('multiseller');
        $loginWithPhone = $this->identity->isLoginByPhone();

        $googleMap = $this->model_module_google_map->getSettings();
        
        $sellerSettings = $this->model_setting_setting->getSetting('multiseller')['msconf_seller_show_fields'];
        
        if(in_array(ucfirst('google map location'), $sellerSettings) && !empty($this->config->get('msconf_seller_google_api_key'))) {
            $googleMap = array_merge($googleMap, [
               'status' => 1,
                'api_key' => $this->config->get('msconf_seller_google_api_key')
            ]);
        }

        $loginSelectors = json_encode([
            'customer' => [
                'login' => [
                    // A list of links that the library will replace with the login pop-up modal
                    // When the user presses one of them
                    'a[href*="' . $this->url->link('account/account') . '"]',
                    'a[href*="' . $this->url->link('account/login') . '"]',
                    'a[href*="' . $this->url->link('account/register') . '"]',
                    'a[href*="' . $this->url->link('account/wishlist') . '"]',
                ],
            ],
            'seller' => [
                'login' => [
                    'a[href*="' . $this->url->link('seller/register-seller') . '"]',
                ]
            ],
            'checkout' => [
                'login' => [
                    '#option_login_popup_trigger_wrap [name="account"]#register',
                    '#option_login_popup_trigger_wrap #option_login_popup_trigger'
                ],
            ],
        ]);

        $storeName = $this->config->get('config_name');

        // social login app
        $this->load->model('setting/extension');
        $extensions = $this->model_setting_extension->getExtensions('module');
        $socialLogin = ['status' => false];
        foreach ($extensions as $extension) {
            if ($extension['code'] == 'd_social_login') {
                $settings = $this->config->get('d_social_login_settings');
                if ($settings) {
                    if ($settings['status']) {
                        $socialLogin['status'] = true;
                        $socialLogin['content'] = $this->getChild('module/' . 'd_social_login');
                    }
                }
                break;
            }
        }
        $socialLogin = json_encode($socialLogin);
        $customerAccountFields = json_encode($this->config->get('config_customer_fields'));
        $isGetRequest = !($this->request->server['REQUEST_METHOD'] == 'POST');
        $libraryStatus = (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) ? 'on' : 'off';
        $this->document->addInlineScript(function () use ($storeName, $lang, $storeCode, $countryId, $whatsAppAgree, $enableMultiseller, $loginWithPhone, $customer, $googleMap, $loginSelectors, $socialLogin, $countries, $customerAccountFields, $isGetRequest, $libraryStatus) {
            $noCache = "v1"; // bin2hex(random_bytes(4));
            $return = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/expandish/view/javascript/loginjs/dist/loginjs.css?nocache=$noCache\"/>";

            if ($isGetRequest && (int)$googleMap['status'] === 1) {
                $googleMapApiKey = $googleMap['api_key'];
                $return .= "<script type=\"text/javascript\" id=\"google-maps-sdk\" src=\"https://maps.googleapis.com/maps/api/js?key=$googleMapApiKey&libraries=places\"></script>";
            }

            $googleMap = json_encode($googleMap);
            
            $return .= "<script type=\"text/javascript\" defer src=\"/expandish/view/javascript/loginjs/dist/loginjs.js?nocache=$noCache\"></script><script id=\"loginjs-plugin-$noCache\">window.addEventListener('DOMContentLoaded', (event) => {window.Loginjs !== undefined && (window.login = new Loginjs({storeName: '$storeName', lang: '$lang', storeCode: '$storeCode', countryId: '$countryId', whatsAppAgree: $whatsAppAgree, enableMultiseller: $enableMultiseller, loginWithPhone: $loginWithPhone, customer: $customer, map: $googleMap, loginSelectors: $loginSelectors, socialLogin: $socialLogin, countries: $countries, customerAccountFields: $customerAccountFields, libraryStatus: '$libraryStatus'}).render());});</script>";

            return $return;
        });

        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->identity->syncCustomerProfile();
        }


        return false;
    }

    protected function validateV2($currentSeller = null)
    {
        if (isset($this->request->post['customer_id'])) {
            $customer = $this->model_account_customer->getCustomer((int)$this->session->data['guest_customer_id']);
        }

        $customer = isset($customer) ? $customer : false;
        $customerFields = $this->config->get('config_customer_fields');
        $customerFields['registration']['email'] = $this->identity->isLoginByPhone() ? 0 : 1;
        $customerFields['registration']['telephone'] = $this->identity->isLoginByPhone() ? 1 : 0;

        $isAddressDataEnabled = $this->config->get('msconf_address_info');

        // ***** Buyer account part *****

        if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
            $this->error['firstname'] = $this->language->get('required_input_firstname');
        }

        if ($customerFields['registration']['email'] == 1 && (utf8_strlen($this->request->post['email']) > 96 || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email']))) {
            $this->error['email'] = $this->language->get('required_input_email');
        }

        if (!$customer && $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
            $this->error['warning'] = $this->language->get('error_exists');
        }

        if ($customerFields['registration']['telephone'] == 1 && (utf8_strlen($this->request->post['telephone']) < 3 || (utf8_strlen($this->request->post['telephone']) > 32))) {
            $this->error['telephone'] = $this->language->get('required_input_telephone');
        }

        if ($customerFields['registration']['dob'] == 1 && empty($this->request->post['dob'])) {
            $this->error['dob'] = $this->language->get('required_input_dob');
        }

        if ($customerFields['registration']['company'] == 1 && empty($this->request->post['company'])) {
            $this->error['company'] = $this->language->get('required_input_company');
        }

        if ($customerFields['registration']['gender'] == 1 && empty($this->request->post['gender'])) {
            $this->error['gender'] = $this->language->get('required_input_gender');
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

        if ($isAddressDataEnabled) {

            if ($customerFields['address']['address_1'] == 1 && (utf8_strlen($this->request->post['address_1']) < 3 || (utf8_strlen($this->request->post['address_1']) > 128))) {
                $this->error['address_1'] = $this->language->get('required_input_address_1');
            }

            if ($customerFields['address']['address_2'] == 1 && (utf8_strlen($this->request->post['address_2']) < 3 || (utf8_strlen($this->request->post['address_2']) > 128))) {
                $this->error['address_2'] = $this->language->get('required_input_address_2');
            }

            if ($customerFields['address']['telephone'] == 1 && (utf8_strlen($this->request->post['shipping_telephone']) < 3 || (utf8_strlen($this->request->post['shipping_telephone']) > 128))) {
                $this->error['shipping_telephone'] = $this->language->get('required_input_shipping_telephone');
            }

            if ($customerFields['address']['zone_id'] == 1 && empty($this->request->post['zone_id'])) {
                $this->error['zone_id'] = $this->language->get('required_input_zone_id');
            }

            if ($customerFields['address']['area_id'] == 1 && empty($this->request->post['area_id'])) {
                $this->error['area_id'] = $this->language->get('required_input_area_id');
            }

            if ($customerFields['address']['postcode'] == 1 && empty($this->request->post['postcode'])) {
                $this->error['postcode'] = $this->language->get('required_input_postcode');
            }

            $this->load->model('localisation/country');

            $country_info = $this->model_localisation_country->getCountry((int)$this->request->post['country_id']);
        }

        if ($this->config->get('config_account_id')) {
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
        }

        // ***** Seller account part *****
        $this->load->model('setting/setting');

        $set = $this->model_setting_setting->getSetting('multiseller');

        $required_fields = $set['msconf_seller_required_fields'];
        $show_fields     = $set['msconf_seller_show_fields'];

        $data = $this->request->post['seller'];

        if (empty($data['nickname'])) {
            //$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_empty');
            $this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_empty');
        } else if (mb_strlen($data['nickname']) < 4 || mb_strlen($data['nickname']) > 128) {
            //$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_length');
            $this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_length');
        } else if ((!$currentSeller || ($currentSeller['ms.nickname'] != $data['nickname'])) && $this->MsLoader->MsSeller->nicknameTaken($data['nickname'])) {
            //$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_taken');
            $this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_taken');
        } else {
            switch ($this->config->get('msconf_nickname_rules')) {
                case 1:
                    // extended latin
                    if (!preg_match("/^[a-zA-Z0-9_\-\s\x{00C0}-\x{017F}]+$/u", $data['nickname'])) {
                        //$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_latin');
                        $this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_latin');
                    }
                    break;

                case 2:
                    // utf8
                    if (!preg_match("/((?:[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}){1,100})./x", $data['nickname'])) {
                        //$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_utf8');
                        $this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_utf8');
                    }
                    break;

                case 0:
                default:
                    // alnum
                    if (!preg_match("/^[a-zA-Z0-9_\-\s]+$/", $data['nickname'])) {
                        //$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_alphanumeric');
                        $this->error['seller_nickname'] = $this->language->get('ms_error_sellerinfo_nickname_alphanumeric');
                    }
                    break;
            }
        }

        if ($this->config->get('msconf_seller_terms_page')) {
            $this->load->model('catalog/information');
            $information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));

            if ($information_info && !isset($data['terms'])) {
                //$json['errors']['seller[terms]'] = htmlspecialchars_decode(sprintf($this->language->get('ms_error_sellerinfo_terms'), $information_info['title']));
                $this->error['seller_terms'] = htmlspecialchars_decode(sprintf($this->language->get('ms_error_sellerinfo_terms'), $information_info['title']));
            }
        }

        if (in_array(ucfirst('mobile'), $show_fields)) {
            if (in_array(ucfirst('mobile'), $required_fields)) {
                if (empty($data['mobile'])) {
                    $this->error['seller_mobile'] = $this->language->get('ms_error_sellerinfo_mobile_empty');
                }
            }
        }

        if (in_array(ucfirst('tax card'), $show_fields)) {
            if (in_array(ucfirst('tax card'), $required_fields)) {
                if (empty($data['tax_card'])) {
                    $this->error['seller_tax_card'] = $this->language->get('ms_error_sellerinfo_tax_card_empty');
                }
            }
        }

        if (in_array(ucfirst('website'), $show_fields)) {
            if (in_array(ucfirst('website'), $required_fields)) {
                if (empty($data['website'])) {
                    $this->error['seller_website'] = $this->language->get('ms_error_sellerinfo_website_empty');
                }
            }
        }

        if (in_array(ucfirst('commercial register'), $show_fields)) {
            if (in_array(ucfirst('commercial register'), $required_fields)) {
                if (empty($data['commercial_reg'])) {
                    $this->error['seller_commercial_reg'] = $this->language->get('ms_error_sellerinfo_commercial_reg_empty');
                }
            }
        }

        if (in_array(ucfirst('record expiration date'), $show_fields)) {
            if (in_array(ucfirst('record expiration date'), $required_fields)) {
                if (empty($data['rec_exp_date'])) {
                    $this->error['seller_rec_exp_date'] = $this->language->get('ms_error_sellerinfo_rec_exp_date_empty');
                }
            }
        }

        if (in_array(ucfirst('industrial license number'), $show_fields)) {
            if (in_array(ucfirst('industrial license number'), $required_fields)) {
                if (empty($data['license_num'])) {
                    $this->error['seller_license_num'] = $this->language->get('ms_error_sellerinfo_license_num_empty');
                }
            }
        }

        if (in_array(ucfirst('license expiration date'), $show_fields)) {
            if (in_array(ucfirst('license expiration date'), $required_fields)) {
                if (empty($data['lcn_exp_date'])) {
                    $this->error['seller_lcn_exp_date'] = $this->language->get('ms_error_sellerinfo_lcn_exp_date_empty');
                }
            }
        }

        if (in_array(ucfirst('personal id'), $show_fields)) {
            if (in_array(ucfirst('personal id'), $required_fields)) {
                if (empty($data['personal_id'])) {
                    $this->error['seller_personal_id'] = $this->language->get('ms_error_sellerinfo_personal_id_empty');
                }
            }
        }

        if (in_array(ucfirst('bank name'), $show_fields)) {
            if (in_array(ucfirst('bank name'), $required_fields)) {
                if (empty($data['bank_name'])) {
                    $this->error['seller_bank_name'] = $this->language->get('ms_error_sellerinfo_bank_name_empty');
                }
            }
        }

        if (in_array(ucfirst('bank iban'), $show_fields)) {
            if (in_array(ucfirst('bank iban'), $required_fields)) {
                if (empty($data['bank_iban'])) {
                    $this->error['seller_bank_iban'] = $this->language->get('ms_error_sellerinfo_bank_iban_empty');
                }
            }
        }

        if (in_array(ucfirst('bank transfer'), $show_fields)) {
            if (in_array(ucfirst('bank transfer'), $required_fields)) {
                if (empty($data['bank_transfer'])) {
                    $this->error['seller_bank_transfer'] = $this->language->get('ms_error_sellerinfo_bank_transfer_empty');
                }
            }
        }

        if (mb_strlen($data['description']) > 1500) {
            $this->error['seller_description'] = $this->language->get('ms_error_sellerinfo_description_length');
        }

        if (($data['paypal'] != "") && ((utf8_strlen($data['paypal']) > 128) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $data['paypal']))) {
            $this->error['seller_paypal'] = $this->language->get('ms_error_sellerinfo_paypal');
        }

        if (isset($data['avatar_name']) && !empty($data['avatar_name'])) {
            if ($this->config->get('msconf_avatars_for_sellers') == 2 && !$this->MsLoader->MsFile->checkPredefinedAvatar($data['avatar_name'])) {
                $this->error['seller_avatar'] = $this->language->get('ms_error_file_upload_error');
            } elseif ($this->config->get('msconf_avatars_for_sellers') == 1 && !$this->MsLoader->MsFile->checkPredefinedAvatar($data['avatar_name']) && !$this->MsLoader->MsFile->checkFileAgainstSession($data['avatar_name'])) {
                $this->error['seller_avatar'] = $this->language->get('ms_error_file_upload_error');
            } elseif ($this->config->get('msconf_avatars_for_sellers') == 0 && !$this->MsLoader->MsFile->checkFileAgainstSession($data['avatar_name'])) {
                $this->error['seller_avatar'] = $this->language->get('ms_error_file_upload_error');
            }
        }

        //Validate Custom Fields
        $data_custom_fields = $this->config->get('msconf_seller_data_custom');
        $config_language_id = $this->config->get('config_language_id');

        if (count($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            foreach ($custom_fields as $key => $value) {

                if ($data_custom_fields[$key]['required'] == '1') {
                    if (empty($custom_fields[$key])) {
                        $this->error["data_custom_$key"] = $data_custom_fields[$key]['title'][$config_language_id] . ' ' . $this->language->get('ms_required');
                    }
                }
            }
        }
        ////////////////////////

        // strip disallowed tags in description
        if ($this->config->get('msconf_enable_rte')) {
            if ($this->config->get('msconf_rte_whitelist') != '') {
                $allowed_tags = explode(",", $this->config->get('msconf_rte_whitelist'));
                $allowed_tags_ready = "";
                foreach ($allowed_tags as $tag) {
                    $allowed_tags_ready .= "<" . trim($tag) . ">";
                }
                $data['description'] = htmlspecialchars(strip_tags(htmlspecialchars_decode($data['description'], ENT_COMPAT), $allowed_tags_ready), ENT_COMPAT, 'UTF-8');
            }
        } else {
            $data['description'] = htmlspecialchars(nl2br($data['description']), ENT_COMPAT, 'UTF-8');
        }

        // Your service module
        if (\Extension::isInstalled('your_service')) {
            $status = $this->config->get('ys')['status'] ?? 0;
            $ysMsEnabled = $this->config->get('ys')['ms_view_requests'] ?? 0;
            if ($status == '1' && $ysMsEnabled == '1') {
                if (empty($this->request->post['service_ids'])) {
                    $this->language->load_json('module/your_service');
                    $this->error['ys_services_error'] = $this->language->get('ys_services_error');
                }
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function zone()
    {
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
}
