<?php

class ControllerPaymentcowpay extends Controller
{
    /**
     * @var string
     */
    private $payment_module_name = 'cowpay';

    /**
     * @var array
     */
    private $allowed_currencies = [
        'EGP',
    ];

    /**
     *
     */
    protected function index()
    {
        $this->language->load_json('payment/' . $this->payment_module_name);
        $this->data['action'] = 'index.php?route=payment/' . $this->payment_module_name . '/confirm';
        if (isset($this->session->data['error_cowpay'])) {
            $this->data['error_cowpay'] = $this->session->data['error_cowpay'];
        }

        $this->template = 'default/template/payment/' . $this->payment_module_name . '.expand';

        $this->render_ecwig();
    }


    /**
     * API function to confirm the payment
     */
    public function confirmPayment()
    {
        $this->initializer([
            'checkout/order'
        ]);
        unset($this->session->data['error_cowpay']);

        // Check Debug Mode
        $curlURL = '';
        if ($this->config->get($this->payment_module_name . '_debug_mode')) {
            $curlURL = 'https://staging.cowpay.me/api/v0/fawry/charge-request-cc';
        } else {
            $curlURL = 'https://cowpay.me/api/v0/fawry/charge-request-cc';
        }

        //Get Order Info
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currency = $order_info['currency_code'];
        $checkIfInArray = (in_array(strtoupper($currency), $this->allowed_currencies)) ? true : false;

        if (!$checkIfInArray) {
            $this->load->model('localisation/currency');
            $currency = $this->config->get('config_currency');
            $currencyValue = $this->model_localisation_currency->getCurrencyByCode($order_info['currency_code']);
        }

        // Prepare Order Items
        $chargeItems = array();
        foreach ($this->cart->getProducts() as $product) {
            $chargeItems[] = [
                "itemId" => $product['product_id'],
                "description" => $product['name'],
                "price" => ($checkIfInArray) ? $this->currency->format($product['price'], $currency, false, false) : $this->currency->convert($this->currency->format($product['price'], $currency, false, false), $currency, $this->allowed_currencies[0]),
                "quantity" => $product['quantity']
            ];
        }

        if ($this->cart->hasShipping())
        {
            $shipping     = $this->session->data['shipping_method']['cost'];
            $shipping_total = $this->currency->format($shipping, $currency, false, false);

            $chargeItems[] = [
                "itemId" => 0,
                "description" => "Shipping Cost",
                "price" => ($checkIfInArray) ? $shipping_total : $this->currency->convert($this->currency->format($shipping_total, $currency, false, false), $currency, $this->allowed_currencies[0]),
                "quantity" => 1
            ];
        }

        //Get Order Tota;
        $total = ($checkIfInArray) ? $this->currency->format($order_info['total'], $currency, false, false) : $this->currency->convert($this->currency->format($order_info['total'], $currency, false, false), $currency, $this->allowed_currencies[0]);

        // Generate Signature
        $signature = hash('sha256', $this->config->get($this->payment_module_name . '_merchant_code') . $order_info['order_id'] . $order_info['customer_id'] . 'CARD' . $total . $this->config->get($this->payment_module_name . '_merchant_hash_key'));

        // Prepare Request Data
        $curlData = [
            "merchant_code" => $this->config->get($this->payment_module_name . '_merchant_code'),
            "merchant_reference_id" => $order_info['order_id'],
            "customer_merchant_profile_id" => $order_info['customer_id'],
            "payment_method" => "CARD",
            "card_number" => $this->session->data['card_number'],
            "expiry_year" => $this->session->data['expiry_year'],
            "expiry_month" => $this->session->data['expiry_month'],
            "cvv" => $this->session->data['cvv'],
            "customer_name" => $order_info['payment_firstname'] . $order_info['payment_lastname'],
            "customer_mobile" => $order_info['payment_telephone'],
            "customer_email" => $order_info['email'],
            "amount" => $total,
            "currency_code" => "EGP", // only EGP for now
            "charge_items" => $chargeItems,
            "signature" => $signature,
            "save_card" => 0,
            "description" => "",

        ];

        //send request
        $result = $this->sendCurlRequest($curlURL, $curlData);
        if ($result['response']->success == true) {
            $this->confirm();
            $result['callback_url'] = $this->url->link('checkout/success', '', true);
            $this->response->setOutput(json_encode($result));
        } else {
            $this->response->setOutput(json_encode($result));
        }
    }

