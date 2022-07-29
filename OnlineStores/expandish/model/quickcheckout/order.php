<?php

class ModelQuickcheckoutOrder extends Model
{

    public function isPhoneNmberValidated($phone, $order_id = 0)
    {
        $sql = "SELECT phoneverified FROM `" . DB_PREFIX . "order` WHERE phoneverified=1 AND telephone = '" . $phone . "'";

        if ($order_id)
            $sql .= " AND order_id=" . $order_id;

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return '1';
        } else {
            return '0';
        }
    }

    public function validateSMSConfirmationCode($order_id, $userVerifCode)
    {
        $query = $this->db->query("SELECT phoneverified FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "' AND smsverifcode = '" . $userVerifCode . "'");

        if ($query->num_rows) {
            $phoneverified = $query->row['phoneverified'];
            if ($phoneverified == 0) {
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET
			smsveriftimestamp = NOW(),
		    phoneverified = 1
		    WHERE order_id='" . $order_id . "'");
            }
            return '1';
        } else {
            return '0';
        }
    }

    public function addOrder($data)
    {
        $digits = 5;
        $smsverifcode = rand(pow(10, $digits - 1), pow(10, $digits) - 1);

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
	    payment_country_id = '" . (int)$this->config->get('config_country_id') . "', 
	    payment_zone_id = '" . (int)$this->config->get('config_zone_id') . "', 
	    shipping_country_id = '" . (int)$this->config->get('config_country_id') . "', 
	    shipping_zone_id = '" . (int)$this->config->get('config_zone_id') . "', 
		affiliate_id = '" . (int)$data['affiliate_id'] . "', 
		payment_telephone = '" . $this->db->escape($data['payment_telephone']) . "', 
		commission = '" . (float)$data['commission'] . "', 
		language_id = '" . (int)$data['language_id'] . "', 
		currency_id = '" . (int)$data['currency_id'] . "', 
		currency_code = '" . $this->db->escape($data['currency_code']) . "', 
		currency_value = '" . (float)$data['currency_value'] . "', 
		ip = '" . $this->db->escape($data['ip']) . "', 
		forwarded_ip = '" . $this->db->escape($data['forwarded_ip']) . "', 
		user_agent = '" . $this->db->escape($data['user_agent']) . "', 
		accept_language = '" . $this->db->escape($data['accept_language']) . "', 
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

    public function addOrder151($data)
    {
        $digits = 5;
        $smsverifcode = rand(pow(10, $digits - 1), pow(10, $digits) - 1);

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
		affiliate_id = '" . (int)$data['affiliate_id'] . "', 
		commission = '" . (float)$data['commission'] . "', 
		language_id = '" . (int)$data['language_id'] . "', 
		currency_id = '" . (int)$data['currency_id'] . "', 
		currency_code = '" . $this->db->escape($data['currency_code']) . "', 
		currency_value = '" . (float)$data['currency_value'] . "', 
		ip = '" . $this->db->escape($data['ip']) . "', 
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


    public function updateOrder($order_id, $data)
    {
        $sms_order_id = $this->config->get('smshare_config_sms_confirm_per_order') ? $order_id : 0;
        $isPhoneVerified = $this->isPhoneNmberValidated($this->db->escape($data['telephone']), $sms_order_id);
		//if verified at sms check if verified at whats 'if not it will send whats message  
		//whatsapp
		//1 mean verified but may be the option of confirm per order is enabled at whatsApp 
		if (\Extension::isInstalled('whatsapp')) {
			if($isPhoneVerified=='1'){
				$isPhoneVerified = $this->config->get('whatsapp_config_WhatsApp_confirm_per_order') && $this->config->get('whatsapp_config_WhatsApp_confirm') ? 0: '1';
			}
		}
		
		//1 mean verified but may be the option of confirm per order is enabled at whatsApp 
		if (\Extension::isInstalled('whatsapp_cloud')) {
			if($isPhoneVerified=='1'){
				$confirm_per_order = ! (int)$this->config->get('whatsapp_cloud_config_customer_phone_confirmation_one_only');
				$isPhoneVerified = $confirm_per_order && $this->config->get('whatsapp_cloud_config_notify_customer_phone_confirm') ? 0: '1';
			}
		}
		
        $this->load->model('localisation/country');
        $payment_country = $this->model_localisation_country->getCountry($data['payment_country_id']);
        $shipping_country = $this->model_localisation_country->getCountry($data['shipping_country_id']);
        $payment_country = $payment_country['name'];
        $shipping_country = $shipping_country['name'];

        $this->load->model('localisation/zone');
        $payment_zone = $this->model_localisation_zone->getZone($data['payment_zone_id']);
        $shipping_zone = $this->model_localisation_zone->getZone($data['shipping_zone_id']);
        $payment_zone = $payment_zone['name'];
        $shipping_zone = $shipping_zone['name'];

        $this->load->model('localisation/area');
        $payment_area = $this->model_localisation_area->getArea((int)$data['payment_area_id']);
        $shipping_area = $this->model_localisation_area->getArea((int)$data['shipping_area_id']);
        $payment_area = $payment_area['name'];
        $shipping_area = $shipping_area['name'];

        $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();

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
			payment_telephone = '" . $this->db->escape($data['payment_telephone']) . "', 
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
			payment_country = '" . $this->db->escape($payment_country) . "', 
			payment_country_id = '" . (int)$data['payment_country_id'] . "', 
			payment_zone = '" . $this->db->escape($payment_zone) . "', 
			payment_zone_id = '" . (int)$data['payment_zone_id'] . "', 
			payment_area = '" . $this->db->escape($payment_area) . "', 
			payment_area_id = '" . (int)$data['payment_area_id'] . "', 
			payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', 
			payment_address_location = '" . $this->db->escape(json_encode($data['payment_address_location'])) . "',
			shipping_address_location = '" . $this->db->escape(json_encode($data['shipping_address_location'])) . "',
			payment_method = '" . $this->db->escape(strip_tags($data['payment_method'])) . "', 
			payment_code = '" . $this->db->escape($data['payment_code']) . "', 
			shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', 
			shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', 
			shipping_company = '" . $this->db->escape($data['shipping_company']) . "', 
			shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', 
			shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', 
			shipping_city = '" . $this->db->escape($data['shipping_city']) . "', 
			shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', 
			shipping_country = '" . $this->db->escape($shipping_country) . "', 
			shipping_country_id = '" . (int)$data['shipping_country_id'] . "', 
			shipping_zone = '" . $this->db->escape($shipping_zone) . "', 
			shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', 
			shipping_area = '" . $this->db->escape($shipping_area) . "', 
			shipping_area_id = '" . (int)$data['shipping_area_id'] . "', 
			shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', 
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
			forwarded_ip = '" . $this->db->escape($data['forwarded_ip']) . "', 
			user_agent = '" . $this->db->escape($data['user_agent']) . "', 
			accept_language = '" . $this->db->escape($data['accept_language']) . "',
			phoneverified = '" . $isPhoneVerified . "',
			gift_product = '" . $data['gift_product'] . "',
            " . (isset($data['psid']) ? "psid = " . $data['psid'] . "," : '') . "
			date_added = '" . $current_date_time . "', 
			date_modified = '" . $current_date_time . "',
			address_id = " . (int)$data['address_id'] . ",
            order_attributes = '" . json_encode($data['order_attributes']) . "'
            " . (
                    ($this->identity->isStoreOnWhiteList() && isset($data['address_id']) && !empty($data['address_id'])) 
                        ? (",address_id = '" .  (int)$data['address_id'] . "'") 
                        : ''
                ) . "
			WHERE order_id = '" . (int)$order_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");
       if(\Extension::isInstalled('rental_products')){
            $this->db->query("DELETE FROM " . DB_PREFIX . "order_product_rental WHERE order_id = '" . (int)$order_id . "'");
       } 
        
        if (\Extension::isInstalled('product_bundles')) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "order_product_bundle WHERE order_id = '" . (int)$order_id . "'");
        }

        foreach ($data['products'] as $product) {
            //echo "<pre>";
            //print_r($product);
            //echo "</pre>";
            $productOrderQuery = '';

            $productOrderQuery .= "INSERT INTO " . DB_PREFIX . "order_product SET ";
            $productOrderQuery .= "order_id = '" . (int)$order_id . "', ";
            $productOrderQuery .= "product_id = '" . (int)$product['product_id'] . "', ";
            $productOrderQuery .= "name = '" . $this->db->escape($product['name']) . "', ";
            $productOrderQuery .= "model = '" . $this->db->escape($product['model']) . "', ";
            $productOrderQuery .= "quantity = '" . (int)$product['quantity'] . "', ";
            $productOrderQuery .= "price = '" . (float)$product['price'] . "', ";
            $productOrderQuery .= "total = '" . (float)$product['total'] . "', ";
            $productOrderQuery .= "tax = '" . (float)$product['tax'] . "', ";
            $productOrderQuery .= "reward = '" . (int)$product['reward'] . "'";

            $this->load->model('module/minimum_deposit/settings');
            if ($this->model_module_minimum_deposit_settings->isActive()) {
                if (isset($product['main_price'])) {
                    $productOrderQuery .= ', main_price="' . $product['main_price'] . '"';
                }

                if (isset($product['remaining_amount'])) {
                    $productOrderQuery .= ', remaining_amount="' . $product['remaining_amount'] . '"';
                }
            }
            if (is_array($product['pd_application'])) {
                $productOrderQuery .= ',pd_tshirt_id="' . $product['pd_application']['tshirtId'] . '"';
            }


            if (isset($product['pricePerMeterData'])) {
                $productOrderQuery .= ",price_meter_data = '" . $product['pricePerMeterData'] . "'";
            }


            if (isset($product['fifaCardsData'])) {
                $productOrderQuery .= ",fifa_cards = '" . $product['fifaCardsData'] . "'";
            }


            if (isset($product['curtain_seller'])) {
                $productOrderQuery .= ",extra_details='" . json_encode(["curtain_seller" => $product['curtain_seller']]) . "'";
            }

            if (isset($product['printingDocument'])) {
                $productOrderQuery .= ",printing_document = '" . $product['printingDocument'] . "'";
            }
            // check if product has codes
            if (isset($product['codeGeneratorData'][$product['product_id']])) {
                $productOrderQuery .= ",code_generator = '" . $product['codeGeneratorData'][$product['product_id']] . "'";
            }

            if (isset($product['delivey_date']) ){
                $productOrderQuery .= ",product_delivery_date = '" . $product['delivey_date'] . "'";                
            }

            $this->db->query($productOrderQuery);

            $order_product_id = $this->db->getLastId();

            if (isset($product['rentData'])) {
                $rent_data = json_decode($product['rentData'],true);
                $to = date('Y-m-d H:i:s', $rent_data['range']['to']);
                $from = date('Y-m-d H:i:s', $rent_data['range']['from']);
                //$productOrderQuery .= ",rent_data = '" . $product['rentData'] . "'";
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_product_rental SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', diff = '" . (int)$rent_data['diff'] . "', from_date = '" . $this->db->escape($from) . "', to_date = '" . $this->db->escape($to) . "'");
            }
            if (isset($product['bundlesData'])) {
                $product['bundlesData'] = json_decode($product['bundlesData'],true);
                foreach ($product['bundlesData'] as $bundle) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_product_bundle SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', bundle_product_id = '" . (int)$bundle['product_id'] . "', quantity = '" . (int)$bundle['quantity']. "', price = '" . $bundle['price'] . "', discount = '" . $bundle['bundle_discount']. "'");
                }
            }

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'pd_application')
                    $sql = "INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', 
                        name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "',
                        `type` = '" . $this->db->escape($option['type']) . "'";
                    if($option['quantity']){
                        $sql .= " , `quantity` = '". $this->db->escape($option['quantity']) . "'";
                    }    
                    $this->db->query($sql);
            }

            foreach ($product['download'] as $download) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($download['name']) . "', filename = '" . $this->db->escape($download['filename']) . "', mask = '" . $this->db->escape($download['mask']) . "', remaining = '" . (int)($download['remaining'] * $product['quantity']) . "'");
            }
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

        foreach ($data['vouchers'] as $voucher) {
            $v_quantity = $voucher['quantity'] ? $voucher['quantity'] : 1;

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "', quantity = '" . (float)$v_quantity . "'");
        }
        $total = 0;
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
        foreach ($data['totals'] as $total) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', text = '" . $this->db->escape($total['text']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
        }

        // add data to delivery slot app
        if (isset($data['slot']) && is_array($data['slot']) && !empty($data['slot']['id_slot'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "ds_delivery_slot_order WHERE order_id = '" . (int)$order_id . "'");
            // get slot data
            $query_slot_data = $this->db->query("SELECT  * FROM " . DB_PREFIX . "ds_delivery_slot WHERE ds_delivery_slot_id = '" . (int)$data['slot']['id_slot'] . "' ");
            $slotData = $query_slot_data->row;


            $querySlot = "INSERT INTO " . DB_PREFIX . "ds_delivery_slot_order SET ";
            $querySlot .= " order_id =" . $order_id;
            $querySlot .= " , ds_delivery_slot_id = " . (int)$data['slot']['id_slot'];
            $querySlot .= " , delivery_date = '" . $data['slot']['slot_date'] . "'";
            $querySlot .= " , ds_day_id = " . (int)$slotData['ds_day_id'];
            $querySlot .= " , slot_description = '" . $slotData['delivery_slot'] . "'";
            $querySlot .= " , day_name = '" . $data['slot']['dayes'][date("w", strtotime($data['slot']['slot_date_dmy_format']))] . "'";

            $querySlotExcute = $this->db->query($querySlot);

        }


        //save order warehouse products if exists
        if ($data['warehouse_products']) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "order_to_warehouse WHERE order_id = '" . (int)$order_id . "'");

            //Save warehouses ids as a json array to use it in admin orders advanced filter by warehouse | new changes v.21.05.2020
            $warehouse_ids = '';
            if (count($data['warehouse_ids']))
                $warehouse_ids = implode(',', $data['warehouse_ids']);

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_to_warehouse SET 
        	                                                                    order_id = '" . (int)$order_id . "', 
        	                                                                    `data` = '" . $this->db->escape($data['warehouse_products']) . "', 
        	                                                                    init_data = '" . $this->db->escape($data['warehouse_products']) . "',
        	                                                                    warehouses_list = '[" . $warehouse_ids . "]'");
        }
        ///////////////////////////////////////////

        return $order_id;
    }

    public function updateOrder152($order_id, $data)
    {

        $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
        $sms_order_id = $this->config->get('smshare_config_sms_confirm_per_order') ? $order_id : 0;
        $isPhoneVerified = $this->isPhoneNmberValidated($this->db->escape($data['telephone']), $sms_order_id);
		//if verified at sms check if verified at whats 'if not it will send whats message  
		//whatsapp
		//1 mean verified but may be the option of confirm per order is enabled at whatsApp 
		if (\Extension::isInstalled('whatsapp')) {
			if($isPhoneVerified=='1'){
				$isPhoneVerified = $this->config->get('whatsapp_config_WhatsApp_confirm_per_order') && $this->config->get('whatsapp_config_WhatsApp_confirm') ? 0: '1';
			}
		}
		
		//1 mean verified but may be the option of confirm per order is enabled at whatsApp 
		if (\Extension::isInstalled('whatsapp_cloud')) {
			if($isPhoneVerified=='1'){
				$confirm_per_order = ! (int)$this->config->get('whatsapp_cloud_config_customer_phone_confirmation_one_only');
				$isPhoneVerified = $confirm_per_order && $this->config->get('whatsapp_cloud_config_notify_customer_phone_confirm') ? 0: '1';
			}
		}
		
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
			payment_address_location = '" . $this->db->escape(json_encode($data['payment_address_location'])) . "', 
			shipping_address_location = '" . $this->db->escape(json_encode($data['shipping_address_location'])) . "', 
			payment_method = '" . $this->db->escape(strip_tags($data['payment_method'])) . "', 
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
			forwarded_ip = '" . $this->db->escape($data['forwarded_ip']) . "', 
			user_agent = '" . $this->db->escape($data['user_agent']) . "', 
			accept_language = '" . $this->db->escape($data['accept_language']) . "',
			phoneverified = '" . $isPhoneVerified . "',
			gift_product = '" . $data['gift_product'] . "',
            " . (isset($data['psid']) ? "psid = " . $data['psid'] . "," : '') . "
			date_added = '" . $current_date_time . "', 
			date_modified = '" . $current_date_time . "' ,
			address_id = " . (int)$data['address_id'] . ",
			WHERE order_id = '" . (int)$order_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");


        foreach ($data['products'] as $product) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "', reward = '" . (int)$product['reward'] . "'");
            $this->load->model('module/minimum_deposit/settings');
            if ($this->model_module_minimum_deposit_settings->isActive()) {
                if (isset($product['main_price'])) {
                    $this->db->query("UPDATE  " . DB_PREFIX . "order_product SET main_price = '" . (float)$product['main_price'] . "' WHERE product_id = '" . (int)$product['product_id'] . "' AND  order_id = '" . (int)$order_id . "'");
                }
                if (isset($product['remaining_amount'])) {
                    $this->db->query("UPDATE  " . DB_PREFIX . "order_product SET remaining_amount = '" . (float)$product['remaining_amount'] . "' WHERE product_id = '" . (int)$product['product_id'] . "' AND  order_id = '" . (int)$order_id . "'");
                }
            }
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
            $v_quantity = $voucher['quantity'] ? $voucher['quantity'] : 1;

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "', quantity = '" . (float)$v_quantity . "'");
        }

        $total = 0;
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");

        foreach ($data['totals'] as $total) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', text = '" . $this->db->escape($total['text']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
        }
        return $order_id;
    }

    public function updateOrder151($order_id, $data)
    {
        $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
        $sms_order_id = $this->config->get('smshare_config_sms_confirm_per_order') ? $order_id : 0;
        $isPhoneVerified = $this->isPhoneNmberValidated($this->db->escape($data['telephone']), $sms_order_id);
				//if verified at sms check if verified at whats 'if not it will send whats message  
		//whatsapp
		//1 mean verified but may be the option of confirm per order is enabled at whatsApp 
		if (\Extension::isInstalled('whatsapp')) {
			if($isPhoneVerified=='1'){
				$isPhoneVerified = $this->config->get('whatsapp_config_WhatsApp_confirm_per_order') && $this->config->get('whatsapp_config_WhatsApp_confirm') ? 0: '1';
			}
		}
		
		//1 mean verified but may be the option of confirm per order is enabled at whatsApp 
		if (\Extension::isInstalled('whatsapp_cloud')) {
			if($isPhoneVerified=='1'){
				$confirm_per_order = ! (int)$this->config->get('whatsapp_cloud_config_customer_phone_confirmation_one_only');
				$isPhoneVerified = $confirm_per_order && $this->config->get('whatsapp_cloud_config_notify_customer_phone_confirm') ? 0: '1';
			}
		}
		
        $this->load->model('model/sale/order');
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
			payment_address_location = '" . $this->db->escape(json_encode($data['payment_address_location'])) . "', 
			shipping_address_location = '" . $this->db->escape(json_encode($data['shipping_address_location'])) . "', 
			payment_method = '" . strip_tags($this->db->escape($data['payment_method'])) . "', 
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
			gift_product = '" . $data['gift_product'] . "',
            " . (isset($data['psid']) ? "psid = " . $data['psid'] . "," : '') . "
			date_added = '" . $current_date_time . "', 
			date_modified = '" . $current_date_time . "' ,
			address_id = " . (int)$data['address_id'] . ",
			WHERE order_id = '" . (int)$order_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");

        foreach ($data['products'] as $product) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "'");

            $this->load->model('module/minimum_deposit/settings');
            if ($this->model_module_minimum_deposit_settings->isActive()) {
                if (isset($product['main_price'])) {
                    $this->db->query("UPDATE  " . DB_PREFIX . "order_product SET main_price = '" . (float)$product['main_price'] . "' WHERE product_id = '" . (int)$product['product_id'] . "' AND  order_id = '" . (int)$order_id . "'");
                }
                if (isset($product['remaining_amount'])) {
                    $this->db->query("UPDATE  " . DB_PREFIX . "order_product SET remaining_amount = '" . (float)$product['remaining_amount'] . "' WHERE product_id = '" . (int)$product['product_id'] . "' AND  order_id = '" . (int)$order_id . "'");
                }
            }
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

    public function updateOrderStatus($order_id, $order_status_id)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

    }

    public function updateProductCodes($product)
    {
        $productCodes = json_decode($product['code_generator'], true);

        foreach ($productCodes as $productCode) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_code_generator SET is_used = 1 WHERE product_code_generator_id = '" . (int)$productCode['product_code_generator_id'] . "'");
        }
    }

    public function addSubscriptionPlanPaymentData($order_info,$subscription_data,$updateCustomerSubscription = FALSE){
        $query_buyer_subscription = "INSERT INTO `" . DB_PREFIX . "buyer_subscription_payments_log` SET ";
        $query_buyer_subscription .= " order_id =" . $order_info['order_id'];
        $query_buyer_subscription .= " , buyer_id = " . (int)$order_info['customer_id'];
        $query_buyer_subscription .= " , amount = '" . $this->db->escape($subscription_data['amount'])."'";
        $query_buyer_subscription .= " , subscription_id = " . (int)$subscription_data['id'];
        $query_buyer_subscription .= " , payment_data = '" . $this->db->escape($order_info['payment_method'])."'";
        $query_buyer_subscription .= " , currency_id = " . (int)$order_info['currency_id'];
        $query_buyer_subscription .= " , currency_code = '" . $this->db->escape($order_info['currency_code'])."'";

        $queryBuyerSubscriptionExcute = $this->db->query($query_buyer_subscription);
        if($updateCustomerSubscription){
            $this->db->query("UPDATE `". DB_PREFIX . "customer` SET buyer_subscription_id = " .(int)$subscription_data['id'] . " WHERE customer_id = " . (int)$order_info['customer_id']);
        }

    }

}

?>
