<?php

/**
 * Class ControllerPaymentRajhiBank
 */
class ControllerPaymentRajhiBank extends Controller
{
    /**
     * @var string
     */
    private $payment_module_name = 'rajhi_bank';

    /**
     * @var array
     */
    private $allowed_currencies_Codes = [
        'SAR' => 682
    ];

    /**
     *
     */
    protected function index()
    {
        $this->language->load_json('payment/' . $this->payment_module_name);
        $this->data['action'] = $this->url->link('payment/' . $this->payment_module_name . '/confirm');
        if (isset($this->session->data['error_rajhi_bank'])) {
            $this->data['error_rajhi_bank'] = $this->session->data['error_rajhi_bank'];
        }

        $this->template = 'default/template/payment/' . $this->payment_module_name . '.expand';

        $this->render_ecwig();
    }


    /**
     * Function confirmPayment
     * will validate the data then generate PaymentPage to redirect
     */
    public function confirmPayment()
    {
        $this->language->load_json('payment/' . $this->payment_module_name);
        $this->initializer([
            'checkout/order'
        ]);
        unset($this->session->data['error_rajhi_bank']);

        $curlURL = '';
        if ($this->config->get($this->payment_module_name . '_debug_mode')) {
            $curlURL = ''; // not supported yet
        } else {
            $curlURL = $this->config->get($this->payment_module_name . '_gatway_endpoint');
        }

        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $resource_key = $this->config->get($this->payment_module_name . '_resource_key');
        $currency = $order['currency_code'];

        $checkIfInArray = in_array(strtoupper($order['currency_code']), array_keys($this->allowed_currencies_Codes));
        if (!$checkIfInArray) {
            $this->load->model('localisation/currency');
            $currency = $this->config->get('config_currency');
        }

        // PREPATE PARAMS
        $params = array([
            "amt" => ($checkIfInArray) ? $this->currency->format($order['total'], $currency, false, false) : $this->currency->convert($this->currency->format($order['total'], $currency, false, false), $currency, array_keys($this->allowed_currencies_Codes)[0]),
            "action" => "1",
            "password" => $this->config->get($this->payment_module_name . '_transportal_password'),
            "id" => $this->config->get($this->payment_module_name . '_transportal_id'),
            "currencyCode" => $this->allowed_currencies_Codes[array_keys($this->allowed_currencies_Codes)[0]],
            "trackId" => $order['order_id'],
            "udf1" => STORECODE,
            "udf2" => "",
            "udf3" => "",
            "udf4" => "",
            "udf5" => "",
            "responseURL" => $this->url->link("payment/rajhi_bank/success"),
            "errorURL" => $this->url->link("payment/rajhi_bank/error"),
        ]);

        // PREPARE CURL DATA AND ENCRYPT THE PARAMS
        $curlData = array([
            "id" => $this->config->get($this->payment_module_name . '_transportal_id'),
            "trandata" => $this->encryptAES(json_encode($params), $resource_key),
            "responseURL" => $this->url->link("payment/rajhi_bank/success"),
            "errorURL" => $this->url->link("payment/rajhi_bank/error"),
        ]);

        // CURL RESULT
        $result = $this->sendCurlRequest($curlURL, json_encode($curlData));
        if ($result['response'][0]->status == 1 && empty($result['response'][0]->errorText)) {
            $json['success'] = true;
            [$json['response']["paymentId"], $json['response']["paymentPage"]] = explode(":", $result['response'][0]->result, 2);
            $json['redirectUrl'] = $json['response']["paymentPage"] . "?PaymentID=" . $json['response']["paymentId"];
        } elseif ($result['response'][0]->error == "IPAY0100293") {
            unset($this->session->data['order_id']);
            $json['success'] = false;
            $json['response'] = $this->language->get("text_try_again");
        } else {
            $json['success'] = false;
            if ( $result['response'] && $result['response'][0]->errorText){
                $json['response'] = $result['response'][0]->errorText;
            }else{
                $json['response'] = $this->language->get("general_error_rajhi_bank");
            }
        }
        $this->response->setOutput(json_encode($json));
    }

