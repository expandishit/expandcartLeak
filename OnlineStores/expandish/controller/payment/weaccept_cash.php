<?php

/**
 * Store front for WeAccept Cash
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ControllerPaymentWeAcceptCash extends Controller
{

	/**
	 * Main handle function
	 */
	public function index()
	{
		if ($this->request->post) {
			// inc models
			$this->load->model('account/customer');
			$this->load->model('checkout/order');
			$this->load->model('payment/weaccept_cash');
			$this->language->load_json('payment/weaccept_cash');

			//Check if user has selected a city
			if (!$this->request->post['weaccept_cash_city']) {
				$this->data['error_no_city'] = $this->language->get('error_city_not_selected');

				//Return cities data to view
				$this->data['cities'] = $this->model_payment_weaccept_cash->getSupportedCties();
				$this->data['cash_info_message'] = $this->language->get('cash_info_message');
				$this->data['cash_btn_confirm'] = $this->language->get('cash_btn_confirm');
				$this->data['cash_alert_msg'] = $this->language->get('cash_alert_msg');

				// if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/weaccept_cash.expand')) {
				// 	$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/weaccept_cash.expand';
				// } else {
				// 	$this->template = 'default/template/payment/weaccept_cash.expand';
				// }
                $this->template = 'default/template/payment/weaccept_cash.expand';
				$this->render_ecwig();
			}

			//Set the user selected state and city
			$selected_state_city = $this->model_payment_weaccept_cash->findStateCity($this->request->post['weaccept_cash_city']);

			// Get token
			$token = $this->model_payment_weaccept_cash->get_auth_token($this->config->get('weaccept_online_api_key'))->token;

			//Get integration id
			$integration_id = $this->config->get('weaccept_cash_integration_id');

			//Get merchant id
			$merchant = $this->config->get('weaccept_online_merchant_id');

			//Get order and customer info
			$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
			$customer = $this->model_account_customer->getCustomerByEmail($order['email']);

			//Check if store has EGP enabled
			if($this->currency->has('EGP') && $order['currency'] !== 'EGP'){
				//convert amount
				$total_egp = $this->currency->convert($order['total'],$order['currenct'],'EGP');
			}else{
				echo '<p class="alert alert-danger">' . $this->language->get('error_currency_not_supported') . '</p>';
				die();
			}

			// create order registration request
			$shipping_address = [
				"apartment"			=> "NA",
				"email"				=> $order['email'],
				"floor"				=> "NA",
				"first_name"		=> empty($order['shipping_firstname']) ? 'NA' : $order['shipping_firstname'],
				"street"			=> $order['shipping_address_1'] . ' - ' . $order['shipping_address_2'],
				"building"			=> "NA",
				"phone_number"		=> empty($order['telephone']) ? 'NA' : $order['telephone'],
				"shipping_method"	=> "UNK",
				"postal_code"		=> empty($order['shipping_postcode']) ? 'NA' : $order['shipping_postcode'],
				"city"				=> empty($selected_state_city['city']) ? 'Cairo' : $selected_state_city['city'],
				"country"			=> empty($order['shipping_country']) ? 'NA' : $order['shipping_country'],
				"last_name"			=> empty($order['shipping_lastname']) ? 'NA' : $order['shipping_lastname'],
				"state"				=> empty($selected_state_city['state']) ? 'Cairo' : $selected_state_city['state'],
			];

			//Check if order exists in DB
			$weaccept_order = $this->model_payment_weaccept_cash->get_weaccept_order($order['order_id']);

			if (!$weaccept_order) {
				$merchant_order_id = $order['order_id'] . '.' . time();

				$order_registration_request = $this->model_payment_weaccept_cash->get_order_registeration_request($token, $merchant, $merchant_order_id, (int) $total_egp * 100, 'EGP', $shipping_address);


				if (!$order_registration_request) {
					die('Unable to register sthe order to Accept servers.<br>' . $this->model_payment_weaccept_cash->getLastError());
					exit();
				}

				$weaccept_order = json_decode($order_registration_request, true);
				$customerId = empty($customer['customer_id']) ? 0 : $customer['customer_id'];


				//insert into DB
				$this->model_payment_weaccept_cash->insertWeAcceptOrder($customerId, $order, $weaccept_order, $merchant_order_id, $order_registration_request);

				$weaccept_order = $this->model_payment_weaccept_cash->get_weaccept_order($order['order_id']);
			}

			//Step 3: generate the payment key
			$billing_address = [
				"apartment"			=> "NA",
				"email"				=> empty($order['email']) ? 'NA' : $order['email'],
				"floor"				=> "NA",
				"first_name"		=> empty($order['payment_firstname']) ? 'NA' : $order['payment_firstname'],
				"street"			=> (empty($order['payment_address_1']) ? 'NA' : $order['payment_address_1']) . ' - ' . empty($order['payment_address_2']) ? 'NA' : $order['payment_address_2'],
				"building"			=> "NA",
				"phone_number"		=> empty($order['telephone']) ? 'NA' : $order['telephone'],
				"shipping_method"	=> "PKG",
				"postal_code"		=> empty($order['payment_postcode']) ? 'NA' : $order['payment_postcode'],
				"city"				=> empty($selected_state_city['city']) ? 'Cairo' : $selected_state_city['city'],
				"country"			=> empty($order['payment_country']) ? 'NA' : $order['payment_country'],
				"last_name"			=> empty($order['payment_lastname']) ? 'NA' : $order['payment_lastname'],
				"state"				=> empty($selected_state_city['state']) ? 'Cairo' : $selected_state_city['state'],
			];


			$payment_key = $this->model_payment_weaccept_cash->get_payment_key_request($token, (int) $total_egp * 100, $weaccept_order[0]['weaccept_order_id'], 'EGP', $integration_id, $billing_address);

			if (!$payment_key) {
				echo '<p class="alert alert-danger">' . $this->language->get('error_unable_to_register_payment_key') . '</p>';
				die($this->model_payment_weaccept_cash->getLastError());
			}

			//Step 4: Start the pay request
			$payment_request = $this->model_payment_weaccept_cash->start_pay_request($payment_key->token);

			//if the city is selected
			if ($this->request->post['weaccept_cash_city']) {
				//confirm the order & redrect
				$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('weaccept_cash_completed_order_status_id'));
				echo '<script>window.location="' . $this->url->link('checkout/success') . '"</script>';
			}
		}
	}

	public function transaction_processed_callback()
	{
		$this->callback();
	}

	public function transaction_response_callback()
	{
		$this->callback();
	}


	/**
	 * Callback function
	 */
	private function callback()
	{
		// inc models
		$this->load->model('checkout/order');
		$this->load->model('payment/weaccept_cash');

		$hmac     = $this->config->get('weaccept_online_hmac_secret');

		$json_raw = file_get_contents('php://input');

		$post = false;

		if ($json_raw) {
			$post = true;
			$json = json_decode($json_raw, true);
			$data = $json['obj'];
			$type = $json['type'];
			if ($json['type'] === 'TRANSACTION') {
				$data['order'] = $data['order']['id'];
				$data['is_3d_secure'] = ($data['is_3d_secure'] === true) ? 'true' : 'false';
				$data['is_auth'] = ($data['is_auth'] === true) ? 'true' : 'false';
				$data['is_capture'] = ($data['is_capture'] === true) ? 'true' : 'false';
				$data['is_refunded'] = ($data['is_refunded'] === true) ? 'true' : 'false';
				$data['is_standalone_payment'] = ($data['is_standalone_payment'] === true) ? 'true' : 'false';
				$data['is_voided'] = ($data['is_voided'] === true) ? 'true' : 'false';
				$data['success'] = ($data['success'] === true) ? 'true' : 'false';
				$data['error_occured'] = ($data['error_occured'] === true) ? 'true' : 'false';
				$data['has_parent_transaction'] = ($data['has_parent_transaction'] === true) ? 'true' : 'false';
				$data['pending'] = ($data['pending'] === true) ? 'true' : 'false';
				$data['source_data_pan'] = $data['source_data']['pan'];
				$data['source_data_type'] = $data['source_data']['type'];
				$data['source_data_sub_type'] = $data['source_data']['sub_type'];
			} elseif ($json['type'] === 'DELIVERY_STATUS') {
				$data['order'] = $data['order_id'];
			}
		} else {
			$data = $_GET;
			$type = 'TRANSACTION';
		}

		$hash = $this->model_payment_weaccept_cash->calculateHash($hmac, $data, $type);

		// auth?
		if ($hash === $_REQUEST['hmac']) {
			if ($post) {
				//get order id
				$order_id = substr($json['obj']['merchant_order_id'], 0, -10);

				if ($type == 'TRANSACTION') {
					if ($data['success'] == "true") {
						// 5 = completed
						$this->model_checkout_order->confirm($order_id, $this->config->get('weaccept_cash_completed_order_status_id'), 'Payment Sucessfull.');
					} else {
						// 10 = fail
						$this->model_checkout_order->confirm($order_id, $this->config->get('weaccept_cash_failed_order_status_id'), 'Payment Declined.');
					}
				} elseif ($type === 'DELIVERY_STATUS') {
					$this->model_checkout_order->confirm($order_id, $this->config->get('weaccept_cash_completed_order_status_id'), $json['obj']['order_delivery_status']);
				}
			} else {
				if ($data['success'] == 'true') {
					$this->response->redirect($this->url->link('checkout/success'));
				} else {
					$this->response->redirect($this->url->link('checkout/failure'));
				}
			}
		} else {
			die("This Server is not ready to handle your request right now.");
		}

		// leave.
		exit();
	}
}
