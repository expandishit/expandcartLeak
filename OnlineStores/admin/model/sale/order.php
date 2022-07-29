<?php
class ModelSaleOrder extends Model {

    //$zonePaymentMethodsArray : this variable using to check if we are using create order from dashboard or not
    //in dashbord case we load payments base on zones    
    public function getPaymentMethods($zonePaymentMethodsArray = null)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'payment'");
        $data = array();


        foreach ($query->rows as $index => $value)
        {

            $settings = $this->config->get($value['code']);

            if ( $settings && is_array($settings) == true )
            {
                $status = $settings['status'];
            }
            else
            {
                $status = $this->config->get($value['code'] . '_status');
            }

            if ( !$status )
            {
                continue;
            }
            
            if (!empty($zonePaymentMethodsArray)) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get($value['code'] . '_geo_zone_id') . "' AND country_id = '" . (int) $zonePaymentMethodsArray['country_id'] . "' AND (zone_id = '" . (int) $zonePaymentMethodsArray['zone_id'] . "' OR zone_id = '0')");
                if (empty($query->row)) {
                    continue;
                }
            }
            
            $data[$index]['code'] = $value['code'];

            $this->load->language('payment/'.$value['code']);

            if ( in_array($value['code'], array('payfort_fort_qpay', 'payfort_fort_sadad') ) )
            {
                $name = $this->language->get('payment_heading_title');
            }
            else
            {
                $name = $this->language->get('heading_title');
            }
            $data[$index]['name'] = $name;
        }

        return $data;
    }

	public function addOrder($data) {
		$this->load->model('setting/store');

		$store_info = $this->model_setting_store->getStore($data['store_id']);

		if ($store_info) {
			$store_name = $store_info['name'];
			$store_url = $store_info['url'];
		} else {
			$store_name = $this->config->get('config_name');
			$store_url = HTTP_CATALOG;
		}

		$this->load->model('setting/setting');

		$setting_info = $this->model_setting_setting->getSetting('setting', $data['store_id']);

		if (isset($setting_info['invoice_prefix'])) {
			$invoice_prefix = $setting_info['invoice_prefix'];
		} else {
			$invoice_prefix = $this->config->get('config_invoice_prefix');
		}

		$this->load->model('localisation/country');

		$this->load->model('localisation/zone');

		$this->load->model('localisation/area');

                $country_info = $this->model_localisation_country->getCountry($data['shipping_country_id']);

		if ($country_info) {
			$shipping_country = $country_info['name'];
			$shipping_address_format = $country_info['address_format'];
		} else {
			$shipping_country = '';
			$shipping_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZoneInLanguage($data['shipping_zone_id']);

		if ($zone_info) {
			$shipping_zone = $zone_info['name'];
		} else {
			$shipping_zone = '';
		}

		$country_info = $this->model_localisation_country->getCountry($data['payment_country_id']);

		if ($country_info) {
			$payment_country = $country_info['name'];
			$payment_address_format = $country_info['address_format'];
		} else {
			$payment_country = '';
			$payment_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZoneInLanguage($data['payment_zone_id']);

		if ($zone_info) {
			$payment_zone = $zone_info['name'];
		} else {
			$payment_zone = '';
		}

                $area_info = $this->model_localisation_area->getAreaInLanguage($data['shipping_area_id']);

                if($area_info){
                    $shipping_area = $area_info['name'];
                }else{
                    $shipping_area = '';
                }

		$this->load->model('localisation/currency');

		$currency_info = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));

		if ($currency_info) {
			$currency_id = $currency_info['currency_id'];
			$currency_code = $currency_info['code'];
			$currency_value = $currency_info['value'];
		} else {
			$currency_id = 0;
			$currency_code = $this->config->get('config_currency');
			$currency_value = 1.00000;
		}

      	$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET
            invoice_prefix = '" . $this->db->escape($invoice_prefix) . "',
            gift_product = '" . $data['gift_product'] . "',
            store_id = '" . (int)$data['store_id'] . "',
            store_name = '" . $this->db->escape($store_name) . "',
            store_url = '" . $this->db->escape($store_url) . "',
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
            payment_country = '" . $this->db->escape($payment_country) . "',
            payment_country_id = '" . (int)$data['payment_country_id'] . "',
            payment_zone = '" . $this->db->escape($payment_zone) . "',
            payment_zone_id = '" . (int)$data['payment_zone_id'] . "',
            payment_area = '" . $this->db->escape($shipping_area) . "',
            payment_area_id = '" . (int)$data['shipping_area_id'] . "',
            payment_address_format = '" . $this->db->escape($payment_address_format) . "',
            payment_method = '" . $this->db->escape($data['payment_method']) . "',
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
            shipping_address_format = '" . $this->db->escape($shipping_address_format) . "',
            shipping_method = '" . $this->db->escape($data['shipping_method']) . "',
            shipping_code = '" . $this->db->escape($data['shipping_code']) . "',
            comment = '" . $this->db->escape($data['comment']) . "',
            order_status_id = '" . (int)$data['order_status_id'] . "',
            affiliate_id  = '" . (int)$data['affiliate_id'] . "',
            language_id = '" . (int)$this->config->get('config_language_id') . "',
            currency_id = '" . (int)$currency_id . "',
            currency_code = '" . $this->db->escape($currency_code) . "',
            currency_value = '" . (float)$currency_value . "',
            payment_telephone = '" . $this->db->escape($data['payment_telephone']) . "',
            date_added = NOW(),
            date_modified = NOW(),
            delivery_info = '" . $this->db->escape($data['delivery_info']) . "' ");

      	$order_id = $this->db->getLastId();

      	if (isset($data['order_product'])) {
      		foreach ($data['order_product'] as $order_product) {

               // $product_total = (float) $order_product['total'];
				$product_total=0;

                if ( $product_total <= 0 )
                {
                    $product_total = ( (float) $order_product['quantity'] * ( (float) $order_product['tax'] + (float) $order_product['price'] ) ) - (float) $order_product['reward'];
                }

				$query = "INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$order_product['product_id'] . "', name = '" . $this->db->escape($order_product['name']) . "', model = '" . $this->db->escape($order_product['model']) . "', quantity = '" . (int)$order_product['quantity'] . "', price = '" . (float)$order_product['price'] . "', total = '" .$product_total. "', tax = '" . (float)$order_product['tax'] . "', reward = '" . (int)$order_product['reward'] . "'";
				
                if (\Extension::isInstalled("product_code_generator") && $this->config->get('product_code_generator')) {
					$codes = $this->db->query("SELECT `product_code_generator_id` , `code` FROM " . DB_PREFIX . "product_code_generator where product_id = ". (int)$order_product['product_id'] .";");
					if($codes->num_rows > 0){
						$query .= " , code_generator = '" . $this->db->escape(json_encode($codes->rows)) ."'";
					}
				}

				$this->db->query($query);

				$order_product_id = $this->db->getLastId();

				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

				if (isset($order_product['order_option'])) {
					foreach ($order_product['order_option'] as $order_option) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$order_option['product_option_id'] . "', product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "', name = '" . $this->db->escape($order_option['name']) . "', `value` = '" . $this->db->escape($order_option['value']) . "', `type` = '" . $this->db->escape($order_option['type']) . "'");

						$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
					}
				}

				if (isset($order_product['order_download'])) {
					foreach ($order_product['order_download'] as $order_download) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($order_download['name']) . "', filename = '" . $this->db->escape($order_download['filename']) . "', mask = '" . $this->db->escape($order_download['mask']) . "', remaining = '" . (int)$order_download['remaining'] . "'");
					}
				}
			}

            // Qoyod Invoice
            $this->qoyod_create_invoice(0, $order_id, $data['order_status_id']);
		}

		if (isset($data['order_voucher'])) {
			foreach ($data['order_voucher'] as $order_voucher) {
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', voucher_id = '" . (int)$order_voucher['voucher_id'] . "', description = '" . $this->db->escape($order_voucher['description']) . "', code = '" . $this->db->escape($order_voucher['code']) . "', from_name = '" . $this->db->escape($order_voucher['from_name']) . "', from_email = '" . $this->db->escape($order_voucher['from_email']) . "', to_name = '" . $this->db->escape($order_voucher['to_name']) . "', to_email = '" . $this->db->escape($order_voucher['to_email']) . "', voucher_theme_id = '" . (int)$order_voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($order_voucher['message']) . "', amount = '" . (float)$order_voucher['amount'] . "'");

      			$this->db->query("UPDATE " . DB_PREFIX . "voucher SET order_id = '" . (int)$order_id . "' WHERE voucher_id = '" . (int)$order_voucher['voucher_id'] . "'");
			}
		}

		// Get the total
		$total = 0;

		if (isset($data['order_total'])) {
			$this->db->query('DELETE FROM ' . DB_PREFIX . 'coupon_history WHERE order_id = '.(int)$order_id );
      		foreach ($data['order_total'] as $order_total) {
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($order_total['code']) . "', title = '" . $this->db->escape($order_total['title']) . "', text = '" . $this->db->escape($order_total['text']) . "', `value` = '" . (float)$order_total['value'] . "', sort_order = '" . (int)$order_total['sort_order'] . "'");
				/**
				 * Get the record of the total order amount
				 */
      			if( strcmp($order_total['code'],'total') == 0 ){
					$total = $order_total['value'];
				}
				if($order_total['code'] == 'coupon'){
					preg_match('#\((.*?)\)#', $order_total['title'], $match);
					$coupon_code = trim($match[1]);
					$coupon_id = $this->db->query('SELECT coupon_id FROM '. DB_PREFIX .'coupon WHERE code = "'.$coupon_code.'"')->row['coupon_id'];
					$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_history SET order_id = '" . (int)$order_id . "', coupon_id = '" . $coupon_id  . "', amount = '" . $order_total['value'] . "', customer_id = '".$this->db->escape($data['customer_id'])."', date_added = NOW() ");
				}
      		}
		}

		// Affiliate
		$affiliate_id = 0;
		$commission = 0;

		if (!empty($this->request->post['affiliate_id'])) {
			$this->load->model('sale/affiliate');

			$affiliate_info = $this->model_sale_affiliate->getAffiliate($this->request->post['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_id = $affiliate_info['affiliate_id'];
				$commission = ($total / 100) * $affiliate_info['commission'];
			}
		}

                if(!empty($data['orderPoints'])){
                    $this->load->model('sale/customer');                    
                    $this->model_sale_customer->addReward((int)$data['customer_id'],'',$data['orderPoints'],$order_id);
                }
                
		// Update order total
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '" . (float)$total . "', affiliate_id = '" . (int)$affiliate_id . "', commission = '" . (float)$commission . "' WHERE order_id = '" . (int)$order_id . "'");
        
        //fire new order trigger for zapier  if installed
        $this->load->model('module/zapier');
        if ($this->model_module_zapier->isInstalled()) {
            $order_info     = $this->getOrder($order_id);
            $this->model_module_zapier->newOrderTrigger($order_info);
        }

		/***************** Start ExpandCartTracking #347716  ****************/

		// send mixpanel receive order and update time of last order
		$this->load->model('setting/mixpanel');
		$this->model_setting_mixpanel->updateUser(['$last order' => date("Y-m-d H:i:s")]);
		$this->model_setting_mixpanel->trackEvent('Received Order',['Order ID'=>$order_id]);

		// send amplitude receive order and update time of last order
		$this->load->model('setting/amplitude');
		$this->model_setting_amplitude->updateUser(['last order' => date("Y-m-d H:i:s")]);
		$this->model_setting_amplitude->trackEvent('Received Order',['Order ID'=>$order_id]);

		/***************** End ExpandCartTracking #347716  ****************/
		
		// add order to knawat if it's products belong to it
		$this->load->model('module/knawat');
		if ($this->model_module_knawat->isInstalled()) {
		    if ($this->model_module_knawat->checkOrderStatusForInsertToKnawat($order_id) && $this->model_module_knawat->checkIfKnawatOrder($order_id))
				$this->model_module_knawat->insertOrderIntoKnawat($order_id);
		}

        $store_statistics = new StoreStatistics($this->user);
        $store_statistics->store_statistcs_push('orders', 'create', [
            'order_id' => $order_id,
            'customer_id' => (int)$data['customer_id'],
            'total' => (float)$total,
            'currency_code' => $currency_code,
            'currency_value' => (float)$currency_value
        ]);

		return $order_id;
	}

	public function mergeOrders($order_ids = array()) {
        $verify_sql = "SELECT COUNT(DISTINCT `" . DB_PREFIX . "order`.`email`) AS customer_count FROM `" . DB_PREFIX . "order`" .
                      " WHERE `" . DB_PREFIX . "order`.`order_id` IN (";

        foreach($order_ids as $order_id) {
            if(end($order_ids) !== $order_id){
                $verify_sql .= $order_id . ",";
            } else {
                $verify_sql .= $order_id;
            }
        }

        $verify_sql .= ")";

        $verify_query = $this->db->query($verify_sql);

        if ($verify_query->row['customer_count'] > 1) {
            return 'DIFF_CUST';
        }

        $base_order_sql = "SELECT `" . DB_PREFIX . "order`.`order_id` FROM `" . DB_PREFIX . "order`" .
                            " WHERE `" . DB_PREFIX . "order`.`order_id` IN (";

        foreach($order_ids as $order_id) {
            if(end($order_ids) !== $order_id){
                $base_order_sql .= $order_id . ",";
            } else {
                $base_order_sql .= $order_id;
            }
        }

        $base_order_sql .= ")" .
                            " ORDER BY `" . DB_PREFIX . "order`.`date_added` DESC" .
                            " LIMIT 1";

        $base_order_query = $this->db->query($base_order_sql);

        $base_order_id = $base_order_query->row['order_id'];

        foreach($order_ids as $order_id) {
            if ($order_id != $base_order_id) {
                $this->db->query("UPDATE " . DB_PREFIX . "order_product SET order_id = '" . (int)$base_order_id . "' WHERE order_id = '" . (int)$order_id . "'");
                $this->db->query("UPDATE " . DB_PREFIX . "order_option SET order_id = '" . (int)$base_order_id . "' WHERE order_id = '" . (int)$order_id . "'");
                $this->db->query("UPDATE " . DB_PREFIX . "order_download SET order_id = '" . (int)$base_order_id . "' WHERE order_id = '" . (int)$order_id . "'");
                $this->db->query("UPDATE " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$base_order_id . "' WHERE order_id = '" . (int)$order_id . "'");

                try {
                    $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
                    if ($queryMultiseller->num_rows) {
                        $this->db->query("UPDATE " . DB_PREFIX . "ms_order_comment SET order_id = '" . (int)$base_order_id . "' WHERE order_id = '" . (int)$order_id . "'");
                        $this->db->query("UPDATE " . DB_PREFIX . "ms_order_product_data SET order_id = '" . (int)$base_order_id . "' WHERE order_id = '" . (int)$order_id . "'");
                    }
                } catch (Exception $e) {

                }

                $this->db->query("UPDATE " . DB_PREFIX . "customer_transaction SET order_id = '" . (int)$base_order_id . "' WHERE order_id = '" . (int)$order_id . "'");

                $this->deleteOrder($order_id);
            }
        }

        return $base_order_id;
    }

	public function quickedit($order_info,$data){

		// get default currency
		$currency = $this->config->get('config_currency');
		$order_sub_total = 0;

		// Restock products before subtracting the stock later on
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND order_id = '" . (int)$this->db->escape($order_info['order_id']) . "'");

		if ($order_query->num_rows) {
            $product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$this->db->escape($order_info['order_id']) . "'");

			foreach($product_query->rows as $product) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$this->db->escape($product['quantity']) . ") WHERE product_id = '" . (int)$this->db->escape($product['product_id']) . "' AND subtract = '1'");

				$option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$this->db->escape($order_info['order_id']) . "' AND order_product_id = '" . (int)$this->db->escape($product['order_product_id']) . "'");

				foreach ($option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$this->db->escape($product['quantity']) . ") WHERE product_option_value_id = '" . (int)$this->db->escape($option['product_option_value_id']) . "' AND subtract = '1'");
				}
			}
        }

		$sub_total_from_inputs = 0;
		$old_products_tax=0;

		$this->tax->setPaymentAddress($data['payment_country_id'], $data['payment_zone_id'], $area_id= 0);
