<?php
/*
 *  Tabby Pay Later..
 *  Itegration done via HPP (Hosted Payment Page) method.
 *  Tabby Docs: https://docs.tabby.ai/#section/Checkout-Web/Redirect-to-Hosted-Payment-Page
 */

class ControllerPaymentTabbyPayLater extends Controller
{
    
    protected $curlClient;
    
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->curlClient = $registry->get('curl_client');
    }
    
    /**
    * @var constant
    */
    const GATEWAY_NAME = 'tabby_pay_later';
    const PAYMENT_TYPE = 'pay_later';

    const BASE_API_URL = 'https://api.tabby.ai/api'; //Production & testing urls are the same.

    /**
     * @var array the validation errors array.
     */
    private $error = [];

    public function index(){
        $this->language->load_json('payment/' . self::GATEWAY_NAME);

        $this->data['action'] = $this->url->link('payment/' . self::GATEWAY_NAME . '/pay');

        if (isset($this->session->data['error_tabby'])) {
          $this->data['error_tabby'] = $this->session->data['error_tabby'];
          unset($this->session->data['error_tabby']);
        }

        $this->template = 'default/template/payment/tabby.expand';
        $this->render_ecwig();
    }

    public function success(){
        unset($this->session->data['error_tabby']);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('tabby_pay_later')['complete_status_id']);
        //redirect to success page...
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    public function failure(){
        $this->language->load_json('payment/' . self::GATEWAY_NAME);
        $this->session->data['error_tabby'] = $this->language->get('text_error_reject');
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

    public function cancel(){
        $this->language->load_json('payment/' . self::GATEWAY_NAME);
        $this->session->data['error_tabby'] = $this->language->get('text_error_reject');
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

   
    public function pay(){
        //create a checkout session first.
        $settings =  $this->config->get(self::GATEWAY_NAME);
        $this->load->model('payment/tabby');

        //Validate Payment Parameters
        if( ($payment = $this->model_payment_tabby->getPaymentObject($this->error, self::GATEWAY_NAME)) == -1 ){
            $result_json['success'] = '0';
            $result_json['message'] = "Tabby ERROR: " .  implode(',', $this->error);
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        $data = [
            'payment' => $payment,
            'lang'    => $this->config->get('config_language') == 'ar' ? 'ar' : 'en',
            'merchant_code' => $this->session->data['tabby_order_country_code'],//KSA, UAE, KWT, BAH
            'merchant_urls' => [
                'success' => $this->url->link('payment/' . self::GATEWAY_NAME . '/success'),
                'cancel'  => $this->url->link('payment/' . self::GATEWAY_NAME . '/cancel'),
                'failure' => $this->url->link('payment/' . self::GATEWAY_NAME . '/failure')
            ]
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => self::BASE_API_URL . "/v2/checkout",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ". $settings['public_key'],
            "Content-Type: application/json",
            "cache-control: no-cache"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response = json_decode($response, true);
        $available_products = array_keys($response['configuration']['available_products']);

        // check if payment type is not supported...
        if( !in_array(self::PAYMENT_TYPE, $available_products) ){
            $result_json['success'] = '0';
            $result_json['message'] = 'Tabby ERROR: ' . self::PAYMENT_TYPE . ' is currently not supported';
            $this->response->setOutput(json_encode($result_json));            
            return;
        }

        // Check HTTP status code
        if (!curl_errno($curl)) {
            switch ($http_code) {
              case 200:  # OK
              if (in_array($response['status'], ['created', 'approved'])) {
                $result_json['success'] = '1';
                $result_json['url'] = 'https://checkout.tabby.ai?apiKey='.$settings['public_key'] . '&sessionId=' . $response['id']. '&product=' . self::PAYMENT_TYPE;
                  $this->model_payment_tabby->addTabbyCapture($response);
                }
                break;
              case 400:
              $result_json['success'] = '0';
              $result_json['message'] = "Tabby ERROR: Bad Request. Request was not accepted; usually the result of a missing required parameter." .  $response['errorType']. ':' . ($response['error'] ?: var_export($response['errors'], true));
                break;
              case 401:
              $result_json['success'] = '0';
              $result_json['message'] = "Tabby ERROR: Unauthorized. Authentication issue; usually the result of using the incorrect key in the header." .  $response['errorType']. ':' . $response['error'];
                break;
              case 403:
              $result_json['success'] = '0';
              $result_json['message'] = "Tabby ERROR: Forbidden. Requested action is not allowed for that payment. For example, if you try to capture a payment that is already CLOSED or refund a payment that is AUTHORIZED." .  $response['errorType']. ':' . $response['error'];
                break;
              case 404:
              $result_json['success'] = '0';
              $result_json['message'] = "Tabby ERROR: Not Found. Requested resource does not exist. For example, if you try to update a payment using an invalid payment ID." .  $response['errorType']. ':' . $response['error'];
                break;
              default:
              $result_json['success'] = '0';
              $result_json['message'] = "Tabby ERROR: Unknown - " . $http_code . $err ?: ($response['errorType']. ':' . $response['error']);
            }
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
