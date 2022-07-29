<?php
class ControllerPaymentPPExpress extends Controller {
	public function index() {
		
		$this->load->language('payment/pp_express');
		
		$settings = $this->config->get('pp_express');

		if ($settings['test'] == 1) {
			$this->data['paypal_environment'] = "sandbox";
		} else {
			$this->data['paypal_environment'] = "production";
		}

		if(isset($this->session->data['error_pp_express'])){
	        $this->data['error_pp_express'] = $this->session->data['error_pp_express']['resp_msg']; 
	        unset($this->session->data['error_pp_express']); 
	     }

		$this->data['continue'] = $this->url->link('payment/pp_express/checkout&page=checkout');
		unset($this->session->data['paypal']);

		$this->template = 'default/template/payment/pp_express.expand';
		$this->render_ecwig();
	}

    /**
     *
     */
	public function checkout(): void
    {
		$page_source = $_GET['page'];
		
		unset($this->session->data['error_pp_express']);

		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		$this->load->model('payment/pp_express');
		$this->load->model('tool/image');
		$this->load->model('checkout/order');
        $this->load->model('checkout/coupon');

        $max_amount = $this->cart->getTotal() * 1.5;
		$max_amount = $this->currency->format($max_amount, $this->session->data['currency'], '', false);
			$shipping = 1;
			$data_shipping = array();

		$data = array(
			'METHOD'             => 'SetExpressCheckout',
			'MAXAMT'             => $max_amount,
			'RETURNURL'          => $this->url->link('payment/pp_express/checkoutReturn&page=' .$page_source),
			'CANCELURL'          => $this->url->link('checkout/'.$page_source),
			'REQCONFIRMSHIPPING' => 0,
			'NOSHIPPING'         => $shipping,
			//'LOCALECODE'         => 'EN',
			//'LANDINGPAGE'        => 'Login',
			//'HDRIMG'             => $this->model_tool_image->resize(html_entity_decode($this->config->get('payment_pp_express_logo'), ENT_QUOTES, 'UTF-8'), 750, 90),
			//'PAYFLOWCOLOR'       => $this->config->get('payment_pp_express_colour'),
			'CHANNELTYPE'        => 'Merchant',
			//'ALLOWNOTE'          => $this->config->get('payment_pp_express_allow_note')
		);
		$data = array_merge($data, $data_shipping);
		if (isset($this->session->data['pp_login']['seamless']['access_token']) && (isset($this->session->data['pp_login']['seamless']['customer_id']) && $this->session->data['pp_login']['seamless']['customer_id'] == $this->customer->getId()) && $this->config->get('module_pp_login_seamless')) {
			$data['IDENTITYACCESSTOKEN'] = $this->session->data['pp_login']['seamless']['access_token'];
		}
		$data = array_merge($data, $this->model_payment_pp_express->paymentRequestInfo());
		$result = $this->model_payment_pp_express->call($data);
		/**
		 * If a failed PayPal setup happens, handle it.
		 */
		if (!isset($result['TOKEN'])) {
			$this->session->data['error_pp_express']['resp_msg'] = "PayPal request failed, please contact the store owner";
			if (isset($result['L_ERRORCODE0'])) {
				$this->session->data['error_pp_express']['resp_msg'] = "[Error code: " . (string)$result['L_ERRORCODE0'] . "]";
			}
			if (isset($result['L_SHORTMESSAGE0'])) {
				$this->session->data['error_pp_express']['resp_msg'] .= " " . (string)$result['L_SHORTMESSAGE0'] . "\r\n";
			}
			if (isset($result['L_LONGMESSAGE0'])) {
				$this->session->data['error_pp_express']['resp_msg'] .= (string)$result['L_LONGMESSAGE0'];
			}
			/**
			 * Unable to add error message to user as the session errors/success are not
			 * used on the cart or checkout pages - need to be added?
			 * If PayPal debug log is off then still log error to normal error log.
			 */
			
			//$this->log->write('Unable to create Paypal session' . json_encode($result));

			//ATH 
			$json = [
                "status" => $result['ACK'],
                'L_SEVERITYCODE0' => $result['L_SEVERITYCODE0'],
                'L_ERRORCODE0'   => $result['L_ERRORCODE0']
            ];
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			// $this->response->redirect($this->url->link('checkout/'.$page_source));
		}
		$json = array("token" => $result['TOKEN']);
		$this->session->data['paypal']['token'] = $result['TOKEN'];
		$this->response->setOutput(json_encode($json));
	}

