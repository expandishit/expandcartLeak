<?php 
class ControllerCheckoutManual extends Controller {
	public function index() {

		$new_products = null;

	    if (isset($this->request->post['formSource']) && $this->request->post['formSource'] == 'dashboard') {
            $this->language->load_json('checkout/dashboard');
        } else {
            $this->language->load_json('checkout/manual');
        }
		
		$json = array();
			
		$this->load->library('user');
		
		$this->user = new User($this->registry);
				
		if ($this->user->isLogged() && $this->user->hasPermission('modify', 'sale/order')) {	
			// Reset everything
			$this->cart->clear();
			$this->customer->logout();

            unset($this->session->data['stock_forecasting_cart']);			
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);			
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);

			// Settings
			$this->load->model('setting/setting');
			
			$settings = $this->model_setting_setting->getSetting('config', $this->request->post['store_id']);
			
			foreach ($settings as $key => $value) {
				$this->config->set($key, $value);
			}
			
			// Customer
			$this->load->model('account/signup');
			$register_login_by_phone_number =  $this->model_account_signup->isLoginRegisterByPhonenumber();
			
			if ($this->request->post['customer_id']) {
				$this->load->model('account/customer');

				$customer_info = $this->model_account_customer->getCustomer($this->request->post['customer_id']);
				
				if ($customer_info) {
					if($register_login_by_phone_number)
						$this->customer->login($customer_info['telephone'], '', false,true);
					else
						$this->customer->login($customer_info['email'], '', true,true);
					$this->cart->clear();
				} else {
					$json['error']['customer'] = $this->language->get('error_customer');
				}
			} else {
				// Customer Group
				$this->config->set('config_customer_group_id', $this->request->post['customer_group_id']);
			}
	
			// Product
			$this->load->model('catalog/product');

			if ( isset($this->request->post['order_product']) )
			{
				$i= 1;
				foreach ( $this->request->post['order_product'] as $order_product )
				{

					$product_info = $this->model_catalog_product->getProduct($order_product['product_id']);
				
					if ( $product_info || $order_product['product_status'] == 0 )
					{
						$option_data = array();
						
						if ( isset($order_product['order_option']) )
						{
							foreach ( $order_product['order_option'] as $option )
							{
								if ( $option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'image' )
								{ 
									$option_data[$option['product_option_id']] = $option['product_option_value_id'];
								}
								elseif ( $option['type'] == 'checkbox' )
								{
									$option_data[$option['product_option_id']][] = $option['product_option_value_id'];
								}
								elseif ( $option['type'] == 'text' || $option['type'] == 'textarea' || $option['type'] == 'file' || $option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time' )
								{
									$option_data[$option['product_option_id']] = $option['value'];						
								}
							}
						}

						$this->cart->add($order_product['product_id'], $order_product['quantity'], $option_data);
					}

					//product_option_value_id
					$new_products[$i]['old'] = $order_product['old'];
					$new_products[$i]['id'] = $order_product['product_id'];
					$new_products[$i]['option_id'] = $option['product_option_id'];
					$new_products[$i]['option_value_id'] = $option['product_option_value_id'];

					$i++;
				}

				$this->session->data['new_products'] = $new_products;
			}

			if ( isset($this->request->post['product_id']) )
			{
				$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);
				
				if ( $product_info )
				{

					$quantity = $this->request->post['quantity'] ?: 1;
					$option = $this->request->post['option'] ? array_filter($this->request->post['option']) : array();
					$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

					foreach ( $product_options as $product_option )
					{
						if ( $product_option['required'] && empty($option[$product_option['product_option_id']]) )
						{
							$json['error']['product']['option'][$product_option['product_option_id']] = sprintf( $this->language->get('error_required'), $product_option['name'] );
						}
					}
					
					if (!isset($json['error']['product']['option'])) {
						$this->cart->add($this->request->post['product_id'], $quantity, $option);
					}
				}
			}

			Cart::$product_status = 0;

			// grab the old order products.
			// put all original order's products' ids in an array called old_products_ids.

			$old_products_ids = array();

			if ( isset( $this->request->post['order_id'] ) )
			{
				$order_id = $this->request->post['order_id'];
				$order_info = ($order_id > 0)? $this->model_account_order->getOrder($order_id) : null;
				
				$this->load->model('account/order');

				$old_order_products = $this->model_account_order->getOrderProducts( $order_id );

				if ( $old_order_products )
				{
					foreach ( $old_order_products as $old_product )
					{
						$old_products_ids[] = (int) $old_product['product_id'];
					}
				}
			}


