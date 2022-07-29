<?php

class ControllerShippingWarehousesShipping extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('shipping/warehouses_shipping');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST')
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'warehouses_shipping', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->insertUpdateSetting('shipping', $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        // ======================= breadcrumbs ==============================

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_shipping'),
            'href' => $this->url->link('extension/shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('shipping/warehouses_shipping', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link('shipping/warehouses_shipping', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->data['cancel'] = $this->url->link('shipping/warehouses_shipping', '', 'SSL');

        // ======================= /breadcrumbs ==============================

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $warehouse_setting = $this->config->get('warehouses');
        $this->data['wrs_app_warning'] = false;
        if(isset($warehouse_setting))
        {
            if($warehouse_setting['status'] != 1){
                $this->data['wrs_app_url'] = $this->url->link('module/warehouses', '', 'SSL');
                $this->data['wrs_app_warning'] = true;
                $this->data['text_wrs_app_warning'] = $this->language->get('text_wrs_app_sts_warning');
                $this->data['text_wrs_app_url']     = $this->language->get('text_wrs_app_sts_url');
            }
        }else{
            $this->data['wrs_app_url'] = $this->url->link('marketplace/home', '', 'SSL');
            $this->data['wrs_app_warning'] = true;
            $this->data['text_wrs_app_warning'] = $this->language->get('text_wrs_app_ini_warning');
                $this->data['text_wrs_app_url']     = $this->language->get('text_wrs_ini_sts_url');
        }
        
        $this->data['current_currency_code'] = $this->currency->getCode();
        
        $this->load->model('localisation/geo_zone');

        $this->data['warehouses_shipping'] = $this->config->get('warehouses_shipping');

        $this->template = 'shipping/warehouses_shipping.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    
    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'shipping/warehouses_shipping') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }
}