	public function checkoutReturn() {

		$page_source = $_GET['page'];

		$settings = $this->config->get('pp_express');

		$this->load->language('payment/pp_express');
		$this->load->model('payment/pp_express');
		$this->load->model('checkout/order');
		$data = array(
			'METHOD' => 'GetExpressCheckoutDetails',
			'TOKEN'  => $this->session->data['paypal']['token']
		);
		$result = $this->model_payment_pp_express->call($data);

		$this->session->data['paypal']['payerid'] = $result['PAYERID'];
		$this->session->data['paypal']['result'] = $result;
		$order_id = $this->session->data['order_id'];
		$paypal_data = array(
			'TOKEN'                      => $this->session->data['paypal']['token'],
			'PAYERID'                    => $this->session->data['paypal']['payerid'],
			'METHOD'                     => 'DoExpressCheckoutPayment',
			'PAYMENTREQUEST_0_NOTIFYURL' => $this->url->link('payment/pp_express/ipn'),
			'RETURNFMFDETAILS'           => 1,
            "BUTTONSOURCE"               => "ExpandCart_Cart_MEA"
		);

		$paymentRequestInfo = $this->model_payment_pp_express->paymentRequestInfo();
		$paymentRequestInfo['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';
		$paypal_data = array_merge($paypal_data, $paymentRequestInfo);
		
		$result = $this->model_payment_pp_express->call($paypal_data);

		if ($result['ACK'] == 'Success') {
			//handle order status
			switch($result['PAYMENTINFO_0_PAYMENTSTATUS']) {
				case 'Canceled_Reversal':
					$order_status_id = $settings['canceled_reversal_status_id'];
					break;
				case 'Completed':
					$order_status_id = $settings['completed_status_id'];
					break;
				case 'Denied':
					$order_status_id = $settings['denied_status_id'];
					break;
				case 'Expired':
					$order_status_id = $settings['expired_status_id'];
					break;
				case 'Failed':
					$order_status_id = $settings['failed_status_id'];
					break;
				case 'Pending':
					$order_status_id = $settings['pending_status_id'];
					break;
				case 'Processed':
					$order_status_id = $settings['processed_status_id'];
					break;
				case 'Refunded':
					$order_status_id = $settings['refunded_status_id'];
					break;
				case 'Reversed':
					$order_status_id = $settings['reversed_status_id'];
					break;
				case 'Voided':
					$order_status_id = $settings['voided_status_id'];
					break;
				default :
					$order_status_id = $settings['default_status_id'];	
			}
			
			//add order to paypal table
			$paypal_order_data = array(
				'order_id'         => $order_id,
				'capture_status'   => ($settings['transaction'] == 'Sale' ? 'Complete' : 'NotComplete'),
				'currency_code'    => $result['PAYMENTINFO_0_CURRENCYCODE'],
				'authorization_id' => $result['PAYMENTINFO_0_TRANSACTIONID'],
				'total'            => $result['PAYMENTINFO_0_AMT']
			);


			$paypal_order_id = $this->model_payment_pp_express->addPPOrder($paypal_order_data);
			//add transaction to paypal transaction table
			$paypal_transaction_data = array(
				'paypal_order_id'       => $paypal_order_id,
				'transaction_id'        => $result['PAYMENTINFO_0_TRANSACTIONID'],
				'parent_id' => '',
				'note'                  => '',
				'msgsubid'              => '',
				'receipt_id'            => (isset($result['PAYMENTINFO_0_RECEIPTID']) ? $result['PAYMENTINFO_0_RECEIPTID'] : ''),
				'payment_type'          => $result['PAYMENTINFO_0_PAYMENTTYPE'],
				'payment_status'        => $result['PAYMENTINFO_0_PAYMENTSTATUS'],
				'pending_reason'        => $result['PAYMENTINFO_0_PENDINGREASON'],
				'transaction_entity'    => ($settings['transaction'] == 'Sale' ? 'payment' : 'auth'),
				'amount'                => $result['PAYMENTINFO_0_AMT'],
				'debug_data'            => json_encode($result)
			);
			$this->model_payment_pp_express->addTransaction($paypal_transaction_data);

			///Update order address and customer info
			$pp_checkout_details = $this->session->data['paypal']['result'];
			$this->model_payment_pp_express->orderUpdateData($pp_checkout_details, $this->session->data['order_id']);
			/////////////////////////////////////////

			$this->load->model('checkout/order');
			$this->model_checkout_order->confirm($this->session->data['order_id'], $order_status_id);

			if (isset($result['REDIRECTREQUIRED']) && $result['REDIRECTREQUIRED'] == true) {
				//- handle german redirect here
				$this->response->redirect('https://www.paypal.com/cgi-bin/webscr?cmd=_complete-express-checkout&token=' . $this->session->data['paypal']['token']);
			} else {
				$this->response->redirect($this->url->link('checkout/success', 'language=' . $this->config->get('config_language')));
			}
		} else {
			if ($result['L_ERRORCODE0'] == '10486') {
				if (isset($this->session->data['paypal_redirect_count'])) {
					if ($this->session->data['paypal_redirect_count'] == 2) {
						$this->session->data['paypal_redirect_count'] = 0;
						$this->session->data['error'] = $this->language->get('error_too_many_failures');
						$this->response->redirect($this->url->link('checkout/'.$page_source));
					} else {
						$this->session->data['paypal_redirect_count']++;
					}
				} else {
					$this->session->data['paypal_redirect_count'] = 1;
				}
				if ($settings['test'] == 1) {
					$this->response->redirect('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $this->session->data['paypal']['token']);
				} else {
					$this->response->redirect('https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $this->session->data['paypal']['token']);
				}
			}

			$this->session->data['error_pp_express']['resp_msg'] = "Payment Proccess Failed!";
			if (isset($result['L_ERRORCODE0'])) {
				$this->session->data['error_pp_express']['resp_msg'] = "[Error code: " . (string)$result['L_ERRORCODE0'] . "]";
			}
			if (isset($result['L_SHORTMESSAGE0'])) {
				$this->session->data['error_pp_express']['resp_msg'] .= " " . (string)$result['L_SHORTMESSAGE0'] . "\r\n";
			}
			if (isset($result['L_LONGMESSAGE0'])) {
				$this->session->data['error_pp_express']['resp_msg'] .= (string)$result['L_LONGMESSAGE0'];
			}

			$this->response->redirect($this->url->link('checkout/'.$page_source));
		}
	}

	public function ipn() {

		$settings = $this->config->get('pp_express');

		$this->load->model('payment/pp_express');
		//$this->load->model('account/recurring');
		$request = 'cmd=_notify-validate';
		foreach ($this->request->post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		if ($settings['test'] == 1) {
			$curl = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
		} else {
			$curl = curl_init('https://www.paypal.com/cgi-bin/webscr');
		}
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$curl_response = curl_exec($curl);
		$curl_response = trim($curl_response);
		if (!$curl_response) {
			$this->model_payment_pp_express->log(array('error' => curl_error($curl), 'error_no' => curl_errno($curl)), 'Curl failed');
		}
		$this->model_payment_pp_express->log(array('request' => $request, 'response' => $curl_response), 'IPN data');
		if ((string)$curl_response == "VERIFIED")  {
			if (isset($this->request->post['transaction_entity'])) {
				$this->log->write($this->request->post['transaction_entity']);
			}
			if (isset($this->request->post['txn_id'])) {
				$transaction = $this->model_payment_pp_express->getTransactionRow($this->request->post['txn_id']);
			} else {
				$transaction = false;
			}
			if (isset($this->request->post['parent_txn_id'])) {
				$parent_transaction = $this->model_payment_pp_express->getTransactionRow($this->request->post['parent_txn_id']);
			} else {
				$parent_transaction = false;
			}
			if ($transaction) {
				//transaction exists, check for cleared payment or updates etc
				$this->model_payment_pp_express->log('Transaction exists', 'IPN data');
				//if the transaction is pending but the new status is completed
				if ($transaction['payment_status'] != $this->request->post['payment_status']) {
					$this->db->query("UPDATE `" . DB_PREFIX . "paypal_order_transaction` SET `payment_status` = '" . $this->db->escape($this->request->post['payment_status']) . "' WHERE `transaction_id` = '" . $this->db->escape($transaction['transaction_id']) . "' LIMIT 1");
				} elseif ($transaction['payment_status'] == 'Pending' && ($transaction['pending_reason'] != $this->request->post['pending_reason'])) {
					//payment is still pending but the pending reason has changed, update it.
					$this->db->query("UPDATE `" . DB_PREFIX . "paypal_order_transaction` SET `pending_reason` = '" . $this->db->escape($this->request->post['pending_reason']) . "' WHERE `transaction_id` = '" . $this->db->escape($transaction['transaction_id']) . "' LIMIT 1");
				}
			} else {
				$this->model_payment_pp_express->log('Transaction does not exist', 'IPN data');
				if ($parent_transaction) {
					//parent transaction exists
					$this->model_payment_pp_express->log('Parent transaction exists', 'IPN data');
					//add new related transaction
					$transaction = array(
						'paypal_order_id'       => $parent_transaction['paypal_order_id'],
						'transaction_id'        => $this->request->post['txn_id'],
						'parent_id'             => $this->request->post['parent_txn_id'],
						'note'                  => '',
						'msgsubid'              => '',
						'receipt_id'            => (isset($this->request->post['receipt_id']) ? $this->request->post['receipt_id'] : ''),
						'payment_type'          => (isset($this->request->post['payment_type']) ? $this->request->post['payment_type'] : ''),
						'payment_status'        => (isset($this->request->post['payment_status']) ? $this->request->post['payment_status'] : ''),
						'pending_reason'        => (isset($this->request->post['pending_reason']) ? $this->request->post['pending_reason'] : ''),
						'amount'                => $this->request->post['mc_gross'],
						'debug_data'            => json_encode($this->request->post),
						'transaction_entity'    => (isset($this->request->post['transaction_entity']) ? $this->request->post['transaction_entity'] : '')
					);
					$this->model_payment_pp_express->addTransaction($transaction);
					/**
					 * If there has been a refund, log this against the parent transaction.
					 */
					if (isset($this->request->post['payment_status']) && $this->request->post['payment_status'] == 'Refunded') {
						if (($this->request->post['mc_gross'] * -1) == $parent_transaction['amount']) {
							$this->db->query("UPDATE `" . DB_PREFIX . "paypal_order_transaction` SET `payment_status` = 'Refunded' WHERE `transaction_id` = '" . $this->db->escape($parent_transaction['transaction_id']) . "' LIMIT 1");
						} else {
							$this->db->query("UPDATE `" . DB_PREFIX . "paypal_order_transaction` SET `payment_status` = 'Partially-Refunded' WHERE `transaction_id` = '" . $this->db->escape($parent_transaction['transaction_id']) . "' LIMIT 1");
						}
					}
					/**
					 * If the capture payment is now complete
					 */
					if (isset($this->request->post['auth_status']) && $this->request->post['auth_status'] == 'Completed' && $parent_transaction['payment_status'] == 'Pending') {
						$captured = $this->currency->format($this->model_payment_pp_express->getTotalCaptured($parent_transaction['paypal_order_id']), $this->session->data['currency'], false, false);
						$refunded = $this->currency->format($this->model_payment_pp_express->getRefundedTotal($parent_transaction['paypal_order_id']), $this->session->data['currency'], false, false);
						$remaining = $this->currency->format($parent_transaction['amount'] - $captured + $refunded, $this->session->data['currency'], false, false);
						$this->model_payment_pp_express->log('Captured: ' . $captured, 'IPN data');
						$this->model_payment_pp_express->log('Refunded: ' . $refunded, 'IPN data');
						$this->model_payment_pp_express->log('Remaining: ' . $remaining, 'IPN data');
						if ($remaining > 0.00) {
							$transaction = array(
								'paypal_order_id'       => $parent_transaction['paypal_order_id'],
								'transaction_id'        => '',
								'parent_id' 			=> $this->request->post['parent_txn_id'],
								'note'                  => '',
								'msgsubid'              => '',
								'receipt_id'            => '',
								'payment_type'          => '',
								'payment_status'        => 'Void',
								'pending_reason'        => '',
								'amount'                => '',
								'debug_data'            => 'Voided after capture',
								'transaction_entity'    => 'auth'
							);
							$this->model_payment_pp_express->addTransaction($transaction);
						}
						$this->model_payment_pp_express->updatePPOrder('Complete', $parent_transaction['order_id']);
					}
				} else {
					//parent transaction doesn't exists, need to investigate?
					$this->model_payment_pp_express->log('Parent transaction not found', 'IPN data');
				}
			}
			/*
			 * Subscription payments
			 *
			 * recurring ID should always exist if its a recurring payment transaction.
			 *
			 * also the reference will match a recurring payment ID
			 */
			/*if (isset($this->request->post['txn_type'])) {
				$this->model_extension_payment_pp_express->log($this->request->post['txn_type'], 'IPN data');
				//payment
				if ($this->request->post['txn_type'] == 'recurring_payment') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					$this->model_extension_payment_pp_express->log($recurring, 'IPN data');
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `amount` = '" . (float)$this->request->post['amount'] . "', `type` = '1'");
						//as there was a payment the recurring is active, ensure it is set to active (may be been suspended before)
						if ($recurring['status'] != 1) {
							$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 1 WHERE `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "'");
						}
					}
				}
				//suspend
				if ($this->request->post['txn_type'] == 'recurring_payment_suspended') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '6'");
						$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 4 WHERE `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "' LIMIT 1");
					}
				}
				//suspend due to max failed
				if ($this->request->post['txn_type'] == 'recurring_payment_suspended_due_to_max_failed_payment') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '6'");
						$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 4 WHERE `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "' LIMIT 1");
					}
				}
				//payment failed
				if ($this->request->post['txn_type'] == 'recurring_payment_failed') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '4'");
					}
				}
				//outstanding payment failed
				if ($this->request->post['txn_type'] == 'recurring_payment_outstanding_payment_failed') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '8'");
					}
				}
				//outstanding payment
				if ($this->request->post['txn_type'] == 'recurring_payment_outstanding_payment') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `amount` = '" . (float)$this->request->post['amount'] . "', `type` = '2'");
						//as there was a payment the recurring is active, ensure it is set to active (may be been suspended before)
						if ($recurring['status'] != 1) {
							$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 1 WHERE `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "'");
						}
					}
				}
				//date_added
				if ($this->request->post['txn_type'] == 'recurring_payment_profile_date_added') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '0'");
						if ($recurring['status'] != 1) {
							$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 6 WHERE `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "'");
						}
					}
				}
				//cancelled
				if ($this->request->post['txn_type'] == 'recurring_payment_profile_cancel') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false && $recurring['status'] != 3) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '5'");
						$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 3 WHERE `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "' LIMIT 1");
					}
				}
				//skipped
				if ($this->request->post['txn_type'] == 'recurring_payment_skipped') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '3'");
					}
				}
				//expired
				if ($this->request->post['txn_type'] == 'recurring_payment_expired') {
					$recurring = $this->model_account_recurring->getOrderRecurringByReference($this->request->post['recurring_payment_id']);
					if ($recurring != false) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '9'");
						$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 5 WHERE `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "' LIMIT 1");
					}
				}
			}*/
		} elseif ((string)$curl_response == "INVALID") {
			$this->model_payment_pp_express->log(array('IPN was invalid'), 'IPN fail');
		} else {
			$this->model_payment_pp_express->log('Response string unknown: ' . (string)$curl_response, 'IPN data');
		}
		header("HTTP/1.1 200 Ok");
	}
}
?>