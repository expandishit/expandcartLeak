<?php

class ControllerPaymentOttu extends Controller {


    protected function index() {

        $this->language->load_json('payment/ottu');

        $this->data['action'] = $this->url->link('payment/ottu/makepayment', '', 'SSL');

        $this->data['currencyError'] = $this->session->data['supportedCurrencyError'];

        $this->data['knet'] = $this->config->get('ottu_knet_getway_code');
        $this->data['creditcard'] = $this->config->get('ottu_cct_getway_code');;
        


        // $this->template = $this->checkTemplate('payment/ottu.expand');
        
        $this->template = 'default/template/payment/ottu.expand';

        $this->render_ecwig();
    }

    public function makepayment() { 
        $this->load->model('checkout/order');

        $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        //here we check if order customer data are exists before we make and payment request in case they are not saved in our database 
        //we redirect customer to checkout page to fill missing fileds
        
        if (!empty($orderInfo['email'])) {

            $amount = $orderInfo['total'];
            $orderCurrency = strtoupper($orderInfo['currency_code']);
            $gatewayCode = $this->request->post['choosenPaymentOption'];
            $orderNo = $orderInfo['order_id'];
            $customerEmail = $orderInfo['email'];
            $callBackUrl = $this->url->link('payment/ottu/callback', '', 'SSL');
            $disClosureUrl = $this->url->link('payment/ottu/disClosure', '', 'SSL');

            $connectArray = [
                'url' => $this->config->get('ottue_api_url'),
                'requestBody' => json_encode([
                    "amount" => round((float)$amount, 2),
                    "currency_code" => $orderCurrency,
                    "gateway_code" => $gatewayCode,
                    "order_no" => $orderNo . '_' . time(),
                    "customer_email" => $customerEmail,
                    "disclosure_url" => $disClosureUrl,
                    "redirect_url" => $callBackUrl
                ])
            ];
            
            if($gatewayCode==$this->config->get('ottu_knet_getway_code'))
            {
                $this->session->data['supportedCurrencyError'] = $this->{"kpaytCurrency"}($orderCurrency);
            }
            else
            {
                $this->session->data['supportedCurrencyError'] = $this->{"cctCurrency"}($orderCurrency);

            }


            $paymentResponse = json_decode($this->connect($connectArray), true);

            if (isset($paymentResponse['url'])) {
                $paymentUrl = $paymentResponse['url'];
                $this->response->redirect($paymentUrl);
                return;
            } else {
                $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
                return;
            }
        } else {
            $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
            return;
        }
    }

    public function callback() {
        $orderId = $this->request->get['order_no'];

        $data = $this->responseData($orderId);

        $gatewayResult = (!empty($data['result'])) ? $data['result'] : '';

        $redirectUrl = (!empty($gatewayResult) && $gatewayResult == 'success') ? $this->returnPaymentSuccess($data) : $this->returnPaymentFailed($data);

        $this->deleteResponseFile($orderId);

        $this->redirect($redirectUrl);
    }

    public function disClosure() {
        //this function is using to save response data to using them callback function 

        $responseDataJson = file_get_contents('php://input');
        $responseDataArray = json_decode($responseDataJson, true);
        $orderId = $responseDataArray['order_no'];
        if (!\Filesystem::isDirExists("temp/ottu")) {
            \Filesystem::createDir("temp/ottu");
            \Filesystem::setPath("temp/ottu")->changeMod('writable');
        }
        if($responseDataArray['gateway_account']==$this->config->get('ottu_knet_getway_code'))
        {
            $gatewayAccount="kpayt";
        }
        elseif($responseDataArray['gateway_account']==$this->config->get('ottu_cct_getway_code'))
        {
            $gatewayCode="cct";
        }
        else
        {
            $gatewayAccount="";
        }

        $gatewayResult = (!empty($responseDataArray['result'])) ? ucfirst($responseDataArray['result']) : '';

        $orderConfirmMethod = $gatewayAccount . $gatewayResult;

        (!empty($orderConfirmMethod) && method_exists($this, $orderConfirmMethod)) ? $this->{$orderConfirmMethod}($responseDataArray) : '';

        $ottuResponseFile = TEMP_DIR_PATH . "ottu/$orderId.json";
        $handle = fopen($ottuResponseFile, 'a');
        fwrite($handle, $responseDataJson);
        fclose($handle);

        ottuLog(['order_id' => $orderId ,
            'body'=>['response' => $responseDataJson , 'store'=>STORECODE]]);
    }

