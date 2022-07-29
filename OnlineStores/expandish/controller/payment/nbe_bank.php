<?php

class ControllerPaymentNbeBank extends Controller {

    public function index()
    {

        $this->language->load_json('payment/nbe_bank');

        $this->data['visaMasterUrl'] = $this->config->get('nbe_bank_api_url');
        $this->data['testMode'] = $this->config->get('nbe_bank_test_mode');
        $apiVersionArray = explode('/', $this->config->get('nbe_bank_api_url'));

        $this->data['version'] = end($apiVersionArray);
        if ($this->config->get('meeza_status')) {
            $this->data['meezaActive'] = 1;
            $this->data['meezaUrl'] = $this->config->get('meeza_api_url');
        }

        // $this->template = $this->checkTemplate('payment/nbe_bank.expand');
        
        $this->template = 'default/template/payment/nbe_bank.expand';

        $this->render_ecwig();
    }

    public function getVisaMastarData() {

        $merchantUsername = $this->config->get('nbe_bank_username');

        $merchantId = $this->config->get('nbe_bank_merchant_number');

        $password = $this->config->get('nbe_bank_password');

        $testMode = ($this->config->get('nbe_bank_test_mode') == 1) ? 'test-' : '';

        $apiVersionArray = explode('/', $this->config->get('nbe_bank_api_url'));
        
        $apiVersion = end($apiVersionArray);//here we get version from endpoint url

        $this->load->model('checkout/order');

        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $orderProducts = implode(' || ', array_column($this->model_checkout_order->getOrderProducts($this->session->data['order_id']), 'name'));

        $uniqueOrderId = $orderInfo['order_id'] . '_' . time();

        $postData = [
            "apiOperation"          => "CREATE_CHECKOUT_SESSION",
            'apiPassword'           => $password,
            'apiUsername'           => 'merchant.' . $merchantId,
            'merchant'              => $merchantId,
            "interaction.operation" => "PURCHASE",
            "order.id"              => $uniqueOrderId,
            "order.amount"          => str_replace(',', '', number_format($orderInfo['total'], 2)),
            "order.currency"        => $orderInfo['currency_code'],
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://{$testMode}nbe.gateway.mastercard.com/api/nvp/version/" . $apiVersion);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,http_build_query($postData));
        
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($curl);

        $array = [];
        foreach (explode('&', $result) as $chunk) {
            $param = explode("=", $chunk);
        
            if ($param) {
                 $array[urldecode($param[0])] = urldecode($param[1]) ;
            }
        }

        curl_close($curl);

        $visaMasterConfig = [
            'sessionId' => $array['session.id'],
            'merchantNumber' => $this->config->get('nbe_bank_merchant_number'),
            'orderId' => $uniqueOrderId,
            'currencyCode' => $orderInfo['currency_code'],
            'storeName' => trim($orderInfo['store_name']),
            'amount' => str_replace(',', '', number_format($orderInfo['total'], 2)),
            'orderProducts' => $orderProducts
        ];
        $this->response->setOutput(json_encode($visaMasterConfig));
    }

    public function getMeezaData() {

        $this->load->model('checkout/order');

        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $merchantId = $this->config->get('meeza_merchant_id');

        $terminalId = $this->config->get('meeza_terminal_id');

        $mezzaData = date('ymdHis') . "&MerchantId={$merchantId}&TerminalId={$terminalId}";

        $hashData = hash_hmac('sha256', $mezzaData, $this->config->get('meeza_secret_key'));

        $meezaConfig = [
            'merchantId' => $merchantId,
            'terminalId' => $terminalId,
            'amount' => str_replace(',', '', number_format($orderInfo['total'], 2)),
            'secureHash' => $hashData
        ];

        $this->response->setOutput(json_encode($meezaConfig));
    }

    public function success() {

        $this->initializer([
            'checkoutOrder' => 'checkout/order',
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        $order_id = $this->session->data['order_id'];

        $order_info = $this->checkoutOrder->getOrder($order_id);

        $orderSuccess = $this->config->get('nbe_bank_pending_status_id');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $this->checkoutOrder->confirm($order_id, $orderSuccess);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => $this->request->get['resultIndicator'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('nbe_bank')['id'],
            'payment_method' => 'NBE Bank',
            'status' => 'Success',
            'amount' => $order_info['total'],
            'currency' => $order_info['currency_code'],
            'details' => '',
        ]);

        $this->redirect($this->url->link('checkout/success', '', true));
        return;
    }

    public function error() {

        $this->initializer([
            'checkoutOrder' => 'checkout/order',
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        $order_id = $this->session->data['order_id'];

        $order_info = $this->checkoutOrder->getOrder($order_id);

        $orderFailed = $this->config->get('nbe_bank_failed_status_id');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $this->checkoutOrder->confirm($order_id, $orderFailed);

        $this->redirect($this->url->link('checkout/error', '', true));
        return;
    }

}
