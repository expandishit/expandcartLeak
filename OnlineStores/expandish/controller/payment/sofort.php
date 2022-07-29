<?php

use Sofort\SofortLib\Sofortueberweisung;
use Sofort\SofortLib\Notification;
use Sofort\SofortLib\TransactionData;

ini_set('display_errors', 1);

class ControllerPaymentSofort extends Controller
{

    public function index()
    {
        $this->initializer([
            'sofort' => 'payment/sofort',
            'checkout/order'
        ]);

        $orderId = $this->session->data['order_id'];
        $country_code=$this->session->data['country_code'];

        if (!$orderId) {
            return false;
        }

        $orderInfo = $this->order->getOrder($orderId);

        if (!$orderInfo) {
            return false;
        }

        $settings = $this->sofort->getSettings();

        $currency = $settings['default_currency'];
        $total = $orderInfo['total'];

        $amount = round(
            $this->currency->convert(
                $total, $this->config->get('config_currency'), $currency
            ), 2
        );

        $reasons = implode(' - ', array_column($this->cart->getProducts(), 'name'));

        if (!$settings['status']) {
            return false;
        }

        $this->data['paymentUrl'] = $this->url->link('payment/sofort/sendrequest', '', 'SSL');
        $this->data['token'] = $this->sofort->generateToken($this->sofort->getSecret($orderId));
        $this->data['reasons'] = $reasons;
        $this->data['amount'] = $amount;
        $this->data['currency'] = $currency;
        $this->data['country_code'] = $country_code;
        $this->data['order_id'] = $orderId;

        // $this->template = $this->checkTemplate('payment/sofort.expand');
        
        $this->template = 'default/template/payment/sofort.expand';

        $this->render_ecwig();
    }

    public function sendrequest()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $this->redirect($this->url->link('error/not_found','','SSL'));
        }

        $this->initializer([
            'sofort' => 'payment/sofort',
            'checkout/order'
        ]);

        $settings = $this->sofort->getSettings();
        
        if (!$settings['status']) {
            $this->redirect($this->url->link('error/not_found','','SSL'));
        }

        $token = $this->request->post['token'];
        $orderId = $this->request->post['order_id'];
        $country_code = $this->request->post['country_code'];

        if ($this->sofort->validateToken($token, $this->sofort->getSecret($orderId)) == false) {
            $this->redirect($this->url->link('error/not_found','','SSL'));
        }

        $total = $this->request->post['amount'];
        $currency = $this->request->post['currency'];
        // Maximum number of character is 40 according to sofort api
        if(strlen($this->request->post['reasons']) >= 40){
            $reasons = substr($this->request->post['reasons'],0,39);
        }
        else{
            $reasons = $this->request->post['reasons'];
        }
        $orderInfo = $this->order->getOrder($orderId);

        $amount = round(
            $this->currency->convert(
                $orderInfo['total'], $this->config->get('config_currency'), $currency
            ), 2
        );
        
        if ($amount != $total) {
            $this->redirect($this->url->link('error/not_found','','SSL'));
        }
        
        $sofort = new Sofortueberweisung($settings['config_key']);

        $sofort->setAmount($total)
            ->setCurrencyCode($currency)
            ->setReason($reasons)
            ->setSenderCountryCode($country_code)
            ->setSuccessUrl($this->url->link('checkout/success'))
            ->setAbortUrl($this->url->link('checkout/checkout', '', 'SSL'))
            ->setNotificationUrl($this->url->link('payment/sofort/callback', '', 'SSL'))
        ->setUserVariable('order_id=' . $orderId . '&xToken=' . $token);

        $sofort->sendRequest();

        if ($sofort->isError()) {
            $error=$this->display_errors($sofort->getErrors());
            $this->redirect($this->url->link('error/not_found','msg='.$error,'SSL'));
        } else {
            $this->redirect($sofort->getPaymentUrl());
        }
    }

    public function display_errors($errors){
        $msg="";
        foreach($errors as $error){
            switch($error['code']){
                case "8054":
                    $msg=$error['message'];
                    break;
                case "8028":
                    $msg.="Sender country ID is locked in project settings. Please, be sure that the default country is the same in sofort project.";
                    break;
            }
        }
        return $msg;
    }
    public function callback()
    {
        $this->initializer([
            'sofort' => 'payment/sofort',
            'checkout/order'
        ]);

        $headers = getallheaders();

        $settings = $this->sofort->getSettings();

        if ($this->sofort->getRealIp() != $headers['X-Real-Ip']) {
            return false;
        }

        $orderInfo = $this->order->getOrder($orderId);

        $requestBody = file_get_contents('php://input');

        $notifications = (new Notification())->getNotification($requestBody);

        $transaction = new TransactionData($settings['config_key']);
        $transaction->addTransaction($notifications)->setApiVersion('2.0');

        $transaction->sendRequest();

        $status = $transaction->getStatus();
        $statusReason = $transaction->getStatusReason();

        parse_str($transaction->getUserVariable(), $userVariable);

        $orderId = $userVariable['order_id'];

        if ($this->sofort->validateToken($userVariable['xToken'], $this->sofort->getSecret($orderId)) === false) {
            return false;
        }

        $orderInfo = $this->order->getOrder($orderId);

        switch ($status) {
            case 'untraceable':
                $orderStatusId = $settings['untraceable_status_id'];
                break;
            case 'received':
                $orderStatusId = $settings['completed_status_id'];
                break;
            case 'loss':
            case 'pending':
                $orderStatusId = $settings['pending_status_id'];
                break;
            case 'refunded':
                $orderStatusId = (
                    $statusReason === 'refunded' ?
                        $settings['refunded_status_id'] :
                        $settings['partially_refunded_status_id']
                );
                break;
        }

        if (!$orderInfo['order_status_id']) {
            $this->order->confirm($orderId, $orderStatusId);
        } else {
            $this->order->update($orderId, $orderStatusId);
        }
    }

}
