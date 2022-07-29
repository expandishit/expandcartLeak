<?php

class ControllerPaymentBookeey extends Controller {
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'bookeey';

    public function index(){
      $this->language->load_json('payment/' . self::GATEWAY_NAME);
      $this->data['action'] = 'index.php?route=payment/' . self::GATEWAY_NAME . '/confirm';

      if (isset($this->session->data['error_bookeey'])) {
          $this->data['error_bookeey'] = $this->session->data['error_bookeey'];
      }
      if (isset($this->session->data['bookeey_payment_mode'])) {
          $this->data['bookeey_payment_mode'] = $this->session->data['bookeey_payment_mode'];
      }
      $this->data['bookeey_payment_modes'] = $this->config->get('bookeey_payment_modes');
      $this->template = 'default/template/payment/' . self::GATEWAY_NAME . '.expand';
      $this->render_ecwig();
    }


    private function _getOrderAmount(){
      $this->load->model('checkout/order');
      $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

      if (!$orderInfo) {
        return false;
      }

      $amount = round($orderInfo['total'], 2);
      return $amount;
    }

    public function pay(){

      $order_id = $this->session->data['order_id'];
      $this->language->load_json('payment/' . self::GATEWAY_NAME);

      $this->load->model('payment/bookeey');
      $mode = $this->request->post['bookeey_payment_mode'];
      $response = $this->model_payment_bookeey->pay( $order_id, $this->_getOrderAmount(), $mode );

      if( $response['g_status'] == -1){
        $result_json['error_bookeey'] = 'BOOKEEY ERROR: ' . $response['g_errorDescription'];
        $result_json['success'] = '0';
      }
      elseif( $response['g_status'] == 1){
        $result_json['success'] = '1';
        //a temporary fix, because API returns g_response_trans_type wrong in case of bookeey
        switch($mode){
          case 'bookeey':
            $result_json['url'] = $response['bookeeyUrl'];
            break;

          case 'knet':
            $result_json['url'] = $response['knetUrl'];
            break;

          case 'amex':
            $result_json['url'] = $response['ccUrl'];
            break;

          case 'credit':
            $result_json['url'] = $response['ccUrl'];
            break;

          default:
            $result_json['url'] = '';
        }
        // switch($response['g_response_trans_type']){
        //   case 'Bookeey_RESPONSE':
        //     $result_json['url'] = $response['bookeeyUrl'];
        //     break;

        //   case 'KNETPAY_RESPONSE':
        //     $result_json['url'] = $response['knetUrl'];
        //     break;

        //   case 'Amex_RESPONSE':
        //     $result_json['url'] = $response['ccUrl'];
        //     break;

        //   case 'CREDIT_RESPONSE':
        //     $result_json['url'] = $response['ccUrl'];
        //     break;

        //   default:
        //     $result_json['url'] = '';
        // }
      }else {
        $result_json['error_bookeey'] = 'BOOKEEY ERROR: ' . $this->language->get('general_error');
        $result_json['success'] = '0';
      }

      $this->response->setOutput(json_encode($result_json));
    }


    function success(){
        unset($this->session->data['error_bookeey']);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get(self::GATEWAY_NAME . '_complete_status_id'));
        
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

 
    function fail(){
        $this->session->data['error_bookeey'] = 'BOOKEEY ERROR: ' . $this->request->get['errorMessage'] . ' ,Merchant Name: ' . $this->request->get['merchantName'] . ', Amount: ' . $this->request->get['amt'];

        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }
}
