<?php
require_once "sadad_bahrain_plugin/SaddedInvoice/Invoice.php";

use SaddedInvoice\Invoice;

class ControllerPaymentSadadBahrain extends Controller{
    
	/**
	* @const strings Gateway Name.
	*/
    const GATEWAY_NAME = 'sadad_bahrain';

    /**
    * @var array
    */
    private $allowed_currencies = ['BHD'];

	public function index(){
      $this->language->load_json('payment/' . self::GATEWAY_NAME);

      if (isset($this->session->data['error_sadad_bahrain'])) {
          $this->data['error_sadad_bahrain'] = $this->session->data['error_sadad_bahrain'];
      }

      $this->template = 'default/template/payment/' . self::GATEWAY_NAME . '.expand';
      $this->render_ecwig();
    }

    public function pay(){
    	$settings = $this->config->get('sadad_bahrain');
    	$invoice  = new Invoice('https://eps-net.sadadbh.com/', $settings['branch_id'], $settings['vendor_id'], $settings['terminal_id'], $settings['api_key']);
		$invoice->amount = $this->_getOrderAmount();
		$invoice->email  = $settings['email'];
		$invoice->msisdn = $settings['msisdn'];
		$invoice->date   = (new DateTime('NOW'))->format("Y-m-d H:i:s");
		$invoice->description  = 'ExpandCart Order #'.$this->session->data['order_id'];
		$invoice->customerName = $settings['customer_name'];
		$invoice->successUrl   = $this->url->link('payment/sadad_bahrain/success');
		$invoice->errorUrl     = $this->url->link('payment/sadad_bahrain/fail');
		$invoice->mode         = $settings['notification_mode'];
		// $invoice->externalReference = $_POST['external_reference'];

		$response = null;
		if (strtolower($invoice->mode) == "online") {
			$response = $invoice->CreateLinkRequest();
		} else if (strtolower($invoice->mode) == "sms") {
			$response = $invoice->CreateSmsRequest();
		} else if (strtolower($invoice->mode) == "email") {
			$response = $invoice->CreateEmailRequest();
		}

		$response = json_decode($response, true);

		if( !empty($response['payment-url']) ) { //Payment Success..
			$this->session->data['sadad_bahrain']['transaction'] = $response;
    	    $result_json['url']     = $response['payment-url'];
    	    $result_json['success'] = '1';
    	}		
       	else{ //Payment Faild..
       		$result_json['success'] = '0';
        	$result_json['message'] = 'SadadBahrain ERROR: Code #' . $response['error-code'] . ' - ' . $response['error-message'];//$this->language->get('text_sadad_bahrain_error');
       	}
        $this->response->setOutput(json_encode($result_json));
    }

    public function success(){
        unset($this->session->data['error_sadad_bahrain']);

		$this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $this->initializer([
                'paymentTransaction' => 'extension/payment_transaction',
                'paymentMethod'      => 'extension/payment_method',
        ]);

        $this->paymentTransaction->insert([
            'order_id'           => $this->session->data['order_id'],
            'transaction_id'     => $this->request->request['TransactionIdentifier'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('sadad_bahrain')['id'],
            'payment_method'     => 'Sadad Bahrain',
            'status'             => 'Success',
            'amount'             => $order_info['total'],
            'currency'           => $order_info['currency_code'],
            'details'            => json_encode($this->request->request)
        ]);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get(self::GATEWAY_NAME)['complete_status_id']);
        
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }
 
    public function fail(){ 
        $this->session->data['error_sadad_bahrain'] = 'SadadBahrain ERROR: Code #' . $this->request->request['ResultCode'] .' - '. $this->request->request['ResultMessage'];
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

	private function _getOrderAmount(){
		$this->load->model('checkout/order');
		$orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$orderInfo) {
			return false;
		}

		return $this->currency->convertUsingRatesAPI($orderInfo['total'], $orderInfo['currency_code'], 'BHD');		
	}

}
