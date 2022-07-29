<?php

class ControllerPaymentNetwork extends Controller
{


    /**
     * @var string $paymentModuleName 
     */
    private $paymentModuleName = 'network';

    const BASE_API_URL_PRODUCTION = 'https://api-gateway.ngenius-payments.com/';
    const BASE_API_URL_TESTING    = 'https://api-gateway.sandbox.ngenius-payments.com/';

    const BASE_API_URL_PRODUCTION_ACCESS_TOKEN = "https://api-gateway.ngenius-payments.com/identity/auth/access-token";
    const BASE_API_URL_TESTING_ACCESS_TOKEN    = "https://api-gateway.sandbox.ngenius-payments.com/identity/auth/access-token";

    /**
     * @var string allowedCurrencies
     */
    private $allowedCurrencies = [
        "AED", /*"USD", "EUR" */
    ];


    /**
     * @var string errors
     */
    private $errors = [];


    /**
     * payment index function
     * checks for post request to handle payment 
     * 
     * @return void
     */
    public function index()
    {
        
        $this->template = 'default/template/payment/' . $this->paymentModuleName . '.expand';
        $this->render_ecwig();
    }


    /**
     * handle payment
     * 1- generate access token
     * 2- store token in session data to caputre payment status later
     * 3- request payment url
     * 4- render .expand file that receives payment url and redirects to the hosted payment page
     * 
     * @return template
     */
    public function confirmPayment()
    {

        $this->language->load_json('payment/' . $this->paymentModuleName);

        
        // generate access token
        $response = $this->generateToken();

        if (!$response->access_token || empty($response->access_token)) {
            $result['success'] = false;
            // $result['message'] = $this->language->get('error_payment_server');
            $result['message'] = 'NETWORK GATEWAY ERROR: ' . $response->errors[0]->message;
            $this->response->setOutput(json_encode($result));
            return;
        }
        $access_token = $response->access_token;

        // store token in session data
        $this->session->data['access_token'] = $access_token;

        // get payment url
        $response = $this->requestPaymentUrl(json_encode($this->getPaymentParameters()), $access_token);

        //Updated
        if(isset($response->_links->payment->href) && !empty($response->_links->payment->href)){
            $result['success'] = true;
            $result['payment_url'] = $response->_links->payment->href;
            $this->response->setOutput(json_encode($result));
            return;
        }

        if (!empty($response->error) ) {
            $result['success'] = false;
            $result['message'] = 'NETWORK GATEWAY ERROR: ' . $response->message;
            $this->response->setOutput(json_encode($result));
            return;
        }

        if (!empty($response->errors) ){
            $result['success'] = false;
            $result['message'] = 'NETWORK GATEWAY ERROR: ' . $response->message . ' : ' . $response->errors[0]->message;
            $this->response->setOutput(json_encode($result));
            return;
        }

        $result['success'] = false;
        $result['message'] = 'NETWORK GATEWAY ERROR: ' . $this->language->get('error_payment_server');
        $this->response->setOutput(json_encode($result));
    }



    /**
     * prepare parameters for the payment
     * 
     * @return array payment parameters
     */
    private function getPaymentParameters()
    {

        $this->initializer([
            'checkout/order'
        ]);


        // Get Order Info
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $decided_currency_amount = $this->decideCurrencyAndAmount($order_info['total'], $order_info['currency_code']); // handle amount&currecny conversion
        $amount = $decided_currency_amount['amount'];
        $currency_code = $decided_currency_amount['currency_code'];

        return [
            'action' => 'SALE',
            'amount' => [
                'currencyCode' => $currency_code,
                'value' => (int)$amount * 100 //This field must be int(64) according to Network gateway documentation - minor units (cents, ...)
            ],
            'emailAddress' => $order_info['email'],
            'billingAddress' => [
                'firstName' => $order_info['payment_firstname'] ?? '-',
                'lastName' => $order_info['payment_lastname'] ?? '-',
                'address1' => $order_info['payment_address_1'] ?? '-',
                'city' => $order_info['payment_city'] ?? '-',
                'countryCode' => $order_info['payment_iso_code_2'] ?? '-',
            ],
            'merchantAttributes' => [
                'skipConfirmationPage' => true,
                'redirectUrl' => $this->url->link('payment/network/handlePayementCallback', '', true),
                'cancelUrl' => $this->url->link('checkout/error', '', true)
            ]
        ];
    }


    /**
     * generate acces token 
     * 
     * @return string access_token
     */
    public function generateToken()
    {
        $headers = [
            "Accept: application/vnd.ni-identity.v1+json",
            "Authorization: Basic " . $this->config->get('network_api_key'),
            "Content-Type: application/vnd.ni-identity.v1+json"
        ];
        

        return $this->invokeCurlRequest(
                "POST",
                $this->_getBaseURL(true), //for Access token url
                $headers,
                $this->_getRealmName()
            );
    }


