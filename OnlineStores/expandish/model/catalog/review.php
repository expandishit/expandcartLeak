<?php
class ModelCatalogReview extends Model {		
	public function addReview($product_id, $data) {
        if (version_compare(VERSION, '2.0') < 0 && $this->config->get('custom_email_templates_status')) {
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
                    $mail->password = html_entity_decode($this->config->get('config_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->port = $this->config->get('config_smtp_port');
                    $mail->timeout = $this->config->get('config_smtp_timeout');
                    $mail->setTo((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
                    $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
                    $mail->setReplyTo(
                        $this->config->get('config_mail_reply_to'),
                        $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                        $this->config->get('config_email')
                    );
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
        if(!empty($data['rate'])){
            $this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$product_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . $this->db->escape(serialize($data['rate'])) . "', date_added = NOW()");

        }else{
            $sql = "INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$product_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . $this->db->escape($data['rating']) . "', date_added = NOW()";
            if($data['status']){
                $sql .= " , status = 1 ";
            }
            $this->db->query($sql);

        }
	}

	public function getReviewsByProductId($product_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 20;
		}		
		$query = $this->db->query("SELECT r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
		foreach ($query->rows as $key => $result) {
            // only unserialize multi values reviews
            if(strlen($query->rows[$key]['rating']) > 2)
			    $query->rows[$key]['rating'] = unserialize($query->rows[$key]['rating']);
			
		}
		return $query->rows;
	}

	public function getTotalReviewsByProductId($product_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row['total'];
	}

    ///////////////////// Seller Review Methods
    public function addSellerReview($seller_id, $data) {
        if (version_compare(VERSION, '2.0') < 0 && $this->config->get('custom_email_templates_status')) {
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
                    $mail->password = html_entity_decode($this->config->get('config_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->port = $this->config->get('config_smtp_port');
                    $mail->timeout = $this->config->get('config_smtp_timeout');

                    $mail->setTo((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
                    $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));

                    $mail->setReplyTo(
                        $this->config->get('config_mail_reply_to'),
                        $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                        $this->config->get('config_email')
                    );

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

        $this->db->query("INSERT INTO " . DB_PREFIX . "ms_seller_review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', seller_id = '" . (int)$seller_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");
    }

    public function getReviewsBySeller($seller_id, $start = 0, $limit = 20) {
        if ($start < 0) {
            $start = 0;
        }
        
        if ($limit < 1) {
            $limit = 20;
        }       
        
        $query = $this->db->query("SELECT r.review_id, r.author, r.rating, r.text, s.seller_id, s.nickname, r.date_added FROM " . DB_PREFIX . "ms_seller_review r LEFT JOIN " . DB_PREFIX . "ms_seller s ON (r.seller_id = s.seller_id) WHERE s.seller_id = '" . (int)$seller_id . "' AND r.status = '1' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
            
        return $query->rows;
    }

    public function getTotalReviewsBySeller($seller_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ms_seller_review r LEFT JOIN " . DB_PREFIX . "ms_seller s ON (r.seller_id = s.seller_id) WHERE s.seller_id = '" . (int)$seller_id . "' AND r.status = '1'");
        
        return $query->row['total'];
    }
    public function getReviewsOptionText($languageId){
        $query = $this->db->query(
            "SELECT t1.*,t2.name as enName FROM (SELECT * FROM review_options INNER JOIN review_options_description ON option_id = review_option_id WHERE review_options_description.language_id=".(int)$languageId." AND status=1 AND type='text') as t1 
            INNER JOIN( SELECT review_options.option_id,review_options_description.name FROM review_options INNER JOIN review_options_description ON option_id = review_option_id JOIN language ON language.language_id = review_options_description.language_id WHERE language.code='en' AND review_options.status=1 AND type='text' ) AS t2 
            WHERE t1.option_id=t2.option_id"
        );
        return $query->rows;
    }
    public function getReviewsOptionRate($languageId){
        $query = $this->db->query(
            "SELECT t1.*,t2.name as enName FROM (SELECT * FROM review_options INNER JOIN review_options_description ON option_id = review_option_id WHERE review_options_description.language_id=".(int)$languageId." AND status=1 AND type='rate') as t1 
            INNER JOIN( SELECT review_options.option_id,review_options_description.name FROM review_options INNER JOIN review_options_description ON option_id = review_option_id JOIN language ON language.language_id = review_options_description.language_id WHERE language.code='en' AND review_options.status=1 AND type='rate' ) AS t2 
            WHERE t1.option_id=t2.option_id"
        );
        return $query->rows;
    }
}
?>