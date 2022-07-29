<?php
class ControllerPaymentFoloosi extends Controller
{
    public function index()
    {
        $this->initializer([
            'foloosi' => 'payment/foloosi',
            'checkout/order'
        ]);

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if (!$order_info) {
            return false;
        }

        $this->data['key_id'] = $this->config->get('payment_foloosipay_merchant_key');
        $this->data['currency_code'] = $order_info['currency_code'];
        $this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $this->data['merchant_order_id'] = $this->session->data['order_id'];
        $this->data['card_holder_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $this->data['email'] = $order_info['email'];
        $this->data['phone'] = $order_info['telephone'];
        $this->data['city'] = $order_info['payment_city'];
        $this->data['address'] = $order_info['payment_address_1'];
        $this->data['postcode'] = $order_info['payment_postcode'];
        $this->data['state'] = $order_info['payment_zone'];
        $this->data['comment'] = $order_info['comment'];
        $this->data['country'] = $order_info['payment_iso_code_3'];
        $this->data['name'] = $this->config->get("config_name");
        $this->data['lang'] = $this->session->data['language'];
        $this->data['return_url'] = $this->url->link('payment/foloosi/callback');

        // if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/default/template/payment/foloosipay.expand')) {
        //     $this->template = 'customtemplates/' . STORECODE . '/default/template/payment/foloosipay.expand';
        // } else {
        //     $this->template = 'default/template/payment/foloosipay.expand';
        // }
        $this->template = 'default/template/payment/foloosipay.expand';

        $this->render_ecwig();
    }


    public function callback() {
        if (isset($this->request->post['merchant_order_id'])) {
            $order_id = $this->request->post['merchant_order_id'];
        } else {
            $order_id = 0;
        }
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
        if ($order_info) {
            $data = array_merge($this->request->post,$this->request->get);
        }
        if (isset($this->request->request['foloosipay_payment_id']) and isset($this->request->request['merchant_order_id'])) {
            $foloosipay_payment_id = $this->request->request['foloosipay_payment_id'];
            $merchant_order_id = $this->request->request['merchant_order_id'];
            $key_id = $this->config->get('payment_foloosipay_merchant_key');
            $key_secret = $this->config->get('payment_foloosipay_secret_key');

            $order_info = $this->model_checkout_order->getOrder($merchant_order_id);
            $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;

            $success = false;
            $error = "";
            try {
                $url = 'https://foloosi.com/api/v1/api/transaction-detail/'.$foloosipay_payment_id;
                $ch = curl_init();
                $headers = [
                    'secret_key:'.$key_secret
                ];
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $response_array = json_decode($result, true);

                //Check success response
                if ($http_status === 200 and isset($response_array['error']) === false) {
                    $success = true;
                    $payment_status = $response_array['data']['status'];
                } else {
                    $success = false;
                    if (!empty($response_array['error']['code'])) {
                        $error = $response_array['error']['code'] . ":" . $response_array['error']['description'];
                    } else {
                        $error = "FOLOOSIPAY_ERROR:Invalid Response <br/>";
                    }
                }
                curl_close($ch);
            } catch (Exception $e) {
                $success = false;
                $error = "OPENCART_ERROR:Request to Foloosipay Failed";
            }

            if ($success === true and $payment_status == 'success') {
                if (!$order_info['order_status_id']) {
                    $this->model_checkout_order->confirm($merchant_order_id, $this->config->get('payment_foloosipay_order_status_id'), 'Payment Successful. Foloosipay Payment Id:' . $foloosipay_payment_id, true);
                } else {
                    $this->model_checkout_order->confirm($merchant_order_id, $this->config->get('payment_foloosipay_order_status_id'), 'Payment Successful. Foloosipay Payment Id:' . $foloosipay_payment_id, true);
                }
                $this->redirect($this->url->link('checkout/success'));

            } else {
                $this->model_checkout_order->confirm($this->request->request['merchant_order_id'], 10, $error . ' Payment Failed! Check Foloosipay dashboard for details of Payment Id:' . $foloosipay_payment_id);
                $this->redirect($this->url->link('checkout/error')) ;

            }
        } else {
            echo 'An error occured. Contact site administrator, please!';
        }
    }
}
?>
