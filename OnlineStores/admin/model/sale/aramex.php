<?php
class ModelSaleAramex extends Model {
	
	public function getWeightClassId($unit)
	{
	  return $this->db->query("SELECT wcd.unit,wcd.weight_class_id FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int)$this->config->get('config_language_id') . "' and LOWER( unit ) LIKE LOWER( '%$unit%' ) ");
	}
	
	public function getClientInfo()
    {
		$account=($this->config->get('aramex_account_number'))?$this->config->get('aramex_account_number'):'';
		$username=($this->config->get('aramex_email'))?$this->config->get('aramex_email'):'';
		$password=($this->config->get('aramex_password'))?$this->config->get('aramex_password'):'';
		$pin=($this->config->get('aramex_account_pin'))?$this->config->get('aramex_account_pin'):'';
		$entity=($this->config->get('aramex_account_entity'))?$this->config->get('aramex_account_entity'):'';
		$country_code=($this->config->get('aramex_account_country_code'))?$this->config->get('aramex_account_country_code'):'';
		return array(
			'AccountCountryCode'	=> $country_code,
			'AccountEntity'		 	=> $entity,
			'AccountNumber'		 	=> $account,
			'AccountPin'		 	=> $pin,
			'UserName'			 	=> $username,
			'Password'			 	=> $password,
			'Version'			 	=> 'v1.0',
			'Source'				=> 31
		);
      }
	  
	public function getWsdlPath(){
	
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				//$base = $this->config->get('config_ssl');
				$base = HTTPS_CATALOG;
		  } else {
				//$base = $this->config->get('config_url');
				$base = HTTP_CATALOG;
		  }
		$base = rtrim($base, "/");
		$wsdlBasePath = $base . '/aramex/wsdl';
		if($this->config->get('aramex_test')==1){
			$wsdlBasePath .='/TestMode';
		}
		return $wsdlBasePath;
	}
	
	public function getOrderStatusId($order_id)
	{
		$query = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id =".(int)$order_id);
		return $query->row['order_status_id'];
	
	}
	
	public function addOrderHistory($order_id, $data) {
		$this->load->model('sale/order');
		$order_status_id = $this->getOrderStatusId($order_id);
		
		
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (isset($data['notify']) ? (int)$data['notify'] : 0) . "', comment = '" . $this->db->escape(strip_tags($data['comment'])) . "', date_added = NOW()");

		$order_info = $this->model_sale_order->getOrder($order_id);

		// Send out any gift voucher mails
		if ($this->config->get('config_complete_status_id') == $order_status_id) {
			$this->load->model('sale/voucher');
            $this->load->model('sale/order');

			$results = $this->model_sale_order->getOrderVouchers($order_id);

			foreach ($results as $result) {
				$this->model_sale_voucher->sendVoucher($result['voucher_id']);
			}
		}

		if ($data['notify']) {
			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/order');

			$subject = sprintf($language->get('text_subject'), $order_info['store_name'], $order_id);

			$message  = $language->get('text_order') . ' ' . $order_id . "\n";
			$message .= $language->get('text_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

			if ($order_status_query->num_rows) {
				$message .= $language->get('text_order_status') . "\n";
				$message .= $order_status_query->row['name'] . "\n\n";
			}

			if ($order_info['customer_id']) {
				$message .= $language->get('text_link') . "\n";
				$message .= html_entity_decode($order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id, ENT_QUOTES, 'UTF-8') . "\n\n";
			}

			if ($data['comment']) {
				$message .= $language->get('text_comment') . "\n\n";
				$message .= strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')) . "\n\n";
			}
			$adminemail = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
			$message .= $language->get('text_footer');

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
			$mail->setTo($order_info['email'].','.$adminemail);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($order_info['store_name']);
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
		}

		//$this->load->model('payment/amazon_checkout');
		//$this->model_payment_amazon_checkout->orderStatusChange($order_id, $data);
		
	}
	public function getOrderHistoriesAWB($order_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}	

		$query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.comment LIKE '%Order No%' AND oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}
	
	public function checkAWB($order_id) 
	{
   		    $query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.comment LIKE '%Order No%' AND oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC");
			$shipments = $query->rows;
			if($shipments)
			{
					return 1;
			}else{
					return 0;
			}
	}
	public function checkShedulePickup($order_id) 
	{
   		    $query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.comment LIKE '%Pickup reference number%' AND oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC");
			$shipments = $query->rows;
			
			
			if($shipments)
			{
				
					return 1;
			}else{
					return 0;
			}
	}
	
	public function getAWB($order_id) 
	{
   		    $query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.comment LIKE '%Order No%' AND oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC");
			$shipments = $query->rows;
			if($shipments)
			{
						foreach($shipments as $key=>$comment)
						{
							$cmnt_txt = ($comment['comment'])?$comment['comment']:'';
							if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
								$awbno = substr($cmnt_txt,0, strpos($cmnt_txt,"- Order No")); 
							}
							else{				
								$awbno = strstr($cmnt_txt,"- Order No",true);
							}
								$awbno=trim($awbno,"AWB No.");					
								break;
						}
						
						return $awbno;
			}else{
					return 0;
			}
	}
	
	public function getAllAWB() 
	{
	
   		    $query = $this->db->query("SELECT oh.order_id,oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.comment LIKE '%Order No%' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY oh.order_id ORDER BY oh.date_added ASC");
			$shipments = $query->rows;
			
			if($shipments)
			{			
						foreach($shipments as $key=>$comment)
						{
							$awbno_arr[] = $comment['order_id'];
						}

						return $awbno_arr;
			}else{
					return 0;
			}
	}
	
	public function getAllPickup() 
	{
   		    $query = $this->db->query("SELECT oh.order_id,oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.comment LIKE '%Pickup reference number%' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY oh.order_id ORDER BY oh.date_added ASC");
			$shipments = $query->rows;
			
			
			if($shipments)
			{			
						foreach($shipments as $key=>$comment)
						{
							$awbno_arr[] = $comment['order_id'];
						}

						return $awbno_arr;
			}else{
					return 0;
			}
	}
	
	public function getTotalOrders($data = array()) {
		
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";

		if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
			$sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND total = '" . (float)$data['filter_total'] . "'";
		}

		if (count($data['tobegenrate'])>0) {
			$tobegenrate = implode(",",$data['tobegenrate']);
			$sql .= " AND order_id IN (" . $tobegenrate . ")";
		}else{
			$sql .= " AND order_id IN ('0')";
		}
		//echo $sql;
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

}
?>