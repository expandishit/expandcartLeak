<?php
class ModelAccountCustomer extends Model
{
    /**
     * Customer table.
     *
     * @var string
     */
	private $customerTable = DB_PREFIX . "customer";
	private $customer_id;
	private $customer_group_info;

	public function addCustomer($data,$modData=null,$isActive=null) {
        // return to new login function if identity mode is enabled
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            return $this->addCustomerV2($data, $modData, $isActive);
        }
        
		if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $data['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
        //check SMS enable to customer once he completes the registration
        $smshare_patterns   = $this->config->get('smshare_config_number_filtering');
        $number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');
//to check if send sms was activate
		
		/*
		 * this line added to keep the old telephone without smsFilter to use it at whatsapp 
		 */
		$whatsapp_telephone =  $data['telephone'];
		
        if($this->config->get('smshare_config_notify_customer_by_sms_on_registration')            &&
            SmsharePhonenumberFilter::isNumberAuthorized($data['telephone'], $smshare_patterns)    &&
            SmsharePhonenumberFilter::passTheNumberSizeFilter($data['telephone'], $number_size_filter)
        )
        {
            //to rewritePhoneNumber befor insert db
        $data['telephone'] = SmsharePhonenumberFilter::rewritePhoneNumber($data['telephone'], $this->config);
       }else
        {
            $data['telephone'] = $data['telephone'];
        }
		
		$this->load->model('account/customer_group');
		
		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
		$isActiveVar = isset($isActive) ? $isActive : false;
		if ($isActiveVar) {
			$address_id = 0;
			$pquery = "INSERT INTO " . DB_PREFIX . "customer SET store_id = '" . (int)$this->config->get('config_store_id')."'";
			if ($modData['f_name_show'])
				$pquery .=  ", firstname = '" . $this->db->escape($data['firstname']) . "'";
			if ($modData['l_name_show'])
				$pquery .= ", lastname = '" . $this->db->escape($data['lastname']) . "'";
			$pquery .= ", email = '" . $this->db->escape(trim($data['email'])) . "'";
			if($data['register_login_by_phone_number'] || $modData['mob_show'] )
				$pquery .= ", telephone = '" . $this->db->escape($data['telephone']) . "'";
			if($data['gender'] || $modData['gender_show'] )
				$pquery .= ", gender = '" . $this->db->escape($data['gender']) . "'";
			if($data['dob'] || $modData['dob_show'] )
				$pquery .= ", dob = '" . $this->db->escape($data['dob']) . "'";
			
			if ($modData['fax_show'])
				$pquery .= ", fax = '" . $this->db->escape($data['fax']) . "'";
			$pquery .= ", salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9))
				. "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "'";
			if ($modData['subsribe_show'])
				$pquery .= ", newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "'";
			$pquery .= ", customer_group_id = '" . (int)$customer_group_id . "', ip = '"
				. $this->db->escape($this->request->server['REMOTE_ADDR'])
				. "', status = '1', approved = '" . (int)!$customer_group_info['approval'] . "', date_added = NOW()";
			$this->db->query($pquery);
			$customer_id = $this->db->getLastId();
			//Remove email from newsletter subscribers table if any
			$this->removeFromNewsLetterSubscribersTable($data['email'], $customer_id);


			if ($modData['company_show'] || $modData['companyId_show'] || $modData['address1_show'] || $modData['address2_show']
				|| $modData['city_show'] || $modData['pin_show'] || $modData['country_show']
				|| ($modData['state_show'] && $modData['country_show']))
			{
				$pquery = "INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id ."'";
				if ($modData['f_name_show'])
					$pquery .= ", firstname = '" . $this->db->escape($data['firstname']) . "'";
				if ($modData['l_name_show'])
					$pquery .= ", lastname = '" . $this->db->escape($data['lastname']) . "'";
				if ($modData['company_show'])
					$pquery .= ", company = '" . $this->db->escape($data['company']) . "'";
				if ($modData['companyId_show'])
					$pquery .= ", company_id = '" . $this->db->escape($data['company_id']) . "'";
					$pquery .= ", tax_id = '" . $this->db->escape($data['tax_id']) . "'";
				if ($modData['address1_show'])
					$pquery .= ", address_1 = '" . $this->db->escape($data['address_1']) . "'";
				if ($modData['address2_show'])
					$pquery .= ", address_2 = '" . $this->db->escape($data['address_2']) . "'";
				if ($modData['city_show'])
					$pquery .= ", city = '" . $this->db->escape($data['city']) . "'";
				if ($modData['pin_show'])
					$pquery .= ", postcode = '" . $this->db->escape($data['postcode']) . "'";
				if ($modData['country_show'])
					$pquery .= ", country_id = '" . (int)$data['country_id'] . "'";
				if ($modData['state_show'] && $modData['country_show']){
					$pquery .= ", zone_id = '" . (int)$data['zone_id'] . "'";
					$pquery .= ", area_id = '" . (int)$data['area_id'] . "'";}
				$this->db->query($pquery);
				$address_id = $this->db->getLastId();
				$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
			}
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer SET store_id = '" . (int)$this->config->get('config_store_id') . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', customer_group_id = '" . (int)$customer_group_id . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', status = '1', approved = '" . (int)!$customer_group_info['approval'] . "', date_added = NOW()");
			$customer_id = $this->db->getLastId();
			$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', company_id = '" . $this->db->escape($data['company_id']) . "', tax_id = '" . $this->db->escape($data['tax_id']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', area_id = '" . (int)$data['area_id'] . "'");
			$address_id = $this->db->getLastId();
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
			//Remove email from newsletter subscribers table if any
			$this->removeFromNewsLetterSubscribersTable($data['email'], $customer_id);
		}
		
