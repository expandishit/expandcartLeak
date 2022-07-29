<?php
class MsMail extends Model {
	/**
	 * SELLER CONSTANTS
	 */
	const SMT_SELLER_ACCOUNT_CREATED = 1;
	const SMT_SELLER_ACCOUNT_AWAITING_MODERATION = 2;
	const SMT_SELLER_ACCOUNT_APPROVED = 3;
	const SMT_SELLER_ACCOUNT_DECLINED = 4;
	const SMT_SELLER_ACCOUNT_DISABLED = 17;
	const SMT_SELLER_ACCOUNT_ENABLED = 18;
	const SMT_SELLER_ACCOUNT_MODIFIED = 19;
	const SMT_SELLER_ORDER = 20;
	const SMT_SELLER_VOTE = 23;
	const SMT_SELLER_CONTACT = 24;

	const AMT_SELLER_ACCOUNT_CREATED = 101;
	const AMT_SELLER_ACCOUNT_AWAITING_MODERATION = 102;

	const SMT_SELLER_CUSTOM_INVOICE = 25;
	/**
	 * PRODUCT CONSTANTS
	 */
	const SMT_PRODUCT_CREATED = 5;
	const SMT_PRODUCT_AWAITING_MODERATION = 6;
	const SMT_PRODUCT_MODIFIED = 21;
	const SMT_PRODUCT_PURCHASED = 11;

	const AMT_NEW_PRODUCT_AWAITING_MODERATION = 104;
	const AMT_EDIT_PRODUCT_AWAITING_MODERATION = 105;
	const AMT_PRODUCT_PURCHASED = 106;
	const AMT_PRODUCT_CREATED = 103;

	/**
	 * WITHDRAW CONSTANTS
	 */
	const SMT_WITHDRAW_REQUEST_SUBMITTED = 12;
	const SMT_WITHDRAW_REQUEST_COMPLETED = 13;
	const SMT_WITHDRAW_REQUEST_DECLINED = 14;
	const SMT_WITHDRAW_PERFORMED = 15;

	const AMT_WITHDRAW_REQUEST_SUBMITTED = 107;
	const AMT_WITHDRAW_REQUEST_COMPLETED = 108;

	/**
	 * OTHER CONSTANTS
	 */
	const SMT_TRANSACTION_PERFORMED = 16;
	const SMT_PRIVATE_MESSAGE = 22;
	const SMT_REMIND_LISTING = 30;

	public function __construct($registry) {
		parent::__construct($registry);
		$this->errors = array();
	}

	private function _modelExists($model) {
		$file  = DIR_APPLICATION . 'model/' . $model . '.php';
		return file_exists($file);
	}

	private function _getRecipients($mail_type) {
		$email = '';
		if ($mail_type < 100 && $this->registry->get('customer')) {
			$email = $this->registry->get('customer')->getEmail();
		}

		if ($email) {
			return $email;
		}

		return $this->config->get('msconf_notification_email') ? $this->config->get('msconf_notification_email') : $this->config->get('config_email');
	}

	//TODO
	private function _getAddressee($mail_type) {
		if ($mail_type < 100)
			return $this->registry->get('customer')->getFirstname();
		else
			return '';//$this->registry->get('customer')->getFirstname();
	}
	
	private function _getOrderProducts($order_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "order_product
				WHERE order_id = " . (int)$order_id;
		
		$res = $this->db->query($sql);

		return $res->rows;
	}

	public function sendOrderMails($order_id) {
		$order_products = $this->_getOrderProducts($order_id);
		
		if (!$order_products)
			return false;
			
		$mails = array();
		foreach ($order_products as $product) {
			$seller_id = $this->MsLoader->MsProduct->getSellerId($product['product_id']);
			if ($seller_id) {
				$mails[$seller_id] = array(
					'type' => MsMail::SMT_PRODUCT_PURCHASED,
					'data' => array(
						'recipients' => $this->MsLoader->MsSeller->getSellerEmail($seller_id),
						'addressee' => $this->MsLoader->MsSeller->getSellerName($seller_id),
						'order_id' => $order_id,
						'seller_id' => $seller_id
					)
				);
			}
		}

		$this->sendMails($mails);
	}
	
	public function sendMails($mails) {
		foreach ($mails as $mail) {
			if (!isset($mail['data'])) {
				$this->sendMail($mail['type']);
			} else {

				$this->sendMail($mail['type'], $mail['data']);
			}
		}
	}
	
