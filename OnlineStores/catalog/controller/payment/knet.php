<?php
require_once(str_replace("system","knet",DIR_SYSTEM).'e24PaymentPipe.inc.php');
class ControllerPaymentKNET extends Controller {
	protected function index() {
		$this->load->language('payment/knet');
		
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
        $this->data['action'] = $this->url->link('payment/knet/makepayment', '', 'SSL');
        
        $this->data['amount'] = $order_info['total'];
        $this->data['order_id'] = $this->session->data['order_id'];
        $this->data['description'] = $this->config->get('config_name');

		$this->data['billing_fullname'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        
		if ($order_info['payment_address_2']) {
            $this->data['billing_address']  = $order_info['payment_address_1'] . "\r\n" . $order_info['payment_address_2'] . "\r\n" . $order_info['payment_city'] . "\r\n" . $order_info['payment_zone'] . "\r\n";
        } else {
            $this->data['billing_address']  = $order_info['payment_address_1'] . "\r\n" . $order_info['payment_city'] . "\r\n" . $order_info['payment_zone'] . "\r\n";
        }
		
        $this->data['billing_postcode'] = $order_info['payment_postcode'];

		if ($this->cart->hasShipping()) {
			$this->data['delivery_fullname'] = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
			
			if ($order_info['shipping_address_2']) {
				$this->data['delivery_address'] = $order_info['shipping_address_1'] . "\r\n" . $order_info['shipping_address_2'] . "\r\n" . $order_info['shipping_city'] . "\r\n" . $order_info['shipping_zone'] . "\r\n";
			} else {
				$this->data['delivery_address'] = $order_info['shipping_address_1'] . "\r\n" . $order_info['shipping_city'] . "\r\n" . $order_info['shipping_zone'] . "\r\n";
			}
		
        	$this->data['delivery_postcode'] = $order_info['shipping_postcode'];
		} else {
			$this->data['delivery_fullname'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
			
			if ($order_info['payment_address_2']) {
				$this->data['delivery_address'] = $order_info['payment_address_1'] . "\r\n" . $order_info['payment_address_2'] . "\r\n" . $order_info['payment_city'] . "\r\n" . $order_info['payment_zone'] . "\r\n";
			} else {
				$this->data['delivery_address'] = $order_info['shipping_address_1'] . "\r\n" . $order_info['payment_city'] . "\r\n" . $order_info['payment_zone'] . "\r\n";
			}
		
        	$this->data['delivery_postcode'] = $order_info['payment_postcode'];			
		}
		
        $this->data['email_address'] = $order_info['email'];
        $this->data['customer_phone_number']= $order_info['telephone'];
        $this->data['success_url'] = $this->url->link('checkout/success', '', 'SSL');
        $this->data['cancel_url'] = $this->url->link('checkout/payment', '', 'SSL');
        
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/knet.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/knet.tpl';
		} else {
			$this->template = 'default/template/payment/knet.tpl';
		}	
		
		$this->render();
	}
	
	public function makepayment()
	{
		
		$this->load->language('payment/knet');
		
		$this->load->model('checkout/order');

		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$trackID = date('YmdHis');
		
		$host = "https://" . $_SERVER['HTTP_HOST'];
		$successUrl = $host . '/success.php';
		$errorUrl = $host . '/error.php';
		$payment = new e24PaymentPipe;
		$payment->setErrorUrl($errorUrl);
		$payment->setResponseURL($successUrl);
		$payment->setLanguage($this->language->get('lang_code'));
		$payment->setCurrency($this->language->get('currency_code')); //USD
		$payment->setResourcePath(str_replace("system","knet",DIR_SYSTEM). STORECODE . '/');
		$payment->setAlias($this->config->get('knet_alias'));
		$payment->setAction("1"); // 1 = Purchase
		$payment->setAmt($order_info['total']);
		$payment->setTrackId($trackID);
		$payment->performPaymentInitialization();
        
		if (strlen($payment->getErrorMsg()) > 0) 
		{
			$this->session->data['error'] = $payment->getErrorMsg();
			//$this->redirect($this->url->link('checkout/cart'));
		}
		else
		{					
			$message = 'PaymentID: ' . $payment->paymentId . "\n";
			$message .= 'TrackID: ' . $trackID . "\n";
			$message .= 'Amount: ' . $order_info['total'] . "\n";
			$message .= 'Time: ' . date('d-m-Y H:i:s') . "\n";
			//Save details in DB before redirecting user to KNET. Else redirect to cart.
			if($order_info)
			{
				$this->load->model('payment/knet');
				$voidOrderStatus = 16;
				$this->model_payment_knet->change_order_status($order_info, $voidOrderStatus, $message);
			
				header('Location: ' . $payment_id = $payment->paymentPage . '?PaymentID=' . $payment->paymentId);
			}
			else
			{
				$this->redirect($this->url->link('checkout/cart'));
			}
		}
	}
	
	public function error()
	{
		//$this->session->data['error'] = $this->language->get('error_declined');
		//$this->redirect($this->url->link('checkout/cart'));
	}
	
	public function success() {
		$this->load->language('payment/knet');
		
		if (isset($this->session->data['order_id'])) {
			$order_id = $this->session->data['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('checkout/order');
				
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		if (!$order_info) {
			$this->session->data['error'] = $this->language->get('error_no_order');
			
			$this->redirect($this->url->link('checkout/cart'));
		}
		
		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();
			
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
		}	
		
		//Read URL params
		$paymentID = $_GET['PaymentID'];
		$presult = $_GET['Result'];
		$postdate = $_GET['PostDate'];
		$tranid = $_GET['TranID'];
		$auth = $_GET['Auth'];
		$ref = $_GET['Ref'];
		$trackid = $_GET['TrackID'];
		// $udf1 = $_GET['UDF1'];
		// $udf2 = $_GET['UDF2'];
		// $udf3 = $_GET['UDF3'];
		// $udf4 = $_GET['UDF4'];
		// $udf5 = $_GET['UDF5'];		
		
		$orderStatusID = 0;
		if ($presult == 'CAPTURED') {
			$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
			$paymentMessage = 'Your order has been successfully processed! Thanks for shopping with us.';
			$orderStatusID = $this->config->get('config_order_status_id');	//Set order_status_id = 'Pending'			
		} else {			
			//$this->load->model('payment/knet');
			$failedOrderStatus = 10;
			//$this->model_payment_knet->change_order_status($order_info, $failedOrderStatus, 'Order failed. Manually check the transaction.');
			$paymentMessage = 'Error processing payment. Order failed.';
			$this->model_checkout_order->confirm($order_id, $failedOrderStatus, $paymentMessage, true);
			$orderStatusID = $failedOrderStatus;	////Set order_status_id = 'Failed'
		}		
		
		$this->data['paymentID'] = $paymentID;
        $this->data['result']= $presult;
		$this->data['paymentDate'] = $postdate;
        $this->data['transID'] = $tranid;
        $this->data['trackID'] = $trackid;
		$this->data['refID'] = $ref;
		$this->data['paymentTime'] = date('H:i:s');
		$this->data['payment_message'] = $paymentMessage;
		
		$message = 'PaymentID: ' . $paymentID . "\n";
		$message .= 'Result: ' . $presult . "\n";
		$message .= 'PostDate: ' . $postdate . "\n";
		$message .= 'TranID: ' . $tranid . "\n";
		$message .= 'Auth: ' . $auth . "\n";
		$message .= 'Ref: ' . $ref . "\n";
		$message .= 'TrackID: ' . $trackid . "\n";
		$message .= 'Time: ' . date('H:i:s') . "\n";
		
		$this->model_checkout_order->update($order_id, $orderStatusID, $message, false);
        
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/knet_success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/knet_success.tpl';
		} else {
			$this->template = 'default/template/payment/knet_success.tpl';
		}	
		
		$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
		
		$this->response->setOutput($this->render());
	}
}
?>