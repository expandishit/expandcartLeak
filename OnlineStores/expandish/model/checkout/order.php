<?php
use ExpandCart\Foundation\Support\Hubspot;
class ModelCheckoutOrder extends Model {

	public function addOrder($data) {
        $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();

		$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', store_id = '" . (int)$data['store_id'] . "', store_name = '" . $this->db->escape($data['store_name']) . "', store_url = '" . $this->db->escape($data['store_url']) . "', customer_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $this->db->escape($data['payment_company']) . "', payment_company_id = '" . $this->db->escape($data['payment_company_id']) . "', payment_tax_id = '" . $this->db->escape($data['payment_tax_id']) . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($data['payment_country']) . "', payment_country_id = '" . (int)$data['payment_country_id'] . "', payment_zone = '" . $this->db->escape($data['payment_zone']) . "', payment_zone_id = '" . (int)$data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '" . $this->db->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($data['shipping_country']) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '" . $this->db->escape($data['shipping_zone']) . "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', total = '" . (float)$data['total'] . "', affiliate_id = '" . (int)$data['affiliate_id'] . "', commission = '" . (float)$data['commission'] . "', language_id = '" . (int)$data['language_id'] . "', currency_id = '" . (int)$data['currency_id'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', currency_value = '" . (float)$data['currency_value'] . "', ip = '" . $this->db->escape($data['ip']) . "', forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', accept_language = '" . $this->db->escape($data['accept_language']) . "', date_added = '" . $current_date_time . "', date_modified = '" . $current_date_time . "'");

		$order_id = $this->db->getLastId();

		foreach ($data['products'] as $product) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['product_id'] . "', name = '" . $this->db->escape($product['name']) . "', model = '" . $this->db->escape($product['model']) . "', quantity = '" . (int)$product['quantity'] . "', price = '" . (float)$product['price'] . "', total = '" . (float)$product['total'] . "', tax = '" . (float)$product['tax'] . "', reward = '" . (int)$product['reward'] . "'");


			$this->load->model('module/minimum_deposit/settings');
			if ($this->model_module_minimum_deposit_settings->isActive()) {

				if (isset($product['main_price'])){
					$this->db->query("UPDATE  " . DB_PREFIX . "order_product SET main_price = '" . (float)$product['main_price']  . "' WHERE  product_id = '" . (int)$product['product_id'] . "' AND  order_id = '" . (int)$order_id. "'");
				}
				if (isset($product['remaining_amount'])){
					$this->db->query("UPDATE  " . DB_PREFIX . "order_product SET remaining_amount = '" . (float)$product['remaining_amount']  . "' WHERE  product_id = '" . (int)$product['product_id'] . "' AND  order_id = '" . (int)$order_id. "'");
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

		foreach ($data['vouchers'] as $voucher) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_voucher SET order_id = '" . (int)$order_id . "', description = '" . $this->db->escape($voucher['description']) . "', code = '" . $this->db->escape($voucher['code']) . "', from_name = '" . $this->db->escape($voucher['from_name']) . "', from_email = '" . $this->db->escape($voucher['from_email']) . "', to_name = '" . $this->db->escape($voucher['to_name']) . "', to_email = '" . $this->db->escape($voucher['to_email']) . "', voucher_theme_id = '" . (int)$voucher['voucher_theme_id'] . "', message = '" . $this->db->escape($voucher['message']) . "', amount = '" . (float)$voucher['amount'] . "'");
		}

        foreach ($data['totals'] as $total) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', text = '" . $this->db->escape($total['text']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
		}

		$store_statistics = new StoreStatistics();
        $store_statistics->store_statistcs_push('orders', 'create', [
        	'order_id' => $order_id,
        	'customer_id' => (int)$data['customer_id'],
        	'total' => (float)$data['total'],
        	'currency_code' => $data['currency_code'],
        	'currency_value' => (float)$data['currency_value']
        ]);

        return $order_id;
    }

	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT *, (SELECT os.name FROM `" . DB_PREFIX . "order_status` os WHERE os.order_status_id = o.order_status_id AND os.language_id = o.language_id) AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
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
				$shipping_phonecode = $country_query->row['phonecode'];
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

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'fax'                     => $order_query->row['fax'],
				'email'                   => $order_query->row['email'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_company_id'      => $order_query->row['payment_company_id'],
				'payment_tax_id'          => $order_query->row['payment_tax_id'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_telephone'       => $order_query->row['payment_telephone'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_address_location'=> $order_query->row['payment_address_location'] ? $order_query->row['payment_address_location'] : '',
				'shipping_address_location'=> $order_query->row['shipping_address_location'] ? $order_query->row['shipping_address_location'] : '',
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_phonecode'       => $payment_phonecode,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
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
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_phonecode'      => !empty($shipping_phonecode) ? $shipping_phonecode : null,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'shipping_code'           => $order_query->row['shipping_code'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'order_status'            => $order_query->row['order_status'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'language_filename'       => $language_filename,
				'language_directory'      => $language_directory,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'payment_trackId'              => $order_query->row['payment_trackId'],

			);
		} else {
			return false;
		}
	}

	//// Add dropna order
	public function dropaSaveOrder($products_syncData, $order_info){
		$this->load->model('account/api');
        $dropnaClient = $this->model_account_api->getDropnaClient();
        if(!$dropnaClient){
            return false;
        }/////////////////

        $order_total_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_info['order_id'] . "' ORDER BY sort_order ASC");

        $dropnaData['order_total']     = $order_total_query->rows;
        $dropnaData['order_info']      = $order_info;
        $dropnaData['order_products']  = $products_syncData;

        $dropnaData['apikey']          = DROPNA_APIKEY;
        $dropnaData['client_api_id']   = $dropnaClient['id'];
        $dropnaData['store_code']      = $dropnaClient['store_code'];
        $dropnaData['client_id']       = $dropnaClient['client_id'];
        $dropnaData['store_to_dropna'] = 1;
        // print_r($dropnaData);
        // exit();
		$soap_do     = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, DROPNA_DOMAIN."api/v1/order");
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, http_build_query($dropnaData));
        // curl_setopt($soap_do, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($soap_do);
        $responseArr = json_decode($response, true);
        $err = curl_error();
        curl_close();

        if($responseArr['status'] == 'success'){
        	$dropna_order_ids = $responseArr['orders_ids'];
        	$count_order_ids = count($dropna_order_ids);
        	for ($i=0; $i < $count_order_ids; $i++) {
        		$this->db->query("INSERT INTO " . DB_PREFIX . "order_to_dropna SET order_id = '" . (int)$order_info['order_id'] . "', dropna_order_id = '" . (int)$dropna_order_ids[$i] . "'");
        	}
        }
	}

	public function confirm($order_id, $order_status_id, $comment = '', $notify = false){

			
			//Dropna Settings
			 $dropna_settings = $this->config->get('dropna');

			// $order_product_query = $this->db->query("SELECT op.*, ptd.dropna_product_id FROM " . DB_PREFIX . "order_product op LEFT JOIN product_to_dropna ptd ON (op.product_id = ptd.product_id) WHERE op.order_id = '" . (int)$order_id . "'");

			// $productVariationsSku = $this->load->model('module/product_variations', ['return' => true]);

			// $products_syncData = [];

			// foreach ($order_product_query->rows as $order_product) {
			// 	//$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");
			// 	//$products_syncData[$order_product['product_id']] = array();
			   //              $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

			   //              $product_rest = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$order_product['product_id'] . "'");

			   //              if($order_product['dropna_product_id'])
			   //              	$products_syncData[$order_product['product_id']] = [ 'quantity' => $product_rest->row['quantity'], 'dropna_product_id' => $order_product['dropna_product_id'] ];

			   //              if($queryMultiseller->num_rows) {
			// 		// Check if the product is attached to seller or not

			// 		$product_sellers = $this->db->query("SELECT * FROM " . DB_PREFIX . "ms_product WHERE product_id = ".(int)$order_product['product_id']);
			// 		if($product_sellers->num_rows){
			// 			if ($this->config->get('msconf_disable_product_after_quantity_depleted')) {
			// 				if ((int)$product_rest->row['quantity'] <= 0) {
			// 					//$this->MsLoader->MsProduct->changeStatus((int)$order_product['product_id'], MsProduct::STATUS_DISABLED);
			// 					//$this->MsLoader->MsProduct->disapprove((int)$order_product['product_id']);
			// 				}
			// 			}
			// 		}

		   //              }

					// 	$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product['order_product_id'] . "'");
					// 	$arr_order_options[$order_product['product_id']] = $order_option_query->rows;

		   //              if ($productVariationsSku->isActive()) {
		   //                  $product_option_value_id = array_column($order_option_query->rows, 'product_option_value_id');
		   //                  if (count($product_option_value_id) > 0) {
		   //                      $optionValuesIds = $productVariationsSku->getOptionValuesIds($product_option_value_id);
		   //                      if ($optionValuesIds) {
		   //                          $optionValuesIds = array_column($optionValuesIds, 'option_value_id');
		   //                          sort($optionValuesIds);
		   //                          // $productVariationsSku->updateVariationQuantityByValuesIds(
		   //                          //     $order_product['product_id'],
		   //                          //     $optionValuesIds,
		   //                          //     $order_product['quantity']
		   //                          // );
		   //                      }
		   //                  }
		   //              }

			// 	foreach ($order_option_query->rows as $option) {
			// 		if($order_product['dropna_product_id']){
			// 			$product_opt_val_rest = $this->db->query("SELECT pov.quantity, povr.dropna_pr_op_val_id FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN pr_op_val_to_dropna povr ON (pov.product_option_value_id = povr.product_option_value_id) WHERE pov.product_option_value_id = '" . (int)$option['product_option_value_id'] . "'");

			// 			$products_syncData[$order_product['product_id']]['opt_vals'][] = [ 'id' => $option['product_option_value_id'], 'quantity' => $product_opt_val_rest->row['quantity'], 'dropna_val_id' => $product_opt_val_rest->row['dropna_pr_op_val_id'] ];
			// 		}

			// 		//$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
			// 	}
			// }
			// $this->dropaSyncQuantity($products_syncData);
			// exit();

        $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
		$order_info = $this->getOrder($order_id);
		$orderDataInvoiceFormatted = $order_info;

		if ($order_info) {

			$is_multiseller = \Extension::isInstalled('multiseller') ?? 0;

			// Fraud Detection
			if ($this->config->get('config_fraud_detection')) {
				$this->load->model('checkout/fraud');

				$risk_score = $this->model_checkout_fraud->getFraudScore($order_info);

				if ($risk_score > $this->config->get('config_fraud_score')) {
					$order_status_id = $this->config->get('config_fraud_status_id');
				}
			}

			// Ban IP
			$status = false;

			$this->load->model('account/customer');

			if ($order_info['customer_id']) {
				$results = $this->model_account_customer->getIps($order_info['customer_id']);

				foreach ($results as $result) {
					if ($this->model_account_customer->isBanIp($result['ip'])) {
						$status = true;

						break;
					}
				}
			} else {
				$status = $this->model_account_customer->isBanIp($order_info['ip']);
			}

			if ($status) {
				$order_status_id = $this->config->get('config_order_status_id');
			}

			// Aliexpress Extension
			if ($this->config->get('module_wk_dropship_status')) {
            	if(isset($this->session->data['warehouseOrders']) && $this->session->data['warehouseOrders']) {
              		foreach ($this->session->data['warehouseOrders'] as $key => $warehouseOrder) {
                		$warehouse_id = $key;
                		$sql = "INSERT INTO ".DB_PREFIX."warehouse_order_shipping SET
                			warehouse_id = '".(int)$warehouse_id."',
                			order_id = '".(int)$order_id."',
                			code = '".$this->db->escape($warehouseOrder['shippingMethod']['code'])."',
                			cost = '".$this->db->escape($warehouseOrder['shippingMethod']['cost'])."',
                			title = '".$this->db->escape($warehouseOrder['shippingMethod']['title'])."' ";
                		$this->db->query($sql);
                		foreach ($warehouseOrder['products'] as $key => $product) {
                  			$sql = "INSERT INTO ".DB_PREFIX."warehouse_order SET
                  				order_id = '".(int)$order_id."',
                  				warehouse_id = '".(int)$warehouse_id."',
                  				product_id = '".(int)$product['product_id']."',
                  				quantity = '".(int)$product['quantity']."',
                  				price = '".$this->db->escape($product['price'])."',
                  				warehouseAmount = '".$this->db->escape($product['warehouseAmount'])."',
                  				adminAmount = '".$this->db->escape($product['adminAmount'])."',
                  				total = '".$this->db->escape($product['total'])."',
                  				order_currency = '".$this->db->escape($this->session->data['currency'])."',
                  				paid_status = '0' ";
                  			$this->db->query($sql);
                  			$this->db->query("UPDATE ".DB_PREFIX."warehouse_product SET
                  				quantity = quantity - '".(int)$product['quantity']."'
                  				WHERE warehouse_id = '".(int)$warehouse_id."' AND
                  				product_id = '".(int)$product['product_id']."' ");
                		}
              		}
            	}
          	}

			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = '". $current_date_time ."' WHERE order_id = '" . (int)$order_id . "'");

            //$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($is_multiseller) {
                $ms_order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
                $this->language->load_json('multiseller/multiseller');
                if (in_array($order_status_id, $this->config->get('msconf_credit_order_statuses'))) {
                    $sendmail = false;
                    foreach ($ms_order_product_query->rows as $order_product) {
                        $seller_id = $this->MsLoader->MsProduct->getSellerId($order_product['product_id']);

                        if (!$seller_id) continue;

                        // check adaptive payments
                        $payment = $this->MsLoader->MsPayment->getPayments(array(
                            'order_id' => $order_id,
                            'seller_id' => $seller_id,
                            'payment_type' => array(MsPayment::TYPE_SALE),
                            'payment_status' => array(MsPayment::STATUS_PAID),
                            'single' => 1
                        ));

                        if ($payment) {
                            $this->db->query("UPDATE " . DB_PREFIX . "ms_product SET number_sold  = (number_sold + " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "'");
                            $sendmail = true;
                            continue;
                        }

                        $balance_entry = $this->MsLoader->MsBalance->getBalanceEntry(
                            array(
                                'seller_id' => $seller_id,
                                'product_id' => $order_product['product_id'],
                                'order_id' => $order_id,
                                'balance_type' => MsBalance::MS_BALANCE_TYPE_SALE
                            )
                        );

                        if (!$balance_entry) {
                            // don't calculate fees for free products
                            if ($order_product['total'] > 0) {
                                $commissions = $this->MsLoader->MsCommission->calculateCommission(array('seller_id' => $seller_id, 'product_id' => $order_product['product_id']));
                                //general
								if($commissions[MsCommission::RATE_SALE]['commission_type']=="general")
								{
								  $store_commission_flat = $commissions[MsCommission::RATE_SALE]['flat'];
                                  $store_commission_pct = $order_product['total'] * $commissions[MsCommission::RATE_SALE]['percent'] / 100;
								}
								//percent list based on category
								else
								{
									$this->load->model('catalog/product'); 
									$categories=$this->model_catalog_product->getCategories($order_product['product_id']);
									if(!empty($categories) && count($categories)>0)
									{
										$store_commission_flat=$commissions[MsCommission::RATE_SALE]['flat'];
										$store_commission_pct = $order_product['total'] * ($commissions[MsCommission::RATE_SALE]['percent'] / 100);
									}
									else{
										$store_commission_flat=0;
										$store_commission_pct=0;
									}                             
								}
                                $seller_net_amt = $order_product['total'] - ($store_commission_flat + $store_commission_pct);
                            } else {
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
                } else if (in_array($order_status_id, $this->config->get('msconf_debit_order_statuses'))) {
                    $sendmail = false;
                    foreach ($ms_order_product_query->rows as $order_product) {
                        $seller_id = $this->MsLoader->MsProduct->getSellerId($order_product['product_id']);
                        if (!$seller_id) continue;
                        $refund_balance_entry = $this->MsLoader->MsBalance->getBalanceEntry(
                            array(
                                'seller_id' => $seller_id,
                                'product_id' => $order_product['product_id'],
                                'order_id' => $order_id,
                                'balance_type' => MsBalance::MS_BALANCE_TYPE_REFUND
                            )
                        );

                        if (!$refund_balance_entry) {
                            $balance_entry = $this->MsLoader->MsBalance->getBalanceEntry(
                                array(
                                    'seller_id' => $seller_id,
                                    'product_id' => $order_product['product_id'],
                                    'order_id' => $order_id,
                                    'balance_type' => MsBalance::MS_BALANCE_TYPE_SALE
                                )
                            );

                            if ($balance_entry) {
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

                                // todo send refund mails
                                // $this->MsLoader->MsMail->sendOrderMails($order_id);
                            } else {
                                // send order status change mails
                            }
                        }
                    }
                }
            }

			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '1', comment = '" . $this->db->escape(($comment) ? $comment : '') . "', date_added = '" . $current_date_time . "'");

			//Join product_to_dropna
			if($dropna_settings['status'] == 1){
				$order_product_query = $this->db->query("SELECT op.*, ptd.dropna_product_id, ptd.dropna_user_id , p.product_id,p.image FROM " . DB_PREFIX . "order_product op LEFT JOIN product_to_dropna ptd ON (op.product_id = ptd.product_id) LEFT JOIN product p ON(op.product_id = p.product_id ) WHERE op.order_id = '" . (int)$order_id . "'");

				$products_syncData = [];
			}
			else{
				$order_product_query = $this->db->query("SELECT op.*,p.image FROM " . DB_PREFIX . "order_product op LEFT JOIN product p on op.product_id=p.product_id WHERE order_id = '" . (int)$order_id . "'");
			}

			$productVariationsSku = $this->load->model('module/product_variations', ['return' => true]);

			/////////////////////////////////////
			//Collect order product options once
			/////////////////////////////////////
			$order_product_ids = [];
			foreach ($order_product_query->rows as $order_product) {
				$order_product_ids[] = $order_product['order_product_id'];
			}
			$order_product_ids = implode(',',$order_product_ids);
			$order_product_options = [];
			$order_product_options_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id IN(" . $order_product_ids . ")");
			foreach ($order_product_options_query->rows as $order_product_option) {
				$order_product_options[$order_product_option['order_product_id']][] =  $order_product_option;
			}
			/////////////////////////////////////////

			foreach ($order_product_query->rows as $order_product) {
				// do not subtract quantity for unlimited products .
				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1' AND unlimited = '0' ");

				if($order_product['dropna_product_id'] && $dropna_settings['status'] == 1)
                	$products_syncData[$order_product['product_id']] = $order_product;

                //$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

                if($is_multiseller) {
					// Check if the product is attached to seller or not
					$product_sellers = $this->db->query("SELECT * FROM " . DB_PREFIX . "ms_product WHERE product_id = ".(int)$order_product['product_id']);
					if($product_sellers->num_rows){
						if ($this->config->get('msconf_disable_product_after_quantity_depleted')) {
							$product_rest = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$order_product['product_id'] . "'");
							if ((int)$product_rest->row['quantity'] <= 0) {
								$this->MsLoader->MsProduct->changeStatus((int)$order_product['product_id'], MsProduct::STATUS_DISABLED);
								$this->MsLoader->MsProduct->disapprove((int)$order_product['product_id']);
							}
						}
					}

                }

				//$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product['order_product_id'] . "'");

                if ($productVariationsSku->isActive()) {
					$product_option_value_id = array_column($order_product_options[$order_product['order_product_id']], 'product_option_value_id');
                	//$product_option_value_id = array_column($order_option_query->rows, 'product_option_value_id');
                    if (count($product_option_value_id) > 0) {
                        $optionValuesIds = $productVariationsSku->getOptionValuesIds($product_option_value_id);
                        if ($optionValuesIds) {
                            $optionValuesIds = array_column($optionValuesIds, 'option_value_id');
                            sort($optionValuesIds);
                            $productVariationsSku->updateVariationQuantityByValuesIds(
                                $order_product['product_id'],
                                $optionValuesIds,
                                $order_product['quantity']
                            );
                        }
                    }
                }

                foreach ($order_product_options[$order_product['order_product_id']] as $option){
				//foreach ($order_option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");

					if($order_product['dropna_product_id'] && $dropna_settings['status'] == 1){
						$product_opt_val_rest = $this->db->query("SELECT pov.quantity, povr.dropna_pr_op_val_id FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN pr_op_val_to_dropna povr ON (pov.product_option_value_id = povr.product_option_value_id) WHERE pov.product_option_value_id = '" . (int)$option['product_option_value_id'] . "'");

						$products_syncData[$order_product['product_id']]['opt_vals'][] = [ 'option_data' => $option, 'id' => $option['product_option_value_id'], 'quantity' => $product_opt_val_rest->row['quantity'], 'dropna_val_id' => $product_opt_val_rest->row['dropna_pr_op_val_id'] ];
					}
				}
			}

			/// Dropna Quantity Sync
			if($dropna_settings['status'] == 1)
				$this->dropaSaveOrder($products_syncData, $order_info);
			////////////////////////

			$this->cache->delete('product');

			// Qoyod Invoice
			$qoyod_invoice_status = array_key_exists("qoyod_invoice", $order_info) ? $order_info['qoyod_invoice'] : 0;
			$this->qoyod_create_invoice($qoyod_invoice_status, $order_id, $order_status_id);

			// Downloads
			$order_download_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "'");

			//Aramex
            if($this->config->get('aramex_auto_create_shipment')==1 && $this->config->get('aramex_status') ==1){
                $this->load->model('aramex/aramex');
                $this->model_aramex_aramex->create($order_id);
            }

            //Salasa
            $salasa_settings = $this->config->get('salasa');
            if(is_array($salasa_settings) && $salasa_settings['status'] == 1 && $salasa_settings['is_shipping'] != 1 && $order_status_id == $salasa_settings['status_code']) {
                $this->load->model('shipping/salasa');
                $this->model_shipping_salasa->create($order_id);
            }

			//DHL Express
            if($this->config->get('dhl_express_auto_creation')==1 && $this->config->get('dhl_express_status') ==1){

				$this->load->model('dhl_express/shipment');
				$this->model_dhl_express_shipment->create($order_id);
            }

            //FDS
            $fds_settings = $this->config->get('fds');
            if(is_array($fds_settings) && $fds_settings['status'] == 1) {
                $this->load->model('shipping/fds');
                $this->model_shipping_fds->create($order_id);
            }

            // reward points
            if ($this->customer->isLogged() && isset( $this->session->data['reward']) && !empty( $this->session->data['reward'])) {
            	$current_points = $this->customer->getRewardpoints();
            	$updated_points = $current_points - (int) $this->session->data['reward'];

            	if ( $updated_points >= 0 )
            	{
					$this->language->load_json('rewardpoints/index');
					$session_points = -1 * abs( (int) $this->session->data['reward']); // that trick is used to change the value to a minus.
					$customer_id = $this->customer->getId();

					$status = (int)($order_status_id==$this->config->get('update_deduction_based_order_status') || in_array($order_status_id, $this->config->get('update_deduction_based_order_status')));
					$query = "INSERT INTO " .DB_PREFIX. "customer_reward SET customer_id = '{$customer_id}', points = '{$session_points}', order_id = '{$order_id}', date_added = NOW(), description = '".$this->language->get('text_reward_used_for_order')."{$order_id}.', status={$status}";

					$this->db->query($query);
				}

            }

			// Gift Voucher
			$this->load->model('checkout/voucher');

			$order_voucher_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_voucher_query->rows as $order_voucher) {
				$voucher_id = $this->model_checkout_voucher->addVoucher($order_id, $order_voucher);

				$this->db->query("UPDATE " . DB_PREFIX . "order_voucher SET voucher_id = '" . (int)$voucher_id . "' WHERE order_voucher_id = '" . (int)$order_voucher['order_voucher_id'] . "'");
			}


			// Send out any gift voucher mails
			if ($this->config->get('config_complete_status_id') == $order_status_id) {
				$this->model_checkout_voucher->confirm($order_id);
			}



	        //Buyer Subscription Plan
			if( !empty($this->session->data['subscription']) ){
				$this->db->query("INSERT INTO `" . DB_PREFIX . "buyer_subscription_payments_log` SET order_id = '" . (int)$order_id . "', buyer_id = " . (int)$order_info['customer_id'] . ", subscription_id = " . (int)$this->session->data['subscription']['id'] . ", amount = " . (float)$this->session->data['subscription']['amount'] . " , payment_data = '" . $this->db->escape($order_info['payment_method']) . '-' .$this->db->escape($order_info['payment_code']) . "' , currency_id = " . (int)$order_info['currency_id'] . ", currency_code = '" . $this->db->escape($order_info['currency_code']) . "'");
				$this->db->query("UPDATE `". DB_PREFIX . "customer` SET buyer_subscription_id = " .(int)$this->session->data['subscription']['id'] . " WHERE customer_id = " . (int)$order_info['customer_id']);
			}



			// Order Totals
			$order_total_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");
			$orderDataInvoiceFormatted['totals'] = $order_total_query->rows;
			if(STORECODE == 'UPMXV084' ){
				$arr  = [
					'order_total_result'=>$order_total_query->rows
				];
				$this->log("ModelCheckoutOrderConfirm:" , json_encode($arr));
			}
			foreach ($order_total_query->rows as $order_total) {
				//Preventing add reward points again, reward points are already added in the previous code  // reward points
				if($order_total['code'] == 'reward') continue;

				if ($this->config->get($order_total['code'] . '_status')) {
                    if ($this->load->model('total/' . $order_total['code'], ['return' => true, 'return_false' => true])) {
                        if (method_exists($this->{'model_total_' . $order_total['code']}, 'confirm')) {
                            $this->{'model_total_' . $order_total['code']}->confirm($order_info, $order_total);
						}
					}
				}
				if(STORECODE == 'UPMXV084' && strtolower($order_total['code']) == 'coupon'){
					$arr  = [];
					$arr['coupon_status'] =$this->config->get('coupon_status');
					$this->log("ModelCheckoutOrderConfirm:" , json_encode($arr));
				}
			}


			// Add customer points.
                        // this comment added because the points are added before and in this block we add points again with order number

			//			if ( $this->customer->isLogged() && isset( $this->session->data['earned_points']) && ! empty( $this->session->data['earned_points'] )  && is_numeric( $this->session->data['earned_points'] ) )
			//			{
			//				$this->language->load_json('rewardpoints/index');
			//				$points = (int) $this->session->data['earned_points'];
			//				$customer_id = (int) $this->customer->getId();
			//
			//				$query = "INSERT INTO " . DB_PREFIX ."customer_reward SET customer_id = '{$customer_id}', order_id = '{$order_id}', points = '{$points}', date_added = NOW(), description = '".$this->language->get('text_reward_earned_from_order')."{$order_id}'";
			//				$this->db->query($query);
			//
			//				unset( $this->session->data['earned_points'] );
			//			}


			// ZOHO inventory create order if app is setup
			$this->load->model('module/zoho_inventory');
			$this->model_module_zoho_inventory->createOrder($order_id, $order_info, $order_product_query->rows);

	
			//fire new order trigger for zapier  if installed
			$this->load->model('module/zapier');
			if ($this->model_module_zapier->isInstalled())
				$this->model_module_zapier->newOrderTrigger($order_info);

			/***************** Start ExpandCartTracking #347716  ****************/

			// send mixpanel receive order and update time of last order
			$this->load->model('setting/mixpanel');
			$this->model_setting_mixpanel->updateUser(['$last order' => date("Y-m-d H:i:s")]);
			$this->model_setting_mixpanel->trackEvent('Received Order',['Order ID'=>$order_id]);

			// send amplitude receive order and update time of last order
			$this->load->model('setting/amplitude');

			$order_product_ids_arr = $order_product_ids?:[];

			if (!is_array($order_product_ids))
				$order_product_ids_arr = explode(',' , $order_product_ids);


			if (!$this->isTestOrder($order_info , $order_product_ids_arr))
			{
				$this->model_setting_amplitude->updateUser(['last order' => date("Y-m-d H:i:s")]);
				$this->model_setting_amplitude->trackEvent('Order Received Successfully',['Order ID'=>$order_id]);
			}

			/***************** End ExpandCartTracking #347716  ****************/

			// add order to knawat if it's products belong to it
			    $this->load->model('module/knawat');
			    if ($this->model_module_knawat->isInstalled()) {
					if ($this->model_module_knawat->checkOrderStatusForInsertToKnawat($order_id) && $this->model_module_knawat->checkIfKnawatOrder($order_id))
						$this->model_module_knawat->insertOrderIntoKnawat($order_id);
			    }

			$data['notification_module']="orders";
			$data['notification_module_code']="orders_new";
			$data['notification_module_id']=$order_id;
			$this->notifications->addAdminNotification($data);

			//################### Freshsales Start #####################################
            try {
                $fields = array();

                if (PRODUCTID == 3)
                    $fields["boolean--Has--Received--Test--Orders"] = true;
                else
                    $fields["boolean--Has--Received--Orders"] = true;

                autopilot_UpdateContactCustomFields(BILLING_DETAILS_EMAIL, $fields);


                $eventName = "Received an Order";

                if (PRODUCTID == 3)
                    $eventName = "Received an Order in Trial Period";

                //FreshsalesAnalytics::init(array('domain'=>'https://' . FRESHSALES_SUBDOMAIN . '.freshsales.io','app_token'=>FRESHSALES_TOKEN));

                //FreshsalesAnalytics::trackEvent(array(
                //    'identifier' => BILLING_DETAILS_EMAIL,
                //    'name' => $eventName
                //));
            }
            catch (Exception $e) {  }
            //################### Freshsales End #####################################

            //################### Intercom Start #####################################
            try {
                $eventName = "orders-customer";

                if (PRODUCTID == 3)
                    $eventName = "orders-trial";

                $url = 'https://api.intercom.io/events';
                $authid = INTERCOM_AUTH_ID;

                $cURL = curl_init();
                curl_setopt($cURL, CURLOPT_URL, $url);
                curl_setopt($cURL, CURLOPT_USERPWD, $authid);
                curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($cURL, CURLOPT_POST, true);
                curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json'
                ));
                $data = '{
                        "event_name" : "' . $eventName . '",
                        "created_at": ' . time() . ',
                        "user_id": "'.STORECODE.'"
                   }';
                curl_setopt($cURL, CURLOPT_POSTFIELDS, $data);
                $result = curl_exec($cURL);
                curl_close($cURL);
            }
            catch (Exception $e) {  }
            //################### Intercom End #######################################
			
			 //################### Hubspot Start #####################################
		
			 if (PRODUCTID == 3) $isTestOrder='Yes';
			 else $isTestOrder='No';

			 Hubspot ::tracking('pe25199511_os_has_recieved_orders',
			 ["ec_os_hroi_is_test_order"=>$isTestOrder]);

		  //################### Hubspot End #####################################


            /********************** Prize Draw *************************************/
			if ( $order_info['customer_id'] ) {
				$this->prize_draw_process($order_info['customer_id'], $order_product_query->rows);
			}
			/********************** Prize Draw *************************************/

			// Send out order confirmation mail
			$language = new Language($order_info['language_code']);
			$language->load_json('mail/order');

			$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

			if ($order_status_query->num_rows) {
				$order_status = $order_status_query->row['name'];
			} else {
				$order_status = '';
				/** * [Modification for Amazon_connector] * @var [Starts] */
				if ($this->config->get('wk_amazon_connector_status')){
					$this->load->model('amazon_map/product');
					$this->model_amazon_map_product->updateProductStockAtAmazon($order_info, $order_status_id, $comment);
				} /** * [Modification for Amazon_connector] * @var [Ends] */

			}

			$subject = sprintf($language->get('text_new_subject'), $order_info['store_name'], $order_id);

			// Get custom fields for email from session
			$shipping = $this->session->data['shipping_address'];
			$payment = $this->session->data['payment_address'];
			$confirm = $this->session->data['confirm']['agree'];
			$custom_fields = [];

			// Set custom fields to the data going to the email template
			if (
				(isset($shipping) && !empty($shipping)) ||
				(isset($payment) && !empty($payment)) ||
				(isset($confirm) && !empty($confirm) && $confirm == 1)
			) {
				$this->load->model('module/quickcheckout_fields');
				$custom_fields = $this->model_module_quickcheckout_fields->
				getOrderCustomFields($this->session->data['order_id']);
			}
			//check delivery slot to show it in email templates for customer and store owner
			$delivery_slot = $this->config->get('delivery_slot');
			if(is_array($delivery_slot) && count($delivery_slot) > 0){
				$this->load->model('module/delivery_slot/slots');
				$orderSlot = $this->model_module_delivery_slot_slots->getOrderDeliverySlot($order_id);
			}

			// HTML Mail
			$template = new Template();
			if(is_array($delivery_slot) && count($delivery_slot) > 0){
				$template->data['delivery_slot'] = true;
				$template->data['order_delivery_slot'] = $orderSlot;
			}
			$template->data['custom_fields'] = $custom_fields;
			$template->data['title'] = sprintf($language->get('text_new_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);

			$template->data['text_greeting'] = sprintf($language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
			$template->data['text_link'] = $language->get('text_new_link');
			$template->data['text_download'] = $language->get('text_new_download');
			$template->data['text_order_detail'] = $language->get('text_new_order_detail');
			$template->data['text_instruction'] = $language->get('text_new_instruction');
			$template->data['text_order_id'] = $language->get('text_new_order_id');
			$template->data['text_date_added'] = $language->get('text_new_date_added');
			$template->data['text_payment_method'] = $language->get('text_new_payment_method');
			$template->data['text_shipping_method'] = $language->get('text_new_shipping_method');
			$template->data['text_email'] = $language->get('text_new_email');
			$template->data['text_telephone'] = $language->get('text_new_telephone');
			$template->data['text_ip'] = $language->get('text_new_ip');
			$template->data['text_payment_address'] = $language->get('text_new_payment_address');
			$template->data['text_shipping_address'] = $language->get('text_new_shipping_address');
			$template->data['text_product'] = $language->get('text_new_product');
			$template->data['text_model'] = $language->get('text_new_model');
			$template->data['text_quantity'] = $language->get('text_new_quantity');
			$template->data['text_price'] = $language->get('text_new_price');
			$template->data['text_total'] = $language->get('text_new_total');
			$template->data['text_footer'] = $language->get('text_new_footer');
			$template->data['text_powered'] = '';
			$template->data['text_delivery_day'] = $language->get('text_delivery_day');
			$template->data['text_delivery_date'] = $language->get('text_delivery_date');
			$template->data['text_delivery_slot'] = $language->get('text_delivery_slot');

			$template->data['logo'] = $this->config->get('config_url') . 'image/' . STORECODE . '/'  . $this->config->get('config_logo');
			$template->data['logo_height'] = $this->config->get('config_order_invoice_logo_height');
			$template->data['store_name'] = $order_info['store_name'];
			$template->data['store_url'] = $order_info['store_url'];
			$template->data['customer_id'] = $order_info['customer_id'];
			$template->data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id;

			if ($order_download_query->num_rows) {
				$template->data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
			} else {
				$template->data['download'] = '';
			}

			$template->data['order_id'] = $order_id;
			$template->data['date_added'] = date('Y-m-d',strtotime($order_info['date_added'])) ;
			$template->data['payment_method'] = $order_info['payment_method'];
			$template->data['shipping_method'] = $order_info['shipping_method'];
			$template->data['email'] = $order_info['email'];
			$template->data['telephone'] = $order_info['telephone'];
			$template->data['ip'] = $order_info['ip'];

			if ($comment && $notify) {
				$template->data['comment'] = nl2br($comment);
			} else {
				$template->data['comment'] = '';
			}

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$template->data['payment_address'] = $orderDataInvoiceFormatted['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$template->data['shipping_address'] = $orderDataInvoiceFormatted['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			// Products
			$template->data['products'] = array();

			foreach ($order_product_query->rows as $product) {
				$option_data = array();

				//$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

				foreach ($order_product_options[$product['order_product_id']] as $option){
				//foreach ($order_option_query->rows as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

                //$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

                if($is_multiseller) {
                    $this->language->load_json('multiseller/multiseller');
                    $seller = $this->MsLoader->MsSeller->getSeller($this->MsLoader->MsProduct->getSellerId($product['product_id']));
                }
				// check if product has codes
				if($product['code_generator'] != null && !in_array($order_info['payment_code'], ['cod', 'ccod', 'bank_transfer', 'cheque', 'my_fatoorah', 'payoneer', 'free_checkout', 'tamara', 'tamara_installment']))
				{
					$productCodeData = json_decode($product['code_generator'] ,true);
				}

				// << Product Option Image PRO module
				if (isset($product['image'])) {
					$this->load->model('module/product_option_image_pro');
					$poip_installed = $this->model_module_product_option_image_pro->installed();
					if ($poip_installed) {
						$product['image'] = $this->model_module_product_option_image_pro->getProductOrderImage($product['product_id'], $order_product_options[$product['order_product_id']], $product['image'], false);
					}
				}
				// >> Product Option Image PRO module

				$template->data['products'][] = array(
                    'product_id' => $is_multiseller ? $product['product_id'] : 0,
                    'seller_text' => $is_multiseller && $seller ? "<br/ > " . $this->language->get('ms_by') . " {$seller['ms.nickname']} <br />" : '',
					'name'     => $product['name'],
					'model'    => $product['model'],
					'thumb'    => \Filesystem::getUrl('image/' . $product['image']),
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'productCodeData'	=> isset($productCodeData) ? $productCodeData : null,
					'price'    => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value'])
				);

			}

			// Vouchers
			$template->data['vouchers'] = array();

			foreach ($order_voucher_query->rows as $voucher) {
				$template->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$template->data['totals'] = $order_total_query->rows;

			if ($order_info['comment']) {
				$template->data['comment'] = nl2br($order_info['comment']);
				$template->data['text_footer'] = $order_info['comment'];
			}

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/order.tpl')) {
				$html = $template->fetch($this->config->get('config_template') . '/template/mail/order.tpl');
			} else {
				$html = $template->fetch('default/template/mail/order.tpl');
			}

			// Text Mail
			$text  = sprintf($language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8')) . "\n\n";
			$text .= $language->get('text_new_order_id') . ' ' . $order_id . "\n";
			$text .= $language->get('text_new_date_added') . ' ' . date('Y-m-d',strtotime($order_info['date_added'])) . "\n";
			$text .= $language->get('text_new_order_status') . ' ' . $order_status . "\n\n";

			if ($comment && $notify) {
				$text .= $language->get('text_new_instruction') . "\n\n";
				$text .= $comment . "\n\n";
			}

			// Products
			$text .= $language->get('text_new_products') . "\n";

			foreach ($order_product_query->rows as $product) {
				$text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' . html_entity_decode($this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

				//$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . $product['order_product_id'] . "'");
				foreach ($order_product_options[$product['order_product_id']] as $option){
				//foreach ($order_option_query->rows as $option) {
					$text .= chr(9) . '-' . $option['name'] . ' ' . (utf8_strlen($option['value']) > 20 ? utf8_substr($option['value'], 0, 20) . '..' : $option['value']) . "\n";
				}
			}

			foreach ($order_voucher_query->rows as $voucher) {
				$text .= '1x ' . $voucher['description'] . ' ' . $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']);
			}

			$text .= "\n";

			$text .= $language->get('text_new_order_total') . "\n";

			foreach ($order_total_query->rows as $total) {
				$text .= $total['title'] . ': ' . html_entity_decode($total['text'], ENT_NOQUOTES, 'UTF-8') . "\n";
			}

			$text .= "\n";

			if ($order_info['customer_id']) {
				$text .= $language->get('text_new_link') . "\n";
				$text .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . "\n\n";
			}

			if ($order_download_query->num_rows) {
				$text .= $language->get('text_new_download') . "\n";
				$text .= $order_info['store_url'] . 'index.php?route=account/download' . "\n\n";
			}

			// Comment
			if ($order_info['comment']) {
				$text .= $language->get('text_new_comment') . "\n\n";
				$text .= $order_info['comment'] . "\n\n";
			}

			$text .= $language->get('text_new_footer') . "\n\n";


			/**
			 * Don't send email to the client and admin
			 * if the payment process is failed
			 */
			if($order_status_id == 10)
			{
				$html="";
				$text  = sprintf($language->get('text_failed_payment_process'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8')) . "\n\n";

			}

			if ( array_key_exists("email", $order_info) && ! empty($order_info['email']) && $order_status_id != 0) {

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
				$mail->setTo($order_info['email']);
				$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
				$mail->setSender($order_info['store_name']);
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
							$orderDataInvoiceFormatted=$order_info;
							$this->formatCustomInvoiceTemplateOrderData($orderDataInvoiceFormatted);
							$orderDataInvoiceFormatted['invoice_no'] = 0;
							if(!$this->config->get('config_stop_auto_generate_invoice_no')){
								$orderDataInvoiceFormatted['invoice_no'] = $this->createInvoiceNo($orderDataInvoiceFormatted['order_id']);
							}
							$orderDataInvoiceFormatted['time_added'] = date('H:i', strtotime($this->getDateByCurrentTimeZone($order_info['date_added'])));
							$path_to_invoice_pdf = $cet->invoice($orderDataInvoiceFormatted, $cet_result['history_id']);
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
				$mail->setHtml($html);
				$mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
				$mail->send();
				if ($this->config->get('custom_email_templates_status')) {
					$mail->sendBccEmails();
				}
            }

            $querySMSModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'smshare'");
            if($querySMSModule->num_rows) {
                // smshare
                if (SmshareCommons::log($this->config)) $this->log->write('[smshare] Checkout... Call notify customer/admin routine..');
                $this->load->model('module/smshare');
				$order_info['check_if_failed'] = $order_status_id;
				$this->model_module_smshare->notify_or_not_customer_on_checkout($order_info, $order_total_query);
				$this->model_module_smshare->notify_or_not_admin_on_checkout($order_info, $order_total_query, $order_status, $order_product_query->rows, $order_voucher_query->rows);
            }

			if (\Extension::isInstalled('whatsapp')) {
				$queryWhatsAppModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'whatsapp'");
				if($queryWhatsAppModule->num_rows) {
					// whatsapp
					if (WhatAppCommons::log($this->config)) $this->log->write('[whatsapp] Checkout... Call notify customer/admin routine..');
					$this->load->model('module/whatsapp');
					$order_info['check_if_failed'] = $order_status_id;
					$this->model_module_whatsapp->notify_or_not_customer_on_checkout($order_info, $order_total_query);
					$this->model_module_whatsapp->notify_or_not_admin_on_checkout($order_info, $order_total_query, $order_status, $order_product_query->rows, $order_voucher_query->rows);
				}
			}
			
			// whatsapp-v2
			if (\Extension::isInstalled('whatsapp_cloud')) {
				$this->load->model('module/whatsapp_cloud');
				$order_info['check_if_failed'] = $order_status_id;
				$order_totals 	= $order_total_query->rows ;   
				$this->model_module_whatsapp_cloud->checkoutNotifications($order_info, $order_totals);
			}

			//		$this->cart->clear(); This Line will create orders without product in some cases.
            // Admin Alert Mail
			if ($this->config->get('config_alert_mail') && $order_status_id != 0) {
				$subject = sprintf($language->get('text_new_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $order_id);

				//Load admin language
				if($this->config->get('config_admin_language')){
					$language = new Language($this->config->get('config_admin_language'));
					$language->load_json('mail/order');
				}


				/**
				 * Don't send email to the client and admin
				 * if the payment process is failed
				 */
				if($order_status_id == 10)
				{
					$html="";
					$text  = sprintf($language->get('text_failed_payment_process'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8')) . "\n\n";

				}
				else{
					$template = new Template();
					if(is_array($delivery_slot) && count($delivery_slot) > 0){
						$template->data['delivery_slot'] = true;
						$template->data['order_delivery_slot'] = $orderSlot;
					}
					$template->data['custom_fields'] = $custom_fields;
						// Text
//					$text  = $language->get('text_new_received') . "\n\n";
					$template->data['title'] = $language->get('text_new_received');
					$template->data['text_greeting'] = $language->get('text_new_received');
					$template->data['text_link'] = $language->get('text_new_link');
					$template->data['text_download'] = $language->get('text_new_download');
					$template->data['text_order_detail'] = $language->get('text_new_order_detail');
					$template->data['text_instruction'] = $language->get('text_new_instruction');
					$template->data['text_order_id'] = $language->get('text_new_order_id');
					$template->data['text_date_added'] = $language->get('text_new_date_added');
					$template->data['text_payment_method'] = $language->get('text_new_payment_method');
					$template->data['text_shipping_method'] = $language->get('text_new_shipping_method');
					$template->data['text_email'] = $language->get('text_new_email');
					$template->data['text_telephone'] = $language->get('text_new_telephone');
					$template->data['text_ip'] = $language->get('text_new_ip');
					$template->data['text_payment_address'] = $language->get('text_new_payment_address');
					$template->data['text_shipping_address'] = $language->get('text_new_shipping_address');
					$template->data['text_product'] = $language->get('text_new_product');
					$template->data['text_model'] = $language->get('text_new_model');
					$template->data['text_quantity'] = $language->get('text_new_quantity');
					$template->data['text_price'] = $language->get('text_new_price');
					$template->data['text_total'] = $language->get('text_new_total');
					$template->data['text_powered'] = '';
					$template->data['logo'] = $this->config->get('config_url') . 'image/' . STORECODE . '/'  . $this->config->get('config_logo');
					$template->data['logo_height'] = $this->config->get('config_order_invoice_logo_height');
					$template->data['store_name'] = $order_info['store_name'];
					$template->data['store_url'] = $order_info['store_url'];
					$template->data['customer_id'] = $order_info['customer_id'];
					$template->data['link'] = $order_info['store_url'] . 'admin/sale/order/info?order_id=' . $order_id . "\n\n";
					$template->data['order_id'] = $order_id;
					$template->data['date_added'] = date('Y-m-d',strtotime($order_info['date_added'])) ;
					$template->data['payment_method'] = $order_info['payment_method'];
					$template->data['shipping_method'] = $order_info['shipping_method'];
					$template->data['email'] = $order_info['email'];
					$template->data['telephone'] = $order_info['telephone'];
					$template->data['ip'] = $order_info['ip'];
					$template->data['text_delivery_day'] = $language->get('text_delivery_day');
					$template->data['text_delivery_date'] = $language->get('text_delivery_date');
					$template->data['text_delivery_slot'] = $language->get('text_delivery_slot');

					$template->data['products'] = array();
					foreach ($order_product_query->rows as $product) {
						$option_data = array();

						//$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");
						foreach ($order_product_options[$product['order_product_id']] as $option){
						//foreach ($order_option_query->rows as $option) {
							if ($option['type'] != 'file') {
								$value = $option['value'];
							} else {
								$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
							}

							$option_data[] = array(
								'name'  => $option['name'],
								'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
							);
						}

						//$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

						if($is_multiseller) {
							$this->language->load_json('multiseller/multiseller');
							$seller = $this->MsLoader->MsSeller->getSeller($this->MsLoader->MsProduct->getSellerId($product['product_id']));
						}
						// check if product has codes
						if($product['code_generator'] != null && !in_array($order_info['payment_code'], ['cod', 'ccod', 'bank_transfer', 'cheque', 'my_fatoorah', 'payoneer', 'free_checkout', 'tamara', 'tamara_installment']))
						{
							$productCodeData = json_decode($product['code_generator'] ,true);
						}

						// << Product Option Image PRO module
						if (isset($product['image'])) {
							$this->load->model('module/product_option_image_pro');
							$poip_installed = $this->model_module_product_option_image_pro->installed();
							if ($poip_installed) {
								$product['image'] = $this->model_module_product_option_image_pro->getProductOrderImage($product['product_id'], $order_product_options[$product['order_product_id']], $product['image'], false);
							}
						}
						// >> Product Option Image PRO module

						$template->data['products'][] = array(
							'product_id' => $is_multiseller ? $product['product_id'] : 0,
							'seller_text' => $is_multiseller ? "<br/ > " . $this->language->get('ms_by') . " {$seller['ms.nickname']} <br />" : '',
							'name'     => $product['name'],
							'thumb' => \Filesystem::getUrl('image/' . $product['image']),
							'model'    => $product['model'],
							'option'   => $option_data,
							'quantity' => $product['quantity'],
							'productCodeData'	=> isset($productCodeData) ? $productCodeData : null,
							'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
							'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
						);

					}

					if ($order_info['payment_address_format']) {
						$format = $order_info['payment_address_format'];
					} else {
						$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					}

					$find = array(
						'{firstname}',
						'{lastname}',
						'{company}',
						'{address_1}',
						'{address_2}',
						'{city}',
						'{postcode}',
						'{zone}',
						'{zone_code}',
						'{country}'
					);

					$replace = array(
						'firstname' => $order_info['payment_firstname'],
						'lastname'  => $order_info['payment_lastname'],
						'company'   => $order_info['payment_company'],
						'address_1' => $order_info['payment_address_1'],
						'address_2' => $order_info['payment_address_2'],
						'city'      => $order_info['payment_city'],
						'postcode'  => $order_info['payment_postcode'],
						'zone'      => $order_info['payment_zone'],
						'zone_code' => $order_info['payment_zone_code'],
						'country'   => $order_info['payment_country']
					);

					$template->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

					if ($order_info['shipping_address_format']) {
						$format = $order_info['shipping_address_format'];
					} else {
						$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					}

					$find = array(
						'{firstname}',
						'{lastname}',
						'{company}',
						'{address_1}',
						'{address_2}',
						'{city}',
						'{postcode}',
						'{zone}',
						'{zone_code}',
						'{country}'
					);

					$replace = array(
						'firstname' => $order_info['shipping_firstname'],
						'lastname'  => $order_info['shipping_lastname'],
						'company'   => $order_info['shipping_company'],
						'address_1' => $order_info['shipping_address_1'],
						'address_2' => $order_info['shipping_address_2'],
						'city'      => $order_info['shipping_city'],
						'postcode'  => $order_info['shipping_postcode'],
						'zone'      => $order_info['shipping_zone'],
						'zone_code' => $order_info['shipping_zone_code'],
						'country'   => $order_info['shipping_country']
					);

					$template->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));


					// Vouchers
					$template->data['vouchers'] = array();

					foreach ($order_voucher_query->rows as $voucher) {
						$template->data['vouchers'][] = array(
							'description' => $voucher['description'],
							'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
						);
					}

					$template->data['totals'] = $order_total_query->rows;

					if ($order_info['comment']) {
						$template->data['comment'] = nl2br($order_info['comment']);
						$template->data['text_footer'] = $order_info['comment'];
					}

					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/order.tpl')) {
						$html = $template->fetch($this->config->get('config_template') . '/template/mail/order.tpl');
					} else {
						$html = $template->fetch('default/template/mail/order.tpl');
					}
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
				$mail->setTo($this->config->get('config_email'));
				$mail->setFrom(!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
				$mail->setSender($order_info['store_name']);
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
							$orderDataInvoiceFormatted=$order_info;
							$this->formatCustomInvoiceTemplateOrderData($orderDataInvoiceFormatted);
							$path_to_invoice_pdf = $cet->invoice($orderDataInvoiceFormatted, $cet_result['history_id']);
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

				$mail->setHtml($html);
				$mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
				$mail->send();
                if ($this->config->get('custom_email_templates_status')) {
                    $mail->sendBccEmails();
                }

				// Send to additional alert emails
				$emails = explode(',', $this->config->get('config_alert_emails'));

				foreach ($emails as $email) {
					if ($email && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', trim($email))) {
						$mail->setTo($email);
						$mail->send();
                        if ($this->config->get('custom_email_templates_status')) {
                            $mail->sendBccEmails();
                        }
					}
				}
            }

		} // end if order info
	}

	public function update($order_id, $order_status_id, $comment = '', $notify = false) {
        $current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
		$order_info = $this->getOrder($order_id);

		if ($order_info && $order_info['order_status_id']) {
			// Fraud Detection
			if ($this->config->get('config_fraud_detection')) {
				$this->load->model('checkout/fraud');

				$risk_score = $this->model_checkout_fraud->getFraudScore($order_info);

				if ($risk_score > $this->config->get('config_fraud_score')) {
					$order_status_id = $this->config->get('config_fraud_status_id');
				}
			}

			// Qoyod Invoice
			$qoyod_invoice_status = $order_info['qoyod_invoice'] ? $order_info['qoyod_invoice'] : 0;
			$this->qoyod_create_invoice($qoyod_invoice_status, $order_id, $order_status_id);

			// Ban IP
			$status = false;

			$this->load->model('account/customer');

			if ($order_info['customer_id']) {

				$results = $this->model_account_customer->getIps($order_info['customer_id']);

				foreach ($results as $result) {
					if ($this->model_account_customer->isBanIp($result['ip'])) {
						$status = true;

						break;
					}
				}
			} else {
				$status = $this->model_account_customer->isBanIp($order_info['ip']);
			}

			if ($status) {
				$order_status_id = $this->config->get('config_order_status_id');
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = '" . $current_date_time . "' WHERE order_id = '" . (int)$order_id . "'");

            $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            if($queryMultiseller->num_rows) {
                $ms_order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
            }

			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = '" . $current_date_time . "'");

			// Send out any gift voucher mails
			if ($this->config->get('config_complete_status_id') == $order_status_id) {
				$this->load->model('checkout/voucher');

				$this->model_checkout_voucher->confirm($order_id);
			}

			if ($notify) {
				$language = new Language($order_info['language_code']);
				$language->load_json('mail/order');

				$subject = sprintf($language->get('text_update_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);

				$message  = $language->get('text_update_order') . ' ' . $order_id . "\n";
				$message .= $language->get('text_update_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

				$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

				if ($order_status_query->num_rows) {
					$message .= $language->get('text_update_order_status') . "\n\n";
					$message .= $order_status_query->row['name'] . "\n\n";
				}

				if ($order_info['customer_id']) {
					$message .= $language->get('text_update_link') . "\n";
					$message .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . "\n\n";
				}

				if ($comment) {
					$message .= $language->get('text_update_comment') . "\n\n";
					$message .= $comment . "\n\n";
				}

				$message .= $language->get('text_update_footer');

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
				$mail->setTo($order_info['email']);
				$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
				$mail->setSender($order_info['store_name']);
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
							$orderDataInvoiceFormatted=$order_info;
							$this->formatCustomInvoiceTemplateOrderData($orderDataInvoiceFormatted);
							$path_to_invoice_pdf = $cet->invoice($orderDataInvoiceFormatted, $cet_result['history_id']);
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

	/**
	 * @param $order_id
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

	public function qoyod_create_invoice($qoyod_invoice_status, $order_id, $order_status_id){

		$this->load->model('module/qoyod_integration');
			if($this->model_module_qoyod_integration->isActive() && !$qoyod_invoice_status){
				$qoyodSettings = $this->model_module_qoyod_integration->getSettings();

				// && in_array($order_status_id, $qoyodSettings['order_statuses'])
				if($qoyodSettings['contact_id'] && $qoyodSettings['api_key'] && $qoyodSettings['inventory_id'] && in_array($order_status_id, $qoyodSettings['order_statuses'])){
					$apiKey = $qoyodSettings['api_key'];
					$qoyod_product_query = $this->db->query("SELECT op.quantity, op.price, p.sku FROM " . DB_PREFIX . "order_product op LEFT JOIN product p ON (op.product_id = p.product_id) WHERE order_id = '" . (int)$order_id . "'");
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
	                							'inventory_id' => $qoyodSettings['inventory_id']

	                					   ];
	                }
					// Add Shipping Cost To Invoice Qyod
					if ($this->cart->hasShipping())
					{
						$shipping     = $this->session->data['shipping_method']['cost'];
						$qoyodProducts[] = [
							'product_id' => 1,
							'quantity' => 1,
							'unit_price' => $shipping,
							'inventory_id' => 1

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
					$res_arr = json_decode($response, true);
					curl_close($curl);
					if ($res_arr['error'] || $res_arr['errors']) {
					  //return false;
					} else {
						$this->db->query("UPDATE `" . DB_PREFIX . "order` SET qoyod_invoice = '1' WHERE order_id = '" . (int)$order_id . "'");;
					}
				}
			}
	}

	/*public function delete($order_id)
	{
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
		if ($this->config->get('module_wk_dropship_status')) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "warehouse_order`
				WHERE order_id = '" . (int)$order_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "warehouse_order_shipping`
				WHERE order_id = '" . (int)$order_id . "'");
		}
	}*/

	//Customer save prize draw
	private function prize_draw_process($customer_id, $products){
		$prdw_settings = $this->config->get('prize_draw_module');
		if (isset($prdw_settings) && $prdw_settings['status'] == 1) {
			$pr_ids = [];
			//List product ids
			foreach ($products as $order_product) {
				$product_id = $order_product['product_id'];
				$order_id   = $order_product['order_id'];
				//$pr_ids[] = $order_product['product_id'];

				$prdw_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prdw_product_to_prize  WHERE product_id = ".$product_id);

				//Save customer draw
				if($prdw_query->num_rows){
					$prdw = $prdw_query->row;

					//Add customer prize based on product quantity
					for($i = 0; $i < $order_product['quantity']; $i++){
						$this->db->query("INSERT INTO " . DB_PREFIX . "prdw_customer_to_prize SET
										 customer_id = '" . (int)$customer_id . "',
									  	 prize_draw_id = '" . (int)$prdw['prize_draw_id'] . "',
										 product_id = '" . (int)$prdw['product_id'] . "',
										 order_id = '" . (int)$order_id . "',
										 code = '" . uniqid() . "'");
						$prdw_id = $this->db->getLastId();

						//Generate draw code
						if($prdw_id){
							$prdw_code = 'C-'. (1000 + (int)$prdw_id);
							$this->db->query("UPDATE `" . DB_PREFIX . "prdw_customer_to_prize` SET code = '" . $prdw_code . "' WHERE id = '" . (int)$prdw_id . "'");
						}
					}
				}
			}
		}
	}


	// get order prodcuts
	public function getOrderProducts($order_id)
	{
		return $this->db->query("SELECT op.`product_id`, op.`name`, op.`price`, op.`quantity`, `image`
			FROM `" . DB_PREFIX . "order_product` op
			JOIN `" . DB_PREFIX . "product` ON product.product_id = op.product_id
			WHERE order_id='{$order_id}'")->rows;
	}

	public function getOrderOptions($order_id, $order_product_id=null) {
		$sql = "SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "'";

		if($order_product_id){
			$sql .= "AND order_product_id = '" . (int)$order_product_id . "'";
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getAbandedOrders()
	{
		$query=$this->db->query("SELECT `order`.* FROM `" . DB_PREFIX . "order`  WHERE payment_code='my_fatoorah' and order_status_id = 0 and payment_trackId IS NOT NULL");
        return $query->rows;
	}
	public function saveInvoiceId($order_id,$payment_trackId)
	{
		$current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET payment_trackId = '" . (int)$payment_trackId . "', date_modified = '". $current_date_time ."' WHERE order_id = '" . (int)$order_id . "'");

	}
	public function freeInvoice($order_id)
	{
		$current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET payment_trackId = NULL  , date_modified = '". $current_date_time ."' WHERE order_id = '" . (int)$order_id . "'");
	}
	public function check_invoiceid_with_orderid($order_id,$payment_trackId)
	{
		$query=$this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`  WHERE payment_code='my_fatoorah' and order_status_id = 0 and payment_trackId = '" . (int)$payment_trackId . "' and order_id = '" . (int)$order_id . "'");
        if ($query->row['total']>0) {
            return true;
        } else {
            return false;
        }
	}
	public function get_order_by_invoiceid($invoiceid)
	{
		$orderData = $this->db->query('SELECT order_id FROM ' . DB_PREFIX . '`order` as o WHERE o.payment_trackId="' . $invoiceid . '"');
        return $orderData->row;
	}
    public function update_status($order_id,$statusid)
	{
		$current_date_time = $this->ecdatetime->get_current_date_time_in_mysql_format();

		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$statusid . "', date_modified = "."'$current_date_time'"." WHERE order_id = '" . (int)$order_id . "'");

	}
    // generat order Invoice Number
    	public function createInvoiceNo($order_id) {
        $order_info = $this->getOrder($order_id);
		if($order_info && $order_info['invoice_no'] != 0){
			return $order_info['invoice_prefix'] . $order_info['invoice_no'];
		}

        if ($order_info && !$order_info['invoice_no']) {

			$query = $this->db->query("SELECT MAX(invoice_no) AS invoice_no FROM `" . DB_PREFIX . "order` WHERE invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "'");

            if ($query->row['invoice_no']) {
                $invoice_no = $query->row['invoice_no'] + 1;
            } else {
                $invoice_no = 1;
            }

            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET invoice_no = '" . (int) $invoice_no . "', invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "' WHERE order_id = '" . (int) $order_id . "'");

            return $order_info['invoice_prefix'] . $invoice_no;
        }
    }


	private function formatCustomInvoiceTemplateOrderData(&$order_info){
		if (!isset($order_info['order_id']))return;
		$this->load->model('localisation/language');
		$this->load->model('account/order');
		$showTotalQuantity = $this->config->get('config_invoice_display_total_quantity') ?? 0;
		$language_id=1;
		$languageCode="en";
		if ($order_info['language_id'])
			$language_id = $order_info['language_id'];
		$language=$this->model_localisation_language->getLanguage($language_id);
		if ($language && is_array($language) && isset($language['code']))
			$languageCode = $language['code'];
		$languageFile = new Language($languageCode);
		$languageFile->load_json('account/order');
		$products =$this->model_account_order->getOrderProductsForInvoice( $order_info['order_id'] , $language_id);
		$totalQuantity = 0;
		foreach ($products as $product){
			if ($showTotalQuantity == 1) {
				$totalQuantity += $product['quantity'];
			}
			$products[$key]['image'] = \Filesystem::getUrl('image/' .$product['image']);
		}
		$order_info['product'] = $products;
		$format = "";
		if ($order_info['shipping_company']) {
			$format .= $languageFile->get('text_company') . ': {company}' . "\n";
		}

		if ($order_info['shipping_address_1'] || $order_info['payment_address_1']) {
			$format .= $languageFile->get('text_address_1') . ': {address_1}' . "\n";
		}

		if ($order_info['shipping_address_2'] || $order_info['payment_address_2']) {
			$format .= $languageFile->get('text_address_2') . ': {address_2}' . "\n";
		}

		if ($order_info['shipping_city'] || $order_info['payment_city']) {
			$format .= $languageFile->get('text_city') . ': {city}' . "\n";
		}

		if ($order_info['shipping_postcode'] || $order_info['payment_postcode']) {
			$format .= $languageFile->get('text_postcode') . ': {postcode}' . "\n";
		}

		if ($order_info['shipping_area'] || $order_info['payment_area_id']) {
			$format .= $languageFile->get('text_area') . ': {area}' . "\n";
		}

		if ($order_info['shipping_zone'] || $order_info['payment_zone_id']) {
			$format .= $languageFile->get('text_zone') . ': {zone}' . "\n";
		}

		if ($order_info['shipping_country'] || $order_info['payment_country_id']) {
			$format .= $languageFile->get('text_country') . ': {country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['shipping_firstname'],
			'lastname'  => $order_info['shipping_lastname'],
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'zone_code' => $order_info['shipping_zone_code'],
			'country'   => $order_info['shipping_country']
		);
		$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
		$order_info['shipping_address'] = $shipping_address;
		if(!$order_info['payment_address']){
			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
			$order_info['payment_address'] = $payment_address;
		}
		if (!isset($order_info['totals'])){
			$total_data = $this->model_account_order->getOrderTotals($order_info['order_id']);
		}else{
			$total_data = $order_info['totals'];
		}
		$earnRewardsToMoney = '';
		foreach ($total_data as &$total) {

			if ($order_info['customer_id'] === 1 && $total['code'] === 'earn_reward') {
				$this->load->model('rewardpoints/helper');
				$pointsValue = $total['value'];
				$convertValue = $this->model_rewardpoints_helper->exchangePointToMoney($pointsValue);
				$earnRewardsToMoney = sprintf($languageFile->get('text_reward_balance'), (int) $pointsValue, (int) $pointsValue, $convertValue . $order_info['currency_code']);
			}

			if ( $total['code'] != 'tax' ){
				if ( $total['code'] == 'wkpos_discount' ){
					$total['title'] = $languageFile->get('wkpos_discount_title');
				}
			}

			if ($total['code'] == 'cffpm' ){
				$total['title']=$this->config->get('cffpm_total_row_name_'.$language['code']);
			}
		}
		if ($showTotalQuantity == 1) {
			$total_data[] = [
				'title' => $languageFile->get('column_total_quantity'),
				'text' => $totalQuantity
			];
		}
		$order_info['reward_money']=$earnRewardsToMoney;
		$order_info['total']=$total_data;
		$order_info['languageCode'] = $language['code'];
	}

	function log($type, $contents , $fileName=false)
	{
		if (!$fileName || empty($fileName))
			$fileName='coupon.log';

		$log = new Log($fileName);
		$log->write('[' . strtoupper($type) . '] ' . $contents);
	}

	public function getDateByCurrentTimeZone($date_added){
        $time_zone = $this->config->get('config_timezone') ?: 'UTC';
		$dateTime = new DateTime('now', new DateTimeZone($time_zone));
		return $dateTime->format("Y-m-d H:i:s");
    }
	public function updateOrderEmail($order_id, $email){
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `email` ='" . $this->db->escape($email) . "' WHERE order_id =".(int)$order_id);
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
        $hideOptionValue = $this->config->get('config_invoice_option_price') == 1;

        $query = [];
        $query[] = 'SELECT oo.*, pov.price, pov.price_prefix,pov.product_id';

        if ($hideOptionValue) {
            $query[] = ', ovd.name AS value, od.name as name';
        }
		// we make left join to get option of type text as text option don't have product_option_value record
        $query[] = 'FROM ' . DB_PREFIX . 'order_option AS oo';
        $query[] = 'LEFT JOIN ' . DB_PREFIX . 'product_option_value AS pov';
        $query[] = 'ON pov.product_option_value_id=oo.product_option_value_id';

        if ($hideOptionValue) {
            $query[] = 'INNER JOIN ' . DB_PREFIX . 'option_value_description AS ovd';
            $query[] = 'ON pov.option_value_id = ovd.option_value_id';
            $query[] = 'AND ovd.language_id = "' . (int) $printLang . '"';
            $query[] = 'INNER JOIN ' . DB_PREFIX . 'option_description AS od';
            $query[] = 'ON pov.option_id = od.option_id';
            $query[] = 'AND od.language_id = "' . (int) $printLang . '"';
        }

        $query[] = 'WHERE order_id = "' . (int)$orderId . '"';
        $query[] = 'AND order_product_id = "' . (int)$orderProductId . '"';

        $data = $this->db->query(implode(' ', $query));

        return $data->rows;
    }


	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getOrderPaymentMethod($order_id)
	{
		$payment_code = $this->db->query("SELECT payment_code FROM `order` WHERE order_id = " . (int)$order_id)->row['payment_code'];
		
		if ( $payment_code == 'cod' ) return 'Cash On Delivery';

		return $this->db->query("SELECT payment_method FROM payment_transactions WHERE order_id = " . (int)$order_id . " ORDER BY `created_at` DESC LIMIT 1")->row['payment_method'] ?? '';
	}


	/**
	 * @param $order
	 * @param array $productsIds
	 * @return bool|null
	 */
	public function isTestOrder($order , array $productsIds = []): ?bool
	{
		$isTestOrder = false;
		// if order variable  hold orderId then get the order
		if (!is_array($order))
			$order = $this->getOrder($order);

		if (!count($productsIds))
		{
			$orderProducts = $this->getOrderProducts($order['order_id'] ?? '0');
			$productsIds = array_column($orderProducts ?? [] , 'product_id');
		}

		if (!$order || !$productsIds)
			return false;


		// you add can add more functions which return boolean
		$conditions = [
			['method' => '_is_dummy_product_exists', 'params' => [$productsIds]],
			['method' => '_is_store_created_within_hours', 'params' => [4]],
			['method' => '_is_order_field_same_merchant', 'params' => [$order, 'email', 'config_email']],
			['method' => '_is_order_field_same_merchant', 'params' => [$order, 'telephone', 'config_telephone']],
			['method' => '_is_order_field_same_merchant', 'params' => [$order, 'ip', 'store_owner_ip']]
		];

		foreach ($conditions as $condition) {

			$method = $condition['method'] ?? '';
			$params = $condition['params'] ?? [];

			if (!method_exists($this, $method))
				continue;

			// calling the method
			try
			{
				$isTestOrder = $this->$method(...$params);
			}
			catch (Exception $exception) {}

			// if test order then break the loop (we test against (OR) condition)
			if ($isTestOrder)
				break;

		}

		return $isTestOrder;

	}

	/**
	 * @param $productIds
	 * @return bool|null
	 */
	private function _is_dummy_product_exists($productIds): ?bool
	{
		$isTestOrder = false;
		foreach ($productIds as $productId) {
			if ((int)$productId <= 355) {
				$isTestOrder = true;
				break;
			}
		}

		return $isTestOrder;
	}

	/**
	 * @param $order
	 * @return bool|null
	 */
	private function _is_order_field_same_merchant($order ,$orderField, $configField): ?bool
	{
		return
			(
				trim($this->config->get($configField))
				==
				trim($order[$orderField] ?? '')
			);
	}

	/**
	 * @param int $hours
	 * @return bool|null
	 */
	private function _is_store_created_within_hours($hours = 4): ?bool
	{
		$store_created_at = new \DateTime(STORE_CREATED_AT);
		$now = new \DateTime();
		$diffObj = $store_created_at->diff($now);
		$diffHours = $diffObj->h;
		$diffHours = $diffHours + ($diffObj->days * 24);
		return ($diffHours <= $hours);
	}


}
?>
