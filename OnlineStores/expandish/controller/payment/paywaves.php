<?php
/*
 * mVisa, NPs cards & Tahweel
 */
class ControllerPaymentPayWaves extends Controller {
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'paywaves';

    const BASE_API_TESTING_URL    = 'http://web.binarywaves.com/BMStaging/Views/Landing.aspx';
    const BASE_API_PRODUCTION_URL = 'http://web.binarywaves.com/BWBM/BWBM/Views/Landing.aspx';

    public function index(){
      $this->language->load_json('payment/' . self::GATEWAY_NAME);
      $this->data['action'] = $this->url->link('payment/paywaves/confirm');

      if (isset($this->session->data['error_paywaves'])) {
          $this->data['error_paywaves'] = $this->session->data['error_paywaves'];
      }

      $this->template = 'default/template/payment/' . self::GATEWAY_NAME . '.expand';
      $this->render_ecwig();
    }

    public function confirm(){
        $settings = $this->config->get('paywaves');
        
        $order_id = $this->session->data['order_id'];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $data = [
        	'Action'      => 'Lightbox',
        	'ServiceID'   => $settings['service_id'],
        	'MerchantID'  => $settings['merchant_id'],
        	'MSISDN'      => $order_info['payment_telephone'],
        	'Amount'      => $this->_getOrderAmount($order_info['total'], $order_info['currency_code']) * 100, //total amount in Pennies/cents
        	'OrderID'     => $order_id,
        	'SecretKey'   => $settings['secret_key']
        ];
        
        $result_json['url'] = $this->_getBaseUrl() . '?' . http_build_query($data);
        $result_json['success'] = '1';
       	
        $this->response->setOutput(json_encode($result_json));
    }

    /*
     * a callback function called by paywaves after payment process done.
     * request data - customer_name,amount, transaction_id, company_name, payment_code,transaction_date,responsecode,udf4,udf5
     *
    */
    public function response(){
  	  if( 
  	  	(!empty($_REQUEST['SystemReference']) && !empty($_REQUEST['NetworkReference']) ) ||
  	  	(!empty($_REQUEST['sysOrderID']) & !empty($_REQUEST['TransactionID'])) 
  	  ) {
  	  		$this->success();
  	  	}
        	else $this->fail();
    }


    public function success(){
        unset($this->session->data['error_paywaves']);
        $settings = $this->config->get(self::GATEWAY_NAME);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $settings['complete_status_id']);
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    public function fail(){
        $this->session->data['error_paywaves'] = 'PayWaves ERROR: payment failed';
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }


    private function _getBaseUrl(){
    	//Check current API Mode..
    	$debugging_mode = $this->config->get('paywaves')['debugging_mode'];
    	return ( isset($debugging_mode) && $debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_PRODUCTION_URL;
    }

    private function _getOrderAmount($amount, $currency_code){
    	$currency_code = strtoupper($currency_code);
        $account_currency_code = $this->config->get('paywaves')['account_currency'];

  		if( $currency_code == $account_currency_code ){
  			return round($amount, 2);
  		}
          elseif ( $currency_code !== 'USD' ) {
              $currenty_rate     = $this->currency->gatUSDRate($currency_code);
              $amount_in_dollars = $currenty_rate * $amount;

              $target_currency_rate = $this->currency->gatUSDRate($account_currency_code);
              $amount_in_account_currency = $amount_in_dollars/$target_currency_rate;
              return round($amount_in_account_currency, 2);
    		}
	    //If USD convert it directly to BHD
  	    else{
  		    $target_currency_rate = $this->currency->gatUSDRate($account_currency_code);
              $amount_in_account_currency  = $amount/$target_currency_rate;
              return round($amount_in_account_currency, 2);
          }
    }
}


