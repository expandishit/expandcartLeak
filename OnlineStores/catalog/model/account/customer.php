<?php
class ModelAccountCustomer extends Model {
	public function addCustomer($data,$modData=null,$isActive=null) {
		if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $data['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
		
		$this->load->model('account/customer_group');
		
		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		// $isActiveVar = isset($isActive) ? $isActive : false;

		// if ($isActiveVar) {
		// 	$address_id = 0;

		// 	$pquery = "INSERT INTO " . DB_PREFIX . "customer SET store_id = '" . (int)$this->config->get('config_store_id')."'";

		// 	if ($modData['f_name_show'])
		// 		$pquery .=  ", firstname = '" . $this->db->escape($data['firstname']) . "'";

		// 	if ($modData['l_name_show'])
		// 		$pquery .= ", lastname = '" . $this->db->escape($data['lastname']) . "'";

		// 	$pquery .= ", email = '" . $this->db->escape($data['email']) . "'";
			if ($modData['mob_show'] || (\Extension::isInstalled('trips')&&$this->config->get('trips')['status']==1))
				$pquery .= ", telephone = '" . $this->db->escape($data['telephone']) . "'";
			if ($modData['fax_show'])
				$pquery .= ", fax = '" . $this->db->escape($data['fax']) . "'";
		// 	$pquery .= ", telephone = '" . $this->db->escape($data['telephone']) . "'";

		// 	if ($modData['fax_show'])
		// 		$pquery .= ", fax = '" . $this->db->escape($data['fax']) . "'";

		// 	$pquery .= ", salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9))
		// 		. "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "'";

		// 	if ($modData['subsribe_show'])
		// 		$pquery .= ", newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "'";

		// 	$pquery .= ", customer_group_id = '" . (int)$customer_group_id . "', ip = '"
		// 		. $this->db->escape($this->request->server['REMOTE_ADDR'])
		// 		. "', status = '1', approved = '" . (int)!$customer_group_info['approval'] . "', date_added = NOW()";

		// 	$this->db->query($pquery);

		// 	$customer_id = $this->db->getLastId();

		// 	if ($modData['company_show'] || $modData['companyId_show'] || $modData['address1_show'] || $modData['address2_show']
		// 		|| $modData['city_show'] || $modData['pin_show'] || $modData['country_show']
		// 		|| ($modData['state_show'] && $modData['country_show']))
		// 	{
		// 		$pquery = "INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id ."'";

		// 		if ($modData['f_name_show'])
		// 			$pquery .= ", firstname = '" . $this->db->escape($data['firstname']) . "'";

		// 		if ($modData['l_name_show'])
		// 			$pquery .= ", lastname = '" . $this->db->escape($data['lastname']) . "'";

		// 		if ($modData['company_show'])
		// 			$pquery .= ", company = '" . $this->db->escape($data['company']) . "'";

		// 		if ($modData['companyId_show'])
		// 			$pquery .= ", company_id = '" . $this->db->escape($data['company_id']) . "'";

		// 			$pquery .= ", tax_id = '" . $this->db->escape($data['tax_id']) . "'";

		// 		if ($modData['address1_show'])
		// 			$pquery .= ", address_1 = '" . $this->db->escape($data['address_1']) . "'";

		// 		if ($modData['address2_show'])
		// 			$pquery .= ", address_2 = '" . $this->db->escape($data['address_2']) . "'";

		// 		if ($modData['city_show'])
		// 			$pquery .= ", city = '" . $this->db->escape($data['city']) . "'";

		// 		if ($modData['pin_show'])
		// 			$pquery .= ", postcode = '" . $this->db->escape($data['postcode']) . "'";

		// 		if ($modData['country_show'])
		// 			$pquery .= ", country_id = '" . (int)$data['country_id'] . "'";

		// 		if ($modData['state_show'] && $modData['country_show'])
		// 			$pquery .= ", zone_id = '" . (int)$data['zone_id'] . "'";

		// 		$this->db->query($pquery);

		// 		$address_id = $this->db->getLastId();

		// 		$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
		// 	}
		// } else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer SET store_id = '" . (int)$this->config->get('config_store_id') . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', gender = '" . $this->db->escape($data['gender']) . (!empty($data['dob']) ? "', dob = '" . $this->db->escape($data['dob']) : '') . "', fax = '" . $this->db->escape($data['fax']) . "', company = '" . $this->db->escape($data['company']) . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', customer_group_id = '" . (int)$customer_group_id . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', status = '1', approved = '" . (int)!$customer_group_info['approval'] . "', date_added = NOW()");

			$customer_id = $this->db->getLastId();

			// $this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', company_id = '" . $this->db->escape($data['company_id']) . "', tax_id = '" . $this->db->escape($data['tax_id']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "'");

			// $address_id = $this->db->getLastId();

			// $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
		// }

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

		$this->language->load('mail/customer');
		$storeName = $this->config->get('config_name') ?? '';
		if(is_array($storeName))
			$storeName = $storeName[$this->config->get('config_language')];
		$subject = sprintf($this->language->get('text_subject'), $storeName);
		
		$message = sprintf($this->language->get('text_welcome'), $storeName) . "\n\n";
		
		if (!$customer_group_info['approval']) {
			$message .= $this->language->get('text_login') . "\n";
		} else {
			$message .= $this->language->get('text_approval') . "\n";
		}
		
		$message .= $this->url->link('account/login', '', 'SSL') . "\n\n";
		$message .= $this->language->get('text_services') . "\n\n";
		$message .= $this->language->get('text_thanks') . "\n";
		$message .= $storeName;

		$mail = new Mail();
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname = $this->config->get('config_smtp_host');
		$mail->username = $this->config->get('config_smtp_username');
		$mail->password = $this->config->get('config_smtp_password');
		$mail->port = $this->config->get('config_smtp_port');
		$mail->timeout = $this->config->get('config_smtp_timeout');				
		$mail->setFrom($this->config->get('config_smtp_username'));
		$mail->setSender($storeName);

		if ($this->config->get('config_account_mail') && !empty($this->config->get('config_smtp_host')) && !empty($this->config->get('config_smtp_username'))) {
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
			$mail->setTo($data['email']);
			$mail->send();
			if ($this->config->get('custom_email_templates_status')) {
				$mail->sendBccEmails();
			}
		}

		// Send to main admin email if new account email is enabled
		if ($this->config->get('config_account_mail') && !empty($this->config->get('config_smtp_host')) && !empty($this->config->get('config_smtp_username'))) {
			$message  = $this->language->get('text_signup') . "\n\n";
			$message .= $this->language->get('text_website') . ' ' . $storeName . "\n";

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

			$mail->setTo($this->config->get('config_email'));
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

		if(! $this->config->get('smshare_cfg_scar_email_confirm_status') &&
			! $this->config->get('smshare_cfg_scar_sms_confirm_status')   ){

			$this->load->model('module/smshare');
			$this->model_module_smshare->notify_or_not_customer_or_admins_on_registration($data);
		}
		
		//whatsapp
		if (\Extension::isInstalled('whatsapp')) {
				$this->load->model('module/whatsapp');
				$this->model_module_whatsapp->notify_or_not_customer_or_admins_on_registration($data);			
			}
			
		//whatsapp-v2
		if (\Extension::isInstalled('whatsapp_cloud')) {
			$this->load->model('module/whatsapp_cloud');
			$this->model_module_whatsapp_cloud->registrationNotification($data);			
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
	
	public function editCustomer($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}

	public function editPassword($email, $password) {
      	$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
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

	public function getCustomerByMobile($telephone) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE telephone = '"
			. $this->db->escape($telephone) . "'");

		return $query->row;
	}


	public function getCustomerByMobileOrEmail($username) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE telephone = '"
			. $this->db->escape($username) . "' OR lower(email) = '"
			. $this->db->escape(utf8_strtolower($username)) . "'");

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

    public function updateFirebaseToken($customerId, $token, $type)
    {
        $query = vsprintf(
            'UPDATE customer SET firebase_token = "%s", device_type = "%s" WHERE customer_id = %d',
            [$token, $type, $customerId]
        );
        $this->db->query($query);
    }

	public function getCustomerNotifications($customer_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "firebase_notifications` WHERE customer_id = '" . (int)$customer_id. "'");
		return $query->rows;
	}

   	public function editPasswordById($customer_id, $password) {
      	$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE customer_id = '" . (int)$customer_id . "'");
	} 
}
?>
