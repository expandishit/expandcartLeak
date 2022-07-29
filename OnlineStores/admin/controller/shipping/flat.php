<?php

class ControllerShippingFlat extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('shipping/flat');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

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

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'flat', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->insertUpdateSetting('flat', $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        // ======================== breadcrumbs =============================


        $this->data['links'] = [
            'action' => $this->url->link('shipping/flat', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        // ======================== /breadcrumbs =============================

        $this->data['flat_cost'] = $this->config->get('flat_cost');

        $this->data['flat_tax_class_id'] = $this->config->get('flat_tax_class_id');

        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->data['flat_geo_zone_id'] = $this->config->get('flat_geo_zone_id');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->data['flat_status'] = $this->config->get('flat_status');
        
        $this->data['flat_sort_order'] = $this->config->get('flat_sort_order') ?: 1;

        $this->template = 'shipping/flat.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    protected function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'shipping/flat') )
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
