<?php

class ControllerPaymentMoyasar extends Controller
{
    public function index()
    {
        $this->data = array_merge($this->data, $this->language->load_json('payment/moyasar'));

        $this->initializer([
            'moyasar' => 'payment/moyasar',
            'checkout/order'
        ]);

        $settings = $this->moyasar->getSettings();

        $this->data = array_merge($this->data, $this->language->load_json('payment/moyasar'));

        if (isset($settings['status']) == false) {
            return;
        }

        if ($settings['status'] == 0) {
            return;
        }

        $orderInfo = $this->order->getOrder($this->session->data['order_id']);

        if (!$orderInfo) {
            return false;
        }

        if (isset($this->request->post['moyasar'])) {
            $this->data['data'] = $this->request->post['moyasar'];
        }

        $total = round($orderInfo['total'], 2);

        $currency = $this->currency->getCode();

        if (!isset($settings['status']) || $settings['status'] != 1) {
            // TODO handle errors
            return false;
        }

        $paymentAddress = [
            'country_id' => $orderInfo['payment_country_id'],
            'zone_id' => $orderInfo['payment_zone_id'],
        ];

        /*if ($settings['geo_zone_id'] != 0 && $this->moyasar->getGeoZone($settings['geo_zone_id'], $address) == false) {
            // TODO handle errors
            return false;
        }*/

        $this->data['moyasar'] = $settings;
        $this->data['orderId'] = $orderInfo['order_id'];
        $this->data['amount'] = $total * 100;
        $this->data['orderName'] = implode(',', array_column($this->cart->getProducts(), 'name'));
        $this->data['currency'] = $currency;
        $this->data['moyasar_key'] = $settings['test_public_key'];
        if ($settings['environment'] == 1) {
            $this->data['moyasar_key'] = $settings['live_public_key'];
        }

        $months = [];
        for ($i = 1; $i < 13; $i++) {
            $months[$i] = date("m - F", strtotime(date("Y-{$i}-01")));
        }

        $years = [];
        $year = date("Y");
        for ($i = 0; $i < 11; $i++) {
            $years[] = $year + $i;
        }

        $this->data['months'] = $months;
        $this->data['years'] = $years;

        //$this->template = $this->checkTemplate('payment/moyasar.expand');

        //use direct submit form
        // $this->template = $this->checkTemplate('payment/moyasar-form.expand');
        
        $this->template = 'default/template/payment/moyasar-form.expand';
        
        $this->render_ecwig();
    }

    public function confirm()
    {
        $this->initializer([
            'moyasar' => 'payment/moyasar',
            'checkout/order'
        ]);

        $orderId = $this->session->data['order_id'];
        $settings = $this->moyasar->getSettings();

        if (isset($settings['status']) == false) {
            return;
        }

        if ($settings['status'] == 0) {
            return;
        }

        if (!isset($orderId)) {
            return;
        }

        //$orderInfo = $this->order->getOrder($this->session->data['order_id']);
        /*$secretKey = $settings['test_secret_key'];
        if ($settings['environment'] == 1) {
            $secretKey = $settings['live_secret_key'];
        }

        $data = $this->request->post['moyasar'];
        //$data['amount'] = 99*100;
        try{
            $this->moyasar->setApiKey($secretKey);
            $this->moyasar->setCallbackUrl($this->url->link('checkout/success'));
            $payment = $this->moyasar->createPayment($data);

            if ($payment->status === 'initiated') {
                    if (!$orderInfo['order_status_id']) {
                        $this->order->confirm($orderId, $settings['completed_status_code']);
                    } else {
                        $this->order->update($orderId, $settings['completed_status_code']);
                    }

                    $this->redirect($this->url->link('checkout/success'));
                }
        }catch(\Exception $exc) {
            if($exc->getResponse()){
                $response = json_decode($exc->getResponse()->getBody(), true);
                $this->session->data['error'] = $response['message'];
            }else{
                $this->session->data['error'] = 'API Error';
            }
            $this->redirect($this->url->link('checkout/success'));
        }*/

        if ($this->request->get['id']) {
            try{
                $secretKey = $settings['test_secret_key'];
                if ($settings['environment'] == 1) {
                    $secretKey = $settings['live_secret_key'];
                }
                $this->moyasar->setApiKey($secretKey);
                $retrieve = $this->moyasar->fetch($this->request->get['id']);
                
                if($retrieve->status == 'paid') {
                    $this->order->confirm($orderId, $settings['completed_status_code']);
                    $this->redirect($this->url->link('checkout/success'));
                }

            }catch(\Exception $exc) {
                //
            }
        }

        $this->order->confirm($orderId, $settings['denied_status_code']);
        $this->redirect($this->url->link('checkout/error'));
    }

    public function returnUrl()
    {
        $this->initializer([
            'moyasar' => 'payment/moyasar',
            'checkout/order'
        ]);

        $settings = $this->moyasar->getSettings();

        $session = $this->session->data['moyasar'];

        $returnIndicator = $this->request->get['resultIndicator'];

        $orderInfo = $this->order->getOrder($this->session->data['order_id']);

        if (!$orderInfo) {
            return;
        }

        $orderId = $orderInfo['order_id'];

        if ($session['successIndicator'] === $returnIndicator) {
            if (!$orderInfo['order_status_id']) {
                $this->model_checkout_order->confirm($orderId, $settings['completed_status_code']);
            } else {
                $this->model_checkout_order->update($orderId, $settings['completed_status_code']);
            }

            $this->redirect(
                $this->url->link('checkout/success')
            );
        } else {
            if (!$orderInfo['order_status_id']) {
                $this->model_checkout_order->confirm($orderId, $settings['denied_status_code']);
            } else {
                $this->model_checkout_order->update($orderId, $settings['denied_status_code']);
            }

            $this->redirect(
                $this->url->link('checkout/error')
            );
        }
    }
}
