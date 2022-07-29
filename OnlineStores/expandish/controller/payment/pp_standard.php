<?php
class ControllerPaymentPPStandard extends Controller {
	protected function index() {
		$this->language->load_json('payment/pp_standard');
		
		$this->data['testmode'] = $this->config->get('pp_standard_test');
		
		if (!$this->config->get('pp_standard_test')) {
    		$this->data['action'] = 'https://www.paypal.com/cgi-bin/webscr';
  		} else {
			$this->data['action'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {
            $currencies = array(
                'AUD',
                'CAD',
                'EUR',
                'GBP',
                'JPY',
                'USD',
                'NZD',
                'CHF',
                'HKD',
                'SGD',
                'SEK',
                'DKK',
                'PLN',
                'NOK',
                'HUF',
                'CZK',
                'ILS',
                'MXN',
                'MYR',
                'BRL',
                'PHP',
                'TWD',
                'THB',
                'TRY'
            );

            if (!in_array($order_info['currency_code'], $currencies)) {
                $order_info['currency_code'] = 'USD';
            }

            if (
                isset($this->session->data['ms_independent_payments']) &&
                $this->session->data['ms_independent_payments']['status'] == true
            ) {
                $this->data['business'] = nl2br($this->session->data['ms_independent_payments']['paypal']);
            } else {
                $this->data['business'] = $this->config->get('pp_standard_email');
            }

			$this->data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');				
			
			$this->data['products'] = array();
			
			foreach ($this->cart->getProducts() as $product) {
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
				
				$this->data['products'][] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'price'    => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
					'quantity' => $product['quantity'],
					'option'   => $option_data,
					'weight'   => $product['weight']
				);
			}	
			
			$this->data['discount_amount_cart'] = 0;
			
			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

			if ($total > 0) {
				$this->data['products'][] = array(
					'name'     => $this->language->get('text_total'),
					'model'    => '',
					'price'    => $total,
					'quantity' => 1,
					'option'   => array(),
					'weight'   => 0
				);	
			} else {
				$this->data['discount_amount_cart'] -= $total;
			}
			
			$this->data['currency_code'] = $order_info['currency_code'];
			$this->data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');	
			$this->data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');	
			$this->data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');	
			$this->data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');	
			$this->data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');	
			$this->data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');	
			$this->data['country'] = $order_info['payment_iso_code_2'];
			$this->data['email'] = $order_info['email'];
			$this->data['invoice'] = 'ORD-' . $this->session->data['order_id'];
			$this->data['lc'] = $this->session->data['language'];
			$this->data['return'] =  $this->url->link('payment/pp_standard/callback', '', 'SSL'); //$this->url->link('checkout/success');
			$this->data['notify_url'] = $this->url->link('payment/pp_standard/callback', '', 'SSL');
			$this->data['cancel_return'] = $this->url->link('checkout/checkout', '', 'SSL');
			
			if (!$this->config->get('pp_standard_transaction')) {
				$this->data['paymentaction'] = 'authorization';
			} else {
				$this->data['paymentaction'] = 'sale';
			}

            $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

            if($queryRewardPointInstalled->num_rows) {
                $this->data['custom'] = $this->session->data['order_id'];
                $this->data['custom'] = base64_encode(serialize(array(
                    'order_id'  => $this->session->data['order_id'],
                    'total_reward_points'  => $this->session->data['total_reward_points'],
                    'points_to_checkout'  => $this->session->data['points_to_checkout']
                )));
            } else {
			    $this->data['custom'] = $this->session->data['order_id'];
            }
		
            // if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/pp_standard.expand')) {
            //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/pp_standard.expand';
            // }
            // else {
            //     $this->template = $this->config->get('config_template') . '/template/payment/pp_standard.expand';
            // }
            
            $this->template = 'default/template/payment/pp_standard.expand';
	
			$this->render_ecwig();
		}
	}
	
	public function callback() {
		if (isset($this->request->post['custom'])) {
			$order_id = $this->request->post['custom'];
		} else {
			$order_id = 0;
		}		
		
		$this->load->model('checkout/order');

        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $decrypt_custom = unserialize(base64_decode($order_id));
            $order_id = $decrypt_custom['order_id'];
        }
				
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		if ($order_info) {
            $currencies = array(
                'AUD',
                'CAD',
                'EUR',
                'GBP',
                'JPY',
                'USD',
                'NZD',
                'CHF',
                'HKD',
                'SGD',
                'SEK',
                'DKK',
                'PLN',
                'NOK',
                'HUF',
                'CZK',
                'ILS',
                'MXN',
                'MYR',
                'BRL',
                'PHP',
                'TWD',
                'THB',
                'TRY'
            );

            if (!in_array($order_info['currency_code'], $currencies)) {
                $order_info['currency_code'] = 'USD';
            }

			$request = 'cmd=_notify-validate';
		
			foreach ($this->request->post as $key => $value) {
				$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
			}
			
			if (!$this->config->get('pp_standard_test')) {
				$curl = curl_init('https://www.paypal.com/cgi-bin/webscr');
			} else {
				$curl = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
			}

			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					
			$response = curl_exec($curl);
			
			if (!$response) {
				$this->log->write('PP_STANDARD :: CURL failed ' . curl_error($curl) . '(' . curl_errno($curl) . ')');
			}
					
			if ($this->config->get('pp_standard_debug')) {
				$this->log->write('PP_STANDARD :: IPN REQUEST: ' . $request);
				$this->log->write('PP_STANDARD :: IPN RESPONSE: ' . $response);
			}

						
			if (isset($this->request->post['payment_status'])) {
				$order_status_id = $this->config->get('config_order_status_id');
				
				switch($this->request->post['payment_status']) {
					case 'Canceled_Reversal':
						$order_status_id = $this->config->get('pp_standard_canceled_reversal_status_id');
						break;
					case 'Completed':
						if ((strtolower($this->request->post['receiver_email']) == strtolower($this->config->get('pp_standard_email'))) && ((float)$this->request->post['mc_gross'] == $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false))) {
							$order_status_id = $this->config->get('pp_standard_completed_status_id');
						} else {
							$this->log->write('PP_STANDARD :: RECEIVER EMAIL MISMATCH! ' . strtolower($this->request->post['receiver_email']));
						}
						break;
					case 'Denied':
						$order_status_id = $this->config->get('pp_standard_denied_status_id');
						break;
					case 'Expired':
						$order_status_id = $this->config->get('pp_standard_expired_status_id');
						break;
					case 'Failed':
						$order_status_id = $this->config->get('pp_standard_failed_status_id');
						break;
					case 'Pending':
						$order_status_id = $this->config->get('pp_standard_pending_status_id');
						break;
					case 'Processed':
						$order_status_id = $this->config->get('pp_standard_processed_status_id');
						break;
					case 'Refunded':
						$order_status_id = $this->config->get('pp_standard_refunded_status_id');
						break;
					case 'Reversed':
						$order_status_id = $this->config->get('pp_standard_reversed_status_id');
						break;	 
					case 'Voided':
						$order_status_id = $this->config->get('pp_standard_voided_status_id');
						break;								
				}
				
				if (!$order_info['order_status_id']) {
					$this->model_checkout_order->confirm($order_id, $order_status_id);
                    if ($this->customer->isLogged()) {
                        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                        if ($queryRewardPointInstalled->num_rows) {
                            $this->log->write("Before update reward points 1");
                            $this->load->model('rewardpoints/observer');//
                            $this->model_rewardpoints_observer->afterPlaceOrder($order_id, $decrypt_custom);
                        }
                    }
				} else {
					$this->model_checkout_order->update($order_id, $order_status_id);
                    if ($this->customer->isLogged()) {
                        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                        if ($queryRewardPointInstalled->num_rows) {
                            $this->log->write("Before update reward points 1");
                            $this->load->model('rewardpoints/observer');//
                            $this->model_rewardpoints_observer->afterPlaceOrder($order_id, $decrypt_custom);
                        }
                    }
				}
			} else {
				$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
                if ($this->customer->isLogged()) {
                    $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                    if ($queryRewardPointInstalled->num_rows) {
                        $this->log->write("Before update reward points 1");
                        $this->load->model('rewardpoints/observer');//
                        $this->model_rewardpoints_observer->afterPlaceOrder($order_id, $decrypt_custom);
                    }
                }
			}
			
			curl_close($curl);

			$this->response->redirect($this->url->link('checkout/success'));
		}	
	}
}
?>
