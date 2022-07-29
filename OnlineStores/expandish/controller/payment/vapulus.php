<?php
class ControllerPaymentVapulus extends Controller{
    
	/**
	* @const strings Gateway Name.
	*/
    const GATEWAY_NAME = 'vapulus';

    /**
    * @var array
    */
    private $allowed_currencies = [];

    public function __construct($registry) {
		parent::__construct($registry);
		$this->allowed_currencies[] = $this->config->get('vapulus_account_currency_code');
	}

	public function index(){
      $this->language->load_json('payment/' . self::GATEWAY_NAME);

      if (isset($this->session->data['error_vapulus'])) {
          $this->data['error_vapulus'] = $this->session->data['error_vapulus'];
      }

	  $this->data['vapulus_id'] = $this->config->get('vapulus_id');
	  $this->data['onaccept']   = $this->url->link('payment/vapulus/success');
	  $this->data['onfail']     = $this->url->link('payment/vapulus/fail');
	  $this->data['amount']     = $this->_getOrderAmount();

      $this->template = 'default/template/payment/' . self::GATEWAY_NAME . '.expand';
      $this->render_ecwig();
    }

    function success(){
        unset($this->session->data['error_vapulus']);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get(self::GATEWAY_NAME . '_complete_status_id'));
        
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

 
    function fail(){ 
        $this->session->data['error_vapulus'] = ' VAPULUS ERROR: Payment ' . $this->request->get['status'] . ' - ' . $this->request->get['msg'] . '<br/> Transaction ID: ' . $this->request->get['transactionId'];

        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

	private function _getOrderAmount(){
		$this->load->model('checkout/order');
		$orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$orderInfo) {
			return false;
		}

		$currency = strtoupper($orderInfo['currency_code']);

		if( in_array($currency , $this->allowed_currencies) ){
			return round($orderInfo['total'], 2);
		}
		elseif ( $currency !== 'USD' ) {
			return $this->_convertAmountToAllowedCurrency($orderInfo['total'], $currency );
		}
		//If USD convert it directly to Allowed currency
		else{
			$target_currency_rate = $this->currency->gatUSDRate($this->allowed_currencies[0]);
            $amount_in_allowed_currency = $orderInfo['total']/$target_currency_rate;
            return round($amount_in_allowed_currency, 2);
		}
	}

	private function _convertAmountToAllowedCurrency($amount, $currency_code){
		$currenty_rate     = $this->currency->gatUSDRate($currency_code);
        $amount_in_dollars = $currenty_rate * $amount;

        $target_currency_rate = $this->currency->gatUSDRate($this->allowed_currencies[0]);
        $amount_in_allowed_currency = $amount_in_dollars/$target_currency_rate;
        return round($amount_in_allowed_currency, 2);
	}

}