    /**
     * request payment url
     * 
     * @return stdClass payment_url
     */
    public function requestPaymentUrl($postData, $access_token)
    {
        $headers = [
            "Authorization: Bearer " . $access_token,
            "Content-Type:application/vnd.ni-payment.v2+json",
            "Accept: application/vnd.ni-payment.v2+json"
        ];

        $outlet_ref = $this->config->get('network_outlet_id');

        return $this->invokeCurlRequest(
            "POST",
            $this->_getBaseURL()."transactions/outlets/{$outlet_ref}/orders",
            $headers,
            $postData
        );
    }




    /**
     * handle curl requests
     * 
     * @param string type 
     * @param string url 
     * @param string headers 
     * @param string data 
     * 
     * @return array curl response
     */    
    public function invokeCurlRequest($type, $url, $headers, $data = [])
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($type == "POST" || $type == "PUT") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            if ($type == "PUT") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            }
        }

        $server_output = curl_exec($ch);
        return json_decode($server_output);
    }


    /**
     * confirm payment
     */
    public function confirm()
    {
        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get($this->paymentModuleName . '_completed_order_status_id'));
    }



    /**
     * handle payment success callback
     */
    public function handlePayementCallback()
    {

        $this->language->load_json('payment/' . $this->paymentModuleName);


        $outlet_ref = $this->config->get('network_outlet_id');
        $order_ref = $this->request->get['ref'];
        $access_token = $this->session->data['access_token'];
        unset($this->session->data['access_token']);

        $url = $this->_getBaseURL() . "transactions/outlets/{$outlet_ref}/orders/{$order_ref}";
        $headers = [
            "Authorization: Bearer " . $access_token,
            "Content-Type:application/vnd.ni-payment.v2+json",
            "Accept: application/vnd.ni-payment.v2+json"
        ];
        $output = $this->invokeCurlRequest('GET', $url, $headers);
        $state = $output->{'_embedded'}->payment[0]->{'_embedded'}->{'cnp:capture'}[0]->state;

        if ($state == 'SUCCESS') {
            $this->confirm();
            $result['success'] = true;
            $this->redirect($this->url->link('checkout/success', '', true));
            return;
        }
        
        $result['success'] = false;
        $result['message'] = $this->language->get('error_card_data');
        $this->redirect($this->url->link('checkout/error', '', true));
    }


    /**
     * do need operation upon amount and currency
     * decide the correct currency
     * round and convert the amount based on the currency
     * multiply the amount in 100 (ex: convert cents to dollars)
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
        //If not USD convert it USD then to AED
        else if(strtoupper($currency_code) !== 'USD'){
            $currenty_rate     = $this->currency->gatUSDRate($currency_code);
            $amount_in_dollars = $currenty_rate * $amount;

            $target_currency_rate = $this->currency->gatUSDRate($allowedCurrencies[0]);
            $amount_in_AED        = $amount_in_dollars/$target_currency_rate;
            
            $amount = round($amount_in_AED, 2);
            $currency_code = $allowedCurrencies[0];
            return compact('amount', 'currency_code');
        }
        //If USD convert it directly to AED
        else{
            $target_currency_rate = $this->currency->gatUSDRate($allowedCurrencies[0]);
            $amount_in_AED        = $amount/$target_currency_rate;
            $amount = round($amount_in_AED, 2);
            $currency_code = $allowedCurrencies[0];
            return compact('amount', 'currency_code');
        }


        // if (!$this->currency->has('USD') && count($allowedCurrencies) > 0) {
        //     $amount = $this->currency->convert($amount, $currency_code, $this->allowedCurrencies[0]);
        //     $amount = round($amount, 2);
        //     $currency_code = $this->allowedCurrencies[0];

        //     return compact('amount', 'currency_code');
        // }
        
        // $currency_code = strtoupper($currency_code) !== 'USD' ? 'USD' : $currency_code;

        // $dollar_rate = $this->currency->gatUSDRate($this->config->get('config_currency'));

        // $amount = $this->currency->format($amount, $currency_code, false, false);
        // $amount = round($dollar_rate * $amount, 2);

        // return compact('amount', 'currency_code');
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

    private function _getRealmName(){
        return $this->config->get('network_live_mode') == 1 ? '{"realmName":"networkinternational"}' : '{"realmName":"ni"}';
    }

    private function _getBaseURL($access_token = 0){
        $mode = $this->config->get('network_live_mode');

        if( $access_token == 1 ){
            return $mode == 1 ? self::BASE_API_URL_PRODUCTION_ACCESS_TOKEN : self::BASE_API_URL_TESTING_ACCESS_TOKEN;
        }
        return $mode == 1 ? self::BASE_API_URL_PRODUCTION : self::BASE_API_URL_TESTING;
    }
}

?>
