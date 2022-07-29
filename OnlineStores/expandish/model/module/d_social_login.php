<?php
/*
 *	location: expandish/model/module/d_social_login.php
 */

class ModelModuleDSocialLogin extends Model {

	public function getCustomerByIdentifier($provider, $identifier) {
        $result = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer_authentication WHERE provider = '" . $this->db->escape($provider) . "' AND identifier = MD5('" . $this->db->escape($identifier) . "') LIMIT 1");

        if ($result->num_rows) {
            return (int) $result->row['customer_id'];
        } else {
            return false;
        }
    }

    public function getCustomerByIdentifierOld($provider, $identifier) {
        $query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DB_DATABASE."' AND TABLE_NAME = '" . DB_PREFIX . "customer' ORDER BY ORDINAL_POSITION"); 
        $result = $query->rows; 
        $columns = array();
        foreach($result as $column){
         $columns[] = $column['COLUMN_NAME'];
        }

        if(in_array(strtolower($provider).'_id', $columns)){
            $result = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE `".strtolower($provider)."_id` = '" . $this->db->escape($identifier) . "'");
            
            if ($result->num_rows) {
                return (int) $result->row['customer_id'];
            } else {
                return false;
            }
        }else{
            return false;
        } 
    }

    public function getCustomerByEmail($email) {
        $result = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' LIMIT 1");

        if ($result->num_rows) {
            return (int) $result->row['customer_id'];
        } else {
            return false;
        }
    }


    public function login($customer_id) {

        $result = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int) $customer_id . "' LIMIT 1");

        if (!$result->num_rows) {
            return false;
        }

        $this->session->data['customer_id'] = $result->row['customer_id'];

        // if ($result->row['cart'] && is_string($result->row['cart'])) {
        //     $cart = unserialize($result->row['cart']);

        //     foreach ($cart as $key => $value) {
        //         if (!array_key_exists($key, $this->session->data['cart'])) {
        //             $this->session->data['cart'][$key] = $value;
        //         } else {
        //             $this->session->data['cart'][$key] += $value;
        //         }
        //     }
        // }

        // if ($result->row['wishlist'] && is_string($result->row['wishlist'])) {
        //     if (!isset($this->session->data['wishlist'])) {
        //         $this->session->data['wishlist'] = array();
        //     }

        //     $wishlist = unserialize($result->row['wishlist']);

        //     foreach ($wishlist as $product_id) {
        //         if (!in_array($product_id, $this->session->data['wishlist'])) {
        //             $this->session->data['wishlist'][] = $product_id;
        //         }
        //     }
        // }

        $this->db->query("UPDATE " . DB_PREFIX . "customer SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', force_logout = '0' WHERE customer_id = '" . (int)$customer_id . "'");
        
        $this->customer->rememberMeFactory($customer_id);

        return true;
    }

    public function addAuthentication($data) {

       $this->db->query("INSERT INTO " . DB_PREFIX . "customer_authentication SET ".
                        "customer_id = '" . (int) $data['customer_id'] . "', ".
                        "provider = '" . $this->db->escape($data['provider']) . "', ".
                        "identifier = MD5('" . $this->db->escape($data['identifier']) . "'), ".
                        "web_site_url = '" . $this->db->escape($data['web_site_url']) . "', ".
                        "profile_url = '" . $this->db->escape($data['profile_url']) . "', ".
                        "photo_url = '" . $this->db->escape($data['photo_url']) . "', ".
                        "display_name = '" . $this->db->escape($data['display_name']) . "', ".
                        "description = '" . $this->db->escape($data['description']) . "', ".
                        "first_name = '" . $this->db->escape($data['first_name']) . "', ".
                        "last_name = '" . $this->db->escape($data['last_name']) . "', ".
                        "gender = '" . $this->db->escape($data['gender']) . "', ".
                        "language = '" . $this->db->escape($data['language']) . "', ".
                        "age = '" . $this->db->escape($data['age']) . "', ".
                        "birth_day = '" . $this->db->escape($data['birth_day']) . "', ".
                        "birth_month = '" . $this->db->escape($data['birth_month']) . "', ".
                        "birth_year = '" . $this->db->escape($data['birth_year']) . "', ".
                        "email = '" . $this->db->escape($data['email']) . "', ".
                        "email_verified = '" . $this->db->escape($data['email_verified']) . "', ".
                        "phone = '" . $this->db->escape($data['phone']) . "', ".
                        "address = '" . $this->db->escape($data['address']) . "', ".
                        "country = '" . $this->db->escape($data['country']) . "', ".
                        "region = '" . $this->db->escape($data['region']) . "', ".
                        "city = '" . $this->db->escape($data['city']) . "', ".
                        "zip = '" . $this->db->escape($data['zip']) . "', ".
                        "date_added = NOW()");
    }

    public function addCustomer($data) {

        $this->db->query("INSERT INTO " . DB_PREFIX . "customer SET store_id = '" . (int)$this->config->get('config_store_id') . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['phone']) . "', fax = '" . $this->db->escape($data['fax']) . "', password = '" . $this->db->escape(md5($data['password'])) . "', newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "', status = '1', date_added = NOW()");
        $customer_id = $this->db->getLastId();

        if ((int)$data['country_id']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "'");
            $address_id = $this->db->getLastId();

            $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
        }



        if (!$this->config->get('config_customer_approval')) {
            $this->db->query("UPDATE " . DB_PREFIX . "customer SET approved = '1' WHERE customer_id = '" . (int)$customer_id . "'");
        }

        if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
            $customer_group_id = $data['customer_group_id'];
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $this->load->model('account/customer_group');