	public function sendMail($mail_type, $data = array()) {

		if (isset($data['product_id']) && $data['product_id']) {
			$product = $this->MsLoader->MsProduct->getProduct($data['product_id']);
			$n = reset($product['languages']);
			$product['name'] = $n['name'];
		}

		if (isset($data['order_id'])) {
			if ($this->_modelExists('checkout/order')) {
				$this->load->model('checkout/order');
				$this->load->model('account/order');
				$order_info = $this->model_checkout_order->getOrder($data['order_id']);
			} else {
				$this->load->model('sale/order');
				$order_info = $this->model_sale_order->getOrder($data['order_id']);
			}
		}
		
		if (isset($data['seller_id'])) {
			$seller = $this->MsLoader->MsSeller->getSeller($data['seller_id']);
			$seller['total_products'] = $this->MsLoader->MsSeller->getSellerProductsCount($data['seller_id']);
		}		



		//$message .= sprintf($this->language->get('ms_mail_regards'), HTTP_SERVER) . "\n" . $this->config->get('config_name');

		$mail = new Mail();
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname = $this->config->get('config_smtp_host');
		$mail->username = $this->config->get('config_smtp_username');
		$mail->password = $this->config->get('config_smtp_password');
		$mail->port = $this->config->get('config_smtp_port');
		$mail->timeout = $this->config->get('config_smtp_timeout');

		if ($mail_type == self::SMT_SELLER_CONTACT && isset($data['recipients'])) {
			$mail->setTo($data['recipients']);
		} else if (!isset($data['recipients'])) {
			$mail->setTo($this->_getRecipients($mail_type));
		}else if (isset($data['customer_email'])) {
			$mail->setTo($data['customer_email']);
		}else {
			$mail->setTo($data['recipients']);
		}
		// commented to add edit to send the reply from the client to the seller 
		//$mail->setFrom($this->config->get('msconf_notification_email') ? $this->config->get('msconf_notification_email') : $this->config->get('config_email'));
		$mail->setFrom($data['customer_email'] ? $data['customer_email'] : ($this->config->get('msconf_notification_email') ? $this->config->get('msconf_notification_email') : $this->config->get('config_email')));
		$mail->setSender(is_array($this->config->get('config_name')) ? $this->config->get('config_name')[$this->config->get('config_language')] : $this->config->get('config_name'));
                
		if (!isset($data['addressee'])) {
			if ($mail_type < 100) {
				$mail_text = sprintf($this->language->get('ms_mail_greeting'), $this->_getAddressee($mail_type));
			} else {
				$mail_text = $this->language->get('ms_mail_greeting_no_name');
			}
		} else {
			$mail_text = sprintf($this->language->get('ms_mail_greeting'), $data['addressee']);
		}

		$mail_subject = '['.$this->config->get('config_name')[$this->config->get('config_admin_language')].'] ';
		$has_mail_template = true;
		$name = trim($this->config->get('config_name'));

		switch($mail_type) {
			// seller
			case self::SMT_SELLER_ACCOUNT_CREATED:
				$mail_subject .= $this->language->get('ms_mail_subject_seller_account_created');
				$mail_text .= sprintf($this->language->get('ms_mail_seller_account_created'), $this->config->get('config_name'));
				break;
			case self::SMT_SELLER_ACCOUNT_AWAITING_MODERATION:
				$mail_subject .= $this->language->get('ms_mail_subject_seller_account_awaiting_moderation');
				$mail_text .= sprintf($this->language->get('ms_mail_seller_account_awaiting_moderation'), $name);
				break;
			case self::SMT_SELLER_ACCOUNT_MODIFIED:
				$mail_subject .= $this->language->get('ms_mail_subject_seller_account_modified');
				$mail_text .= sprintf($this->language->get('ms_mail_seller_account_modified'), $name, $this->language->get('ms_seller_status_' . $seller['ms.seller_status']));
				break;
			case self::SMT_PRODUCT_AWAITING_MODERATION:
				$has_mail_template = false;
				$mail_subject .= $this->language->get('ms_mail_subject_product_awaiting_moderation');
				$mail_text .= sprintf($this->language->get('ms_mail_product_awaiting_moderation'), $product['name'], $name);
				break;
			case self::SMT_PRODUCT_MODIFIED:
				$mail_subject .= $this->language->get('ms_mail_subject_product_modified');
				$mail_text .= sprintf($this->language->get('ms_mail_product_modified'), $product['name'], $name, $this->language->get('ms_product_status_' . $product['mp.product_status']));
				break;
			case self::SMT_PRODUCT_PURCHASED:
				$order_products = $this->MsLoader->MsOrderData->getOrderProducts(array('order_id' => $data['order_id'], 'seller_id' => $data['seller_id']));

				$products = '';
				foreach ($order_products as $p) {
					if ($p['quantity'] > 1) $products .= "{$p['quantity']} x "; 
					$products .= "{$p['name']}\t" . $this->currency->format($p['seller_net_amt'], $this->config->get('config_currency')) . "\n";

					if ($this->_modelExists('account/order')) {
						$options = $this->model_account_order->getOrderOptions($data['order_id'], $p['order_product_id']);
					} else {
						$options = $this->model_sale_order->getOrderOptions($data['order_id'], $p['order_product_id']);
					}

					foreach ($options as $option)
					{
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
						}

						$option['value']	=  utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value;

						$products .= "\r\n";
						$products .= "- {$option['name']} : {$option['value']}";
					}

					$products .= "\n";
				}
			
				$total = $this->currency->format($this->MsLoader->MsOrderData->getOrderTotal($data['order_id'], array('seller_id' => $data['seller_id'])), $this->config->get('config_currency'));
			
				$mail_subject .= $this->language->get('ms_mail_subject_product_purchased');
				if (!$this->config->get('msconf_hide_emails_in_emails')) {
					$mail_text .= sprintf($this->language->get('ms_mail_product_purchased'), $name, $order_info['firstname'] . ' ' . $order_info['lastname'], $order_info['email'], $products, $total);
				} else {
					$mail_text .= sprintf($this->language->get('ms_mail_product_purchased_no_email'), $name, $order_info['firstname'] . ' ' . $order_info['lastname'], $products, $total);
				}

				$order_info['comment'] = $this->MsLoader->MsOrderData->getOrderComment(array('order_id' => $data['order_id'], 'seller_id' => $data['seller_id']));
				if ($order_info['comment']) {
					$mail_text .= sprintf($this->language->get('ms_mail_product_purchased_comment'), $order_info['comment']);
				}

				if ($this->config->get('msconf_provide_buyerinfo') == 1 || ($this->config->get('msconf_provide_buyerinfo') == 2 && $product['shipping'] == 1))
				{
					$mail_text .= sprintf($this->language->get('ms_mail_product_purchased_info'), $order_info['shipping_firstname'], $order_info['shipping_lastname'], $order_info['shipping_company'], $order_info['shipping_address_1'], $order_info['shipping_address_2'], $order_info['shipping_city'], $order_info['shipping_postcode'], $order_info['shipping_zone'], $order_info['shipping_country']);
				}
				break;				
			
			case self::SMT_WITHDRAW_REQUEST_SUBMITTED:
				$mail_subject .= $this->language->get('ms_mail_subject_withdraw_request_submitted');
				$mail_text .= sprintf($this->language->get('ms_mail_withdraw_request_submitted'));
				break;
			case self::SMT_WITHDRAW_REQUEST_COMPLETED:
				$mail_subject .= $this->language->get('ms_mail_subject_withdraw_request_completed');
				$mail_text .= sprintf($this->language->get('ms_mail_withdraw_request_completed'));
				break;
			case self::SMT_WITHDRAW_REQUEST_DECLINED:
				$mail_subject .= $this->language->get('ms_mail_subject_withdraw_request_declined');
				$mail_text .= sprintf($this->language->get('ms_mail_withdraw_request_declined'), $name);
				break;
			/*
			case self::SMT_WITHDRAW_PERFORMED:
				$mail_subject .= $this->language->get('ms_mail_subject_withdraw_performed');
				$mail_text .= sprintf($this->language->get('ms_mail_withdraw_performed'), $name);
				break;
			*/
			case self::SMT_TRANSACTION_PERFORMED:
				$mail_subject .= $this->language->get('ms_mail_subject_transaction_performed');
				$mail_text .= sprintf($this->language->get('ms_mail_transaction_performed'), $name);
				break;
				
			case self::SMT_SELLER_ORDER:
			case self::SMT_SELLER_CONTACT:
				$mail_subject .= $this->language->get('ms_mail_subject_seller_contact');
				if (!$this->config->get('msconf_hide_emails_in_emails')) {
					$mail_text .= sprintf($this->language->get('ms_mail_seller_contact'), $data['customer_name'], $data['customer_email'], isset($data['product_id']) ? $product['name'] : '', $data['customer_message']);
				} else {
					$mail_text .= sprintf($this->language->get('ms_mail_seller_contact_no_mail'), $data['customer_name'], isset($data['product_id']) ? $product['name'] : '', $data['customer_message']);
				}
				break;

			case self::SMT_PRIVATE_MESSAGE:
				$mail_subject .= $this->language->get('ms_mail_subject_private_message');
				$mail_text .= sprintf($this->language->get('ms_mail_private_message'), $data['customer_name'], $data['title'], $data['customer_message']);
				break;
			
			case self::SMT_SELLER_VOTE:
				$mail_subject .= $this->language->get('ms_mail_subject_seller_vote');
				$mail_text .= sprintf($this->language->get('ms_mail_seller_vote_message'), $data['customer_name'], $data['title'], $data['customer_message']);
				break;
			
			case self::SMT_REMIND_LISTING:
				$mail_subject .= $this->language->get('ms_mail_subject_remind_listing');
				$mail_text .= sprintf($this->language->get('ms_mail_seller_remind_listing'), $product['name']);
				break;
			
			case self::SMT_SELLER_ACCOUNT_ENABLED:
				$mail_subject .= $this->language->get('ms_mail_subject_seller_account_enabled');
				$mail_text .= sprintf($this->language->get('ms_mail_seller_account_enabled'), $name);
				break;
			case self::SMT_SELLER_ACCOUNT_DISABLED:
				$mail_subject .= $this->language->get('ms_mail_subject_seller_account_disabled');
				$mail_text .= sprintf($this->language->get('ms_mail_seller_account_disabled'), $name);
				break;
			case self::SMT_SELLER_CUSTOM_INVOICE:
				$mail_subject .= $this->language->get('ms_mail_subject_seller_custom_invoice');
				$mail_text .= sprintf($this->language->get('ms_mail_message_seller_custom_invoice'), 
									  $name,
									  $data['vars']['seller_nickname'],
									  $data['vars']['order_id'],
									  $data['vars']['orders_url']
									 );
				break;

			///////////////// Admin /////////////////////////////////
			case self::AMT_PRODUCT_CREATED:
				$mail_subject .= $this->language->get('ms_mail_admin_subject_product_created');
				$mail_text .= sprintf($this->language->get('ms_mail_admin_product_created'), $product['name'], $name);
				break;
			
			case self::AMT_SELLER_ACCOUNT_CREATED:
				$mail_subject .= $this->language->get('ms_mail_admin_subject_seller_account_created');
				$mail_text .= sprintf($this->language->get('ms_mail_admin_seller_account_created'), $name, $data['seller_name'], $data['customer_name'], $data['customer_email']);
				break;
			case self::AMT_SELLER_ACCOUNT_AWAITING_MODERATION:
				$mail_subject .= $this->language->get('ms_mail_admin_subject_seller_account_awaiting_moderation');
				$mail_text .= sprintf($this->language->get('ms_mail_admin_seller_account_awaiting_moderation'), $name, $data['seller_name'], $data['customer_name'], $data['customer_email']);
				break;
				
			case self::AMT_NEW_PRODUCT_AWAITING_MODERATION:
				$has_mail_template = false;
				$mail_subject .= $this->language->get('ms_mail_admin_subject_new_product_awaiting_moderation');
				$mail_text .= sprintf($this->language->get('ms_mail_admin_new_product_awaiting_moderation'), $product['name'], $name);
				break;

			case self::AMT_EDIT_PRODUCT_AWAITING_MODERATION:
				$mail_subject .= $this->language->get('ms_mail_admin_subject_edit_product_awaiting_moderation');
				$mail_text .= sprintf($this->language->get('ms_mail_admin_edit_product_awaiting_moderation'), $product['name'], $name);
				break;
			
			case self::AMT_WITHDRAW_REQUEST_SUBMITTED:
				$mail_subject .= $this->language->get('ms_mail_admin_subject_withdraw_request_submitted');
				$mail_text .= sprintf($this->language->get('ms_mail_admin_withdraw_request_submitted'));
				break;

			default:
				break;
		}

		if (isset($data['message']) && !empty($data['message'])) {
			$mail_text .= sprintf($this->language->get('ms_mail_message'), $data['message']);			
		}

		$mail_text .= sprintf($this->language->get('ms_mail_ending'), $name);

		$mail->setSubject($mail_subject);
        if ($this->config->get('custom_email_templates_status') && !isset($cet) && !isset($data['no_template'])) {

            include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

            $cet = new CustomEmailTemplates($this->registry);

            $_CLASS    = isset($data['class']) ? $data['class'] : __CLASS__;
            $_FUNCTION = isset($data['function']) ? $data['function'] : __FUNCTION__;
            $_VARS     = isset($data['vars']) ? $data['vars'] : get_defined_vars();

            $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => $_CLASS, 'function' => $_FUNCTION, 'vars' => $_VARS, 'has_mail_template' => $has_mail_template));

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
		$mail->setText($mail_text);

		 $mail->send();
        if ($this->config->get('custom_email_templates_status')) {
            $mail->sendBccEmails();
        }
	}
}
?>
