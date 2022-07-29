<?php

class ControllerPaymentPaytr extends Controller
{

    /**
     * @var string $stringpaymentName
     */
    private $paymentName = 'paytr';


    /**
     * @var array $allowedCurrencies
     */
    private $allowedCurrencies = [
        "TL", "USD", "GBP", "TRY", "EUR", "RUB"
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
        $this->language->load_json('payment/' . $this->paymentName);
        if (isset($this->session->data["error_{$this->paymentName}"])) {
            $this->data["error_{$this->paymentName}"] = $this->session->data["error_{$this->paymentName}"];
        }

        // create payment order and obtain iframe token
        // the ontained iframe token is appneded to $this->data array
        $this->confirmPayment();

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

        // prepare payment data
        $payment_data = $this->preparePaymentData($order_info);

        // get iframe toke
        $generated_iframe_token = $this->generateToken($payment_data);
        if ($generated_iframe_token['status'] == 'success') {
            $this->data['iframe_token'] = $generated_iframe_token['token'];
        }
    }


    /**
     * form the payment data array
     *
     * @param array order_info
     *
     * @return array payment_data
     */
    public function preparePaymentData($order_info)
    {
        $this->initializer([
            'checkout/order'
        ]);

        $payment_data = [];

        // gateway credentials
        $payment_data['merchant_id'] = $this->config->get('paytr_merchant_id');
        $merchant_key = $this->config->get('paytr_merchant_key');
        $secret_key = $this->config->get('paytr_secret_key');

        // decide currency and total
        $decided_currency_and_total = $this->decideCurrencyAndAmount($order_info['total'], $order_info['currency_code']);

        // payment params
        $payment_data['email'] = $order_info['email'];
        $payment_data['payment_amount']	= $decided_currency_and_total['amount'];
        $payment_data['currency'] = $decided_currency_and_total['currency_code'];
        $payment_data['merchant_oid'] = $order_info['order_id'];
        $payment_data['user_name'] = "{$order_info['firstname']} {$order_info['lastname']}";
        $payment_data['user_address'] = $this->determineUseAddress($order_info);
        $payment_data['user_phone'] = $order_info['telephone'];
        $payment_data['merchant_ok_url'] = $this->url->link('payment/paytr/callBack', '', true);
        $payment_data['merchant_fail_url'] = $this->url->link('payment/paytr/callBack', '', true);
        $payment_data['user_basket'] = array_map(function($order_product) use ($payment_data) {
            if ($order_product) {
                return [
                    $order_product['name'],
                    $this->decideCurrencyAndAmount($order_product['price'], $payment_data['currency'])['amount'],
                    $order_product['quantity']
                ];
            }
        }, $this->model_checkout_order->getOrderProducts($order_info['order_id']));
        $payment_data['user_basket'] = base64_encode(json_encode($payment_data['user_basket']));
        $payment_data['user_ip'] = $this->getCurrentUserIP(); // capture user ip
        $payment_data['timeout_limit'] = "30";
        $payment_data['debug_on'] = 1;
        $payment_data['test_mode'] = (int)$this->config->get($this->paymentName . '_test_mode');
        $payment_data['no_installment']	= 1;
        $payment_data['max_installment'] = 0;

        // generate sha256 signature
        $payment_data['paytr_token'] = $this->generatePaymentTokenHash(
            'sha256',
            $merchant_key,
            $secret_key,
            [
                $payment_data['merchant_id'],
                $payment_data['user_ip'],
                $payment_data['merchant_oid'],
                $payment_data['email'],
                $payment_data['payment_amount'],
                $payment_data['user_basket'],
                $payment_data['no_installment'],
                $payment_data['max_installment'],
                $payment_data['currency'],
                $payment_data['test_mode']
            ]
        );

        return $payment_data;
    }


    /**
     * generate iframe token
     *
     * @param array payment_data
     *
     * @return array iframe (token service response)
     */
    private function generateToken(array $payment_data)
    {

        return $this->invokeCurlRequest('https://www.paytr.com/odeme/api/get-token', [
            'merchant_id' => $payment_data['merchant_id'],
            'user_ip' => $payment_data['user_ip'],
            'merchant_oid' => $payment_data['merchant_oid'],
            'email' => $payment_data['email'],
            'payment_amount' => $payment_data['payment_amount'],
            'paytr_token' => $payment_data['paytr_token'],
            'user_basket' => $payment_data['user_basket'],
            'debug_on' => $payment_data['debug_on'],
            'no_installment' => $payment_data['no_installment'],
            'max_installment' => $payment_data['max_installment'],
            'user_name' => $payment_data['user_name'],
            'user_address' => $payment_data['user_address'],
            'user_phone' => $payment_data['user_phone'],
            'merchant_ok_url' => $payment_data['merchant_ok_url'],
            'merchant_fail_url' => $payment_data['merchant_fail_url'],
            'timeout_limit' => $payment_data['timeout_limit'],
            'currency' => $payment_data['currency'],
            'test_mode' => $payment_data['test_mode'],
            'payment_type'=>'Card',
            'lang' => 'en'
        ]);
    }


    /**
     * get current user IP
     *
     * @return string ip
     */
    private function getCurrentUserIP()
    {
        if(isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }

        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }

