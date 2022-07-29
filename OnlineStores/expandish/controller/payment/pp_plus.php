<?php

use ExpandCart\Foundation\Support\Factories\PayPal\Payment;
use ExpandCart\Foundation\Support\Factories\PayPal\Webhook;

class ControllerPaymentPPPlus extends Controller
{
    protected function index()
    {

        $this->data['error']['isError'] = false;

        $this->initializer([
            'settings' => 'payment/pp_plus',
            'checkout/order'
        ]);

        $orderInfo = $this->order->getOrder($this->session->data['order_id']);

        if (!$orderInfo) {
            return false;
        }

        $settings = $this->settings->getSettings();

        $this->data['test_mode'] = $settings['test_mode'];

        $transactionID = $orderid;
        $firstName = $orderInfo['payment_firstname'];
        $family = $orderInfo['payment_lastname'];
        $street = $orderInfo['payment_address_1'];
        $zip = $orderInfo['payment_postcode'];
        $city = $orderInfo['payment_city'];
        $state = $orderInfo['payment_zone'];
        $country = $orderInfo['payment_iso_code_2'];
        $email = $orderInfo['email'];
        $ip = $orderInfo['ip'];

        if (empty($state)) {
            $state = $city;
        }

        if (!$firstName  || !$street || !$city || !$state || !$country) {

            $this->data['error']['message'] = $this->language->get('missing_form_data');

            return $this->requestError();
        }

        $cartProducts = $this->cart->getProducts();

        if (!$cartProducts || count($cartProducts) < 1) {
            return false;
        }

        $currencies = array(
            'AUD',
            'CAD',
            'EUR',
            'GBP',
            'JPY',
            'USD',
            'NZD',
            'CHF',
            'HKD',
            'SGD',
            'SEK',
            'DKK',
            'PLN',
            'NOK',
            'HUF',
            'CZK',
            'ILS',
            'MXN',
            'MYR',
            'BRL',
            'PHP',
            'TWD',
            'THB',
            'TRY'
        );

        if (!in_array($orderInfo['currency_code'], $currencies)) {
            $order_info['currency_code'] = 'USD';
        }


        $taxes = $this->currency->format(array_sum($this->cart->getTaxes()), $orderInfo["currency_code"], false, false);

        $payment = new Payment;

        $payment->setContext([
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
        ]);

        $payment->setMode($settings['test_mode']);

        $payment->setCurrency($orderInfo["currency_code"]);

        $subTotal = $this->currency->format($this->cart->getSubTotal(), $orderInfo["currency_code"], false, false);

        $shippingCost = $this->currency->format($this->session->data['shipping_method']['cost'], $orderInfo["currency_code"], false, false);

        $orderInfo["total"] = $this->currency->format($orderInfo["total"], $orderInfo["currency_code"], false, false);

        $discountAmount = $subTotal + $shippingCost - $orderInfo['total'];

        $total = $this->currency->format(round($this->cart->getTotal(), 2) + $this->session->data['shipping_method']['cost'], $orderInfo["currency_code"], false, false);

        $discount = [
            'name' => 'Discount value',
            'quantity' => 1,
            'price' => -$discountAmount
        ];

        if ($discountAmount > 0) {
            $payment->addProduct($discount);

            $total = $total - $discountAmount;
            $subTotal = $subTotal - $discountAmount;
        }

        foreach ($cartProducts as $product) {
//            $subTotal[] = ($product['price'] * $product['quantity']);
            $payment->addProduct($product);
        }

        $payment->setTotal($total);

        $payment->setTax($taxes);
        $payment->setShipping($shippingCost);
        $payment->setSubTotal($subTotal);

        $payment->setInvoice('ORDER-' . $this->session->data['order_id']);

        $payment->setReturnUrl(
            $this->url->link('payment/pp_plus/success', '', 'SSL')
        )->setCancelUrl(
            $this->url->link('checkout/checkout', '', 'SSL')
        );

        $approvalLink = $payment->create();

        if (isset($approvalLink['status']) && $approvalLink['status'] === 'error') {

            $this->data['error']['isError'] = true;

            $this->data['error']['message'] = $approvalLink['message'];

            if (isset($this->request->get['__debug_x_']) && $this->request->get['__debug_x_'] == 'qaz123') {
                echo json_encode($approvalLink);
            }
        }

        $this->data['country'] = $orderInfo['payment_iso_code_2'];
        $this->data['mode'] = $payment->getMode();
        $this->data['approval_url'] = $approvalLink;

        // $this->template = $this->checkTemplate('payment/pp_plus.expand');
        
        $this->template = 'default/template/payment/pp_plus.expand';

        $this->render_ecwig();
    }

