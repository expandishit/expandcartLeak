<?php
class ControllerPaymentKlarnaCheckout extends Controller {

	public function index() {
		$this->load->language('payment/klarna_checkout');

		$this->load->model('payment/klarna_checkout');
		$this->load->model('localisation/country');

		$this->document->setTitle($this->language->get('heading_title'));

		// if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/klarna_checkout.tpl')) {
		// 	$this->template = $this->config->get('config_template') . '/template/payment/klarna_checkout.tpl';
		// } else {
		// 	$this->template = 'default/template/payment/klarna_checkout.tpl';
		// }
  
        $this->template = 'default/template/payment/klarna_checkout.tpl';

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

		
		$this->render_ecwig();

	}

	public function main() {
		$this->load->language('payment/klarna_checkout');

		$this->load->model('payment/klarna_checkout');
		$this->load->model('localisation/country');

		$redirect = false;
		$html_snippet = '';

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$redirect = $this->url->link('checkout/cart');
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
				$redirect = $this->url->link('checkout/cart');
			}
		}

		// Validate cart has recurring products
		// if ($this->cart->hasRecurringProducts()) {
		 	//$redirect = $this->url->link('checkout/cart');
		// }

		list($totals, $taxes, $total) = $this->model_payment_klarna_checkout->getTotals();

		if ($this->config->get('klarna_checkout_total') > 0 && $this->config->get('klarna_checkout_total') > $total) {
			echo 'Klarna: Total Order must be > '.$this->config->get('klarna_checkout_total').'<br/>';
			//$redirect = $this->url->link('checkout/cart');
		}

		if (!$this->config->get('klarna_checkout_status')) {
			exit;
			$redirect = $this->url->link('checkout/cart');
		}

		if ($this->model_payment_klarna_checkout->checkForPaymentTaxes($products)) {
			echo 'Klarna: Tax Error!<br/>';
			$redirect = $this->url->link('checkout/cart');
		}

		$text_title = $this->language->get('text_title');

		unset($this->session->data['success']);

		$this->setPayment();
		$this->setShipping();

		$this->session->data['payment_method'] = array(
			'code'       => 'klarna_checkout',
			'title'      => $text_title,
			'terms'      => $this->url->link('information/information', 'information_id=' . $this->config->get('klarna_checkout_terms'), 'SSL'),
			'sort_order' => '1'
		);

		// Shipping
		$unset_shipping_method = true;
		if (isset($this->session->data['shipping_method']) && isset($this->session->data['shipping_methods'])) {
			foreach ($this->session->data['shipping_methods'] as $shipping_method) {
				if ($shipping_method['quote']) {
					foreach ($shipping_method['quote'] as $quote) {
						if ($quote['code'] == $this->session->data['shipping_method']['code']) {
							$unset_shipping_method = false;
							break 2;
						}
					}
				}
			}
		}

		if ($unset_shipping_method) {
			unset($this->session->data['shipping_method']);
		}

		if ((!isset($this->session->data['shipping_method']) || empty($this->session->data['shipping_method'])) && (isset($this->session->data['shipping_methods']) && !empty($this->session->data['shipping_methods']))) {
			$this->session->data['shipping_method'] = $this->model_payment_klarna_checkout->getDefaultShippingMethod($this->session->data['shipping_methods']);
		}
		
		$klSettings = $this->config->get('klarna_checkout_account');
		
		if ($klSettings[0]['currency'] !== $this->session->data['currency']) {
			echo 'Klarna: Currency must be '.$klSettings[0]['currency'];
			exit;
		}

		//Klarna Connector
		list($klarna_account, $connector) = $this->model_payment_klarna_checkout->getConnector($this->config->get('klarna_checkout_account'), $this->session->data['currency']);


		
		if (!$klarna_account || !$connector) {
			echo 'Klarna: Account settings error!';
			exit;
			//$redirect = $this->url->link('checkout/cart');
		}
		

		if (!$redirect) {
			// Get currency code and currency value to use to calculate taxes

			// Build order_lines
			$create_order = true;
			$klarna_checkout = false;

			//$this->createOrder();

			list($klarna_order_data, $encrypted_order_data) = $this->klarnaOrderData($klarna_account);
			
			if (isset($this->session->data['klarna_checkout_currency']) && $this->session->data['klarna_checkout_currency'] != $this->session->data['currency']) {
				$this->model_payment_klarna_checkout->log('Currency changed, unsetting kc order id');
				unset($this->session->data['klarna_checkout_order_id']);
				unset($this->session->data['klarna_checkout_data']);
			}
			
			$this->session->data['klarna_checkout_currency'] = $this->session->data['currency'];
			// Fetch or create order
			if (isset($this->session->data['klarna_checkout_order_id'])) {
				$retrieve = $this->model_payment_klarna_checkout->orderRetrieve($connector, $this->session->data['klarna_checkout_order_id']);

				if ($retrieve) {
					//If address has changed, unset klarna_checkout_order_id and create new order
					$address_change = false;

					if (isset($this->session->data['klarna_checkout_data']) && $this->session->data['klarna_checkout_data']) {
						if (isset($this->session->data['klarna_checkout_data']['zone_code'])) {
							$kc_region = $this->session->data['klarna_checkout_data']['zone_code'];
						} else {
							$kc_region = '';
						}

						if (isset($this->session->data['klarna_checkout_data']['iso_code_2'])) {
							$kc_country = $this->session->data['klarna_checkout_data']['iso_code_2'];
						} else {
							$kc_country = '';
						}

						if (isset($this->session->data['shipping_address']['zone_code'])) {
							$oc_region = $this->session->data['shipping_address']['zone_code'];
						} else {
							$oc_region = '';
						}

						if (isset($this->session->data['shipping_address']['iso_code_2'])) {
							$oc_country = $this->session->data['shipping_address']['iso_code_2'];
						} else {
							$oc_country = '';
						}

						$kc_address = array(
							'region'  => $kc_region,
							'country' => $kc_country,
						);

						$oc_address = array(
							'region'  => $oc_region,
							'country' => $oc_country,
						);

						//If address has changed, dont use retrieved order, create new one instead
						if (array_diff(array_map('strtolower', $kc_address), array_map('strtolower', $oc_address))) {
							$address_change = true;
						}
					}

					if (!$address_change) {


                        $this->model_payment_klarna_checkout->log('Order Updated');
                        $this->model_payment_klarna_checkout->log($klarna_order_data);

						

						$update = $this->model_payment_klarna_checkout->orderUpdate($connector, $this->session->data['klarna_checkout_order_id'], $klarna_order_data);

						if ($update) {
							$create_order = false;
							$klarna_checkout = $update->fetch();

							//$this->model_payment_klarna_checkout->updateOrder($this->session->data['order_id'], $klarna_checkout['order_id'], $encrypted_order_data);
						}
					}
				}
			}

			if ($create_order) {

				$this->model_payment_klarna_checkout->log('Order Created');
				$this->model_payment_klarna_checkout->log($klarna_order_data);
				unset($this->session->data['klarna_checkout_data']);
				$create = $this->model_payment_klarna_checkout->orderCreate($connector, $klarna_order_data);

				if ($create) {
					$klarna_checkout = $create->fetch();
					//$this->model_payment_klarna_checkout->addOrder($this->session->data['order_id'], $klarna_checkout['order_id'], $encrypted_order_data);
					
				}
			}

			if ($klarna_checkout) {
				$this->session->data['klarna_checkout_order_id'] = $klarna_checkout['order_id'];
				
				$html_snippet = $klarna_checkout['html_snippet'];
			}
		}

		if (isset($this->request->post['response']) && $this->request->post['response'] == 'template') {
            $this->data['redirect'] = $redirect;

			$this->data['klarna_checkout'] = $html_snippet;

            // if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/klarna_checkout_main.tpl')) {
            //     $this->template = $this->config->get('config_template') . '/template/payment/klarna_checkout_main.tpl';
            // } else {
            //     $this->template = 'default/template/payment/klarna_checkout_main.tpl';
            // }
            $this->template = 'default/template/payment/klarna_checkout_main.tpl';
            $this->response->setOutput($this->render());
            //$this->render_ecwig();
		} elseif (isset($this->request->post['response']) && $this->request->post['response'] == 'json') {
			$json['redirect'] = $redirect;

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
		}
	}

	public function sidebar() {
		$this->load->language('checkout/checkout');
		$this->load->language('payment/klarna_checkout');

		$this->load->model('payment/klarna_checkout');

        $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

		$this->setPayment();
		$this->setShipping();

		// Shipping
		$unset_shipping_method = true;
		if (isset($this->session->data['shipping_method']) && isset($this->session->data['shipping_methods'])) {
			foreach ($this->session->data['shipping_methods'] as $shipping_method) {
				if ($shipping_method['quote']) {
					foreach ($shipping_method['quote'] as $quote) {
						if ($quote['code'] == $this->session->data['shipping_method']['code']) {
							$unset_shipping_method = false;
							break 2;
						}
					}
				}
			}
		}

		if ($unset_shipping_method) {
			unset($this->session->data['shipping_method']);
		}

		if ((!isset($this->session->data['shipping_method']) || empty($this->session->data['shipping_method'])) && (isset($this->session->data['shipping_methods']) && !empty($this->session->data['shipping_methods']))) {
			$this->session->data['shipping_method'] = $this->model_payment_klarna_checkout->getDefaultShippingMethod($this->session->data['shipping_methods']);
		}

		$this->data['text_choose_shipping_method'] = $this->language->get('text_choose_shipping_method');
		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');
		$this->data['text_no_shipping'] = $this->language->get('error_no_shipping');

		$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['shipping_required'] = $this->cart->hasShipping();

		if (isset($this->session->data['shipping_methods'])) {
			$this->data['shipping_methods'] = $this->session->data['shipping_methods'];
		} else {
			unset($this->data['shipping_method']);

			$this->data['shipping_methods'] = array();
		}

		if (isset($this->session->data['shipping_method']['code'])) {
			$this->data['code'] = $this->session->data['shipping_method']['code'];
		} else {
			$this->data['code'] = '';
		}

		$this->load->model('tool/image');

		$this->data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			} else {
				$image = '';
			}

            $option_data = array();

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['option_value'];
                } else {
                    $filename = $this->encryption->decrypt($option['option_value']);

                    $value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
                }

                $option_data[] = array(
                    'name'  => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                );
            }

			// Display prices
			if ($isCustomerAllowedToViewPrice) {
				$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			}

			// Display prices
			if ($isCustomerAllowedToViewPrice) {
				$total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']);
			} else {
				$total = false;
			}

			$this->data['products'][] = array(
				'key'       => $product['key'],
				'thumb'     => $image,
				'name'      => $product['name'],
				'model'     => $product['model'],
				'option'    => $option_data,
				'recurring' => ($product['recurring'] ? $product['recurring']['name'] : ''),
				'quantity'  => $product['quantity'],
				'price'     => $price,
				'total'     => $total,
				'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);
		}

		// Gift Voucher
		$this->data['vouchers'] = array();

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$v_quantity = $voucher['quantity'] ? $voucher['quantity'] : 1;
				$this->data['vouchers'][] = array(
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format( ($voucher['amount'] * $v_quantity), $this->session->data['currency'])
				);
			}
		}

        $total_data = array();
        $total = 0;
        $taxes = $this->cart->getTaxes();

        // Display prices
        if ($isCustomerAllowedToViewPrice) {
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get($result['code'] . '_status')) {
                    $this->load->model('total/' . $result['code']);

                    $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
                }
            }

            $sort_order = array();

            foreach ($total_data as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $total_data);
        }

        $this->data['totals'] = array();

        foreach ($total_data as $total) {
            $this->data['totals'][] = array(
                'title' => $total['title'],
                'text'  => $this->currency->format($total['value'])
            );
        }

        // if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/klarna_checkout_sidebar.tpl')) {
        //     $this->template = $this->config->get('config_template') . '/template/payment/klarna_checkout_sidebar.tpl';
        // } else {
        //     $this->template = 'default/template/payment/klarna_checkout_sidebar.tpl';
        // }
        $this->template = 'default/template/payment/klarna_checkout_sidebar.tpl';
        //$this->render_ecwig();
        $this->response->setOutput($this->render());
	}

	public function shippingAddress() {
		$this->load->model('payment/klarna_checkout');
		$this->load->model('localisation/zone');

		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

		$json = array();

		unset($this->session->data['shipping_address']);
		unset($this->session->data['shipping_methods']);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['country'])) {
			$country_info = $this->model_payment_klarna_checkout->getCountryByIsoCode3($this->request->post['country']);

			if ($country_info) {
				// Set default zone for shipping calculations. Get overwritten by correct data when order is confirmed
				$zones = $this->model_localisation_zone->getZonesByCountryId($country_info['country_id']);

				$zone = array();
				if (isset($this->request->post['region']) && !empty($this->request->post['region'])) {
					$zone = $this->model_payment_klarna_checkout->getZoneByCode($this->request->post['region'], $country_info['country_id']);
				}

				if ($zone || $zones) {
					$this->session->data['shipping_address'] = array(
						'address_id'	 => null,
						'firstname'		 => utf8_substr($this->request->post['given_name'], 0, 32),
						'lastname'		 => utf8_substr($this->request->post['family_name'], 0, 32),
						'company'		 => null,
						'address_1'		 => utf8_substr($this->request->post['street_address'], 0, 128),
						'address_2'		 => utf8_substr($this->request->post['street_address'], 129, 256),
						'postcode'		 => utf8_substr($this->request->post['postal_code'], 0, 10),
						'city'			 => utf8_substr($this->request->post['city'], 0, 128),
						'zone_id'		 => ($zone ? $zone['zone_id'] : $zones[0]['zone_id']),
						'zone'			 => ($zone ? $zone['name'] : $zones[0]['name']),
						'zone_code'		 => ($zone ? $zone['code'] : $zones[0]['code']),
						'country_id'	 => $country_info['country_id'],
						'country'		 => $country_info['name'],
						'iso_code_2'	 => $country_info['iso_code_2'],
						'iso_code_3'	 => $country_info['iso_code_3'],
						'address_format' => $country_info['address_format'],
						'custom_field'	 => array(),
					);

					//$this->tax->unsetRates();
					$this->tax->setShippingAddress($this->session->data['shipping_address']['country_id'], $this->session->data['shipping_address']['zone_id']);
					$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
				}
			} else {
				$this->model_payment_klarna_checkout->log('Couldnt find country: ' . $this->request->post['country']);
			}
		}

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function cartTotal() {
		$this->load->language('checkout/cart');

		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

        // Totals
        $this->load->model('setting/extension');
        $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
        $total_data = array();
        $total = 0;
        $taxes = $this->cart->getTaxes();

        // Display prices
        if ($isCustomerAllowedToViewPrice) {
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get($result['code'] . '_status')) {
                    $this->load->model('total/' . $result['code']);

                    $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
                }
            }

            $sort_order = array();

            foreach ($total_data as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $total_data);

            $total = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
        }

		$this->response->setOutput(json_encode($total));
	}

	public function addressUpdate() {
		$this->load->language('payment/klarna_checkout');

		$this->load->model('account/customer');
		$this->load->model('checkout/order');
		$this->load->model('payment/klarna_checkout');
		$this->load->model('localisation/zone');

		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

		$process = true;

		$request = json_decode(file_get_contents('php://input'));

		$json = array();

		$http_response_code = 400;

		// Check to see if request data is complete
		if (!$request || !isset($request->order_lines) || empty($request->order_lines) || !isset($request->shipping_address) || empty($request->shipping_address)) {
			$this->model_payment_klarna_checkout->log('Request data incomplete. Full request below:');
			$this->model_payment_klarna_checkout->log($request);
			$process = false;
		}

		// Get Klarna order info from db
		if ($process) {
			$order_id = null;

			foreach ($request->order_lines as $order_line) {
				if ($order_line->type == 'physical' || $order_line->type == 'digital' || $order_line->type == 'gift_card') {
					$order_id = $this->encryption->decrypt($order_line->merchant_data);
					break;
				}
			}

			if ($order_id) {
				// Get klarna order data from db
				$klarna_checkout_order = $this->model_payment_klarna_checkout->getOrderByOrderId($order_id);

				if (!$klarna_checkout_order || !$klarna_checkout_order['data']) {
					$this->model_payment_klarna_checkout->log('No klarna order found using order_id: ' . $order_id);
					$process = false;
				}
			} else {
				$process = false;
			}
		}

		if ($process) {
			$klarna_checkout_order_data = json_decode($this->encryption->decrypt($klarna_checkout_order['data']), true);

			// Check credentials in request with ones stored in db
			$valid_request = false;
			foreach ($this->config->get('klarna_checkout_account') as $account) {
				if (($account['merchant_id'] == $klarna_checkout_order_data['merchant_id']) && ($account['secret'] == $klarna_checkout_order_data['secret'])) {
					$valid_request = true;
					break;
				}
			}

			if (!$valid_request) {
				$this->model_payment_klarna_checkout->log('Cannot validate request. Terminating.');
				$process = false;
			}
		}

		// Request is valid, we can spoof/simulate the customer to calculate shipping
		if ($process) {
			session_destroy();
            session_id($klarna_checkout_order_data['session_id']);
            session_start();
            $this->session->data = array();
            $this->session->data = &$_SESSION;

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if (!$order_info) {
				$this->model_payment_klarna_checkout->log('No order found using order_id: ' . $order_id . '. Full request below:');
				$this->model_payment_klarna_checkout->log($request);
				$process = false;
			}

			// Set more session data from the order
			$this->session->data['currency'] = $order_info['currency_code'];
			$this->session->data['language'] = $order_info['language_code'];

			$country_info = $this->model_payment_klarna_checkout->getCountryByIsoCode2($request->shipping_address->country);

			if (!$country_info) {
				$this->model_payment_klarna_checkout->log('No country found using: ' . $request->shipping_address->country . '. Full request below:');
				$this->model_payment_klarna_checkout->log($request);
			}

			if ($order_info && $country_info) {
				$zones = $this->model_localisation_zone->getZonesByCountryId($country_info['country_id']);

				$zone = array();
				if (isset($request->shipping_address->region) && !empty($request->shipping_address->region)) {
					$zone = $this->model_payment_klarna_checkout->getZoneByCode($request->shipping_address->region, $country_info['country_id']);
				}

				if ($zone || $zones) {
					$this->session->data['shipping_address'] = array(
						'address_id'	 => null,
						'firstname'		 => null,
						'lastname'		 => null,
						'company'		 => null,
						'address_1'		 => null,
						'address_2'		 => null,
						'postcode'		 => null,
						'city'			 => null,
						'zone_id'		 => ($zone ? $zone['zone_id'] : $zones[0]['zone_id']),
						'zone'			 => ($zone ? $zone['name'] : $zones[0]['name']),
						'zone_code'		 => ($zone ? $zone['code'] : $zones[0]['code']),
						'country_id'	 => $country_info['country_id'],
						'country'		 => $country_info['name'],
						'iso_code_2'	 => $country_info['iso_code_2'],
						'iso_code_3'	 => $country_info['iso_code_3'],
						'address_format' => '',
						'custom_field'	 => array()
					);

					$this->session->data['klarna_checkout_data'] = array(
						'email'		 => $request->billing_address->email,
						'firstname'	 => $request->billing_address->given_name,
						'lastname'	 => $request->billing_address->family_name,
						'telephone'	 => $request->billing_address->phone,
						'iso_code_2' => $this->session->data['shipping_address']['iso_code_2'],
						'zone_code'  => $this->session->data['shipping_address']['zone_code']
					);

					// Unset $tax_rates
					//$this->tax->unsetRates();
					$this->tax->setShippingAddress($country_info['country_id'], ($zone ? $zone['zone_id'] : $zones[0]['zone_id']));
					$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

					//Check if customer is US. If so, send taxes differently
					if ($this->session->data['shipping_address']['iso_code_2'] === 'US') {
						$include_taxes = "0";
					} else {
						$include_taxes = "1";
					}

					$method_data = array();

					$this->load->model('setting/extension');

					$results = $this->model_setting_extension->getExtensions('shipping');

					foreach ($results as $result) {
						if ($this->config->get($result['code'] . '_status')) {
							$this->load->model('shipping/' . $result['code']);

							$quote = $this->{'model_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

							if ($quote) {
								$method_data[$result['code']] = array(
									'title'      => $quote['title'],
									'quote'      => $quote['quote'],
									'sort_order' => $quote['sort_order'],
									'error'      => $quote['error']
								);
							}
						}
					}

					$sort_order = array();

					foreach ($method_data as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $method_data);

					$shipping_methods = $method_data;

					if ($shipping_methods) {
						list($klarna_account, $connector) = $this->model_payment_klarna_checkout->getConnector($this->config->get('klarna_checkout_account'), $this->session->data['currency']);

						if ($klarna_account && $connector) {
							list($klarna_order_data, $encrypted_order_data) = $this->klarnaOrderData($klarna_account);

							if ($this->cart->hasShipping()) {
								$shipping_method = array();

                                if (isset($this->session->data['shipping_method']) && !empty($this->session->data['shipping_method'])) {
                                    foreach ($shipping_methods as $individual_shipping_method) {
                                        if ($individual_shipping_method['quote']) {
                                            foreach ($individual_shipping_method['quote'] as $quote) {
                                                if (($this->session->data['shipping_method']['code'] == $quote['code']) && ($this->session->data['shipping_method']['title'] == $quote['title']) && ($this->session->data['shipping_method']['cost'] == $quote['cost']) && ($this->session->data['shipping_method']['tax_class_id'] == $quote['tax_class_id'])) {
                                                    $shipping_method = $quote;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                }

								// If the current shipping method isn't in the available shipping methods, assign default
								if (!$shipping_method) {
									$this->session->data['shipping_method'] = $this->model_payment_klarna_checkout->getDefaultShippingMethod($shipping_methods);
								}

								$total_amount = $this->currency->format($this->tax->calculate($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id'], $include_taxes), $order_info['currency_code'], $order_info['currency_value'], false) * 100;

								if ($include_taxes) {
									$total_tax_amount = $this->currency->format($this->tax->getTax($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']), $order_info['currency_code'], $order_info['currency_value'], false) * 100;
								} else {
									$total_tax_amount = 0;
								}

								$total_sub_amount = $this->currency->format($this->tax->calculate($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id'], false), $order_info['currency_code'], $order_info['currency_value'], false) * 100;

								$tax_rate = 0;

								if ($include_taxes && $total_tax_amount && $total_sub_amount) {
									$tax_rate = ($total_tax_amount / $total_sub_amount) * 100;
								}

								foreach ($klarna_order_data['order_lines'] as $key => $order_line) {
									if ($order_line['type'] == 'shipping_fee') {
										unset($klarna_order_data['order_lines'][$key]);
										break;
									}
								}

								$klarna_order_data['order_lines'][] = array(
									'type'					=> 'shipping_fee',
									'name'					=> $this->session->data['shipping_method']['title'],
									'quantity'				=> '1',
									'unit_price'			=> round($this->currency->format($this->tax->calculate($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id'], $include_taxes), $order_info['currency_code'], $order_info['currency_value'], false) * 100),
									'tax_rate'				=> round($tax_rate * 100),
									'total_amount'			=> round($total_amount),
									'total_tax_amount'		=> round($total_tax_amount),
									'total_discount_amount' => 0
								);
							}

							list($totals, $taxes, $total) = $this->model_payment_klarna_checkout->getTotals();

							//If $include_taxes is false, means customer is US so we add a new sales_tax order line with all the tax
							if (!$include_taxes) {
								foreach ($klarna_order_data['order_lines'] as $key => $order_line) {
									if ($order_line['type'] == 'sales_tax') {
										unset($klarna_order_data['order_lines'][$key]);
										break;
									}
								}

								$klarna_order_data['order_lines'][] = array(
									'type'					=> 'sales_tax',
									'name'					=> $this->language->get('text_sales_tax'),
									'quantity'				=> '1',
									'unit_price'			=> round($this->currency->format(array_sum($taxes), $order_info['currency_code'], $order_info['currency_value'], false) * 100),
									'tax_rate'				=> 0,
									'total_amount'			=> round($this->currency->format(array_sum($taxes), $order_info['currency_code'], $order_info['currency_value'], false) * 100),
									'total_tax_amount'		=> 0,
									'total_discount_amount' => 0
								);
							}

							$http_response_code = 200;

							$json = array(
								'order_amount'     => round($this->currency->format($total, $order_info['currency_code'], $order_info['currency_value'], false) * 100),
								'order_tax_amount' => round($this->currency->format(array_sum($taxes), $order_info['currency_code'], $order_info['currency_value'], false) * 100),
								'order_lines'      => array_values($klarna_order_data['order_lines'])
							);
						}
					}
				}
			}
		}

		$this->model_payment_klarna_checkout->log($http_response_code);
		$this->model_payment_klarna_checkout->log($json);

		http_response_code($http_response_code);
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function notification() {
		$this->load->model('payment/klarna_checkout');
		$this->load->model('checkout/order');

		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

		$request = json_decode(file_get_contents('php://input'));

		if (isset($request->order_id)) {
			$klarna_checkout_order = $this->model_payment_klarna_checkout->getOrder($request->order_id);

			if ($klarna_checkout_order) {
				$order_info = $this->model_checkout_order->getOrder($klarna_checkout_order['order_id']);

				if ($order_info) {
					list($klarna_account, $connector) = $this->model_payment_klarna_checkout->getConnector($this->config->get('klarna_checkout_account'), $order_info['currency_code']);

					if (!$klarna_account || !$connector) {
						$this->model_payment_klarna_checkout->log('Could not getConnector');
					}

					$retrieve = $this->model_payment_klarna_checkout->omOrderRetrieve($connector, $request->order_id);

					$order_status_id = false;
					if ($retrieve) {
						switch ($request->event_type) {
							case 'FRAUD_RISK_ACCEPTED':
								$order_status_id = $this->config->get('klarna_checkout_order_status_fraud_accepted_id');
								break;
							case 'FRAUD_RISK_REJECTED':
								$order_status_id = $this->config->get('klarna_checkout_order_status_fraud_rejected_id');
								break;
						}
					}

					if ($order_status_id) {
						$this->model_payment_klarna_checkout->addOrderHistory($klarna_checkout_order['order_id'], $order_status_id);
					}
				}
			} else {
				$this->model_payment_klarna_checkout->log('Could not find order id using ' . $request->order_id);
			}
		} else {
			$this->model_payment_klarna_checkout->log('$request->order_id is not set');
		}
	}

	public function validation() {
		$this->load->model('account/customer');
		$this->load->model('checkout/order');
		$this->load->model('payment/klarna_checkout');

		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

		$validate = true;

		$request = json_decode(file_get_contents('php://input'));

		$json = array();

		// Check to see if request data is complete
		if (!$request || !isset($request->order_lines) || empty($request->order_lines) || !isset($request->shipping_address) || empty($request->shipping_address) || !isset($request->billing_address) || empty($request->billing_address)) {
			$this->model_payment_klarna_checkout->log('Request data incomplete. Full request below:');
			$this->model_payment_klarna_checkout->log($request);
			$validate = false;
		}

		// Get Klarna order info from db
		if ($validate) {
			$order_id = null;

			foreach ($request->order_lines as $order_line) {
				if ($order_line->type == 'physical' || $order_line->type == 'digital' || $order_line->type == 'gift_card') {
					$order_id = $this->encryption->decrypt($order_line->merchant_data);
					break;
				}
			}

			if ($order_id) {
				// Get klarna order data from db
				$klarna_checkout_order = $this->model_payment_klarna_checkout->getOrderByOrderId($order_id);

				if (!$klarna_checkout_order || !$klarna_checkout_order['data']) {
					$this->model_payment_klarna_checkout->log('No klarna order found using order_id: ' . $order_id);
					$validate = false;
				}
			} else {
				$this->model_payment_klarna_checkout->log('Cannot get decrypted order_id');
				$validate = false;
			}
		}

		if ($validate) {
			$klarna_checkout_order_data = json_decode($this->encryption->decrypt($klarna_checkout_order['data']), true);

			// Check credentials in request with ones stored in db
			$valid_request = false;
			foreach ($this->config->get('klarna_checkout_account') as $account) {
				if (($account['merchant_id'] == $klarna_checkout_order_data['merchant_id']) && ($account['secret'] == $klarna_checkout_order_data['secret'])) {
					$valid_request = true;
					break;
				}
			}

			if (!$valid_request) {
				$this->model_payment_klarna_checkout->log('Cannot validate request. Terminating.');
				$validate = false;
			}
		}

		// Spoof/simulate the customer to calculate shipping
		if ($validate) {
			session_destroy();
            session_id($klarna_checkout_order_data['session_id']);
            session_start();
            $this->session->data = array();
            $this->session->data = &$_SESSION;

			$this->model_payment_klarna_checkout->log('Session ID:');
			$this->model_payment_klarna_checkout->log($klarna_checkout_order_data['session_id']);

			// Validate cart has products and has stock.
			if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
				$this->model_payment_klarna_checkout->log('Cart has no products or cart has no stock');
				$validate = false;
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
					$this->model_payment_klarna_checkout->log('Cart doesnt meet minimum quantities');
					$validate = false;
				}
			}

			// Validate cart has recurring products
			if ($this->cart->hasRecurringProducts()) {
				$this->model_payment_klarna_checkout->log('Cart has recurring products');
				$validate = false;
			}
		}

		// Check order total to see if session matches post data
		if ($validate) {
			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				// Unset $tax_rates and set them again using correct shipping data
				//$this->tax->unsetRates();
				$this->tax->setShippingAddress($this->session->data['shipping_address']['country_id'], $this->session->data['shipping_address']['zone_id']);
				$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

				list($totals, $taxes, $total) = $this->model_payment_klarna_checkout->getTotals();

				// Check order_amount
				if (round($this->currency->format($total, $order_info['currency_code'], $order_info['currency_value'], false) * 100) != $request->order_amount) {
					$this->model_payment_klarna_checkout->log('Klarna Checkout order_amount does not match session order total. Klarna Request: ' . $request->order_amount . '. OpenCart: ' . round($this->currency->format($total, $order_info['currency_code'], $order_info['currency_value'], false) * 100));
					$this->model_payment_klarna_checkout->log('$totals');
					$this->model_payment_klarna_checkout->log($totals);
					$this->model_payment_klarna_checkout->log('$order_info');
					$this->model_payment_klarna_checkout->log($order_info);
					$this->model_payment_klarna_checkout->log('$this->cart->getTaxes()');
					$this->model_payment_klarna_checkout->log($this->cart->getTaxes());
					$validate = false;
				}

				// Check order_tax_amount
				if (round($this->currency->format(array_sum($taxes), $order_info['currency_code'], $order_info['currency_value'], false) * 100) != $request->order_tax_amount) {
					$this->model_payment_klarna_checkout->log('Klarna Checkout order_tax_amount does not match session tax total. Totals below:');
					$this->model_payment_klarna_checkout->log('Session taxes:');
					$this->model_payment_klarna_checkout->log(round($this->currency->format(array_sum($taxes), $order_info['currency_code'], $order_info['currency_value'], false) * 100));
					$this->model_payment_klarna_checkout->log('Request taxes:');
					$this->model_payment_klarna_checkout->log($request->order_tax_amount);
					$validate = false;
				}
			} else {
				$this->model_payment_klarna_checkout->log('Cannot find order using: ' . $order_id);
				$validate = false;
			}
		}

		// If validates, add customer's email (if guest checkout) and then send 200 response
		if ($validate) {
			if (!$this->customer->isLogged()) {
				$this->model_payment_klarna_checkout->updateOcOrderEmail($order_id, utf8_substr($request->shipping_address->email, 0, 96));
			}

			// If customer ticked newsletter, sign them up
			if (isset($request->merchant_requested->additional_checkbox) && $request->merchant_requested->additional_checkbox && $order_info['customer_id']) {
				$this->model_payment_klarna_checkout->subscribeNewsletter($order_info['customer_id']);
			}

			// Update OpenCart order with payment and shipping details
			$payment_country_info = $this->model_payment_klarna_checkout->getCountryByIsoCode2($request->billing_address->country);
			$shipping_country_info = $this->model_payment_klarna_checkout->getCountryByIsoCode2($request->shipping_address->country);

			//If region is passed, try to update OpenCart order with correct region/zone
			$payment_zone_info = array();
			if ($payment_country_info && isset($request->billing_address->region)) {
				$payment_zone_info = $this->model_payment_klarna_checkout->getZoneByCode($request->billing_address->region, $payment_country_info['country_id']);
			}

			$shipping_zone_info = array();
			if ($shipping_country_info && isset($request->shipping_address->region)) {
				$shipping_zone_info = $this->model_payment_klarna_checkout->getZoneByCode($request->shipping_address->region, $shipping_country_info['country_id']);
			}

			$order_data = array(
				'firstname'				  => utf8_substr($request->billing_address->given_name, 0, 32),
				'lastname'				  => utf8_substr($request->billing_address->family_name, 0, 32),
				'telephone'				  => utf8_substr($request->billing_address->phone, 0, 32),
				'payment_firstname'		  => utf8_substr($request->billing_address->given_name, 0, 32),
				'payment_lastname'		  => utf8_substr($request->billing_address->family_name, 0, 32),
				'payment_address_1'		  => utf8_substr($request->billing_address->street_address, 0, 128),
				'payment_address_2'		  => (isset($request->billing_address->street_address2) ? utf8_substr($request->billing_address->street_address2, 0, 128) : ''),
				'payment_city'			  => utf8_substr($request->billing_address->city, 0, 128),
				'payment_postcode'		  => utf8_substr($request->billing_address->postal_code, 0, 10),
				'payment_zone'			  => ($payment_zone_info ? $payment_zone_info['name'] : ''),
				'payment_zone_id'		  => ($payment_zone_info ? $payment_zone_info['zone_id'] : ''),
				'payment_country'		  => ($payment_country_info ? $payment_country_info['name'] : ''),
				'payment_country_id'	  => ($payment_country_info ? $payment_country_info['country_id'] : ''),
				'payment_address_format'  => ($payment_country_info ? $payment_country_info['address_format'] : ''),
				'shipping_firstname'	  => utf8_substr($request->shipping_address->given_name, 0, 32),
				'shipping_lastname'		  => utf8_substr($request->shipping_address->family_name, 0, 32),
				'shipping_address_1'	  => utf8_substr($request->shipping_address->street_address, 0, 128),
				'shipping_address_2'	  => (isset($request->shipping_address->street_address2) ? utf8_substr($request->shipping_address->street_address2, 0, 128) : ''),
				'shipping_city'			  => utf8_substr($request->shipping_address->city, 0, 128),
				'shipping_postcode'		  => utf8_substr($request->shipping_address->postal_code, 0, 10),
				'shipping_zone'			  => ($shipping_zone_info ? $shipping_zone_info['name'] : ''),
				'shipping_zone_id'		  => ($shipping_zone_info ? $shipping_zone_info['zone_id'] : ''),
				'shipping_country'		  => ($shipping_country_info ? $shipping_country_info['name'] : ''),
				'shipping_country_id'	  => ($shipping_country_info ? $shipping_country_info['country_id'] : ''),
				'shipping_address_format' => ($shipping_country_info ? $shipping_country_info['address_format'] : '')
			);

			$this->model_payment_klarna_checkout->updateOcOrder($order_id, $order_data);

			$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));

			http_response_code(200);
		} else {
			http_response_code(303);
			$this->response->addHeader('Location: ' . $this->url->link('checkout/checkout', '', 'SSL'));
		}

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function confirmation() {
		$this->load->model('checkout/order');
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('klarna_checkout_order_status_id'));
	 	$this->response->redirect($this->url->link('checkout/success', '', $server_conn_slug));
		exit;

		$this->load->language('payment/klarna_checkout');

		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();

            unset($this->session->data['stock_forecasting_cart']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);

            unset($this->session->data['klarna_checkout_order_id']);
            unset($this->session->data['klarna_checkout_data']);
		}

		$this->document->setTitle($this->language->get('heading_title_success'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/cart'),
			'text'      => $this->language->get('text_basket'),
			'separator' => $this->language->get('text_separator')
		);

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'text'      => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		);

		$this->data['heading_title'] = $this->language->get('heading_title_success');

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['continue'] = $this->url->link('common/home');

		$this->load->model('payment/klarna_checkout');
		$this->load->model('checkout/order');

		$klarna_checkout = false;
		$html_snippet = '';

		if (isset($this->request->get['klarna_order_id'])) {
			$klarna_checkout_order = $this->model_payment_klarna_checkout->getOrder($this->request->get['klarna_order_id']);

			if ($klarna_checkout_order) {
				$order_info = $this->model_checkout_order->getOrder($klarna_checkout_order['order_id']);

				if ($order_info) {
					list($klarna_account, $connector) = $this->model_payment_klarna_checkout->getConnector($this->config->get('klarna_checkout_account'), $order_info['currency_code']);

					if (!$klarna_account || !$connector) {
						$this->model_payment_klarna_checkout->log('Could not getConnector');
						$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
					}

					$retrieve = $this->model_payment_klarna_checkout->orderRetrieve($connector, $this->request->get['klarna_order_id']);

					if ($retrieve) {
						$klarna_checkout = $retrieve->fetch();

						if ($klarna_checkout && $klarna_checkout['html_snippet']) {
							$html_snippet = $klarna_checkout['html_snippet'];
						}
					} else {
						$this->redirect($this->url->link('checkout/cart', '', 'SSL'));
					}
				}
			} else {
				$this->model_payment_klarna_checkout->log('Could not find order id using ' . $this->request->get['klarna_order_id']);
				$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
			}
		} else {
			$this->model_payment_klarna_checkout->log('$this->request->get[\'klarna_order_id\'] is not set');
			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$this->data['klarna_checkout'] = $html_snippet;

		// if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/klarna_checkout_success.tpl')) {
		// 	$this->template = $this->config->get('config_template') . '/template/payment/klarna_checkout_success.tpl';
		// } else {
		// 	$this->template = 'default/template/payment/klarna_checkout_success.tpl';
		// }
        $this->template = 'default/template/payment/klarna_checkout_success.tpl';

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
		//$this->render_ecwig();
		$this->response->setOutput($this->render());
	}

	public function push() {
		$this->load->model('payment/klarna_checkout');
		$this->load->model('checkout/order');

		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

		if (isset($this->request->get['klarna_order_id'])) {
			$klarna_checkout_order = $this->model_payment_klarna_checkout->getOrder($this->request->get['klarna_order_id']);

			if ($klarna_checkout_order) {
				$order_info = $this->model_checkout_order->getOrder($klarna_checkout_order['order_id']);

				if ($order_info) {
					list($klarna_account, $connector) = $this->model_payment_klarna_checkout->getConnector($this->config->get('klarna_checkout_account'), $order_info['currency_code']);

					if ($klarna_account && $connector) {
						$order = $this->model_payment_klarna_checkout->omOrderRetrieve($connector, $this->request->get['klarna_order_id']);

						$this->model_payment_klarna_checkout->log('Order details from push:');
						$this->model_payment_klarna_checkout->log($order);

						if ($order) {
							if ($order->acknowledge()) {
								// Update OpenCart order with payment and shipping details
								$payment_country_info = $this->model_payment_klarna_checkout->getCountryByIsoCode2($order['billing_address']['country']);
								$shipping_country_info = $this->model_payment_klarna_checkout->getCountryByIsoCode2($order['shipping_address']['country']);

								//If region is passed, try to update OpenCart order with correct region/zone
								$payment_zone_info = array();
								if ($payment_country_info && isset($order['billing_address']['region'])) {
									$payment_zone_info = $this->model_payment_klarna_checkout->getZoneByCode($order['billing_address']['region'], $payment_country_info['country_id']);
								}

								$shipping_zone_info = array();
								if ($shipping_country_info && isset($order['shipping_address']['region'])) {
									$shipping_zone_info = $this->model_payment_klarna_checkout->getZoneByCode($order['shipping_address']['region'], $shipping_country_info['country_id']);
								}

								$order_data = array(
									'firstname'				  => utf8_substr($order['billing_address']['given_name'], 0, 32),
									'lastname'				  => utf8_substr($order['billing_address']['family_name'], 0, 32),
									'telephone'				  => utf8_substr($order['billing_address']['phone'], 0, 32),
									'payment_firstname'		  => utf8_substr($order['billing_address']['given_name'], 0, 32),
									'payment_lastname'		  => utf8_substr($order['billing_address']['family_name'], 0, 32),
									'payment_address_1'		  => utf8_substr($order['billing_address']['street_address'], 0, 128),
									'payment_address_2'		  => (isset($order['billing_address']['street_address2']) ? utf8_substr($order['billing_address']['street_address2'], 0, 128) : ''),
									'payment_city'			  => utf8_substr($order['billing_address']['city'], 0, 128),
									'payment_postcode'		  => utf8_substr($order['billing_address']['postal_code'], 0, 10),
									'payment_zone'			  => ($payment_zone_info ? $payment_zone_info['name'] : ''),
									'payment_zone_id'		  => ($payment_zone_info ? $payment_zone_info['zone_id'] : ''),
									'payment_country'		  => ($payment_country_info ? $payment_country_info['name'] : ''),
									'payment_country_id'	  => ($payment_country_info ? $payment_country_info['country_id'] : ''),
									'payment_address_format'  => ($payment_country_info ? $payment_country_info['address_format'] : ''),
									'shipping_firstname'	  => utf8_substr($order['shipping_address']['given_name'], 0, 32),
									'shipping_lastname'		  => utf8_substr($order['shipping_address']['family_name'], 0, 32),
									'shipping_address_1'	  => utf8_substr($order['shipping_address']['street_address'], 0, 128),
									'shipping_address_2'	  => (isset($order['shipping_address']['street_address2']) ? utf8_substr($order['shipping_address']['street_address2'], 0, 128) : ''),
									'shipping_city'			  => utf8_substr($order['shipping_address']['city'], 0, 128),
									'shipping_postcode'		  => utf8_substr($order['shipping_address']['postal_code'], 0, 10),
									'shipping_zone'			  => ($shipping_zone_info ? $shipping_zone_info['name'] : ''),
									'shipping_zone_id'		  => ($shipping_zone_info ? $shipping_zone_info['zone_id'] : ''),
									'shipping_country'		  => ($shipping_country_info ? $shipping_country_info['name'] : ''),
									'shipping_country_id'	  => ($shipping_country_info ? $shipping_country_info['country_id'] : ''),
									'shipping_address_format' => ($shipping_country_info ? $shipping_country_info['address_format'] : '')
								);

								$this->model_payment_klarna_checkout->updateOcOrder($klarna_checkout_order['order_id'], $order_data);

								$order_status_id = false;
								switch ($order['status']) {
									case 'AUTHORIZED':
										$order_status_id = $this->config->get('klarna_checkout_order_status_authorised_id');

										if ($order['fraud_status'] == 'PENDING') {
											$order_status_id = $this->config->get('klarna_checkout_order_status_fraud_pending_id');
										} elseif ($order['fraud_status'] == 'REJECTED') {
											$order_status_id = $this->config->get('klarna_checkout_order_status_fraud_rejected_id');
										}
										break;
									case 'PART_CAPTURED':
										$order_status_id = $this->config->get('klarna_checkout_order_status_part_captured_id');
										break;
									case 'CAPTURED':
										$order_status_id = $this->config->get('klarna_checkout_order_status_captured_id');
										break;
									case 'CANCELLED':
										$order_status_id = $this->config->get('klarna_checkout_order_status_cancelled_id');
										break;
								}

								if ($order_status_id) {
									$this->model_payment_klarna_checkout->addOrderHistory($klarna_checkout_order['order_id'], $order_status_id);
								}
							}
						} else {
							$this->model_payment_klarna_checkout->log('Cannot retrieve KC order using order_id: ' . $this->request->get['klarna_order_id']);
						}
					}
				}
			} else {
				$this->model_payment_klarna_checkout->log('Cannot find KC order using order_id: ' . $this->request->get['klarna_order_id']);
			}
		}
	}

    public function cartRemove() {
        $this->load->language('checkout/cart');

		if (!$this->config->get('klarna_checkout_status')) {
			return false;
		}

		$json = array();

		// Remove
		if (isset($this->request->post['remove'])) {
			$this->cart->remove($this->request->post['remove']);

			unset($this->session->data['vouchers'][$this->request->post['remove']]);

			$this->session->data['success'] = $this->language->get('text_remove');

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);
		}

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

	private function setPayment() {
		$this->load->model('account/address');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		if (isset($this->session->data['payment_address']) && !empty($this->session->data['payment_address'])) {
			$this->session->data['payment_address'] = $this->session->data['payment_address'];
		} elseif ($this->customer->isLogged() && $this->customer->getAddressId()) {
			$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
		} else {
			$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));

			$zone_info = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));

			$this->session->data['payment_address'] = array(
				'address_id'	 => null,
				'firstname'		 => null,
				'lastname'		 => null,
				'company'		 => null,
				'company_id'	 => null,
				'tax_id'         => null,
				'address_1'		 => null,
				'address_2'		 => null,
				'postcode'		 => null,
				'city'			 => null,
				'zone_id'		 => $zone_info['zone_id'],
				'zone'			 => $zone_info['name'],
				'zone_code'		 => $zone_info['code'],
				'country_id'	 => $country_info['country_id'],
				'country'		 => $country_info['name'],
				'iso_code_2'	 => $country_info['iso_code_2'],
				'iso_code_3'	 => $country_info['iso_code_3'],
				'address_format' => '',
				'custom_field'	 => array()
			);
		}

		$this->tax->setPaymentAddress($this->session->data['payment_address']['country_id'], $this->session->data['payment_address']['zone_id']);
		$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
	}

	private function setShipping() {
		$this->load->model('account/address');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

        if (isset($this->session->data['shipping_address']) && !empty($this->session->data['shipping_address'])) {
            $this->session->data['shipping_address'] = $this->session->data['shipping_address'];
        } elseif (isset($this->session->data['shipping_country_id']) && !empty($this->session->data['shipping_country_id'])) {
			$country_info = $this->model_localisation_country->getCountry($this->session->data['shipping_country_id']);

            if (isset($this->session->data['shipping_zone_id']) && !empty($this->session->data['shipping_zone_id'])) {
                $zone_info = $this->model_localisation_zone->getZone($this->session->data['shipping_zone_id']);
            } else {
                $zone_info = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));
            }

            if (isset($this->session->data['shipping_postcode']) && !empty($this->session->data['shipping_postcode'])) {
                $postcode = $this->session->data['shipping_postcode'];
            } else {
                $postcode = '';
            }

			$this->session->data['shipping_address'] = array(
				'address_id'	 => null,
				'firstname'		 => null,
				'lastname'		 => null,
				'company'		 => null,
				'address_1'		 => null,
				'address_2'		 => null,
				'postcode'		 => $postcode,
				'city'			 => null,
				'zone_id'		 => $zone_info['zone_id'],
				'zone'			 => $zone_info['name'],
				'zone_code'		 => $zone_info['code'],
				'country_id'	 => $country_info['country_id'],
				'country'		 => $country_info['name'],
				'iso_code_2'	 => $country_info['iso_code_2'],
				'iso_code_3'	 => $country_info['iso_code_3'],
				'address_format' => '',
				'custom_field'	 => null,
			);
        } elseif ($this->customer->isLogged() && $this->customer->getAddressId()) {
			$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
		} else {
			$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));

			$zone_info = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));

			$this->session->data['shipping_address'] = array(
				'address_id'	 => null,
				'firstname'		 => null,
				'lastname'		 => null,
				'company'		 => null,
				'address_1'		 => null,
				'address_2'		 => null,
				'postcode'		 => null,
				'city'			 => null,
				'zone_id'		 => $zone_info['zone_id'],
				'zone'			 => $zone_info['name'],
				'zone_code'		 => $zone_info['code'],
				'country_id'	 => $country_info['country_id'],
				'country'		 => $country_info['name'],
				'iso_code_2'	 => $country_info['iso_code_2'],
				'iso_code_3'	 => $country_info['iso_code_3'],
				'address_format' => '',
				'custom_field'	 => array()
			);
		}

		//$this->tax->unsetRates();
		$this->tax->setShippingAddress($this->session->data['shipping_address']['country_id'], $this->session->data['shipping_address']['zone_id']);
		$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

		if (isset($this->session->data['shipping_address'])) {
			$this->session->data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
			$this->session->data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
			$this->session->data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];

			// Shipping Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('shipping/' . $result['code']);

					$quote = $this->{'model_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

					if ($quote) {
						$method_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['shipping_methods'] = $method_data;
		}
	}

	private function createOrder() {
		//Klarna defaults:
		$this->session->data['comment'] = '';

		if (!$this->customer->isLogged()) {
			$this->session->data['guest'] = array(
				'customer_group_id' => $this->config->get('config_customer_group_id'),
				'firstname'			=> '',
				'lastname'			=> '',
				'email'				=> '',
				'telephone'			=> '',
				'fax'				=> '',
				'custom_field'		=> array(),
                'shipping'          => $this->session->data['shipping_address']
			);
		}

		//OpenCart:
		$order_data = array();

		list($total_data, $taxes, $total) = $this->model_payment_klarna_checkout->getTotals();

		$order_data['totals'] = $total_data;

		$this->load->language('checkout/checkout');

        $order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
        $order_data['store_id'] = $this->config->get('config_store_id');
        $order_data['store_name'] = $this->config->get('config_name');

        if ($order_data['store_id']) {
            $order_data['store_url'] = $this->config->get('config_url');
        } else {
            $order_data['store_url'] = HTTP_SERVER;
        }

        if ($this->customer->isLogged()) {
            $order_data['customer_id'] = $this->customer->getId();
            $order_data['customer_group_id'] = $this->customer->getCustomerGroupId();
            $order_data['firstname'] = $this->customer->getFirstName();
            $order_data['lastname'] = $this->customer->getLastName();
            $order_data['email'] = $this->customer->getEmail();
            $order_data['telephone'] = $this->customer->getTelephone();
            $order_data['fax'] = $this->customer->getFax();
        } elseif (isset($this->session->data['guest'])) {
            $order_data['customer_id'] = 0;
            $order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
            $order_data['firstname'] = $this->session->data['guest']['firstname'];
            $order_data['lastname'] = $this->session->data['guest']['lastname'];
            $order_data['email'] = $this->session->data['guest']['email'];
            $order_data['telephone'] = $this->session->data['guest']['telephone'];
            $order_data['fax'] = $this->session->data['guest']['fax'];
        }

        $order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
        $order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
        $order_data['payment_company'] = $this->session->data['payment_address']['company'];
        $order_data['payment_company_id'] = $this->session->data['payment_address']['company_id'];
        $order_data['payment_tax_id'] = $this->session->data['payment_address']['tax_id'];
        $order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
        $order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
        $order_data['payment_city'] = $this->session->data['payment_address']['city'];
        $order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
        $order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
        $order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
        $order_data['payment_country'] = $this->session->data['payment_address']['country'];
        $order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
        $order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];

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
            $order_data['shipping_method'] = '';
            $order_data['shipping_code'] = '';
        }

        $product_data = array();

        foreach ($this->cart->getProducts() as $product) {
            $option_data = array();

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['option_value'];
                } else {
                    $value = $this->encryption->decrypt($option['option_value']);
                }

                $option_data[] = array(
                    'product_option_id'       => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'option_id'               => $option['option_id'],
                    'option_value_id'         => $option['option_value_id'],
                    'name'                    => $option['name'],
                    'value'                   => $value,
                    'type'                    => $option['type']
                );
            }

            $product_data[] = array(
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'option'     => $option_data,
                'download'   => $product['download'],
                'quantity'   => $product['quantity'],
                'subtract'   => $product['subtract'],
                'price'      => $product['price'],
                'total'      => $product['total'],
                'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward'     => $product['reward']
            );
        }

        // Gift Voucher
        $voucher_data = array();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {
                $voucher_data[] = array(
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

        $order_data['products'] = $product_data;
        $order_data['vouchers'] = $voucher_data;
        $order_data['totals'] = $total_data;
        $order_data['comment'] = $this->session->data['comment'];
        $order_data['total'] = $total;

        $affiliateAppIsActive = (
            \Extension::isInstalled('affiliates') && $this->config->get('affiliates')['status']
        );

        if (isset($this->request->cookie['tracking']) && $affiliateAppIsActive) {
            $this->load->model('affiliate/affiliate');

            $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
            $subtotal = $this->cart->getSubTotal();

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

        $order_data['language_id'] = $this->config->get('config_language_id');
        $order_data['currency_id'] = $this->currency->getId();
        $order_data['currency_code'] = $this->currency->getCode();
        $order_data['currency_value'] = $this->currency->getValue($this->currency->getCode());
        $order_data['ip'] = $this->request->server['REMOTE_ADDR'];

        if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
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

		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
	}

	private function klarnaOrderData($klarna_account) {
		$this->load->language('payment/klarna_checkout');

		$this->load->model('payment/klarna_checkout');
		$this->load->model('localisation/country');

		$currency_code = $this->session->data['currency'];
		$currency_value = $this->currency->getValue($this->session->data['currency']);

		// Shipping
		$unset_shipping_method = true;
		if (isset($this->session->data['shipping_method']) && isset($this->session->data['shipping_methods'])) {
			foreach ($this->session->data['shipping_methods'] as $shipping_method) {
				if ($shipping_method['quote']) {
					foreach ($shipping_method['quote'] as $quote) {
						if ($quote == $this->session->data['shipping_method']) {
							$unset_shipping_method = false;
							break 2;
						}
					}
				}
			}
		}

		if ($unset_shipping_method) {
			unset($this->session->data['shipping_method']);
		}

		if ((!isset($this->session->data['shipping_method']) || empty($this->session->data['shipping_method'])) && (isset($this->session->data['shipping_methods']) && !empty($this->session->data['shipping_methods']))) {
			$this->session->data['shipping_method'] = $this->model_payment_klarna_checkout->getDefaultShippingMethod($this->session->data['shipping_methods']);
		}

		//Check if customer is US. If so, send taxes differently
		if ($this->session->data['shipping_address']['iso_code_2'] === 'US') {
			$include_taxes = "0";
		} else {
			$include_taxes = "1";
		}

		if ($this->cart->hasShipping() && isset($this->session->data['shipping_method']) && !empty($this->session->data['shipping_method'])) {
			$total_amount = $this->currency->format($this->tax->calculate($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id'], $include_taxes), $currency_code, $currency_value, false) * 100;

			if ($include_taxes) {
				$total_tax_amount = $this->currency->format($this->tax->getTax($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']), $currency_code, $currency_value, false) * 100;
			} else {
				$total_tax_amount = 0;
			}

			$total_sub_amount = $this->currency->format($this->tax->calculate($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id'], false), $currency_code, $currency_value, false) * 100;

			$tax_rate = 0;

			if ($include_taxes && $total_tax_amount && $total_sub_amount) {
				$tax_rate = ($total_tax_amount / $total_sub_amount) * 100;
			}

			$klarna_order_data['order_lines'][] = array(
				'type'					=> 'shipping_fee',
				'name'					=> $this->session->data['shipping_method']['title'],
				'quantity'				=> '1',
				'unit_price'			=> round($this->currency->format($this->tax->calculate($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id'], $include_taxes), $currency_code, $currency_value, false) * 100),
				'tax_rate'				=> round($tax_rate * 100),
				'total_amount'			=> round($total_amount),
				'total_tax_amount'		=> round($total_tax_amount),
				'total_discount_amount' => 0
			);
		}

		// Billing Address
		if (isset($this->session->data['klarna_checkout_data'])) {
			$klarna_order_data['billing_address'] = array(
				'given_name'  => $this->session->data['klarna_checkout_data']['firstname'],
				'family_name' => $this->session->data['klarna_checkout_data']['lastname'],
				'email'		  => $this->session->data['klarna_checkout_data']['email'],
				'phone'		  => $this->session->data['klarna_checkout_data']['telephone'],
				'postal_code' => $this->session->data['payment_address']['postcode'],
				'region'	  => $this->session->data['payment_address']['zone_code'],
				'country'	  => $this->session->data['payment_address']['iso_code_2'],
			);
		} else {
			$klarna_order_data['billing_address'] = array(
				'given_name'	  => $this->session->data['payment_address']['firstname'],
				'family_name'	  => $this->session->data['payment_address']['lastname'],
				'email'			  => ($this->customer->isLogged() ? $this->customer->getEmail() :  $this->session->data['payment_address']['email']),
				'phone'			  => ($this->customer->isLogged() ? $this->customer->getTelephone() : $this->session->data['payment_address']['telephone']),
				"street_address"  => $this->session->data['payment_address']['address_1'],
		        "street_address2" => $this->session->data['payment_address']['address_2'],
		        //"street_name"=> "",
		        //"street_number"=> "",
		        //"house_extension"=> "",
		        "postal_code"=> $this->session->data['payment_address']['postcode'],
		        "city"=> $this->session->data['payment_address']['city'],
		        "region"=> $this->session->data['payment_address']['zone_code'],
		        "country"=> $this->session->data['payment_address']['iso_code_2'],
		        //"care_of"=>""
			);
		}

		// Order Total
		list($totals, $taxes, $total) = $this->model_payment_klarna_checkout->getTotals();

		$merchant_urls = array(
			'checkout'	     => html_entity_decode($this->url->link('payment/klarna_checkout', 'klarna_order_id={checkout.order.id}', 'SSL')),
			'confirmation'   => html_entity_decode($this->url->link('payment/klarna_checkout/confirmation', 'klarna_order_id={checkout.order.id}', 'SSL')),
			'push'			 => html_entity_decode($this->url->link('payment/klarna_checkout/push', 'klarna_order_id={checkout.order.id}', 'SSL')),
			'validation'	 => html_entity_decode($this->url->link('payment/klarna_checkout/validation', 'klarna_order_id={checkout.order.id}', 'SSL')),

			//'address_update' => html_entity_decode($this->url->link('payment/klarna_checkout/addressUpdate', 'klarna_order_id={checkout.order.id}', 'SSL')),
			//'notification'	 => html_entity_decode($this->url->link('payment/klarna_checkout/notification', 'klarna_order_id={checkout.order.id}', 'SSL')),
			// 'checkout' => 'http://qaz123.expandcart.com/expandcartdev/OnlineStores/index.php?route=payment/klarna_checkout&klarna_order_id={checkout.order.id}',
   //          'confirmation' => 'http://qaz123.expandcart.com/expandcartdev/OnlineStores/index.php?route=payment/klarna_checkout/confirmation&klarna_order_id={checkout.order.id}',
   //          'push' => 'http://qaz123.expandcart.com/expandcartdev/OnlineStores/index.php?route=payment/klarna_checkout/push&klarna_order_id={checkout.order.id}',
   //          'validation' => 'https://qaz123.expandcart.com/expandcartdev/OnlineStores/index.php?route=payment/klarna_checkout/validation&klarna_order_id={checkout.order.id}',
            //'address_update' => 'https://qaz123.expandcart.com/expandcartdev/OnlineStores/index.php?route=payment/klarna_checkout/addressUpdate&klarna_order_id={checkout.order.id}',
            //'notification' => 'http://qaz123.expandcart.com/expandcartdev/OnlineStores/index.php?route=payment/klarna_checkout/notification&klarna_order_id={checkout.order.id}',
            //'terms' => 'http://qaz123.expandcart.com/expandcartdev/OnlineStores/index.php?route=information/information&information_id=4'
		);

		if ($this->config->get('klarna_checkout_terms')) {
			$merchant_urls['terms'] = html_entity_decode($this->url->link('information/information', 'information_id=' . $this->config->get('klarna_checkout_terms'), 'SSL'));
		}

        $country_info = $this->model_localisation_country->getCountry($klarna_account['country']);

        if ($country_info) {
            $klarna_order_data['purchase_country'] = $country_info['iso_code_2'];
        }

		$klarna_order_data['purchase_currency'] = $currency_code;
		$klarna_order_data['locale'] = $klarna_account['locale'];

		$klarna_order_data['order_amount'] = round($this->currency->format($total, $currency_code, $currency_value, false) * 100);
		$klarna_order_data['order_tax_amount'] = round($this->currency->format(array_sum($taxes), $currency_code, $currency_value, false) * 100);

		$klarna_order_data['merchant_urls'] = $merchant_urls;

		// Callback data to be used to spoof/simulate customer to accurately calculate shipping
		$encrypted_order_data = $this->encryption->encrypt(json_encode(array(
			'session_id'  => $this->session->getId(),
			'customer_id' => $this->customer->getId(),
			'order_id'	  => $this->session->data['order_id'],
			'merchant_id' => $klarna_account['merchant_id'],
			'secret'      => $klarna_account['secret']
		)));

		$encrypted_order_id = $this->encryption->encrypt($this->session->data['order_id']);

		$klarna_order_data['merchant_reference1'] = $this->session->data['order_id'];

		$klarna_order_data['options'] = array();

		if ($this->config->get('klarna_checkout_colour_button')) {
			$klarna_order_data['options']['color_button'] = $this->config->get('klarna_checkout_colour_button');
		}

		if ($this->config->get('klarna_checkout_colour_button_text')) {
			$klarna_order_data['options']['color_button_text'] = $this->config->get('klarna_checkout_colour_button_text');
		}

		if ($this->config->get('klarna_checkout_colour_checkbox')) {
			$klarna_order_data['options']['color_checkbox'] = $this->config->get('klarna_checkout_colour_checkbox');
		}

		if ($this->config->get('klarna_checkout_colour_checkbox_checkmark')) {
			$klarna_order_data['options']['color_checkbox_checkmark'] = $this->config->get('klarna_checkout_colour_checkbox_checkmark');
		}

		if ($this->config->get('klarna_checkout_colour_header')) {
			$klarna_order_data['options']['color_header'] = $this->config->get('klarna_checkout_colour_header');
		}

		if ($this->config->get('klarna_checkout_colour_link')) {
			$klarna_order_data['options']['color_link'] = $this->config->get('klarna_checkout_colour_link');
		}

		if ($this->config->get('klarna_checkout_separate_shipping_address')) {
			$klarna_order_data['options']['allow_separate_shipping_address'] = true;
		}

		// Only pass DOB/title mandatory for UK stores
		if ($country_info['iso_code_2'] == 'GB') {
			if ($this->config->get('klarna_checkout_dob_mandatory')) {
				$klarna_order_data['options']['date_of_birth_mandatory'] = true;
			}

			if ($this->config->get('klarna_checkout_title_mandatory')) {
				$klarna_order_data['options']['title_mandatory'] = true;
			}
		}

		if ($this->config->get('klarna_checkout_additional_text_box') && $this->customer->isLogged() && !$this->customer->getNewsletter()) {
			$klarna_order_data['options']['additional_checkbox'] = array(
				'text'	   => $this->language->get('text_newsletter'),
				'checked'  => false,
				'required' => false
			);
		}

        $shipping_countries = $this->model_payment_klarna_checkout->getCountriesByGeoZone($klarna_account['shipping']);

        $klarna_shipping_countries = array();
        foreach ($shipping_countries as $shipping_country) {
            $country_info = $this->model_localisation_country->getCountry($shipping_country['country_id']);

            if ($country_info && $country_info['iso_code_2']) {
                $klarna_shipping_countries[] = $country_info['iso_code_2'];
            }
        }

		$klarna_order_data['shipping_countries'] = $klarna_shipping_countries;

		$average_product_tax_rate = array();

		// Products (Add these last because we send encrypted session order_id)
		foreach ($this->cart->getProducts() as $product) {
			$total_amount = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $include_taxes) * $product['quantity'], $currency_code, $currency_value, false) * 100;

			if ($include_taxes) {
				$total_tax_amount = $this->currency->format($this->tax->getTax($product['price'], $product['tax_class_id']) * $product['quantity'], $currency_code, $currency_value, false) * 100;
			} else {
				$total_tax_amount = 0;
			}

			$total_sub_amount = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], false) * $product['quantity'], $currency_code, $currency_value, false) * 100;

			$tax_rate = 0;

			if ($include_taxes && $total_tax_amount && $total_sub_amount) {
				$tax_rate = ($total_tax_amount / $total_sub_amount) * 100;
			}

			$average_product_tax_rate[] = round($tax_rate * 100);

			$klarna_order_data['order_lines'][] = array(
				'type'					=> ($product['shipping'] ? 'physical' : 'digital'),
				'reference'				=> $product['model'],
				'name'					=> $product['name'],
				'quantity'				=> $product['quantity'],
				'quantity_unit'			=> 'pcs',
				'unit_price'			=> round($this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $include_taxes), $currency_code, $currency_value, false) * 100),
				'tax_rate'				=> round($tax_rate * 100),
				'total_amount'			=> round($total_amount),
				'total_tax_amount'		=> round($total_tax_amount),
				'merchant_data'         => $encrypted_order_id,
				'total_discount_amount' => 0
			);
		}

		// Gift Voucher
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$klarna_order_data['order_lines'][] = array(
					'type'					=> 'gift_card',
					'reference'				=> '',
					'name'					=> $voucher['description'],
					'quantity'				=> 1,
					'quantity_unit'			=> 'pcs',
					'unit_price'			=> round($this->currency->format($voucher['amount'], $currency_code, $currency_value, false) * 100),
					'tax_rate'				=> 0,
					'total_amount'			=> round($this->currency->format($voucher['amount'], $currency_code, $currency_value, false) * 100),
					'total_tax_amount'		=> 0,
					'merchant_data'         => $encrypted_order_id,
					'total_discount_amount' => 0
				);
			}
		}

		foreach ($totals as $result) {
			if ($result['code'] == 'coupon') {
				$discount_total_price = 0;
				$discount_sub_total_price = 0;
				foreach ($this->cart->getProducts() as $product) {
					$discount_total_price += $this->tax->calculate($result['value'], $product['tax_class_id'], $include_taxes);
					$discount_sub_total_price += $result['value'];
				}

				$average_discount_total_price = $discount_total_price / count($average_product_tax_rate);

				$average_discount_sub_total_price = $discount_sub_total_price / count($average_product_tax_rate);

				$total_tax_amount = ($average_discount_sub_total_price / 100) * (array_sum($average_product_tax_rate) / count($average_product_tax_rate));

				$klarna_order_data['order_lines'][] = array(
					'type'					=> 'discount',
					'name'					=> $result['title'],
					'quantity'				=> '1',
					'unit_price'			=> round($this->currency->format($average_discount_total_price, $currency_code, $currency_value, false) * 100),
					'tax_rate'				=> array_sum($average_product_tax_rate) / count($average_product_tax_rate),
					'total_amount'			=> round($this->currency->format($average_discount_total_price, $currency_code, $currency_value, false) * 100),
					'total_tax_amount'		=> round($total_tax_amount),
					'total_discount_amount' => 0
				);
			}

			if ($result['code'] == 'voucher') {
				$klarna_order_data['order_lines'][] = array(
					'type'					=> 'discount',
					'name'					=> $result['title'],
					'quantity'				=> '1',
					'unit_price'			=> round($this->currency->format($result['value'], $currency_code, $currency_value, false) * 100),
					'tax_rate'				=> 0,
					'total_amount'			=> round($this->currency->format($result['value'], $currency_code, $currency_value, false) * 100),
					'total_tax_amount'		=> 0,
					'total_discount_amount' => 0
				);
			}
		}

		//If $include_taxes is false, means customer is US so we add a new sales_tax order line with all the tax
		if (!$include_taxes) {
			$klarna_order_data['order_lines'][] = array(
				'type'					=> 'sales_tax',
				'name'					=> $this->language->get('text_sales_tax'),
				'quantity'				=> '1',
				'unit_price'			=> round($this->currency->format(array_sum($taxes), $currency_code, $currency_value, false) * 100),
				'tax_rate'				=> 0,
				'total_amount'			=> round($this->currency->format(array_sum($taxes), $currency_code, $currency_value, false) * 100),
				'total_tax_amount'		=> 0,
				'total_discount_amount' => 0
			);
		}
		return array($klarna_order_data, $encrypted_order_data);
	}
}
