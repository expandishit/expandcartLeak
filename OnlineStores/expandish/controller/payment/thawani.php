<?php

/**
 * Thawani payment handler for the store front
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart 
 */

class ControllerPaymentThawani extends Controller
{
	/**
	 * Show the default payment view
	 *
	 * @return void
	 */
	public function index()
	{
        if (isset($this->request->post['email']) && !empty($this->request->post['email'])) {
            $identity = $this->request->post['email'];
        } elseif (isset($this->request->post['telephone']) && !empty($this->request->post['telephone'])) {
            $identity = $this->request->post['telephone'];
        } elseif (isset($this->session->data['payment_address']['email']) && !empty($this->session->data['payment_address']['email'])) {
            $identity = $this->session->data['payment_address']['email'];
        } elseif (isset($this->session->data['payment_address']['telephone']) && !empty($this->session->data['payment_address']['telephone'])) {
            $identity = $this->session->data['payment_address']['telephone'];
        } 
                
		if (isset($identity)) {
			$this->initializer([
				'thawani' => 'payment/thawani',
				'checkout/order'
			]);

			$orderInfo = $this->order->getOrder($this->session->data['order_id']);
			if (!$orderInfo) {
				return false;
			}	
			// 1. add customer 
			$customerData = array("client_customer_id" => $identity );
			$customerInfo = $this->model_payment_thawani->addCustomer($customerData);
			$customerId = $customerInfo->data->id;	
			// 2. create session
			$price = $orderInfo['total'] * 1000;
			$products = array(array("name"=>"Order_No_".$orderInfo['order_id'],"unit_amount"=> (int)$price,"quantity"=> 1));

			$sessionData = array(
							"client_reference_id" => $customerId ,
							"customer_id" =>"",
							"products" => $products,
							"success_url" =>$this->url->link('payment/thawani/callback'),
							"cancel_url"  =>$this->url->link('payment/thawani/callback'),
							"metadata" => array(
								"order_id"=>$orderInfo['order_id'],
								"customer_name"=>$orderInfo['firstname']." ".$orderInfo['lastname'] ,
								"customer_email"=>$orderInfo['email'] ,
								"customer_phone"=>$orderInfo['telephone'] ,
							),
							);

			$sessionInfo = $this->model_payment_thawani->createSession($sessionData);
			// success create code is 2004
            $thawani_error = '';
			if ($sessionInfo->code == 2004) {
				$this->data['status'] = 'success';
				// Set the request URL base on the environment
				if($this->config->get('thawani_test_mode') == '1'){
					$paymentUrl = "http://uatcheckout.thawani.om/pay/";
				}else{
					$paymentUrl = "http://checkout.thawani.om/pay/";
				}
				$this->data['url'] = $paymentUrl.$sessionInfo->data->session_id."?key=".$this->config->get('thawani_public_key');
			} else {
				$this->data['status'] = 'error';
                $thawani_error = $sessionInfo->description ?: $sessionInfo->detail;
			}

		}
        $thawani_error .= $this->session->data['thawani_error'] ? $this->session->data['thawani_error'] : '';
        $this->session->data['thawani_error'] = '';
        $this->data['thawani_error'] = $thawani_error;

        // Set response data into session to use it in callback
        $this->session->data['thawani'] = $sessionInfo;

        // if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/default/template/payment/thawani.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/default/template/payment/thawani.expand';
        // } else {
        //     $this->template =  'default/template/payment/thawani.expand';
        // }
        
        $this->template =  'default/template/payment/thawani.expand';

        $this->render_ecwig();
	}

	/**
	 * Handle the call back to confirm the order and redirect
	 *
	 * @return void
	*/
	public function callback()
	{

        // request get payment status
        $data = isset($this->session->data['thawani']) ? $this->session->data['thawani'] : '';

        if ($data) {
			$this->initializer([
				'thawani' => 'payment/thawani',
				'checkout/order'
			]);
			// get session details to check payment status
            $result = $this->model_payment_thawani->getSession($data->data->session_id);
            if ($result->data->payment_status == 'paid') {
                $this->load->model('checkout/order');
                $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('thawani_completed_order_status_id'));
                $this->response->redirect($this->url->link('checkout/success'));
            } else {
                $this->session->data['thawani_error'] = $result->description;
                $this->response->redirect($this->url->link('checkout/checkout'));
            }

            // in case of cURL error, set the error into array
            if($err){
                $this->session->data['thawani_error'] = $err;
                $this->response->redirect($this->url->link('checkout/checkout'));
            }
        }else {
            $this->session->data['thawani_error'] = 'Paymant failed!';
            $this->response->redirect($this->url->link('checkout/checkout'));
        }
	}
}
