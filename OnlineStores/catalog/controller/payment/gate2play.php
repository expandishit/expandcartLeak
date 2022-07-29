<?php

class ControllerPaymentGate2play extends Controller {

    protected function index() {
        $this->language->load('payment/gate2play');

        $data['button_confirm'] = $this->language->get('button_confirm');
        //--------------------------------------
        $testMode = $this->config->get('gate2play_testmode');
        if ($testMode == 0) {
            $scriptURL = "https://oppwa.com/v1/paymentWidgets.js?checkoutId=";
            $url = "https://oppwa.com/v1/checkouts";
        } else {
            $scriptURL = "https://test.oppwa.com/v1/paymentWidgets.js?checkoutId=";
            $url = "https://test.oppwa.com/v1/checkouts";
        }

        $this->load->model('checkout/order');
        // Name : Ahmed abdelfattah
        // Date : 4 - 1 - 2017
        // Add Amount in gate2play
        // Amount
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $orderAmount = $order_info['total'];
        $orderid = $this->session->data['order_id'];


        $channel = $this->config->get('gate2play_channel');
        $mode = $this->config->get('gate2play_trans_mode');
        $login = $this->config->get('gate2play_loginid');
        $pwd = $this->config->get('gate2play_password');
        $type = $this->config->get('gate2play_trans_type');
        $currency = $this->config->get('gate2play_base_currency');
        $amount = number_format(
            round(
                $this->currency->convert(
                    $orderAmount, $this->currency->getCode(), $currency
                ), 2
            ), 2, '.', ''
        );
        $transactionID = $orderid;
        $firstName = $order_info['payment_firstname'];
        $family = $order_info['payment_lastname'];
        $street = $order_info['payment_address_1'];
        $zip = $order_info['payment_postcode'];
        $city = $order_info['payment_city'];
        $state = $order_info['payment_zone'];
        $country = $order_info['payment_iso_code_2'];
        $email = $order_info['email'];
        $ip = $order_info['ip'];

        if (empty($state)) {
            $state = $city;
        }

        if (!$firstName || !$family || !$street || !$city || !$state || !$country) {
            return $this->requestError();
        }

        $datacontent = "authentication.userId=$login" .
                "&authentication.password=$pwd" .
                "&authentication.entityId=$channel" .
                "&amount=$amount" .
                "&currency=$currency" .
                "&paymentType=$type" .
                "&merchantTransactionId=$transactionID" .
                "&customer.givenName=$firstName" .
                "&customer.surname=$family" .
                "&customer.email=$email" .
                "&customer.ip=$ip" .
                "&billing.city=$city" .
                "&billing.country=$country" .
                "&billing.street1=$street";

        if ($mode == 'CONNECTOR_TEST') {
            $datacontent .="&testMode=EXTERNAL";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datacontent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);


        $result = json_decode($responseData);
        $token = '';

        if (isset($result->id)) {
            $token = $result->id;
        }
    //print_r($result);exit;
        $payment_brands = implode(' ', $this->config->get('gate2play_brands'));
        //--------------------------------------
        $data['token'] = $token;
        $data['payment_brands'] = $payment_brands;
        $data['scriptURL'] = $scriptURL . $token;

        $data['formStyle'] = $this->config->get('gate2play_payment_style');

        $http = explode(':', $this->url->link('checkout/success'));
        $url = HTTP_SERVER;
        if ($http[0] == 'https') {
            $url = HTTPS_SERVER;
        }
        $data['postbackURL'] = $url . 'index.php?route=payment/gate2play/callback';

        // $data['postbackURL'] = $url . "success.php?name=gate2play";

        $this->data = $data;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/gate2play.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/gate2play.tpl';
        } else {
            $this->template = 'default/template/payment/gate2play.tpl';
        }

