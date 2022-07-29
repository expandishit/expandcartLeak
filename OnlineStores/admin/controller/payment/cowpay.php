<?php


class ControllerPaymentCowpay extends Controller
{

    /**
     * @var array
     */
    private $error = array();

    /**
     * @var string
     */
    private $payment_module_name  = 'cowpay';

    /**
     * @return boolean
     */
    private function validate()
    {
        // $log = new Log('cowpay.log');
        // $log->write("Validation ran.");

        if (!$this->user->hasPermission('modify', 'payment/'.$this->payment_module_name))
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post[$this->payment_module_name.'_merchant_code']) && empty($this->request->post[$this->payment_module_name.'_merchant_code']))
        {
            $this->error[$this->payment_module_name.'_merchant_code'] = $this->language->get('erorr_merchant_code');
        }

        if (isset($this->request->post[$this->payment_module_name.'_merchant_hash_key']) && empty($this->request->post[$this->payment_module_name.'_merchant_hash_key']))
        {
            $this->error[$this->payment_module_name.'_merchant_hash_key'] = $this->language->get('erorr_merchant_hash_key');
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

        $notify_url = $this->url->link('payment/cowpay/callback');
        $notify_url = str_replace(HTTPS_SERVER, HTTP_CATALOG, $notify_url);
        $return_url = $this->url->link('payment/cowpay/success');
        $return_url = str_replace(HTTPS_SERVER, HTTP_CATALOG, $return_url);

        $this->data['notify_default']          = $notify_url;
        $this->data['return_default']          = $return_url;

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
            'href'      => $this->url->link('payment/cowpay', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/cowpay', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('payment/cowpay', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $file = DIR_LOGS . 'cowpay.log';

        if (file_exists($file)) {
            $this->data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
        } else {
            $this->data['log'] = '';
        }


        $this->data[$this->payment_module_name.'_merchant_code'] = $this->config->get($this->payment_module_name.'_merchant_code');

        $this->data[$this->payment_module_name.'_merchant_hash_key'] = $this->config->get($this->payment_module_name.'_merchant_hash_key');

        $this->data[$this->payment_module_name.'_iframe_id'] = $this->config->get($this->payment_module_name.'_iframe_id');

        $this->data[$this->payment_module_name.'_risk_speed'] = $this->config->get($this->payment_module_name.'_risk_speed');

        $this->data[$this->payment_module_name.'_geo_zone'] = $this->config->get($this->payment_module_name.'_geo_zone');

        $this->data[$this->payment_module_name.'_status'] = $this->config->get($this->payment_module_name.'_status');

        $this->data[$this->payment_module_name.'_sort_order'] = $this->config->get($this->payment_module_name.'_sort_order');

        $this->data[$this->payment_module_name.'_paid_status_id'] = $this->config->get($this->payment_module_name.'_paid_status_id');

        $this->data[$this->payment_module_name.'_confirmed_status_id'] = $this->config->get($this->payment_module_name.'_confirmed_status_id');

        $this->data[$this->payment_module_name.'_complete_status_id'] = $this->config->get($this->payment_module_name.'_complete_status_id');

        $this->data[$this->payment_module_name.'_notify_url'] = $notify_url;

        $this->data[$this->payment_module_name.'_return_url'] = $return_url;

        $this->data[$this->payment_module_name.'_debug_mode'] = $this->config->get($this->payment_module_name.'_debug_mode');

        $this->data['clear'] = $this->url->link('payment/cowpay/clear', 'token=' . $this->session->data['token'], 'SSL');

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
    public function clear() {
        $this->language->load('payment/cowpay');

        $file = DIR_LOGS . 'cowpay.log';

        $handle = fopen($file, 'w+');

        fclose($handle);

        $this->session->data['success'] = $this->language->get('text_success');

        $this->redirect($this->url->link('payment/cowpay', 'token=' . $this->session->data['token'], 'SSL'));
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
            $this->payment_module_name.'_complete_status_id'  => (isset($statuses['Complete'])) ? $statuses['Complete'] : $order_status_default
        ));
    }
}
