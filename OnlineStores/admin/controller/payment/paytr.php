<?php

class ControllerPaymentPaytr extends Controller
{

    /**
     * errors bag
     */
    private $error = array();

    /**
     * payment name
     * 
     * will be used as a unified name for all related files calls
     */
    private $paymentName = 'paytr';

    /**
     * @var array $allowedCurrencies
     */
    private $allowedCurrencies = [
        "TL", "USD", "GBP", "TRY", "EUR", "RUB"
    ];


    /**
     * index function that listens for payment render call and saving data
     */
    public function index()
    {

        $this->language->load("payment/{$this->paymentName}");

        $this->load->model('setting/setting');
        $this->load->model('localisation/geo_zone');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/language');


        // set document tilte
        $this->document->setTitle($this->language->get('heading_title'));

        // set page breadcrumbs
        $this->setBreadcrumbs($this->data);
        
        // set form action url
        $this->data['action'] = $this->url->link("payment/{$this->paymentName}/savePaymentData", 'token=' . $this->session->data['token'], 'SSL');

        // set cancellation url
        $this->data['cancel'] = $this->url->link("payment/{$this->paymentName}", 'token=' . $this->session->data['token'], 'SSL');

        // form fields
        $fields = $this->getFormFields();

        $settings = $this->model_setting_setting->getSetting("{$this->paymentName}");
 
        foreach ($fields as $field) {
            $this->data["{$this->paymentName}_{$field}"] = $settings["{$this->paymentName}_{$field}"];
        }

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['currencies_supported'] = $this->allowedCurrencies;

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();


        // get payment settings
        $settings = $this->model_setting_setting->getSetting("{$this->paymentName}");

        // append sys languages
        $this->data['languages'] = $languages = $this->model_localisation_language->getLanguages();

        // get payment display field name for each language 
        foreach ($languages as $language) {
            $this->data["{$this->paymentName}_field_name_{$language['language_id']}"] = $settings["{$this->paymentName}_field_name_{$language['language_id']}"];
        }

        $this->template = "payment/{$this->paymentName}.expand";
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }


    /**
     * validate post data 
     * 
     * @return bool
     */
    private function validate()
    {

        $this->language->load("payment/{$this->paymentName}");

        if (!$this->user->hasPermission('modify', "payment/{$this->paymentName}")) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post["{$this->paymentName}_merchant_id"]) {
            $this->error["{$this->paymentName}_merchant_id"] = $this->language->get('error_merchant_id');
        }
        
        if (!$this->request->post["{$this->paymentName}_merchant_key"]) {
            $this->error["{$this->paymentName}_merchant_key"] = $this->language->get('error_merchant_key');
        }
        
        if (!$this->request->post["{$this->paymentName}_secret_key"]) {
            $this->error["{$this->paymentName}_secret_key"] = $this->language->get('error_secret_key');
        }

        return $this->error ? false : true;
    }


    /**
     * append breadcrumbs to data
     * 
     * @param &data array
     */
    private function setBreadcrumbs(&$data)
    {
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link("payment/{$this->paymentName}", 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

    }



    /**
     * handle payment save(POST) call
     */
    public function savePaymentData()
    {
        if (!$this->validate()) {
            $result['success'] = '0';
            $result['errors'] = $this->error;

            $this->response->setOutput(json_encode($result));

            return;
        }

        // save data into db
        $result = $this->savePaymentIntoDB($this->request->post);

        $this->response->setOutput(json_encode($result));

        return;
    }



    /**
     * @save data into DB
     * 
     * @return json
     */
    private function savePaymentIntoDB($data) 
    {

        $this->load->model('setting/setting');

        $result = [];
        $this->model_setting_setting->checkIfExtensionIsExists('payment', "{$this->paymentName}", true);

                    $this->tracking->updateGuideValue('PAYMENT');

        $this->model_setting_setting->insertUpdateSetting("{$this->paymentName}", $data);
        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

        $result['success'] = '1';

        return $result;
    }



    /**
     * get payment form fields
     */
    private function getFormFields()
    {
        return [
            'status', 'test_mode', 'merchant_id', 'merchant_key', 'secret_key',
            'currency', 'completed_order_status_id', 'geo_zone_id', 'default_currency','faild_order_status_id','refund_order_status'
        ];
    }
    public function refund() 
    {
        $this->language->load('payment/paytr');
        $merchant_id  = $this->config->get('paytr_merchant_id');
        $merchant_key = $this->config->get('paytr_merchant_key');
        $merchant_salt = $this->config->get('paytr_secret_key');   
        $order_id= $this->request->post['order_id'];

        $this->load->model('extension/payment_transaction');
        $paymentTransaction = $this->model_extension_payment_transaction->selectByOrderId($order_id);
        
        $return_amount=$paymentTransaction['amount'];
        $merchant_oid=$paymentTransaction['order_id'];
        $paytr_token = base64_encode(hash_hmac('sha256', $merchant_id . $merchant_oid . $return_amount . $merchant_salt, $merchant_key, true));

        $post_vals = array('merchant_id' => $merchant_id,
        'merchant_oid' => $merchant_oid,
        'return_amount' => $return_amount,
        'paytr_token' => $paytr_token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/iade");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);

        $result = @curl_exec($ch);

            if (curl_errno($ch)) {
                echo curl_error($ch);
                curl_close($ch);
                exit;
            }
            curl_close($ch);
        
            $result = json_decode($result, 1);

            if ($result[status] == 'success') {
                // DB operations
                 $transactionDetails=json_decode($paymentTransaction['details']);
                 $refundStatus = ['isRefunded' => 1,];
                 $details_merge = array_merge((array) $transactionDetails, (array) $refundStatus);
                 $this->db->query("UPDATE " . DB_PREFIX . "payment_transactions SET details =  '" . json_encode($details_merge) . "'WHERE order_id = '" . (int)$order_id . "'");
                $json['success'] = true;
                $json['response'] = $this->language->get('refund_success');
            } else {
                $json['success'] = false;
                $json['response'] = 'Refund Failed, '."Paytr Gateway Error: ". $result[err_no] . " - " . $result[err_msg];
            }
            
        $this->response->setOutput(json_encode($json));
        return;
    }
    
}
