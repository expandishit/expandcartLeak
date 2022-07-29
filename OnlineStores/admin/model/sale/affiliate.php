<?php
class ModelSaleAffiliate extends Model {

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="firstname", $orderType="ASC") {
        $query = "SELECT * FROM " . DB_PREFIX . "affiliate";
        //$query = ;
        $total = $totalFiltered = $this->db->query($query)->num_rows;
        
        if ( !empty($search) )
        {

            $search = $this->db->escape($search);
            $where = "";

            $where .= "(affiliate.firstname like '%{$search}%'
                    OR affiliate.lastname like '%{$search}%'
                    OR affiliate.email like '%{$search}%'
                    )";

            $query .= " WHERE " . $where;
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        $query .= " ORDER by {$orderColumn} {$orderType}";

        if ( $length != -1 )
        {
            $query .= " LIMIT " . $start . ", " . $length;
        }
        //$data = array_merge($this->db->query($query)->rows, array($totalFiltered));

        $data = array (
            'data' => $this->db->query($query)->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        //$data = $this->db->query($query)->rows;
        return $data;
    }

	public function addAffiliate($data)
    {
        if ( strtolower( $data['status'] ) == 'on' )
        {
            $data['status'] = 1;
        }

      	$this->db->query("INSERT INTO " . DB_PREFIX . "affiliate SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', code = '" . $this->db->escape($data['code']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '" . (int)$data['status'] . "',seller_affiliate_code = '" . $this->db->escape($data['seller_affiliate_code']) . "', seller_affiliate_commission = '" . $this->db->escape($data['seller_affiliate_commission']) . "',seller_affiliate_type ='" . $this->db->escape($data['seller_affiliate_type']) . "',  date_added = NOW()");
	}
	
	public function editAffiliate($affiliate_id, $data) {
        if ( strtolower( $data['status'] ) == 'on' )
        {
            $data['status'] = 1;
        }
		$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', code = '" . $this->db->escape($data['code']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '" . (int)$data['status'] . "', seller_affiliate_code = '" . $this->db->escape($data['seller_affiliate_code']) . "', seller_affiliate_commission = '" . $this->db->escape($data['seller_affiliate_commission']) . "',seller_affiliate_type ='" . $this->db->escape($data['seller_affiliate_type']) . "' WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	
      	if ($data['password']) {
        	$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE affiliate_id = '" . (int)$affiliate_id . "'");
      	}
	}
	
	public function deleteAffiliate($affiliate_id) {
        $this->load->model('sale/coupon');
        // get all user coupons
        $userCoupons = $this->model_sale_coupon->getCouponByAffiliateUser($affiliate_id);
        // revoke the coupons from the user
        foreach ($userCoupons->rows as $coupon){
            $coupon['from_affiliate'] = 0;
            // disable the coupon
            $coupon['status'] = 0;
            $this->model_sale_coupon->editCoupon($coupon['coupon_id'],$coupon);
        }
        // delete the user
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	}
	
	public function getAffiliate($affiliate_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	
		return $query->row;
	}
	
	public function getAffiliateByEmail($email) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "affiliate WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	
		return $query->row;
	}
			
	public function getAffiliates($data = array()) {
        $selections = "*, CONCAT(a.firstname, ' ', a.lastname) AS name, (SELECT SUM(at.amount) FROM " . DB_PREFIX . "affiliate_transaction at WHERE at.affiliate_id = a.affiliate_id GROUP BY at.affiliate_id) AS balance";
        if (isset($data['selections']) && is_array($data['selections'])) {
            $selections = implode(", ", $data['selections']);
        }

		$sql = "SELECT $selections  FROM " . DB_PREFIX . "affiliate a";

		$implode = array();
		
		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(a.firstname, ' ', a.lastname) LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "LCASE(a.email) = '" . $this->db->escape(utf8_strtolower($data['filter_email'])) . "'";
		}
		
		if (!empty($data['filter_code'])) {
			$implode[] = "a.code = '" . $this->db->escape($data['filter_code']) . "'";
		}
					
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "a.status = '" . (int)$data['filter_status'] . "'";
		}	
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "a.approved = '" . (int)$data['filter_approved'] . "'";
		}		
		
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(a.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
		
		$sort_data = array(
			'name',
			'a.email',
			'a.code',
			'a.status',
			'a.approved',
			'a.date_added'
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
	
	public function approve($affiliate_id) {
		$affiliate_info = $this->getAffiliate($affiliate_id);
			
		if ($affiliate_info) {
			$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET approved = '1' WHERE affiliate_id = '" . (int)$affiliate_id . "'");
			
			$this->language->load('mail/affiliate');
			$message  = sprintf($this->language->get('text_approve_welcome'), $this->config->get('config_name')[$this->config->get('config_language')]) . "\n\n";
			$message .= $this->language->get('text_approve_login') . "\n";
			$message .= HTTP_CATALOG . 'index.php?route=affiliate/login' . "\n\n";
			$message .= $this->language->get('text_approve_services') . "\n\n";
			$message .= $this->language->get('text_approve_thanks') . "\n";
			$message .= $this->config->get('config_name')[$this->config->get('config_language')];
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
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
			$mail->setTo($affiliate_info['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($this->config->get('config_name')[$this->config->get('config_language')]);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_approve_subject'), $this->config->get('config_name')[$this->config->get('config_language')]), ENT_QUOTES, 'UTF-8'));
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
	}
	
	public function getAffiliatesByNewsletter() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate WHERE newsletter = '1' ORDER BY firstname, lastname, email");
	
		return $query->rows;
	}
		
	public function getTotalAffiliates($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "affiliate";
		
		$implode = array();
		
		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		
		if (!empty($data['filter_email'])) {
			$implode[] = "LCASE(email) = '" . $this->db->escape(utf8_strtolower($data['filter_email'])) . "'";
		}	
				
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "status = '" . (int)$data['filter_status'] . "'";
		}			
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "approved = '" . (int)$data['filter_approved'] . "'";
		}		
				
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$query = $this->db->query($sql);
				
		return $query->row['total'];
	}
		
	public function getTotalAffiliatesAwaitingApproval() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "affiliate WHERE status = '0' OR approved = '0'");

		return $query->row['total'];
	}
	
	public function getTotalAffiliatesByCountryId($country_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "affiliate WHERE country_id = '" . (int)$country_id . "'");
		
		return $query->row['total'];
	}	
	
	public function getTotalAffiliatesByZoneId($zone_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "affiliate WHERE zone_id = '" . (int)$zone_id . "'");
		
		return $query->row['total'];
	}
		
	public function addTransaction($affiliate_id, $description = '', $amount = '', $order_id = 0,$isSellerAffiliate = 0) {
		$affiliate_info = $this->getAffiliate($affiliate_id);
        $affiliate_amount = $this->getTransactionTotal($affiliate_id);

		if ($affiliate_info) {
            if ( (int) $amount < 0 )
            {
                $positive_amount = (float) abs($amount);

                if ( $positive_amount > (float) $affiliate_amount )
                {
                    $amount = (float) ($affiliate_amount * -1);
                }

            }
//            else if ( (int) $amount == 0 && ( $description == 'reset' || $description == 'استرجاع' ) )
//            {
//                $amount = (float) ($affiliate_amount * -1);
//            }
            if ( $amount == 0 ) return false;

			$this->db->query("INSERT INTO " . DB_PREFIX . "affiliate_transaction SET affiliate_id = '" . (int)$affiliate_id . "', order_id = '" . (float)$order_id . "', description = '" . $this->db->escape($description) . "', amount = '" . $amount . "', date_added = NOW(),is_seller_affiliate ='".$isSellerAffiliate."' ");
		
			$this->language->load('mail/affiliate');
							
			$message  = sprintf($this->language->get('text_transaction_received'), $this->currency->format($amount, $this->config->get('config_currency'))) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $this->currency->format($this->getTransactionTotal($affiliate_id,$isSellerAffiliate), $this->config->get('config_currency')));

            if($isSellerAffiliate){
                $transactionSubject = $this->language->get('text_seller_transaction_subject');
            }else{
                $transactionSubject = $this->language->get('text_transaction_subject');
            }

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
			$mail->setTo($affiliate_info['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode(sprintf($transactionSubject, $this->config->get('config_name')), ENT_QUOTES, 'UTF-8'));
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
	}
	
	public function deleteTransaction($order_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_transaction WHERE order_id = '" . (int)$order_id . "'");
	}
	
	public function getTransactions($affiliate_id, $start = 0, $limit = 10,$conditions = array()) {
        $where = "";
        if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 10;
		}
		if(!is_null($conditions['is_seller_affiliate'])){
            $where = " AND is_seller_affiliate = '".$conditions['is_seller_affiliate']."' ";
        }
        $sql =  "SELECT * FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id ."'".$where." ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit;

		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getTotalTransactions($affiliate_id, $conditions = array() ) {
        $sql = "SELECT COUNT(*) AS total  FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'";

        if(!is_null($conditions['is_seller_affiliate'])){
           $sql .= " AND is_seller_affiliate ='". $conditions['is_seller_affiliate'] ."' ";
        }

		$query = $this->db->query($sql);
		return $query->row['total'];
	}
			
	public function getTransactionTotal($affiliate_id,$conditions = array()) {
        $sql = 	"SELECT SUM(amount) AS total FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "' ";
            if(!is_null($conditions['is_seller_affiliate'])){
                $sql .= " AND is_seller_affiliate ='".$conditions['is_seller_affiliate']."' ";
            }

        $query = $this->db->query($sql);
		return $query->row['total'];
	}	
	
	public function getTotalTransactionsByOrderId($order_id,$conditions = array() ) {
        $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "affiliate_transaction WHERE order_id = '" . (int)$order_id . "'";
            if (!is_null($conditions['is_seller_affiliate'])){
                $sql .= " AND is_seller_affiliate ='".$conditions['is_seller_affiliate']."' ";
            }
		$query = $this->db->query($sql);
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