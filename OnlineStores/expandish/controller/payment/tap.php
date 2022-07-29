<?php

use ExpandCart\Foundation\PaymentController;

class ControllerPaymentTap extends PaymentController
{
    protected static $isExternalPayment = true;
    
	public function index() {
		

		$this->data['button_confirm'] = $this->language->get('button_confirm');

 
		$this->isTestMode();

		$this->load->model('checkout/order');

		
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		

		$this->setPurchaseMeta();
		
		

		$this->data['returnurl'] = $this->url->link('payment/tap/callback', 'hashcd=' . md5($order_info['order_id'] . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) . $order_info['currency_code'] . $this->config->get('tap_password')));


		$this->setOrderDetails($order_info);

		$this->setLayouts();

		$this->render_ecwig();


		// return $this->load->view('extension/payment/tap', $this->data);
	}

	/**ATH  */
	protected function isTestMode(){


		// var_dump($this->config->get('tap_test'));
		// die();
	
		// if( ! $this->config->get('tap_test') )

		if ( ((int) $this->config->get('tap_test') == 0 ) ) {

			$this->data['action'] = 'https://www.gotapnow.com/webpay.aspx';

		} 
		else {

			$this->data['action'] = 'http://live.gotapnow.com/webpay.aspx';

		}


	}


	/**
	 * 
	 * 
	 */
	protected function setPurchaseMeta(){

		$this->data['APIKey'] = $this->config->get('tap_apikey');
	
		$this->data['MerchantID'] = $this->config->get('tap_merchantid');
	
		$this->data['Username'] = $this->config->get('tap_username');
	
		$this->data['Password'] = $this->config->get('tap_password');


	}

	protected function ifNotKWDConvert($order_info){
		/*if ( $order_info['currency_code'] !== 'KWD' )
		{
	
			return  $this->currency->convert( $order_info['total'] , $this->config->get('config_currency') , 'KWD' );
	
		} */

		return  $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
	}



	/**
	 * 
	 * prepare order items and details to purchase
	 * 
	 */

	protected function setOrderDetails($order_info){

		$this->data['itemprice1'] = $this->ifNotKWDConvert($order_info);

		$this->data['itemname1'] ='Order ID - '.$order_info['order_id'];
		
		$this->data['currencycode'] = $order_info['currency_code'];
		
		$this->data['ordid'] = $order_info['order_id'];

		$this->data['cstemail'] = $order_info['email'];
		
		$this->data['cstname'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
		
		$this->data['cstmobile'] = $order_info['telephone'];
		
		$this->data['cntry'] = $order_info['payment_iso_code_2'];

		


	}



	/**
	 * 
	 * set proper layout and template
	 *@author ATH  
	 */
	protected function setLayouts(){



		// if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/tap.expand')) {
		
		// 	$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/payment/tap.expand';
		
		// }
		
		// else {
		
		// 	$this->template = 'default' . '/template/payment/tap.expand';
            
		// }
        
        $this->template = 'default/template/payment/tap.expand';


	}

	public function callback() {



		$this->language->load_json('payment/tap');

		if (isset($this->request->get['trackid'])) {
			
			$order_id = $this->request->get['trackid'];
		
		} else {
			
			$order_id = 0;
		
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		$refid = '';

		if ($order_info) {
			
			$error = '';
			
			$key = $this->config->get('tap_merchantid');
			
			$refid = $this->request->get['ref'];
			
			$str = 'x_account_id'.$key.'x_ref'.$refid.'x_resultSUCCESSx_referenceid'.$order_id.'';
		
			$hashstring = hash_hmac('sha256', $str, '1tap7');
			
			$responsehashstring=$this->request->get['hash'];
				
			// if ($hashstring != $responsehashstring) {
			// 	$error = $this->language->get('text_unable');
			// } else if ($this->request->get['result'] != 'SUCCESS') {
			// 	$error = $this->language->get('text_declined');
			// }
		} else {
			$error = $this->language->get('text_unable');
		}

		$this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
			'paymentMethod' => 'extension/payment_method',
		]);
        $paymentTitle = $this->language->get('text_title');

        if (isset($tapPaymentTitle[$this->config->get('config_language_id')])) {
            if (!empty($tapPaymentTitle[$this->config->get('config_language_id')])) {
                $paymentTitle = $tapPaymentTitle[$this->config->get('config_language_id')];
            }
        }

		if ($error) {

			$this->paymentTransaction->insert([
                'order_id' => $order_id,
                'transaction_id' => $refid,
                'payment_gateway_id' => $this->paymentMethod->selectByCode('tap')['id'],
                'payment_method' => $paymentTitle,
                'status' => 'Failed',
                'amount' => '',
                'currency' => '',
                'details' => json_encode($this->request->get),
            ]);


			$this->handleFailurePayment($order_id, $error);


		} else {
			
			// $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('tap_order_status_id'));

            // get order status from config
            $status = $this->config->get('tap_complete_status_id') ? $this->config->get('tap_complete_status_id') :
                $this->config->get('config_order_status_id');

            // Here just to make sure that order has been recorded into DB
            $this->model_checkout_order->confirm($order_id, $status, 'Processing', true);

			$this->paymentTransaction->insert([
                'order_id' => $order_id,
                'transaction_id' => $refid,
                'payment_gateway_id' => $this->paymentMethod->selectByCode('tap')['id'],
                'payment_method' => $paymentTitle,
                'status' => 'Success',
                'amount' => '',
                'currency' => '',
                'details' => json_encode($this->request->get),
            ]);

			//$this->handleSuccessPayment($order_id);

            // Return to success page
            $this->response->redirect($this->url->link('checkout/success'));
		}
	}

	/*
	 * No need for this function, we handle the successful order in callback
	 *
	protected function handleSuccessPayment($order_id){

		$this->model_checkout_order->confirm($order_id, $this->config->get('tap_complete_status_id'), 'Processing', true);

		$this->response->redirect($this->url->link('checkout/success'));

	}
    */



	protected function handleFailurePayment($order_id, $error){


			$this->breadcrumbs();

			$this->justifyLang($error);

			$result_json['success'] = '0';

			$result_json['errors'] = $this->error;

			$this->response->setOutput(json_encode($result_json));

			$failed_order_status_id= $this->config->get('tap_denied_status_id');
		
			$this->model_checkout_order->confirm($order_id, $failed_order_status_id, 'Failed', true);


			$this->response->redirect($this->url->link('checkout/error'));	



		}



		protected function justifyLang($error){

			$this->data['heading_title'] = $this->language->get('text_failed');

			$this->data['text_message'] = sprintf($this->language->get('text_failed_message'), $error, $this->url->link('information/contact'));

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['continue'] = $this->url->link('common/home');



		}





		protected function breadcrumbs(){

				$this->data['breadcrumbs'] = array();

				$this->data['breadcrumbs'][] = array(
					
					'text' => $this->language->get('text_home'),
					
					'href' => $this->url->link('common/home')
				
				);

				$this->data['breadcrumbs'][] = array(
					
					'text' => $this->language->get('text_basket'),
					
					'href' => $this->url->link('checkout/cart')
				);

				$this->data['breadcrumbs'][] = array(
					
					'text' => $this->language->get('text_checkout'),
					
					'href' => $this->url->link('checkout/checkout', '', 'SSL')
				
				);

				$this->data['breadcrumbs'][] = array(
				
					'text' => $this->language->get('text_failed'),
				
					'href' => $this->url->link('checkout/success')
				
				);


		}

}
