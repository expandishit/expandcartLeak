<?php

class ControllerPaymentNbeBank extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('payment/nbe_bank');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        $this->load->model('payment/nbe_bank');

        $this->load->model('localisation/order_status');

        $this->load->model('localisation/geo_zone');

        $this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'nbe_bank', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('nbe_bank', $this->request->post);
            
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        // ======================== breadcrumbs ==========================

        $this->data['breadcrumbs'] = array();

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
            'href' => $this->url->link('payment/smeonline', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/nbe_bank', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        // ======================== /breadcrumbs ==========================

        $this->data['nbe_bank'] = $data['nbe_bank'] = $this->config->get('nbe_bank');

        $this->data['nbe_bank']['api_url'] = $this->config->get('nbe_bank_api_url');
        
        $this->data['nbe_bank']['username'] = $this->config->get('nbe_bank_username');

        $this->data['nbe_bank']['password'] = $this->config->get('nbe_bank_password');

        $this->data['nbe_bank']['merchant_number'] = $this->config->get('nbe_bank_merchant_number');

        $this->data['nbe_bank']['pending_status_id'] = $this->config->get('nbe_bank_pending_status_id');

        $this->data['nbe_bank']['failed_status_id'] = $this->config->get('nbe_bank_failed_status_id') ?: 10;
              
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['nbe_bank']['geo_zone_id'] = $this->config->get('nbe_bank_geo_zone_id');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        
        $this->data['nbe_bank']['total'] = $this->config->get('nbe_bank_total');

        $this->data['nbe_bank']['test_mode'] = $this->config->get('nbe_bank_test_mode');

        $this->data['nbe_bank']['enabled'] = $this->config->get('nbe_bank_enabled');

        $this->data['nbe_bank']['nbe_bank_status'] = $this->config->get('nbe_bank_status');

        $this->data['nbe_bank']['sort_order'] = $this->config->get('nbe_bank_sort_order');
        
        $this->data['nbe_bank_field_name'] = $this->config->get('nbe_bank_field_name');
        
        $this->data['meeza']['meeza_status'] = $this->config->get('meeza_status');
        
        $this->data['meeza']['api_url'] = $this->config->get('meeza_api_url');
        
        $this->data['meeza']['terminal_id'] = $this->config->get('meeza_terminal_id');
        
        $this->data['meeza']['merchant_id'] = $this->config->get('meeza_merchant_id');
        
        $this->data['meeza']['secret_key'] = $this->config->get('meeza_secret_key');

        $this->data['current_currency_code'] = $this->currency->getCode();

        $this->template = 'payment/nbe_bank.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }


    private function validate()
    {

        if ( ! $this->user->hasPermission('modify', 'payment/nbe_bank') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

//        if ( ! $this->request->post['nbe_bank_api_url'] )
//        {
//            $this->error['nbe_bank_api_url'] = $this->language->get('error_api_url');
//        }

        if ( ! $this->request->post['nbe_bank_username'] )
        {
            $this->error['nbe_bank_username'] = $this->language->get('error_username');
        }

        if ( ! $this->request->post['nbe_bank_password'] )
        {
            $this->error['nbe_bank_password'] = $this->language->get('error_password');
        }

        if ( ! $this->request->post['nbe_bank_merchant_number'] )
        {
            $this->error['nbe_bank_merchant_number'] = $this->language->get('error_merchant_number');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }
}
