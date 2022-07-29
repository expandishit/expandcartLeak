<?php

use ExpandCart\Foundation\Support\Factories\Payments\FastpayCash\FastpayCash;

class ControllerPaymentFastpayCash extends Controller
{
    protected function index()
    {

        $this->initializer([
            'settings' => 'payment/fastpaycash',
            'checkout/order',
        ]);

        $orderId = $this->session->data['order_id'];

        if (!$orderId) {
            return false;
        }

        $orderInfo = $this->order->getOrder($this->session->data['order_id']);

        if (!$orderInfo) {
            return false;
        }

        $settings = $this->settings->getSettings();

        $cartProducts = $this->cart->getProducts();

        if (!$cartProducts || count($cartProducts) < 1) {
            return false;
        }

        $fastpayCash = new FastpayCash;

        $session_currency = $this->session->data['currency'];
        $value = $this->cart->getTotal();

        if ( $session_currency !== 'IQD' )
        {
            $value = $this->currency->convert( $value, $session_currency, 'IQD' );
        }

        $fastpayCash->setMode($settings['test_mode'])
            ->setEndPoint('merchant/generate-payment-token')
            ->setMerchantNo($settings['merchant_no'])
            ->setStorePassword($settings['store_password'])
            ->setAmount( $value )
            ->setOrderId('ORD-' . $orderId)
            ->setSuccessUrl($this->url->link('payment/fastpaycash/success', '', 'SSL'))
            ->setFailUrl($this->url->link('payment/fastpaycash/fail', '', 'SSL'))
            ->setCancelUrl($this->url->link('checkout/checkout', '', 'SSL'))
        ;

        $response = $fastpayCash->payment();

        $response = json_decode($response, true);

        if ($response['code'] != '200') {

            $this->data['fastpaycash']['errors'] = $response['messages'];

            // $this->template = $this->checkTemplate('payment/fastpaycash.expand');
            $this->template = 'default/template/payment/fastpaycash.expand';

            $this->render_ecwig();
        }

        $this->data['links'] = [
            'action' => $fastpayCash->getBaseUrl() . 'merchant/payment'
        ];

        $this->data['token'] = $response['token'];

        // $this->template = $this->checkTemplate('payment/fastpaycash.expand');
        $this->template = 'default/template/payment/fastpaycash.expand';

        $this->render_ecwig();
    }

    public function success()
    {
        $this->initializer([
            'settings' => 'payment/fastpaycash',
            'checkout/order'
        ]);

        $settings = $this->settings->getSettings();

        $orderId = null; // TODO will be implemented after the test.

        if (!$orderId) {
            $this->redirect(
                $this->url->link('error/not_found')
            );
        }

        $orderInfo = $this->order->getOrder($orderId);

        if (!$orderInfo) {
            $this->redirect(
                $this->url->link('error/not_found')
            );
        }

        $this->order->confirm($orderId, $settings['pending_status_id']);

        $this->redirect(
            $this->url->link('checkout/success')
        );
    }

    public function fail()
    {
        $this->initializer([
            'settings' => 'payment/fastpaycash',
            'checkout/order'
        ]);

        $settings = $this->settings->getSettings();

        $orderId = null; // TODO will be implemented after the test.

        if (!$orderId) {
            $this->redirect(
                $this->url->link('error/not_found')
            );
        }

        $orderInfo = $this->order->getOrder($orderId);

        if (!$orderInfo) {
            $this->redirect(
                $this->url->link('error/not_found')
            );
        }

        $this->order->confirm($orderId, $settings['denied_status_id']);

        $this->redirect(
            $this->url->link('checkout/error')
        );
    }

    public function callback()
    {
        $this->initializer([
            'settings' => 'payment/fastpaycash',
            'checkout/order'
        ]);

        $settings = $this->settings->getSettings();

        if ($settings['status'] === false) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $requestBody = $this->request->post;

        $orderId = $requestBody['order_id'];

        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($requestBody)
        ) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        if ($this->settings->validateNotificationRequest($requestBody) == false) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $orderInfo = $this->order->getOrder($orderId);

        if (!$orderInfo) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        if ($this->settings->validateAmount($requestBody['bill_amount'], $orderInfo['total']) == false) {
            $this->redirect($this->url->link('error/not_found', '', 'SSL'));
        }

        $fastpayCash = new FastpayCash;

        $fastpayCash->setMode($settings['test_mode'])
            ->setEndPoint('merchant/payment/validation')
            ->setMerchantNo($settings['merchant_no'])
            ->setStorePassword($settings['store_password'])
            ->setOrderId('ORD-' . $orderId)
        ;

        $response = $fastpayCash->validate();

        $response = json_decode($response);

        switch ($response->data->status) {
            case 'Success':
                $orderStatusId = $settings['completed_status_id'];
                break;
            case 'Failed':
                $orderStatusId = $settings['denied_status_id'];
                break;
            case 'Cancelled':
                $orderStatusId = $settings['cancelled_status_id'];
                break;
        }

        $orderMessage = [];

        $orderMessage[] = 'Order Status : ' . $response->data->status;
        $orderMessage[] = 'Recieved at : ' . $response->data->received_at;
        $orderMessage[] = 'With transaction Id : ' . $response->data->transaction_id;
        $orderMessage[] = 'From Customer : ' . $response->data->customer_account_no;
        $orderMessage[] = 'Who paid : ' . $response->data->bill_amount . ' USD';

        $this->order->confirm($orderId, $orderStatusId, implode('<br />', $orderMessage));
    }
}
