<?php

class ControllerShippingWeight extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('shipping/weight');

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

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'weight', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->insertUpdateSetting('weight', $this->request->post);

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
            'href' => $this->url->link('shipping/weight', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link('shipping/weight', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->data['cancel'] = $this->url->link('shipping/weight', '', 'SSL');

        // ======================= /breadcrumbs ==============================

        $this->load->model('localisation/geo_zone');

        $geo_zones = $this->model_localisation_geo_zone->getGeoZones();

        foreach ( $geo_zones as $geo_zone )
        {
            
            $this->data['weight_' . $geo_zone['geo_zone_id'] . '_rate'] = $this->config->get('weight_' . $geo_zone['geo_zone_id'] . '_rate');

            $this->data['weight_' . $geo_zone['geo_zone_id'] . '_status'] = $this->config->get('weight_' . $geo_zone['geo_zone_id'] . '_status');
        }

        $this->data['geo_zones'] = $geo_zones;

        $this->data['weight_tax_class_id'] = $this->config->get('weight_tax_class_id');

        $this->load->model('localisation/tax_class');

        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->data['weight_status'] = $this->config->get('weight_status');

        $this->data['weight_sort_order'] = $this->config->get('weight_sort_order');

        $this->template = 'shipping/weight.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    
    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'shipping/weight') )
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