        return $_SERVER["REMOTE_ADDR"];
    }


    /**
     * generate payment hash token
     *
     * @param string method (sha256 default)
     * @param string merchant_key
     * @param string secret_key
     * @param array payment_args
     *
     * @return string hash
     */
    public function generatePaymentTokenHash($method = 'sha256', $merchant_key, $secret_key, $payment_args)
    {
        return base64_encode(hash_hmac(
            $method ? $method : 'sha256',
            implode('', $payment_args) . $secret_key,
            $merchant_key,
            true
        ));
    }


    /**
     * handle CURL request
     *
     * @return array curl response
     */
    private function invokeCurlRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = @curl_exec($ch);

        if(curl_errno($ch)){
            die("PAYTR IFRAME connection error. err:".curl_error($ch));
        }

        curl_close($ch);
        return json_decode($result, true);
    }


    /**
     * get user's address to order info
     *
     * @param array $order_into
     *
     * @return string address
     */
    private function determineUseAddress(array $order_info):string
    {

        if (isset($order_info['payment_address_1']) && $order_info['payment_address_1']) {
            return $order_info['payment_address_1'];
        }

        if (isset($order_info['payment_address_2']) && $order_info['payment_address_2']) {
            return $order_info['payment_address_2'];
        }

        return $order_info['payment_country'];
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

    /**
     * @param $amount
     * @param $currency_code
     * @return array
     */
    private function decideCurrencyAndAmount(string $amount, string $currency_code): array
    {
        $amount = $amount * 100;

        // remove uninstalled currencies
        $allowedCurrencies = $this->filterAllowedCurrencies();

        $check_if_in_array = in_array(
            strtoupper($currency_code),
            $allowedCurrencies
        );

        if (! empty($paytr_default_currency = $this->config->get('paytr_default_currency'))) {
          if( $currency_code == $paytr_default_currency ){
               $amount = round($amount,2);
             }
          else{
            $amount = $this->currency->convert($amount, 'USD', $paytr_default_currency);
            $amount = round($amount);
             }
            $currency_code = $paytr_default_currency;
            return compact('amount', 'currency_code');
        }

        if ($check_if_in_array && count($allowedCurrencies) > 0) {
            $amount = $this->currency->convert($amount, 'USD', $currency_code);
            $amount = round($amount);
            return compact('amount', 'currency_code');
        }

        if (!$this->currency->has('USD') && count($allowedCurrencies) > 0) {
            $amount = $this->currency->convert($amount, $currency_code, $this->allowedCurrencies[0]);
            $amount = round($amount);
            $currency_code = $this->allowedCurrencies[0];

            return compact('amount', 'currency_code');
        }

        $currency_code = strtoupper($currency_code) !== 'USD' ? 'USD' : $currency_code;

        $dollar_rate = $this->currency->gatUSDRate($this->config->get('config_currency'));

        $amount = $this->currency->format($amount, $currency_code, false, false);

        $amount = round($dollar_rate * $amount);

        return compact('amount', 'currency_code');
    }


    /**
     * remove uninstalled currencies
     *
     * @return array allowed currencies
     */
    private function filterAllowedCurrencies(): array
    {
        return array_filter($this->allowedCurrencies, function ($currency) {
            return $this->currency->has($currency);
        });
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
     * handel the payment success callback
     * confirm order
     * redirect to checkout/success to complete order
     */
    public function handlePaymentSuccessCallback()
    {
        $this->confirm();
        $this->redirect($this->url->link('checkout/success'));
    }
    
    
    public function callBack() {

        $this->load->model('checkout/order');
        $this->load->model('extension/payment_method');
        $post = $this->request->post;
        $merchant_key = $this->config->get('paytr_merchant_key');
        $merchant_salt = $this->config->get('paytr_secret_key');

        $order_info = $this->model_checkout_order->getOrder($post['merchant_oid']);

        $hash = base64_encode(hash_hmac('sha256', $post['merchant_oid'] . $merchant_salt . $post['status'] . $post['total_amount'], $merchant_key, true));

        $orderSuccess = $this->config->get($this->paymentName . '_completed_order_status_id');

        $orderFaild = $this->config->get($this->paymentName . '_faild_order_status_id');


        if ($hash != $post['hash'] || $post['status'] == 'failed') {
            $this->model_checkout_order->confirm($order_info['order_id'], $orderFaild, 'payment not complete from payment method');
            die('PAYTR notification failed: bad hash2');
        }


        if ($post['status'] == 'success') {
            $this->model_checkout_order->confirm($order_info['order_id'], $orderSuccess);

            $this->load->model('payment/paytr');
            $this->model_payment_paytr->insertpaymentTransaction([
                'order_id' => (int)$order_info['order_id'],
                'transaction_id' => $post['merchant_oid'],
                'payment_gateway_id' => $this->model_extension_payment_method->selectByCode('paytr')['id'],
                'payment_method' => 'PayTr',
                'status' => 'Success',
                'amount' => $post['payment_amount'],
                'currency' => $post['currency'],
                'details' => '',
            ]);
            echo "OK";
            exit;
        }
        echo "OK";
        exit;
    }

}
