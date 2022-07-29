<?php
/**
 * Store front for WeAccept Online
 * @author Mohamed Hassan
 */
class ControllerPaymentWeAcceptOnline extends Controller {
	
	/**
	 * Main handle function
	 */
	public function index() {
			// if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/weaccept_online.expand')) {
            //     $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/weaccept_online.expand';
            // }else {
            //     $this->template = 'default/template/payment/weaccept_online.expand';
			// }
            $this->template = 'default/template/payment/weaccept_online.expand';
			$this->render_ecwig();
			// $this->response->setOutput();
	}

    /**
     * Confirm Payment
     */
	public function confirm_payment()
    {
        
        // inc models
            $this->load->model('account/customer');
            $this->load->model('checkout/order');
            $this->load->model('payment/weaccept_online');
            $this->language->load_json('payment/weaccept_online');

            // collect data 
            $token = $this->model_payment_weaccept_online->get_auth_token($this->config->get('weaccept_online_api_key'))->token;
            $integration_id = $this->config->get('weaccept_online_integration_id');
            $iframe_id = $this->config->get('weaccept_online_iframe_id');
            $merchant = $this->config->get('weaccept_online_merchant_id');
            // $iframeCss= $this->config->get('weaccept_online_iframe_css');
            // $this->data['iframe_css'] = $iframeCss;

            $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $customer = $this->model_account_customer->getCustomerByEmail($order['email']);

            $currency = $order['currency_code'];
            $allowed = ['EGP'];

        //Check if store has EGP enabled
        if($this->currency->has('EGP') && $order['currency'] !== 'EGP'){
            //convert amount
            $order['total'] = $this->currency->convert($order['total'],$currency,'EGP');
            $currency = "EGP";
        }else{
            $this->data['error_payment'] = $this->language->get('error_currency_not_supported');
            $this->response->setOutput(json_encode($this->data));
            return;
        }

            // create order registration request
            $shipping_address = [
                "apartment" => "NA",
                "email" => empty($order['email']) ? 'NA' : $order['email'],
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
            $weaccept_order = $this->model_payment_weaccept_online->get_weaccept_order($order['order_id']);

            if (!$weaccept_order) {
                $merchant_order_id = $order['order_id'] . '.' . time();

                $order_registration_request = $this->model_payment_weaccept_online->
                get_order_registeration_request($token, $merchant, $merchant_order_id, (int)$order['total'] * 100, $currency, $shipping_address);


                if (!$order_registration_request) {
                    die('Unable to register the order to Accept servers.<br>' . $this->model_payment_weaccept_online->getLastError());
                }

                $weaccept_order = json_decode($order_registration_request, true);
                $customerId = empty($customer['customer_id']) ? 0 : $customer['customer_id'];


                //insert into DB
                $this->model_payment_weaccept_online->insertWeAcceptOrder($customerId, $order, $weaccept_order, $merchant_order_id, $order_registration_request);

                $weaccept_order = $this->model_payment_weaccept_online->get_weaccept_order($order['order_id']);

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


            $payment_request = $this->model_payment_weaccept_online->get_payment_key_request($token, (int)$order['total'] * 100, $weaccept_order[0]['weaccept_order_id'], $currency, $integration_id, $billing_address);

            if ($payment_request) {
                $this->data['url'] = 'https://accept.paymobsolutions.com/api/acceptance/iframes/' . $iframe_id . '?payment_token=' . $payment_request->token;
            } else {
                $this->data['error_payment'] = $this->model_payment_weaccept_online->getLastError();
            }

            $this->response->setOutput(json_encode($this->data));
        }

    public function transaction_processed_callback(){
		echo json_encode($this->request->get);
		die();
	}

	public function transaction_response_callback(){
        $payment_status = filter_var($this->request->get['success'], FILTER_VALIDATE_BOOLEAN);
		$this->load->model('checkout/order');
		$this->language->load_json('payment/accept_cards');
        $this->load->model('payment/weaccept_online');
        if(!$this->session->data['order_id'] && $this->request->get['order']){
            $weaccept_order = $this->model_payment_weaccept_online->get_weaccept_order_by_id($this->request->get['order']);
            if(isset($weaccept_order['expand_order_id']))
                $this->session->data['order_id'] = $weaccept_order['expand_order_id'];
        }

		if($payment_status && $this->response->get['error_txn_response_code'] == 0){
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('weaccept_online_completed_order_status_id'));
			$this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
		}else{
			$this->session->data['error_payment_response'] = array(
                'resp_code'    => $this->response->get['txn_response_code'],
                'resp_msg'     => $this->language->get($this->response->get['error_txn_response_code_'.$this->response->get['txn_response_code']]),
                'continue'     => $this->url->link('checkout/cart'),
            );
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('weaccept_online_failed_order_status_id'));
            $this->response->redirect($this->url->link('checkout/error/show', '', 'SSL'));
		}
	}


/**
 * Old callback function
 */
// public function callback(){
// 		// inc models
// 		$this->load->model('account/customer');
// 		$this->load->model('checkout/order');
// 		$this->load->model('payment/accept_cards');
// 		$hmac     = $this->config->get('accept_cards_hmac_secret');
// 		$json_raw = file_get_contents('php://input');
// 		$post = false;
// 		if ($json_raw) {
// 			$post = true;
// 			$json = json_decode($json_raw, true);
// 			$this->data = $json['obj'];
// 			$type = $json['type'];
// 			if ($json['type'] === 'TRANSACTION') {
// 				$this->data['order'] = $this->data['order']['id'];
// 				$this->data['is_3d_secure'] = ($this->data['is_3d_secure'] === true)?'true':'false';
// 				$this->data['is_auth'] = ($this->data['is_auth'] === true)?'true':'false';
// 				$this->data['is_capture'] = ($this->data['is_capture'] === true)?'true':'false';
// 				$this->data['is_refunded'] = ($this->data['is_refunded'] === true)?'true':'false';
// 				$this->data['is_standalone_payment'] = ($this->data['is_standalone_payment'] === true)?'true':'false';
// 				$this->data['is_voided'] = ($this->data['is_voided'] === true)?'true':'false';
// 				$this->data['success'] = ($this->data['success'] === true)?'true':'false';
// 				$this->data['error_occured'] = ($this->data['error_occured'] === true)?'true':'false';
// 				$this->data['has_parent_transaction'] = ($this->data['has_parent_transaction'] === true)?'true':'false';
// 				$this->data['pending'] = ($this->data['pending'] === true)?'true':'false';
// 				$this->data['source_data_pan'] = $this->data['source_data']['pan'];
// 				$this->data['source_data_type'] = $this->data['source_data']['type'];
// 				$this->data['source_data_sub_type'] = $this->data['source_data']['sub_type'];
// 			} elseif ($json['type'] === 'DELIVERY_STATUS') {
// 				$this->data['order'] = $this->data['order']['id'];
// 			}
// 		} else {
// 			$this->data = $_GET;
// 			$type = 'TRANSACTION';
// 		}
// 		$hash = $this->model_payment_accept_cards->calculateHash($hmac, $this->data, $type);
// 		// auth?
// 		if($hash === $_REQUEST['hmac']){
// 			if($post)
// 			{
// 				// is post
// 				if ($type == 'TRANSACTION')
// 				{
// 					$order_id = substr($json['obj']['order']['merchant_order_id'],0, -10);
// 				}
// 				else if ($type == 'TOKEN')
// 				{
// 					$customer = $this->model_account_customer->getCustomerByEmail($this->data['email']);
// 					if($customer && false)
// 					{
// 						$cards       = $this->db->query("SELECT * FROM `" . DB_PREFIX . "accept_cards_tokens` WHERE `card_subtype` = '".$this->data['card_subtype']."' AND `masked_pan` = '".$this->data['masked_pan']."' AND `customer_id` = '" . (int)$customer['customer_id'] . "'")->rows;
// 						// customer has cards stored?
// 						if(!$cards){
// 							$this->db->query("INSERT INTO `" . DB_PREFIX . "accept_cards_tokens` SET `customer_id` = '" . (int)$customer['customer_id'] . "', `token` = '" . $this->data['token'] . "', `masked_pan` = '" . $this->data['masked_pan'] . "', `card_subtype` = '" . $this->data['card_subtype'] . "'");
// 						}
// 					}
// 				}
// 			}
// 			else
// 			{
// 				if($this->data['success'] == 'true')
// 				{
// 					$this->load->model('checkout/order');
// 					$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('accept_cards_order_status_id'));
// 					$this->response->redirect($this->url->link('checkout/success'));
// 				} else {
// 					$this->response->redirect($this->url->link('checkout/failure'));
// 				}
// 			}
// 		} else {
// 			die("This Server is not ready to handle your request right now.");
// 		}
// 		// leave.
// 		exit();
// 	}
} 
