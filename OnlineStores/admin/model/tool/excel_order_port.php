<?php
class ModelToolExcelOrderPort extends Model {
	
	public function addorder($data){
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET order_id = '".(int)$data['order_id']."', invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', store_id = '" . (int)$data['store_id'] . "', store_name = '" . $this->db->escape($data['store_name']) . "', store_url = '" . $this->db->escape($data['store_url']) . "', customer_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $this->db->escape($data['payment_company']) . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($data['payment_country']) . "', payment_country_id = '" . (int)$data['payment_country_id'] . "', payment_zone = '" . $this->db->escape($data['payment_zone']) . "', payment_zone_id = '" . (int)$data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "',  payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '" . $this->db->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($data['shipping_country']) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', total = '" . (float)$data['total'] . "', affiliate_id = '" . (int)$data['affiliate_id'] . "', commission = '" . (float)$data['commission'] . "', language_id = '" . (int)$data['language_id'] . "', currency_id = '" . (int)$data['currency_id'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', currency_value = '" . (float)$data['currency_value'] . "', ip = '" . $this->db->escape($data['ip']) . "', forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', accept_language = '" . $this->db->escape($data['accept_language']) . "', date_added = '".$this->db->escape($data['date_added'])."', date_modified = '".$this->db->escape($data['date_modified'])."',order_status_id = '" . (int)$data['order_status_id'] . "'");
		
		$order_id = $data['order_id'];

        // check delivery slot app
        $this->load->model('module/delivery_slot/settings');
        $delivery_slot_status = $this->model_module_delivery_slot_settings->isActive();
        if($delivery_slot_status == true ){
            foreach ($data['orderproduct'] as $product) {
                foreach ($product['delivery_solt'] as $delivery_solt_details ){
                    $this->db->query("INSERT INTO " . DB_PREFIX . "ds_delivery_slot_order SET order_id = '" . (int)$order_id . "', day_name = '" . $this->db->escape($delivery_solt_details['day_name']) . "', delivery_date = '" . $this->db->escape($delivery_solt_details['delivery_date']) . "', slot_description = '" .  $this->db->escape($delivery_solt_details['slot_description']) .  "'");
                }
            }
        }
		
		// Products
		if (isset($data['orderproduct'])){
			foreach ($data['orderproduct'] as $product) {
				  $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "', reward = '" . (int)$product['reward'] . "'"); 

				  $order_product_id = $this->db->getLastId();

				foreach($product['productoptions'] as $option){
					$product_option_value_id = 0;
					$product_option_id = 0;
					$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = '".$this->db->escape(utf8_strtolower($option['type']))."' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option['name']))."' LIMIT 0,1");
					if($oquery->row){
						$ovquery = $this->db->query("SELECT ov.option_value_id FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '".(int)$oquery->row['option_id']."' AND LCASE(ovd.name) = '".$this->db->escape(utf8_strtolower($option['value']))."' LIMIT 0,1");
						
						if(!empty($ovquery->row['option_value_id'])){
						   $query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$product['product_id'] . "' AND option_id = '" . (int)$oquery->row['option_id'] . "'");
						   if(!empty($query->row['product_option_id'])){
							  $product_option_id = $query->row['product_option_id'];
							   $ifoptionvalue = $this->db->query("SELECT product_option_value_id FROM ".DB_PREFIX."product_option_value WHERE  product_option_id = '" . (int)$product_option_id . "' AND option_value_id = '" . (int)$ovquery->row['option_value_id'] ."'");
							  if(!empty($ifoptionvalue->row['product_option_value_id'])){
								  $product_option_value_id = $ifoptionvalue->row['product_option_value_id'];
							  }
						   }
						}
					}
					
					
					 $this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$product_option_id . "', product_option_value_id = '" . (int)$product_option_value_id . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'"); 
				}
			}
		}
		
		
		// Vouchers
		if (isset($data['ordervouchers'])){
			foreach($data['ordervouchers'] as $voucher){
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "'");

				$order_voucher_id = $this->db->getLastId();
				
				$this->db->query("INSERT INTO " . DB_PREFIX . "voucher SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "', status = '1', date_added = NOW()");

				$voucher_id = $this->db->getLastId();

				$this->db->query("UPDATE " . DB_PREFIX . "order_voucher SET voucher_id = '" . (int)$voucher_id . "' WHERE order_voucher_id = '" . (int)$order_voucher_id . "'");
			}
		}
		
		$currency_info = $this->db->query("SELECT * FROM ".DB_PREFIX."currency WHERE code = '".$this->db->escape($data['currency_code'])."'")->row;
		if(!empty($currency_info['symbol_left'])){
			$currencycode = $currency_info['symbol_left'];
		}elseif(!empty($currency_info['symbol_right'])){
			$currencycode = $currency_info['symbol_right'];
		}else{
			$currencycode = '';
		}
		
		// Totals
		if(isset($data['order_total'])){
			foreach($data['order_total'] as $total){
				$textvalue = number_format($total['value'],2);
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', text = '" . $this->db->escape($currencycode.$textvalue) . "', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}
		
		//Order History
		if(isset($data['orderhistorys'])){
			foreach($data['orderhistorys'] as $history){
				$user_id = $history['user_id'] ?? $this->session->data['user_id'];
				$this->db->query("INSERT INTO " . DB_PREFIX .
				"order_history SET order_id = '" . (int)$order_id .
				"', order_status_id = '" . (int)$history['order_status_id'] .
				"', notify = '" . (int)$history['notify'] .
				"', comment = '" . $this->db->escape($history['comment']) .
				"', user_id = '" . $this->db->escape($user_id ) .
				"', date_added = '".$this->db->escape($history['date_added'])."'");
			}
		}
	}
	
	
	public function editOrder($data,$order_id){
		//dd("edit");
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', store_id = '" . (int)$data['store_id'] . "', store_name = '" . $this->db->escape($data['store_name']) . "', store_url = '" . $this->db->escape($data['store_url']) . "', customer_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $this->db->escape($data['payment_company']) . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($data['payment_country']) . "', payment_country_id = '" . (int)$data['payment_country_id'] . "', payment_zone = '" . $this->db->escape($data['payment_zone']) . "', payment_zone_id = '" . (int)$data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '" . $this->db->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($data['shipping_country']) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', total = '" . (float)$data['total'] . "', affiliate_id = '" . (int)$data['affiliate_id'] . "', commission = '" . (float)$data['commission'] . "', language_id = '" . (int)$data['language_id'] . "', currency_id = '" . (int)$data['currency_id'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', currency_value = '" . (float)$data['currency_value'] . "', ip = '" . $this->db->escape($data['ip']) . "', forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', accept_language = '" . $this->db->escape($data['accept_language']) . "', date_added = '".$this->db->escape($data['date_added'])."', date_modified = '".$this->db->escape($data['date_modified'])."',order_status_id = '" . (int)$data['order_status_id'] . "' WHERE order_id = '" . (int)$order_id . "'");
		
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");

        // check delivery slot app
        $this->load->model('module/delivery_slot/settings');
        $delivery_slot_status = $this->model_module_delivery_slot_settings->isActive();
        if($delivery_slot_status == true ){
            foreach ($data['orderproduct'] as $product) {
                foreach ($product['delivery_solt'] as $delivery_solt_details ){
                    $this->db->query("UPDATE " . DB_PREFIX . "ds_delivery_slot_order SET  day_name = '" . $this->db->escape($delivery_solt_details['day_name']) . "', delivery_date = '" . $this->db->escape($delivery_solt_details['delivery_date']) . "', slot_description = '" . $this->db->escape($delivery_solt_details['slot_description']) .  "' WHERE order_id = '" . (int)$order_id . "'");
                }
            }
        }
		
		// Products
		if (isset($data['orderproduct'])){
			foreach ($data['orderproduct'] as $product) {
				  $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "', reward = '" . (int)$product['reward'] . "'"); 

				  $order_product_id = $this->db->getLastId();

				foreach($product['productoptions'] as $option){
					$product_option_value_id = 0;
					$product_option_id = 0;
					$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = '".$this->db->escape(utf8_strtolower($option['type']))."' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option['name']))."' LIMIT 0,1");
					if($oquery->row){
						$ovquery = $this->db->query("SELECT ov.option_value_id FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '".(int)$oquery->row['option_id']."' AND LCASE(ovd.name) = '".$this->db->escape(utf8_strtolower($option['value']))."' LIMIT 0,1");
						
						if(!empty($ovquery->row['option_value_id'])){
						   $query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$product['product_id'] . "' AND option_id = '" . (int)$oquery->row['option_id'] . "'");
						   if(!empty($query->row['product_option_id'])){
							  $product_option_id = $query->row['product_option_id'];
							   $ifoptionvalue = $this->db->query("SELECT product_option_value_id FROM ".DB_PREFIX."product_option_value WHERE  product_option_id = '" . (int)$product_option_id . "' AND option_value_id = '" . (int)$ovquery->row['option_value_id'] ."'");
							  if(!empty($ifoptionvalue->row['product_option_value_id'])){
								  $product_option_value_id = $ifoptionvalue->row['product_option_value_id'];
							  }
						   }
						}
					}
					
					
					 $this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$product_option_id . "', product_option_value_id = '" . (int)$product_option_value_id . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'"); 
				}
			}
		}
		
		
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");
		
		// Vouchers
		if (isset($data['ordervouchers'])){
			foreach($data['ordervouchers'] as $voucher){
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "'");

				$order_voucher_id = $this->db->getLastId();
				
				$this->db->query("INSERT INTO " . DB_PREFIX . "voucher SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "', status = '1', date_added = NOW()");

				$voucher_id = $this->db->getLastId();

				$this->db->query("UPDATE " . DB_PREFIX . "order_voucher SET voucher_id = '" . (int)$voucher_id . "' WHERE order_voucher_id = '" . (int)$order_voucher_id . "'");
			}
		}
		
		//Order History
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");

		if(isset($data['orderhistorys'])){
			foreach($data['orderhistorys'] as $history){
				$user_id = $history['user_id'] ?? $this->session->data['user_id'];
				$this->db->query("INSERT INTO " . DB_PREFIX .
				"order_history SET order_id = '" . (int)$order_id .
				"', order_status_id = '" . (int)$history['order_status_id'] .
				"', notify = '" . (int)$history['notify'] .
			 	"', comment = '" . $this->db->escape($history['comment']) .
				"', user_id = '" . $this->db->escape($user_id ) .
				"', date_added = '".$this->db->escape($history['date_added'])."'");
			}
		}
		
		
		$currency_info = $this->db->query("SELECT * FROM ".DB_PREFIX."currency WHERE code = '".$this->db->escape($data['currency_code'])."'")->row;
		if(!empty($currency_info['symbol_left'])){
			$currencycode = $currency_info['symbol_left'];
		}elseif(!empty($currency_info['symbol_right'])){
			$currencycode = $currency_info['symbol_right'];
		}else{
			$currencycode = '';
		}
		
		
		// Totals
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		if(isset($data['order_total'])){
			foreach($data['order_total'] as $total){
				$textvalue = number_format($total['value'],2);
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', text = '" . $this->db->escape($currencycode.$textvalue) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}
	}
	
	public function addoldcustomer($data,$password_format,$customer_id){
		$this->db->query("INSERT INTO " .DB_PREFIX. "customer SET customer_id = '".(int)$customer_id."', customer_group_id = '" . (int)$data['customer_group_id']. "', firstname = '".$this->db->escape($data['firstname'])."', lastname = '". $this->db->escape($data['lastname']) ."', email = '".$this->db->escape($data['email'])."',telephone = '".$this->db->escape($data['telephone'])."', fax = '" . $this->db->escape($data['fax']) . "',status = '" . (int)$data['status'] . "', approved = '".(int)$data['approved']."', newsletter = '".(int)$data['newsletter']."', date_added = NOW()");
	
		if($data['password']){
			if($password_format=='P'){
				$this->db->query("UPDATE ".DB_PREFIX."customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE customer_id = '".(int)$customer_id."'");
			}
		
			if($password_format=='E'){
				$this->db->query("UPDATE ".DB_PREFIX."customer SET salt = '" . $this->db->escape($data['salt']) . "', password = '" . $this->db->escape($data['password']) . "' WHERE customer_id = '".(int)$customer_id."'");
			}
		}
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address1']) . "', address_2 = '" . $this->db->escape($data['address2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country'] . "', zone_id = '" . (int)$data['state'] . "'");
		
		$address_id = $this->db->getLastId();
		
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}
	
	public function addcustomer($data,$password_format){
		$this->db->query("INSERT INTO " .DB_PREFIX. "customer SET customer_group_id = '" . (int)$data['customer_group_id']. "', firstname = '".$this->db->escape($data['firstname'])."', lastname = '". $this->db->escape($data['lastname']) ."', email = '".$this->db->escape($data['email'])."',telephone = '".$this->db->escape($data['telephone'])."', fax = '" . $this->db->escape($data['fax']) . "',status = '" . (int)$data['status'] . "', approved = '".(int)$data['approved']."', newsletter = '".(int)$data['newsletter']."', date_added = NOW()");
	
		$customer_id = $this->db->getLastId();
		
		if($data['password']){
			if($password_format=='P'){
				$this->db->query("UPDATE ".DB_PREFIX."customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE customer_id = '".(int)$customer_id."'");
			}
		
			if($password_format=='E'){
				$this->db->query("UPDATE ".DB_PREFIX."customer SET salt = '" . $this->db->escape($data['salt']) . "', password = '" . $this->db->escape($data['password']) . "' WHERE customer_id = '".(int)$customer_id."'");
			}
		}
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address1']) . "', address_2 = '" . $this->db->escape($data['address2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country'] . "', zone_id = '" . (int)$data['state'] . "'");
		
		$address_id = $this->db->getLastId();
		
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}
	
	public function EditCustomer($data,$password_format,$customer_id){
		$this->db->query("UPDATE " .DB_PREFIX. "customer SET customer_group_id = '" . (int)$data['customer_group_id']. "', firstname = '".$this->db->escape($data['firstname'])."', lastname = '". $this->db->escape($data['lastname']) ."', email = '".$this->db->escape($data['email'])."', telephone = '".$this->db->escape($data['telephone'])."', fax = '" . $this->db->escape($data['fax']) . "',status = '" . (int)$data['status'] . "', approved = '".(int)$data['approved']."', newsletter = '".(int)$data['newsletter']."' WHERE customer_id =  '".(int)$customer_id."'");
	
		if($data['password']){
			if($password_format=='P'){
				$this->db->query("UPDATE ".DB_PREFIX."customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE customer_id =  '".(int)$customer_id."'");
			}
			
			if($password_format=='E'){
				$this->db->query("UPDATE ".DB_PREFIX."customer SET salt = '" . $this->db->escape($data['salt']) . "', password = '" . $this->db->escape($data['password']) . "' WHERE customer_id =  '".(int)$customer_id."'");
			}
		}
		
		$this->db->query("DELETE FROM ".DB_PREFIX."address WHERE customer_id = '" . (int)$customer_id . "'");
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address1']) . "', address_2 = '" . $this->db->escape($data['address2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country'] . "', zone_id = '" . (int)$data['state'] . "'");
		
		$address_id = $this->db->getLastId();
		
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}
	
	public function getcustomer($customer_id){
		return $this->db->query("SELECT * FROM ".DB_PREFIX."customer WHERE customer_id = '".(int)$customer_id."'")->row;
	}
	
	public function getCustomers($data = array()){
		$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name,  (select name from customer_group_description where customer_group_id=c.customer_group_id and language_id = '" .(int)$this->config->get('config_language_id') . "') AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group cgd ON (c.customer_group_id = cgd.customer_group_id) ";

		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (!empty($data['filter_ip'])) {
            $implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c.approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
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
			'c.approved',
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
	
	public function getAddress($address_id) {
		$address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "'");

		if ($address_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			return array(
				'address_id'     => $address_query->row['address_id'],
				'customer_id'    => $address_query->row['customer_id'],
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
			);
		}
	}
	
	public function getAddresses($customer_id){
		$address_data = array();

		$query = $this->db->query("SELECT address_id FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");

		foreach ($query->rows as $result) {
			$address_info = $this->getAddress($result['address_id']);

			if ($address_info) {
				$address_data[$result['address_id']] = $address_info;
			}
		}

		return $address_data;
	}
	
	public function addCustomerGroup($data){
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_group SET approval = '" . (int)$data['approval'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$customer_group_id = $this->db->getLastId();

		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_group_description SET customer_group_id = '" . (int)$customer_group_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "'");
		}
	}
	
	public function addoldCustomerGroup($data,$customer_group_id){
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_group SET customer_group_id = '".(int)$customer_group_id."', approval = '" . (int)$data['approval'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_group_description SET customer_group_id = '" . (int)$customer_group_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "'");
		}
	}
	
	public function editCustomerGroup($data,$customer_group_id){
		$this->db->query("UPDATE " . DB_PREFIX . "customer_group SET approval = '" . (int)$data['approval'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE customer_group_id = '" . (int)$customer_group_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = '" . (int)$customer_group_id . "'");

		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_group_description SET customer_group_id = '" . (int)$customer_group_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "'");
		}
	}
	
	public function getCustomerGroup($customer_group_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cg.customer_group_id = '" . (int)$customer_group_id . "' AND cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getCustomerGroups($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = array(
			'cgd.name',
			'cg.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY cgd.name";
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
	
	public function getCustomerGroupsdata($data){
		$sql = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$data['language_id'] . "'";

		$sql .= " ORDER BY cgd.name ASC";

		if (isset($data['start']) || isset($data['limit'])){
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1){
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
	/*Customer END**/
	
	public function getOrders($data = array()){
		
		$sql = "SELECT o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status, o.shipping_code, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified FROM `" . DB_PREFIX . "order` o";

		if (isset($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			} else {

			}
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_to_order_id']) && empty($data['filter_from_order_id'])){
			$sql .= " AND o.order_id = '" . (int)$data['filter_to_order_id'] . "'";
		}
		
		if (empty($data['filter_to_order_id']) && !empty($data['filter_from_order_id'])){
			$sql .= " AND o.order_id = '" . (int)$data['filter_from_order_id'] . "'";
		}
		
		if (!empty($data['filter_to_order_id']) && !empty($data['filter_from_order_id'])){
			$sql .= " AND o.order_id BETWEEN '" . (int)$data['filter_to_order_id'] . "' AND '" . (int)$data['filter_from_order_id'] . "'";
		}


		if (!empty($data['filter_to_date_added']) && empty($data['filter_from_date_added'])){
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_to_date_added']) . "')";
		}
		
		if (empty($data['filter_to_date_added']) && !empty($data['filter_from_date_added'])){
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_from_date_added']) . "')";
		}

		if (!empty($data['filter_to_date_added']) && !empty($data['filter_from_date_added'])){
			$sql .= " AND DATE(o.date_added) BETWEEN DATE('" . $this->db->escape($data['filter_to_date_added']) . "') AND DATE('" . $this->db->escape($data['filter_from_date_added']) . "')";
		}
		
		if (!empty($data['filter_to_date_modified']) && empty($data['filter_form_date_modified'])){
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_to_date_modified']) . "')";
		}
		
		if (empty($data['filter_to_date_modified']) && !empty($data['filter_form_date_modified'])){
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_form_date_modified']) . "')";
		}
		
		if (!empty($data['filter_to_date_modified']) && !empty($data['filter_form_date_modified'])){
			$sql .= " AND DATE(o.date_modified) BETWEEN DATE('" . $this->db->escape($data['filter_to_date_modified']) . "') AND DATE('" . $this->db->escape($data['filter_form_date_modified']) . "')";
		}

        if ($data['filter_total_from'] ==  $data['filter_total_to'] && !empty($data['filter_total_from'])) {
            $sql .= " AND o.total = '" . (float)$data['filter_total_from'] . "'";
        }elseif (!empty($data['filter_total_from'])) {
            $sql .= " AND o.total >= '" . (float)$data['filter_total_from'] . "'";
        }elseif (!empty($data['filter_total_to'])) {
            $sql .= " AND o.total <= '" . (float)$data['filter_total_to'] . "'";
        }


		
		$sort_data = array(
			'o.order_id',
			'customer',
			'status',
			'o.date_added',
			'o.date_modified',
			'o.total'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
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
	
	public function getOrder($order_id){
		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$reward = 0;

			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows){
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			if ($order_query->row['affiliate_id']) {
				$affiliate_id = $order_query->row['affiliate_id'];
			} else {
				$affiliate_id = 0;
			}

			$this->load->model('sale/affiliate');

			$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);

			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
				$language_directory = $language_info['directory'];
			} else {
				$language_code = '';
				$language_directory = '';
			}

            if (\Extension::isInstalled('manual_shipping')) {
                if($order_query->row['manual_shipping_gateway'] == 1){
                    $this->load->model('module/manual_shipping/gateways');
                    $manual_shipping_gateway = $this->model_module_manual_shipping_gateways->getCompactShippingGatewayById($order_query->row['shipping_gateway_id'],["language_id"=>$this->config->get('config_language_id')]);
                    if(is_array($manual_shipping_gateway)){
                        $manual_shipping_gateway_title = $manual_shipping_gateway[0]['title'];
                    }
                }
            }

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'customer'                => $order_query->row['customer'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'email'                   => $order_query->row['email'],
				'telephone'               => $order_query->row['telephone'],
				'fax'                     => $order_query->row['fax'],
				'custom_field'            => '',
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_custom_field'    => '',
				'payment_method'          => $order_query->row['payment_method'],
				'payment_code'            => $order_query->row['payment_code'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => isset($manual_shipping_gateway_title) ? $manual_shipping_gateway_title :$order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_custom_field'   => '',
				'shipping_method'         => $order_query->row['shipping_method'],
				'shipping_code'           => $order_query->row['shipping_code'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'reward'                  => $reward,
				'order_status_id'         => $order_query->row['order_status_id'],
				'affiliate_id'            => $order_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'language_directory'      => $language_directory,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified']
			);
		} else {
			return;
		}
	}
	
	public function getOrderexportHistories($order_id){
		$query = $this->db->query("SELECT *,os.name AS status FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC ");

		return $query->rows;
	}
	/**ORDER EXPORT**/
	
	public function getLanguages($data = array()){
		$language_data = $this->cache->get('language');
		if(!$language_data){
			$language_data = array();

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language ORDER BY sort_order, name");

			foreach($query->rows as $result){
				$language_data[$result['code']] = array(
					'language_id' => $result['language_id'],
					'name'        => $result['name'],
					'code'        => $result['code'],
					'locale'      => $result['locale'],
					'image'       => $result['image'],
					'directory'   => $result['directory'],
					'sort_order'  => $result['sort_order'],
					'status'      => $result['status']
				);
			}

			$this->cache->set('language', $language_data);
		}
		
		return $language_data;
	}
}