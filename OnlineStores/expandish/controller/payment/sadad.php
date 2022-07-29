<?php

class ControllerPaymentSadad extends Controller
{

    /**
     * @var string $stringpaymentName
     */
    private $paymentName = 'sadad';

    /**
     * @var array $errors
     */
    private $errors = array();


    /**
     * index function that appends needed template data then renders it
     *
     * @return template
     */
    public function index()
    {
        $this->language->load_json('payment/' . $this->paymentName);
        if (isset($this->session->data["error_{$this->paymentName}"])) {
            $this->data["error_{$this->paymentName}"] = $this->session->data["error_{$this->paymentName}"];
        }

        $this->data['msisdn'] = $this->language->get('msisdn');
        $this->data['birth_year'] = $this->language->get('birth_year');
        $this->data['send_otp'] = $this->language->get('send_otp');
        $this->data['otp'] = $this->language->get('otp');
        $this->data['confirm_order'] = $this->language->get('confirm_order');

        $this->template = 'default/template/payment/' . $this->paymentName . '.expand';

        $this->render_ecwig();
    }


    /**
     * prepares payment data and generating payment iframe token
     *
     * @return void
     */
    public function confirmPayment()
    {
        $payment_data['OTP'] = $this->request->post['otp'];
        $payment_data['TransactionId'] = (int)$this->request->post['transaction_id'];

        $url = 'https://pgw.almadar.ly/api/Pay';
        if ($this->config->get('sadad_test_mode') == 1) {
            $url = 'https://pgw-test.almadar.ly/api/Pay';
        }

        $response = $this->invokeCurlRequest($url, json_encode($payment_data));

        if (isset($response['statusCode']) && $response['statusCode'] === 0) {
            $result['success'] = true;
            $this->response->setOutput(json_encode($result));
        } else {
            $result['success'] = false;
            $result['message'] = $response['result'] ? $response['result'] : $response['message'];
            $this->response->setOutput(json_encode($result));
        }

    }

    /**
     * request payment otp
     *
     * @return array payment_data
     */
    public function requestPaymentOTP()
    {
        $this->initializer([
            'checkout/order'
        ]);

        // Get Order Info
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $payment_data = [];

        $payment_data['Msisdn'] = $this->request->post['msisdn'];
        $payment_data['BirthYear'] = $this->request->post['birth_year'];
        $payment_data['InvoiceNo'] = $order_info['order_id'] . '-' . time();
        $payment_data['Amount'] = (float)$order_info['total'];
        $payment_data['Category'] = (int)$this->config->get('sadad_category');
        
        $url = 'https://pgw.almadar.ly/api/Validate';
        if ($this->config->get('sadad_test_mode') == 1) {
            $url = 'https://pgw-test.almadar.ly/api/Validate';
        }

        $response = $this->invokeCurlRequest($url, json_encode($payment_data));

        if (isset($response['statusCode']) && $response['statusCode'] === 0) {

            $this->load->model('checkout/order');
            $this->model_checkout_order->confirm(
                $this->session->data['order_id'],
                $this->config->get($this->paymentName . '_completed_order_status_id')
            );

            $result['success'] = true;
            $result['transaction_id'] = $response['result']['transactionId'];

            $this->response->setOutput(json_encode($result));
        } else {
            $result['success'] = false;
            $result['message'] = $response['result'] ? $response['result'] : $response['message'];
            $this->response->setOutput(json_encode($result));
        }

    }

    /**
     * handle CURL request
     *
     * @return array curl response
     */
    private function invokeCurlRequest($url, $data)
    {
        $authorization = "Authorization: Bearer " . $this->config->get('sadad_token');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = @curl_exec($ch);

        if(curl_errno($ch)){
            die("connection error. err:".curl_error($ch));
        }

        curl_close($ch);
        return json_decode($result, true);
    }



    /**
     * redirectSuccess
     */
    public function redirectSuccess()
    {
        $this->redirect($this->url->link('checkout/success'));
    }

    
}
