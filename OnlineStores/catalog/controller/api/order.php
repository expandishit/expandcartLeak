<?php
class ControllerApiOrder extends Controller {
	public function add() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Customer
			if (!isset($this->session->data['customer'])) {
				$json['error'] = $this->language->get('error_customer');
			}

			// Payment Address
			if (!isset($this->session->data['payment_address'])) {
				$json['error'] = $this->language->get('error_payment_address');
			}

			// Payment Method
			if (!isset($this->session->data['payment_method'])) {
				$json['error'] = $this->language->get('error_payment_method');
			}

			// Shipping
			if ($this->cart->hasShipping()) {
				// Shipping Address
				if (!isset($this->session->data['shipping_address'])) {
					$json['error'] = $this->language->get('error_shipping_address');
				}

				// Shipping Method
				if (!isset($this->request->post['shipping_method'])) {
					$json['error'] = $this->language->get('error_shipping_method');
				}
			} else {
				unset($this->session->data['shipping_address']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
			}

			// Cart
			if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
				$json['error'] = $this->language->get('error_stock');
			}

			// Validate minimum quantity requirements.
			$products = $this->cart->getProducts();

			foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$json['error'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);

					break;
				}
			}

			if (!$json) {
				$order_data = array();

				// Store Details
				$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
				$order_data['store_id'] = $this->config->get('config_store_id');
				$order_data['store_name'] = $this->config->get('config_name');
				$order_data['store_url'] = $this->config->get('config_url');

				// Customer Details
				$order_data['customer_id'] = $this->session->data['customer']['customer_id'];
				$order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
				$order_data['firstname'] = $this->session->data['customer']['firstname'];
				$order_data['lastname'] = $this->session->data['customer']['lastname'];
				$order_data['email'] = $this->session->data['customer']['email'];
				$order_data['telephone'] = $this->session->data['customer']['telephone'];
				$order_data['fax'] = $this->session->data['customer']['fax'];
				$order_data['custom_field'] = $this->session->data['customer']['custom_field'];

				// Payment Details
				$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
				$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
				$order_data['payment_company'] = $this->session->data['payment_address']['company'];
				$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
				$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
				$order_data['payment_city'] = $this->session->data['payment_address']['city'];
				$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
				$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
				$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
				$order_data['payment_country'] = $this->session->data['payment_address']['country'];
				$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
				$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
				$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

				if (isset($this->session->data['payment_method']['title'])) {
					$order_data['payment_method'] = $this->session->data['payment_method']['title'];
				} else {
					$order_data['payment_method'] = '';
				}

				if (isset($this->session->data['payment_method']['code'])) {
					$order_data['payment_code'] = $this->session->data['payment_method']['code'];
				} else {
					$order_data['payment_code'] = '';
				}

				// Shipping Details
				if ($this->cart->hasShipping()) {
					$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
					$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
					$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
					$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
					$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
					$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
					$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
					$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
					$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
					$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
					$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
					$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
					$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

					if (isset($this->session->data['shipping_method']['title'])) {
						$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
					} else {
						$order_data['shipping_method'] = '';
					}

					if (isset($this->session->data['shipping_method']['code'])) {
						$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
					} else {
						$order_data['shipping_code'] = '';
					}
				} else {
					$order_data['shipping_firstname'] = '';
					$order_data['shipping_lastname'] = '';
					$order_data['shipping_company'] = '';
					$order_data['shipping_address_1'] = '';
					$order_data['shipping_address_2'] = '';
					$order_data['shipping_city'] = '';
					$order_data['shipping_postcode'] = '';
					$order_data['shipping_zone'] = '';
					$order_data['shipping_zone_id'] = '';
					$order_data['shipping_country'] = '';
					$order_data['shipping_country_id'] = '';
					$order_data['shipping_address_format'] = '';
					$order_data['shipping_custom_field'] = array();
					$order_data['shipping_method'] = '';
					$order_data['shipping_code'] = '';
				}

				// Products
				$order_data['products'] = array();

				foreach ($this->cart->getProducts() as $product) {
					$option_data = array();

					foreach ($product['option'] as $option) {
						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $option['value'],
							'type'                    => $option['type']
						);
					}

					$order_data['products'][] = array(
						'product_id' => $product['product_id'],
						'name'       => $product['name'],
						'model'      => $product['model'],
						'option'     => $option_data,
						'download'   => $product['download'],
						'quantity'   => $product['quantity'],
						'subtract'   => $product['subtract'],
						'price'      => $product['price'],
                        'currency'    => $this->currency->getCode(),
                        'total'      => $product['total'],
						'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
						'reward'     => $product['reward']
					);
				}

				// Gift Voucher
				$order_data['vouchers'] = array();

				if (!empty($this->session->data['vouchers'])) {
					foreach ($this->session->data['vouchers'] as $voucher) {
						$order_data['vouchers'][] = array(
							'description'      => $voucher['description'],
							'code'             => substr(md5(mt_rand()), 0, 10),
							'to_name'          => $voucher['to_name'],
							'to_email'         => $voucher['to_email'],
							'from_name'        => $voucher['from_name'],
							'from_email'       => $voucher['from_email'],
							'voucher_theme_id' => $voucher['voucher_theme_id'],
							'message'          => $voucher['message'],
							'amount'           => $voucher['amount']
						);
					}
				}

				// Order Totals
				$this->load->model('extension/extension');

				$order_data['totals'] = array();
				$total = 0;
				$taxes = $this->cart->getTaxes();

				$sort_order = array();

				$results = $this->model_extension_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status')) {
						$this->load->model('total/' . $result['code']);

						$this->{'model_total_' . $result['code']}->getTotal($order_data['totals'], $total, $taxes);
					}
				}

				$sort_order = array();

				foreach ($order_data['totals'] as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $order_data['totals']);

				if (isset($this->request->post['comment'])) {
					$order_data['comment'] = $this->request->post['comment'];
				} else {
					$order_data['comment'] = '';
				}

				$order_data['total'] = $total;

				if (isset($this->request->post['affiliate_id'])) {
					$subtotal = $this->cart->getSubTotal();

					// Affiliate
					$this->load->model('affiliate/affiliate');

					$affiliate_info = $this->model_affiliate_affiliate->getAffiliate($this->request->post['affiliate_id']);

					if ($affiliate_info) {
						$order_data['affiliate_id'] = $affiliate_info['affiliate_id'];
						$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
					} else {
						$order_data['affiliate_id'] = 0;
						$order_data['commission'] = 0;
					}

					// Marketing
					$order_data['marketing_id'] = 0;
					$order_data['tracking'] = '';
				} else {
					$order_data['affiliate_id'] = 0;
					$order_data['commission'] = 0;
					$order_data['marketing_id'] = 0;
					$order_data['tracking'] = '';
				}

				$order_data['language_id'] = $this->config->get('config_language_id');
				$order_data['currency_id'] = $this->currency->getId();
				$order_data['currency_code'] = $this->currency->getCode();
				$order_data['currency_value'] = $this->currency->getValue($this->currency->getCode());
				$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

				if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
				} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
				} else {
					$order_data['forwarded_ip'] = '';
				}

				if (isset($this->request->server['HTTP_USER_AGENT'])) {
					$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
				} else {
					$order_data['user_agent'] = '';
				}

				if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
					$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
				} else {
					$order_data['accept_language'] = '';
				}
				
				$this->load->model('checkout/order');
	
				$json['order_id'] = $this->model_checkout_order->addOrder($order_data);
	
				// Set the order history
				if (isset($this->request->post['order_status_id'])) {
					$order_status_id = $this->request->post['order_status_id'];
				} else {
					$order_status_id = $this->config->get('config_order_status_id');
				}
	
				$this->model_checkout_order->update($json['order_id'], $order_status_id);
	
				$json['success'] = $this->language->get('text_success');				
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('checkout/order');

			if (isset($order_id)) {
				$order_id = $order_id;
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				// Customer
				if (!isset($this->session->data['customer'])) {
					$json['error'] = $this->language->get('error_customer');
				}

				// Payment Address
				if (!isset($this->session->data['payment_address'])) {
					$json['error'] = $this->language->get('error_payment_address');
				}

				// Payment Method
				if (!isset($this->session->data['payment_method'])) {
					$json['error'] = $this->language->get('error_payment_method');
				}

				// Shipping
				if ($this->cart->hasShipping()) {
					// Shipping Address
					if (!isset($this->session->data['shipping_address'])) {
						$json['error'] = $this->language->get('error_shipping_address');
					}

					// Shipping Method
					if (!isset($this->request->post['shipping_method'])) {
						$json['error'] = $this->language->get('error_shipping_method');
					}
				} else {
					unset($this->session->data['shipping_address']);
					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
				}

				// Cart
				if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
					$json['error'] = $this->language->get('error_stock');
				}

				// Validate minimum quantity requirements.
				$products = $this->cart->getProducts();

				foreach ($products as $product) {
					$product_total = 0;

					foreach ($products as $product_2) {
						if ($product_2['product_id'] == $product['product_id']) {
							$product_total += $product_2['quantity'];
						}
					}

					if ($product['minimum'] > $product_total) {
						$json['error'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);

						break;
					}
				}

				if (!$json) {
					$order_data = array();

					// Store Details
					$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
					$order_data['store_id'] = $this->config->get('config_store_id');
					$order_data['store_name'] = $this->config->get('config_name');
					$order_data['store_url'] = $this->config->get('config_url');

					// Customer Details
					$order_data['customer_id'] = $this->session->data['customer']['customer_id'];
					$order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
					$order_data['firstname'] = $this->session->data['customer']['firstname'];
					$order_data['lastname'] = $this->session->data['customer']['lastname'];
					$order_data['email'] = $this->session->data['customer']['email'];
					$order_data['telephone'] = $this->session->data['customer']['telephone'];
					$order_data['fax'] = $this->session->data['customer']['fax'];
					$order_data['custom_field'] = $this->session->data['customer']['custom_field'];

					// Payment Details
					$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
					$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
					$order_data['payment_company'] = $this->session->data['payment_address']['company'];
					$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
					$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
					$order_data['payment_city'] = $this->session->data['payment_address']['city'];
					$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
					$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
					$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
					$order_data['payment_country'] = $this->session->data['payment_address']['country'];
					$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
					$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
					$order_data['payment_custom_field'] = $this->session->data['payment_address']['custom_field'];

					if (isset($this->session->data['payment_method']['title'])) {
						$order_data['payment_method'] = $this->session->data['payment_method']['title'];
					} else {
						$order_data['payment_method'] = '';
					}

					if (isset($this->session->data['payment_method']['code'])) {
						$order_data['payment_code'] = $this->session->data['payment_method']['code'];
					} else {
						$order_data['payment_code'] = '';
					}

					// Shipping Details
					if ($this->cart->hasShipping()) {
						$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
						$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
						$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
						$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
						$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
						$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
						$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
						$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
						$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
						$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
						$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
						$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
						$order_data['shipping_custom_field'] = $this->session->data['shipping_address']['custom_field'];

						if (isset($this->session->data['shipping_method']['title'])) {
							$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
						} else {
							$order_data['shipping_method'] = '';
						}

						if (isset($this->session->data['shipping_method']['code'])) {
							$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
						} else {
							$order_data['shipping_code'] = '';
						}
					} else {
						$order_data['shipping_firstname'] = '';
						$order_data['shipping_lastname'] = '';
						$order_data['shipping_company'] = '';
						$order_data['shipping_address_1'] = '';
						$order_data['shipping_address_2'] = '';
						$order_data['shipping_city'] = '';
						$order_data['shipping_postcode'] = '';
						$order_data['shipping_zone'] = '';
						$order_data['shipping_zone_id'] = '';
						$order_data['shipping_country'] = '';
						$order_data['shipping_country_id'] = '';
						$order_data['shipping_address_format'] = '';
						$order_data['shipping_custom_field'] = array();
						$order_data['shipping_method'] = '';
						$order_data['shipping_code'] = '';
					}

					// Products
					$order_data['products'] = array();

					foreach ($this->cart->getProducts() as $product) {
						$option_data = array();

						foreach ($product['option'] as $option) {
							$option_data[] = array(
								'product_option_id'       => $option['product_option_id'],
								'product_option_value_id' => $option['product_option_value_id'],
								'option_id'               => $option['option_id'],
								'option_value_id'         => $option['option_value_id'],
								'name'                    => $option['name'],
								'value'                   => $option['value'],
								'type'                    => $option['type']
							);
						}

						$order_data['products'][] = array(
							'product_id' => $product['product_id'],
							'name'       => $product['name'],
							'model'      => $product['model'],
							'option'     => $option_data,
							'download'   => $product['download'],
							'quantity'   => $product['quantity'],
							'subtract'   => $product['subtract'],
							'price'      => $product['price'],
							'currency'   => $this->currency->getCode(),
							'total'      => $product['total'],
							'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
							'reward'     => $product['reward']
						);
					}

					// Gift Voucher
					$order_data['vouchers'] = array();

					if (!empty($this->session->data['vouchers'])) {
						foreach ($this->session->data['vouchers'] as $voucher) {
							$order_data['vouchers'][] = array(
								'description'      => $voucher['description'],
								'code'             => substr(md5(mt_rand()), 0, 10),
								'to_name'          => $voucher['to_name'],
								'to_email'         => $voucher['to_email'],
								'from_name'        => $voucher['from_name'],
								'from_email'       => $voucher['from_email'],
								'voucher_theme_id' => $voucher['voucher_theme_id'],
								'message'          => $voucher['message'],
								'amount'           => $voucher['amount']
							);
						}
					}

					// Order Totals
					$this->load->model('extension/extension');

					$order_data['totals'] = array();
					$total = 0;
					$taxes = $this->cart->getTaxes();

					$sort_order = array();

					$results = $this->model_extension_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get($result['code'] . '_status')) {
							$this->load->model('total/' . $result['code']);

							$this->{'model_total_' . $result['code']}->getTotal($order_data['totals'], $total, $taxes);
						}
					}

					$sort_order = array();

					foreach ($order_data['totals'] as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $order_data['totals']);

					if (isset($this->request->post['comment'])) {
						$order_data['comment'] = $this->request->post['comment'];
					} else {
						$order_data['comment'] = '';
					}

					$order_data['total'] = $total;

                    $affiliateAppIsActive = (
                        \Extension::isInstalled('affiliates') && $this->config->get('affiliates')['status']
                    );

					if (isset($this->request->post['affiliate_id']) && $affiliateAppIsActive) {
						$subtotal = $this->cart->getSubTotal();

						// Affiliate
						$this->load->model('affiliate/affiliate');

						$affiliate_info = $this->model_affiliate_affiliate->getAffiliate($this->request->post['affiliate_id']);

						if ($affiliate_info) {
							$order_data['affiliate_id'] = $affiliate_info['affiliate_id'];
							$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
						} else {
							$order_data['affiliate_id'] = 0;
							$order_data['commission'] = 0;
						}
					} else {
						$order_data['affiliate_id'] = 0;
						$order_data['commission'] = 0;
					}

					$this->model_checkout_order->editOrder($order_id, $order_data);

					// Set the order history
					if (isset($this->request->post['order_status_id'])) {
						$order_status_id = $this->request->post['order_status_id'];
					} else {
						$order_status_id = $this->config->get('config_order_status_id');
					}

					$this->model_checkout_order->update($order_id, $order_status_id);

					$json['success'] = $this->language->get('text_success');
				}
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function delete() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('checkout/order');

			if (isset($order_id)) {
				$order_id = $order_id;
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$this->model_checkout_order->deleteOrder($order_id);

				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function history() {
		$this->load->language('api/order');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			// Add keys for missing post vars
			$keys = array(
				'order_status_id',
				'notify',
				'append',
				'comment'
			);

			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}

			$this->load->model('checkout/order');

			if (isset($order_id)) {
				$order_id = $order_id;
			} else {
				$order_id = 0;
			}

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$this->model_checkout_order->update($order_id, $this->request->post['order_status_id'], $this->request->post['comment'], $this->request->post['notify']);

				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getList() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $with_products_details = $params->with_products_details ?? 0;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->language->load('account/order');
                $this->load->model('account/order');
                $this->load->model('catalog/product');
                $json['orders'] = array();

                $results = $this->model_account_order->getOrders(0, 100);

                foreach ($results as $result) {
                    $product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
                    $voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

				if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') 
				    && $this->config->get('trips')['status']==1) &&$params->trips==1) 
				 {
					 $this->load->model('multiseller/seller');
					 $this->load->model('module/trips');
					 $products = $this->model_account_order->getOrderProductsTrips($result['order_id'],$params->trips_date);
					 foreach ($products as $product) {
						$product_info = $this->model_catalog_product->getProduct($product['product_id']);
						$tripData= $this->model_module_trips->getTripProduct($product_info['product_id']); 
						 $json['orders'][] = array(
							 'order_id'   => $result['order_id'],
							 'name' => $product['name'],
							 'image' => \Filesystem::getUrl('image/'.$product_info['image']),
							 'reservation' => $product['quantity'],
							 'Date' => $tripData['from_date'],
							 'currency' => $this->currency->getCode(),
							 'price' => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
							 'total' => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
						 );
					 }
				 }else{
                    // Get products ids
                    $products_ids = array();
                    $products = $this->model_account_order->getOrderProducts($result['order_id']);
       
                    $products_data = $this->handleOrderProducts($products,$with_products_details);
                    $json['orders'][] = array(
                        'order_id'   => $result['order_id'],
                        'name'       => $result['firstname'] . ' ' . $result['lastname'],
                        'status'     => $result['status'],
                        'order_status_id'   => $result['order_status_id'],
                        'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                        'products'   => ($product_total + $voucher_total),
                        'products_ids'   => $products_data['products_ids'],
                        'products_data'   => $products_data['products'] ?? 0,
                        'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
                        //'href'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL'),
                        //'reorder'    => $this->url->link('account/order', 'order_id=' . $result['order_id'], 'SSL')
                    );
			     	}
				
                }

                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {

        }
    }

    public function getInfo() {
        try {
            $this->load->language('api/cart');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');
			$this->load->model('catalog/product');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $json = array();
                if ($this->customer->isLogged()) {
                    $this->load->model('account/order');
                    if (isset($params->order_id)) {
                        $order_id = $params->order_id;
                    } else {
                        $order_id = 0;
                    }

                    $order_info = $this->model_account_order->getOrder($order_id);

                    if ($order_info) {
                        if ($order_info['invoice_no']) {
                            $json['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
                        } else {
                            $json['invoice_no'] = '';
                        }

                        $json['order_id'] = $order_id;
                        $json['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

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
                            'lastname' => $order_info['payment_lastname'],
                            'company' => $order_info['payment_company'],
                            'address_1' => $order_info['payment_address_1'],
                            'address_2' => $order_info['payment_address_2'],
                            'city' => $order_info['payment_city'],
                            'postcode' => $order_info['payment_postcode'],
                            'zone' => $order_info['payment_zone'],
                            'zone_code' => $order_info['payment_zone_code'],
                            'country' => $order_info['payment_country']
                        );

                        $json['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                        $json['payment_method'] = $order_info['payment_method'];

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
                            'lastname' => $order_info['shipping_lastname'],
                            'company' => $order_info['shipping_company'],
                            'address_1' => $order_info['shipping_address_1'],
                            'address_2' => $order_info['shipping_address_2'],
                            'city' => $order_info['shipping_city'],
                            'postcode' => $order_info['shipping_postcode'],
                            'zone' => $order_info['shipping_zone'],
                            'zone_code' => $order_info['shipping_zone_code'],
                            'country' => $order_info['shipping_country']
                        );

                        $json['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                        $json['shipping_method'] = $order_info['shipping_method'];

                        $json['products'] = array();

                        $products = $this->model_account_order->getOrderProducts($order_id);

                        foreach ($products as $product) {
                            $option_data = array();

                            $options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);
                            $product_info = $this->model_catalog_product->getProduct($product['product_id']);
							
							if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') 
							    && $this->config->get('trips')['status']==1) ) 
								{
									$this->load->model('module/trips');
							    	$cancelTripFlag= $this->model_module_trips->cancelTripConfig($product['product_id']); 
							    	$cancelStatus= $this->model_module_trips->cancelStatus($order_id); 
									$seller_id = $this->MsLoader->MsProduct->getSellerId($product['product_id']);
									$seller = $this->MsLoader->MsSeller->getSeller($seller_id);
								}
                            foreach ($options as $option) {
                                if ($option['type'] != 'file') {
                                    $value = $option['value'];
                                } else {
                                    $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
                                }

                                $option_data[] = array(
                                    'name' => $option['name'],
                                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                                );
                            }

                            $json['products'][] = array(
                                'product_id' => $product['product_id'],
                                'name' => $product['name'],
                                'model' => $product['model'],
                                'image' => \Filesystem::getUrl('image/'.$product_info['image']),
                                'manufacturer' => $product_info['manufacturer'],
                                'option' => $option_data,
                                'quantity' => $product['quantity'],
                                'currency' => $this->currency->getCode(),
                                'price' => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
                                'total' => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
                                'return' => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
                            );
							if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') 
							    && $this->config->get('trips')['status']==1) ) 
								{  
									$json['products'] += ['driver_name'=>$seller['ms.nickname'],'driver_phone'=>$seller['ms.mobile'] ];
								if($cancelStatus)
									$json['products'] += ['cancelTripButton' => false,'cancelStatus'=>$cancelStatus, ];
								else
									$json['products'] += ['cancelTripButton' => $cancelTripFlag,'cancelStatus'=>$cancelStatus, ];	
								}
                        }
                        if(!(\Extension::isInstalled('multiseller'))&&!(\Extension::isInstalled('trips') 
							    && $this->config->get('trips')['status']!=1) ) 
						{ 
                        $json['products'] = $this->handleOrderProducts($products,1)['products'];
						}
                        // Voucher
                        $json['vouchers'] = array();

                        $vouchers = $this->model_account_order->getOrderVouchers($order_id);

                        foreach ($vouchers as $voucher) {
                            $json['vouchers'][] = array(
                                'description' => $voucher['description'],
                                'amount' => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
                            );
                        }

                        $json['totals'] = $this->model_account_order->getOrderTotals($order_id);

                        $json['comment'] = nl2br($order_info['comment']);

                        $json['histories'] = array();

                        $this->load->model('aramex/aramex');
                        $checkAWB = $this->model_aramex_aramex->checkAWB($order_id);
                        if ($checkAWB) {
                            $json['track_url'] = $this->url->link('account/aramex_traking', 'order_id=' . $order_id, 'SSL');
                        } else {
                            $json['track_url'] = "";
                        }

                        $results = $this->model_account_order->getOrderHistories($order_id);

                        foreach ($results as $result) {
                            $json['histories'][] = array(
                                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                                'status' => $result['status'],
                                'status_id' => $result['status_id'],
                                'comment' => nl2br($result['comment'])
                            );
                        }
						$json['order_status_count'] = count($json['histories']);
                    }
					else{   $json['id'] = $this->customer->getId();}
                } else {
                    $json['error'] = "not registered!";
                }
                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');

                $this->response->setOutput(json_encode($json));
            }
        } catch (Exception $ex) {

        }
    }

    private function handleOrderProducts($products,$with_products_details = false)
    {
    	$products_data = [];
        foreach ($products as $product) {
            // Get products ids
	 		$products_data['products_ids'][] = $product['product_id'];
	 		if($with_products_details){
                $this->load->model('catalog/product');
		        $option_data = array();
		        $options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);
		        $product_info = $this->model_catalog_product->getProduct($product['product_id']);
		        foreach ($options as $option) {
		            if ($option['type'] != 'file') {
		                $value = $option['value'];
		            } else {
		                $value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
		            }

		            $option_data[] = array(
		                'name' => $option['name'],
		                'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
		            );
		        }
		        $products_data['products'][] = array(
		            'product_id' => $product['product_id'],
		            'name' => $product_info['name'],
		            'model' => $product['model'],
		            'image' => \Filesystem::getUrl('image/'.$product_info['image']),
		            'manufacturer' => $product_info['manufacturer'],
		            'option' => $option_data,
		            'quantity' => $product['quantity'],
		            'currency' => $this->currency->getCode(),
		            'price' => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
		            'total' => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
		            'return' => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
		        );	 			
	 		}
	    }
	    return $products_data;
    }

	public function trackingOrderStatuses()
    {
            $json = array();
			$statuses=array();
            if (!isset($this->session->data['api_id'])) {
				$json['status'] = false;
				$json['error'] =$this->language->get('error_permission');
            } else 
			{
				$statuses = $this->config->get('config_order_tracking_status');
				$json['status'] = true;
				$json['data'] =$statuses;
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            $this->response->setOutput(json_encode($json));
        
    }
}
