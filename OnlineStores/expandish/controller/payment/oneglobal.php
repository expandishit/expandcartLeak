<?php

class ControllerPaymentOneglobal extends Controller
{
    /* payit checkout payment host */
    private $host = "https://pay-it.mobi/globalpayit/pciglobal/WebForms/payitcheckoutservice3.aspx";

    public function index()
    {
        $data['action'] = $this->url->link('payment/oneglobal/confirm');

        if (isset($this->session->data['error_oneglobal'])) {
            $data['error_oneglobal'] = $this->session->data['error_oneglobal']['resp_msg'];
        }

        $data['error_oneglobal'] = $this->session->data['error_oneglobal'] ?? '';

        $this->data = $data;

        $this->template = 'default/template/payment/oneglobal.expand';

        $this->render_ecwig();
    }

    public function confirm()
    {
        $this->language->load_json('payment/oneglobal');

        $settings = $this->config->get('oneglobal');

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        // v14x Backwards Compatibility
        if (isset($order_info['currency_code'])) {
            $order_info['currency'] = $order_info['currency_code'];
        }
        if (isset($order_info['currency_value'])) {
            $order_info['value'] = $order_info['currency_value'];
        }

        unset($this->session->data['error_oneglobal']);

        // Check for supported currency, otherwise convert to default.
        $supported_currencies = array('EGP', 'SAR', 'USD', 'EUR', 'BHD', 'KWD', 'GBP', 'UGX', 'LBP', 'JOD');
        if (in_array($order_info['currency'], $supported_currencies)) {
            $currency = $order_info['currency'];
        } else {
            $currency = 'USD';
        }
        $amount = $this->currency->format($order_info['total'], $currency, FALSE, FALSE);

        $this->load->model('localisation/country');
        $rand12 = rand(100000000000, 999999999999); //12 digit random number
        $country = $this->model_localisation_country->getCountry($order_info['payment_country_id']);
        $settings['paymentchannel'] = "all";
        $paymentchannel = $settings['paymentchannel']; //"kwkfhcc"; /* kwknetonedc for knet */
        $isysid = $order_info['order_id'] . substr($rand12, strlen((string) $order_info['order_id'])); /* Must be a unique number (atleast 12 and atmost 14) for each request*/
        $amount = $amount;
        $description = 'test'; //urldecode($order_info['comment']);
        $description2 = "test";
        $tunnel = "isys";
        $currency = $paymentchannel == "KWKNETONEDC" ? 414 : $currency;
        $language = "en";
        $country = $country['iso_code_2'];
        $merchant_name = $settings['merchant_name'];
        $akey = $settings['akey'];
        $timestamp = time();
        $rnd = "";
        $original = $settings['original'];
        $msisdn = $order_info['payment_telephone'] ? str_replace("+","0",$order_info['payment_telephone'])  : str_replace("+","0",$order_info['telephone']) ; // phone

        $dataToComputeHash = $paymentchannel . "paymentchannel" . $isysid . "isysid" . $amount . "amount" . $timestamp . "timestamp" .  
        $description . "description" . $rnd . "rnd" . $original . "original" . $msisdn . "msisdn" . $currency . 
        "currency" . $tunnel . "tunnel";
        $decryptedOriginal = $settings['decryptedOriginal'];
        $hash = strtoupper(hash_hmac("sha256", $dataToComputeHash, $decryptedOriginal)); 

        /* the redirect url to receive payment notification */
        $merchantResponseUrl = $this->url->link('payment/oneglobal/callback');

        /* Construct the url with parameters */
        $json['url'] = $this->host . "?country=" . $country .
            "&paymentchannel=" . $paymentchannel .
            "&isysid=" . $isysid .
            "&amount=" . $amount .
            "&tunnel=" . $tunnel .
            "&description=" . $description .
            "&description2=" . $description2 .
            htmlentities("&currency=") . $currency .
            "&Responseurl=" . $merchantResponseUrl .
            "&merchant_name=" . $merchant_name .
            "&akey=" . $akey .
            "&hash=" . $hash .
            "&msisdn=" . $msisdn .
            "&original=" . urlencode($original) .
            htmlentities("&timestamp=") . $timestamp .
            "&rnd=" . $rnd;
        $json['url'] = str_replace('&amp;' , '&' , $json['url']);
        $this->response->setOutput(json_encode($json));
    }

    public function callback()
    {
        if (isset($this->request->get['result']) && $this->request->get['result'] == "CAPTURED") {
            $settings = $this->config->get('oneglobal');
            $this->load->model('checkout/order');
            $this->model_checkout_order->confirm($this->session->data['order_id'], $settings['order_status_id']);
            $this->redirect($this->url->link('checkout/success'));
        }
        $this->session->data['error_oneglobal'] = 'Payment failed';
        $this->redirect($this->url->link('checkout/checkout'));
    }
}
