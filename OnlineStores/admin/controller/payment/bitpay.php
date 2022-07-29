<?php
/**
 * @license https://github.com/bitpay/opencart-bitpay/blob/master/LICENSE MIT
 */

class ControllerPaymentBitpay extends Controller
{

    /**
     * @var array
     */
    private $error = array();

    /**
     * @var string
     */
    private $payment_module_name  = 'bitpay';

    /**
     * @return boolean
     */
    private function validate()
    {
        // $log = new Log('bitpay.log');
        // $log->write("Validation ran.");

        if (!$this->user->hasPermission('modify', 'payment/'.$this->payment_module_name))
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['bitpay_api_key']) && !preg_match('/[a-zA-Z0-9]{30,50}/', $this->request->post['bitpay_api_key']))
        {
            $this->error['bitpay_api_key'] = $this->language->get('error_api_key_valid');
        }

        if (!$this->request->post['bitpay_api_key'])
        {
            $this->error['bitpay_api_key'] = $this->language->get('error_api_key');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    /**
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

            $this->tracking->updateGuideValue('PAYMENT');
            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'bitpay', true);

            $this->model_setting_setting->insertUpdateSetting($this->payment_module_name, $this->request->post);
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            
            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }


        $this->document->setTitle($this->language->get('heading_title'));

        $notify_url = $this->url->link('payment/bitpay/callback');
        $notify_url = str_replace(HTTPS_SERVER, HTTP_CATALOG, $notify_url);
        $return_url = $this->url->link('payment/bitpay/success');
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
            'href'      => $this->url->link('payment/bitpay', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/bitpay', 'token=' . $this->session->data['token'], 'SSL');
        
        $this->data['cancel'] = $this->url->link('payment/bitpay', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $file = DIR_LOGS . 'bitpay.log';

        if (file_exists($file)) {
            $this->data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
        } else {
            $this->data['log'] = '';
        }

        
        $this->data[$this->payment_module_name.'_api_key'] = $this->config->get($this->payment_module_name.'_api_key');

        $this->data[$this->payment_module_name.'_api_server'] = $this->config->get($this->payment_module_name.'_api_server');

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

        $this->data['clear'] = $this->url->link('payment/bitpay/clear', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = 'payment/'.$this->payment_module_name.'.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render(TRUE));
    }

    public function clear() {
        $this->language->load('payment/bitpay');

        $file = DIR_LOGS . 'bitpay.log';

        $handle = fopen($file, 'w+');

        fclose($handle);

        $this->session->data['success'] = $this->language->get('text_success');

        $this->redirect($this->url->link('payment/bitpay', 'token=' . $this->session->data['token'], 'SSL'));
    }

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
            $this->payment_module_name.'_paid_status_id'      => (isset($statuses['Processing'])) ? $statuses['Processing'] : $order_status_default,
            $this->payment_module_name.'_confirmed_status_id' => (isset($statuses['Processed'])) ? $statuses['Processed'] : $order_status_default,
            $this->payment_module_name.'_complete_status_id'  => (isset($statuses['Complete'])) ? $statuses['Complete'] : $order_status_default
        ));
    }
}
