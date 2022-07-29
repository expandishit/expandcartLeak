<?php

/**
 * paylink payment handler for the store front
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart 
 */

class ControllerPaymentPaylink extends Controller
{
	/**
	 * Show the default payment view
	 *
	 * @return void
	 */
	protected function index()
	{
        $this->initializer([
            'paylink' => 'payment/paylink',
            'checkout/order'
        ]);

        $orderInfo = $this->order->getOrder($this->session->data['order_id']);
        if (!$orderInfo) {
            return false;
        }
        $paylink_error = '';
        // 1. login
        $authData = $this->model_payment_paylink->login();
        if (!isset($authData->id_token)) {
            $this->data['status'] = 'error';
            $paylink_error = $authData->error . " " . $authData->message . '<br/>';
        } else {
            // 2. add invoice
            $price = $orderInfo['total'];
            $products = array(array("description" => "Order_No_" . $orderInfo['order_id'], "imageSrc" => "", "price" => $price, "qty" => 1, "title" => $orderInfo['order_id']));

            $invoiceData = array(
                "amount" => $price,
                "callBackUrl" => $this->url->link('payment/paylink/callback'),
                "clientEmail" => $orderInfo['email'],
                "clientMobile" => $orderInfo['telephone'],
                "clientName" => $orderInfo['firstname'] . " " . $orderInfo['lastname'],
                "note" => "",
                "orderNumber" => "Order_No_" . $orderInfo['order_id'],
                "products" => $products
            );
            $invoiceInfo = $this->model_payment_paylink->createInvoice($authData->id_token, $invoiceData);
            if ($invoiceInfo->success == 'true') {
                $this->data['status'] = 'success';
                // save token at session
                $this->session->data['paylink_token'] = $authData->id_token;
                $this->data['url'] = $invoiceInfo->url;
            } else {
                $this->data['status'] = 'error';
                $paylink_error = $invoiceInfo->error . " " . $invoiceInfo->message . '<br/>';
            }
        }

        $paylink_error .= $this->session->data['paylink_error'] ? $this->session->data['paylink_error'] : '';

        $this->session->data['paylink_error'] = '';
        $this->data['paylink_error'] = $paylink_error;
        // Set response data into session to use it in callback
        $this->session->data['paylink'] = $invoiceInfo;

        // if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/default/template/payment/paylink.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/default/template/payment/paylink.expand';
        // } else {
        //     $this->template =  'default/template/payment/paylink.expand';
        // }
        
        $this->template =  'default/template/payment/paylink.expand';

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
        $data = isset($this->session->data['paylink']) ? $this->session->data['paylink'] : '';
        $paylink_token = isset($this->session->data['paylink_token']) ? $this->session->data['paylink_token'] : '';
        if ($data) {
			$this->initializer([
				'paylink' => 'payment/paylink',
				'checkout/order'
			]);
			// check for invoice status endpoint
            $result = $this->model_payment_paylink->getInvoice($paylink_token,$data->transactionNo);
            if ($result->orderStatus == 'Paid') {
                $this->load->model('checkout/order');
                $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('paylink_completed_order_status_id'));
                $this->response->redirect($this->url->link('checkout/success'));
            } else {
                $this->session->data['paylink_error'] = $result->detail;
                $this->response->redirect($this->url->link('checkout/checkout'));
            }

            // in case of cURL error, set the error into array
            if($err){
                $this->session->data['paylink_error'] = $err;
                $this->response->redirect($this->url->link('checkout/checkout'));
            }
        }else {
            $this->session->data['paylink_error'] = 'Paymant failed!';
            $this->response->redirect($this->url->link('checkout/checkout'));
        }
	}
}
