<?php


class ControllerPaymentRajhiBank extends Controller
{

    /**
     * @var array
     */
    private $error = array();

    /**
     * @var string
     */
    private $payment_module_name  = 'rajhi_bank';

    /**
     * @var array
     */
    private $allowed_currencies_Codes = [
        'SAR' => 682
    ];


    /**
     * @return boolean
     */
    private function validate()
    {

        if (!$this->user->hasPermission('modify', 'payment/'.$this->payment_module_name))
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post[$this->payment_module_name.'_merchant_code']) && empty($this->request->post[$this->payment_module_name.'_merchant_code']))
        {
            $this->error[$this->payment_module_name.'_merchant_code'] = $this->language->get('error_merchant_code');
        }

        if (isset($this->request->post[$this->payment_module_name.'_merchant_hash_key']) && empty($this->request->post[$this->payment_module_name.'_merchant_hash_key']))
        {
            $this->error[$this->payment_module_name.'_merchant_hash_key'] = $this->language->get('error_merchant_hash_key');
        }

        if (isset($this->request->post[$this->payment_module_name.'_transportal_password']) && empty($this->request->post[$this->payment_module_name.'_transportal_password']))
        {
            $this->error[$this->payment_module_name.'_merchant_hash_key'] = $this->language->get('error_merchant_hash_key');
        }

        if (isset($this->request->post[$this->payment_module_name.'_resource_key']) && empty($this->request->post[$this->payment_module_name.'_resource_key']))
        {
            $this->error[$this->payment_module_name.'_resource_key'] = $this->language->get('error_resource_key');
        }

        if (isset($this->request->post[$this->payment_module_name.'_gatway_endpoint']) && empty($this->request->post[$this->payment_module_name.'_gatway_endpoint']))
        {
            $this->error[$this->payment_module_name.'_gatway_endpoint'] = $this->language->get('error_gatway_endpoint');
        }

        if (isset($this->request->post[$this->payment_module_name.'_support_endpoint']) && empty($this->request->post[$this->payment_module_name.'_support_endpoint']))
        {
            $this->error[$this->payment_module_name.'_support_endpoint'] = $this->language->get('error_support_endpoint');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }

    /**
     *
     */
    public function index()
    {
        $this->load->language('payment/'.$this->payment_module_name);
        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/geo_zone');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->model_setting_setting->insertUpdateSetting($this->payment_module_name, $this->request->post);
                        $this->tracking->updateGuideValue('PAYMENT');

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));
            return;

        }


        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs']   = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/rajhi_bank', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/rajhi_bank', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('payment/rajhi_bank', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $file = DIR_LOGS . 'rajhi_bank.log';

        if (file_exists($file)) {
            $this->data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
        } else {
            $this->data['log'] = '';
        }


        $this->data[$this->payment_module_name.'_transportal_id'] = $this->config->get($this->payment_module_name.'_transportal_id');

        $this->data[$this->payment_module_name.'_transportal_password'] = $this->config->get($this->payment_module_name.'_transportal_password');

        $this->data[$this->payment_module_name.'_resource_key'] = $this->config->get($this->payment_module_name.'_resource_key');

        $this->data[$this->payment_module_name.'_gatway_endpoint'] = $this->config->get($this->payment_module_name.'_gatway_endpoint');

        $this->data[$this->payment_module_name.'_support_endpoint'] = $this->config->get($this->payment_module_name.'_support_endpoint');

        $this->data[$this->payment_module_name.'_geo_zone'] = $this->config->get($this->payment_module_name.'_geo_zone');

        $this->data[$this->payment_module_name.'_status'] = $this->config->get($this->payment_module_name.'_status');

        $this->data[$this->payment_module_name.'_sort_order'] = $this->config->get($this->payment_module_name.'_sort_order');

        $this->data[$this->payment_module_name.'_paid_status_id'] = $this->config->get($this->payment_module_name.'_paid_status_id');

        $this->data[$this->payment_module_name.'_failed_status_id'] = $this->config->get($this->payment_module_name.'_failed_status_id');

        $this->data[$this->payment_module_name.'_complete_status_id'] = $this->config->get($this->payment_module_name.'_complete_status_id');

        $this->data[$this->payment_module_name.'_refund_status_id'] = $this->config->get($this->payment_module_name.'_refund_status_id');

        $this->data[$this->payment_module_name.'_debug_mode'] = $this->config->get($this->payment_module_name.'_debug_mode');

        $this->data['clear'] = $this->url->link('payment/rajhi_bank/clear', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = 'payment/'.$this->payment_module_name.'.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    /**
     *
     */
    public function refund() {
        $this->load->language('payment/'.$this->payment_module_name);

        $curlURL = '';
        if ($this->config->get($this->payment_module_name . '_debug_mode')) {
            $curlURL = ''; // not supported yet
        } else {
            $curlURL = $this->config->get($this->payment_module_name . '_support_endpoint');
        }

        if(empty($curlURL))
        {
            $json['success'] = false;
            $json['response'] = $this->language->get("error_support_endpoint");
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model('sale/order');
        $order = $this->model_sale_order->getOrder($this->request->get['order_id']);

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
            "action" => "2",
            "password" => $this->config->get($this->payment_module_name . '_transportal_password'),
            "id" => $this->config->get($this->payment_module_name . '_transportal_id'),
            "currencyCode" => $this->allowed_currencies_Codes[array_keys($this->allowed_currencies_Codes)[0]],
            "udf5" => "PaymentID",
            "transId" => $order["payment_trackId"],
        ]);

        // PREPARE CURL DATA AND ENCRYPT THE PARAMS
        $curlData = array([
            "id" => $this->config->get($this->payment_module_name . '_transportal_id'),
            "trandata" => $this->encryptAES(json_encode($params), $resource_key),
        ]);

        $result = $this->sendCurlRequest($curlURL, json_encode($curlData));
        if ($result['response'][0]->status = true && empty($result['response'][0]->errorText)) {
            $json['success'] = true;
            $json['response'] = json_decode($this->decryptData($result['response'][0]->trandata,$resource_key))[0]->result;
            $data['order_status_id'] = $this->config->get($this->payment_module_name.'_refund_status_id');
            $order = $this->model_sale_order->addOrderHistory($order['order_id'], $data);
        } else {
            $json['success'] = false;
            $json['response'] = $result['response'][0]->errorText;
        }

        $this->response->setOutput(json_encode($json));
        return;
    }

    /**
     *
     */
    public function inquery() {
        $this->load->language('payment/'.$this->payment_module_name);

        $curlURL = '';
        if ($this->config->get($this->payment_module_name . '_debug_mode')) {
            $curlURL = ''; // not supported yet
        } else {
            $curlURL = $this->config->get($this->payment_module_name . '_support_endpoint');
        }

        if(empty($curlURL))
        {
            $json['success'] = false;
            $json['response'] = $this->language->get("error_support_endpoint");
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model('sale/order');
        $order = $this->model_sale_order->getOrder($this->request->get['order_id']);

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
            "action" => "8",
            "password" => $this->config->get($this->payment_module_name . '_transportal_password'),
            "id" => $this->config->get($this->payment_module_name . '_transportal_id'),
            "currencyCode" => $this->allowed_currencies_Codes[array_keys($this->allowed_currencies_Codes)[0]],
            "udf5" => "PaymentID",
            "transId" => $order["payment_trackId"],
        ]);

        // PREPARE CURL DATA AND ENCRYPT THE PARAMS
        $curlData = array([
            "id" => $this->config->get($this->payment_module_name . '_transportal_id'),
            "trandata" => $this->encryptAES(json_encode($params), $resource_key),
        ]);

        $result = $this->sendCurlRequest($curlURL, json_encode($curlData));
        if ($result['response'][0]->status = true && empty($result['response'][0]->errorText)) {
            $json['success'] = true;
            $json['response'] = json_decode($this->decryptData($result['response'][0]->trandata,$resource_key))[0]->result;
        } else {
            $json['success'] = false;
            $json['response'] = $result['response'][0]->errorText;
        }

        $this->response->setOutput(json_encode($json));
        return;
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

    /**
     *
     */
    public function clear() {
        $this->language->load('payment/rajhi_bank');

        $file = DIR_LOGS . 'rajhi_bank.log';

        $handle = fopen($file, 'w+');

        fclose($handle);

        $this->session->data['success'] = $this->language->get('text_success');

        $this->redirect($this->url->link('payment/rajhi_bank', 'token=' . $this->session->data['token'], 'SSL'));
    }

    /**
     *
     */
    public function install() {
        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();
        $statuses = array();
        foreach ($order_statuses as $order_status) {
            $statuses[$order_status['name']] = $order_status['order_status_id'];
        }
        $order_status_default = $this->config->get('config_order_status_id');

        $this->model_setting_setting->insertUpdateSetting($this->payment_module_name, array(
            $this->payment_module_name.'_api_server'          => 'live',
            $this->payment_module_name.'_failed_status_id' => (isset($statuses['Processed'])) ? $statuses['Processed'] : $order_status_default,
            $this->payment_module_name.'_complete_status_id'  => (isset($statuses['Complete'])) ? $statuses['Complete'] : $order_status_default,
            $this->payment_module_name.'_refund_status_id'  => (isset($statuses['Chargeback'])) ? $statuses['Chargeback'] : $order_status_default

        ));
    }
}