    private function responseData($orderId) {

        if (\Filesystem::isExists("temp/ottu/$orderId.json")) {
            $ottuResponse = TEMP_DIR_PATH . "ottu/$orderId.json";
            $dataJson = file_get_contents($ottuResponse);
            return json_decode($dataJson, true);
        }
    }

    private function deleteResponseFile($orderId) {

        if (\Filesystem::isExists("temp/ottu/$orderId.json")) {
            \Filesystem::deleteFile("temp/ottu/$orderId.json");
        }
    }

    private function returnPaymentSuccess($gatewayResponse) {

        unset($this->session->data['customPaymentDetails']['pg_success_msg']);
        unset($this->session->data['customPaymentDetails']['pg_fail_msg']);

        $this->load->model('checkout/order');

        $order_id = explode('_', $gatewayResponse['order_no'])[0];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $paymentData = $gatewayResponse['gateway_response'];

        if ($gatewayResponse['gateway_account'] == 'kpayt') {

            $this->language->load_json('payment/knet');

            $this->session->data['order_id'] = $order_id;

            if ($this->customer->isLogged()) {
                $successMessage = sprintf(
                        $this->language->get('text_customer'),
                        $order_id,
                        $paymentData['amt'] . ' ' . $order_info['currency_code'],
                        $paymentData['paymentid'],
                        $paymentData['tranid'],
                        $paymentData['trackid'],
                        $order_info['date_added'],
                        $this->url->link('account/account', '', 'SSL'),
                        $this->url->link('account/order', '', 'SSL'),
                        $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact')
                );
            } else {
                $successMessage = sprintf(
                        $this->language->get('text_guest'),
                        $order_id,
                        $paymentData['amt'] . ' ' . $order_info['currency_code'],
                        $paymentData['paymentid'],
                        $paymentData['tranid'],
                        $paymentData['trackid'],
                        $order_info['date_added'],
                        $this->url->link('information/contact')
                );
            }


            $this->session->data['customPaymentDetails']['pg_success_msg'] = $successMessage;

            $message .= 'Date: ' . $order_info['date_added'] . "\n";

            $message .= 'Order ID: ' . $order_id . "\n";

            $message .= 'Amount: ' . $paymentData['amt'] . ' ' . $order_info['currency_code'] . "\n";

            $this->sendNotificationEmail($order_info['email'], "Knet Tasnaction Notification", $message);
        }

        return $this->url->link('checkout/success', '', 'SSL');
    }

    private function returnPaymentFailed($gatewayResponse) {

        unset($this->session->data['customPaymentDetails']['pg_success_msg']);
        unset($this->session->data['customPaymentDetails']['pg_fail_msg']);

        $this->load->model('checkout/order');

        $order_id = explode('_', $gatewayResponse['order_no'])[0];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $paymentData = $gatewayResponse['gateway_response'];

        if ($gatewayResponse['gateway_account'] == 'kpayt') {

            $this->language->load_json('payment/knet');

            $this->session->data['order_id'] = $order_id;

            $failMessage = sprintf(
                    $this->language->get('failure_msg'),
                    $order_id,
                    $paymentData['amt'] . ' ' . $order_info['currency_code'],
                    $paymentData['paymentid'],
                    $paymentData['tranid'],
                    $paymentData['trackid'],
                    $order_info['date_added'],
                    $this->url->link('information/contact')
            );

            $this->session->data['customPaymentDetails']['pg_fail_msg'] = $failMessage;
        }

        return $this->url->link('checkout/error', '', 'SSL');
    }

    private function kpaytCurrency($customerCurrency) {
        $this->language->load_json('payment/ottu');
        $supporetdCurrency = ['KWD', 'INR'];
        $result = (!in_array($customerCurrency, $supporetdCurrency)) ? $this->language->get('kaynetSupportedCurrncy') : '';

        return $result;
    }

    private function cctCurrency($customerCurrency) {
        return '';
    }