//        $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

        // add reward points
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");
        if($queryRewardPointInstalled->num_rows) {

            $this->load->model('rewardpoints/observer');
            $data['status'] = 1; // user is always approved while social login
            $this->model_rewardpoints_observer->afterAddCustomer($customer_id, $data);
        }

        $this->language->load_json('mail/customer');

        $subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

        $message = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";

        if (!$this->config->get('config_customer_approval')) {
            $message .= $this->language->get('text_login') . "\n";
        } else {
            $message .= $this->language->get('text_approval') . "\n";
        }

        $message .= $this->url->link('account/login', '', 'SSL') . "\n\n";
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
        $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject($subject);
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
        $mail->setText($message);

        if ($data['email']) {
            $mail->setTo($data['email']);
            $mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }
        }

        // Send to main admin email if new account email is enabled
        if ($this->config->get('config_account_mail')) {

            $mail->setTo((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));

            $message  = $this->language->get('text_signup') . "\n\n";
            $message .= $this->language->get('text_website') . ' ' . $this->config->get('config_name') . "\n";
            if(isset($data['firstname']))
                $message .= $this->language->get('text_firstname') . ' ' . $data['firstname'] . "\n";
            if(isset($data['lastname']))
                $message .= $this->language->get('text_lastname') . ' ' . $data['lastname'] . "\n";
            if(isset($data['company']))
                if ($data['company']) {
                    $message .= $this->language->get('text_company') . ' '  . $data['company'] . "\n";
                }

            $message .= $this->language->get('text_email') . ' '  .  $data['email'] . "\n";
            if(isset($data['telephone']))
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

        $store_statistics = new StoreStatistics();
        $store_statistics->store_statistcs_push('customers', 'create', [
            'customer_id' => $customer_id,
            'email' => $data['email'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname']
        ]);
        $this->__dispatchCustomerAddedNotification($customer_id);


        return $customer_id;
    }

	public function getCountryIdByName($country){
        if (!$country || empty($country))
            return false;

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE LOWER(name) LIKE '" . $this->db->escape(utf8_strtolower($country)). "' OR iso_code_2 LIKE '" . $this->db->escape($country) . "' OR iso_code_3 LIKE '" . $this->db->escape($country) . "' LIMIT 1");
	
		if ($query->num_rows) {
            return $query->row['country_id'];
        } else {
            return false;
        }
	}

	public function getZoneIdByName($zone){
        if (!$zone || empty($zone))
            return false;

        $query = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . "zone WHERE LOWER(name) LIKE '" . $this->db->escape(utf8_strtolower($zone)). "' OR code LIKE '" . $this->db->escape($zone) . "' LIMIT 1");

        if ($query->num_rows) {
            return $query->row['zone_id'];
        } else {
            return false;
        }
    }

    private function __dispatchCustomerAddedNotification($customerId)
    {
        if (!$customerId)
            return false;
        $data['notification_module'] = "customers";
        $data['notification_module_code'] = "customers_registered";
        $data['notification_module_id'] = $customerId;
        $this->notifications->addAdminNotification($data);
    }

}
