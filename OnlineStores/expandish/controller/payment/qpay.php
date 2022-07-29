<?php

class ControllerPaymentQpay extends Controller {

    private $testUrl = "https://demopaymentapi.qpayi.com/api/gateway/v1.0",
            $liveUrl = "https://qpayi.com:9100/api/gateway/v1.0";

    protected function index() {

        //gateway configuration data start

        $this->language->load_json('payment/qpay');

        $this->data['action'] = ($this->config->get('qpay_testmode') == 1) ? $this->testUrl : $this->liveUrl;

        $this->data['mode'] = ($this->config->get('qpay_testmode') == 1) ? "TEST" : "LIVE";

        $this->data['returnUrl'] = $this->url->link('payment/qpay/callback', '', 'SSL');

        $this->data['gatewayId'] = $this->config->get('entry_gid');

        $this->data['secretKey'] = $this->config->get('entry_secret_key');

        //gateway configuration data end
        //order data start

        $this->load->model('checkout/order');

        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $orderProducts = $this->model_checkout_order->getOrderProducts($this->session->data['order_id']);

        $this->data['referenceId'] = $orderInfo['order_id'].'_'.time();

        $this->data['name'] = $orderInfo['firstname'] . ' ' . $orderInfo['lastname'];

        $this->data['phone'] = $orderInfo['telephone'];

        $this->data['email'] = $orderInfo['email'];

        $this->data['country'] = (!empty($orderInfo['payment_iso_code_2'])) ? $orderInfo['payment_iso_code_2'] : $orderInfo['shipping_iso_code_2'];

        $this->data['city'] = (!empty($orderInfo['payment_zone'])) ? $orderInfo['payment_zone'] : $orderInfo['shipping_zone'];

        $this->data['address'] = (!empty($orderInfo['payment_address_1'])) ? $orderInfo['payment_address_1'] : $orderInfo['shipping_address_1'];

        $this->data['amount'] = number_format($orderInfo['total'], 2,'.', '');

        $this->data['currency'] = $orderInfo['currency_code'];

        $this->data['description'] = implode('-', array_column($orderProducts, 'product_id'));

        //order data end


        // $this->template = $this->checkTemplate('payment/qpay.expand');
        
        $this->template = 'default/template/payment/qpay.expand';
        
        $this->render_ecwig();
    }

    public function callback() {

        $callBackMethod = $this->request->get['status'];

        if (method_exists($this, $callBackMethod)) {
            $this->{$callBackMethod}();
        } else {
            $this->redirect($this->url->link('checkout/error', '', 'SSL'));
            return;
        }
    }

    public function success() {

        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
            'order' => 'checkout/order'
        ]);

        $order_id = (!empty($this->session->data['order_id'])) ? $this->session->data['order_id'] : 0;

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $orderSuccess = $this->config->get('qpay_order_status_id');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
            return;
        }


        $this->model_checkout_order->confirm($order_id, $orderSuccess, "reference_id: " . $this->request->get['referenceId']);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => $this->request->get['transactionId'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('qpay')['id'],
            'payment_method' => 'QPAY',
            'status' => 'Success',
            'amount' => $this->request->get['amount'],
            'currency' => $order_info['currency_code'],
            'details' => '',
        ]);

        $this->redirect($this->url->link('checkout/success', '', 'SSL'));
        return;
    }

    public function error() {
        
        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
            'order' => 'checkout/order'
        ]);

        $order_id = (!empty($this->session->data['order_id'])) ? $this->session->data['order_id'] : 0;

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $orderFailed = $this->config->get('entry_order_status_failed');

        $this->model_checkout_order->confirm($order_id, $orderFailed, "reason :" . $this->request->get['reason']);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => 0,
            'payment_gateway_id' => $this->paymentMethod->selectByCode('qpay')['id'],
            'payment_method' => 'QPAY',
            'status' => 'Failed',
            'amount' => $this->request->get['amount'],
            'currency' => $order_info['currency_code'],
            'details' => '',
        ]);

        $this->redirect($this->url->link('checkout/error', '', 'SSL'));
        return;
    }
    
     public function cancel() {
         $this->error();
     }
}

?>
