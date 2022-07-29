<?php
class ModelSaleReturn extends Model {

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="customer_id", $orderType="ASC")
    {
        $language_id = $this->config->get('config_language_id') ?: 1;

        $query = "SELECT *, CONCAT(r.firstname, ' ', r.lastname) AS customer, (SELECT rs.name FROM " . DB_PREFIX . "return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '{$language_id}') AS status FROM `" . DB_PREFIX . "return` r";
        
        $total = $totalFiltered = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "CONCAT(r.firstname, ' ', r.lastname) like '%" . $this->db->escape($search) . "%' or r.order_id like '%" . $this->db->escape($search) . "%' or r.product like '%" . $this->db->escape($search) . "%'";
            $query .= " WHERE " . $where;
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

	public function addReturn($data) {
        if ($this->config->get('custom_email_templates_status')) {
            include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

            $cet = new CustomEmailTemplates($this->registry);

            $cet_result = $cet->getEmailTemplate(array('type' => 'catalog', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

            if ($cet_result) {
                if ($cet_result['html'] || $cet_result['bcc_html']) {
                    if (version_compare(VERSION, '2.0.2') < 0) {
                        $mail = new Mail($this->config->get('config_mail'));
                    } else {
                        $mail = new Mail();
                        $mail->protocol = $this->config->get('config_mail_protocol');
                        $mail->parameter = $this->config->get('config_mail_parameter');
                        $mail->smtp_hostname = $this->config->get('config_mail_smtp_host');
                        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
                    }

                    $mail->setReplyTo(
                        $this->config->get('config_mail_reply_to'),
                        $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                        $this->config->get('config_email')
                    );

                    $mail->setTo($this->request->post['email']);
                    $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
                    $mail->setSender(is_array($this->config->get('config_name')) ? $this->config->get('config_name')[$this->config->get('config_language')] : $this->config->get('config_name')); //sender name
                    $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));

                    if ($cet_result['html']) {
                        $mail->setNewHtml($cet_result['html']);
                    }

                    if ($cet_result['text']) {
                        $mail->setNewText($cet_result['text']);
                    }

                    if ($cet_result['bcc_html']) {
                        $mail->setBccHtml($cet_result['bcc_html']);
                    }

                    $mail->setBccEmails($cet_result['bcc_emails']);
                    $mail->send();

                    $mail->sendBccEmails();
                }
            }
        }
      	$this->db->query("INSERT INTO `" . DB_PREFIX . "return` SET order_id = '" . (int)$data['order_id'] . "', product_id = '" . (int)$data['product_id'] . "', customer_id = '" . (int)$data['customer_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', product = '" . $this->db->escape($data['product']) . "', model = '" . $this->db->escape($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', opened = '" . (int)$data['opened'] . "', return_reason_id = '" . (int)$data['return_reason_id'] . "', return_action_id = '" . (int)$data['return_action_id'] . "', return_status_id = '" . (int)$data['return_status_id'] . "', comment = '" . $this->db->escape($data['comment']) . "', date_ordered = '" . $this->db->escape($data['date_ordered']) . "', date_added = NOW(), date_modified = NOW()");
      	
      	$return_id = $this->db->getLastId();
		$data['is_product_quantity_added'] = $data['is_product_quantity_added']  ? $data['is_product_quantity_added']  : 0 ;
      	if( isset($data['return_products']) ){
      		
      		$products = $data['return_products'];
      		foreach ($products as $product) {
      			$this->db->query("
      				INSERT INTO `" . DB_PREFIX . "return_product` 
      				SET 
      				return_id = '" . (int)$return_id . "', 
      				product_id = '" . (int)$product['id'] . "', 
      				name = '" . $this->db->escape($product['name']) . "', 
      				model = '" . $this->db->escape($product['model']) . "', 
      				quantity = '" . (int)$product['quantity'] . "',
					is_product_quantity_added = '".$data['is_product_quantity_added']."'
				");	
      		}
		  }
		  
		  
        return $return_id;
	}
	
	public function editReturn($return_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "return` SET order_id = '" . (int)$data['order_id'] . "', product_id = '" . (int)$data['product_id'] . "', customer_id = '" . (int)$data['customer_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', product = '" . $this->db->escape($data['product']) . "', model = '" . $this->db->escape($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', opened = '" . (int)$data['opened'] . "', return_reason_id = '" . (int)$data['return_reason_id'] . "', return_action_id = '" . (int)$data['return_action_id'] . "', return_status_id = '" . (int)$data['return_status_id'] . "', comment = '" . $this->db->escape($data['comment']) . "', date_ordered = '" . $this->db->escape($data['date_ordered']) . "', date_modified = NOW() WHERE return_id = '" . (int)$return_id . "'");
		
		if( isset($data['return_products']) ){
      		$this->db->query("DELETE FROM `" . DB_PREFIX . "return_product` WHERE return_id= '" . (int)$return_id ."'");
      		
      		$products = $data['return_products'];

      		foreach ($products as $product) {
      			$this->db->query("
      				INSERT INTO `" . DB_PREFIX . "return_product` 
      				SET 
      				return_id = '" . (int)$return_id . "', 
      				product_id = '" . (int)$product['id'] . "', 
      				name = '" . $this->db->escape($product['name']) . "', 
      				model = '" . $this->db->escape($product['model']) . "', 
      				quantity = '" . (int)$product['quantity'] . "',
					is_product_quantity_added = '".$data['is_product_quantity_added']."'
      				");	
      		}
      	}

	}
	
	public function editReturnAction($return_id, $return_action_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "return` SET return_action_id = '" . (int)$return_action_id . "' WHERE return_id = '" . (int)$return_id . "'");
	}
		
	public function deleteReturn($return_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "return` WHERE return_id = '" . (int)$return_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "return_history WHERE return_id = '" . (int)$return_id . "'");
		return true;
	}
	
	public function getReturn($return_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = r.customer_id) AS customer FROM `" . DB_PREFIX . "return` r WHERE r.return_id = '" . (int)$return_id . "'");
		
		$row = $query->row;
		//Check If new DB structure (return_product)
		if( $row['product_id'] <= 0 ){
			$products_query = $this->db->query("
				SELECT * 
				FROM `" . DB_PREFIX . "return_product` 
				WHERE return_id = '" . (int)$return_id . "'
				");

			$row['products'] = $products_query->rows;
		}
		else{
			$row['products'] = [
				[
					'return_id'  => $row['return_id'],
                    'product_id' => $row['product_id'],
                    'name'       => $row['product'],
                    'model'      => $row['model'],
					'quantity'   => $row['quantity'],
					'is_product_quantity_added' => $this->checkProdQuantityAdded( $row['product_id'], $row['return_id']),
					'is_amount_added_to_customer' => $this->checkIfOrderAmountAddedToCustomer( $row['product_id'], $row['return_id'])
				]
			];
		}
		
		return $row;
	}
		
	public function getReturns($data = array()) {
		$sql = "SELECT *, CONCAT(r.firstname, ' ', r.lastname) AS customer, (SELECT rs.name FROM " . DB_PREFIX . "return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status, ( SELECT rr.name FROM " . DB_PREFIX . "return_reason rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '{$this->config->get('config_language_id')}' ) AS return_reason FROM `" . DB_PREFIX . "return` r";

		$implode = array();
		
		if (!empty($data['filter_return_id'])) {
			$implode[] = "r.return_id = '" . (int)$data['filter_return_id'] . "'";
		}
		
		if (!empty($data['filter_order_id'])) {
			$implode[] = "r.order_id = '" . (int)$data['filter_order_id'] . "'";
		}
						
		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(r.firstname, ' ', r.lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
		}
		
		if (!empty($data['filter_product'])) {
			$implode[] = "r.product = '" . $this->db->escape($data['filter_product']) . "'";
		}	
		
		if (!empty($data['filter_model'])) {
			$implode[] = "r.model = '" . $this->db->escape($data['filter_model']) . "'";
		}	
						
		if (!empty($data['filter_return_status_id'])) {
			$implode[] = "r.return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
		}	
		
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(r.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$implode[] = "DATE(r.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}
				
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
		
		$sort_data = array(
			'r.return_id',
			'r.order_id',
			'customer',
			'r.product',
			'r.model',
			'status',
			'r.date_added',
			'r.date_modified'
		);	
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY r.return_id";	
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
						
	public function getTotalReturns($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "return`r";
		
		$implode = array();
		
		if (!empty($data['filter_return_id'])) {
			$implode[] = "r.return_id = '" . (int)$data['filter_return_id'] . "'";
		}
				
		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(r.firstname, ' ', r.lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
		}
		
		if (!empty($data['filter_order_id'])) {
			$implode[] = "r.order_id = '" . $this->db->escape($data['filter_order_id']) . "'";
		}
		
		if (!empty($data['filter_product'])) {
			$implode[] = "r.product = '" . $this->db->escape($data['filter_product']) . "'";
		}	
		
		if (!empty($data['filter_model'])) {
			$implode[] = "r.model = '" . $this->db->escape($data['filter_model']) . "'";
		}	
				
		if (!empty($data['filter_return_status_id'])) {
			$implode[] = "r.return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
		}	
		
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(r.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		
		if (!empty($data['filter_date_modified'])) {
			$implode[] = "DATE(r.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}
				
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$query = $this->db->query($sql);
				
		return $query->row['total'];
	}
		
	public function getTotalReturnsByReturnStatusId($return_status_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "return` WHERE return_status_id = '" . (int)$return_status_id . "'");
				
		return $query->row['total'];
	}	
			
	public function getTotalReturnsByReturnReasonId($return_reason_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "return` WHERE return_reason_id = '" . (int)$return_reason_id . "'");
				
		return $query->row['total'];
	}	
	
	public function getTotalReturnsByReturnActionId($return_action_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "return` WHERE return_action_id = '" . (int)$return_action_id . "'");
				
		return $query->row['total'];
	}	
	
	public function addReturnHistory($return_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "return` SET return_status_id = '" . (int)$data['return_status_id'] . "', date_modified = NOW() WHERE return_id = '" . (int)$return_id . "'");

		$user_id = $this->user->getId();
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "return_history SET return_id = '" . (int)$return_id . "', return_status_id = '" . (int)$data['return_status_id'] . "', notify = '" . (isset($data['notify']) ? (int)$data['notify'] : 0) . "', comment = '" . $this->db->escape(strip_tags($data['comment'])) . "', user_id = '" . $user_id . "', date_added = NOW()");

        $returnInfo = $this->getReturn($return_id);

        $this->load->model('sale/order');

        $order_info = $this->model_sale_order->getOrder($returnInfo['order_id']);

        if ($data['notify_by_sms'] && Extension::isInstalled('smshare')) {
            $this->load->model('module/smshare');
            $this->model_module_smshare->notify_or_not_on_order_status_update($order_info, $data);
        }


      	if ($data['notify']) {
        	$return_query = $this->db->query("SELECT *, rs.name AS status FROM `" . DB_PREFIX . "return` r LEFT JOIN " . DB_PREFIX . "return_status rs ON (r.return_status_id = rs.return_status_id) WHERE r.return_id = '" . (int)$return_id . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
			if ($return_query->num_rows) {
				$this->language->load('mail/return');

				$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $return_id);

				$message  = $this->language->get('text_return_id') . ' ' . $return_id . "\n";
				$message .= $this->language->get('text_date_added') . ' ' . date($this->language->get('date_format_short'), strtotime($return_query->row['date_added'])) . "\n\n";
				$message .= $this->language->get('text_return_status') . "\n";
				$message .= $return_query->row['status'] . "\n\n";

				if ($data['comment']) {
					$message .= $this->language->get('text_comment') . "\n\n";
					$message .= strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')) . "\n\n";
				}

				$message .= $this->language->get('text_footer');

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
				$mail->setTo($return_query->row['email']);
				$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
	    		$mail->setSender(is_array($this->config->get('config_name')) ? $this->config->get('config_name')[$this->config->get('config_language')] : $this->config->get('config_name'));
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
		}
	}
		
	public function getReturnHistories($return_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 10;
		}	
				
		$query = $this->db->query("SELECT rh.date_added, rs.name AS status, rh.comment, rh.notify, rh.user_id, u.username, u.firstname, u.lastname, u.email FROM " . DB_PREFIX . "return_history rh LEFT JOIN " . DB_PREFIX . "return_status rs ON rh.return_status_id = rs.return_status_id LEFT JOIN " . DB_PREFIX . "user u ON rh.user_id = u.user_id WHERE rh.return_id = '" . (int)$return_id . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY rh.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}	
	
	public function getTotalReturnHistories($return_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "return_history WHERE return_id = '" . (int)$return_id . "'");

		return $query->row['total'];
	}	
		
	public function getTotalReturnHistoriesByReturnStatusId($return_status_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "return_history WHERE return_status_id = '" . (int)$return_status_id . "' GROUP BY return_id");

		return $query->row['total'];
	}

    /**
     * Get total return orders by order status ids.
     *
     * @param array $return_status_ids
     *
     * @return array|bool
     */
    public function getTotalReturnsByReturnStatusIds($return_status_ids)
    {
        $query = [];
        $query[] = 'SELECT COUNT(*) AS total FROM `' . DB_PREFIX . 'return` WHERE';
        $query[] = 'return_status_id IN (' . implode(',', $return_status_ids) . ')';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row['total'];
        }

        return false;
    }

    /**
     * Get total return histories by order status ids.
     *
     * @param array $return_status_ids
     *
     * @return array|bool
     */
    public function getTotalReturnHistoriesByReturnStatusIds($return_status_ids)
    {
        $query = [];
        $query[] = 'SELECT COUNT(*) AS total FROM `' . DB_PREFIX . 'return_history` WHERE';
        $query[] = 'return_status_id IN (' . implode(',', $return_status_ids) . ')';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row['total'];
        }

        return false;
    }

    /**
     * Get total return orders by return action ids.
     *
     * @param array $return_action_ids
     *
     * @return array|bool
     */
    public function getTotalReturnsByReturnActionIds($return_action_ids)
    {
        $query = [];
        $query[] = 'SELECT COUNT(*) AS total FROM `' . DB_PREFIX . 'return` WHERE';
        $query[] = 'return_action_id IN (' . implode(',', $return_action_ids) . ')';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row['total'];
        }

        return false;
    }

    /**
     * Get total return orders by return reason ids.
     *
     * @param array $return_reason_ids
     *
     * @return array|bool
     */
    public function getTotalReturnsByReturnReasonIds($return_reason_ids)
    {
        $query = [];
        $query[] = 'SELECT COUNT(*) AS total FROM `' . DB_PREFIX . 'return` WHERE';
        $query[] = 'return_reason_id IN (' . implode(',', $return_reason_ids) . ')';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row['total'];
        }

        return false;
	}
	
	public function checkProdQuantityAdded($product_id , $return_id){
		$query = "SELECT is_product_quantity_added  FROM `". DB_PREFIX ."return_product` WHERE return_id = ".$return_id." AND product_id =".$product_id;
		$res = $this->db->query($query);
		return $res->row['is_product_quantity_added'];
	}

	public function updateQtyAdded($val, $return_id , $product_id){
		$this->db->query("UPDATE `" . DB_PREFIX . "return_product` SET is_product_quantity_added = '" . (int)$val. "' WHERE return_id = '".$return_id. "' AND product_id = '".$product_id."'");
	}

    public function checkIfOrderAmountAddedToCustomer($product_id , $return_id)
    {
        $sql= "SELECT return_id FROM `" . DB_PREFIX . "return_product` WHERE return_id = ".(int)$return_id." AND product_id = ".(int)$product_id;

        $result = $this->db->query($sql);

        return ($result->row['is_amount_added_to_customer'] == 1) ? TRUE : FALSE;
    }

    public function updateCustomerBalanceAdded($val, $return_id , $product_id){
        $this->db->query("UPDATE `" . DB_PREFIX . "return_product` SET is_amount_added_to_customer = '" . (int)$val. "' WHERE return_id = '".$return_id. "' AND product_id = '".$product_id."'");
    }
}
?>