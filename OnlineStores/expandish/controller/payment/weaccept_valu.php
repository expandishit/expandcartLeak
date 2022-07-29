<?php

/**
 * Store front for WeAccept Valu
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ControllerPaymentWeAcceptValu extends Controller
{

	/**
	 * Main handle function
	 */
	public function index()
	{
			// if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/weaccept_valu.expand')) {
			// 	$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/weaccept_valu.expand';
			// } else {
			// 	$this->template = 'default/template/payment/weaccept_valu.expand';
			// }
            $this->template = 'default/template/payment/weaccept_valu.expand';
			$this->render_ecwig();
			// $this->response->setOutput();
		
	}

    public function confirm_payment()
    {
            // inc models
            $this->load->model('account/customer');
            $this->load->model('checkout/order');
            $this->load->model('payment/weaccept_valu');
            $this->language->load_json('payment/weaccept_valu');

            //Step 1: Get a token 
            $token = $this->model_payment_weaccept_valu->get_auth_token($this->config->get('weaccept_online_api_key'))->token;

            //Get integration id
            $integration_id = $this->config->get('weaccept_valu_integration_id');

            //Get merchant id
            $merchant = $this->config->get('weaccept_online_merchant_id');

            //Get valu iframe id
            $iframe_id = $this->config->get('weaccept_valu_iframe_id');

            //Get order info
            $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            //Get customer info
            $customer = $this->model_account_customer->getCustomerByEmail($order['email']);

            //Check if store has EGP enabled
            if ($this->currency->has('EGP') && $order['currency'] !== 'EGP') {
                //convert amount
                $total_egp = $this->currency->convert($order['total'], $order['currency_code'], 'EGP');
            } else {
                echo '<p class="alert alert-danger">' . $this->language->get('error_currency_not_supported') . '</p>';
                die();
            }

            // create order registration request
            $shipping_address = [
                "apartment" => "NA",
                "email" => $order['email'],
                "floor" => "NA",
                "first_name" => empty($order['shipping_firstname']) ? 'NA' : $order['shipping_firstname'],
                "street" => $order['shipping_address_1'] . ' - ' . $order['shipping_address_2'],
                "building" => "NA",
                "phone_number" => empty($order['telephone']) ? 'NA' : $order['telephone'],
                "shipping_method" => "UNK",
                "postal_code" => empty($order['shipping_postcode']) ? 'NA' : $order['shipping_postcode'],
                "city" => empty($order['shipping_city']) ? 'NA' : $order['shipping_city'],
                "country" => empty($order['shipping_country']) ? 'NA' : $order['shipping_country'],
                "last_name" => empty($order['shipping_lastname']) ? 'NA' : $order['shipping_lastname'],
                "state" => empty($order['shipping_zone']) ? 'NA' : $order['shipping_zone'],
            ];

            //Check if order exists in DB
            $weaccept_order = $this->model_payment_weaccept_valu->get_weaccept_order($order['order_id']);

            if (!$weaccept_order) {
                $merchant_order_id = $order['order_id'] . '.' . time();

                $order_registration_request = $this->model_payment_weaccept_valu->get_order_registeration_request($token, $merchant, $merchant_order_id, (int)$total_egp * 100, 'EGP', $shipping_address);

                if (!$order_registration_request) {
                    die('Unable to register the order to Accept servers.<br>' . $this->model_payment_weaccept_valu->getLastError());
                    exit();
                }

                $weaccept_order = json_decode($order_registration_request, true);
                $customerId = empty($customer['customer_id']) ? 0 : $customer['customer_id'];


                //insert into DB
                $this->model_payment_weaccept_valu->insertWeAcceptOrder($customerId, $order, $weaccept_order, $merchant_order_id, $order_registration_request);

                $weaccept_order = $this->model_payment_weaccept_valu->get_weaccept_order($order['order_id']);
            }

            //Step 3: generate the payment key
            $billing_address = [
                "apartment" => "NA",
                "email" => empty($order['email']) ? 'NA' : $order['email'],
                "floor" => "NA",
                "first_name" => empty($order['payment_firstname']) ? 'NA' : $order['payment_firstname'],
                "street" => (empty($order['payment_address_1']) ? 'NA' : $order['payment_address_1']) . ' - ' . empty($order['payment_address_2']) ? 'NA' : $order['payment_address_2'],
                "building" => "NA",
                "phone_number" => empty($order['telephone']) ? 'NA' : $order['telephone'],
                "shipping_method" => "PKG",
                "postal_code" => empty($order['payment_postcode']) ? 'NA' : $order['payment_postcode'],
                "city" => empty($order['payment_city']) ? 'NA' : $order['payment_city'],
                "country" => empty($order['payment_country']) ? 'NA' : $order['payment_country'],
                "last_name" => empty($order['payment_lastname']) ? 'NA' : $order['payment_lastname'],
                "state" => empty($order['payment_zone']) ? 'NA' : $order['payment_zone']
            ];

            //Get payment request token
            $payment_request = $this->model_payment_weaccept_valu->get_payment_key_request($token, (int)$total_egp * 100, $weaccept_order[0]['weaccept_order_id'], 'EGP', $integration_id, $billing_address);

            if (!$payment_request) {
                $this->data['error_payment'] = $this->language->get('error_unable_to_register_payment_key');
                die($this->model_payment_weaccept_valu->getLastError());
            }

            //Set the iframe
            $this->data['url'] = 'https://accept.paymobsolutions.com/api/acceptance/iframes/' . $iframe_id . '?payment_token=' . $payment_request->token;
            $this->response->setOutput(json_encode($this->data));
    }
	/**
	 * Handle transaction processed callback
	 *
	 * @return void
	 */
	public function transaction_processed_callback()
	{
		$this->callback();
	}

	/**
	 * Handle transaction response callback
	 *
	 * @return void
	 */
	public function transaction_response_callback()
	{
		$this->callback();
	}

	/**
	 * Handle the webhook that will be invoked from accept servers
	 *
	 * @return void
	 */
	private function callback()
	{
		// inc models
		$this->load->model('checkout/order');
		$this->load->model('payment/weaccept_valu');

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
				$data['order'] = $data['order']['id'];
			}
		} else {
			$data = $_GET;
			$type = 'TRANSACTION';
		}

		$hash = $this->model_payment_weaccept_valu->calculateHash($hmac, $data, $type);

		// auth?
		if ($hash === $_REQUEST['hmac']) {
			if ($post) {
				// is post
				if ($type == 'TRANSACTION') {
					$order_id = substr($json['obj']['order']['merchant_order_id'], 0, -10);
					if ($data['success'] == "true") {
						// 5 = completed
						$this->model_checkout_order->confirm($order_id,$this->config->get('weaccept_valu_completed_order_status_id'), 'Payment Sucessfull.');
					} else {
						// 10 = fail
						$this->model_checkout_order->confirm($order_id, $this->config->get('weaccept_valu_failed_order_status_id'), 'Payment Declined.');
					}
				}
			} else {
				if ($data['success'] == 'true') {
					$this->response->redirect($this->url->link('checkout/success'));
				} else {
                    $this->language->load_json('payment/weaccept_valu');
                    $this->session->data['error_payment_response'] = array(
                        'resp_code'    => $data['txn_response_code'],
                        'resp_msg'     => $this->language->get('error_txn_response_code_'.$data['txn_response_code']),
                        'continue'     => $this->url->link('checkout/cart'),
                    );
					$this->response->redirect($this->url->link('checkout/error/show'));
				}
			}
		} else {
			die("This Server is not ready to handle your request right now.");
		}

		// leave.
		exit();
	}
}
