<?php

class ControllerSellerAccountProfile extends ControllerSellerAccount {
	public function jxUploadSellerAvatar() {
		$json = array();
		$file = array();
		
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
				$thumbUrl = $this->MsLoader->MsFile->resizeImage(
					$fileName,
					$this->config->get('msconf_preview_seller_avatar_image_width'),
					$this->config->get('msconf_preview_seller_avatar_image_height')
				);
				$json['files'][] = array(
					'name' => $fileName,
					'thumb' => $thumbUrl
				);
			}
		}
		
		return $this->response->setOutput(json_encode($json));
	}

	/**
	 * @return mixed
	 */
	public function jxUploadSellerImage()
	{
		$json = array();
		$json['errors'] = $this->MsLoader->MsFile->checkPostMax($_POST, $_FILES);

		if ($json['errors']) {
			return $this->response->setOutput(json_encode($json));
		}

		foreach ($_FILES as $file) {
			$errors = $this->MsLoader->MsFile->checkImage($file);

			if ($errors) {
				$json['errors'] = array_merge($json['errors'], $errors);
			} else {

				$fileName = $this->MsLoader->MsFile->uploadImage($file, "sellers/{$this->customer->getId()}/");

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

	public function getConfig()
	{
		$lang=$this->config->get('config_language');
		return $this->response->setOutput(json_encode($lang));
	}

	public function jxSaveSellerInfo()
	{
		$this->load->model('setting/setting');
        
		$set = $this->model_setting_setting->getSetting('multiseller');
		
		$required_fields = $set['msconf_seller_required_fields'];
		$show_fields     = $set['msconf_seller_show_fields'];
		
		$data = $this->request->post;
		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());
		$json = array();
		$json['redirect'] = $this->url->link('seller/account-dashboard');
		
		//Get Seller title from setting table
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();
		
		if (!empty($seller) && (in_array($seller['ms.seller_status'], array(MsSeller::STATUS_DISABLED, MsSeller::STATUS_DELETED)))) {
			return $this->response->setOutput(json_encode($json));
		}
		
		if ($this->config->get('msconf_change_seller_nickname') || empty($seller)) {
			// seller doesn't exist yet
			if (empty($data['seller']['nickname'])) {
				$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_empty'); 
			} else if (mb_strlen($data['seller']['nickname']) < 4 || mb_strlen($data['seller']['nickname']) > 128 ) {
				$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_length');			
			} else if ( ($data['seller']['nickname'] != $seller['ms.nickname']) && ($this->MsLoader->MsSeller->nicknameTaken($data['seller']['nickname'])) ) {
				$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_taken');
			} else {
				switch($this->config->get('msconf_nickname_rules')) {
					case 1:
						// extended latin
						if(!preg_match("/^[a-zA-Z0-9_\-\s\x{00C0}-\x{017F}]+$/u", $data['seller']['nickname'])) {
							$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_latin');
						}
						break;
						
					case 2:
						// utf8
						if(!preg_match("/((?:[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}){1,100})./x", $data['seller']['nickname'])) {
							$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_utf8');
						}
						break;
						
					case 0:
					default:
						// alnum
						if(!preg_match("/^[a-zA-Z0-9_\-\s]+$/", $data['seller']['nickname'])) {
							$json['errors']['seller[nickname]'] = $this->language->get('ms_error_sellerinfo_nickname_alphanumeric');
						}
						break;
				}
			}
		} else {
			$data['seller']['nickname'] = $seller['ms.nickname'];
		}
		
		if (empty($seller)) {
			if ($this->config->get('msconf_seller_terms_page')) {
				$this->load->model('catalog/information');
				$information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));

				if ($information_info && !isset($data['seller']['terms'])) {
	 				$json['errors']['seller[terms]'] = htmlspecialchars_decode(sprintf($this->language->get('ms_error_sellerinfo_terms'), $information_info['title']));
				}
			}
		}
		

		if(in_array(ucfirst('google map location'),$show_fields)){
			if(in_array(ucfirst('google map location'),$required_fields)){
				if(empty($data['seller']['seller_location'])){
					$json['errors']['seller[seller_location]'] = $this->language->get('ms_error_sellerinfo_seller_location_empty');			
				}
			}
		}

		if(in_array(ucfirst('mobile'),$show_fields)){
			if(in_array(ucfirst('mobile'),$required_fields)){
				if(empty($data['seller']['mobile'])){
					$json['errors']['seller[mobile]'] = $this->language->get('ms_error_sellerinfo_mobile_empty');			
				}
			}
		}

		if(in_array(ucfirst('company'),$show_fields)){
			if(in_array(ucfirst('company'),$required_fields)){
				if(empty($data['seller']['company'])){
					$json['errors']['seller[company]'] = $this->language->get('ms_error_sellerinfo_company_empty');			
				}
				else if (mb_strlen($data['seller']['company']) > 50 ) {
					$json['errors']['seller[company]'] = $this->language->get('ms_error_sellerinfo_company_length');			
				}
			}
		}

		if(in_array(ucfirst('description'),$show_fields)){
			if(in_array(ucfirst('description'),$required_fields)){
				if(empty($data['seller']['description'])){
					$json['errors']['seller[description]'] = $this->language->get('ms_error_sellerinfo_description_empty');			
				}
				else if (mb_strlen($data['seller']['description']) > 1500) {
					$json['errors']['seller[description]'] = $this->language->get('ms_error_sellerinfo_description_length');			
				}
			}
		}

		if(in_array(ucfirst('website'),$show_fields)){
			if(in_array(ucfirst('website'),$required_fields)){
				if(empty($data['seller']['website'])){
					$json['errors']['seller[website]'] = $this->language->get('ms_error_sellerinfo_website_empty');			
				}
			}
		}

		if(in_array(ucfirst('tax card'),$show_fields)){
			if(in_array(ucfirst('tax card'),$required_fields)){
				if(empty($data['seller']['tax_card'])){
					$json['errors']['seller[tax_card]'] = $this->language->get('ms_error_sellerinfo_tax_card_empty');			
				}
			}
		}

		if(in_array(ucfirst('commercial register'),$show_fields)){
			if(in_array(ucfirst('commercial register'),$required_fields)){
				if(empty($data['seller']['commercial_reg'])){
					$json['errors']['seller[commercial_reg]'] = $this->language->get('ms_error_sellerinfo_commercial_reg_empty');			
				}
			}
		}

		if(in_array(ucfirst('record expiration date'),$show_fields)){
			if(in_array(ucfirst('record expiration date'),$required_fields)){
				if(empty($data['seller']['rec_exp_date'])){
					$json['errors']['seller[rec_exp_date]'] = $this->language->get('ms_error_sellerinfo_rec_exp_date_empty');			
				}
			}
		}

		if(in_array(ucfirst('industrial license number'),$show_fields)){
			if(in_array(ucfirst('industrial license number'),$required_fields)){
				if(empty($data['seller']['license_num'])){
					$json['errors']['seller[license_num]'] = $this->language->get('ms_error_sellerinfo_license_num_empty');			
				}
			}
		}

		if(in_array(ucfirst('license expiration date'),$show_fields)){
			if(in_array(ucfirst('license expiration date'),$required_fields)){
				if(empty($data['seller']['lcn_exp_date'])){
					$json['errors']['seller[lcn_exp_date]'] = $this->language->get('ms_error_sellerinfo_lcn_exp_date_empty');			
				}
			}
		}

		if(in_array(ucfirst('personal id'),$show_fields)){
			if(in_array(ucfirst('personal id'),$required_fields)){
				if(empty($data['seller']['personal_id'])){
					$json['errors']['seller[personal_id]'] = $this->language->get('ms_error_sellerinfo_personal_id_empty');			
				}
			}
		}

		if(in_array(ucfirst('country'),$show_fields)){
			if(in_array(ucfirst('country'),$required_fields)){
				if($data['seller']['country'] == ""){
					$json['errors']['seller[country]'] = $this->language->get('ms_error_sellerinfo_country_empty');			
				}
			}
		}

		if(in_array(ucfirst('region'),$show_fields)){
			if(in_array(ucfirst('region'),$required_fields)){
				if(empty($data['seller']['zone'])){
					$json['errors']['seller[zone]'] = $this->language->get('ms_error_sellerinfo_region_empty');			
				}
			}
		}

		if(in_array(ucfirst('paypal'),$show_fields)){
			if(in_array(ucfirst('paypal'),$required_fields)){
				if (($data['seller']['paypal'] != "") && ((utf8_strlen($data['seller']['paypal']) > 128) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $data['seller']['paypal']))) {
					$json['errors']['seller[paypal]'] = $this->language->get('ms_error_sellerinfo_paypal');
				}
			}
		}
		
		if(in_array(ucfirst('bank name'),$show_fields)){
			if(in_array(ucfirst('bank name'),$required_fields)){
				if(empty($data['seller']['bank_name'])){
					$json['errors']['seller[bank_name]'] = $this->language->get('ms_error_sellerinfo_bank_name_empty');			
				}
			}
		}

		if(in_array(ucfirst('bank iban'),$show_fields)){
			if(in_array(ucfirst('bank iban'),$required_fields)){
				if(empty($data['seller']['bank_iban'])){
					$json['errors']['seller[bank_iban]'] = $this->language->get('ms_error_sellerinfo_bank_iban_empty');			
				}
			}
		}

		if(in_array(ucfirst('bank transfer'),$show_fields)){
			if(in_array(ucfirst('bank transfer'),$required_fields)){
				if(empty($data['seller']['bank_transfer'])){
					$json['errors']['seller[bank_transfer]'] = $this->language->get('ms_error_sellerinfo_bank_transfer_empty');			
				}
			}
		}

		if(in_array(ucfirst('payment methods'),$show_fields)){
			if(in_array(ucfirst('payment methods'),$required_fields)){
				if(empty($data['seller']['payment_methods'])){
					$json['errors']['seller[payment_methods]'] = $this->language->get('ms_error_sellerinfo_payment_methods_empty');			
				}
			}
		}

		if(in_array(ucfirst('avatar'),$show_fields)){
			if (isset($data['seller']['avatar_name']) && !empty($data['seller']['avatar_name'])) {
				if ($this->config->get('msconf_avatars_for_sellers') == 2 && !$this->MsLoader->MsFile->checkPredefinedAvatar($data['seller']['avatar_name'])) {
					$json['errors']['seller[avatar]'] = $this->language->get('ms_error_file_upload_error');
				} elseif ($this->config->get('msconf_avatars_for_sellers') == 1 && !$this->MsLoader->MsFile->checkPredefinedAvatar($data['seller']['avatar_name']) && !$this->MsLoader->MsFile->checkFileAgainstSession($data['seller']['avatar_name'])) {
					$json['errors']['seller[avatar]'] = $this->language->get('ms_error_file_upload_error');
				} elseif ($this->config->get('msconf_avatars_for_sellers') == 0 && !$this->MsLoader->MsFile->checkFileAgainstSession($data['seller']['avatar_name'])) {
					$json['errors']['seller[avatar]'] = $this->language->get('ms_error_file_upload_error');
				}
			}
		}
		if (in_array(ucfirst('commercial record image'), $show_fields, true)){
			if (in_array(ucfirst('commercial record image'), $required_fields)){
				if(! isset($data['seller']['commercial_image_name']) && empty($data['seller']['commercial_image_name']) && ! $this->MsLoader->MsFile->checkFileAgainstSession($data['seller']['commercial_image_name']) ) {
					$json['errors']['seller[commercial_image]'] = $this->language->get('ms_error_file_upload_error');
				}
			}
		}
		if (in_array(ucfirst('industrial license image'), $show_fields, true)){
			if (in_array(ucfirst('industrial license image'), $required_fields)){
				if(! isset($data['seller']['license_image_name']) && empty($data['seller']['license_image_name']) && ! $this->MsLoader->MsFile->checkFileAgainstSession($data['seller']['license_image_name']) ) {
					$json['errors']['seller[license_image]'] = $this->language->get('ms_error_file_upload_error');
				}
			}
		}

		if ( in_array(ucfirst('tax card image'), $show_fields, true)){
			if (in_array(ucfirst('tax card image'), $required_fields)){
				if(! isset($data['seller']['tax_image_name']) && empty($data['seller']['tax_image_name']) && ! (bool)$this->config->get('msconf_avatars_for_sellers') && ! $this->MsLoader->MsFile->checkFileAgainstSession($data['seller']['tax_image_name'])) {
					$json['errors']['seller[tax_image]'] = $this->language->get('ms_error_file_upload_error');
				}
			}
		}

		if ( in_array(ucfirst('image id'), $show_fields, true)){
			if (in_array(ucfirst('image id'), $required_fields)){
				if(! isset($data['seller']['image_id_name']) && empty($data['seller']['image_id_name']) && ! (bool)$this->config->get('msconf_avatars_for_sellers') && ! $this->MsLoader->MsFile->checkFileAgainstSession($data['seller']['image_id_name'])) {
					$json['errors']['seller[image_id]'] = $this->language->get('ms_error_file_upload_error');
				}
			}
		}
		//Validate Custom Fields
		$data_custom_fields = $this->config->get('msconf_seller_data_custom');
		$config_language_id = $this->config->get('config_language_id');

		$custom_fields = [];
		if(count($data['seller']['custom_fields'])){

			$custom_fields = $data['seller']['custom_fields'];
			foreach ($custom_fields as $key => $value) {

				if($data_custom_fields[$key]['required'] == '1'){
					if(empty($custom_fields[$key])){
						$json['errors']["seller[custom_fields][$key]"] = $data_custom_fields[$key]['title'][$config_language_id].' '.$this->language->get('ms_required');			
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
				$data['seller']['description'] = htmlspecialchars(strip_tags(htmlspecialchars_decode($data['seller']['description'], ENT_COMPAT), $allowed_tags_ready), ENT_COMPAT, 'UTF-8');
			}
		} else {
			$data['seller']['description'] = htmlspecialchars(nl2br($data['seller']['description']), ENT_COMPAT, 'UTF-8');
		}
		
		// uncomment to enable RTE for message field
		/*
		if(isset($data['reviewer_message'])) {
			$data['seller']['reviewer_message'] = strip_tags(html_entity_decode($data['seller']['reviewer_message']), $allowed_tags_ready);
		}
		*/

		if (empty($json['errors'])) {
			$mails = array();
			unset($data['seller']['commission']);
			
			if ($this->config->get('msconf_change_seller_nickname') || empty($seller)) {
				// SEO urls generation for sellers
				if ($this->config->get('msconf_enable_seo_urls_seller')) {
					$latin_check = '/[^\x{0030}-\x{007f}]/u';
					$non_latin_chars = preg_match($latin_check, $data['seller']['nickname']);
					if ($this->config->get('msconf_enable_non_alphanumeric_seo') && $non_latin_chars) {
						$data['seller']['keyword'] = implode("-", str_replace("-", "", explode(" ", strtolower($data['seller']['nickname']))));
					}
					else {
						$data['seller']['keyword'] = trim(implode("-", str_replace("-", "", explode(" ", preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($data['seller']['nickname']))))), "-");
					}
				}
			}
			
			if (empty($seller)) {
				$data['seller']['approved'] = 0;
				// create new seller
				switch ($this->config->get('msconf_seller_validation')) {
					/*
					case MsSeller::MS_SELLER_VALIDATION_ACTIVATION:
						$data['seller_status'] = MsSeller::STATUS_TOBEACTIVATED;
						break;
					*/
					
					case MsSeller::MS_SELLER_VALIDATION_APPROVAL:
						$mails[] = array(
							'type' => MsMail::SMT_SELLER_ACCOUNT_AWAITING_MODERATION,
							'data' => array('no_template' => 1)
						);
						$mails[] = array(
							'type' => MsMail::AMT_SELLER_ACCOUNT_AWAITING_MODERATION,
							'data' => array(
								'message' => $data['seller']['reviewer_message'],
								'seller_name' => $data['seller']['nickname'],
								'customer_name' => $this->customer->getFirstname() . ' ' . $this->customer->getLastname(),
								'customer_email' => $this->MsLoader->MsSeller->getSellerEmail($this->customer->getId()),
								'no_template' => 1
							)
						);
						$data['seller']['status'] = MsSeller::STATUS_INACTIVE;
						if ($this->config->get('msconf_allow_inactive_seller_products')) {
							$json['redirect'] = $this->url->link('account/account');
						} else {
							$json['redirect'] = $this->url->link('seller/account-profile');
						}
						break;
					
					case MsSeller::MS_SELLER_VALIDATION_NONE:
					default:
						$mails[] = array(
							'type' => MsMail::SMT_SELLER_ACCOUNT_CREATED,
							'data' => array('no_template' => 1)
						);
						$mails[] = array(
							'type' => MsMail::AMT_SELLER_ACCOUNT_CREATED,
							'data' => array(
								'seller_name' => $data['seller']['nickname'],
								'customer_name' => $this->customer->getFirstname() . ' ' . $this->customer->getLastname(),
								'customer_email' => $this->MsLoader->MsSeller->getSellerEmail($this->customer->getId()),
								'no_template' => 1
							)
						);
						if (
							$this->config->get('msconf_enable_subscriptions_plans_system')
							&& $this->config->get('msconf_enable_subscriptions_plans_system') == 1
						) {
                            $data['seller']['status'] = MsSeller::STATUS_NOPAYMENT;
                        } else {
                            $data['seller']['status'] = MsSeller::STATUS_ACTIVE;
                        }
						$data['seller']['approved'] = 1;
						break;
				}
				
				$data['seller']['seller_id'] = $this->customer->getId();
				$data['seller']['product_validation'] = $this->config->get('msconf_product_validation');

				//Save Custom Fields
				$data['seller']['custom_fields'] = count($custom_fields) ? serialize($custom_fields) : '';
				///////////////
				$plan = $this->MsLoader->MsSeller->getSubscriptionPlan();
				$new_seller_id = $this->MsLoader->MsSeller->createSeller($data['seller']);

				//add avaiable payment methods to this seller to be enabled all by default
				$this->_addAvailablePaymentMethods($new_seller_id);
				$affiliateAppIsActive = (
					\Extension::isInstalled('affiliates') && $this->config->get('affiliates')['status']
				);
				if ($affiliateAppIsActive){
					$this->MsLoader->MsSeller->applySellerAffiliateCommission($plan);
				}
				$json['redirect'] = $this->url->link('seller/account-profile');
				$this->response->setOutput(json_encode($json));
				return;
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
		
							$this->MsLoader->MsMail->sendMails($mails);
							return $this->response->setOutput(json_encode($json));
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
				}

				$this->session->data['success'] = sprintf( $this->language->get('ms_account_sellerinfo_saved') , $seller_title );
			} else {

				//Save Custom Fields
				$data['seller']['custom_fields'] = count($custom_fields) ? serialize($custom_fields) : '';
				///////////////

				// edit seller
				$data['seller']['seller_id'] = $seller['seller_id'];
				$this->MsLoader->MsSeller->editSeller($data['seller']);
				
				if ($seller['ms.seller_status'] == MsSeller::STATUS_UNPAID) {
					$commissions = $this->MsLoader->MsCommission->calculateCommission(array('seller_group_id' => $this->config->get('msconf_default_seller_group_id')));
					$fee = (float)$commissions[MsCommission::RATE_SIGNUP]['flat'];
					
					if ($fee > 0) {
						switch($commissions[MsCommission::RATE_SIGNUP]['payment_method']) {
							case MsPayment::METHOD_PAYPAL:
								// initiate paypal payment
								
								// set product status to unpaid
								$this->MsLoader->MsSeller->changeStatus($this->customer->getId(), MsSeller::STATUS_UNPAID);
								
								// check if payment exists
								$payment = $this->MsLoader->MsPayment->getPayments(array(
									'seller_id' => $this->customer->getId(),
									'payment_type' => array(MsPayment::TYPE_SIGNUP),
									'payment_status' => array(MsPayment::STATUS_UNPAID),
									'payment_method' => array(MsPayment::METHOD_PAYPAL),
									'single' => 1
								));
								
								if (!$payment) {
									// create new payment
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
								} else {
									$payment_id = $payment['payment_id'];
									
									// edit payment
									$this->MsLoader->MsPayment->updatePayment($payment_id, array(
										'amount' => $fee,
										'date_created' => 1,
										'description' => sprintf($this->language->get('ms_transaction_signup'), $this->config->get('config_name'))
									));									
								}
								// assign payment variables
								$json['data']['amount'] = $this->currency->format($fee, $this->config->get('config_currency'), '', FALSE);
								$json['data']['custom'] = $payment_id;
								
								return $this->response->setOutput(json_encode($json));
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
								
								break;
						}
					}
				}
				
				$this->session->data['success'] = sprintf( $this->language->get('ms_account_sellerinfo_saved') , $seller_title );
			}

            /*------------------------------Remove seller cache-----------------------------------------*/
            $this->cache->delete("seller" . $data['seller']['seller_id']);
            $this->cache->delete("catalog_seller");
            $this->cache->delete("catalog_seller_total");
            $this->cache->delete("ms_carousel");
            $this->cache->delete("ms_newsellers");
            $this->cache->delete("ms_topsellers");
            /*----------------------------------------------------------------------------------------------------*/
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function index() {
		$this->document->addScript('expandish/view/javascript/multimerch/account-seller-profile.js');
		$this->document->addScript('expandish/view/javascript/multimerch/plupload/plupload.full.js');
		$this->document->addScript('expandish/view/javascript/multimerch/plupload/jquery.plupload.queue/jquery.plupload.queue.js');

		// ckeditor
		if($this->config->get('msconf_enable_rte')){
			$this->document->addScript('expandish/view/javascript/multimerch/ckeditor/getConfig.js');
			$this->document->addScript('expandish/view/javascript/multimerch/ckeditor/ckeditor.js');
		
		}
			
		// colorbox
		$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox.js');
		$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');
		
		$this->load->model('localisation/country');
		$this->data['countries'] = $this->model_localisation_country->getCountries();		

		$seller = $this->MsLoader->MsSeller->getSeller($this->customer->getId());
		$this->_checkIfSellerHasEditPermission($seller);

		$this->data['salt'] = $this->MsLoader->MsSeller->getSalt($this->customer->getId());
		$this->data['statusclass'] = 'attention';

		//Get Seller title from setting table
		$this->load->model('seller/seller');
		$seller_title = $this->model_seller_seller->getSellerTitle();

		if ($seller) {
			switch ($seller['ms.seller_status']) {
				case MsSeller::STATUS_UNPAID:
					$this->data['statusclass'] = 'attention';
					break;				
				case MsSeller::STATUS_ACTIVE:
					$this->data['statusclass'] = 'success';
					break;
				case MsSeller::STATUS_DISABLED:
				case MsSeller::STATUS_DELETED:
					$this->data['statusclass'] = 'warning';
					break;
			}
			
			$this->data['seller'] = $seller;
			$this->data['country_id'] = $seller['ms.country_id'];

			///Custom Fields
			if (!empty($seller['ms.custom_fields'])) {
				$data_custom_fields = $this->config->get('msconf_seller_data_custom'); 
				$ms_custom_fields =unserialize($seller['ms.custom_fields']);
				$this->data['seller']['ms.custom_fields'] = $ms_custom_fields; 
				$file_arr = array();
				$files_indecies = array();
				foreach($data_custom_fields as $k=>$v){
					$file_arr = array();
					if($v['field_type']['name'] == 'file_uploader'){
						array_push($files_indecies,$k); //to set indecies array to init pluploader in view
						$ext = explode('.', $ms_custom_fields[$k]); 
						$ext = end($ext);
						$file_arr['name'] = $ms_custom_fields[$k];
						if($ext == 'pdf'){
							$file_arr['mask'] = end(explode('_',$ms_custom_fields[$k]));
							$file_arr['href'] = $this->url->link('seller/account-profile/download', 'download=' . $ms_custom_fields[$k], 'SSL');
						}else{
							$file_arr['image'] 	= $this->MsLoader->MsFile->resizeImage($ms_custom_fields[$k], $this->config->get('msconf_preview_product_image_width'), $this->config->get('msconf_preview_product_image_height'));
						}
						$this->data['seller']['ms.custom_fields'][$k] = $file_arr;
					}
				}
				$this->data['seller']['files_indecies'] = $files_indecies;
			}
			//////////////

			if (!empty($seller['ms.avatar'])) {
				$this->data['seller']['ms.avatar_name'] = $seller['ms.avatar'];
				$this->data['seller']['avatar']['thumb'] = $this->MsLoader->MsFile->resizeImage(
					$seller['ms.avatar'],
					$this->config->get('msconf_preview_seller_avatar_image_width'),
					$this->config->get('msconf_preview_seller_avatar_image_height')
				);
				$this->session->data['multiseller']['files'][] = $seller['ms.avatar'];
			}

			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$custom_server =  HTTPS_SELLER_FILES.$this->customer->getId() .'/';
			} else {
				$custom_server =  HTTP_SELLER_FILES.$this->customer->getId() .'/';
			}

			if (!empty($seller['ms.commercial_image'])) {
				$this->data['seller']['commercial_image']['name'] = $seller['ms.commercial_image'];
				$this->data['seller']['commercial_image']['thumb'] = $this->MsLoader->MsFile->resizeImage(
					$seller['ms.commercial_image'], 
					250, 
					250
				);
				$this->session->data['multiseller']['files'][] = $seller['ms.commercial_image'];
			}

			if (!empty($seller['ms.license_image'])) {
				$this->data['seller']['license_image']['name'] = $seller['ms.license_image'];
				$this->data['seller']['license_image']['thumb'] = $this->MsLoader->MsFile->resizeImage(
					$seller['ms.license_image'], 
					250, 
					250
				);
				$this->session->data['multiseller']['files'][] = $seller['ms.license_image'];
			}

			if (!empty($seller['ms.tax_image'])) {
				$this->data['seller']['tax_image']['name'] = $seller['ms.tax_image'];
				$this->data['seller']['tax_image']['thumb'] = $this->MsLoader->MsFile->resizeImage(
					$seller['ms.tax_image'], 
					250, 
					250
				);
				$this->session->data['multiseller']['files'][] = $seller['ms.tax_image'];
			}

			if (!empty($seller['ms.image_id'])) {
				$this->data['seller']['image_id']['name'] = $seller['ms.image_id'];
				$this->data['seller']['image_id']['thumb'] = $this->MsLoader->MsFile->resizeImage(
					$seller['ms.image_id'], 
					250, 
					250
				);
				$this->session->data['multiseller']['files'][] = $seller['ms.tax_image'];
			}

			$this->data['statustext'] = sprintf( $this->language->get('ms_account_status') , $seller_title ). $this->language->get('ms_seller_status_' . $seller['ms.seller_status']);

			if( $seller['ms.seller_status'] == MsSeller::STATUS_UNPAID || $seller['ms.seller_status'] == MsSeller::STATUS_NOPAYMENT ){
				$this->redirect($this->url->link('seller/account-subscriptions/updatePlan', '', 'SSL'));
			}
			if ($seller['ms.seller_status'] == MsSeller::STATUS_INACTIVE && !$seller['ms.seller_approved']) {
				$this->data['statustext'] .= $this->language->get('ms_account_status_tobeapproved');
			}

			$this->data['ms_account_sellerinfo_terms_note'] = '';
		} else {
			$this->data['seller'] = FALSE;
			$this->data['country_id'] = $this->config->get('config_country_id');


			$this->data['statustext'] = $this->language->get('ms_account_status_please_fill_in');
			
			if ($this->config->get('msconf_seller_terms_page')) {
				$this->load->model('catalog/information');
				
				$information_info = $this->model_catalog_information->getInformation($this->config->get('msconf_seller_terms_page'));
				
				if ($information_info) {
					$this->data['ms_account_sellerinfo_terms_note'] = sprintf($this->language->get('ms_account_sellerinfo_terms_note'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('msconf_seller_terms_page'), 'SSL'), $information_info['title'], $information_info['title']);
				} else {
					$this->data['ms_account_sellerinfo_terms_note'] = '';
				}
			} else {
				$this->data['ms_account_sellerinfo_terms_note'] = '';
			}
		}

		if (!$seller || $seller['ms.seller_status'] == MsSeller::STATUS_UNPAID) {
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
		}

		////// Custom fields
		$this->data['seller_custom_fields'] = $this->config->get('msconf_seller_data_custom');
		$this->data['config_language_id'] = $this->config->get('config_language_id');
		$this->data['ms_images_files_upload_note'] = sprintf($this->language->get('ms_images_files_upload_note'), $this->config->get('msconf_seller_allowed_files_types'));
		////////////////////

		$this->data['seller_validation'] = $this->config->get('msconf_seller_validation');
		$this->data['link_back'] = $this->url->link('seller/account-dashboard', '', 'SSL');
		$this->data['seller_required_fields'] = $this->config->get('msconf_seller_required_fields');
		$this->data['seller_show_fields'] = $this->config->get('msconf_seller_show_fields');

		$this->document->setTitle(
			sprintf($this->language->get('ms_account_sellerinfo_heading') , $seller_title)
		);
		
		$this->data['ms_account_sellerinfo_heading'] = sprintf( $this->language->get('ms_account_sellerinfo_heading') , $seller_title );

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
				'text' => sprintf( $this->language->get('ms_account_sellerinfo_breadcrumbs') , $seller_title ),
				'href' => $this->url->link('seller/account-profile', '', 'SSL'),
			)
		));

		// Get avatars
		if ($this->config->get('msconf_avatars_for_sellers') == 1 || $this->config->get('msconf_avatars_for_sellers') == 2) {
			$this->data['predefined_avatars'] = $this->MsLoader->MsFile->getPredefinedAvatars();
		}
		
		$this->data['msconf_seller_google_api_key'] = $this->config->get('msconf_seller_google_api_key');

		$this->data['payment_gateways'] = [
			'bank_transfer',
			'pp_standard',
			'cod',
		];

		$this->data['active_payment_gateways'] = json_decode($seller['payment_methods'], true);
		
		list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('account-profile');
		$this->response->setOutput($this->render());
	}

	private function _addAvailablePaymentMethods($seller_id){

		$this->load->model('seller/allowed_payment_methods');
        
        $active_payment_methods = $this->model_seller_allowed_payment_methods->getActiveMethods();
    	
    	$this->model_seller_allowed_payment_methods->save($seller_id, array_column($active_payment_methods, 'code'));
	}

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
						$fileName = $this->MsLoader->MsFile->uploadImage($file, "sellers/{$this->customer->getId()}/");
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

	public function download(){
		if (!$this->customer->isLogged()) {
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}
		$download_path = $this->request->get['download'];
		\Filesystem::setPath($download_path);
		if (!headers_sent()) {
			if (\Filesystem::isExists()) {
				header('Content-Type: application/octet-stream');
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename="' . ( basename($download_path)) . '"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' .\Filesystem::getSize());
				if (ob_get_level()) ob_end_clean();
				//readfile($download_path, 'rb');
                echo \Filesystem::read();
				exit;
			} 
		}
	}

	private function _checkIfSellerHasEditPermission($seller){
		if($seller){
			$settings = $this->config->get('customer_profile_permissions');
    		$this->language->load_json('module/customer_profile_permissions');

	        if (\Extension::isInstalled('customer_profile_permissions') && 
	        	$settings['status'] == 1 &&
	        	$settings['limit_seller_edit_profile_data'] == 1){

	        	$this->session->data['warning'] = $this->language->get('editing_profile_error');
				$this->redirect($this->url->link('seller/account-dashboard', '', 'SSL'));
	        }   
		}
	}
}
?>