    public function requestError()
    {
        $this->data['error']['isError'] = true;

        // $this->template = $this->checkTemplate('payment/pp_plus.expand');
        
        $this->template = 'default/template/payment/pp_plus.expand';

        $this->render_ecwig();
    }

    public function success()
    {
        $this->initializer([
            'settings' => 'payment/pp_plus',
            'checkout/order'
        ]);

        $settings = $this->settings->getSettings();

        $payment = new Payment;

        $paymentId = $_GET['paymentId'];

        $payment->setContext([
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
        ]);

        $payment->setPaymentId($paymentId);

        $paymentInfo = $payment->get();

        $orderId = explode('-', $paymentInfo->transactions[0]->invoice_number)[1];

        $execute = $payment->setPayerId(
            $paymentInfo->payer->payer_info->payer_id
        )->execute();

        if (in_array($execute->payer->status, [
            'VERIFIED', 'UNVERIFIED'
        ])) {

            $orderInfo = $this->order->getOrder($orderId);

            $sale = $execute->transactions[0]->related_resources[0]->sale;

            switch($sale->state) {
                case 'completed':
                    $order_status_id = $settings['completed_status_id'];
                    break;
                case 'partially_refunded':
                    $order_status_id = $settings['partially_refunded_status_id'];
                    break;
                case 'pending':
                    $order_status_id = $settings['pending_status_id'];
                    break;
                case 'refunded':
                    $order_status_id = $settings['refunded_status_id'];
                    break;
                case 'denied':
                    $order_status_id = $settings['denied_status_id'];
                    break;
            }

            if (!$orderInfo['order_status_id']) {
                $this->model_checkout_order->confirm($orderId, $order_status_id);
            } else {
                $this->model_checkout_order->update($orderId, $order_status_id);
            }

            $this->redirect(
                $this->url->link('checkout/success')
            );
        } else {
            $this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));

            $this->redirect(
                $this->url->link('checkout/success')
            );
        }
    }

    public function callback()
    {
        $this->initializer([
            'settings' => 'payment/pp_plus',
            'checkout/order'
        ]);

        $settings = $this->settings->getSettings();

        if (!isset($settings['webhook'])) {
            echo 'invalid';exit;
        }

        $webhook = new Webhook;

        $headers = getallheaders();

        $headers = array_change_key_case($headers, CASE_UPPER);

        $requestBody = file_get_contents('php://input');

        $webhook->setContext([
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
        ]);

        $webhook->setVerifyHeaders($headers);
        $webhook->setVerifyRequestBody($requestBody);
        $webhook->setWebhookString($settings['webhook']);

        $output = $webhook->verify();

        if (!$output) {
            http_response_code(400);
            exit;
        }

        if (isset($output['error'])) {
            http_response_code(400);
            exit;
        }

        if ($output->verification_status === 'SUCCESS') {

            $requestBody = json_decode($requestBody, true);
            $paymentId = $requestBody['parent_payment'];

            $payment = new Payment;

            $payment->setContext([
                'client_id' => $settings['client_id'],
                'client_secret' => $settings['client_secret'],
            ]);

            $payment->setPaymentId($paymentId);

            $paymentInfo = $payment->get();

            $orderId = explode('-', $paymentInfo->transactions[0]->invoice_number)[1];

            $orderInfo = $this->order->getOrder($orderId);

            $sale = $paymentInfo->transactions[0]->related_resources[0]->sale;

            switch($sale->state) {
                case 'completed':
                    $order_status_id = $settings['completed_status_id'];
                    break;
                case 'partially_refunded':
                    $order_status_id = $settings['partially_refunded_status_id'];
                    break;
                case 'pending':
                    $order_status_id = $settings['pending_status_id'];
                    break;
                case 'refunded':
                    $order_status_id = $settings['refunded_status_id'];
                    break;
                case 'denied':
                    $order_status_id = $settings['denied_status_id'];
                    break;
            }

            if (!$orderInfo['order_status_id']) {
                $this->model_checkout_order->confirm($orderId, $order_status_id);
            } else {
                $this->model_checkout_order->update($orderId, $order_status_id);
            }

            http_response_code(200);

        } else {
            http_response_code(400);
        }
    }
}