//		$product_orders = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id =  '" . (int)$this->db->escape($order_info['order_id']). "'");
//		foreach ($product_orders->rows as $product_order){
//			$product_item = $this->db->query("SELECT price,tax_class_id FROM `" . DB_PREFIX . "product` WHERE product_id =  '" . (int)$this->db->escape($product_order['product_id']) . "'  limit 1");
//			$old_tax  =   $this->tax->getTax($product_item->row['price']*$product_order['quantity'] , $product_item->row['tax_class_id']);
//			$old_products_tax += $old_tax;
//		}

		$this->db->query("START TRANSACTION;");
	//	$productsTax = $order_info['tax_value'] - $old_products_tax ;
		$productsTax = 0 ;
		if (isset($data['order_product'])) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_info['order_id'] . "'");

			foreach ($data['order_product'] as $order_product) {
				$product_item = $this->db->query("SELECT tax_class_id FROM `" . DB_PREFIX . "product` WHERE product_id =  '" . (int)$this->db->escape($order_product['product_id']) . "'  limit 1");
				$price = $order_product['price'];
				$priceConverted = $this->currency->convert($price,$order_info['currency_code'], $currency );

				$totalProductValue = ( (int) $order_product['quantity']   * ( (float) $priceConverted ) ) ;
				$order_sub_total += $totalProductValue;
				$sub_total_from_inputs += $price;
				$productsTax += (float) $this->tax->getTax($order_product['price']*$order_product['quantity'],$product_item->row['tax_class_id']);

                $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET  order_id = '" . (int)$this->db->escape($order_info['order_id'])  . "', product_id = '" . (int)$this->db->escape($order_product['product_id']) . "', name = '" . $this->db->escape($order_product['name']) . "', model = '" . $this->db->escape($order_product['model']) . "', quantity = '" . (int)$this->db->escape($order_product['quantity']) . "', price = '" . (float)$this->db->escape($priceConverted)  . "', total = '" . $this->db->escape($totalProductValue) . "', tax = '" . (float)$this->db->escape($order_product['tax']) . "', reward = '" . (int)$this->db->escape($order_product['reward']) . "'");


                $order_product_id = $this->db->getLastId();

                //Insert product option
				foreach ($order_product['order_option'] as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$this->db->escape($order_product['quantity']) . ") WHERE product_option_value_id = '" . (int)$this->db->escape($option['product_option_value_id']) . "' AND subtract = '1'");

                    $this->db->query("UPDATE " . DB_PREFIX . "order_option SET order_product_id='".(int) $this->db->escape($order_product_id)."' WHERE order_id = '" . (int)$this->db->escape($order_info['order_id']) . "' AND product_option_value_id = '" . (int)$this->db->escape($option['product_option_value_id']) . "'");
                }


				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$this->db->escape($order_product['quantity']) . ") WHERE product_id = '" . (int)$this->db->escape($order_product['product_id']) . "' AND subtract = '1'");
			}
		}

		$this->db->query("COMMIT;");

		// update order sub_total
		$order_sub_total_text = $order_info['currency_code'].$order_sub_total;
		$order_items_value = $order_sub_total;
		$this->db->query("UPDATE " . DB_PREFIX . "order_total SET text = '".$this->db->escape($order_sub_total_text)."' , value ='".$this->db->escape($order_items_value)."' WHERE code = 'sub_total' AND order_id = '" . (int)$this->db->escape($order_info['order_id']) . "'");
		// update order tax
		$order_tax_text = $order_info['currency_code'].$productsTax;
		$this->db->query("UPDATE " . DB_PREFIX . "order_total SET text = '".$this->db->escape($order_tax_text)."' , value ='".$this->db->escape($productsTax)."' WHERE code = 'tax' AND order_id = '" . (int)$this->db->escape($order_info['order_id']) . "'");

		$total_custom = 0;
		$total_custom_original = 0;

		$custom_title = isset($data['custom_title']) ? $data['custom_title'] : null;
		$custom_value = isset($data['custom_value']) ? $data['custom_value'] : null;

		// insert order total
		if(is_array($custom_title) && is_array($custom_value))
		{
			foreach ($custom_title as $key=>$item) {
				$order_items_value = round($this->currency->convert($custom_value[$key],$order_info['currency_code'], $currency ),2);
				$order_items_text = $order_info['currency_code'].$custom_value[$key];
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET `order_id` = ".(int)$this->db->escape($order_info['order_id'])." , `title` = '".$this->db->escape($item)."' , `text` = '".$this->db->escape($order_items_text)."' , `value` = '".$this->db->escape($order_items_value)."' , `code` = 'custom' , `sort_order` = 2 ");
				$total_custom += $order_items_value;
				$total_custom_original += $custom_value[$key];
			}
		}
		// get order totals without  total

		$orderCurrentTotals = $this->db->query("Select SUM(`value`) As value FROM " . DB_PREFIX . "order_total WHERE `order_id` = '".(int)$this->db->escape($order_info['order_id'])."' AND `code` NOT IN('total','earn_point', 'earn_reward') ");
		$order_total = $orderCurrentTotals->row['value'];
		$order_total_orginal = $this->currency->convert($order_total,$currency,$order_info['currency_code'] );
		$order_total_orginal_text = $order_info['currency_code'].$this->currency->format($order_total_orginal, $order_info['currency_code']);
		
		// Update order total on order total table
		$this->db->query("UPDATE " . DB_PREFIX . "order_total SET text = '".$this->db->escape($order_total_orginal_text)."' , value ='".$this->db->escape($order_total)."' WHERE code = 'total' AND order_id = '" . (int)$this->db->escape($order_info['order_id']) . "'");

		// Update order total on order table
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '".$this->db->escape($order_total)."'  WHERE `order_id` = '" . (int)$this->db->escape($order_info['order_id']) . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_modification SET order_id = '" . (int)$this->db->escape($order_info['order_id']) . "', status_id = 1, user_id = '".(int)$this->db->escape($this->session->data['user_id'])."' ");

	}

	public function editOrder($order_id, $data) {
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $this->load->model('promotions/reward_points_transactions');
            $this->model_promotions_reward_points_transactions->beforeUpdateOrder($order_id, $data);
        }

		$this->load->model('localisation/country');

		$this->load->model('localisation/zone');

		$country_info = $this->model_localisation_country->getCountry($data['shipping_country_id']);

		if ($country_info) {
			$shipping_country = $country_info['name'];
			$shipping_address_format = $country_info['address_format'];
		} else {
			$shipping_country = '';
			$shipping_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZoneInLanguage($data['shipping_zone_id']);

		if ($zone_info) {
			$shipping_zone = $zone_info['name'];
		} else {
			$shipping_zone = '';
		}

		$country_info = $this->model_localisation_country->getCountry($data['payment_country_id']);

		if ($country_info) {
			$payment_country = $country_info['name'];
			$payment_address_format = $country_info['address_format'];
		} else {
			$payment_country = '';
			$payment_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZoneInLanguage($data['payment_zone_id']);

		if ($zone_info) {
			$payment_zone = $zone_info['name'];
		} else {
			$payment_zone = '';
		}

		// Restock products before subtracting the stock later on
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND order_id = '" . (int)$order_id . "'");

        $qoyod_invoice_status = 0;
		if ($order_query->num_rows) {
            $qoyod_invoice_status = $order_query->row['qoyod_invoice'] ? $order_query->row['qoyod_invoice'] : 0;

			$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach($product_query->rows as $product) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_id = '" . (int)$product['product_id'] . "' AND subtract = '1'");

				$option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

				foreach ($option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}
        }



      	$this->db->query("UPDATE `" . DB_PREFIX . "order` SET customer_group_id = '".$this->db->escape($data['customer_group_id'])."', customer_id = '" . $this->db->escape($data['customer_id']) . "',firstname = '" . $this->db->escape($data['firstname']) . "',lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $this->db->escape($data['payment_company']) . "', payment_company_id = '" . $this->db->escape($data['payment_company_id']) . "', payment_tax_id = '" . $this->db->escape($data['payment_tax_id']) . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($payment_country) . "', payment_country_id = '" . (int)$data['payment_country_id'] . "', payment_zone = '" . $this->db->escape($payment_zone) . "', payment_zone_id = '" . (int)$data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($payment_address_format) . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "',  shipping_company = '" . $this->db->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($shipping_country) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '" . $this->db->escape($shipping_zone) . "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($shipping_address_format) . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', order_status_id = '" . (int)$data['order_status_id'] . "', affiliate_id  = '" . (int)$data['affiliate_id'] . "', payment_telephone = '" . $this->db->escape($data['payment_telephone']) . "', date_modified = NOW(), gift_product = '" . $data['gift_product'] . "',delivery_info='" . $this->db->escape($data['delivery_info']) . "' WHERE order_id = '" . (int)$order_id . "'");

        if ($order_query->row['order_status_id'] != $data['order_status_id']) {
            $historyData = [
                'notify' => 0,
                'notify_by_sms' => 0,
                'order_status_id' => $data['order_status_id'],
                'comment' => '',
                'date_added' => date('Y-m-d h:i:s'),
            ];
            $this->addOrderHistory($order_id, $historyData);
        }

      	if (isset($data['order_product'])) {
      		foreach ($data['order_product'] as $order_product) {
                // check for delete type
                if($order_product['delete_status'] === "hard"){
                    // delete order product record;
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                }
                elseif($order_product['delete_status'] === "not_deleted" || !isset($order_product['delete_status'])){
                    // order product is not deleted but may be updated
                    // 1.delete old order product record;
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
					$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
					$this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                    // 2. insert the new order product record
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_product_id = '" . (int)$order_product['order_product_id'] . "', order_id = '" . (int)$order_id . "', product_id = '" . (int)$order_product['product_id'] . "', name = '" . $this->db->escape($order_product['name']) . "', model = '" . $this->db->escape($order_product['model']) . "', quantity = '" . (int)$order_product['quantity'] . "', price = '" . (float)$order_product['price'] . "', total = '" . (float)$order_product['price']*(float)$order_product['quantity'] . "', tax = '" . (float)$order_product['tax'] . "', reward = '" . (int)$order_product['reward'] . "', added_by_user_type = '" . $this->db->escape($order_product['added_by_user_type']) . "' ");

                    $order_product_id = $this->db->getLastId();
                    // 3. update the quantity
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

                }
                else{
                    // this order product is soft deleted
                    // 1.copy the record to order_deleted_product
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_deleted_product (order_product_id,order_id,product_id,added_by_user_type,name,model,quantity,price,total,tax,reward,extra_details,code_generator) SELECT order_product_id,order_id,product_id,added_by_user_type,name,model,quantity,price,total,tax,reward,extra_details,code_generator FROM  ". DB_PREFIX . "order_product WHERE order_product_id = " . (int)$order_product['order_product_id']);
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_deleted_option (order_deleted_option_id, order_id, order_product_id, product_option_id, product_option_value_id, name, value, type) SELECT order_option_id, order_id, order_product_id, product_option_id, product_option_value_id, name, value, type FROM  ". DB_PREFIX . "order_option WHERE order_option_id = " . (int)$order_product['order_product_id']);
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_deleted_download (order_deleted_download_id, order_id, order_product_id, name, filename, mask, extension, remaining) SELECT order_download_id, order_id, order_product_id, name, filename, mask, extension, remaining FROM  ". DB_PREFIX . "order_download WHERE order_download_id = " . (int)$order_product['order_product_id']);

                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

                    // 2. delete the record from order_product as it is saved now in order_deleted_product table
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                }

				if (isset($order_product['order_option'])) {
					foreach ($order_product['order_option'] as $order_option) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_option_id = '" . (int)$order_option['order_option_id'] . "', order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$order_option['product_option_id'] . "', product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "', name = '" . $this->db->escape($order_option['name']) . "', `value` = '" . $this->db->escape($order_option['value']) . "', `type` = '" . $this->db->escape($order_option['type']) . "'");

						$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
					}
				}

				if (isset($order_product['order_download'])) {
					foreach ($order_product['order_download'] as $order_download) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_download_id = '" . (int)$order_download['order_download_id'] . "', order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($order_download['name']) . "', filename = '" . $this->db->escape($order_download['filename']) . "', mask = '" . $this->db->escape($order_download['mask']) . "', remaining = '" . (int)$order_download['remaining'] . "'");
					}
				}
			}
            // Qoyod Invoice
            $this->qoyod_create_invoice($qoyod_invoice_status, $order_id, $data['order_status_id']);
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

		if (isset($data['order_voucher'])) {
			foreach ($data['order_voucher'] as $order_voucher) {
      			$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_voucher_id = '" . (int)$order_voucher['order_voucher_id'] . "', order_id = '" . (int)$order_id . "', voucher_id = '" . (int)$order_voucher['voucher_id'] . "', description = '" . $this->db->escape($order_voucher['description']) . "', code = '" . $this->db->escape($order_voucher['code']) . "', from_name = '" . $this->db->escape($order_voucher['from_name']) . "', from_email = '" . $this->db->escape($order_voucher['from_email']) . "', to_name = '" . $this->db->escape($order_voucher['to_name']) . "', to_email = '" . $this->db->escape($order_voucher['to_email']) . "', voucher_theme_id = '" . (int)$order_voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($order_voucher['message']) . "', amount = '" . (float)$order_voucher['amount'] . "'");

				$this->db->query("UPDATE " . DB_PREFIX . "voucher SET order_id = '" . (int)$order_id . "' WHERE voucher_id = '" . (int)$order_voucher['voucher_id'] . "'");
			}
		}

        // get old shippment
		$old_shipping_code = $this->db->query("SELECT shipping_code FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'")->row;
		$old_shipping_total = $this->db->query("SELECT `title`, `text`, `value` FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'")->row;

		// Get the total
		$total = 0;

		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		if (isset($data['order_total'])) {
			$this->db->query('DELETE FROM ' . DB_PREFIX . 'coupon_history WHERE order_id = '.(int)$order_id );
      		foreach ($data['order_total'] as $order_total) {

                if ($order_total['code'] == 'shipping' && $old_shipping_code['shipping_code'] == $data['shipping_code'] && $old_shipping_total) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_total_id = '" . (int)$order_total['order_total_id'] . "', order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($order_total['code']) . "', title = '" . $this->db->escape($order_total['title']) . "', text = '" . $order_total['value']." ".$order_query->row['currency_code'] . "', `value` = '" . (float)$order_total['value'] . "', sort_order = '" . (int)$order_total['sort_order'] . "'");
                    continue;
                }

                $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_total_id = '" . (int)$order_total['order_total_id'] . "', order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($order_total['code']) . "', title = '" . $this->db->escape($order_total['title']) . "', text = '" . $this->db->escape($order_total['text']) . "', `value` = '" . (float)$order_total['value'] . "', sort_order = '" . (int)$order_total['sort_order'] . "'");
                /**
                 * Get the record of the total order amount
                 */
                if( strcmp($order_total['code'],'total') == 0 ){
                    $total = $order_total['value'];
                }
				
				if($order_total['code'] == 'coupon'){
					preg_match('#\((.*?)\)#', $order_total['title'], $match);
					$coupon_code = trim($match[1]);
					$coupon_id = $this->db->query('SELECT coupon_id FROM ' . DB_PREFIX . 'coupon WHERE code = "'.$coupon_code.'"')->row['coupon_id'];
					
					$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_history SET order_id = '" . (int)$order_id . "', coupon_id = '" . $coupon_id  . "', amount = '" .$order_total['value'] . "', customer_id = '".$this->db->escape($data['customer_id'])."', date_added = NOW() ");
				}
			
			}
		}

		// Affiliate
		$affiliate_id = 0;
		$commission = 0;

		if (!empty($this->request->post['affiliate_id'])) {
			$this->load->model('sale/affiliate');

			$affiliate_info = $this->model_sale_affiliate->getAffiliate($this->request->post['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_id = $affiliate_info['affiliate_id'];
				$commission = ($total / 100) * $affiliate_info['commission'];
			}
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '" . (float)$total . "', affiliate_id = '" . (int)$affiliate_id . "', commission = '" . (float)$commission . "' WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_modification SET order_id = '" . (int)$order_id . "', status_id = 1, user_id = '".(int)$this->session->data['user_id']."' ");

	}

    public function updateShippingTrackingURL($order_id, $url){
        $colExists = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "`order` LIKE 'shipping_tracking_url'");

        if(!$colExists->num_rows){
            $this->db->query("ALTER TABLE " . DB_PREFIX . "`order` ADD COLUMN `shipping_tracking_url` TEXT NULL AFTER `shipping_code`");
        }

        $str_sql = [];
        $str_sql[] = "UPDATE " . DB_PREFIX . "`order` set shipping_tracking_url = '".$this->db->escape($url)."'";
        $str_sql[] = "WHERE order_id = $order_id";

        $this->db->query(implode(" ",$str_sql));
    }
	
	//created for API
	public function updateOrderProducts($order_id, $data) {
       
		//need to review this part 
		/**
		 $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $this->load->model('promotions/reward_points_transactions');
            $this->model_promotions_reward_points_transactions->beforeUpdateOrder($order_id, $data);
        }
		**/


		// Restock products before subtracting the stock later on
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND order_id = '" . (int)$order_id . "'");

        $qoyod_invoice_status = 0;
		if ($order_query->num_rows) {
			$qoyod_invoice_status = 0;
			if(array_key_exists("qoyod_invoice", $order_query->row)){
            $qoyod_invoice_status = $order_query->row['qoyod_invoice'] ? $order_query->row['qoyod_invoice'] : 0;
			}
			$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach($product_query->rows as $product) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_id = '" . (int)$product['product_id'] . "' AND subtract = '1'");

				$option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

				foreach ($option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}
        }


      	if (isset($data["order_products"])) {
      		foreach ($data["order_products"] as $order_product) {
                // check for delete type
                if($order_product['delete_status'] == "hard"){
                    // delete order product record;
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                }
                elseif($order_product['delete_status'] == "not_deleted"){
                    // order product is not deleted but may be updated
                    // 1.delete old order product record;
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                    // 2. insert the new order product record
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_product_id = '" . (int)$order_product['order_product_id'] . "', order_id = '" . (int)$order_id . "', product_id = '" . (int)$order_product['product_id'] . "', name = '" . $this->db->escape($order_product['name']) . "', model = '" . $this->db->escape($order_product['model']) . "', quantity = '" . (int)$order_product['quantity'] . "', price = '" . (float)$order_product['price'] . "', total = '" . (float)$order_product['price']*(float)$order_product['quantity'] . "', tax = '" . (float)$order_product['tax'] . "', reward = '" . (int)$order_product['reward'] . "', added_by_user_type = '" . $this->db->escape($order_product['added_by_user_type']) . "' ");

                    $order_product_id = $this->db->getLastId();
                    // 3. update the quantity
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'"); 

                }
                else{
                    // this order product is soft deleted
                    // 1.copy the record to order_deleted_product
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_deleted_product (order_product_id,order_id,product_id,added_by_user_type,name,model,quantity,price,total,tax,reward,extra_details,code_generator) SELECT order_product_id,order_id,product_id,added_by_user_type,name,model,quantity,price,total,tax,reward,extra_details,code_generator FROM  ". DB_PREFIX . "order_product WHERE order_product_id = " . (int)$order_product['order_product_id']);

                    // 2. delete the record from order_product as it is saved now in order_deleted_product table
                    $this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$order_product['order_product_id'] . "'");
                }

				if (isset($order_product['order_option'])) {
					foreach ($order_product['order_option'] as $order_option) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_option_id = '" . (int)$order_option['order_option_id'] . "', order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$order_option['product_option_id'] . "', product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "', name = '" . $this->db->escape($order_option['name']) . "', `value` = '" . $this->db->escape($order_option['value']) . "', `type` = '" . $this->db->escape($order_option['type']) . "'");

						$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
					}
				}

				if (isset($order_product['order_download'])) {
					foreach ($order_product['order_download'] as $order_download) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_download SET order_download_id = '" . (int)$order_download['order_download_id'] . "', order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->db->escape($order_download['name']) . "', filename = '" . $this->db->escape($order_download['filename']) . "', mask = '" . $this->db->escape($order_download['mask']) . "', remaining = '" . (int)$order_download['remaining'] . "'");
					}
				}
			}
            // Qoyod Invoice
            $this->qoyod_create_invoice($qoyod_invoice_status, $order_id, $data['order_status_id']);
		}



        // get old shippment 
		//no need here as shipping code not changed here 
		//$old_shipping_code = $this->db->query("SELECT shipping_code FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'")->row;
		
		$old_shipping_total = $this->db->query("SELECT `title`, `text`, `value` FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'")->row;

			// need to review this part to check the effect of $data['order_total']

		// Get the total
		$total = 0;

		//update order totals
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		if (isset($data['order_total'])) {
      		foreach ($data['order_total'] as $order_total) {

                //if ($order_total['code'] == 'shipping' && $old_shipping_code['shipping_code'] == $data['shipping_code'] && $old_shipping_total) {
                if ($order_total['code'] == 'shipping'  && $old_shipping_total) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_total_id = '" . (int)$order_total['order_total_id'] . "', order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($order_total['code']) . "', title = '" . $this->db->escape($order_total['title']) . "', text = '" . $this->db->escape($order_total['text']) . "', `value` = '" . (float)$order_total['value'] . "', sort_order = '" . (int)$order_total['sort_order'] . "'");
                    continue;
                }

                $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_total_id = '" . (int)$order_total['order_total_id'] . "', order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($order_total['code']) . "', title = '" . $this->db->escape($order_total['title']) . "', text = '" . $this->db->escape($order_total['text']) . "', `value` = '" . (float)$order_total['value'] . "', sort_order = '" . (int)$order_total['sort_order'] . "'");
                /**
                 * Get the record of the total order amount
                 */
                if( strcmp($order_total['code'],'total') == 0 ){
                    $total = $order_total['value'];
                }
      		}
		}


		// Affiliate
		$affiliate_id = 0;
		$commission = 0;
		if (!empty($data['affiliate_id'])) {
			$this->load->model('sale/affiliate');
			$affiliate_info = $this->model_sale_affiliate->getAffiliate($data['affiliate_id']);
			if ($affiliate_info) {
				$affiliate_id = $affiliate_info['affiliate_id'];
				$commission = ($total / 100) * $affiliate_info['commission'];
			}
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '" . (float)$total . "', affiliate_id = '" . (int)$affiliate_id . "', commission = '" . (float)$commission . "' WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_modification SET order_id = '" . (int)$order_id . "', status_id = 1, user_id = '".(int)$this->session->data['user_id']."' ");
		return true;
	}
	
    public function updateShippingTrackingNumber($order_id, $tracking_number){
        $colExists = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "`order` LIKE 'tracking'");

        if(!$colExists->num_rows){
            $this->db->query("ALTER TABLE " . DB_PREFIX . "`order` ADD COLUMN `tracking` TEXT NULL");
        }

       $this->db->query("UPDATE " . DB_PREFIX . "`order`
            SET
            tracking = '". $this->db->escape($tracking_number) ."'
            
            WHERE order_id = '" . $order_id . "'");
    }

    public function updateShippingLabelURL($order_id, $url){
      $colExists = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "`order` LIKE 'shipping_label_url'");

      if(!$colExists->num_rows){
          $this->db->query("ALTER TABLE " . DB_PREFIX . "`order` ADD COLUMN `shipping_label_url` TEXT NULL AFTER `shipping_tracking_url`");
      }
      $this->db->query("UPDATE `" . DB_PREFIX . "order` set shipping_label_url = '" . $this->db->escape($url) . "' WHERE order_id = " . (int)$order_id);
    }

	public function deleteOrder($order_id, $action = 'archive') {

        if($action == 'archive' || $action == 'unarchive'){

            $order_query = $this->db->query("SELECT admin_log FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");
            $admin_log = [];

            if($order_query->row['admin_log']){
               $admin_log = unserialize($order_query->row['admin_log']);
            }

            ////// Archive Order
            if($action == 'archive'){
                $log = [    'id' => $this->user->getId(),
                            'name' => $this->user->getUserName(),
                            'action' => 'Archive Order',
                            'time' => date('m/d/Y h:i:s a', time())
                       ];
                array_push($admin_log, $log);

                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET archived = 1 , admin_log='".serialize($admin_log)."' WHERE order_id =" . (int)$order_id);
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_modification SET order_id = '" . (int)$order_id . "', status_id = 2, user_id = '".(int)$this->session->data['user_id']."' ");

				return true;
            }

            ////// UnArchive Order
            if($action == 'unarchive'){
                $log = [    'id' => $this->user->getId(),
                            'name' => $this->user->getUserName(),
                            'action' => 'Unarchive Order',
                            'time' => date('m/d/Y h:i:s a', time())
                       ];
                array_push($admin_log, $log);

                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET archived = 0 , admin_log='".serialize($admin_log)."' WHERE order_id =" . (int)$order_id);
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_modification SET order_id = '" . (int)$order_id . "', status_id = 3, user_id = '".(int)$this->session->data['user_id']."' ");

				return true;
            }
        }

        ////// Delete Order
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "ms_order_product_data` WHERE order_id = '" . (int)$order_id . "'");
        }

		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND order_id = '" . (int)$order_id . "'");

        $productVariationsSku = $this->load->model('module/product_variations', ['return' => true]);

		if ($order_query->num_rows) {
			$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");


			foreach($product_query->rows as $product) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_id = '" . (int)$product['product_id'] . "' AND subtract = '1'");

                $option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

				if ($productVariationsSku->isActive()) {

                    $product_option_value_id = array_column($option_query->rows, 'product_option_value_id');

                    if(count($product_option_value_id) > 0){
                        $optionValuesIds = $productVariationsSku->getOptionValuesIds($product_option_value_id);
                        if ($optionValuesIds) {
                            $optionValuesIds = array_column($optionValuesIds, 'option_value_id');
                            sort($optionValuesIds);
                            $productVariationsSku->addVariationQuantityByValuesIds(
                                $product['product_id'],
                                $optionValuesIds,
                                $product['quantity']
                            );
                        }
					}
                }

				foreach ($option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
      	$this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");
      	$this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "order_fraud WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE order_id = '" . (int)$order_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_transaction WHERE order_id = '" . (int)$order_id . "'");

        if($this->config->get('wk_amazon_connector_status')){
            $this->db->query("DELETE FROM " . DB_PREFIX . "amazon_order_map WHERE oc_order_id = '" . (int)$order_id . "'");
        }

        $setting =  $this->load->model('setting/setting', ['return' => true]);

		//solve notice error at delete API
		if($this->config->get('knawat_dropshipping_status')){
				$knawat_module = $this->load->model('module/knawat_dropshipping', ['return' => true]);
				$knawat_module->delete_knawat_meta($order_id,'knawat_order','order');
				$knawat_module->delete_knawat_meta($order_id,'knawat_order_id','order');
				$knawat_module->delete_knawat_meta($order_id,'knawat_sync_failed','order');
		}

        return True;
	}

	public function nextOrderId($order_id){

		$sql = "select min(order_id) as order_id from `order` where order_id > '" .$order_id.
			"' and order_status_id > 0 AND archived = 0";

		$query = $this->db->query($sql);
		return $query->row['order_id'];
	}

	public function previousOrderId($order_id){

		$sql = "select max(order_id) as order_id from `order` where order_id < '" .$order_id.
			"' and order_status_id > 0 AND archived = 0";

		$query = $this->db->query($sql);
		return $query->row['order_id'];
    }
	public function getOrder($order_id) {

		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer, (SELECT `approved` FROM customer as cu where cu.customer_id=o.customer_id) as customer_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$reward = 0;

			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
                $payment_phonecode  = $country_query->row['phonecode'];                
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
                $shipping_country_name = $country_query->row['name'];
                $shipping_phonecode  = $country_query->row['phonecode'];                
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
                $shipping_country_name = $order_query->row['shipping_country'];
			}

            $languageId = $this->config->get('config_language_id');
            if (isset($this->request->request['language_id'])) {
                $languageId = $this->request->request['language_id'];
            }

            $languageCode = $this->config->get('config_language');
            if (isset($this->request->request['language_code'])) {
                $languageCode = $this->request->request['language_code'];
            }

            if ($country_query->num_rows && $this->options['routeString'] == 'sale/order/invoice') {

                $country_query = $this->db->query("SELECT *, (select name from countries_locale where countries_locale.country_id = country.country_id and lang_id = '" . $languageId . "') as name_local FROM `country` WHERE country_id = '" . $order_query->row['shipping_country_id'] . "'");
                $shipping_iso_code_2 = $country_query->row['iso_code_2'];
                $shipping_iso_code_3 = $country_query->row['iso_code_3'];
                $shipping_country_name = $country_query->row['name_local'];
            }

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

            //Get GeoArea code
            $shipping_area_code = '';
            if( isset($order_query->row['shipping_area_id']) ){
                $area_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "geo_area` WHERE area_id = '" . (int)$order_query->row['shipping_area_id'] . "'");
                if ($area_query->num_rows) {
                    $shipping_area_code = $area_query->row['code'];
                }
            }

			if ($order_query->row['affiliate_id']) {
				$affiliate_id = $order_query->row['affiliate_id'];
			} else {
				$affiliate_id = 0;
			}


            $shipping_method_name_parts = explode('.', $order_query->row['shipping_code']);
            $shipping_method_name = end($shipping_method_name_parts);

            if (isset($shipping_method_name_parts[0]) && $shipping_method_name_parts[0] === 'category_product_based') {
                $shipping_method_name = 'category_product_based';
            }

            $shipping_method = $this->ecusersdb->query(implode(' ', [
                'SELECT * FROM shipping_methods sm INNER JOIN shipping_methods_description smd ON',
                'sm.id = smd.shipping_method_id',
                'WHERE smd.lang = "'. $languageCode .'"',
                'AND sm.code = "'. $shipping_method_name .'"'
            ]));

            if ($shipping_method->num_rows > 0 && strpos($shipping_method->row['code'], 'category_product_based') === false) {
                $shipping_method_locale = $shipping_method->row['title'];
            } else {
                $shipping_method_locale = $order_query->row['shipping_method'];
            }
            $affiliate_firstname = '';
            $affiliate_lastname = '';

			if ($affiliate_id) {

				$this->load->model('sale/affiliate');

				$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);
                if ($affiliate_info) {
                    $affiliate_firstname = $affiliate_info['firstname'];
                    $affiliate_lastname = $affiliate_info['lastname'];
                }
    
            }

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
				$language_filename = $language_info['filename'];
				$language_directory = $language_info['directory'];
			} else {
				$language_code = '';
				$language_filename = '';
				$language_directory = '';
			}
			//solve API issue at undefined index tracking
			if(!array_key_exists('tracking',$order_query->row)){
				$order_query->row['tracking']='';
			}

			$transactionQuery = $this->db->query("select * from payment_transactions where order_id = '" . (int)$order_id . "' ORDER BY `id` DESC LIMIT 1");
			$paymentMethod = $order_query->row['payment_method'];
			if($transactionQuery->num_rows > 0) {

				$paymentMethod = $transactionQuery->row["payment_method"];
			}
			$address_id = isset($order_query->row['address_id']) ? $order_query->row['address_id']:  0;


			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'customer'                => $order_query->row['customer'],
				'customer_status'         => $order_query->row['customer_status'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'payment_telephone'	      => $order_query->row['payment_telephone'],
				'fax'                     => $order_query->row['fax'],
				'email'                   => $order_query->row['email'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_company_id'      => $order_query->row['payment_company_id'],
				'payment_tax_id'          => $order_query->row['payment_tax_id'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_area_id'         => $order_query->row['payment_area_id'],
				'payment_area'            => $order_query->row['payment_area'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
                'payment_phonecode'       => $payment_phonecode,            
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_address_location'=> $order_query->row['payment_address_location'],
				'payment_method'          => $paymentMethod,
				'payment_code'            => $order_query->row['payment_code'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_area_id'        => $order_query->row['shipping_area_id'],
				'shipping_area'           => $order_query->row['shipping_area'],
                'shipping_zone_code'      => $shipping_zone_code,
				'shipping_area_code'      => $shipping_area_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $shipping_country_name,
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
                'shipping_phonecode'      => !empty($shipping_phonecode) ? $shipping_phonecode : null,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
                'shipping_address_location'=> $order_query->row['shipping_address_location'],
				'shipping_method'         => $shipping_method_locale,
                'shipping_code'           => $order_query->row['shipping_code'],
                'shipping_tracking_url'   => $order_query->row['shipping_tracking_url'],
                'shipping_label_url'      => $order_query->row['shipping_label_url'],
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
				'language_filename'       => $language_filename,
				'language_directory'      => $language_directory,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'address_id'          => $order_query->row['address_id'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified'],
				'gift_product'            => $order_query->row['gift_product'],
                'archived'                => $order_query->row['archived'],
				'delivery_info'           => $order_query->row['delivery_info'],
                'tracking'                => $order_query->row['tracking'],
				'payment_trackId'         => $order_query->row['payment_trackId'],
				'shipping_trackId'        => $order_query->row['shipping_trackId'],
                'psid'                    => isset($order_query->row['psid']) ? $order_query->row['psid'] : null,
                'order_attributes'        => json_decode($order_query->row['order_attributes'], true),
                'gift_product'            => $order_query->row['gift_product'],
                'order_quantity'          => count($order_product_query->rows),
				'product'				  => $order_product_query->rows

			);
		} else {
			return false;
		}
	}

	public function getOrders($data = array()) {

        $language_id = $this->config->get('config_language_id') ?: 1;

		$sql = "SELECT o.order_id, o.order_status_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT `approved` FROM customer as cu where cu.customer_id=o.customer_id) as customer_status,(SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . $language_id . "') AS status, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified, o.customer_id, o.gift_product FROM `" . DB_PREFIX . "order` o";

		if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

        //Exclude archived orders
        $sql .= " AND o.archived = 0";

		if (!empty($data['filter_total'])) {
			$sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
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

	/**
	 * Api get orders
	 *
	 * @return mixed
	 */
	public function getOrdersForApi($filterData = array())
	{
        $data['select_extra_fields'] = [
            'o.invoice_no',
            'o.firstname',
            'o.lastname',
            'o.email',
            'o.telephone',
            'o.payment_method',
            'o.payment_code',
            'o.shipping_firstname',
            'o.shipping_lastname',
            'o.shipping_company',
            'o.shipping_address_1',
            'o.shipping_address_2',
            'o.shipping_postcode',
            'o.shipping_city',
            'o.shipping_zone',
            'o.shipping_zone_id',
            'o.comment'
		];

		//sort,order,start and limit are handled at getOrdersToFilter in data array not on filterData array
		if (isset($filterData['sort'])) {
            $data['sort'] = $filterData['sort'];
        }
		if (isset($filterData['order'])) {
            $data['order'] = $filterData['order'];
        }
		if (isset($filterData['start'])) {
            $data['start'] = $filterData['start'];
        }
		if (isset($filterData['limit'])) {
            $data['limit'] = $filterData['limit'];
        }

		$ordersData = $this->getOrdersToFilter($data, $filterData);
		$return = [
				'status' => 'ok',
				'data'   => $ordersData['data'],
				'page'   => $data['start'],
				'limit'  => $data['limit'],
				'total'  => $ordersData['total']
		];
		return $return;
	}

	/**
	 * Api get order details
	 * @param int $order_id
	 * @return mixed
	 */
	public function getOrderForApi($order_id)
	{
		$return = [
			'status' => 'ok',
			'data'   => $this->getOrder($order_id),
			'products'   => $this->getOrderProducts($order_id),
			'history' => $this->getOrderHistories($order_id),
			'totals'  	 => $this->getOrderTotals($order_id),
			'order_options'  => $this->getOrderOptions($order_id),
		];
		return $return;
	}

	/**
	 * Get all order products.
	 *
	 * @param int $order_id
	 *
	 * @return array|bool
	 */
	public function getOrderProducts($order_id,$product_designer=false)
	{

        $query = $fields = [];
        $fields[] = 'order_product.*';
        $fields[] = 'product.image';
        $fields[] = 'product.status as product_status';
        $fields[] = 'product_description.name as product_name';
        $fields[] = 'product.subtract as product_subtract_status';
        $fields[] = 'product.product_id as original_id';
        $fields[] = 'product.weight as weight';
        $fields[] = 'product.weight_class_id';
        $fields[] = 'product.length_class_id';
        $fields[] = 'product.width as width';
        $fields[] = 'product.height as height';
        $fields[] = 'product.length as length';
        $fields[] = 'product.sku';
        $fields[] = 'product.barcode';
        $fields[] = 'product.storable';
        if($product_designer){
            $fields[] = 'product.pd_back_image';
            $fields[] = 'tshirtdesign.*';
        }

        $fields = implode(',', $fields);

        $query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
        $query[] = 'LEFT JOIN product ON order_product.product_id = product.product_id';
        $query[] = 'LEFT JOIN product_description ON order_product.product_id = product_description.product_id';
        $query[] = 'AND product_description.language_id="'.(int)$this->config->get('config_language_id').'"';
    	if($product_designer){
            	$query[] = 'LEFT JOIN tshirtdesign ON order_product.product_id = tshirtdesign.pid AND order_product.pd_tshirt_id = tshirtdesign.did';
    	}
    	$query[] = 'WHERE order_id = "' . (int)$order_id . '"';

		$data = $this->db->query(implode(' ', $query));
		if ($data->num_rows) {
            return $data->rows;

        }

        return false;
	}
    
    public function getOrderShipmentDescription($order_id)
    {
    	return $this->db->query('SELECT group_concat(`name`) AS description
    		FROM category_description 
    		WHERE category_id = 
    		(
    			SELECT category_id 
    			FROM `product_to_category`
    			WHERE product_id IN (SELECT product_id FROM order_product WHERE order_id = ' .(int)$order_id . ') 
    			limit 1
    		) AND language_id = '. (int)$this->config->get('config_language_id'))->row['description'];
    }
	/**
	 * Gete all orders for invoice
	 *
	 * @param int $orderId
	 * @param int $languageId
	 *
	 * return array|bool
	 */
	public function getOrderProductsForInvoice($orderId, $languageId)
	{
        $sortBy = $this->config->get('config_invoice_products_sort_order');
		$sortTy = $this->config->get('config_invoice_products_sort_type') ?? 'ASC';
        $this->load->model('module/rental_products/settings');

        if($sortBy === 'category'){
			$sortLv = $this->config->get('config_invoice_products_sort_ctlevel') ?? 0;
            /*$categories = [];
            $sorted_categories = [];*/
            $products=[];
			//Select order's products ordered by category name
			$query = '
            SELECT
                DISTINCT op.product_id,
                op.model,
                op.quantity,
                op.price,
                op.total,
                op.order_product_id,
                op.tax,
                IFNULL(ptc.category_id, 0) as category_id,
                cp.level,
                cd.name as category_name,
                p.image,
                p.status as product_status,
                p.sku,
                pd.name,
                p.barcode
            FROM order_product op
            left join product_to_category ptc on ptc.product_id=op.product_id
            left join category_path cp on cp.path_id=ptc.category_id
            left join category_description cd on ptc.category_id=cd.category_id
            left join product p on op.product_id=p.product_id
            left join product_description pd on op.product_id=pd.product_id

            WHERE order_id = '.$orderId.'
            AND cd.language_id=' . (int)$languageId . '
            AND pd.language_id=' . (int)$languageId . '
            ORDER BY category_name '.$sortTy;

			$tempIds = [0];
			$allData = $this->db->query($query)->rows;
			//First pick products with category level of selected level $sortLv
			foreach ($allData as $idx => $product) {
				if ($product['level'] == $sortLv && !in_array($product['order_product_id'], $tempIds)) {
					$products[$product['order_product_id']] = $product;
					$tempIds[] = $product['order_product_id'];
					unset($allData[$idx]);
				}
			}
			//Check if there are products not picked in last step

			foreach ($allData as $idx => $product) {
				if (!in_array($product['order_product_id'], $tempIds)) {
					$products[$product['order_product_id']] = $product;
					$tempIds[] = $product['order_product_id'];
				}
			}
            //get categories
            /*$query = '
            SELECT
                op.product_id,
                IFNULL(ptc.category_id, 0)
            FROM order_product op
            left join product_to_category ptc on ptc.product_id=op.product_id
            WHERE order_id = '.$orderId;*/

			//Getting products categories ids based on child level
			/*$query = '
            SELECT
               DISTINCT ptc.category_id
            FROM order_product op
            left join product_to_category ptc on ptc.product_id=op.product_id
            left join category_path cp on cp.path_id=ptc.category_id
            WHERE cp.level = 0 AND order_id = '.$orderId;*/


            /*foreach($this->db->query($query)->rows as $product){
					//get product categories
					$sql = '
                    select
                        c.category_id,
                        cd.name
                    from category c
                    left join category_description cd on c.category_id=cd.category_id
                    where c.category_id=' . (int)$product['category_id'] . '
                    and cd.language_id=' . (int)$languageId . '
                ';

					if (!isset($categories[$product['category_id']])) {
						$categories[$product['category_id']] = $this->db->query($sql)->row;
					} else {
						$categories[0] = '';
					}
					$categories[$product['category_id']]['products'][] = $product['product_id'];
				}*/

				/*foreach ($categories as $cat) {
					$sorted_categories[$cat['category_id']] = $cat['name'];
				}*/

				//asort($sorted_categories);

				/*foreach ($sorted_categories as $key => $category) {
					//get category products by ids
					$query = [];

					$fields = 'order_product.*, product.image, product.status as product_status, product.sku, product_description.name, product.barcode';

					$query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
					$query[] = 'JOIN product ON order_product.product_id = product.product_id';
					$query[] = 'JOIN product_description ON order_product.product_id = product_description.product_id';
					$query[] = 'WHERE product.product_id in(' . implode(',', $categories[$key]['products']) . ')';
					$query[] = 'AND order_id = "' . (int)$orderId . '"';
					$query[] = 'AND language_id = "' . (int)$languageId . '"';

					$data = $this->db->query(implode(' ', $query));

					foreach ($data->rows as $product) {
						if (!isset($products[$product['product_id']])) {
							$products[$product['product_id']] = $product;
						}
					}
				}*/

				return $products;
        }

		$query = [];

		$fields = 'order_product.*, product.image, product.status as product_status, product.sku,product_description.name,product.barcode';
        
        // get rental fields if rental app is installed
        if($this->model_module_rental_products_settings->isActive()){
            $fields .= ',order_product_rental.from_date,order_product_rental.to_date,order_product_rental.diff';
        }

		$query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
        $query[] = 'JOIN product ON order_product.product_id = product.product_id';
        $query[] = 'JOIN product_description ON order_product.product_id = product_description.product_id';

        // join order_product_rental table is rental app is installed
        if($this->model_module_rental_products_settings->isActive()){
            $query[] = 'LEFT JOIN order_product_rental ON order_product.order_product_id = order_product_rental.order_product_id';
        }

        $query[] = 'WHERE order_product.order_id = "' . (int)$orderId . '"';
        $query[] = 'AND language_id = "' . (int)$languageId . '"';

        if ($sortBy == 'model') {
            $query[] = 'ORDER BY product.model '.$sortTy;
        } else if ($sortBy == 'sku') {
            $query[] = 'ORDER BY product.sku '.$sortTy ;
        }

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
        	return $data->rows;
		}

        return false;
	}
    public function getOrderSoftDeletedProductsForInvoice($orderId, $languageId)
    {
        $sortBy = $this->config->get('config_invoice_products_sort_order');
        $sortTy = $this->config->get('config_invoice_products_sort_type') ?? 'ASC';
        $query = [];
        $fields = 'order_deleted_product.*, product.image, product.status as product_status, product.sku, product_description.name, product.barcode';

        $query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_deleted_product';
        $query[] = 'JOIN product ON order_deleted_product.product_id = product.product_id JOIN';
        $query[] = 'product_description ON order_deleted_product.product_id = product_description.product_id';
        $query[] = 'WHERE order_id = "' . (int)$orderId . '"';
        $query[] = 'AND language_id = "' . (int)$languageId . '"';

        if ($sortBy == 'model') {
            $query[] = 'ORDER BY product.model '.$sortTy;
        } else if ($sortBy == 'sku') {
            $query[] = 'ORDER BY product.sku '.$sortTy ;
        }

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }
	public function getOrderProductsRemainingTotal($orderId)
	{

		$query = $this->db->query('SELECT SUM(`remaining_amount`)   as remaining_total  FROM ' . DB_PREFIX . 'order_product WHERE order_id = "' . (int)$orderId . '"');

        return $query->row['remaining_total'];
	}


	/**
	 * Gete all orders for invoice
	 *
	 * @param int $orderId
	 * @param int $languageId
	 *
	 * return array|bool
	 */
	public function getOrderProductsForCancelledOrder($orderId)
	{
		$query = [];

		$fields = 'product.product_id, order_product.quantity,order_product_id';

		$query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
        $query[] = 'JOIN product ON order_product.product_id = product.product_id';
        $query[] = 'WHERE order_id = "' . (int)$orderId . '"';
        $query[] = 'AND product.subtract = 1';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
        	return $data->rows;
		}

        return false;
    }

    public function updateOrderProductQuantities($product_data,$operation)
	{
		$query = [];

		$query[] = 'UPDATE ' . DB_PREFIX . 'product';
        $query[] = 'SET quantity=quantity '.$operation.' '.(int)$product_data['qty'];
        $query[] = 'WHERE product_id = ' . (int)$product_data['id'];

        $this->db->query(implode(' ', $query));

    }

    public function updateOrderProductOptionQuantities($product_option_data,$operation)
	{
		$query[] = 'UPDATE ' . DB_PREFIX . 'product_option_value';
        $query[] = 'SET quantity=quantity '.$operation.' '.(int)$product_option_data['op_qty'];
        $query[] = 'WHERE product_option_value_id = ' . (int)$product_option_data['product_option_value_id'];
        $query[] = 'AND subtract = 1';

        $this->db->query(implode(' ', $query));

    }

	public function getOrderProductsForProductDesigner($order_id)
    {
        $query = $this->db->query("SELECT order_product.*, product.image, product.pd_back_image, product.status as product_status FROM " . DB_PREFIX . "order_product JOIN product ON order_product.product_id = product.product_id WHERE order_id = '" . (int)$order_id . "'");

        return $query->rows;
    }

	public function getOrderOption($order_id, $order_option_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_option_id = '" . (int)$order_option_id . "'");

		return $query->row;
	}

	public function getOrderOptions($order_id, $order_product_id=null) {
        $sql = "SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'";

        if($order_product_id){
            $sql .= "AND order_product_id = '" . (int)$order_product_id . "'";
        }

		$query = $this->db->query($sql);

		return $query->rows;
    }

	public function getProductOptionValue($product_option_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = '" . (int)$product_option_id . "' ");

		return $query->rows;
	}

	/**
	 * Get order options for invoices.
	 *
	 * @param int $orderId
	 * @param int $orderProductId
	 *
	 * @return array
	 */
    public function getOrderOptionsForInvoice($orderId, $orderProductId, $printLang = 1)
    {
        $isOptionPriceHidden = $this->config->get('config_invoice_option_price') == 1;

        $query = [];
        $query[] = 'SELECT oo.* ,pov.product_id';
		$query[] = ', IF(ovd.name IS NULL or ovd.name = "", oo.value, ovd.name) AS value, IF(od.name IS NULL or od.name = "", oo.name, od.name) as name';


		if (!$isOptionPriceHidden){
			$query[] = ', pov.price, pov.price_prefix';
		}

// we make left join to get option of type text as text option don't have product_option_value record
        $query[] = 'FROM ' . DB_PREFIX . 'order_option AS oo';
        $query[] = 'LEFT JOIN ' . DB_PREFIX . 'product_option_value AS pov';
        $query[] = 'ON pov.product_option_value_id=oo.product_option_value_id';
		$query[] = 'LEFT JOIN ' . DB_PREFIX . 'option_value_description AS ovd';
		$query[] = 'ON pov.option_value_id = ovd.option_value_id';
		$query[] = 'AND ovd.language_id = "' . (int) $printLang . '"';
		$query[] = 'INNER JOIN ' . DB_PREFIX . 'product_option AS po';
		$query[] = 'ON po.product_option_id = oo.product_option_id';
		$query[] = 'LEFT JOIN ' . DB_PREFIX . 'option_description AS od';
		$query[] = 'ON po.option_id = od.option_id';
		$query[] = 'AND od.language_id = "' . (int) $printLang . '"';
        $query[] = 'WHERE order_id = "' . (int)$orderId . '"';
        $query[] = 'AND order_product_id = "' . (int)$orderProductId . '"';

        $data = $this->db->query(implode(' ', $query));

        return $data->rows;
    }

	public function getOrderDownloads($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderVoucherByVoucherId($voucher_id) {
      	$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->row;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getTotalOrders($data = array()) {
		$language_id = $this->config->get('config_language_id') ?: 1;
    	$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` as o";

      	$sql .= " LEFT JOIN order_status os ON (os.order_status_id = o.order_status_id)";
		if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

        if (!empty($data['filter_date_added_greater_equal'])) {
            $sql .= " AND DATE(date_added) >= DATE('" . $this->db->escape($data['filter_date_added_greater_equal']) . "')";
        }

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND total = '" . (float)$data['filter_total'] . "'";
		}

        //Exclude archived orders
        $sql .= " AND archived = 0";

		$sql .= ' AND os.language_id = "' . $language_id . '"';

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalOrdersByStoreId($store_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE store_id = '" . (int)$store_id . "' AND archived = 0");

		return $query->row['total'];
	}

	public function getTotalOrdersByOrderStatusId($order_status_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id = '" . (int)$order_status_id . "' AND order_status_id > '0' AND archived = 0");

		return $query->row['total'];
	}

	public function getTotalOrdersByLanguageId($language_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE language_id = '" . (int)$language_id . "' AND order_status_id > '0' AND archived = 0");

		return $query->row['total'];
	}

	public function getTotalOrdersByCurrencyId($currency_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE currency_id = '" . (int)$currency_id . "' AND order_status_id > '0' AND archived = 0");

		return $query->row['total'];
	}

	public function getTotalSales() {
      	$query = $this->db->query("SELECT SUM(total) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND archived = 0");

		return $query->row['total'];
	}

	public function getTotalSalesByYear($year) {
      	$query = $this->db->query("SELECT SUM(total) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND YEAR(date_added) = '" . (int)$year . "' AND archived = 0");

		return $query->row['total'];
	}

	public function createInvoiceNo($order_id) {
		$order_info = $this->getOrder($order_id);

		if ($order_info && !$order_info['invoice_no']) {
			$query = $this->db->query("SELECT MAX(invoice_no) AS invoice_no FROM `" . DB_PREFIX . "order` WHERE invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "'");

			if ($query->row['invoice_no']) {
				$invoice_no = $query->row['invoice_no'] + 1;
			} else {
				$invoice_no = 1;
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "' WHERE order_id = '" . (int)$order_id . "'");

			return $order_info['invoice_prefix'] . $invoice_no;
		}
	}

	public function addOrderHistory($order_id, $data) {
        //smshare
        $is_email_notif = isset($data['notify']) ? (int)$data['notify'] : 0;
        $is_sms_notif   = isset($data['notify_by_sms']) ? (int)$data['notify_by_sms'] : 0;
        $is_whatsapp_notif   = isset($data['notify_by_whatsapp']) ? (int)$data['notify_by_whatsapp'] : 0;
        $is_notifications_notif   = isset($data['notify_by_notifications']) ? (int)$data['notify_by_notifications'] : 0;
        $is_notif       = $is_email_notif || $is_sms_notif;

        if(isset($data['api']) && $data['api']){
			$user_id = $data['user_id'];
		}else{
			$user_id = $this->user->getId();
		}

        $all_comments = $comment = "";
		
		if(isset($data['comment'])){
			$all_comments = $comment = $this->db->escape($data['comment']);
		}
		
        if($this->getOrderCommentByID($order_id) !== false){
            $previous_comment  = $this->getOrderCommentByID($order_id);
            $all_comments = $this->db->escape(implode("<br>",[$previous_comment,$all_comments]));
        }

        $dateNow = date('Y-m-d H:i:s');

		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$data['order_status_id'] . "', comment='".$all_comments."', date_modified = "."'$dateNow'"." WHERE order_id = '" . (int)$order_id . "'");
        
        /*
            Tamara payment gateway
            Check if Tamara installed and the new status is a tamara status, 
            Then make an API Call to tamara appropriate method
        */
        $this->_checkIfTamaraStatus($data['order_status_id'], $order_id);

        // todo order balance entries
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            $ms_order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

            // todo order balance entries
            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($queryMultiseller->num_rows) {
                $this->language->load('multiseller/multiseller');
                if (in_array($data['order_status_id'], $this->config->get('msconf_credit_order_statuses'))) {
                    $sendmail = false;
                    foreach ($ms_order_product_query->rows as $order_product) {
                        $seller_id = $this->MsLoader->MsProduct->getSellerId($order_product['product_id']);
                        if (!$seller_id) continue;

                        $balance_entry = $this->MsLoader->MsBalance->getBalanceEntry(
                            array(
                                'seller_id' => $seller_id,
                                'product_id' => $order_product['product_id'],
                                'order_id' => $order_id,
                            )
                        );

                        if (!$balance_entry || $balance_entry['balance_type']==MsBalance::MS_BALANCE_TYPE_REFUND) {
                            $commissions = $this->MsLoader->MsCommission->calculateCommission(array('seller_id' => $seller_id, 'product_id' => $order_product['product_id']));

                            if ($order_product['total'] > 0) {
                                $store_commission_flat = $commissions[MsCommission::RATE_SALE]['flat'];
                                $store_commission_pct = $order_product['total'] * $commissions[MsCommission::RATE_SALE]['percent'] / 100;
                                $seller_net_amt = $order_product['total'] - ($store_commission_flat + $store_commission_pct);
                            } else  {
                                $store_commission_flat = $store_commission_pct = $seller_net_amt = 0;
                            }

                            // Add order data if doesn't exist yet
                            $order_data = $this->MsLoader->MsOrderData->getOrderData(
                                array(
                                    'product_id' => $order_product['product_id'],
                                    'order_id' => $order_product['order_id'],
                                )
                            );
                            if (!$order_data) {
                                $this->MsLoader->MsOrderData->addOrderProductData(
                                    $order_product['order_id'],
                                    $order_product['product_id'],
                                    array(
                                        'seller_id' => $seller_id,
                                        'store_commission_flat' => $store_commission_flat,
                                        'store_commission_pct' => $store_commission_pct,
                                        'seller_net_amt' => $seller_net_amt
                                    )
                                );
                            }

                            $this->db->query("UPDATE " . DB_PREFIX . "ms_product SET number_sold  = (number_sold + " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "'");

                            $this->MsLoader->MsBalance->addBalanceEntry(
                                $seller_id,
                                array(
                                    'order_id' => $order_product['order_id'],
                                    'product_id' => $order_product['product_id'],
                                    'balance_type' => MsBalance::MS_BALANCE_TYPE_SALE,
                                    'amount' => $seller_net_amt,
                                    'description' => sprintf($this->language->get('ms_transaction_sale'),  ($order_product['quantity'] > 1 ? $order_product['quantity'] . ' x ' : '')  . $order_product['name'], $this->currency->format($store_commission_flat + $store_commission_pct, $this->config->get('config_currency')))
                                )
                            );

                            $sendmail = true;
                        } else {
                            // send order status change mails
                        }
                    }
                    if ($sendmail) $this->MsLoader->MsMail->sendOrderMails($order_id);
                } else if (in_array($data['order_status_id'], $this->config->get('msconf_debit_order_statuses'))) {
                    $sendmail = false;
                    foreach ($ms_order_product_query->rows as $order_product) {
                        $seller_id = $this->MsLoader->MsProduct->getSellerId($order_product['product_id']);
                        if (!$seller_id) continue;

                        $balance_entry = $this->MsLoader->MsBalance->getBalanceEntry(
                            array(
                                'seller_id' => $seller_id,
                                'product_id' => $order_product['product_id'],
                                'order_id' => $order_id,
                            )
                        );

                        if ($balance_entry && $balance_entry['balance_type']==MsBalance::MS_BALANCE_TYPE_SALE) {
                            $this->db->query("UPDATE " . DB_PREFIX . "ms_product SET number_sold  = (number_sold - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "'");

                            $this->MsLoader->MsBalance->addBalanceEntry(
                                $balance_entry['seller_id'],
                                array(
                                    'order_id' => $balance_entry['order_id'],
                                    'product_id' => $balance_entry['product_id'],
                                    'balance_type' => MsBalance::MS_BALANCE_TYPE_REFUND,
                                    'amount' => -1 * $balance_entry['amount'],
                                    'description' => sprintf($this->language->get('ms_transaction_refund'),  ($order_product['quantity'] > 1 ? $order_product['quantity'] . ' x ' : '')  . $order_product['name'])
                                )
                            );

                            // send refund mails
                            // $this->MsLoader->MsMail->sendOrderMails($order_id);
                        } else {
                            // send order status change mails
                        }
                    }
                }
            }
        }
        // Get last order history status id
        $last_order_history_data = $this->getOrderHistories($order_id,0,1);
        $last_order_status_id = array_column($last_order_history_data,'order_status_id');

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$data['order_status_id'] . "', notify = '" . $is_notif . "', comment = '" . $this->db->escape(strip_tags($comment)) . "', user_id = '" . $user_id . "', date_added = '" . date("Y-m-d H:i:s") . "'");

        $order_info = $this->getOrder($order_id);
        
        //Check if payment method is jumiapay and the new status is jumiapay refund status
        if($order_info['payment_code'] == 'jumiapay' && 
        	$data['order_status_id'] == $this->config->get('jumiapay')['refund_status_id']) {
        	$this->sendJumiaRefundRequest($order_id);
        }

        //fire update order status trigger for zapier  if installed
        $this->load->model('module/zapier');
        if ($this->model_module_zapier->isInstalled()) {
            $this->model_module_zapier->updateOrderStatusTrigger($order_info);
        }

		/***************** add order to knawat if it's products belong to it  ****************/
		$this->load->model('module/knawat');
		if ($this->model_module_knawat->isInstalled()) {
			if ($this->model_module_knawat->checkOrderStatusForInsertToKnawat($order_id) && $this->model_module_knawat->checkIfKnawatOrder($order_id))
				$this->model_module_knawat->insertOrderIntoKnawat($order_id);
		}

        $querySMSModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'smshare'");
        if($querySMSModule->num_rows) {
            $this->load->model('module/smshare');
            $this->model_module_smshare->notify_or_not_on_order_status_update($order_info, $data);
        }
		
		//whatsapp 
		if (\Extension::isInstalled('whatsapp')) {
			$queryWhatsAppModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'whatsapp'");
			
			if($queryWhatsAppModule->num_rows) {
				$this->load->model('module/whatsapp');
				$this->model_module_whatsapp->notify_or_not_on_order_status_update($order_info, $data);
			}
		}
		
		//whatsapp-v2
		if (\Extension::isInstalled('whatsapp_cloud')) {
			$this->load->model('module/whatsapp_cloud');
			$this->model_module_whatsapp_cloud->orderStatusUpdateNotification($order_info, $data);
		}
		
        /*
           Check if the order is cancelled to return product quantities
           in case of products stock are able to be subtracted
           Author: Bassem
        */
        if ($this->config->get('config_cancelled_order_status_id') == $data['order_status_id']) {
            $this->returnProductQuantites($order_id);
        }
        /*
           Check if the cancelled order will be reversal to return product quantities
           in case of products stock are able to be subtracted
           Author: Bassem
        */
        if ($this->config->get('config_cancelled_reversal_status_id') == $data['order_status_id']
        && $last_order_status_id[0]  ==  $this->config->get('config_cancelled_order_status_id')) {
            $this->returnProductQuantites($order_id,'-');
        }
		// Send out any gift voucher mails
		if ($this->config->get('config_complete_status_id') == $data['order_status_id']) {
			$this->load->model('sale/voucher');

			$results = $this->getOrderVouchers($order_id);

			foreach ($results as $result) {
				$this->model_sale_voucher->sendVoucher($result['voucher_id']);
			}
        }

        if(isset($data['notify']) && $data['notify']){
			$language = new Language($order_info['language_directory'],$this->registry);
			$language->load($order_info['language_filename']);
			$language->load('mail/order');

			$subject = sprintf($language->get('text_subject'), $order_info['store_name'], $order_id);

			$message  = $language->get('text_order') . ' ' . $order_id . "\n";
			$message .= $language->get('text_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$data['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

			if ($order_status_query->num_rows) {
				$message .= $language->get('text_order_status') . "\n";
				$message .= $order_status_query->row['name'] . "\n\n";
			}

			if ($order_info['customer_id']) {
				$message .= $language->get('text_link') . "\n";
				$message .= html_entity_decode($order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id, ENT_QUOTES, 'UTF-8') . "\n\n";
			}

			if ($comment) {
				$message .= $language->get('text_comment') . "\n\n";
				$message .= strip_tags(html_entity_decode($comment, ENT_QUOTES, 'UTF-8')) . "\n\n";
			}

			$message .= $language->get('text_footer');

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
                       $order_info['store_name'] = (!empty($order_info['store_name'])) ? $order_info['store_name'] : $this->config->get('config_name')[$this->config->get('config_language')];
                                
			$mail->setTo($order_info['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($order_info['store_name']); //here we check if store name exists else get from config 
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));

            $custome_email = false;

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
                    $custome_email = true;
                    $mail->setBccEmails($cet_result['bcc_emails']);
                    $mail->send();
                    $mail->sendBccEmails();
                }
            }

            if(!$custome_email){
                $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
                $mail->send();
            }

            //if ($this->config->get('custom_email_templates_status')) {
            //$mail->sendBccEmails();
            //}
		}

        if(\Extension::isInstalled('customer_notifications') &&$this->config->get('customer_notifications')['status']==1
            &&$this->config->get('customer_notifications')['order_status_notify']==1 &&  $is_notifications_notif)
            {
                $customerNotifData = array(
                    'customer_id' => $order_info['customer_id'],
                    'icon'=> 'fa-shopping-cart',   
                    'notification_text'=> ['order_status_id',$data['order_status_id']],
                    'notification_module'=> 'orders',
                    'notification_module_code'=> 'orders_update_status',
                    'notification_module_id'=>$order_id
                );
                $this->load->model('module/customer_notifications');
                $this->model_module_customer_notifications->addCustomerNotifications($customerNotifData);

            }

        return true;
	}

    private function getOrderCommentByID($order_id)
    {
        $sql = "SELECT comment from `order` where order_id=$order_id";
        $result = $this->db->query($sql);

        if($result->num_rows){
            return $result->row['comment'];
        }
        return false;
    }
    private function returnProductQuantites($order_id,$operation = '+')
    {
        # code...
        $products = $this->getOrderProductsForCancelledOrder($order_id);
        $productVariationsSku = $this->load->model('module/product_variations', ['return' => true]);

        foreach ($products as $product) {
            # code...
            $product_data = array(
                'id' => $product['product_id'],
                'qty'=> $product['quantity']
            );
            # First level: product
            $this->updateOrderProductQuantities($product_data,$operation);

            $order_product_options = $this->getOrderOptions($order_id,$product['order_product_id']);
            foreach ($order_product_options as $order_product_option) {
                # code...
                $option_data = array(
                    'op_qty' => $product['quantity'],
                    'product_option_value_id'=> $order_product_option['product_option_value_id']
                );
                # Second level: product option
                $this->updateOrderProductOptionQuantities($option_data,$operation);
            }
            # Third level: product option sku
            if ($productVariationsSku->isActive()) {
                $product_option_value_id = array_column($order_product_options, 'product_option_value_id');

                if(count($product_option_value_id) > 0){
                    $optionValuesIds = $productVariationsSku->getOptionValuesIds($product_option_value_id);

                    if ($optionValuesIds) {
                        $optionValuesIds = array_column($optionValuesIds, 'option_value_id');
                        sort($optionValuesIds);
                        $productVariationsSku->addVariationQuantityByValuesIds(
                            $product['product_id'],
                            $optionValuesIds,
                            $product['quantity']
                        );
                    }
                }
            }
        }
    }
	public function getOrderHistories($order_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1 && $limit != -1) {
			$limit = 10;
		}

        $lang_id = (int) $this->config->get('config_language_id') ?: 1;

		$sql = "SELECT oh.date_added, os.name AS status, os.bk_color AS status_color, oh.comment, oh.notify, oh.order_status_id, oh.user_id, u.username, u.firstname, u.lastname, u.email FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id LEFT JOIN " . DB_PREFIX . "user u ON oh.user_id = u.user_id WHERE oh.order_id = '" . (int)$order_id .
			"' AND os.language_id = '{$lang_id}' ORDER BY oh.order_history_id DESC ";

		if ($limit != -1){
			$sql .=	"LIMIT " .(int) $start . "," . (int) $limit;
		}
		$query = $this->db->query($sql);

		return $query->rows;
	}
	public function getOrderModifications($order_id) {

		$query = $this->db->query("SELECT om.status_id,om.user_id,om.date_added,u.firstname, u.lastname, u.email FROM " . DB_PREFIX . "order_modification om
										LEFT JOIN " . DB_PREFIX . "user u ON om.user_id = u.user_id
										WHERE om.order_id = '" . (int)$order_id . "'
									ORDER BY date_added DESC");

		return $query->rows;
	}
	public function getOrderHistoriesFilter($order_id, $start, $end) {
		$lang_id = (int) $this->config->get('config_language_id') ?: 1;

		$query = "SELECT oh.date_added, os.name AS status, os.bk_color AS status_color, oh.comment, oh.notify, oh.order_status_id, oh.user_id, u.username, u.firstname, u.lastname, u.email FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id LEFT JOIN " . DB_PREFIX . "user u ON oh.user_id = u.user_id WHERE oh.order_id = '" . (int)$order_id . "' " ;
		if ($start !='' && $end != '' ){
			$query .= " AND DATE(oh.date_added) BETWEEN '" . $start ."' AND '" . $end . "'";
		}
		$query.= " AND os.language_id = '{$lang_id}' ORDER BY oh.order_history_id DESC ";
		$result = $this->db->query($query);
		return $result->rows;
	}


	public function getTotalOrderHistories($order_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderHistoriesByOrderStatusId($order_status_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_status_id = '" . (int)$order_status_id . "'");

		return $query->row['total'];
	}

    public function getTelephonesByProductsOrdered($products, $start, $end) {
        $implode = array();

        foreach ($products as $product_id) {
            $implode[] = "op.product_id = '" . $product_id . "'";
        }

        $query = $this->db->query("SELECT DISTINCT firstname, lastname, telephone FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0'");

        return $query->rows;
    }

	public function getEmailsByProductsOrdered($products, $start, $end) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . $product_id . "'";
		}

		$query = $this->db->query("SELECT DISTINCT firstname, lastname, email, telephone FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0'");

		return $query->rows;
	}

	public function getTotalEmailsByProductsOrdered($products) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . $product_id . "'";
		}

		$query = $this->db->query("SELECT DISTINCT email FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0' LIMIT " . $start . "," . $end);

		return $query->row['total'];
	}

    /**
     * Get abandoned orders
     */
    public function getAbandonedCartOrders($data = [], $filterData = [])
    {
        $query = $fields = $statusQuery = [];

        $fields = [
        	'o.order_id',
            'CONCAT(o.firstname, " ", o.lastname) AS customer',
			'FORMAT((select `value` from order_total as o_t where (code like "total" or code = "sub_total") and o_t.order_id = o.order_id order by code desc limit 1),2) as total',
			'o.date_added',
			'o.date_modified',
			'o.customer_id',
			'o.email',
			'o.telephone'
        ];

        $fields =implode(',', $fields);

        $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'order` o';
        $query[] = 'WHERE o.order_status_id = "0"';

        $total = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as td_col', implode(' ', $query))
            .') AS t'
        )->row['totalData'];

        if (isset($filterData['search'])) {

            $filterData['search'] = $this->db->escape($filterData['search']);

            $query[] = 'AND (';

            if (trim($filterData['search'][0]) == '#') {
                $order_id = preg_replace('/\#/', '', $filterData['search']);
                $query[] = "o.order_id LIKE '%{$order_id}%'";
            } else {
                $query[] = "o.order_id LIKE '%{$filterData['search']}%'";
                $query[] = 'OR CONCAT(o.firstname, " ", o.lastname) LIKE "%' . $filterData['search'] . '%"';
				$query[] = 'OR email LIKE "%' . $filterData['search'] . '%"';
            }
            $query[] = ')';
        }

        if (isset($filterData['date_added'])) {

            $startDate = null;
            $endDate = null;
            if (isset($filterData['date_added']['start']) && isset($filterData['date_added']['end'])) {
                $startDate = strtotime($filterData['date_added']['start']);
				$endDate = strtotime($filterData['date_added']['end']);
            }

            if (($startDate && $endDate) && $endDate > $startDate) {

                $formattedStartDate = date('Y-m-d', $startDate);
                $formattedEndDate = date('Y-m-d', $endDate);

                $query[] = 'AND (date(date_added) BETWEEN "' . $formattedStartDate . '" AND "' . $formattedEndDate . '")';
            } elseif(($startDate && $endDate) && $endDate == $startDate) {
                $formattedStartDate = date('Y-m-d', $startDate);

                $query[] = 'AND (date(date_added) ="' . $formattedStartDate . '")';
            }
        }
        $totalFiltered = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as td_col', implode(' ', $query))
            .') AS t'
        )->row['totalData'];

        $sort_data = array(
            'order_id',
            'customer',
            'status',
            'date_added',
            'date_modified',
            'total'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $query[] = "ORDER BY " . $data['sort'];
        } else {
            $query[] = "ORDER BY o.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'desc')) {
            $query[] = "DESC";
        } else {
            $query[] = "ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] > 0) {
                $query[] = "LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
        }

        $query = $this->db->query(implode(' ', $query));

        return [
        	'data' => $query->rows,
			'total' => $total,
			'totalFiltered' => $totalFiltered
		];
    }

    public function getTotalAbandonedCartOrders() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id = 0");
        return $query->row['total'];
    }

    /**
     * Gets the order list after applying the filter on the dataset.
	 *
	 * @param array $data
	 * @param array $filterData
	 * @param int $abandoned
     *
     * @return array|bool
     */
	public function getOrdersToFilter($data = [], $filterData = [], $abandoned = false)
	{
		$query = $fields = $statusQuery = [];

        $language_id = $this->config->get('config_language_id') ?: 1;

        /*$statusQuery[] = 'SELECT CONCAT(os.name,\'_\',os.bk_color) FROM ' . DB_PREFIX . 'order_status os';
        $statusQuery[] = 'WHERE os.order_status_id = o.order_status_id AND os.language_id = "' . $language_id . '"';*/

        $fields = [
        	'o.order_id',
            'o.payment_telephone',
        	'o.order_status_id',
            'CONCAT(o.firstname, " ", o.lastname) AS customer',
            'CONCAT_WS(" - ", IF(o.shipping_address_1 != \'\', o.shipping_address_1, NULL), IF(o.shipping_zone != \'\', o.shipping_zone, NULL),IF(o .shipping_country != \'\', o .shipping_country, NULL)) as address',
            'CONCAT_WS(" - ", IF(o.payment_address_1 != \'\', o.payment_address_1, NULL), IF(o.payment_zone != \'\', o.payment_zone, NULL), IF(o.payment_country != \'\', o.payment_country, NULL)) as payment_address',
            'o.payment_country',
            'o.payment_zone',
            'o.payment_city',
            'o.payment_address_1',
            'o.shipping_method',
            'o.payment_method',
			'o.payment_code',
			'FORMAT((select `value` from order_total as o_t where (code like "total" or code = "sub_total") and o_t.order_id = o.order_id order by code desc limit 1),2) as total',
			'o.currency_code',
			'o.currency_value',
			'o.date_added',
			'o.date_modified',
			'o.customer_id',
			'o.gift_product',
			'o.email',
            'o.archived',
            'o.fax',
			'(SELECT `approved` FROM customer as cu where cu.customer_id=o.customer_id) as customer_status',
			'os.name AS status',
			'os.bk_color AS status_color',
			'(SELECT `transaction_id` FROM payment_transactions as pt where pt.order_id=o.order_id order by pt.id DESC limit 1) as transaction_id',
			'c.iso_code_2 AS country_code'
        ];
        
       


        if(isset($data['select_extra_fields']) && is_array($data['select_extra_fields']) && count($data['select_extra_fields'])){
            $fields = array_merge($fields, $data['select_extra_fields']);
        }

        if (isset($data['wkpos']) && $data['wkpos'] == 1) {
           array_push($fields, 'po.user_order_id as pos_order_id');
        }


		if (isset($filterData['status_comment'])) {
			array_push($fields, '(SELECT `comment` FROM order_history as oh where oh.order_id = o.order_id and oh.order_status_id = o.order_status_id order by order_history_id DESC limit 1) as status_comment');
		}
        if(\Extension::isInstalled('order_assignee') &&$this->config->get('order_assignee')['status']==1)
        {
            $userAssigneeID='(SELECT `user_id` FROM order_assignee as orderAs where orderAs.order_id=o.order_id)';
            array_push($fields, ''.$userAssigneeID.' as order_assignee_id');
            array_push($fields, '(SELECT CONCAT(firstname, " ",lastname) FROM user as usertbl where '.$userAssigneeID.'=usertbl.user_id) as order_assignee_name');
        }

		$delivery_slot = false;
        if (isset($data['delivery_slot']) && $data['delivery_slot'] == 1) {
            array_push($fields, 'dso.slot_description as ds_time');
            array_push($fields, 'dso.delivery_date as ds_date');
			$delivery_slot = true;
        }
        if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)&&$data['trips'] == 1)
        {
            array_push($fields, 'order_pro.quantity as seats_num , order_pro.name as trip_name');
            array_push($fields, 'trips_pro.product_id as product_id,trips_pro.from_date as from_date,trips_pro.to_date as to_date,trips_pro.destination_point as destination');
           if($data['canceled_trips'] ==1 ) array_push($fields, 'trips_o.isRiderCancelTrip as riderCancelTrip, trips_o.isDriverCancelTrip as driverCancelTrip');
            
        }

		$paid_to_merchant = false;
		if (isset($filterData['paid_to_merchant']) && $filterData['paid_to_merchant'] != '') {
			array_push($fields, 'po.paid_to_merchant');
			array_push($fields, 'po.order_id as po_order_id');
			$paid_to_merchant = true;
		}

        //warehouses | new changes v.21.05.2020
		if (isset($data['warehouses']) && $data['warehouses'] == 1 && $this->db->check(['order_to_warehouse'], 'table') && $this->db->check(['order_to_warehouse' => ['data']], 'column')) {
			$warehouses = true;
			array_push($fields, 'wr.data as wrs_data', 'wr.warehouses_list as wrs_list');
		}

        $fields =implode(',', $fields);

        $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'order` o';
        $query[] = 'LEFT JOIN ' . DB_PREFIX . 'order_status os ON (os.order_status_id = o.order_status_id)';
        $query[] = 'LEFT JOIN ' . DB_PREFIX . 'country c ON (c.country_id = o.payment_country_id)';
        if(\Extension::isInstalled('order_assignee') &&$this->config->get('order_assignee')['status']==1)
        {
        $query[] = 'LEFT JOIN ' . DB_PREFIX . 'order_assignee orderAs ON (orderAs.order_id = o.order_id)';
        }

        /*if (isset($filterData['ds_day']) && isset($filterData['ds_time'])) {
            if (!empty($filterData['ds_day']) && count($filterData['ds_time']) > 0)
            $query[] = 'LEFT JOIN ' . DB_PREFIX . 'ds_delivery_slot_order dso ON (o.order_id = dso.order_id)';
        }*/

        if (isset($data['wkpos']) && $data['wkpos'] == 1) {
            $query[] = 'LEFT JOIN `' . DB_PREFIX . 'wkpos_user_orders` po ON (o.order_id = po.order_id)';
        }
        if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)&&$data['trips'] == 1)
        {
            $query[]= "LEFT JOIN " . DB_PREFIX . " order_product order_pro ON (order_pro.order_id = o.order_id)";
            $query[]= "RIGHT JOIN " . DB_PREFIX . " trips_product trips_pro ON (trips_pro.product_id = order_pro.product_id)";
            if($data['canceled_trips'] ==1) {$query[]= "RIGHT JOIN " . DB_PREFIX . " 	trips_orders trips_o ON (trips_o.order_id = o.order_id)";}
           
            
        }

        if ($delivery_slot) {
            $query[] = 'LEFT JOIN `' . DB_PREFIX . 'ds_delivery_slot_order` dso ON (o.order_id = dso.order_id)';
        }

        if ($paid_to_merchant) {
            $query[] = 'LEFT JOIN `' . DB_PREFIX . 'provider_orders` po ON (o.order_id = po.order_id)';
        }

        //warehouses | new changes v.21.05.2020
		if (isset($warehouses) && $warehouses) {
			$query[] = 'LEFT JOIN `' . DB_PREFIX . 'order_to_warehouse` wr ON (o.order_id = wr.order_id)';
		}

        if ($abandoned == false) {
        	$query[] = 'WHERE o.order_status_id > "0"';
		} else {
        	$query[] = 'WHERE o.order_status_id = "0"';
		}
        if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)&&$data['trips'] == 1&&$data['canceled_trips'] !=1)
        {
            $query[] = " AND o.order_id NOT IN ( SELECT order_id FROM trips_orders WHERE order_id IS NOT NULL)";
        }

		$query[] = 'AND os.language_id = "' . $language_id . '"';

        // Handle archived
        if (
            isset($filterData['archived_orders']) && $filterData['archived_orders'] == 'on'
        ) {
            $query[] = 'AND o.archived = 1';
        }else{
            $query[] = 'AND o.archived = 0';
        }

		if ($paid_to_merchant) {
			if($filterData['paid_to_merchant'] == 'yes')
				$query[] = 'AND po.provider_name = "expandship" AND po.paid_to_merchant IS NOT NULL';
			else
				$query[] = 'AND po.provider_name = "expandship" AND po.paid_to_merchant IS NULL';
		}

        if (isset($filterData['cities']) && is_array($filterData['cities'])) {
            $citiesQuery = [];
            foreach ($filterData['cities'] as $city) {
                $citiesQuery[] = 'o.payment_city LIKE "%'.$city.'%"';
            }
            $query[] = sprintf('AND (%s)', implode(' OR ', $citiesQuery));
        }

        if (
            isset($data['wkpos']) &&
            $data['wkpos'] == 1 &&
            isset($filterData['outlet_id']) &&
            is_array($filterData['outlet_id'])
        ) {
            $outletQuery = [
                'SELECT * FROM %s wko LEFT JOIN %s wku ON wko.outlet_id = wku.outlet_id',
                'LEFT JOIN %s wkuo ON wkuo.user_id = wku.user_id',
                'WHERE o.order_id = wkuo.order_id and wko.outlet_id in (%s)'
            ];
            $outletQuery = vsprintf(implode(' ', $outletQuery), [
                'wkpos_outlet',
                POS_USERS_TABLE,
                'wkpos_user_orders',
                implode(',', $filterData['outlet_id'])
            ]);
            $query[] = sprintf('AND EXISTS (%s)', $outletQuery);
        }

        $total = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
            .') AS t'
        )->row['totalData'];

        if (isset($filterData['search']) && $filterData['search'] !== false ) {

            $filterData['search'] = $this->db->escape($filterData['search']);

            $query[] = 'AND (';

            if (trim($filterData['search'][0]) == '#') {
                $order_id = preg_replace('/\#/', '', $filterData['search']);
                $query[] = "o.order_id LIKE '%{$order_id}%'";
            } else {
                $query[] = "o.order_id LIKE '%{$filterData['search']}%'";
                $query[] = 'OR CONCAT(o.firstname, " ", o.lastname) LIKE "%' . $filterData['search'] . '%"';
                $query[] = 'OR CONCAT(o.shipping_address_1,",",o.shipping_city,",",o.shipping_country,",",o.shipping_zone) LIKE "%' . $filterData['search'] . '%"';
				$query[] = 'OR CONCAT(o.payment_address_1,",",o.payment_city,",",o.payment_country,",",o.payment_zone) LIKE "%' . $filterData['search'] . '%"';
				$query[] = 'OR email LIKE "%' . $filterData['search'] . '%"';
				$query[] = 'OR fax LIKE "%' . $filterData['search'] . '%"';
                $query[] = "OR o.payment_telephone LIKE '%{$filterData['search']}%'";
            }
            $query[] = ')';
        }

        if (isset($filterData['order_status_id']) && count($filterData['order_status_id']) > 0) {

            $statusesIds = implode(', ', $this->filterArrayOfIds($filterData['order_status_id']));

            $query[] = 'AND (o.order_status_id IN (' . $statusesIds . '))';
        }
        if (isset($filterData['order_assignee_id']) && count($filterData['order_assignee_id']) > 0) {

            $order_assignee_Ids = implode(', ', $this->filterArrayOfIds($filterData['order_assignee_id']));

            $query[] = 'AND (orderAs.user_id IN (' .$this->db->escape($order_assignee_Ids). '))';
        }

        if (isset($filterData['customer_id']) && count($filterData['customer_id']) > 0) {

            $customersIds = implode(', ', $this->filterArrayOfIds($filterData['customer_id']));

            $query[] = 'AND (customer_id IN (' . $customersIds . '))';
        }

        if (isset($filterData['product_id']) && count($filterData['product_id']) > 0) {

            $productIds = implode(', ', $this->filterArrayOfIds($filterData['product_id']));

            $orderProductQuery = 'SELECT order_id FROM order_product WHERE product_id IN (' . $productIds . ')';

            $query[] = 'AND (o.order_id IN (' . $orderProductQuery . '))';
        }

        //Warehouses filter | new changes v.21.05.2020
		if (isset($filterData['warehouse_id']) && count($filterData['warehouse_id']) > 0) {
			$warehouseIds = implode(', ', $this->filterArrayOfIds($filterData['warehouse_id']));
			$query[] = 'AND JSON_CONTAINS(wr.warehouses_list, \'['.$warehouseIds.']\')';
		}

        if (isset($filterData['country_id']) && count($filterData['country_id']) > 0) {

            $countriesIds = implode(', ', $this->filterArrayOfIds($filterData['country_id']));

            $query[] = vsprintf('AND (shipping_country_id IN (%s) OR payment_country_id IN (%s))', [
                $countriesIds,
                $countriesIds
            ]);
        }

        //////// Delivery slot filter
        /*if (isset($filterData['ds_day']) && isset($filterData['ds_time']) && $delivery_slot) {
            if (!empty($filterData['ds_day']) && count($filterData['ds_time']) > 0) {
                $times = implode(',', $filterData['ds_time']);
                $query[] = "AND (dso.ds_day_id = {$filterData['ds_day']} AND ds_delivery_slot_id IN($times))";
            }
        }*/
		if(isset($filterData['delivery_slot_date']) && $delivery_slot){
			$ds_startDate = null;
			$ds_endDate = null;
			if (isset($filterData['delivery_slot_date']['start']) && isset($filterData['delivery_slot_date']['end'])) {
				$ds_startDate = strtotime($filterData['delivery_slot_date']['start']);
				$ds_endDate = strtotime($filterData['delivery_slot_date']['end']);
			}

			if (($ds_startDate && $ds_endDate) && $ds_endDate > $ds_startDate) {

				$ds_formattedStartDate = date('m-d-Y', $ds_startDate);
				$ds_formattedEndDate = date('m-d-Y', $ds_endDate);

				$query[] = 'AND (dso.delivery_date BETWEEN "' . $ds_formattedStartDate . '" AND "' . $ds_formattedEndDate . '")';
			} elseif(($ds_startDate && $ds_endDate) && $ds_endDate == $ds_startDate) {
				$ds_formattedEndDate = date('m-d-Y', $ds_startDate);

				$query[] = 'AND (dso.delivery_date ="' . $ds_formattedEndDate . '")';
			}
		}
		///////////

        if (isset($filterData['zone_id']) && count($filterData['zone_id']) > 0) {

            $zonesIds = implode(', ', $this->filterArrayOfIds($filterData['zone_id']));

            $query[] = vsprintf('AND (shipping_zone_id IN (%s) OR payment_zone_id IN (%s))', [
                $zonesIds,
                $zonesIds
            ]);
        }

        if (
        	isset($filterData['unhandled_orders']['status']) &&
			$filterData['unhandled_orders']['status'] == 'unhandled'
		) {
            
            $orderHistoryQuery = [];

            $orderHistoryQuery[] = 'SELECT `order`.order_id FROM `order`';
            $orderHistoryQuery[] = 'LEFT JOIN order_history ON `order`.order_id = order_history.order_id';
            $orderHistoryQuery[] = 'where `order`.order_status_id != 0 AND archived = 0';
            if ($this->config->get('wkpos_status')) {
                $orderHistoryQuery[] = 'AND po.user_order_id is null';
            }
            $orderHistoryQuery[] = 'GROUP by `order`.`order_id` HAVING count(`order_history`.`order_history_id`) in (0)';
            $query[] = 'AND (o.order_id IN (' . implode(' ', $orderHistoryQuery) . '))';
        }

        if (isset($filterData['ranges']) && count($filterData['ranges']) > 0) {

            $ranges = $filterData['ranges'];

            if (isset($ranges['total'])) {
                $price = $ranges['total'];

                if (((float)$price['min'] > 0 || (float)$price['max'] > 0) && $price['min'] != $price['max']) {
                    $query[] = 'AND ((total >= ' . $price['min'] . ') AND (total <= ' . $price['max'] . '))';
                }elseif ((float)$price['min'] == (float)$price['max']) {
					$query[] = 'AND ((total = ' . $price['max'] . '))';
				}
            }

            unset($ranges);
        }
        if (isset($filterData['date_added'])) {

            $startDate = null;
            $endDate = null;
            if (isset($filterData['date_added']['start']) && isset($filterData['date_added']['end'])) {
                $startDate = strtotime($filterData['date_added']['start']);
				$endDate = strtotime($filterData['date_added']['end']);
            }

            if (($startDate && $endDate) && $endDate > $startDate) {

                $formattedStartDate = date('Y-m-d', $startDate);
                $formattedEndDate = date('Y-m-d', $endDate);

                $query[] = 'AND (date(date_added) BETWEEN "' . $formattedStartDate . '" AND "' . $formattedEndDate . '")';
            } elseif(($startDate && $endDate) && $endDate == $startDate) {
                $formattedStartDate = date('Y-m-d', $startDate);

                $query[] = 'AND (date(date_added) ="' . $formattedStartDate . '")';
            }
        }
        $totalFiltered = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
            .') AS t'
        )->row['totalData'];

        $sort_data = array(
            'order_id',
            'customer',
            'status',
            'date_added',
            'date_modified',
            'total',
            'ds_date'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $query[] = "ORDER BY " . $data['sort'];
        } else {
            $query[] = "ORDER BY o.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'desc')) {
            $query[] = "DESC";
        } else {
            $query[] = "ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] <= 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] > 0) {
                $query[] = "LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
        }
        $query = $this->db->query(implode(' ', $query));

        return [
        	'data' => $query->rows,
			'total' => $total,
			'totalFiltered' => $totalFiltered
		];
	}

	/**
	 * get orders By customer Email
	 * @param $customerEmail
	 * @param bool $abandoned
	 * @return bool
	 */
	public function getOrdersByCustomerEmail($customerEmail , $abandoned = false){
		$query = 'SELECT * FROM '. DB_PREFIX. '`order` as o WHERE o.email="'.$customerEmail.'" ';
		if ($abandoned == false) {
			$query .= 'AND o.order_status_id > "0"';
		} else {
			$query .= 'AND o.order_status_id = "0"';
		}

        //Exclude archived orders
        $query .= ' AND archived = 0';

		$result = $this->db->query($query);
		if($result){
			return $result->rows;
		}
		return false;
	}

	/**
	 * Update order Fields by Order ID
	 * @param number $order_id
	 * @param array $fields
	 * @return bool
	 */
	public function updateOrderFields($order_id, $fields)
	{

		$orderData = $this->db->query('SELECT * FROM ' . DB_PREFIX . '`order` as o WHERE o.order_id="' . $order_id . '"');
		if ($orderData) {
			$tableCols = array_keys($orderData->row);
			$data = '';
			foreach ($fields as $key => $value) {
				// check if the colums is exist before set in the query
				if (in_array($key, $tableCols)) {
					$data .= ','. $key .'="'. $value .'"';
				}
			}
			$query = 'UPDATE ' . DB_PREFIX . '`order` as o SET ' . ltrim($data,',') . ' WHERE o.order_id="' . $order_id . '" ';
			$result = $this->db->query($query);
			return $result->rows;
		}
		return false;
	}
	/**
	 * Gets the minimum and maximum total for all orders.
	 *
	 * @return array|bool
	 */
	public function getOrderMinMaxTotal()
	{
        $fields = 'MIN(`total`) as _min, MAX(`total`) as _max';

        $queryString = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'order` WHERE order_status_id > "0" ';

        $data = $this->db->query($queryString);

        if ($data->num_rows) {
            return [
                'min' => (float)$data->row['_min'],
                'max' => (float)$data->row['_max'],
            ];
        }

        return false;
	}

    /**
     * Get all orders products.
     *
     * @return array|bool
     */
	public function getAllOrdersProducts()
	{
		$query = [];
		$fields = 'product_id, MAX(name) as product_name';
		$query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product';
		$query[] = 'GROUP BY product_id';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
        	return $data->rows;
		}

		return false;
	}

    public function getCustomerOrdersForCustomerProfile( $customer_id, $limit = false )
    {
        $fields[] = 'o.*';
        $fields[] = 'os.name AS status';

        $queryString[] = 'SELECT ' . implode(', ', $fields) . ' FROM `' . DB_PREFIX . 'order` AS o';
        $queryString[] = 'INNER JOIN `' . DB_PREFIX . 'order_status` AS os';
        $queryString[] = 'ON os.order_status_id=o.order_status_id';
        $queryString[] = 'AND os.language_id = "' . $this->config->get('config_language_id') . '"';
        $queryString[] = "WHERE o.customer_id = '{$customer_id}'";

        if ( $limit )
        {
            $queryString[] = 'LIMIT ' . $limit;
        }

        $orders_query = $this->db->query(implode(' ', $queryString));

        return $orders_query->num_rows > 0 ? $orders_query->rows : false;
    }

    /**
     * Gets a specific customer orders using the customer id.
     *
	 * @param int $customerId
	 * @param int $limit
	 *
     * @return array|bool
     */
	public function getCustomerOrders($customerId, $limit = false)
    {
        $queryString = $fields = [];

        $fields[] = 'o.*';
        $fields[] = 'op.*';
        $fields[] = 'p.image';
        $fields[] = 'os.name AS status';

        $queryString[] = 'SELECT ' . implode(', ', $fields) . ' FROM `' . DB_PREFIX . 'order` AS o';
        $queryString[] = 'INNER JOIN `' . DB_PREFIX . 'order_product` AS op';
        $queryString[] = 'ON o.order_id=op.order_id';
        $queryString[] = 'INNER JOIN `' . DB_PREFIX . 'product` AS p';
        $queryString[] = 'ON p.product_id=op.product_id';
        $queryString[] = 'INNER JOIN `' . DB_PREFIX . 'order_status` AS os';
        $queryString[] = 'ON os.order_status_id=o.order_status_id';
        $queryString[] = 'AND os.language_id = "' . $this->config->get('config_language_id') . '"';
        $queryString[] = 'WHERE o.customer_id=' . $customerId;
        $queryString[] = 'AND o.archived = 0';
        $queryString[] = 'ORDER BY o.order_id DESC';

        if ($limit) {
            $queryString[] = 'LIMIT ' . $limit;
        }

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    /**
     *
     *
     */
    public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row['total'];
	}
    /**
     * Validate personal tab fields
	 *
	 * @param array $data
	 *
	 * @return array
	 */
    public function validatePersonal($data, $order = null)
	{
		$errors = [];
		if (mb_strlen($data['firstname']) < 1 || isset($data['firstname']) == false) {
			$errors['firstname'] = 'error in firstname field';
		}

        // if (mb_strlen($data['lastname']) < 1 || isset($data['lastname']) == false) {
        //     $errors['lastname'] = 'error in lastname field';
        // }

        if(
            ($data['register_login_by_phone_number'] && $data['email'] != "") ||
            !$data['register_login_by_phone_number']
        ) {
            if (empty($data['telephone']) && (
                mb_strlen($data['email']) < 1 ||
                isset($data['email']) == false ||
                filter_var($data['email'], FILTER_VALIDATE_EMAIL) == false)
            ) {
                $errors['email'] = 'error in email field';
            }

            if ($order['customer_id'] == $data['customer_id']) {
                if ($this->getTotalCustomersByEmail($data['email']) && $order && $data['email'] != $order['email']) {
    				$errors['email'] = 'email is already exists';
    		  	}
            }
        }

        if (
            strlen($data['telephone']) < 1 ||
            isset($data['telephone']) == false ||
            preg_match('#^[\+0-9]+[0-9]$#', $data['telephone']) == false
        ) {
            $errors['telephone'] = 'error in telephone field';
        }

        if (count($errors) > 0) {
			return [
				'hasErrors' => true,
				'errors' => $errors
			];
		}

		return [
			'hasErrors' => false
		];
	}

    /**
     * Validate payment tab fields
     *
     * @param array $data
     *
     * @return array
     */
    public function validatePayment($data)
    {
        $errors = [];

        if (mb_strlen($data['payment_firstname']) < 1 || isset($data['payment_firstname']) == false) {
            $errors['payment_firstname'] = 'error in payment_firstname field';
        }

        // if (mb_strlen($data['payment_lastname']) < 1 || isset($data['payment_lastname']) == false) {
        //     $errors['payment_lastname'] = 'error in payment_lastname field';
        // }

        if (
            mb_strlen($data['payment_country_id']) < 1 ||
            isset($data['payment_country_id']) == false ||
            $data['payment_country_id'] < 1
        ) {
            $errors['payment_country_id'] = 'error in payment_country_id field';
        }

        // if (mb_strlen($data['payment_address_1']) < 1 || isset($data['payment_address_1']) == false) {
        //     $errors['payment_address_1'] = 'error in payment_address_1 field';
        // }

        // if (mb_strlen($data['payment_city']) < 1 || isset($data['payment_city']) == false) {
        //     $errors['payment_city'] = 'error in payment_city field';
        // }

        if (
            mb_strlen($data['payment_zone_id']) < 1 ||
            isset($data['payment_zone_id']) == false ||
            $data['payment_zone_id'] < 1
        ) {
            $errors['payment_zone_id'] = 'error in payment_zone_id field';
        }

        if (count($errors) > 0) {
            return [
                'hasErrors' => true,
                'errors' => $errors
            ];
        }

        return [
            'hasErrors' => false
        ];
    }

    /**
     * Validate Shipping tab fields
     *
     * @param array $data
     *
     * @return array
     */
    public function validateShipping($data)
    {
        $errors = [];

        if (mb_strlen($data['shipping_firstname']) < 1 || isset($data['shipping_firstname']) == false) {
            $errors['shipping_firstname'] = 'error in shipping_firstname field';
        }

        // if (mb_strlen($data['shipping_lastname']) < 1 || isset($data['shipping_lastname']) == false) {
        //     $errors['shipping_lastname'] = 'error in shipping_lastname field';
        // }

        if (
            mb_strlen($data['shipping_country_id']) < 1 ||
            isset($data['shipping_country_id']) == false ||
            $data['shipping_country_id'] < 1
        ) {
            $errors['shipping_country_id'] = 'error in shipping_country_id field';
        }

        // if (mb_strlen($data['shipping_address_1']) < 1 || isset($data['shipping_address_1']) == false) {
        //     $errors['shipping_address_1'] = 'error in shipping_address_1 field';
        // }

        // if (mb_strlen($data['shipping_city']) < 1 || isset($data['shipping_city']) == false) {
        //     $errors['shipping_city'] = 'error in shipping_city field';
        // }

        if (
            mb_strlen($data['shipping_zone_id']) < 1 ||
            isset($data['shipping_zone_id']) == false ||
            $data['shipping_zone_id'] < 1
        ) {
            $errors['shipping_zone_id'] = 'error in shipping_zone_id field';
        }

        if (count($errors) > 0) {
            return [
                'hasErrors' => true,
                'errors' => $errors
            ];
        }

        return [
            'hasErrors' => false
        ];
    }

    /**
     * Validate Voucher tab fields
     *
     * @param array $data
     *
     * @return array
     */
    public function validateVouchers($data)
    {
        $errors = [];

        if (mb_strlen($data['to_name']) < 1 || isset($data['to_name']) == false) {
            $errors['to_name'] = 'error in to_name field';
        }

        if (
            mb_strlen($data['to_email']) < 1 ||
            isset($data['to_email']) == false ||
            filter_var($data['to_email'], FILTER_VALIDATE_EMAIL) == false
        ) {
            $errors['to_email'] = 'error in to_email field';
        }

        if (mb_strlen($data['from_name']) < 1 || isset($data['from_name']) == false) {
            $errors['from_name'] = 'error in from_name field';
        }

        if (
            mb_strlen($data['from_email']) < 1 ||
            isset($data['from_email']) == false ||
            filter_var($data['from_email'], FILTER_VALIDATE_EMAIL) == false
        ) {
            $errors['from_email'] = 'error in from_email field';
        }

        if ($data['voucher_theme_id'] < 1 || isset($data['voucher_theme_id']) == false) {
            $errors['voucher_theme_id'] = 'error in voucher_theme_id field';
        }

        if ($data['amount'] < 1 || isset($data['amount']) == false) {
            $errors['amount'] = 'error in amount field';
        }

        if (count($errors) > 0) {
            return [
                'hasErrors' => true,
                'errors' => $errors
            ];
        }

        return [
            'hasErrors' => false
        ];
    }

    /**
     * Filter array of ids, this method is targeting filtering only ids.
     *
     * @param array $inputs
     *
     * @return array
     */
    public function filterArrayOfIds($inputs)
    {
        return array_filter($inputs, function ($input) {
            return $this->filterInteger($input);
        });
    }

    /**
     * Filter int input.
     *
     * @param int $input
     *
     * @return array
     */
    public function filterInteger($input)
    {
        return filter_var($input, FILTER_VALIDATE_INT);
    }

    /**
     * Get total order histories by order status ids.
     *
     * @param array $order_status_ids
     *
     * @return array|bool
     */
    public function getTotalOrderHistoriesByOrderStatusIds($order_status_ids)
	{
		$query = [];
		$query[] = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'order_history WHERE';
		$query[] = 'order_status_id IN (' . implode(',', $order_status_ids) . ')';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row['total'];
        }

        return false;
    }

    /**
     * Get abandoned orders by order id.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function getAbandonedOrderByOrderId($orderId)
    {
        $query = [];
        $query[] = 'SELECT * FROM `' . DB_PREFIX . 'order`';
        $query[] = 'WHERE order_id = "' . (int)$this->db->escape($orderId) . '"';
        $query[] = 'AND order_status_id = 0';
        $query[] = 'AND email != "0"';
        $data = $this->db->query(implode(' ', $query));
        if ($data->num_rows) {
            return $data->row;
        }
        return false;
    }

	/**
	 * @param array $productsSKUs
	 * @param int $apiKey
	 * @return array
	 */
	private function _getQoyodProductsIdsBySKUs($productsSKUs ,$apiKey){
		if (count($productsSKUs)==0)
			return [];
		$curl = curl_init();
		//url_example :
		//https://www.qoyod.com/api/2.0/products?q[sku_in][]=634349903068&q[sku_in][]=637614059872
		$url = "https://www.qoyod.com/api/2.0/products?";
		foreach ($productsSKUs as $sku){
			$url .= "q[sku_in][]={$sku}&";
		}
		$url = rtrim($url,'&');
		curl_setopt_array($curl, array(
			CURLOPT_URL =>  $url ,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"API-KEY: $apiKey",
				"Content-Type: application/json"
			),
		));

		$resposeJson = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($resposeJson, true);

		if (isset($result['products']))
			return $result['products'];
		else
			return [];
	}
	private function _getQoyodCustomer($order_id , $apiKey){
		$expandQoyodCustomerPerfix= "Expand_Customer_no_";
		$orderQuery = $this->db->query("SELECT customer_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
		$customerArr = [];
		if($orderQuery->num_rows){
			$OrderArr=$orderQuery->row;
			$customerQuery = $this->db->query("SELECT customer_id,firstname , lastname , email , telephone FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$OrderArr['customer_id'] . "'");
			if($customerQuery->num_rows){
				$customerArr=$customerQuery->row;
			}
		}
		// guest user
		if (count($customerArr) == 0){
			$customerArr = [
				'customer_id'=>"0",
				'email'=>"",
				'telephone'=>"",
			];
		}
		$curl = curl_init();
		$url = "https://www.qoyod.com/api/2.0/customers";
		curl_setopt_array($curl, array(
			CURLOPT_URL =>  $url ,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"API-KEY: $apiKey",
				"Content-Type: application/json"
			),
		));
		$resposeJson = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($resposeJson, true);

		$qoyodCustomer=array_filter($result['customers'] , function ($customer)use($customerArr ,$expandQoyodCustomerPerfix){
			return (
				(!empty($customer['phone_number'])&&$customer['phone_number'] == $customerArr['telephone'])	||
				(!empty($customer['email'])&&$customer['email'] == $customerArr['email'])||
				(!empty($customer['organization'])&&$customer['organization'] == $expandQoyodCustomerPerfix.$customerArr['customer_id'])

			) ;
		});

		if(count($qoyodCustomer)>0)
			return current($qoyodCustomer)["id"];

		// create customer

		$customerCurl = curl_init();
		if (!$customerArr['customer_id']){
			$contact = ['organization'=>$expandQoyodCustomerPerfix.$customerArr['customer_id'] , 'name'=>'Expand Guest'];
		}else{
			$contact = [
				'organization'=>$expandQoyodCustomerPerfix.$customerArr['customer_id'] ,
				'name'=>$customerArr['firstname'].' '.$customerArr['lastname'],
				'phone_number'=>$customerArr['telephone'],
				'email'=>$customerArr['email']
			];
		}
		$customerBody = [
			'contact'=>$contact
		];
		$customerBody=json_encode($customerBody);

		curl_setopt_array($customerCurl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $customerBody,
			CURLOPT_HTTPHEADER => array(
				"API-KEY: $apiKey",
				"Content-Type: application/json"
			),
		));
		$response = curl_exec($customerCurl);
		$res_arr = json_decode($response, true);

		if (!$res_arr['error'] || !$res_arr['errors']){
			return $res_arr['contact']['id'];
		}
		return false;
	}

    /**
     * Qoyod Integration Create Invoice
     */
    public function qoyod_create_invoice($qoyod_invoice_status, $order_id, $order_status_id){

        $this->load->model('module/qoyod_integration');
            if($this->model_module_qoyod_integration->isActive() && !$qoyod_invoice_status){
                $qoyodSettings = $this->model_module_qoyod_integration->getSettings();

                if( $qoyodSettings['contact_id']
                    && $qoyodSettings['api_key']
                    && $qoyodSettings['inventory_id']
                    && in_array($order_status_id, $qoyodSettings['order_statuses'])
                  ){
                    $apiKey = $qoyodSettings['api_key'];
                    $qoyod_product_query = $this->db->query("SELECT op.quantity, op.price, op.tax, p.sku FROM " . DB_PREFIX . "order_product op LEFT JOIN product p ON (op.product_id = p.product_id) WHERE order_id = '" . (int)$order_id . "'");
                    $qoyodProducts = [];
					$productsSKUs=array_column($qoyod_product_query->rows ,'sku');
					$qoyodProductIds=$this->_getQoyodProductsIdsBySKUs($productsSKUs,$apiKey);
					$contact_id=$this->_getQoyodCustomer($order_id , $apiKey);
					if (!$contact_id)
						$contact_id = $qoyodSettings['contact_id'];
                    foreach ($qoyod_product_query->rows as $order_product) {
						$qoyodProductId = $order_product['sku'];
						$key = array_search($order_product['sku'], array_column($qoyodProductIds, 'sku'));
						if ($key !== false){
							$qoyodProductId=$qoyodProductIds[$key]['id'];
							unset($qoyodProductIds[$key]);
							$qoyodProductIds = array_values($qoyodProductIds);
						}
                        $qoyodProducts[] = [
                                                'product_id' => $qoyodProductId,
                                                'quantity' => $order_product['quantity'],
                                                'unit_price' => $order_product['price'],
												'tax_percent' => ((100 * $order_product['tax']) / $order_product['price']),
                                                'inventory_id' => $qoyodSettings['inventory_id']
                                           ];
                    }

                    $qoyodInvArr = [
                                    'invoice' => [
                                                'contact_id' => $contact_id,
                                                'description' => $qoyodSettings['invoice_desc'] .' - Expandcart to qoyod Invoice.',
                                                'issue_date' => date('Y-m-d'),
                                                'due_date' => date('Y-m-d'),
                                                'reference' => 'orderID_'.$order_id,
												'inventory_id' => $qoyodSettings['inventory_id'],
                                                'line_items' => $qoyodProducts
                                                 ]
                                    ];
                    $qoyodInvData = json_encode($qoyodInvArr);
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                      CURLOPT_URL => "https://www.qoyod.com/api/2.0/invoices",
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => "",
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 30,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => "POST",
                      CURLOPT_POSTFIELDS => $qoyodInvData,
                      CURLOPT_HTTPHEADER => array(
                        "API-KEY: $apiKey",
                        "Content-Type: application/json"
                      ),
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                    if ($err) {
                      //return false;
                    } else {
                      //echo $response;
                        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET qoyod_invoice = '1' WHERE order_id = '" . (int)$order_id . "'");;
                    }
                }
            }
    }


    public function getOrderProductById(int $order_id, int $product_id)
    {
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."order_product WHERE `product_id` = '{$product_id}' AND `order_id` = '{$order_id}' LIMIT 1;");

        return $query->row;
    }

    /**
     * Get the product price according to the customer_group_id
     * @param product_id The product id
     * @param customer_group_id The customer group id
     * @return float price according to the relation
     * @author Mohamed Hassan
     */
    public function get_product_price_by_group_id($product_id,$customer_group_id){
            $query = $this->db->query("SELECT price FROM ".DB_PREFIX."product_special WHERE product_id=".(int)$product_id." && customer_group_id=".(int)$customer_group_id."");
            return $query->row['price'];
    }


	public function checkSmsApp(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'smshare'");
		if ($query->num_rows) {
			return TRUE;
		}
		return FALSE;
}
    /**
	 *  get delivery details by product id
	 */

	public function orderDeliveryDetails($order_id){
		$query = $this->db->query("SELECT day_name, delivery_date ,slot_description FROM ".DB_PREFIX." ds_delivery_slot_order dso  LEFT JOIN " . DB_PREFIX . " `order` o ON(dso.order_id= o.order_id) WHERE dso.order_id = ".(int)$order_id."");
		return $query->row;
    }

    /**
     * Hot edit field
     */
    public function hotEdit($data, $order_id) {
        foreach ($data as $column => $value) {
            $this->db->query("UPDATE `".DB_PREFIX."order` SET `".$this->db->escape($column)."` = '".$this->db->escape($value)."' WHERE `order_id` = " . (int) $this->db->escape($order_id));
        }
    }

    /**
     * Exchange reward points to money
     */
    public function exchangePointToMoney($points)
	{
		if ($points < 0) {
			return 0;
		}

		$rate  = explode("/", $this->config->get("currency_exchange_rate"));
		$money = ($points * 1.0 * $rate[1]) / $rate[0];

		return $money;
	}

    /**
     * Updates basic customer info
     *
     * @param int $orderId
     * @param array $info
     *
     * @return bool
     */
    public function updateCustomerInfo($orderId, $info)
    {
        $query = $columns = [];

        $query[] = 'UPDATE `order` SET';

        if (isset($info['firstname'])) {
            $columns[] = sprintf('firstname = "%s"', $this->db->escape($info['firstname']));
        }

        if (isset($info['lastname'])) {
            $columns[] = sprintf('lastname = "%s"', $this->db->escape($info['lastname']));
        }

        if (isset($info['email'])) {
            $columns[] = sprintf('email = "%s"', $this->db->escape($info['email']));
        }

        if (isset($info['telephone'])) {
            $columns[] = sprintf('telephone = "%s"', $this->db->escape($info['telephone']));
        }

        if (!count($columns)) {
            return false;
        }

        $query[] = implode(',', $columns);
        $query[] = 'WHERE order_id = %d';

        try {
            $this->db->query(sprintf(implode(' ', $query), $orderId));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Updates basic customer payment & shipping addresses
     *
     * @param int $orderId
     * @param array $info
     *
     * @return bool
     */
    public function updateCustomerAddresses($orderId, $info)
    {
        $query = $columns = [];

        $query[] = 'UPDATE `order` SET';

        if (isset($info['payment_firstname'])) {
            $columns[] = sprintf('payment_firstname = "%s"', $this->db->escape($info['payment_firstname']));
        }

        if (isset($info['payment_lastname'])) {
            $columns[] = sprintf('payment_lastname = "%s"', $this->db->escape($info['payment_lastname']));
        }

        if (isset($info['payment_telephone'])) {
            $columns[] = sprintf('payment_telephone = "%s"', $this->db->escape($info['payment_telephone']));
        }

        if (isset($info['payment_address_1'])) {
            $columns[] = sprintf('payment_address_1 = "%s"', $this->db->escape($info['payment_address_1']));
        }

        if (isset($info['payment_address_2'])) {
            $columns[] = sprintf('payment_address_2 = "%s"', $this->db->escape($info['payment_address_2']));
        }

        if (isset($info['payment_city'])) {
            $columns[] = sprintf('payment_city = "%s"', $this->db->escape($info['payment_city']));
        }

        if (isset($info['payment_postcode'])) {
            $columns[] = sprintf('payment_postcode = "%s"', $this->db->escape($info['payment_postcode']));
        }

        if (isset($info['payment_country'])) {
            $columns[] = sprintf('payment_country = "%s"', $this->db->escape($info['payment_country']));
        }

        if (isset($info['payment_country_id'])) {
            $columns[] = sprintf('payment_country_id = "%s"', $this->db->escape($info['payment_country_id']));
        }

        if (isset($info['payment_zone'])) {
            $columns[] = sprintf('payment_zone = "%s"', $this->db->escape($info['payment_zone']));
        }

        if (isset($info['payment_zone_id'])) {
            $columns[] = sprintf('payment_zone_id = "%s"', $this->db->escape($info['payment_zone_id']));
        }

        if (isset($info['shipping_firstname'])) {
            $columns[] = sprintf('shipping_firstname = "%s"', $this->db->escape($info['shipping_firstname']));
        }

        if (isset($info['shipping_lastname'])) {
            $columns[] = sprintf('shipping_lastname = "%s"', $this->db->escape($info['shipping_lastname']));
        }

        if (isset($info['shipping_telephone'])) {
            $columns[] = sprintf('shipping_telephone = "%s"', $this->db->escape($info['shipping_telephone']));
        }

        if (isset($info['shipping_address_1'])) {
            $columns[] = sprintf('shipping_address_1 = "%s"', $this->db->escape($info['shipping_address_1']));
        }

        if (isset($info['shipping_address_2'])) {
            $columns[] = sprintf('shipping_address_2 = "%s"', $this->db->escape($info['shipping_address_2']));
        }

        if (isset($info['shipping_city'])) {
            $columns[] = sprintf('shipping_city = "%s"', $this->db->escape($info['shipping_city']));
        }

        if (isset($info['shipping_postcode'])) {
            $columns[] = sprintf('shipping_postcode = "%s"', $this->db->escape($info['shipping_postcode']));
        }

        if (isset($info['shipping_country'])) {
            $columns[] = sprintf('shipping_country = "%s"', $this->db->escape($info['shipping_country']));
        }

        if (isset($info['shipping_country_id'])) {
            $columns[] = sprintf('shipping_country_id = "%s"', $this->db->escape($info['shipping_country_id']));
        }

        if (isset($info['shipping_zone'])) {
            $columns[] = sprintf('shipping_zone = "%s"', $this->db->escape($info['shipping_zone']));
        }

        if (isset($info['shipping_zone_id'])) {
            $columns[] = sprintf('shipping_zone_id = "%s"', $this->db->escape($info['shipping_zone_id']));
        }
        
        if (isset($info['delivery_info'])) {
            $columns[] = sprintf('delivery_info = "%s"', $this->db->escape($info['delivery_info']));
        }

        if (!count($columns)) {
            return false;
        }

        $query[] = implode(',', $columns);
        $query[] = 'WHERE order_id = %d';

        try {
            $this->db->query(sprintf(implode(' ', $query), $orderId));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get manual shipping gateway id by order id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function getManualShippingGatewayId(int $id)
    {
        $data = $this->db->query(sprintf('SELECT * FROM `%s` WHERE order_id = %d', 'order', $id));

        if ($data->num_rows > 0) {

            $isManual = 0;
            if (isset($data->row['manual_shipping_gateway'])) {
                $isManual = $data->row['manual_shipping_gateway'];
            }

            return [
                'id' => $data->row['shipping_gateway_id'],
                'is_manual' => $isManual,
            ];
        }

        return false;
    }

	/**
	 * delete order delivery slot
	 *
	 * @param array $data
	 * @return bool
	 */
	public function deleteSlot($order_id){
		$querySlot = "DELETE FROM " . DB_PREFIX . "ds_delivery_slot_order WHERE order_id = " . (int)$order_id;

		try {
			$this->db->query($querySlot);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

    public function getOrderTotalWeight($order_id){
        $products = $this->getOrderProducts($order_id);

        $total_weight = 0.0;

        foreach($products as $product){
            if($product['weight_class_id'] == 1) //KG
                $total_weight += $product['weight'];
            else{
                $total_weight += $this->weight->convert($product['weight'], $product['weight_class_id'] , 1);
            }
        }
        return $total_weight;
    }

        public function sendEmailOnEditOrderOrder($order_id)
        {
        $order_info = $this->getOrder($order_id);
        $language = new Language($order_info['language_directory'], $this->registry);
        $language->load($order_info['language_filename']);
        $language->load('mail/order');
        $language->load('sale/order');

        $subject = sprintf($language->get('text_subject'), $order_info['store_name'], $order_info['order_id']);

        $message = $language->get('text_order') . ' ' . $order_info['order_id'] . "\n";
        $message .= $language->get('text_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

        if ($order_info['customer_id']) {
            $message .= $language->get('text_link') . "\n";
            $message .= html_entity_decode($order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id'], ENT_QUOTES, 'UTF-8') . "\n\n";
        }

          $orderProducts = $this->getOrderProducts($order_id);

        $message.= $language->get('text_product') .' - '. $language->get('column_quantity').' - '.$language->get('column_price')."\n";

        foreach ($orderProducts as $orderProduct)
        {
            $message.="$orderProduct[name] - $orderProduct[quantity] - $orderProduct[total] \n";
            $totalPrice += $orderProduct['total'];
        }

        $message.= $language->get('cardless_price'). ": $totalPrice \n";

	if ($comment) {
            $message .= $language->get('text_comment') . "\n\n";
            $message .= strip_tags(html_entity_decode($comment, ENT_QUOTES, 'UTF-8')) . "\n\n";
        }
        $message .= $language->get('text_footer');

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
        
        $order_info['store_name'] = (!empty($order_info['store_name'])) ? $order_info['store_name'] : $this->config->get('config_name')[$this->config->get('config_language')];
        
        $mail->setTo($order_info['email']);
        $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') : $this->config->get('config_email')));
        $mail->setSender($order_info['store_name']); //here we check if store name exists else get from config 
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
                $custome_email = true;
                $mail->setBccEmails($cet_result['bcc_emails']);
                $mail->send();
                $mail->sendBccEmails();
            }
        }

        if (!$custome_email) {
            $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
            $mail->send();
        }
        return true;
    }

    public function getOrderProductAddByUserType($order_product_id){
        return $this->db->query("
            SELECT added_by_user_type
            FROM `".DB_PREFIX."order_product`
            WHERE order_product_id=".(int)$order_product_id)->row['added_by_user_type'];
    }

	public function getOrderProductPrice($order_id,$product_id) {
		$query = $this->db->query("SELECT price  FROM " . DB_PREFIX . "order_product WHERE order_id = " . (int)$order_id . " AND product_id =" . (int)$product_id . " LIMIT 1");

		return $query->row['price'];
	}

    public function getShippingCost($order_id){
        $totals = $this->getOrderTotals($order_id);
        foreach ($totals as $key => $total) {
            if( $total['code'] == 'shipping' )
                return $total['value'];
        }
    }

    public function getOrderTaxValue($order_id){
        $totals = $this->getOrderTotals($order_id);
        foreach ($totals as $key => $total) {
            if( $total['code'] == 'tax' )
                return $total['value'];
        }
    }

    private function _checkIfTamaraStatus($order_status_id, $order_id){
        if( \Extension::isInstalled('tamara') ){
            $this->load->model('payment/tamara');
            $tamara_statuses   = $this->config->get('tamara_statuses');
            $tamara_status_ids = array_column($tamara_statuses, 'code', 'expandcartid');

            //if tamara status, then call the tamara api
            if(array_key_exists($order_status_id, $tamara_status_ids)){
                if( $tamara_status_ids[$order_status_id] == 'captured' ){
                    $this->model_payment_tamara->capturePayment($order_id, $this->getOrder($order_id), $this->getShippingCost($order_id), $this->getOrderTaxValue($order_id));       
                }
                elseif ($tamara_status_ids[$order_status_id] == 'canceled') {
                    $this->model_payment_tamara->cancelPayment($order_id, $this->getOrder($order_id), $this->getShippingCost($order_id), $this->getOrderTaxValue($order_id));                       
                }
                elseif ($tamara_status_ids[$order_status_id] == 'refunded') {
                    $this->model_payment_tamara->refundPayment($order_id, $this->getOrder($order_id), $this->getShippingCost($order_id), $this->getOrderTaxValue($order_id));                           
                }
            }
        }
    }

    private function sendJumiaRefundRequest($order_id)
    {
    	if( \Extension::isInstalled('jumiapay') &&
    		$this->config->get('jumiapay')['status'] == '1'
    	) {
            $this->load->model('payment/jumiapay');
            $response = $this->model_payment_jumiapay->refundPayment($order_id);            
        }
    }

    public function getShipmentDetails($orderId) {
        $result = $this->db->query("SELECT * FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId  ");
        return $result->row;
    }

    public function freeInvoice($order_id)
	{
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET payment_trackId = NULL  , date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
	}
    public function confirm($order_id,$order_status_id)
    {
        $data = [
            'notify' => 1,
            'notify_by_sms' => 0,
            'order_status_id' => $order_status_id,
            'comment' => '',
            'date_added' => date('Y-m-d h:i:s'),
        ];
        return $this->addOrderHistory($order_id, $data);
    }

    public function getOrderProductBundles($orderProductId , $orderId , $languageId=false) {
        $query = $fields = [];
        $fields[] = 'order_product_bundle.*';
        $fields[] = 'product_description.name as product_name';
        $fields[] = 'product.image as product_image';

        $fields = implode(',', $fields);
        if($languageId&&is_numeric($languageId))
        	$language = $languageId ;
        else
        	$language = $this->config->get('config_language_id');


        $query[] = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . 'order_product_bundle';
        $query[] = 'LEFT JOIN product_description ON order_product_bundle.bundle_product_id = product_description.product_id';
        $query[] = 'AND product_description.language_id="'.(int)$language.'"';
        $query[] = 'LEFT JOIN product ON order_product_bundle.bundle_product_id = product.product_id AND order_product_bundle.order_id ="' . (int)$orderId . '"';

        $query[] = 'WHERE order_product_id = "' . (int)$orderProductId . '"';
        $data = $this->db->query(implode(' ', $query));
        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }
    public function assignOrder($user_id,$order_id)
    {
        try {
        $getAssignOrderQry = $this->db->query("SELECT * FROM  " . DB_PREFIX . "order_assignee where `order_id` = " . (int) $this->db->escape($order_id)."");
        if ($getAssignOrderQry->num_rows) {
            $this->db->query("UPDATE " . DB_PREFIX . "order_assignee SET user_id = '" . (int)$user_id . "' WHERE order_id = '" . (int)$this->db->escape($order_id) . "'");
        }
        else{
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_assignee SET order_id = '" . (int)$this->db->escape($order_id) . "', user_id = '" . (int)$user_id. "'");
        }
        $this->sendEmailOnAssignOrder($user_id,$order_id);
        return true;
       }catch (\Exception $e) {
        return false;
      }
        
    }
    public function getOrderAssignee($order_id)
    {
        $qry = $this->db->query("SELECT user_id FROM  " . DB_PREFIX . "order_assignee where `order_id` = " . (int) $this->db->escape($order_id)."");
        return $qry->row['user_id'];
    }
    public function sendEmailOnAssignOrder($user_id,$order_id)
    {
        $order_info = $this->getOrder($order_id);
        $language = new Language($order_info['language_directory'], $this->registry);
        $language->load($order_info['language_filename']);
        $language->load('mail/order');
        $language->load('sale/order');
        $this->load->model('user/user');    
        $user_info = $this->model_user_user->getUser($user_id);
       

        $subject = sprintf('Order Assignment |', $order_info['store_name'], $order_info['order_id']);

        $message = $language->get('text_order') . ' ' . $order_info['order_id'] . "\n";
        $message .= $language->get('text_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";
        $message.="Order #". $order_info['order_id']." has been assigned to you, ";
        if ($user_info['user_id']) {
            $message .= $language->get('text_link') . "\n";
            $message .= html_entity_decode($order_info['store_url'] . 'admin/sale/order/info?order_id=' . $order_info['order_id'], ENT_QUOTES, 'UTF-8') . "\n\n";
        }
    
    if ($comment) {
            $message .= $language->get('text_comment') . "\n\n";
            $message .= strip_tags(html_entity_decode($comment, ENT_QUOTES, 'UTF-8')) . "\n\n";
        }
        $message .= $language->get('text_footer');

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
        
        $order_info['store_name'] = $this->config->get('config_name')[$this->config->get('config_language')];
        $mail->setTo($user_info['email']);
        $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') : $this->config->get('config_email')));
        $mail->setSender($order_info['store_name']); //here we check if store name exists else get from config 
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
                $custome_email = true;
                $mail->setBccEmails($cet_result['bcc_emails']);
                $mail->send();
                $mail->sendBccEmails();
            }
        }

        if (!$custome_email) {
            $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
            $mail->send();
        }
        return true;
    }

    /*********** Get Order products category name *************/
	public function getOrderProductsCategoryName($orderId){
		$queryString[] = 'SELECT cd.category_id ,cd.name  FROM `' . DB_PREFIX . 'category_description` as cd';
		$queryString[] = 'INNER JOIN `' . DB_PREFIX . 'product_to_category` AS ptc';
		$queryString[] = 'ON cd.category_id = ptc.category_id';
		$queryString[] = 'INNER JOIN `' . DB_PREFIX . 'order_product` AS op';
		$queryString[] = 'ON ptc.product_id = op.product_id';
		$queryString[] = 'WHERE op.order_id=' . (int)$orderId;
		$queryString[] = 'AND cd.language_id = "' . $this->config->get('config_language_id') . '"';
		$queryString[] = 'GROUP BY cd.category_id';

		$data = $this->db->query(implode(' ', $queryString));

		return $data->num_rows ? $data->rows:[];

	}

	/*********** Get provider order by order id   *************/
	public function getProviderOrder($orderId){

		$result = $this->db->query('SELECT *  FROM `' . DB_PREFIX . 'provider_orders` WHERE order_id='. (int)$orderId);
		return $result->row;

	}

	/*********** Create Provider Order   *************/
	public function createProviderOrder($data=[]){

		$sql = "INSERT INTO " . DB_PREFIX . "provider_orders SET order_id = '" . (int)$data['order_id'] . "', provider_name = '" . $this->db->escape($data['provider_name']) . "', courier_name = '" . $this->db->escape($data['courier_name']) . "'";
		if($data['paid_to_merchant'])
			$sql .= ", paid_to_merchant = '" . $this->db->escape($data['paid_to_merchant']) . "'";
		$this->db->query($sql);

	}

	/*********** Update Provider Order   *************/
	public function updateProviderOrder($id, $data=[]){

		$sql = "UPDATE " . DB_PREFIX . "provider_orders SET courier_name = '" . $this->db->escape($data['courier_name']) . "'";
		if($data['paid_to_merchant'])
			$sql .= ", paid_to_merchant = '" . $this->db->escape($data['paid_to_merchant']) . "'";

		$sql .="WHERE id = '" . (int)$id . "'";
		$this->db->query($sql);

	}

	public function getCustomerLastOrderIdWIthCustomFields($data = array()){
		$order_id = $data['order_id'];
		$result = $this->db->query('SELECT DISTINCT(o.order_id)  FROM `' . DB_PREFIX . 'order` o
	 INNER JOIN `' . DB_PREFIX . 'order_custom_fields` oc ON o.order_id = oc.order_id
	 WHERE o.customer_id='. (int)$data['customer_id'] .' AND o.shipping_country_id = '.$data['shipping_country_id']
			.' AND o.shipping_zone_id ='.$data['shipping_zone_id']." AND oc.value <> '' "
		);

		$order_ids = [];
		foreach ($result->rows as $value){
			$order_ids[] = $value['order_id'] ;
		}

		if(!in_array($data['order_id'],$order_ids)){
			$order_id = $result->row['order_id'];
		}

		return $order_id;
	}


	/**
	 * @param int|null $order_id
	 * @return bool|null
	 */
	public function isOrderFirst(?int $order_id): ?bool
	{
		try {
			$query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "`order` WHERE order_id = '" . (int)$order_id . "'");
		} catch (Exception $e) {
			return false;
		}
		return ($query->row['order_id'] && $query->row['order_id'] == 1);
	}
}
?>
