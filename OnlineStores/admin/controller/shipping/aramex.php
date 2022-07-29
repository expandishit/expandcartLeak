<?php

class ControllerShippingAramex extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('shipping/aramex');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('shipping/aramex');
        $this->load->model('localisation/weight_class');
        $this->load->model('localisation/tax_class');
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

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'aramex', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->editSetting('aramex', $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        // ======================== breadcrumbs =========================

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
            'href' => $this->url->link('shipping/aramex', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'action' => $this->url->link('shipping/aramex', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->data['cancel'] = $this->url->link('shipping/aramex', '', 'SSL');

        $this->data['links'] = [
            'action' => $this->url->link('shipping/aramex', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        // ======================== breadcrumbs =========================

        $this->data['aramex_email'] = $this->config->get('aramex_email');
        
        $this->data['aramex_password'] = $this->config->get('aramex_password');

        $this->data['aramex_account_pin'] = $this->config->get('aramex_account_pin');

        $this->data['aramex_account_number'] = $this->config->get('aramex_account_number');

        $this->data['aramex_account_entity'] = $this->config->get('aramex_account_entity');

        $this->data['aramex_account_country_code'] = $this->config->get('aramex_account_country_code');


        $this->data['additional_account'] = $this->config->get('additional_account');
        $this->data['last_key'] = key(array_slice($this->config->get('additional_account'), -1));

        $this->data['aramex_test'] = $this->config->get('aramex_test');

        $this->data['aramex_report_id'] = '9729';

        $this->data['aramex_allowed_domestic_methods'] = $this->config->get('aramex_allowed_domestic_methods');

        $this->data['allowed_domestic_methods'] = $this->model_shipping_aramex->domesticmethods();

        $this->data['aramex_allowed_domestic_additional_services'] = $this->config->get('aramex_allowed_domestic_additional_services');

        $adas = $this->model_shipping_aramex->domesticAdditionalServices();

        $this->data['allowed_domestic_additional_services'] = $adas;

        unset($adas);

        $this->data['aramex_allowed_international_methods'] = $this->config->get('aramex_allowed_international_methods');

        $this->data['allowed_international_methods'] = $this->model_shipping_aramex->internationalmethods();

        $this->data['aramex_allowed_international_additional_services'] = $this->config->get('aramex_allowed_international_additional_services');

        $aias = $this->model_shipping_aramex->internationalAdditionalServices();

        $this->data['allowed_international_additional_services'] = $aias;

        unset($aias);

        $this->data['aramex_weight_class_id'] = $this->config->get('aramex_weight_class_id');

        $this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
        
        $this->data['aramex_tax_class_id'] = $this->config->get('aramex_tax_class_id');

        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->data['aramex_geo_zone_id'] = $this->config->get('aramex_geo_zone_id');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->data['aramex_status'] = $this->config->get('aramex_status');

        $this->data['aramex_sort_order'] = $this->config->get('aramex_sort_order');

        $this->data['aramex_default_allowed_domestic_methods'] = $this->config->get('aramex_default_allowed_domestic_methods');

        $this->data['aramex_default_allowed_domestic_additional_services'] = $this->config->get('aramex_default_allowed_domestic_additional_services');
        
        $this->data['aramex_default_allowed_international_additional_services'] = $this->config->get('aramex_default_allowed_international_additional_services');

        $this->data['aramex_shipper_name'] = $this->config->get('aramex_shipper_name');

        $this->data['aramex_shipper_email'] = $this->config->get('aramex_shipper_email');

        $this->data['aramex_shipper_company'] = $this->config->get('aramex_shipper_company');

        $this->data['aramex_shipper_address'] = $this->config->get('aramex_shipper_address');

        $this->data['aramex_shipper_country_code'] = $this->config->get('aramex_shipper_country_code');

        $this->data['aramex_shipper_city'] = $this->config->get('aramex_shipper_city');

        $this->data['aramex_shipper_postal_code'] = $this->config->get('aramex_shipper_postal_code');

        $this->data['aramex_shipper_state'] = $this->config->get('aramex_shipper_state');

        $this->data['aramex_shipper_phone'] = $this->config->get('aramex_shipper_phone');

        $this->data['aramex_auto_create_shipment'] = $this->config->get('aramex_auto_create_shipment');

        $this->data['aramex_live_rate_calculation'] = $this->config->get('aramex_live_rate_calculation');

        $this->data['aramex_default_rate'] = $this->config->get('aramex_default_rate');

        $this->data['aramex_custom_pieces'] = $this->config->get('aramex_custom_pieces');
        $this->data['aramex_cities_table'] = $this->config->get('aramex_cities_table')?:0;
        $this->template = 'shipping/aramex.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    
    private function validate()
    {

        if ( ! $this->user->hasPermission('modify', 'shipping/aramex') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if  ( ! $this->request->post['aramex_email'] )
        {
            $this->error['aramex_email'] = $this->language->get('error_email');
        }

        if ( ! $this->request->post['aramex_password'] )
        {
            $this->error['aramex_password'] = $this->language->get('error_password');
        }

        if ( ! $this->request->post['aramex_account_pin'] )
        {
            $this->error['aramex_account_pin'] = $this->language->get('error_account_pin');
        }

        if ( ! $this->request->post['aramex_account_number'] )
        {
            $this->error['aramex_account_number'] = $this->language->get('error_account_number');
        }

        if ( ! $this->request->post['aramex_account_country_code'] )
        {
            $this->error['aramex_account_country_code'] = $this->language->get('error_postcode');
        }

        if ( ! $this->request->post['aramex_account_entity'] )
        {
            $this->error['aramex_account_entity'] = $this->language->get('error_account_entity');
        }

        #########   Shipper validation #############

        if ( ! $this->request->post['aramex_shipper_name'] )
        {
            $this->error['aramex_shipper_name'] = $this->language->get('error_shipper_name');
        }

        if ( ! $this->request->post['aramex_shipper_email'] )
        {
            $this->error['aramex_shipper_email'] = $this->language->get('error_shipper_email');
        }

        if ( ! $this->request->post['aramex_shipper_company'] )
        {
            $this->error['aramex_shipper_company'] = $this->language->get('error_shipper_company');
        }

        if ( ! $this->request->post['aramex_shipper_address'] )
        {
            $this->error['aramex_shipper_address'] = $this->language->get('error_shipper_address');
        }

        if ( ! $this->request->post['aramex_shipper_country_code'] )
        {
            $this->error['aramex_shipper_country_code'] = $this->language->get('error_shipper_country_code');
        }

        if ( ! $this->request->post['aramex_shipper_city'] )
        {
            $this->error['aramex_shipper_city'] = $this->language->get('error_shipper_city');
        }

        if ( ! $this->request->post['aramex_shipper_postal_code'] )
        {
            $this->error['aramex_shipper_postal_code'] = $this->language->get('error_shipper_postal_code');
        }

        if ( ! $this->request->post['aramex_shipper_state'] )
        {
            $this->error['aramex_shipper_state'] = $this->language->get('error_shipper_state');
        }

        if ( ! $this->request->post['aramex_shipper_phone'] )
        {
            $this->error['aramex_shipper_phone'] = $this->language->get('error_shipper_phone');
        }

        if ( $this->request->post['aramex_auto_create_shipment'] )
        {
            if ( ! $this->request->post['aramex_default_allowed_domestic_methods'] )
            {
                $this->error['aramex_default_allowed_domestic_methods'] = $this->language->get('error_default_allowed_domestic_methods');
            }

            if ( ! $this->request->post['aramex_default_allowed_domestic_additional_services'] )
            {
                $this->error['aramex_default_allowed_domestic_additional_services'] = $this->language->get('error_default_allowed_domestic_additional_services');
            }

            if ( ! $this->request->post['aramex_default_allowed_international_additional_services'] )
            {
                $this->error['error_default_allowed_international_additional_services'] = $this->language->get('error_default_allowed_international_additional_services');
            }

        }


        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }
}
