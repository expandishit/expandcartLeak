<?php

class ControllerPaymentEButler extends Controller {
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'ebutler';

    public function index(){
      $this->language->load_json('payment/' . self::GATEWAY_NAME);
      $this->data['action'] = $this->url->link('payment/ebutler/confirm');

      if (isset($this->session->data['error_ebutler'])) {
          $this->data['error_ebutler'] = $this->session->data['error_ebutler'];
      }

      $this->template = 'default/template/payment/' . self::GATEWAY_NAME . '.expand';
      $this->render_ecwig();
    }

    public function confirm(){
        $order_id = $this->session->data['order_id'];
        $this->language->load_json('payment/' . self::GATEWAY_NAME);

        $this->load->model('payment/ebutler');
        $response = $this->model_payment_ebutler->pay( $order_id );

        if( isset($response['error']) || $response['code'] != 200 ){
            $result_json['success']  =  '0';
            $result_json['message']  =  self::GATEWAY_NAME . ': ' . (isset( $response['message']) ? $response['code'] . ' - ' . $response['message'] :  var_export($response['error'], true));
        }
        elseif( $response['code'] == 200 && !empty($response['data']['code'])) {
            $result_json['success']  =  '1';
            $result_json['url']      =  'https://payment.e-butler.com/' .  $response['data']['code'];
            $this->session->data["ebutler_payment_code"] = $response["data"]["code"];
        }
        else{
            $result_json['success']  =  '0';
            $result_json['message']  =  self::GATEWAY_NAME . ': Unknown error';    
        }
        $this->response->setOutput(json_encode($result_json));
    }

    /*
     * a callback function called by ebutler after payment process done.
     * request data - customer_name,amount, transaction_id, company_name, payment_code,transaction_date,responsecode,udf4,udf5
     *
    */
    public function response(){
      $responsecode = $this->request->get['responsecode'];

      if($responsecode == '000') $this->success();
      else $this->fail();
    }


    public function success(){
        unset($this->session->data['error_ebutler']);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get(self::GATEWAY_NAME . '_complete_status_id'));
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    public function fail(){
        $this->session->data['error_ebutler'] = 'E-Butler ERROR: payment failed';
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }
}