        $this->render();
    }

    private function requestError()
    {
        $configTemplate = $this->config->get('config_template') . '/template/payment/gate2play_request_error.tpl';
        $customTemplate = 'customtemplates/' . STORECODE . '/' .
            $this->config->get('config_template') . '/template/payment/gate2play_request_error.tpl';

        if (file_exists(DIR_TEMPLATE . $customTemplate)) {
            $this->template = $customTemplate;
        } else if (file_exists(DIR_TEMPLATE . $configTemplate)) {
            $this->template = $configTemplate;
        } else {
            $this->template = 'default/template/payment/gate2play_request_error.tpl';
        }

        $data['general_error'] = $this->language->get('error_in_payment_form');

        $this->data = $data;
        $this->children = array(
            'common/column_right',
            'common/footer',
            'common/column_left',
            'common/header'	);
        $this->response->setOutput($this->render(TRUE));
    }

    public function callback() {

        $this->load->model('checkout/order');
        if (isset($_GET['id'])) {
            $token =  $_GET["id"];

            $testMode = $this->config->get('gate2play_testmode');

            if ($testMode == 0) {
                $url = "https://oppwa.com/v1/checkouts/$token/payment";
            } else {
                $url = "https://test.oppwa.com/v1/checkouts/$token/payment";
            }
            $url .= "?authentication.userId=" . $this->config->get('gate2play_loginid');
            $url .= "&authentication.password=" . $this->config->get('gate2play_password');
            $url .= "&authentication.entityId=" . $this->config->get('gate2play_channel');


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $responseData = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);
            $resultJson = json_decode($responseData);
           // print_r($resultJson);exit;
            $success = 0;
            $failed_msg = '';
            $orderid = '';

            switch ($resultJson->result->code) {
                case (preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $resultJson->result->code) ? true : false) :
                case (preg_match('/^(000\.400\.0|000\.400\.100)/', $resultJson->result->code) ? true : false) :
                    $success = 1;
                    break;
                default :
                    $failed_msg = $resultJson->result->description;
            }
            $orderid = $resultJson->merchantTransactionId;


            $order_info = $this->model_checkout_order->getOrder($orderid);

            if ($order_info) {
                if ($success == 1) {
                    // Order is accepted.
                    $transUniqueID = $resultJson->id;
                    $this->model_checkout_order->confirm($orderid, $this->config->get('gate2play_order_status_id'), "Trans Unique ID:$transUniqueID\n", TRUE);
                    $this->success();
                } else {
                    // Order is not approved.
                    $this->model_checkout_order->confirm($orderid, $this->config->get('gate2play_order_status_failed_id'), '', TRUE);
                    $this->log->write("Hyperpay: Unauthorized Transaction. Transaction Failed. $failed_msg . Order Id: $orderid");
                    $this->session->data['gate2play_error'] = $failed_msg;
                    $this->redirect($this->url->link('payment/gate2play/fail', '', 'SSL'));
                }
                exit;
            } else {
                if ($this->config->get('gate2play_mailerrors') == 1) {
                    $message = "Hello,\n\nThis is your OpenCart site at " . $this->url->link('common/home') . ".\n\n";
                    $message .= "I've received this callback from Hyperpay, and I couldn't approve it.\n\n";
                    $message .= "This is the failed message that were sent from Hyperpay: $failed_msg.\n\n";

                    $message .= "\nYou can disable these notifications by changing the \"Enable error logging by email?\" setting within the Hyperpay merchant setup.";

                    $this->sendEmail($this->config->get('config_email'), 'Hyperpay callback failed!', $message);
                }

                //$this->model_checkout_order->confirm($orderid, $this->config->get('gate2play_order_status_failed_id'), '', TRUE);
                $this->model_checkout_order->confirm($orderid, $this->config->get('gate2play_order_status_failed_id'), '', TRUE);
                $this->log->write("Hyperpay: Unauthorized Transaction. Transaction Failed. $failed_msg. Order Id: $orderid");
                $this->redirect($this->url->link('payment/gate2play/fail', '', 'SSL'));
                print 'fff';
                exit;
            }
        }

        exit;
    }

    public function sendEmail($toEmail, $subject, $message) {
        $this->load->model('setting/store');

        $store_name = $this->config->get('config_name');

        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');
        $mail->setTo($toEmail);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($store_name);
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText($message);
        $mail->send();
    }

    protected function success() {
        $this->redirect($this->url->link('checkout/success', '', 'SSL'));
        exit;
    }

    public function fail() {
        $this->language->load('payment/gate2play');
        $data['heading_title'] = $this->language->get('heading_title');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/gate2play_fail.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/gate2play_fail.tpl';
        } else {
            $this->template = 'default/template/payment/gate2play_fail.tpl';
        }

        if (isset($this->session->data['gate2play_error'])) {
            $data['general_error'] = $this->session->data['gate2play_error'];
        } else {
            $data['general_error'] = $this->language->get('general_error');
            ;
        }
        $data['button_back'] = $this->language->get('button_back');
        $data['back'] = $this->url->link('common/home');

        $this->data = $data;
        $this->children = array(
            'common/column_right',
            'common/footer',
            'common/column_left',
            'common/header'	);
        $this->response->setOutput($this->render(TRUE));
    }

}

?>
