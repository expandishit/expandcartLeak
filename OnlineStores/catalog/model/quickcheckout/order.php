<?php
class ModelQuickcheckoutOrder extends Model {

public function isPhoneNmberValidated($phone) {
    $query = $this->db->query("SELECT phoneverified FROM `" . DB_PREFIX . "order` WHERE phoneverified=1 AND telephone = '" . $phone . "'");

    if ($query->num_rows) {
        return '1';
    } else {
        return  '0';
    }
}

public function validateSMSConfirmationCode($order_id,$userVerifCode) {
	$query = $this->db->query("SELECT phoneverified FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "' AND smsverifcode = '" . $userVerifCode . "'");

	if ($query->num_rows) {
		$phoneverified = $query->row['phoneverified'];
		if($phoneverified == 0) {
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET
			smsveriftimestamp = NOW(),
		    phoneverified = 1
		    WHERE order_id='" . $order_id . "'");
		}
		return '1';
	} else {
		return  '0';
	}
}

public function addOrder($data) {
	$digits = 5;
	$smsverifcode = rand(pow(10, $digits-1), pow(10, $digits)-1);
    $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();

	$whatsveriftrials =(int)$this->config->get('whatsapp_config_WhatsApp_trials');
		
	if (\Extension::isInstalled('whatsapp_cloud')) {		
		$whatsveriftrials = (int)$this->config->get('whatsapp_cloud_config_confirmation_trials');
	}
		
	$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET
		store_id = '" . (int)$data['store_id'] . "', 
		store_name = '" . $this->db->escape($data['store_name']) . "', 
		store_url = '" . $this->db->escape($data['store_url']) . "', 
		total = '" . (float)$data['total'] . "', 
                payment_country_id = '" . (int)$this->config->get('config_country_id'). "', 
                payment_zone_id = '" . (int)$this->config->get('config_zone_id') . "', 
                shipping_country_id = '" . (int)$this->config->get('config_country_id'). "', 
                shipping_zone_id = '" . (int)$this->config->get('config_zone_id') . "', 
		affiliate_id = '" . (int)$data['affiliate_id'] . "', 
		commission = '" . (float)$data['commission'] . "', 
		language_id = '" . (int)$data['language_id'] . "', 
		currency_id = '" . (int)$data['currency_id'] . "', 
		currency_code = '" . $this->db->escape($data['currency_code']) . "', 
		currency_value = '" . (float)$data['currency_value'] . "', 
		ip = '" . $this->db->escape($data['ip']) . "', 
		forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "', 
		user_agent = '" . $this->db->escape($data['user_agent']) . "', 
		accept_language = '" . $this->db->escape($data['accept_language']) . "', 
		payment_address_location = '" . $this->db->escape($data['payment_address_location']) . "',
		shipping_address_location = '" . $this->db->escape($data['shipping_address_location']) . "',
		date_added = '" . $current_date_time . "', 
		date_modified = '" . $current_date_time . "',
		smsverifcode = '" . $smsverifcode . "',
		smsveriftimestamp = NOW(),
		smsveriftrials = IFNULL(" . (int)$this->config->get('smshare_config_sms_trials') . ", 999)+1,
		whatsveriftrials = IFNULL(" . $whatsveriftrials . ", 999)+1,
		smsexpirationdatetime = NOW() + INTERVAL 1 DAY");
		$order_id = $this->db->getLastId();

		$store_statistics = new StoreStatistics();
        $store_statistics->store_statistcs_push('orders', 'create', [
        	'order_id' => $order_id,
        	'total' => (float)$data['total'],
        	'currency_code' => $data['currency_code'],
        	'currency_value' => (float)$data['currency_value']
        ]);

		return $order_id;
}
	
public function addOrder151($data) {
	$digits = 5;
	$smsverifcode = rand(pow(10, $digits-1), pow(10, $digits)-1);
    $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
	$whatsveriftrials  = (int)$this->config->get('whatsapp_config_WhatsApp_trials');
		
	if (\Extension::isInstalled('whatsapp_cloud')) {		
		$whatsveriftrials = (int)$this->config->get('whatsapp_cloud_config_confirmation_trials');
	}
	
	$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET 
		store_id = '" . (int)$data['store_id'] . "', 
		store_name = '" . $this->db->escape($data['store_name']) . "', 
		store_url = '" . $this->db->escape($data['store_url']) . "', 
		total = '" . (float)$data['total'] . "', 
		affiliate_id = '" . (int)$data['affiliate_id'] . "', 
		commission = '" . (float)$data['commission'] . "', 
		language_id = '" . (int)$data['language_id'] . "', 
		currency_id = '" . (int)$data['currency_id'] . "', 
		currency_code = '" . $this->db->escape($data['currency_code']) . "', 
		currency_value = '" . (float)$data['currency_value'] . "', 
		ip = '" . $this->db->escape($data['ip']) . "', 
		payment_address_location = '" . $this->db->escape($data['payment_address_location']) . "',
		shipping_address_location = '" . $this->db->escape($data['shipping_address_location']) . "',
		date_added = '" . $current_date_time . "', 
		date_modified = '" . $current_date_time . "',
		smsverifcode = '" . $smsverifcode . "',
		smsveriftimestamp = NOW(),
		smsveriftrials = IFNULL(" . (int)$this->config->get('smshare_config_sms_trials') . ", 999)+1,
		whatsveriftrials = IFNULL(" .$whatsveriftrials . ", 999)+1,
		smsexpirationdatetime = NOW() + INTERVAL 1 DAY");
		$order_id = $this->db->getLastId();

		$store_statistics = new StoreStatistics();
        $store_statistics->store_statistcs_push('orders', 'create', [
        	'order_id' => $order_id,
        	'total' => (float)$data['total'],
        	'currency_code' => $data['currency_code'],
        	'currency_value' => (float)$data['currency_value']
        ]);

		return $order_id;
}


public function updateOrder($order_id,$data) {
    $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
		$isPhoneVerified = $this->isPhoneNmberValidated($this->db->escape($data['telephone']));
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET 
			invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', 
			store_id = '" . (int)$data['store_id'] . "', 
			store_name = '" . $this->db->escape($data['store_name']) . "', 
			store_url = '" . $this->db->escape($data['store_url']) . "', 
			customer_id = '" . (int)$data['customer_id'] . "', 
			customer_group_id = '" . (int)$data['customer_group_id'] . "', 
			firstname = '" . $this->db->escape($data['firstname']) . "', 
			lastname = '" . $this->db->escape($data['lastname']) . "', 
			email = '" . $this->db->escape($data['email']) . "', 
			telephone = '" . $this->db->escape($data['telephone']) . "', 
			fax = '" . $this->db->escape($data['fax']) . "', 
			payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', 
			payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', 
			payment_company = '" . $this->db->escape($data['payment_company']) . "', 
			payment_company_id = '" . $this->db->escape($data['payment_company_id']) . "', 
			payment_tax_id = '" . $this->db->escape($data['payment_tax_id']) . "', 
			payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', 
			payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', 
			payment_city = '" . $this->db->escape($data['payment_city']) . "', 
			payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', 
			payment_country = '" . $this->db->escape($data['payment_country']) . "', 
			payment_country_id = '" . (int)$data['payment_country_id'] . "', 
			payment_zone = '" . $this->db->escape($data['payment_zone']) . "', 
			payment_zone_id = '" . (int)$data['payment_zone_id'] . "', 
			payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', 
			payment_address_location = '" . $this->db->escape($data['payment_address_location']) . "', 
			payment_method = '" . $this->db->escape($data['payment_method']) . "', 
			payment_code = '" . $this->db->escape($data['payment_code']) . "', 
			shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', 
			shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', 
			shipping_company = '" . $this->db->escape($data['shipping_company']) . "', 
			shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', 
			shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', 
			shipping_city = '" . $this->db->escape($data['shipping_city']) . "', 
			shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', 
			shipping_country = '" . $this->db->escape($data['shipping_country']) . "', 
			shipping_country_id = '" . (int)$data['shipping_country_id'] . "', 
			shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', 
			shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', 
			shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "',
			shipping_address_location = '" . $this->db->escape($data['shipping_address_location']) . "', 
			shipping_method = '" . $this->db->escape($data['shipping_method']) . "', 
			shipping_code = '" . $this->db->escape($data['shipping_code']) . "', 
			comment = '" . $this->db->escape($data['comment']) . "', 
			total = '" . (float)$data['total'] . "', 
			affiliate_id = '" . (int)$data['affiliate_id'] . "', 
			commission = '" . (float)$data['commission'] . "', 
			language_id = '" . (int)$data['language_id'] . "', 
			currency_id = '" . (int)$data['currency_id'] . "', 
			currency_code = '" . $this->db->escape($data['currency_code']) . "', 
			currency_value = '" . (float)$data['currency_value'] . "', 
			ip = '" . $this->db->escape($data['ip']) . "', 
			forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "', 
			user_agent = '" . $this->db->escape($data['user_agent']) . "', 
			accept_language = '" . $this->db->escape($data['accept_language']) . "',
			phoneverified = '" . $isPhoneVerified . "',
			date_added = '" . $current_date_time . "', 
			date_modified = '" . $current_date_time . "'
			WHERE order_id = '" . (int)$order_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'"); 
       	$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");

		foreach ($data['products'] as $product) { 
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "', reward = '" . (int)$product['reward'] . "'");
 
			$order_product_id = $this->db->getLastId();

			foreach ($product['option'] as $option) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
			}
				
			foreach ($product['download'] as $download) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($download['name']) . "', filename = '" . $this->db->escape($download['filename']) . "', mask = '" . $this->db->escape($download['mask']) . "', remaining = '" . (int)($download['remaining'] * $product['quantity']) . "'");
			}	
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'"); 
		
		foreach ($data['vouchers'] as $voucher) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "'");
		}
		$total = 0;	
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		foreach ($data['totals'] as $total) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', text = '" . $this->db->escape($total['text']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
        }	

		return $order_id;
	}
	public function updateOrder152($order_id, $data) {
        $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
		$isPhoneVerified = $this->isPhoneNmberValidated($this->db->escape($data['telephone']));
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET 
			invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', 
			store_id = '" . (int)$data['store_id'] . "', 
			store_name = '" . $this->db->escape($data['store_name']) . "', 
			store_url = '" . $this->db->escape($data['store_url']) . "', 
			customer_id = '" . (int)$data['customer_id'] . "', 
			customer_group_id = '" . (int)$data['customer_group_id'] . "', 
			firstname = '" . $this->db->escape($data['firstname']) . "', 
			lastname = '" . $this->db->escape($data['lastname']) . "', 
			email = '" . $this->db->escape($data['email']) . "', 
			telephone = '" . $this->db->escape($data['telephone']) . "', 
			fax = '" . $this->db->escape($data['fax']) . "', 
			shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', 
			shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', 
			shipping_company = '" . $this->db->escape($data['shipping_company']) . "', 
			shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', 
			shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', 
			shipping_city = '" . $this->db->escape($data['shipping_city']) . "', 
			shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', 
			shipping_country = '" . $this->db->escape($data['shipping_country']) . "', 
			shipping_country_id = '" . (int)$data['shipping_country_id'] . "', 
			shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', 
			shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', 
			shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', 
			shipping_address_location = '" . $this->db->escape($data['shipping_address_location']) . "', 
			shipping_method = '" . $this->db->escape($data['shipping_method']) . "', 
			shipping_code = '" . $this->db->escape($data['shipping_code']) . "', 
			payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', 
			payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', 
			payment_company = '" . $this->db->escape($data['payment_company']) . "', 
			payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', 
			payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', 
			payment_city = '" . $this->db->escape($data['payment_city']) . "', 
			payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', 
			payment_country = '" . $this->db->escape($data['payment_country']) . "', 
			payment_country_id = '" . (int)$data['payment_country_id'] . "', 
			payment_zone = '" . $this->db->escape($data['payment_zone']) . "', 
			payment_zone_id = '" . (int)$data['payment_zone_id'] . "', 
			payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', 
			payment_address_location = '" . $this->db->escape($data['payment_address_location']) . "', 
			payment_method = '" . $this->db->escape($data['payment_method']) . "', 
			payment_code = '" . $this->db->escape($data['payment_code']) . "', 
			comment = '" . $this->db->escape($data['comment']) . "', 
			total = '" . (float)$data['total'] . "', 
			affiliate_id = '" . (int)$data['affiliate_id'] . "', 
			commission = '" . (float)$data['commission'] . "', 
			language_id = '" . (int)$data['language_id'] . "', 
			currency_id = '" . (int)$data['currency_id'] . "', 
			currency_code = '" . $this->db->escape($data['currency_code']) . "', 
			currency_value = '" . (float)$data['currency_value'] . "', 
			ip = '" . $this->db->escape($data['ip']) . "', 
			forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "', 
			user_agent = '" . $this->db->escape($data['user_agent']) . "', 
			accept_language = '" . $this->db->escape($data['accept_language']) . "',
			phoneverified = '" . $isPhoneVerified . "',
			date_added = '" . $current_date_time . "', 
			date_modified = '" . $current_date_time . "' 
			WHERE order_id = '" . (int)$order_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'"); 
       	$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");

		foreach ($data['products'] as $product) { 
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "', reward = '" . (int)$product['reward'] . "'");
 
			$order_product_id = $this->db->getLastId();

			foreach ($product['option'] as $option) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
			}
				
			foreach ($product['download'] as $download) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($download['name']) . "', filename = '" . $this->db->escape($download['filename']) . "', mask = '" . $this->db->escape($download['mask']) . "', remaining = '" . (int)($download['remaining'] * $product['quantity']) . "'");
			}	
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'"); 
		
		foreach ($data['vouchers'] as $voucher) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "'");
		}
		
		$total = 0;	
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		
        foreach ($data['totals'] as $total) {
            // if the total is reward:
            // make the text value of it equals to:
            // the string version of the points value + the custom reward points name if exists OR 'point(s)' as a fallback word.
            if ($total['code'] == 'reward') {
                $reward_points_text = $this->config->get('text_points_'.$this->config->get('config_language')) ? $this->config->get('text_points_'.$this->config->get('config_language')) : 'Point(s)';
                $total_text = (string) $total['value'] . ' ' . $reward_points_text;
            } else {
                $total_text = $total['text'];
            }

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', text = '" . $this->db->escape($total_text) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
        }

        return $order_id;
    }
	public function updateOrder151($order_id, $data) {

		$isPhoneVerified = $this->isPhoneNmberValidated($this->db->escape($data['telephone']));
        $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
		// compatibility - removed: , reward = '" . (float)$data['reward'] . "',
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET 
			invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', 
			store_id = '" . (int)$data['store_id'] . "', 
			store_name = '" . $this->db->escape($data['store_name']) . "', 
			store_url = '" . $this->db->escape($data['store_url']) . "', 
			customer_id = '" . (int)$data['customer_id'] . "', 
			customer_group_id = '" . (int)$data['customer_group_id'] . "', 
			firstname = '" . $this->db->escape($data['firstname']) . "', 
			lastname = '" . $this->db->escape($data['lastname']) . "', 
			email = '" . $this->db->escape($data['email']) . "', 
			telephone = '" . $this->db->escape($data['telephone']) . "', 
			fax = '" . $this->db->escape($data['fax']) . "', 
			shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', 
			shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', 
			shipping_company = '" . $this->db->escape($data['shipping_company']) . "', 
			shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', 
			shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', 
			shipping_city = '" . $this->db->escape($data['shipping_city']) . "', 
			shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', 
			shipping_country = '" . $this->db->escape($data['shipping_country']) . "', 
			shipping_country_id = '" . (int)$data['shipping_country_id'] . "', 
			shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', 
			shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', 
			shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', 
			shipping_address_location = '" . $this->db->escape($data['shipping_address_location']) . "', 
			shipping_method = '" . $this->db->escape($data['shipping_method']) . "', 
			payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', 
			payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', 
			payment_company = '" . $this->db->escape($data['payment_company']) . "', 
			payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', 
			payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', 
			payment_city = '" . $this->db->escape($data['payment_city']) . "', 
			payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', 
			payment_country = '" . $this->db->escape($data['payment_country']) . "', 
			payment_country_id = '" . (int)$data['payment_country_id'] . "', 
			payment_zone = '" . $this->db->escape($data['payment_zone']) . "', 
			payment_zone_id = '" . (int)$data['payment_zone_id'] . "', 
			payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', 
			payment_address_location = '" . $this->db->escape($data['payment_address_location']) . "', 
			payment_method = '" . $this->db->escape($data['payment_method']) . "', 
			comment = '" . $this->db->escape($data['comment']) . "', 
			total = '" . (float)$data['total'] . "', 
			affiliate_id = '" . (int)$data['affiliate_id'] . "', 
			commission = '" . (float)$data['commission'] . "', 
			language_id = '" . (int)$data['language_id'] . "', 
			currency_id = '" . (int)$data['currency_id'] . "', 
			currency_code = '" . $this->db->escape($data['currency_code']) . "', 
			currency_value = '" . (float)$data['currency_value'] . "', 
			ip = '" . $this->db->escape($data['ip']) . "',
			phoneverified = '" . $isPhoneVerified . "',
			date_added = '" . $current_date_time . "', 
			date_modified = '" . $current_date_time . "'
			WHERE order_id = '" . (int)$order_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'"); 
       	$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");

		foreach ($data['products'] as $product) { 
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "'");
 
			$order_product_id = $this->db->getLastId();

			foreach ($product['option'] as $option) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
			}
				
			foreach ($product['download'] as $download) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($download['name']) . "', filename = '" . $this->db->escape($download['filename']) . "', mask = '" . $this->db->escape($download['mask']) . "', remaining = '" . (int)($download['remaining'] * $product['quantity']) . "'");
			}	
		}
		$total = 0;	
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		
		foreach ($data['totals'] as $total) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', text = '" . $this->db->escape($total['text']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
		}	

		return $order_id;
	}
}
?>