			// set manual order id
			Cart::$manual_order_id = $this->request->post['order_id'];

			// Stock
			$pstock = $this->cart->hasStock( true, $old_products_ids );

			if ($pstock !== true && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$json['error']['product']['stock'] = $this->language->get('error_stock');
				$json['error']['product_has_error'] = $pstock;
			}		
			
			// Tax
			if ($this->cart->hasShipping()) {
				$this->tax->setShippingAddress($this->request->post['shipping_country_id'], $this->request->post['shipping_zone_id'], $this->request->post['shipping_area_id']);
			} else {
				$this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'), $this->config->get('config_area_id'));
			}
			
			$this->tax->setPaymentAddress($this->request->post['payment_country_id'], $this->request->post['payment_zone_id'], $this->request->post['payment_area_id']);				
			$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'), $this->config->get('config_area_id'));	
						
			// Products
			$json['order_product'] = array();

			$products = $this->cart->getProducts();

			Cart::$product_status = 1;
			
			foreach ($products as $product) {
				$product_total = 0;
					
				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}	
								
				if ($product['minimum'] > $product_total) {
					$json['error']['product']['minimum'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}	
								
				$option_data = array();

				foreach ($product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['option_value'],
						'type'                    => $option['type']
					);
				}
		
				$download_data = array();
				
				foreach ($product['download'] as $download) {
					$download_data[] = array(
						'name'      => $download['name'],
						'filename'  => $download['filename'],
						'mask'      => $download['mask'],
						'remaining' => $download['remaining']
					);
				}

