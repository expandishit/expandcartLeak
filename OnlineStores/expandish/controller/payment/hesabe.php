<?php

require_once 'hesabe_kit/HesabeCrypt.php';

class ControllerPaymentHesabe extends Controller
{

    /**
     * @var string $stringpaymentName
     */
    private $paymentName = 'hesabe';


    /**
     * @var array allowedCurrencies
     */
    private $allowedCurrencies = [
        'KWD', 'BHD', 'AED', 'OMR', 'QAR', 'SAR', 'USD', 'GBP', 'EUR'
    ];

    /**
     * @var array $errors
     */
    private $errors = array();


    /**
     * index function that appends needed template data then renders it
     * 
     * @return template
     */
    public function index()
    {
     
        $this->template = 'default/template/payment/' . $this->paymentName . '.expand';
        $this->render_ecwig();
    }


    /**
     * prepares payment data and generating payment iframe token
     * 
     * @return void
     */
    public function confirmPayment()
    {
        $this->initializer([
            'checkout/order'
        ]);
        unset($this->session->data["error_{$this->paymentName}"]);

        $this->language->load_json('payment/' . $this->paymentName);

        // Get Order Info
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        $payment_response = $this->orderPayment($order_info);

        if (isset($payment_response['mobile_number'])) {
            $this->errors['error_payment'] = $payment_response['mobile_number'][0];
            $this->response->setOutput(json_encode($this->errors));
            return;
        }

        $this->data['payment_url'] = $payment_response;

        $this->response->setOutput(json_encode($this->data));
    }


    /**
     * form the payment data array 
     * 
     * @param array order_info
     * 
     * @return array payment_data
     */
    public function preparePaymentData()
    {
        $this->initializer([
            'checkout/order'
        ]);
        
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        $payment_data = [];
        $orderTotal = $order_info['total'] * $order_info['currency_value'];

        // payement params
        $decide_currency_amount = $this->decideCurrencyAndAmount($orderTotal, $order_info['currency_code']);
        $payment_data['currency'] = $decide_currency_amount['currency_code'];
        $payment_data['amount'] = $decide_currency_amount['amount'];
        $payment_data['responseUrl'] = $this->url->link('payment/hesabe/handlePaymentCallback', '', true);
        $payment_data['failureUrl'] = $this->url->link('checkout/error');
        $payment_data['orderReferenceNumber'] = $order_info['order_id'];
        $payment_data['version'] = '2.0';

        return $payment_data;
    }



    /**
     * create a payment order
     * 
     * @return mixed false|string(payment url)
     */
    public function orderPayment()
    {
        // gateway credentials
        $merchant_code = $this->config->get("{$this->paymentName}_merchant_id");
        $access_code = $this->config->get("{$this->paymentName}_access_code");
        $secret_key = $this->config->get("{$this->paymentName}_secret_key");
        $iv_key = $this->config->get("{$this->paymentName}_iv_key");
        $payment_type = $this->config->get("{$this->paymentName}_payment_type");
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        
        $payment_data = $this->preparePaymentData();
        $payment_data['merchantCode'] = $merchant_code;
        $payment_data['paymentType'] = $payment_type;
        $data['fname'] = $this->customer->getFirstName();
        $data['lname'] = $this->customer->getLastName();
        $payment_data['name']  = isset($data['fname'], $data['lname']) ? $data['fname'] . ' ' . $data['lname'] : $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];

        $payment_data['email'] = $this->customer->getEmail();
        $payment_data['email']  = isset( $payment_data['email']) ?  $payment_data['email'] : $order_info['email'];

        $data['mobile_number'] = $this->customer->getTelephone();
        $payment_data['mobile_number']  = isset($data['mobile_number']) ? $data['mobile_number'] : $order_info['payment_telephone']; 

        $payment_data = json_encode($payment_data);
        $payment_data = HesabeCrypt::encrypt($payment_data, $secret_key, $iv_key);

       
        $url = $this->makeUrl("checkout");
        $headers = [
            "accessCode" => $access_code
        ];

