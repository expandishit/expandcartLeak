<?php
class ModelAffiliateAffiliate extends Model {
	public function addAffiliate($data) {
      	$this->db->query("INSERT INTO " . DB_PREFIX . "affiliate SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', code = '" . $this->db->escape(uniqid()) . "', seller_affiliate_code = '" . $this->db->escape(uniqid()) . "', commission = '" . (float)$this->config->get('config_commission') . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '1', date_added = NOW()");
	
		$this->language->load_json('mail/affiliate');
		
		$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
		
		$message  = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";
		$message .= $this->language->get('text_approval') . "\n";
		$message .= $this->url->link('affiliate/login', '', 'SSL') . "\n\n";
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
		$mail->setTo($this->request->post['email']);
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
	}
	
	public function editAffiliate($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "' WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");
	}

	public function editPayment($data) {
      	$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "' WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");
	}
	
	public function editPassword($email, $password) {
      	$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	}
				
	public function getAffiliate($affiliate_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");
		
		return $query->row;
	}
	
	public function getAffiliateByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		
		return $query->row;
	}
		
	public function getAffiliateByCode($code) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

    public function getAffiliateBySellerCode($code) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate WHERE seller_affiliate_code = '" . $this->db->escape($code) . "'");

        return $query->row;
    }

	public function getTotalAffiliatesByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "affiliate WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		
		return $query->row['total'];
	}
	public function getAffiliateByCoupon($coupon){
        $query = $this->db->query("SELECT * FROM ". DB_PREFIX ."affiliate a  
        INNER JOIN ". DB_PREFIX . "coupon_to_affiliate cta
        ON `a`.`affiliate_id` = `cta`.`affiliate_id`
        AND `cta`.`coupon_id` = '" . $this->db->escape($coupon) . "'");
        return $query->row ;
    }
}
?>