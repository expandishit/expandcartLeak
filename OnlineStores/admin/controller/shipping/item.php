<?php

class ControllerShippingItem extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('shipping/item');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'item', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->insertUpdateSetting('item', $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }


        // ======================== breadcrumbs ======================

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
            'href' => $this->url->link('shipping/item', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link('shipping/item', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->data['cancel'] = $this->url->link('shipping/item', '', 'SSL');

        // ======================== /breadcrumbs ======================

        $this->data['item_cost'] = $this->config->get('item_cost');
        
        $this->data['item_tax_class_id'] = $this->config->get('item_tax_class_id');

        $this->load->model('localisation/tax_class');

        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
        
        $this->data['item_geo_zone_id'] = $this->config->get('item_geo_zone_id');

        $this->load->model('localisation/geo_zone');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->data['item_status'] = $this->config->get('item_status');

        $this->template = 'shipping/item.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'shipping/item') )
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
