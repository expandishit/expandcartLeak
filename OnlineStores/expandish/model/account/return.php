<?php
class ModelAccountReturn extends Model {
	public function addReturn($data) {
        if ($this->config->get('custom_email_templates_status')) {
            include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

            $cet = new CustomEmailTemplates($this->registry);

            $cet_result = $cet->getEmailTemplate(array('type' => 'catalog', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

            if ($cet_result) {
                if ($cet_result['html'] || $cet_result['bcc_html']) {
                    
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

        $this->db->query("INSERT INTO `" . DB_PREFIX . "return` SET product_id=".(int)$data['product_id'].", order_id = '" . (int)$data['order_id'] . "', customer_id = '" . (int)$this->customer->getId() . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', product = '" . $this->db->escape($data['product']) . "', model = '" . $this->db->escape($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', opened = '" . (int)$data['opened'] . "', return_reason_id = '" . (int)$data['return_reason_id'] . "', return_status_id = '" . (int)$this->config->get('config_return_status_id') . "', comment = '" . $this->db->escape($data['comment']) . "', date_ordered = '" . $this->db->escape($data['date_ordered']) . "', date_added = NOW(), date_modified = NOW()");
        
        
        $return_id = $this->db->getLastId();
        $data['is_product_quantity_added'] = $data['is_product_quantity_added']  ? $data['is_product_quantity_added']  : 0 ;
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "return_product` 
            SET 
            return_id = '" . (int)$return_id . "', 
            product_id = '" . $data['product_id']  . "', 
            name = '" . $data['product'] . "', 
            model = '" . $data['model'] . "', 
            quantity = '" . $data['quantity'] . "',
            is_product_quantity_added = '".$data['is_product_quantity_added']."'
            ");	

        return $return_id;
	}
	
	public function getReturn($return_id) {
		$query = $this->db->query("SELECT r.product_id, r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, (SELECT rr.name FROM " . DB_PREFIX . "return_reason rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '" . (int)$this->config->get('config_language_id') . "') AS reason, (SELECT ra.name FROM " . DB_PREFIX . "return_action ra WHERE ra.return_action_id = r.return_action_id AND ra.language_id = '" . (int)$this->config->get('config_language_id') . "') AS action, (SELECT rs.name FROM " . DB_PREFIX . "return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status, r.comment, r.date_ordered, r.date_added, r.date_modified FROM `" . DB_PREFIX . "return` r WHERE return_id = '" . (int)$return_id . "' AND customer_id = '" . $this->customer->getId() . "'");
		
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
        
        return $row;
	}
    
    public function getOrderProductsReturn($order_id) {
        $query = $this->db->query("SELECT r.product_id,(SELECT rs.name FROM " . DB_PREFIX . "return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status FROM `" . DB_PREFIX . "return` r 
        WHERE r.order_id = '" . (int)$order_id . "' AND r.customer_id = '" . $this->customer->getId() . "'");
		return $query->rows;
    }
    
	public function getReturns($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 20;
		}	
				
		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, rs.name as status, r.date_added FROM `" . DB_PREFIX . "return` r LEFT JOIN " . DB_PREFIX . "return_status rs ON (r.return_status_id = rs.return_status_id) WHERE r.customer_id = '" . $this->customer->getId() . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.return_id DESC LIMIT " . (int)$start . "," . (int)$limit);
		
		return $query->rows;
	}
			
	public function getTotalReturns() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "return`WHERE customer_id = '" . $this->customer->getId() . "'");
		
		return $query->row['total'];
	}
	
	public function getReturnHistories($return_id) {
		$query = $this->db->query("SELECT rh.date_added, rs.name AS status, rh.comment, rh.notify FROM " . DB_PREFIX . "return_history rh LEFT JOIN " . DB_PREFIX . "return_status rs ON rh.return_status_id = rs.return_status_id WHERE rh.return_id = '" . (int)$return_id . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY rh.date_added ASC");

		return $query->rows;
	}			
}
?>