				$json['order_product'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'], 
					'option'     => $option_data,
					'download'   => $download_data,
					'quantity'   => $product['quantity'],
					'stock'      => $product['stock'],
					'price'      => $product['price'],	
					'total'      => $product['total'],	
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward'],
					'added_by_user_type' => $this->model_catalog_product->getOrderProductAddedByUserType(Cart::$manual_order_id, $product['product_id']) ?: 'admin'
				);
			}
			
			// Voucher
			$this->session->data['vouchers'] = array();
			
			if (isset($this->request->post['order_voucher'])) {
				foreach ($this->request->post['order_voucher'] as $voucher) {
					$this->session->data['vouchers'][] = array(
						'voucher_id'       => $voucher['voucher_id'],
						'description'      => $voucher['description'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'], 
						'message'          => $voucher['message'],
						'amount'           => $voucher['amount']    
					);
				}
			}

			// Add a new voucher if set
			if ( utf8_strlen($this->request->post['from_email']) > 0)
			{
				if ((utf8_strlen($this->request->post['from_name']) < 1) || (utf8_strlen($this->request->post['from_name']) > 64)) {
					$json['error']['vouchers']['from_name'] = $this->language->get('error_from_name');
				}  
			
				if ((utf8_strlen($this->request->post['from_email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['from_email'])) {
					$json['error']['vouchers']['from_email'] = $this->language->get('error_email');
				}
			
				if ((utf8_strlen($this->request->post['to_name']) < 1) || (utf8_strlen($this->request->post['to_name']) > 64)) {
					$json['error']['vouchers']['to_name'] = $this->language->get('error_to_name');
				}       
			
				if ((utf8_strlen($this->request->post['to_email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['to_email'])) {
					$json['error']['vouchers']['to_email'] = $this->language->get('error_email');
				}
			
				if (($this->request->post['amount'] < 1) || ($this->request->post['amount'] > 1000)) {
					$json['error']['vouchers']['amount'] = sprintf($this->language->get('error_amount'), $this->currency->format(1, false, 1), $this->currency->format(1000, false, 1) . ' ' . $this->config->get('config_currency'));
				}
			
				if (!isset($json['error']['vouchers'])) { 
					$voucher_data = array(
						'order_id'         => 0,
						'code'             => substr(md5(mt_rand()), 0, 10),
						'from_name'        => $this->request->post['from_name'],
						'from_email'       => $this->request->post['from_email'],
						'to_name'          => $this->request->post['to_name'],
						'to_email'         => $this->request->post['to_email'],
						'voucher_theme_id' => $this->request->post['voucher_theme_id'], 
						'message'          => $this->request->post['message'],
						'amount'           => $this->request->post['amount'],
						'status'           => true             
					); 
					
					$this->load->model('checkout/voucher');
					
					$voucher_id = $this->model_checkout_voucher->addVoucher(0, $voucher_data);  
									
					$this->session->data['vouchers'][] = array(
						'voucher_id'       => $voucher_id,
						'description'      => sprintf($this->language->get('text_for'), $this->currency->format($this->request->post['amount'], $this->config->get('config_currency')), $this->request->post['to_name']),
						'code'             => substr(md5(mt_rand()), 0, 10),
						'from_name'        => $this->request->post['from_name'],
						'from_email'       => $this->request->post['from_email'],
						'to_name'          => $this->request->post['to_name'],
						'to_email'         => $this->request->post['to_email'],
						'voucher_theme_id' => $this->request->post['voucher_theme_id'], 
						'message'          => $this->request->post['message'],
						'amount'           => $this->request->post['amount']            
					); 
				}
			}
			
			$json['order_voucher'] = array();
					
			foreach ($this->session->data['vouchers'] as $voucher) {
				$json['order_voucher'][] = array(
					'voucher_id'       => $voucher['voucher_id'],
					'description'      => $voucher['description'],
					'code'             => $voucher['code'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'], 
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount']    
				);
			}
						
			$this->load->model('setting/extension');
			
			$this->load->model('localisation/country');
		
			$this->load->model('localisation/zone');
                        
			$this->load->model('localisation/area');
			
			// Shipping
			$json['shipping_method'] = array();
			
			if ($this->cart->hasShipping()) {

				$this->load->model('localisation/country');
				
				$country_info = $this->model_localisation_country->getCountry($this->request->post['shipping_country_id']);
				
				if ( $this->request->post['formSource'] != 'dashboard'  )
				{
					if ( ($country_info && $country_info['postcode_required'] && (utf8_strlen($this->request->post['shipping_postcode']) < 2) || (utf8_strlen($this->request->post['shipping_postcode']) > 10)))
					{
						$json['error']['shipping']['postcode'] = $this->language->get('error_postcode');
					}
			
					if ($this->request->post['shipping_country_id'] == '') {
						$json['error']['shipping']['country'] = $this->language->get('error_country');
					}
					
					if (!isset($this->request->post['shipping_zone_id']) || $this->request->post['shipping_zone_id'] == '') {
						$json['error']['shipping']['zone'] = $this->language->get('error_zone');
					}
								
					$this->load->model('localisation/country');
					
					$country_info = $this->model_localisation_country->getCountry($this->request->post['shipping_country_id']);
					
					if ($country_info && $country_info['postcode_required'] && (utf8_strlen($this->request->post['shipping_postcode']) < 2) || (utf8_strlen($this->request->post['shipping_postcode']) > 10)) {
						$json['error']['shipping']['postcode'] = $this->language->get('error_postcode');
					}
				}

				if (!isset($json['error']['shipping'])) {
					if ($country_info) {
						$country = $country_info['name'];
						$iso_code_2 = $country_info['iso_code_2'];
						$iso_code_3 = $country_info['iso_code_3'];
						$address_format = $country_info['address_format'];
					} else {
						$country = '';
						$iso_code_2 = '';
						$iso_code_3 = '';	
						$address_format = '';
					}
				
					$zone_info = $this->model_localisation_zone->getZone($this->request->post['shipping_zone_id']);
                                        
					$area_info = $this->model_localisation_area->getarea($this->request->post['shipping_area_id']);
					
					if ($zone_info) {
						$zone = $zone_info['name'];
						$zone_code = $zone_info['code'];
					} else {
						$zone = '';
						$zone_code = '';
					}
                                        
					if ($area_info) {
						$area = $area_info['name'];
					} else {
						$area = '';
					}					
	
					$address_data = array(
						'firstname'      => $this->request->post['shipping_firstname'],
						'lastname'       => $this->request->post['shipping_lastname'],
						'company'        => $this->request->post['shipping_company'],
						'address_1'      => $this->request->post['shipping_address_1'],
						'address_2'      => $this->request->post['shipping_address_2'],
						'postcode'       => $this->request->post['shipping_postcode'],
						'city'           => $this->request->post['shipping_city'],
						'zone_id'        => $this->request->post['shipping_zone_id'],
						'zone'           => $zone,
						'area_id'        => $this->request->post['shipping_area_id'],
						'area'           => $area,
						'zone_code'      => $zone_code,
						'country_id'     => $this->request->post['shipping_country_id'],
						'country'        => $country,	
						'iso_code_2'     => $iso_code_2,
						'iso_code_3'     => $iso_code_3,
						'address_format' => $address_format
					);
					
					$results = $this->model_setting_extension->getExtensions('shipping');

					foreach ($results as $result) {
						// Check for dhl_express code as it special case here
						$code = $result['code'] == 'hitdhlexpress' ? 'dhl_express' : $result['code'];

                        $settingGroup = $this->config->get($code);
                        $status = null;
                        if ($settingGroup && is_array($settingGroup) === true) {
                            $status = $settingGroup['status'];
                        } else {
                            $status = $this->config->get($code . '_status');
                        }

						if ($status == 1 && $result['code']!='seller_based') {
							$this->load->model('shipping/' . $code);
							
							$quote = $this->{'model_shipping_' . $code}->getQuote($address_data);

							if ($quote) {
								$json['shipping_method'][$result['code']] = array( 
									'title'      => $quote['title'],
									'quote'      => $quote['quote'], 
									'sort_order' => $quote['sort_order'],
									'error'      => $quote['error']
								);
							}
						}
					}
			
					$sort_order = array();
				  
					foreach ($json['shipping_method'] as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $json['shipping_method']);

					if (!$json['shipping_method']) {
						$json['error']['shipping_method'] = $this->language->get('error_no_shipping');
					} elseif ($this->request->post['shipping_code']) {
						$shipping = explode('.', $this->request->post['shipping_code']);
						
						if (!isset($shipping[0]) || !isset($shipping[1])) {
							$json['error']['shipping_method'] = $this->language->get('error_shipping');
						} else {
							$this->session->data['shipping_method'] = $json['shipping_method'][$shipping[0]]['quote'][$shipping[1]];
						}				
					}					
				}
			}



			// Totals
			$json['order_total'] = array();					
			$total = 0;
			$taxes = $this->cart->getTaxes();
			$this->session->data['coupon'] = $this->request->post['coupon'];
			$sort_order = array(); 
			
			$results = $this->model_setting_extension->getExtensions('total');
			
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}
			
			array_multisort($sort_order, SORT_ASC, $results);
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);
		
					$this->{'model_total_' . $result['code']}->getTotal($json['order_total'], $total, $taxes);
				}
				
				$sort_order = array(); 
			  
				foreach ($json['order_total'] as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}
	
				array_multisort($sort_order, SORT_ASC, $json['order_total']);				
			}
            if($total == 0){
                $json['error']['product'] = $this->language->get('error_no_products');
            }

			// Coupon
			$this->load->model('checkout/coupon');

			if(!empty($this->request->post['coupon'])){
				
				$current_coupon = $this->model_checkout_coupon->getActiveCouponDetails($this->request->post['coupon']);
				if($current_coupon != null){
					$apply_coupons[$current_coupon['code']] = $current_coupon;
					$delete_condition = '';
				}
				else
					$json['error']['coupon'] = $this->language->get('error_coupon');
			}
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE order_id = '" . (int)$this->db->escape($this->request->post['order_id']) . "' ".$delete_condition);
			foreach($json['order_total'] as $total_item){
				if($total_item['code'] == 'coupon'){
					$code = '';
				
					$start = strpos($order_total['title'], '(') + 1;
					$end = strrpos($order_total['title'], ')');

					if ($start && $end) {
						$total_coupon_code = substr($order_total['title'], $start, $end - $start);
						$coupon_info = $apply_coupons[$total_coupon_code];
						$couponAmount = $total_item['value'];
						$this->model_checkout_coupon->redeem($coupon_info['coupon_id'],$this->request->post['order_id'] ,$this->request->post['order_id']['customer_id'],$couponAmount);
					}
				}
			}

			// Voucher
			if (!empty($this->request->post['voucher'])) {
				$this->load->model('checkout/voucher');
			
				$voucher_info = $this->model_checkout_voucher->getVoucher($this->request->post['voucher']);			
			
				if ($voucher_info) {					
					$this->session->data['voucher'] = $this->request->post['voucher'];
				} else {
					$json['error']['voucher'] = $this->language->get('error_voucher');
				}
			}
						
			// Reward Points
			if (!empty($this->request->post['reward'])) {
				$points = $this->customer->getRewardPoints();
				
				if ($this->request->post['reward'] > $points) {
					$json['error']['reward'] = sprintf($this->language->get('error_points'), $this->request->post['reward']);
				}
				
				if (!isset($json['error']['reward'])) {
					$points_total = 0;
					
					foreach ($this->cart->getProducts() as $product) {
						if ($product['points']) {
							$points_total += $product['points'];
						}
					}				
					
					if ($this->request->post['reward'] > $points_total) {
						$json['error']['reward'] = sprintf($this->language->get('error_maximum'), $points_total);
					}
					
					if (!isset($json['error']['reward'])) {		
						$this->session->data['reward'] = $this->request->post['reward'];
					}
				}
			}

		
		
			// Payment
			if ($this->request->post['payment_country_id'] == '') {
				$json['error']['payment']['country'] = $this->language->get('error_country');
			}
			
			if (!isset($this->request->post['payment_zone_id']) || $this->request->post['payment_zone_id'] == '') {
				$json['error']['payment']['zone'] = $this->language->get('error_zone');
			}		
			
			if (!isset($json['error']['payment'])) {
				$json['payment_methods'] = array();
				
				$country_info = $this->model_localisation_country->getCountry($this->request->post['payment_country_id']);
				
				if ($country_info) {
					$country = $country_info['name'];
					$iso_code_2 = $country_info['iso_code_2'];
					$iso_code_3 = $country_info['iso_code_3'];
					$address_format = $country_info['address_format'];
				} else {
					$country = '';
					$iso_code_2 = '';
					$iso_code_3 = '';	
					$address_format = '';
				}
				
				$zone_info = $this->model_localisation_zone->getZone($this->request->post['payment_zone_id']);
				
				if ($zone_info) {
					$zone = $zone_info['name'];
					$zone_code = $zone_info['code'];
				} else {
					$zone = '';
					$zone_code = '';
				}					
				
				$address_data = array(
					'firstname'      => $this->request->post['payment_firstname'],
					'lastname'       => $this->request->post['payment_lastname'],
					'company'        => $this->request->post['payment_company'],
					'address_1'      => $this->request->post['payment_address_1'],
					'address_2'      => $this->request->post['payment_address_2'],
					'postcode'       => $this->request->post['payment_postcode'],
					'city'           => $this->request->post['payment_city'],
					'zone_id'        => $this->request->post['payment_zone_id'],
					'zone'           => $zone,
					'zone_code'      => $zone_code,
					'country_id'     => $this->request->post['payment_country_id'],
					'country'        => $country,	
					'iso_code_2'     => $iso_code_2,
					'iso_code_3'     => $iso_code_3,
					'address_format' => $address_format
				);
				
				$json['payment_method'] = array();
								
				$results = $this->model_setting_extension->getExtensions('payment');
		
				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status') || $this->config->get($result['code'])['status']) {
						$this->load->model('payment/' . $result['code']);
						
						$method = $this->{'model_payment_' . $result['code']}->getMethod($address_data, $total); 
						
						if ($method) {
							$json['payment_method'][$result['code']] = $method;
						}
					}
				}
							 
				$sort_order = array(); 
			  
				foreach ($json['payment_method'] as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}
		
				array_multisort($sort_order, SORT_ASC, $json['payment_method']);

				if (!$json['payment_method'] || $total === 0) {
					// $json['error']['payment_method'] = $this->language->get('error_no_payment');
				} elseif ($this->request->post['payment_code']) {
					if (!isset($json['payment_method'][$this->request->post['payment_code']])) {
						$json['error']['payment_method'] = $this->language->get('error_payment');
					}
				}
			}
			
			if (!isset($json['error'])) { 
				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error']['warning'] = $this->language->get('error_warning');
			}
			
			// Reset everything
			$this->cart->clear();
			$this->customer->logout();
			Cart::$manual_order_id = 0;
			
            unset($this->session->data['stock_forecasting_cart']);			
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
		} else {
      		$json['error']['warning'] = $this->language->get('error_permission');
		}
	
		$this->response->setOutput(json_encode($json));	
	}
}
?>
