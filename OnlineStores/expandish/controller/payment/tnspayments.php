<?php

class ControllerPaymentTNSPayments extends Controller
{
    public function index()
    {
        $this->initializer([
            'tns' => 'payment/tnspayments',
            'checkout/order'
        ]);

        $orderInfo = $this->order->getOrder($this->session->data['order_id']);

        if (!$orderInfo) {
            return false;
        }

        $total = $this->currency->format($orderInfo['total'], $orderInfo['currency_code'], 1.00000, false);
        //$total = round($this->cart->getTotal(), 2) + $this->session->data['shipping_method']['cost'];
        $total = number_format(round($total,1),2); // round the total with 2 percision ends with 0
        $settings = $this->tns->getSettings();

        if (!isset($settings['status']) || $settings['status'] != 1) {
            // TODO handle errors
            return false;
        }

        $paymentAddress = [
            'country_id' => $orderInfo['payment_country_id'],
            'zone_id' => $orderInfo['payment_zone_id'],
        ];

        $this->data['tns'] = $settings;
        $this->data['store_name'] = substr(trim($orderInfo['store_name']),0,40);
        $settings['orderId'] = $this->data['orderId'] = $orderInfo['order_id'];
        $settings['total'] = $this->data['total'] = $total;
        $settings['order_name'] = $this->data['order_name'] = implode(',', array_column($this->cart->getProducts(), 'name'));


        unset($this->session->data['tns']);

        if (!isset($this->session->data['tns'])) {
            $sessionId = $this->session->data['tns'] = $this->tns->generateSessionId($settings);
        }

        if (!$sessionId['session_id']) {
            return false;
        }

        $this->data['session_id'] = $sessionId['session_id'];

        // $this->template = $this->checkTemplate('payment/tnspayments.expand');
        
        $this->template = 'default/template/payment/tnspayments.expand';

        $this->render_ecwig();
    }

    public function returnUrl()
    {
        $this->initializer([
            'tns' => 'payment/tnspayments',
            'checkout/order'
        ]);

        $settings = $this->tns->getSettings();

        $session = $this->session->data['tns'];

        $returnIndicator = $this->request->get['resultIndicator'];

        $orderInfo = $this->order->getOrder($this->session->data['order_id']);

        if (!$orderInfo) {
            return;
        }

        $orderId = $orderInfo['order_id'];

        if ($session['successIndicator'] === $returnIndicator) {
            // if (!$orderInfo['order_status_id']) {
            $this->model_checkout_order->confirm($orderId, $settings['complete_status_id']);
            // } else {
            //     $this->model_checkout_order->update($orderId, $settings['complete_status_id']);
            // }

            $this->redirect(
                $this->url->link('checkout/success')
            );
        } else {
            //if (!$orderInfo['order_status_id']) {
            $this->model_checkout_order->confirm($orderId, $settings['denied_status_id']);
            // } else {
            //     $this->model_checkout_order->update($orderId, $settings['denied_status_id']);
            // }

            $this->redirect(
                $this->url->link('checkout/error')
            );
        }
    }
}