    /**
     * initialize IFrame
     */
    public function init()
    {
        $this->initializer([
            'checkout/order'
        ]);
        unset($this->session->data['error_cowpay']);

        $curlURL = '';
        if ($this->config->get($this->payment_module_name . '_debug_mode')) {
            $curlURL = ''; // not supported yet
        } else {
            $curlURL = 'https://cowpay.me/api/v0/iframes/cc/initiate-payment';
        }

        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currency = $this->config->get('config_currency');
        $checkIfInArray = (in_array(strtoupper($currency), $this->allowed_currencies)) ? true : false;

        if (!$checkIfInArray) {
            $this->load->model('localisation/currency');
            $currency = $this->config->get('config_currency');
            $currencyValue = $this->model_localisation_currency->getCurrencyByCode($order['currency_code']);
        }

        $chargeItems = array();
        foreach ($this->cart->getProducts() as $product) {
            $chargeItems[] = [
                "itemId" => $product['product_id'],
                "description" => $product['name'],
                "price" => $product['price'],
                "quantity" => $product['quantity']
            ];
        }

        if ($this->cart->hasShipping())
        {
            $shipping     = $this->session->data['shipping_method']['cost'];
            $shipping_total = $this->currency->format($shipping, $currency, false, false);

            $chargeItems[] = [
                "itemId" => 0,
                "description" => "Shipping Cost",
                "price" => ($checkIfInArray) ? $this->currency->format($shipping_total, $order['currency_code'], $order['currency_value'], false) : $this->currency->convert($this->currency->format($shipping_total, $currency, false, false), $currency, $this->allowed_currencies[0]),
                "quantity" => 1
            ];
        }

        // Generate Signature
        $signature = hash('sha256', $this->config->get($this->payment_module_name . '_merchant_code') . $order['order_id'] . $order['customer_id'] . 'CARD' . 'IFRAME' . number_format($order['total'], 2) . $this->config->get($this->payment_module_name . '_merchant_hash_key'));

        $curlData = [
            "merchant_code" => $this->config->get($this->payment_module_name . '_merchant_code'),
            "customer_name" => $order['payment_firstname'] . $order['payment_lastname'],
            "customer_mobile" => $order['payment_telephone'],
            "customer_email" => $order['email'],
            "customer_merchant_profile_id" => $order['customer_id'],
            "merchant_reference_id" => $order['order_id'],
            "amount" => number_format($order['total'], 2),
            "currency_code" => "EGP", // only EGP
            "charge_items" => $chargeItems,
            "signature" => $signature,
            "description" => ""
        ];

        $result = $this->sendCurlRequest($curlURL, $curlData);

        if ($result) {
            if ($this->config->get($this->payment_module_name . '_iframe_id')) {
                $result['iframe_id'] = $this->config->get($this->payment_module_name . '_iframe_id');
            }
            $this->response->setOutput(json_encode($result));
            return;
        } else {
            $this->response->setOutput(json_encode(['ERROR' => "CAN'T Access server now"]));
        }
    }

    /**
     *
     */
    function confirm()
    {
        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get($this->payment_module_name . '_complete_status_id'));
    }

    /**
     * @param string $contents
     */
    function log($type, $contents)
    {
        $log = new Log('cowpay.log');
        $log->write('[' . strtoupper($type) . '] ' . $contents);
    }


    /**
     */
    public function callback()
    {
        if ($this->request->get['status_code'] == 200) {
            $this->confirm();
            $this->redirect($this->url->link('checkout/success'));
        } else {
            $this->redirect($this->url->link('checkout/error'));
        }
    }

    /**
     * @param $_url
     * @param $data
     * @return array
     */
    private function sendCurlRequest($_url, $data)
    {
        $curl = curl_init($_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER => false,  // don't return headers
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
            CURLOPT_CUSTOMREQUEST => 'POST',

        );
        curl_setopt_array($curl, $options);


        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        $result = ['response' => $response,
            'data' => $data
        ];
        return $result;
    }

}
