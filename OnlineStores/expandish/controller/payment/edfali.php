<?php

/**
 * Edfali payment handler for the store front
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ControllerPaymentEdfali extends Controller
{
	private $errors = [];

	private $response_errors = ['PW1', 'PW', 'ACC', 'Limit', 'Bal'];

	/**
	 * Show the default payment view
	 *
	 * @return void
	 */
	protected function index()
	{
		$this->initializer([
			'edfali' => 'payment/edfali',
			'checkout/order'
		],[
            'payment/edfali'
        ]);

		$orderInfo = $this->order->getOrder($this->session->data['order_id']);

		if (!$orderInfo) {
			return false;
		}

		//Send pin code to customer
		$this->sendPinCode($orderInfo['payment_telephone'], (float) ($orderInfo['total'] * $orderInfo['currency_value']));

		//Send errors to view if any
		$this->data['errors'] = $this->errors;

		// if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/default/template/payment/edfali.expand')) {
		// 	$this->template = 'customtemplates/' . STORECODE . '/default/template/payment/edfali.expand';
		// } else {
		// 	$this->template =  'default/template/payment/edfali.expand';
		// }
        $this->template =  'default/template/payment/edfali.expand';

		$this->render_ecwig();
	}

	/**
	 * Send the pin code to the customer using soap
	 *
	 * @param int $customer_mobile
	 * @param float $amount
	 * @return void
	 */
	private function sendPinCode($customer_mobile = null, $amount = 0)
	{
		//Set the required fields 
		$fields = [
			'Mobile' => $this->config->get('edfali_merchant_mobile'),
			'Pin' => $this->config->get('edfali_merchant_pin'),
			'Cmobile' => $customer_mobile,
			'Amount' => (float) $amount,
			'PW' => "123@xdsr$#!!",
		];

		//Create a new SOAP Client
		$client = new SoapClient('http://62.240.55.2:6187/BCDUssd/Edfali.asmx?WSDL', array("trace" => 1, "exception" => 0));

		//Get the response and encode it as array
		$response = $client->DoPTrans($fields);
		$response_arr = json_decode(json_encode($response));

		//Check if the returned response is an error key
		if (in_array($response_arr->DoPTransResult, $this->response_errors)) {
			$this->language->load_json('payment/edfali');
			array_push($this->errors, [
				'code' => $response_arr->DoPTransResult,
				'message' => $this->language->get('response_error_' . $response_arr->DoPTransResult)
			]);
		}

		//Success: store edfali session id in user session
		if (count($this->errors) < 1) {
			$this->session->data['edfali_session_id'] = $response_arr->DoPTransResult;
		}
	}

	/**
	 * Validate the pin code sent by the user
	 * 
	 */
	public function validate_pin_code()
	{
		//Set return var
		$return = [];

		// Validate the pin code syntax
		if (!$this->request->post['customer_pin_code'] || strlen($this->request->post['customer_pin_code']) != 4) {
			$return = [
				'code' => 'error',
				'message' => 'Pin code has to be 4 digits'
			];

			echo json_encode($return);
			die;
		}

		//Get order info
		$this->load->model('checkout/order');
		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		//Set the required fields 
		$fields = [
			'Mobile' => $this->config->get('edfali_merchant_mobile'),
			'Pin' => $this->request->post['customer_pin_code'],
			'sessionID' => $this->session->data['edfali_session_id'],
			'PW' => "123@xdsr$#!!",
		];

		//Create a new SOAP Client
		$client = new SoapClient('http://62.240.55.2:6187/BCDUssd/Edfali.asmx?WSDL', array("trace" => 1, "exception" => 0));

		//Get the response and encode it as array
		$response = $client->OnlineConfTrans();
		$response_arr = json_decode(json_encode($response));

		//If success
		if ($response_arr->OnlineConfTransResult == 'OK') {
			
			//Confirm Order and return redirect data
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('edfali_completed_order_status_id'));

			//Return error
			$return = [
				'code' => 'success',
				'message' => $this->url->link('checkout/success')
			];

			echo json_encode($return);
			die;
		} else {
			//Return error
			$return = [
				'code' => 'error',
				'message' => 'The pin code you entered is wrong, please contact support.'
			];

			echo json_encode($return);
			die;
		}
	}
}
