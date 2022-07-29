<?php
class ModelPaymentTabby extends Model
{
    protected $curlClient;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->curlClient = $registry->get('curl_client');
    }

    const GATEWAY_NAME = 'tabby_installments';

    const BASE_API_URL = 'https://api.tabby.ai/api'; //Production & testing urls are the same.

    public function install()
    {
        $this->load->model('setting/extension');
        $this->model_setting_extension->install('payment', 'tabby_pay_later');
        $this->model_setting_extension->install('payment', 'tabby_installments');

        //Add Checkout Custom Field Date-Of-Birth (required in tabby request)
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $field_description = [];

        foreach ($languages as $key => $language) {
            if ($key == 'ar') {
                $field_description[$language['language_id']] = [
                    'field_title' => 'تاريخ ميلاد المشتري',
                    'field_error' => 'تاريخ الميلاد مطلوب',
                    'field_tooltip' => 'YYYY-MM-DD'
                ];
            } else {
                $field_description[$language['language_id']] = [
                    'field_title'   => 'Buyer Date Of Birth',
                    'field_error'   => 'Date Of Birth is required',
                    'field_tooltip' => 'YYYY-MM-DD'
                ];
            }
        }

        $this->load->model('module/quickcheckout_fields');
        $field_id = $this->model_module_quickcheckout_fields->addField([
            'field_type' => 'text',
            'section'    => 'payment_address',
            'field_description' => $field_description
        ]);

        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting('tabby', ['tabby_dob_field_id' => $field_id]);

        $quickcheckout_settings = $this->config->get('quickcheckout');
        $quickcheckout_settings['option']['guest']['payment_address']['fields']['custom_' . $field_id]['display'] = 1;
        $quickcheckout_settings['option']['guest']['payment_address']['fields']['custom_' . $field_id]['require'] = 0;
        $quickcheckout_settings['option']['register']['payment_address']['fields']['custom_' . $field_id]['display'] = 1;
        $quickcheckout_settings['option']['register']['payment_address']['fields']['custom_' . $field_id]['require'] = 0;
        $quickcheckout_settings['option']['logged']['payment_address']['fields']['custom_' . $field_id]['display'] = 1;
        $quickcheckout_settings['option']['logged']['payment_address']['fields']['custom_' . $field_id]['require'] = 0;
        $quickcheckout_settings['step']['payment_address']['fields']['custom_' . $field_id]['sort_order'] = '';

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('quickcheckout', ['quickcheckout' => $quickcheckout_settings]);
        
        // Insert Capture status if doesn't exist
        // $this->load->model('localisation/order_status');
        
        // if (($expandcart_id = $this->model_localisation_order_status->findOrderStatusByName('Capture')) <= 0) {
        //     $this->model_localisation_order_status->addOrderStatus(["order_status" => [
        //         1 => ['name' => 'Capture'],
        //         2 => ['name' => 'Capture']
        //     ]]);
        // }
          
        // $isSetCapture = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "order_status WHERE name = 'Capture';");
        
        // if (!$isSetCapture->rows[0]['total']) {
        //     //get max id of order status table
        //     $max_order_status_id = $this->db->query("SELECT MAX(`order_status_id`) AS max_order_status_id FROM  `" . DB_PREFIX . "order_status`");

        //     foreach ($max_order_status_id->rows as $id) {
        //         $new_order_status_id = $id['max_order_status_id'] + 1;
        //     }

        //     $lang_ids = $this->db->query("SELECT DISTINCT `language_id` FROM  `" . DB_PREFIX . "order_status`");

        //     foreach ($lang_ids->rows as $id) {
        //         $this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET `order_status_id` = '" . (int)$new_order_status_id . "', `language_id` = '" . (int)$id['language_id'] . "', `name` = 'Capture'");
        //     }
        // }
    }

    public function uninstall()
    {
        $this->load->model('module/quickcheckout_fields');
        $this->model_module_quickcheckout_fields->deleteField($this->config->get('tabby_dob_field_id'));

        $this->load->model('setting/extension');
        $this->model_setting_extension->uninstall('payment', 'tabby_pay_later');
        $this->model_setting_extension->uninstall('payment', 'tabby_installments');

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('tabby_pay_later');
        $this->model_setting_setting->deleteSetting('tabby_installments');

    }

    public function createWebhook($secret_key,$country_code){
        // define webhook url
        $hookUrl = HTTP_CATALOG.'index.php?route=payment/tabby_installments/notification';
        // define data array
        $data['url']     = $hookUrl;
        $data['is_test'] = true;

        //send request
        $results = $this->curlClient->request(
            'POST',
            self::BASE_API_URL . "/v1/webhooks",
            [],
            $data,
            [
                'Content-Type'    => 'application/json',
                'Authorization'   => "Bearer " . $secret_key,
                'cache-control'   => "no-cache",
                'X-Merchant-Code' => $country_code,
            ]
        );
        return $results;
    }

    public function updateOrderStatus(int $order_id, int $order_status)
    {
        $this->load->model('sale/order');

        $order = $this->model_sale_order->getOrder($order_id);
        if (!$order) return false;

        // if ($order['order_status_id'] == $order_status) return;

        $this->load->model('extension/payment_transaction');

        $paymentTransactionRecord = $this->model_extension_payment_transaction->selectByOrderId($order_id);

        if (!$paymentTransactionRecord) return false;

        $paymentTransaction = json_decode($paymentTransactionRecord['details'], true);
        
        $settings =  $this->config->get(self::GATEWAY_NAME);
        
        $isCapture = $settings['capture_status_id'] == $order_status;
        $isClose   = $settings['close_status_id'] == $order_status;
        $isRefunds = $settings['refund_status_id'] == $order_status;
        
        $tabbyAction = $isCapture ? 'captures' : ($isClose ? 'close' : ($isRefunds ? 'refunds' : null));
        
        if (!$tabbyAction) return;
        
        // retrieving the order
        $results = $this->curlClient->request(
            'GET',
            self::BASE_API_URL . "/v2/payments/" . $paymentTransaction['id'],
            [],
            [],
            [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . $settings['secret_key'],
                'cache-control' => "no-cache",
            ]
        );
        
        if (!$results->ok()) return;
        
        $tOrder = $results->getContent();

        switch ($tabbyAction) {
            case 'captures':
                if ($tOrder['status'] == 'CLOSED') return;
                $totalAmount = (float)$tOrder['amount'];
                $capturedAmount = (float)array_sum(array_column($tOrder['captures'], 'amount'));
                $pendingCaptureAmount = $totalAmount - $capturedAmount;
                
                if (!$pendingCaptureAmount) return;
                
                $data = [
                    'amount' => $pendingCaptureAmount,
                    'tax_amount' => $tOrder['order']['tax_amount'],
                    'shipping_amount' => $tOrder['order']['shipping_amount'],
                    'discount_amount' => $tOrder['order']['discount_amount'],
                    'created_at' => (new DateTime(date('Y-m-d H:i:s', time())))->format(DateTime::ATOM),
                    'items' => $tOrder['order']['items'],
                ];
                
                $results = $this->curlClient->request(
                    'POST',
                    self::BASE_API_URL . "/v1/payments/" . $paymentTransaction['id'] . "/" . $tabbyAction,
                    [],
                    $data,
                    [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer " . $settings['secret_key'],
                        'cache-control' => "no-cache",
                    ]
                );
                
                return $results->ok();
            case "refunds":
                $totalCapturedAmount = (float)array_sum(array_column($tOrder['captures'], 'amount'));
                $data = [
                    'id' => $tOrder['id'],
                    'amount' => (float)$totalCapturedAmount,
                    'created_at' => (new DateTime(date('Y-m-d H:i:s', time())))->format(DateTime::ATOM),
                    'items' => $tOrder['order']['items'],
                ];

                $results = $this->curlClient->request(
                    'POST',
                    self::BASE_API_URL . "/v1/payments/" . $paymentTransaction['id'] . "/" . $tabbyAction,
                    [],
                    $data,
                    [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer " . $settings['secret_key'],
                        'cache-control' => "no-cache",
                    ]
                );
                return $results->ok();
            case "close":
                $totalCapturedAmount = (float)array_sum(array_column($tOrder['captures'], 'amount'));
                
                $data = [];
                
                if ($totalCapturedAmount > 0) {
                    $data = [
                        'id' => $tOrder['id']
                    ];
                }

                if (!$totalCapturedAmount)  return false;

                $results = $this->curlClient->request(
                    'POST',
                    self::BASE_API_URL . "/v1/payments/" . $paymentTransaction['id'] . "/" . $tabbyAction,
                    [],
                    $data,
                    [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer " . $settings['secret_key'],
                        'cache-control' => "no-cache",
                    ]
                );
                return $results->ok();
        }
    }
}
