<?php

/**
 * Store front for WeAccept Kiosk
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ControllerPaymentWeAcceptKiosk extends Controller
{

	/**
	 * Main handle function
	 */
	public function index()
	{
        $this->data['action'] = $this->url->link('payment/weaccept_kiosk/pay_action');
        $this->template = 'default/template/payment/weaccept_kiosk.expand';
        $this->render_ecwig();
	}

	public function pay_action(){
            // inc models
            $this->load->model('account/customer');
            $this->load->model('checkout/order');
            $this->load->model('payment/weaccept_kiosk');
            $this->language->load_json('payment/weaccept_kiosk');

            // collect data
            $token = $this->model_payment_weaccept_kiosk->get_auth_token($this->config->get('weaccept_online_api_key'))->token;

            $integration_id = $this->config->get('weaccept_kiosk_integration_id');

            $merchant = $this->config->get('weaccept_online_merchant_id');

            $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $customer = $this->model_account_customer->getCustomerByEmail($order['email']);

            //Check if store has EGP enabled
            if($order['currency_code'] == 'EGP') {
                $total_egp = $order['total'];
            }else if($this->currency->has('EGP') && $order['currency_code'] !== 'EGP'){
                //convert amount
                $total_egp = $this->currency->convert($order['total'],$order['currency_code'],'EGP');
            }else{
                $result_json['error_kiosk'] = $this->language->get('error_currency_not_supported');
                $result_json['success'] = '0';
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
                "city"				=> empty($order['shipping_city']) ? 'NA' : $order['shipping_city'],
                "country"			=> empty($order['shipping_country']) ? 'NA' : $order['shipping_country'],
                "last_name"			=> empty($order['shipping_lastname']) ? 'NA' : $order['shipping_lastname'],
                "state"				=> empty($order['shipping_zone']) ? 'NA' : $order['shipping_zone'],
            ];

            //Check if order exists in DB
            $weaccept_order = $this->model_payment_weaccept_kiosk->get_weaccept_order($order['order_id']);

            if (!$weaccept_order) {
                $merchant_order_id = $order['order_id'] . time();

                $order_registration_request = $this->model_payment_weaccept_kiosk->get_order_registeration_request($token, $merchant, $merchant_order_id, (int) $total_egp * 100, 'EGP', $shipping_address);

                if (!$order_registration_request) {
                    $result_json['error_kiosk'] = $this->model_payment_weaccept_kiosk->getLastError();
                    $result_json['success'] = '0';
                }

                $weaccept_order = json_decode($order_registration_request, true);
                $customerId = empty($customer['customer_id']) ? 0 : $customer['customer_id'];


                //insert into DB
                $this->model_payment_weaccept_kiosk->insertWeAcceptOrder($customerId, $order, $weaccept_order, $merchant_order_id, $order_registration_request);

                //$get we accept order
                $weaccept_order = $this->model_payment_weaccept_kiosk->get_weaccept_order($order['order_id']);
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
                "city"				=> empty($order['payment_city']) ? 'NA' : $order['payment_city'],
                "country"			=> empty($order['payment_country']) ? 'NA' : $order['payment_country'],
                "last_name"			=> empty($order['payment_lastname']) ? 'NA' : $order['payment_lastname'],
                "state"				=> empty($order['payment_zone']) ? 'NA' : $order['payment_zone']
            ];


            $payment_key = $this->model_payment_weaccept_kiosk->get_payment_key_request($token, (int) $total_egp* 100, $weaccept_order[0]['weaccept_order_id'], 'EGP', $integration_id, $billing_address);

            if (!$payment_key) {
                $result_json['error_kiosk'] = $this->language->get('error_unable_to_register_payment_key');
                $result_json['success'] = '0';

            }

            if($result_json['success'] != '0'){
                //Step 4: Start the pay request
                $payment_request = $this->model_payment_weaccept_kiosk->start_pay_request($payment_key->token);

                // echo $payment_request->data->bill_reference;
                //$this->data['bill_reference'] = $payment_request->data->bill_reference;
                //$this->data['redirect'] = $this->url->link('checkout/success');

                //Set session variable to catch in success
                /*
                 * The following lines form weaccept documentation: https://acceptdocs.paymobsolutions.com/docs/kiosk-payments
                 If the request was successful, you should have "pending": true and "success": false.
                This means that the transaction has been created but pending payment.
                You will need to display a message to the client with the "bill_reference" value from the "data" dictionary.
                The message should tell customers to ask for Madfouaat Mutanouea Accept مدفوعات متنوعة اكسبت and give their reference number.
                 */
                if($payment_request->pending && !$payment_request->success ){
                    $this->session->data['is_kiosk'] = true;
                    $this->session->data['kiosk_bill_reference'] = $payment_request->data->bill_reference;

                    $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('weaccept_kiosk_completed_order_status_id'), 'Payment Sucess.');

                    $this->initializer([
                        'paymentTransaction' => 'extension/payment_transaction',
                        'paymentMethod' => 'extension/payment_method',
                    ]);
                    $this->paymentTransaction->insert([
                        'order_id' => $this->session->data['order_id'],
                        'transaction_id' => $payment_request->data->bill_reference,
                        'payment_gateway_id' => $this->paymentMethod->selectByCode('weaccept_online')['id'],
                        'payment_method' => 'weaccept_online_kiosk',
                        'status' => 'Success',
                        'amount' => $total_egp,
                        'currency' => 'EGP',
                        'details' => json_encode($payment_request)
                    ]);

                    $result_json['url']        = $this->url->link('checkout/success');
                    $result_json['success'] = '1';
                }
            }

            return $this->response->setOutput(json_encode($result_json));
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
		// load models
		$this->load->model('checkout/order');
		$this->load->model('payment/weaccept_kiosk');

		//Get hmac
		$hmac     = $this->config->get('weaccept_online_hmac_secret');

		//get json from input stream
		$json_raw = file_get_contents('php://input');

		$post = false;

		//check if json_raw is empty or not
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

		$hash = $this->model_payment_weaccept_kiosk->calculateHash($hmac, $data, $type);

		// auth?
		if ($hash === $_REQUEST['hmac']) {
			if ($post) {
				// is post
				if ($type == 'TRANSACTION') {
					$order_id = substr($json['obj']['order']['merchant_order_id'], 0, -10);
					if ($data['success'] == "true") {
						// 5 = completed
						$this->model_checkout_order->confirm($order_id, $this->config->get('weaccept_kiosk_completed_order_status_id'), 'Payment Sucessfull.');
					} else {
						// 10 = fail
						$this->model_checkout_order->confirm($order_id, $this->config->get('weaccept_cash_failed_order_status_id'), 'Payment Declined.');
					}
				}
			} else {
				if ($data['success'] == 'true') {
					$this->response->redirect($this->url->link('checkout/success'));
                    //Confirm order and wait for payment
                    $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('weaccept_kiosk_pending_order_status_id'));
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
