<?php
/*
 *  Tabby By Installments..
 *  Itegration done via HPP (Hosted Payment Page) method.
 *  Tabby Docs: https://docs.tabby.ai/#section/Checkout-Web/Redirect-to-Hosted-Payment-Page
 */

class ControllerPaymentTabbyInstallments extends Controller 
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
    const GATEWAY_NAME = 'tabby_installments';
    const PAYMENT_TYPE = 'installments';

    const BASE_API_URL = 'https://api.tabby.ai/api'; //Production & testing urls are the same.

    /**
     * @var array the validation errors array.
     */
    private $error = [];

    public function index(){
      $this->language->load_json('payment/' . self::GATEWAY_NAME);

	    $this->data['action'] = $this->url->link('payment/' . self::GATEWAY_NAME . '/pay');

      if (isset($this->session->data['error_tabby'])) {
          $this->data['error_tabby'] =  $this->session->data['error_tabby'];
          unset($this->session->data['error_tabby']);
      }

      $this->template = 'default/template/payment/tabby.expand';
      $this->render_ecwig();
    }

    public function success(){
        unset($this->session->data['error_tabby']);

        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get(self::GATEWAY_NAME)['complete_status_id']);


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
        if( $response['status'] == 'error'){
            $result_json['success'] = '0';
            $result_json['message'] = 'Tabby ERROR: ' . $response['errorType'] . ' - ' . $response['error'];
            $this->response->setOutput(json_encode($result_json));            
            return;
        }

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

    public function notification(){

        //Check if Method not post
        if( $this->request->server['REQUEST_METHOD'] != 'POST' ) {
            //Send a 405 Method Not Allowed header.
            header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'message' => "No route found - Method Not Allowed (Only POST)"
            ]));
            return FALSE;
        }
        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod'      => 'extension/payment_method',
        ]);

        //if post, get body json parameters...
        $response = json_decode(file_get_contents('php://input'), true);
        $tabby_settings  = $this->config->get('tabby_pay_later');
        $log = new Log("tabby.log");
//        $log->write(file_get_contents('php://input'));

        $tabby_statuses = [
            "closed" => "tabby_close_status_id",
            "authorized" => "tabby_authorized_status_id",
            "rejected" => "tabby_rejected_status_id",
            "expired" => "tabby_expired_status_id"
        ];
        $status_id = $tabby_settings[$tabby_statuses[$response['status']]];

        $this->load->model('payment/tabby');
        $log->write( date("d-m-Y H:i:s") . $response['status'] .'|');
        $log->write( date("d-m-Y H:i:s") . $status_id .'|');

        if ($response['status'] == 'authorized') {
            if ($checkoutResponse = $this->paymentTransaction->getPaymentTransactionInfoByOrderId($response['order']['reference_id'])) {
                $checkoutResponse = json_decode($checkoutResponse['details'], true);
                // mark payment to captured
                $paymentTransaction = $checkoutResponse['payment'];

                $data = [
                    'amount' => $paymentTransaction['amount'],
                    'tax_amount' => $paymentTransaction['order']['tax_amount'],
                    'shipping_amount' => $paymentTransaction['order']['shipping_amount'],
                    'discount_amount' => $paymentTransaction['order']['discount_amount'],
                    'created_at' => (new DateTime(date('Y-m-d H:i:s', time())))->format(DateTime::ATOM),
                    'items' => $paymentTransaction['items'],
                ];

                $settings = $this->config->get(self::GATEWAY_NAME);

                $results = $this->curlClient->request(
                    'POST',
                    self::BASE_API_URL . "/v1/payments/" . $paymentTransaction['id'] . "/captures",
                    [],
                    $data,
                    [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer " . $settings['secret_key'],
                        'cache-control' => "no-cache",
                    ]
                );

                if ($results->ok()) {
                    $tabbyData = [
                        'order_id' => $response['order']['reference_id'],
                        'transaction_id' => $checkoutResponse['id'],
                        'payment_gateway_id' => $this->paymentMethod->selectByCode('tabby')['id'],
                        'payment_method' => 'Tabby',
                        'status' => 'Success',
                        'amount' => $paymentTransaction['amount']?:$checkoutResponse['amount'],
                        'currency' => $paymentTransaction['currency']?:$checkoutResponse['currency']
                    ];
                    if ($results->getContent())
                        $tabbyData['details'] = json_encode($results->getContent());

                    $this->paymentTransaction->update($tabbyData);

                }
            }
        }

        if($response['status'] == 'authorized' && !empty($response['captures'])){
            $status_id = (int)$tabby_settings["capture_status_id"];
        }elseif($response['status'] == 'closed' && !empty($response['refunds'])){
            $status_id = (int)$tabby_settings["refund_status_id"];
        }elseif($response['status'] == 'closed' && !empty($response['captures']) && empty($response['refunds'])){
            $status_id = Null;
        }
        try{
            if($status_id == null)
                return;
            $log->write( date("d-m-Y H:i:s") . $response['order']['reference_id'] .'|');
            $this->model_payment_tabby->changeTabbyOrderStatus((int)$response['order']['reference_id'], $status_id);
            $log->write( date("d-m-Y H:i:s") . 'changeTabbyOrderStatus -> passed' .'|');
            // add data to order status history
            $historyData = [
                'order_id' => (int)$response['order']['reference_id'],
                'notify' => 0,
                'notify_by_sms' => 0,
                'order_status_id' => $status_id,
                'comment' => 'Status Changed By Tabby'
            ];
            $this->model_payment_tabby->addOrderHistory($historyData);
            $log->write( date("d-m-Y H:i:s") . 'addOrderHistory -> passed' .'|');
        }catch (\Exception $e){
            $log = new Log("tabby.log");
            $log->write($e->getMessage());
            return;
        }


    }
}