    /**
     * FUNCTION ENCRYPTION FROM RAJHI
     * @param $str "params"
     * @param $key "resource Key"
     * @return string
     */
    function encryptAES($str, $key)
    {
        $str = urlencode($str);
        $str = $this->pkcs5_pad($str);
        $ivlen = openssl_cipher_iv_length($cipher = "aes-256-cbc");
        $iv = "PGKEYENCDECIVSPC";

        $encrypted = openssl_encrypt($str, "aes-256-cbc", $key, OPENSSL_ZERO_PADDING, $iv);
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $encrypted = $this->byteArray2Hex($encrypted);

        return $encrypted;
    }


    /**
     * FUNCTION ENCRYPTION FROM RAJHI
     * @param $text
     * @return string
     */
    function pkcs5_pad($text)
    {
        $blocksize = 16;
        $pad = $blocksize - (strlen($text) % $blocksize);

        return $text . str_repeat(chr($pad), $pad);

    }

    /**
     * FUNCTION ENCRYPTION FROM RAJHI
     * @param $byteArray
     * @return string
     */
    function byteArray2Hex($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);

        return bin2hex($bin);
    }

    /**
     * FUNCTION DECRYPTION FROM RAJHI
     * @param $code
     * @param $key
     * @return bool|false|string
     */
    function decryptData($code, $key)
    {
        $code = $this->hex2ByteArray(trim($code));
        $code = $this->byteArray2String($code);

        $iv = "PGKEYENCDECIVSPC";
        $code = base64_encode($code);
        $decrypted = openssl_decrypt($code, 'AES-256-CBC', $key, OPENSSL_ZERO_PADDING, $iv);

        return urldecode($this->pkcs5_unpad($decrypted));

    }

    /**
     * FUNCTION DECRYPTION FROM RAJHI
     * @param $hexString
     * @return array|false
     */
    function hex2ByteArray($hexString)
    {
        $string = hex2bin($hexString);

        return unpack('C*', $string);
    }

    /**
     * FUNCTION DECRYPTION FROM RAJHI
     * @param $byteArray
     * @return string
     */
    function byteArray2String($byteArray)
    {
        $chars = array_map("chr", $byteArray);

        return join($chars);

    }

    /**
     * FUNCTION DECRYPTION FROM RAJHI
     * @param $text
     * @return bool|false|string
     */
    function pkcs5_unpad($text)
    {

        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }

        return substr($text, 0, -1 * $pad);

    }

    /**
     * callback success function
     */
    public function success()
    {

        $resource_key = $this->config->get($this->payment_module_name . '_resource_key');
        $result = $this->decryptData($this->request->post['trandata'], $resource_key);
        if (!empty($this->request->post["Error"] || !empty($this->request->post["ErrorText"]) || json_decode($result)[0]->result != "CAPTURED")) {
            $this->error();
        } else {
            $this->load->model('checkout/order');
            $data['payment_trackId'] = json_decode($result)[0]->paymentId;
            $order_id = json_decode($result)[0]->trackId;
            $this->session->data['order_id'] = $this->session->data['order_id'] ?? $order_id;
            $this->model_checkout_order->updateOrderFields($this->session->data['order_id'], $data);
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get($this->payment_module_name . '_complete_status_id'));
            $this->redirect($this->url->link('checkout/success'));
        }

    }

    /**
     * callback fail function
     */
    public function error()
    {
        $resource_key = $this->config->get($this->payment_module_name . '_resource_key');
        $result= $this->decryptData($this->request->post['trandata'], $resource_key);
        $order_id = ($this->session->data['order_id'])?$this->session->data['order_id']:json_decode($result)[0]->trackId;
        $this->load->model('checkout/order');
        $this->model_checkout_order->confirm($order_id, $this->config->get($this->payment_module_name . '_failed_status_id'), $result);
        $this->redirect($this->url->link('checkout/error'));
    }

    /**
     * @param string $contents
     */
    function log($type, $contents)
    {
        $log = new Log('rajhi_bank.log');
        $log->write('[' . strtoupper($type) . '] ' . $contents);
    }


    /**
     * @param $_url
     * @param $data
     * @return array
     */
    private function sendCurlRequest($_url, $data)
    {
        $curl = curl_init($_url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => array('content-type: application/json'),
            CURLOPT_FOLLOWLOCATION => false,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response,
            CURLOPT_SSL_VERIFYPEER => false
        );
        curl_setopt_array($curl, $options);

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, ($data));
        }
        $status = curl_getinfo($curl);
        $response = curl_exec($curl);

        $response = json_decode($response);
        curl_close($curl);
        $result = ['response' => $response,
            'data' => $data
        ];
        return $result;
    }

}