        $response = $this->invokeCurlRequest('POST', $url, $headers, $payment_data);
        $response = HesabeCrypt::decrypt($response, $secret_key, $iv_key);
        $response = json_decode($response, true);

        return !$response['status'] ? $response['data'] : $this->makeUrl('payment', ['data' => $response['response']['data']]);
    }


    /**
     * handle CURL request
     * 
     * @return array curl response
     */
    public function invokeCurlRequest($type, $url, $headers, $data)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => compact('data'),
            CURLOPT_HTTPHEADER => [
                "accessCode: " . $headers['accessCode'],
                "Accept: application/json"
            ]
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    
    /**
     * confirm order
     */
    public function confirm()
    {
        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm(
            $this->session->data['order_id'], 
            $this->config->get($this->paymentName . '_completed_order_status_id')
        );
    }


    /**
     * handel the payment callback
     * confirm order
     * redirect to checkout/success to complete order  
     */
    public function handlePaymentCallback()
    {

        $response = $this->request->get['data'];
        $secret_key = $this->config->get("{$this->paymentName}_secret_key");
        $iv_key = $this->config->get("{$this->paymentName}_iv_key");
        $response = HesabeCrypt::decrypt($response, $secret_key, $iv_key);
        $response = json_decode($response, true);
        if (!$response['status']) {
            $this->redirect($this->url->link('checkout/error'));
        }

        $this->load->model('payment/hesabe');
        $this->confirm();
        $this->initializer([
            'paymentMethod' => 'extension/payment_method',
            ]);
        $payment_gateway_id = $this->paymentMethod->selectByCode('hesabe')['id'];
        $response['payment_gateway_id'] = $payment_gateway_id;
        $this->model_payment_hesabe->insertpaymentTransaction($response);

        $this->redirect($this->url->link('checkout/success'));
    }


    /**
     * do needed operations upon amount and currency,
     * decide the correct currency,
     * round and convert the amount based on the currency,
     * and multiply the amount in 100 (ex: convert cents to dollars).
     * 
     * @param float $amount
     * @param string $currency_code
     * 
     * @return array [altered amount, altered currency code]
     */

    private function decideCurrencyAndAmount($amount, $currency_code)
    {

        // remove uninstalled currencies 
        $allowedCurrencies = $this->filterAllowdCurrencires();
        $check_if_in_array = (in_array(
            strtoupper($currency_code), 
            $allowedCurrencies
        )) ? true : false;

        if ($check_if_in_array && count($allowedCurrencies) > 0) {
            
            $amount = round($amount, 2);
            return compact('amount', 'currency_code');
        } 

        if (!$this->currency->has('USD') && count($allowedCurrencies) > 0) {
            $amount = $this->currency->convert($amount, $currency_code, $this->allowedCurrencies[0]);
            $amount = round($amount, 2);
            $currency_code = $this->allowedCurrencies[0];

            return compact('amount', 'currency_code');
        }

        $currency_code = strtoupper($currency_code) !== 'USD' ? 'USD' : $currency_code;

        $dollar_rate = $this->currency->gatUSDRate($this->config->get('config_currency'));

        $amount = $this->currency->format($amount, $currency_code, false, false);
        $amount = round($dollar_rate * $amount, 2);

        return compact('amount', 'currency_code');
    }


    /**
     * remove uninstalled currencies 
     * 
     * @return array allowed currencies
     */
    private function filterAllowdCurrencires()
    {
        return array_filter($this->allowedCurrencies, function ($crncy) {
            return $this->currency->has($crncy);
        });
    }


    /**
     * consist a url from base
     * 
     * @param string $path
     */
    private function makeUrl($path, $params = '')
    {
        
        $query_params = '?';
        foreach($params as $key => $param) {
            $query_params .= "{$key}={$param}&";
        }
        $query_params = rtrim($query_params, '&');
        
        if ($query_params === '?') {
            $query_params = '';
        }

        $test_mode = (bool)$this->config->get("{$this->paymentName}_test_mode");
        if ($test_mode) {
            $base_url = "https://sandbox.hesabe.com/";
        } else {
            $base_url = "https://api.hesabe.com/";
        }

        return $base_url . $path . $query_params;
    }
  
}

?>