    private function cctSuccess($gatewayResponse) {

        $this->load->model('checkout/order');
        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        $order_id = explode('_', $gatewayResponse['order_no'])[0];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $orderSuccess = $this->config->get('ottu_order_status_id');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $gatewayName = $gatewayResponse['gateway_name'];
        $referenceNumber = $gatewayResponse['reference_number'];
        $paymentName = $gatewayResponse['gateway_response']['score_card_scheme'];
        $transactionId = $gatewayResponse['gateway_response']['transaction_id'];

        $message = "Ottu – Gateway Name : $gatewayName \n";
        $message .= "Payment Name : $paymentName";
        $message .= "Reference Number : $referenceNumber";
        $message .= "Transaction Id : $transactionId";

        $this->model_checkout_order->confirm($order_id, $orderSuccess, $message);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => $gatewayResponse['reference_number'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('ottu')['id'],
            'payment_method' => 'OTTU',
            'status' => 'Success',
            'amount' => $gatewayResponse['amount'],
            'currency' => $gatewayResponse['currency_code'],
            'details' => '',
        ]);
    }

    private function cctError($gatewayResponse) {

        unset($this->session->data['customPaymentDetails']['pg_success_msg']);
        unset($this->session->data['customPaymentDetails']['pg_fail_msg']);

        $this->load->model('checkout/order');
        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        $order_id = explode('_', $gatewayResponse['order_no'])[0];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $orderFailed = $this->config->get('entry_order_status_failed');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $gatewayName = $gatewayResponse['gateway_name'];
        $paymentName = $gatewayResponse['gateway_response']['score_card_scheme'];

        $message = "Ottu – Gateway Name : $gatewayName \n";
        $message .= "Payment Name : $paymentName";


        $this->model_checkout_order->confirm($order_id, $orderFailed, $message);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => $gatewayResponse['reference_number'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('ottu')['id'],
            'payment_method' => 'OTTU',
            'status' => 'Failed',
            'amount' => $gatewayResponse['amount'],
            'currency' => $gatewayResponse['currency_code'],
            'details' => '',
        ]);
    }

    private function kpaytSuccess($gatewayResponse) {

        unset($this->session->data['customPaymentDetails']['pg_success_msg']);
        unset($this->session->data['customPaymentDetails']['pg_fail_msg']);

        $this->language->load_json('payment/knet');

        $this->load->model('checkout/order');

        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        $order_id = explode('_', $gatewayResponse['order_no'])[0];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $orderSuccess = $this->config->get('ottu_order_status_id');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $paymentData = $gatewayResponse['gateway_response'];

        $message = 'Ottu – Gateway Name: KNET' . "\n";
        $message .= 'PaymentID: ' . $paymentData['paymentid'] . "\n";
        $message .= 'TransactionID: ' . $paymentData['tranid'] . "\n";
        $message .= 'TrackID: ' . $paymentData['trackid'] . "\n";
        $message .= 'Auth ID: ' . $paymentData['result'] . "\n";
        $message .= 'Ref ID: ' . $paymentData['ref'] . "\n";

        $this->model_checkout_order->confirm($order_id, $orderSuccess, $message);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => $gatewayResponse['reference_number'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('ottu')['id'],
            'payment_method' => 'OTTU',
            'status' => 'Success',
            'amount' => $gatewayResponse['amount'],
            'currency' => $gatewayResponse['currency_code'],
            'details' => '',
        ]);
    }

    private function kpaytFailed($gatewayResponse) {

        unset($this->session->data['customPaymentDetails']['pg_success_msg']);
        unset($this->session->data['customPaymentDetails']['pg_fail_msg']);

        $this->language->load_json('payment/knet');

        $this->load->model('checkout/order');

        $this->initializer([
            'paymentTransaction' => 'extension/payment_transaction',
            'paymentMethod' => 'extension/payment_method',
        ]);

        $order_id = explode('_', $gatewayResponse['order_no'])[0];

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $orderFailed = $this->config->get('entry_order_status_failed');

        if (!$order_info) {
            $this->session->data['error'] = $this->language->get('error_no_order');

            $this->redirect($this->url->link('checkout/cart'));
        }

        $paymentData = $gatewayResponse['gateway_response'];

        $message = 'Ottu – Gateway Name: KNET' . "\n";
        $message .= 'PaymentID: ' . $paymentData['paymentid'] . "\n";
        $message .= 'TransactionID: ' . $paymentData['tranid'] . "\n";
        $message .= 'TrackID: ' . $paymentData['trackid'] . "\n";

        $this->model_checkout_order->confirm($order_id, $orderFailed, $message);

        $this->paymentTransaction->insert([
            'order_id' => $order_id,
            'transaction_id' => $gatewayResponse['reference_number'],
            'payment_gateway_id' => $this->paymentMethod->selectByCode('ottu')['id'],
            'payment_method' => 'OTTU',
            'status' => 'Failed',
            'amount' => $gatewayResponse['amount'],
            'currency' => $gatewayResponse['currency_code'],
            'details' => '',
        ]);
    }

    private function connect($connectArray) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $connectArray['url']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $connectArray['requestBody']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        return $responseData;
    }

    private function sendNotificationEmail($toEmail, $subject, $message) {
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
        $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
        );
        $mail->setTo($toEmail);
        $mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') : $this->config->get('config_email')));
        $mail->setSender($store_name);
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText($message);
        $mail->send();
    }

}

?>