		$queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");
		if($queryRewardPointInstalled->num_rows) {
			$this->load->model('rewardpoints/observer');
			if (!$customer_group_info['approval']) {
				$data['status'] = 1;
			} else {
				$data['status'] = 0;
			}
			$this->model_rewardpoints_observer->afterAddCustomer($customer_id, $data);
		}

		 // Odoo create customer if app is installed
		 if (\Extension::isInstalled('odoo') && $this->config->get('odoo')['status'] 
		 && $this->config->get('odoo')['customers_integrate']) 
		 {
		  $this->load->model('module/odoo/customers');
		  $this->model_module_odoo_customers->createCustomer($customer_id, $data);
		 }

        $token = null;
		// This variable will be used to skip the Sms activation if $customer_group_info['email_activation']
        // were set to yes, and to send activation sms message if ($customer_group_info['sms_activation'] were.
        // set to yes and $customer_group_info['email_activation'] where set to no)
        $activationMethod = null;
        if (isset($customer_group_info['email_activation']) && $customer_group_info['email_activation'] == 1) {
			
			$this->updateCustomerApprovalByCustomerId($customer_id, 2);
            $activation = $this->load->model('account/activation', ['return' => true]);
            $token = $activation->generateToken();
			$data['confirm_code']=$token;
			
            $activation->insertNewActivationCode([
                'customer_id' => $customer_id,
                'token' => $token,
                'activation_type' => 1,
            ]);
            $activationMethod = 'email';
        }
        if (
            isset($customer_group_info['sms_activation']) &&
            $customer_group_info['sms_activation'] == 1 &&
            $activationMethod != 'email' &&
            $this->session->data['isPhoneVerified'] == 0
        ) {
            $this->updateCustomerApprovalByCustomerId($customer_id, 3);
            $activation = $this->load->model('account/activation', ['return' => true]);
            $smsToken = $activation->generateSmsToken();
            $activation->insertNewActivationCode([
                'customer_id' => $customer_id,
                'token' => $smsToken,
                'activation_type' => 2,
            ]);
            $activationMethod = 'sms';
        }
		if($data['email'] != ""){
			$this->language->load_json('mail/customer');
		
			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
			
			$message = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";
			if (isset($token)) {
				$message .= $this->language->get('text_email_activation') . " : \n";
			} else if (!$customer_group_info['approval']) {
				$message .= $this->language->get('text_login') . "\n";
			} else {
				$message .= $this->language->get('text_approval') . "\n";
			}
			if (isset($token)) {
				
				$activationUrl = $this->url->link('account/activation/activate', 'token=' . $token, 'SSL');
				$message .= $activationUrl . "\n\n";
			} else {
				$message .= $this->url->link('account/login', '', 'SSL') . "\n\n";
			}
			$message .= $this->language->get('text_services') . "\n\n";
			$message .= $this->language->get('text_thanks') . "\n";
			$message .= $this->config->get('config_name');
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
            $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );
			$mail->setTo($data['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
				include_once(DIR_SYSTEM . 'library/custom_email_templates.php');
				$cet = new CustomEmailTemplates($this->registry);
				$cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));
				if ($cet_result) {
					if ($cet_result['subject']) {
						$mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
					}
					if($cet_result['html']) {
						$mail->setNewHtml($cet_result['html']);
					}	
				
					if ($cet_result['text']) {
						$mail->setNewText($cet_result['text']);
					}
					if ($cet_result['bcc_html']) {
						$mail->setBccHtml($cet_result['bcc_html']);
					}
					if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
						$path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);
						$mail->addAttachment($path_to_invoice_pdf);
					}
					if (isset($this->request->post['fattachments'])) {
						if ($this->request->post['fattachments']) {
							foreach ($this->request->post['fattachments'] as $attachment) {
								foreach ($attachment as $file) {
									$mail->addAttachment($file);
								}
							}
						}
					}
					$mail->setBccEmails($cet_result['bcc_emails']);
				}
			}
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
			if ($this->config->get('custom_email_templates_status')) {
				$mail->sendBccEmails();
			}
			unset($mail);
			unset($message);
		}
		
		if(! $this->config->get('smshare_cfg_scar_email_confirm_status') &&
			! $this->config->get('smshare_cfg_scar_sms_confirm_status')   ){
			$this->load->model('module/smshare');
			if (isset($activationMethod) && $activationMethod == 'sms') {
                $this->model_module_smshare->sendActivationSms($data, $smsToken);
            } else {
                $this->model_module_smshare->notify_or_not_customer_or_admins_on_registration($data);
            }
		}

		
		//whatsapp
		
		if (\Extension::isInstalled('whatsapp')) {
            $this->load->model('module/whatsapp');
			$data['telephone'] = $whatsapp_telephone;
			try{
				$this->model_module_whatsapp->notify_or_not_customer_or_admins_on_registration($data);
			}catch(Exception $e){
			}
            			
        }
		
		//whatsapp-v2
		if (\Extension::isInstalled('whatsapp_cloud')) {
            $this->load->model('module/whatsapp_cloud');
			$data['telephone'] = $whatsapp_telephone;
			try{
				$this->model_module_whatsapp_cloud->registrationNotification($data);
			}catch(Exception $e){
			}
            			
        }
		// Send to main admin email if new account email is enabled
		if ($this->config->get('config_account_mail')) {
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom(!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
            $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );
			$mail->setSender($this->config->get('config_name'));
			
			$message  = $this->language->get('text_signup') . "\n\n";
			$message .= $this->language->get('text_website') . ' ' . $this->config->get('config_name') . "\n";
			if(isset($data['firstname']))
				$message .= $this->language->get('text_firstname') . ' ' . $data['firstname'] . "\n";
			if(isset($data['lastname']))
				$message .= $this->language->get('text_lastname') . ' ' . $data['lastname'] . "\n";
			$message .= $this->language->get('text_customer_group') . ' ' . $customer_group_info['name'] . "\n";
			if(isset($data['company']))
				if ($data['company']) {
					$message .= $this->language->get('text_company') . ' '  . $data['company'] . "\n";
				}
			
			$message .= $this->language->get('text_email') . ' '  .  $data['email'] . "\n";
			if(isset($data['telephone']))
				$message .= $this->language->get('text_telephone') . ' ' . $data['telephone'] . "\n";
			
			//$mail->setTo($this->config->get('config_email'));
			$mail->setSubject(html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));
			
			// Comment this code because it send the same email template to admin and 
			// there isn`t template code for admin that new customer did registration
			/*
			
			if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                include_once(DIR_SYSTEM . 'library/custom_email_templates.php');
                $cet = new CustomEmailTemplates($this->registry);
                $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));
                if ($cet_result) {
                    if ($cet_result['subject']) {
                        $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                    }
                    if ($cet_result['html']) {
                        $mail->setNewHtml($cet_result['html']);
                    }
                    if ($cet_result['text']) {
                        $mail->setNewText($cet_result['text']);
                    }
                    if ($cet_result['bcc_html']) {
                        $mail->setBccHtml($cet_result['bcc_html']);
                    }
                    if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                        $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);
                        $mail->addAttachment($path_to_invoice_pdf);
                    }
                    if (isset($this->request->post['fattachments'])) {
                        if ($this->request->post['fattachments']) {
                            foreach ($this->request->post['fattachments'] as $attachment) {
                                foreach ($attachment as $file) {
                                    $mail->addAttachment($file);
                                }
                            }
                        }
                    }
                    $mail->setBccEmails($cet_result['bcc_emails']);
                }
			}
			*/
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }
			
			// Send to additional alert emails if new account email is enabled
			$emails = explode(',', $this->config->get('config_alert_emails'));
			
			foreach ($emails as $email) {
				if (strlen($email) > 0 && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $email)) {
					$mail->setTo($email);
					$mail->send();
                    if ($this->config->get('custom_email_templates_status')) {
                        $mail->sendBccEmails();
                    }
				}
			}
		}
        // set customer id and customer group info
        $this->customer_id = $customer_id;
		$this->customer_group_info = $customer_group_info;
		
		//fire delete product trigger for zapier if installed
        $this->load->model('module/zapier');
        if ($this->model_module_zapier->isInstalled()) {
            $customer = $this->getCustomer($customer_id);
            if ($customer)
				$this->model_module_zapier->newCustomerTrigger($customer);
        }

        $store_statistics = new StoreStatistics();
        $store_statistics->store_statistcs_push('customers', 'create', [
        	'customer_id' => $customer_id,
            'email' => $data['email'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname']
        ]);
	}
    // get customer id and customer group info
	public function getCustomerIdAndGroupInfo()
    {
        return ["customer_id"=>$this->customer_id,'customer_group_info'=>$this->customer_group_info];
    }

	public function editCustomer($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function editPassword($email, $password) {
      	$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}

	public function editPasswordByPhone($phone, $password) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE LOWER(telephone) = '" . $this->db->escape(utf8_strtolower($phone)) . "'");
	}
	  
	
	public function editNewsletter($newsletter) {
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");
        if($queryRewardPointInstalled->num_rows) {
            $this->load->model('rewardpoints/observer');
            $this->model_rewardpoints_observer->afterSubscribeNewsletter($this->customer->getId(), array('newsletter' => $newsletter));
        }
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = '" . (int)$newsletter . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}
					
	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
		
		return $query->row;
	}
	
	public function getCustomerByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		
		return $query->row;
	}
	
	public function getCustomerByPhone($phone_number) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE telephone = '" . $this->db->escape(utf8_strtolower($phone_number)) . "'");
		
		return $query->row;
	}
		
	public function getCustomerByToken($token) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE token = '" . $this->db->escape($token) . "' AND token != ''");
		
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET token = ''");
		
		return $query->row;
	}
		
	public function getCustomers($data = array()) {
		$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cg.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group cg ON (c.customer_group_id = cg.customer_group_id) ";
		$implode = array();
		
		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(c.firstname, ' ', c.lastname)) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}
		
		if (isset($data['filter_email']) && !is_null($data['filter_email'])) {
			$implode[] = "LCASE(c.email) = '" . $this->db->escape(utf8_strtolower($data['filter_email'])) . "'";
		}
		
		if (isset($data['filter_customer_group_id']) && !is_null($data['filter_customer_group_id'])) {
			$implode[] = "cg.customer_group_id = '" . $this->db->escape($data['filter_customer_group_id']) . "'";
		}	
		
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}	
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c.approved = '" . (int)$data['filter_approved'] . "'";
		}	
			
		if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
			$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}	
				
		if (isset($data['filter_date_added']) && !is_null($data['filter_date_added'])) {
			$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
		
		$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
		);	
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY name";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}		
		
		$query = $this->db->query($sql);
		
		return $query->rows;	
	}
		
	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		
		return $query->row['total'];
	}
	public function getTotalCustomersByPhone($phone_number) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE telephone = '" . $this->db->escape($phone_number) . "'");
		
		return $query->row['total'];
	}
	
	public function getIps($customer_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip` WHERE customer_id = '" . (int)$customer_id . "'");
		
		return $query->rows;
	}	
	
	public function isBanIp($ip) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ban_ip` WHERE ip = '" . $this->db->escape($ip) . "'");
		
		return $query->num_rows;
	}

	public function updateCustomerNewsletterByCustomerId($customer_id, $newsletter = 1)
	{
		$this->db->query("UPDATE customer SET newsletter = " . (int)$newsletter . " WHERE customer_id = " . (int)$customer_id);
	}
	/**
	 * Update a customer approved status using the customer id.
     *
     * @param int $customerId
     * @param int $approval
     *
     * return void
     */
	public function updateCustomerApprovalByCustomerId($customerId, $approval)
    {
        $queryString = [];
        $queryString[] = 'UPDATE `' . $this->customerTable . '` SET';
        $queryString[] = '`approved`=' . $approval;
        $queryString[] = 'WHERE customer_id=' . $customerId;
        $this->db->query(implode(' ', $queryString));
	}
	
	public function updateCustomerLanguage($customerId, $languageId) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET language_id = " . $languageId . " WHERE customer_id = " . $customerId);
	}

	public function getCustomerLanguageById($customerId) {
		$query = $this->db->query("SELECT `language_id` FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . $customerId . "'");
		return $query->row;
	}

	public function getCustomerLanguageByEmail($customerEmail) {
		$query = $this->db->query("SELECT `language_id` FROM `" . DB_PREFIX . "customer` WHERE email = '" . $customerEmail . "'");
		return $query->row;
    }
    
    /**
     * get customer by email or phone
     *
     * @param [array] $condition
     * @return void
     */
    public function getCustomerByEmailOrPhone(array $condition)
    {
        list($key, $value) = $condition;

        $value = $this->db->escape(utf8_strtolower($value));

        $query = $this->db->query(sprintf("SELECT * FROM %s WHERE LOWER(%s) = '%s'", DB_PREFIX . 'customer', $key, $value));

        return $query->num_rows ? $query->row : null;
    }
    
    public function getCustomerBy(array $conditions)
    {
        $queries = [];
        
        foreach ($conditions as $condition) {
            list($key, $value) = $condition;
            $value = $this->db->escape($value);
            $queries[] = "`$key` = '$value'";
        }
        
        if (!empty($queries)) {
            $result = $this->db->query(sprintf("SELECT * FROM %s WHERE %s", DB_PREFIX . 'customer', implode(' AND ', $queries)));
            return $result->num_rows ? $result->row : null;
        }
        
        return null;
    }

    /**
     * Update customer basic data
     * @param array $data the identity customer data 
     */
    public function updateCustomerBasicData(array $data = [])
    {
        $queries = [];
        $query = "UPDATE " . DB_PREFIX . "customer SET ";

        if (array_key_exists('firstname', $data)) $queries[] = "firstname = '" . $this->db->escape($data['firstname']) . "'";
        elseif (array_key_exists('name', $data)) $queries[] = "firstname = '" . $this->db->escape($data['name']) . "'";
        
        if (array_key_exists('email', $data)) $queries[] = "email = '" . $this->db->escape($data['email']) . "'";
        if (array_key_exists('telephone', $data)) $queries[] = "telephone = '" . $this->db->escape($data['telephone']) . "'";
        if (array_key_exists('dob', $data)) $queries[] = "dob = " .  (!!$this->db->escape($data['dob']) ? ("'" . $this->db->escape($data['dob']) . "'") : "null");
        if (array_key_exists('gender', $data)) $queries[] = "gender = '" . $this->db->escape($data['gender']) . "'";
        
        if (array_key_exists('company', $data)) $queries[] = "company = '" . $this->db->escape($data['company']) . "'";
        
        if (array_key_exists('id', $data)) $queries[] = "expand_id = '" . (int)$data['id'] . "'";
        elseif (array_key_exists('expand_id', $data)) $queries[] = "expand_id = '" . (int)$data['expand_id'] . "'";
        
        if (array_key_exists('newsletter', $data)) $queries[] = "newsletter = '" . (int)$data['newsletter'] . "'";
        
        if (array_key_exists('customer_group_id', $data)) {
            if (is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
                $customer_group_id = $data['customer_group_id'];
            } else {
                $customer_group_id = $this->config->get('config_customer_group_id');
            }
            
            $queries[] = "customer_group_id = '" . (int)$customer_group_id . "'";
            
            $this->load->model('account/customer_group');
            $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
            
            $queries[] = "approved = '" . (int)!$customer_group_info['approval'] . "'";
            
        }

        if (!empty($queries)) {
            $query .= implode(", ", $queries);
            $query .= " WHERE customer_id = '" . (int)$data['customer_id'] . "'";

            $this->db->query($query);
        }
    }
    
    /**
     * set customer addresses from identity server
     *
     * @param integer $customer_id
     * @param array $addresses list of addresses
     * @param bool $customRegisterAppSetup default false
     * @param array $appSchema customRegisterAppSetup schema
     * @return void
     */
    public function setCustomerAddresses(int $customer_id, array $addresses, $customRegisterAppSetup = false, $appSchema = [])
    {
        $escapeAttr = function (&$item) {
            $item = $this->db->escape($item);
        };

        foreach ($addresses as $addr) {
            array_walk($addr, $escapeAttr);
            $this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . $customer_id . "', firstname = '" . $addr['firstname'] . "', lastname = '" . $addr['lastname'] . "', company = '" . $addr['company'] . "', company_id = '" . $addr['company_id'] . "', tax_id = '" . $addr['tax_id'] . "', address_1 = '" . $addr['address_1'] . "', address_2 = '" . $addr['address_2'] . "', city = '" . $addr['city'] . "', location = '" . $addr['location'] . "', postcode = '" . $addr['postcode'] . "', country_id = '" . (int)$addr['country_id'] . "', zone_id = '" . (int)$addr['zone_id'] . "', address_expand_id = '" . (isset($addr['id']) ? (int)$addr['id'] : null) . "'");
            
            $isDefault = (int)(isset($addr['is_default']) ? $addr['is_default'] : (isset($addr['default']) ? $addr['default'] : 0));
             
            if ($isDefault) {
                $address_id = $this->db->getLastId();
                $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . $customer_id . "'");
            }
        }
    }
    
    public function setIdentityLastUpdate(int $customer_id, string $date)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET expand_updated_at = '" . $date .  "' WHERE customer_id = '" . $customer_id . "'");
    }
    
    public function updateExpandUpdatedAt(int $customer_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET expand_updated_at = current_timestamp() WHERE customer_id = '" . $customer_id . "'");
    }
    
    /**
     * The same addCustomer function 
     * but without saving addresses info
     * and keep tel number without applying any smsFilter rewrite changes
     * 
     * @return int the registered $customer_id
     */
    public function addCustomerV2($data, $modData = null, $isActive = null)
    {
        if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
            $customer_group_id = $data['customer_group_id'];
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }
        
        /*
		 * this line added to keep the old telephone without smsFilter to use it at whatsapp 
		 */
        $whatsapp_telephone =  $data['telephone'];

        $this->load->model('account/customer_group');

        $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

        $this->db->query("INSERT INTO " . DB_PREFIX . "customer SET store_id = '" . (int)$this->config->get('config_store_id') . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', gender = '" . $this->db->escape($data['gender']) . (!empty($data['dob']) ? "', dob = '" . $this->db->escape($data['dob']) : '') . "', fax = '" . $this->db->escape($data['fax']) . "', company = '" . $this->db->escape($data['company']) . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', customer_group_id = '" . (int)$customer_group_id . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', status = '1', approved = '" . (int)!$customer_group_info['approval'] . "', date_added = NOW()" . ", expand_id = '" . (isset($data['expand_id']) ? (int)$data['expand_id'] : null) . "'");
        $customer_id = $this->db->getLastId();
		
		//Remove email from newsletter subscribers table if any
		$this->removeFromNewsLetterSubscribersTable($data['email'], $customer_id);

        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");
        
        if ($queryRewardPointInstalled->num_rows) {
            $this->load->model('rewardpoints/observer');
            if (!$customer_group_info['approval']) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
            $this->model_rewardpoints_observer->afterAddCustomer($customer_id, $data);
        }
        
        $token = null;
        
        // This variable will be used to skip the Sms activation if $customer_group_info['email_activation']
        // were set to yes, and to send activation sms message if ($customer_group_info['sms_activation'] were.
        // set to yes and $customer_group_info['email_activation'] where set to no)
        $activationMethod = null;
        if (isset($customer_group_info['email_activation']) && $customer_group_info['email_activation'] == 1) {
            $this->updateCustomerApprovalByCustomerId($customer_id, 2);
            $activation = $this->load->model('account/activation', ['return' => true]);
            $token = $activation->generateToken();
            $data['confirm_code'] = $token;

            $activation->insertNewActivationCode([
                'customer_id' => $customer_id,
                'token' => $token,
                'activation_type' => 1,
            ]);
            $activationMethod = 'email';
        }
        if (
            isset($customer_group_info['sms_activation']) &&
            $customer_group_info['sms_activation'] == 1 &&
            $activationMethod != 'email' &&
            $this->session->data['isPhoneVerified'] == 0
        ) {
            $this->updateCustomerApprovalByCustomerId($customer_id, 3);
            $activation = $this->load->model('account/activation', ['return' => true]);
            $smsToken = $activation->generateSmsToken();
            $activation->insertNewActivationCode([
                'customer_id' => $customer_id,
                'token' => $smsToken,
                'activation_type' => 2,
            ]);
            $activationMethod = 'sms';
        }
        if ($data['email'] != "") {
            $this->language->load_json('mail/customer');

            $subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

            $message = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";
            if (isset($token)) {
                $message .= $this->language->get('text_email_activation') . " : \n";
            } else if (!$customer_group_info['approval']) {
                $message .= $this->language->get('text_login') . "\n";
            } else {
                $message .= $this->language->get('text_approval') . "\n";
            }
            if (isset($token)) {

                $activationUrl = $this->url->link('account/activation/activate', 'token=' . $token, 'SSL');
                $message .= $activationUrl . "\n\n";
            } else {
                $message .= $this->url->link('account/login', '', 'SSL') . "\n\n";
            }
            $message .= $this->language->get('text_services') . "\n\n";
            $message .= $this->language->get('text_thanks') . "\n";
            $message .= $this->config->get('config_name');
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->hostname = $this->config->get('config_smtp_host');
            $mail->username = $this->config->get('config_smtp_username');
            $mail->password = $this->config->get('config_smtp_password');
            $mail->port = $this->config->get('config_smtp_port');
            $mail->timeout = $this->config->get('config_smtp_timeout');
            $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );
            $mail->setTo($data['email']);
            $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
            if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                include_once(DIR_SYSTEM . 'library/custom_email_templates.php');
                $cet = new CustomEmailTemplates($this->registry);
                $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));
                if ($cet_result) {
                    if ($cet_result['subject']) {
                        $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                    }
                    if ($cet_result['html']) {
                        $mail->setNewHtml($cet_result['html']);
                    }

                    if ($cet_result['text']) {
                        $mail->setNewText($cet_result['text']);
                    }
                    if ($cet_result['bcc_html']) {
                        $mail->setBccHtml($cet_result['bcc_html']);
                    }
                    if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                        $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);
                        $mail->addAttachment($path_to_invoice_pdf);
                    }
                    if (isset($this->request->post['fattachments'])) {
                        if ($this->request->post['fattachments']) {
                            foreach ($this->request->post['fattachments'] as $attachment) {
                                foreach ($attachment as $file) {
                                    $mail->addAttachment($file);
                                }
                            }
                        }
                    }
                    $mail->setBccEmails($cet_result['bcc_emails']);
                }
            }
            $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
            $mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }
            unset($mail);
            unset($message);
        }

        if (
            !$this->config->get('smshare_cfg_scar_email_confirm_status') &&
            !$this->config->get('smshare_cfg_scar_sms_confirm_status')
        ) {
            $this->load->model('module/smshare');
            if (isset($activationMethod) && $activationMethod == 'sms') {
                $this->model_module_smshare->sendActivationSms($data, $smsToken);
            } else {
                $this->model_module_smshare->notify_or_not_customer_or_admins_on_registration($data);
            }
        }
        
        //whatsapp
        if (\Extension::isInstalled('whatsapp')) {
            $this->load->model('module/whatsapp');
            $data['telephone'] = $whatsapp_telephone;
            $this->model_module_whatsapp->notify_or_not_customer_or_admins_on_registration($data);
        }
		
        //whatsapp-v2
        if (\Extension::isInstalled('whatsapp_cloud')) {
            $this->load->model('module/whatsapp_cloud');
            $data['telephone'] = $whatsapp_telephone;
            $this->model_module_whatsapp_cloud->registrationNotification($data);
        }
        
        // Send to main admin email if new account email is enabled
        if ($this->config->get('config_account_mail')) {
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->hostname = $this->config->get('config_smtp_host');
            $mail->username = $this->config->get('config_smtp_username');
            $mail->password = $this->config->get('config_smtp_password');
            $mail->port = $this->config->get('config_smtp_port');
            $mail->timeout = $this->config->get('config_smtp_timeout');
            $mail->setTo($this->config->get('config_email'));
            $mail->setFrom(!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
            $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );
            $mail->setSender($this->config->get('config_name'));

            $message  = $this->language->get('text_signup') . "\n\n";
            $message .= $this->language->get('text_website') . ' ' . $this->config->get('config_name') . "\n";
            if (isset($data['firstname']))
                $message .= $this->language->get('text_firstname') . ' ' . $data['firstname'] . "\n";
            if (isset($data['lastname']))
                $message .= $this->language->get('text_lastname') . ' ' . $data['lastname'] . "\n";
            $message .= $this->language->get('text_customer_group') . ' ' . $customer_group_info['name'] . "\n";
            if (isset($data['company']))
                if ($data['company']) {
                    $message .= $this->language->get('text_company') . ' '  . $data['company'] . "\n";
                }

            $message .= $this->language->get('text_email') . ' '  .  $data['email'] . "\n";
            if (isset($data['telephone']))
                $message .= $this->language->get('text_telephone') . ' ' . $data['telephone'] . "\n";

            $mail->setSubject(html_entity_decode($this->language->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));
            $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
            $mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }

            // Send to additional alert emails if new account email is enabled
            $emails = explode(',', $this->config->get('config_alert_emails'));

            foreach ($emails as $email) {
                if (strlen($email) > 0 && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $email)) {
                    $mail->setTo($email);
                    $mail->send();
                    if ($this->config->get('custom_email_templates_status')) {
                        $mail->sendBccEmails();
                    }
                }
            }
        }
        // set customer id and customer group info
        $this->customer_id = $customer_id;
        $this->customer_group_info = $customer_group_info;

        //fire delete product trigger for zapier if installed
        $this->load->model('module/zapier');
        if ($this->model_module_zapier->isInstalled()) {
            $customer = $this->getCustomer($customer_id);
            if ($customer)
                $this->model_module_zapier->newCustomerTrigger($customer);
        }

        $store_statistics = new StoreStatistics();
        $store_statistics->store_statistcs_push('customers', 'create', [
        	'customer_id' => $customer_id,
            'email' => $data['email'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname']
        ]);

        return $customer_id;
    }

    public function removeFromNewsLetterSubscribersTable($email, $customer_id)
    {
    	$this->load->model("newsletter/subscriber");
    	$subscriber = $this->model_newsletter_subscriber->getSubscriberByEmail($email);
    	if( $subscriber ){
    		//Subscribe to newsletter for this new customer
    		$this->updateCustomerNewsletterByCustomerId($customer_id, 1);
    		//delete this email from unregistered subscriber
    		$this->model_newsletter_subscriber->deleteSubscriber($email);
    	}
    }
}
?>
