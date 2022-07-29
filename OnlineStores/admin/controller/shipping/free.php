<?php

class ControllerShippingFree extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('shipping/free');

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

            $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'free', true);

            
            $this->tracking->updateGuideValue('SHIPPING');

            $this->model_setting_setting->insertUpdateSetting('free', $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }


        // ======================== breadcrumbs ============================

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
            'href' => $this->url->link('shipping/free', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link('shipping/free', '', 'SSL'),
            'cancel' => $this->url->link('extension/shipping', '', 'SSL'),
        ];

        $this->data['cancel'] = $this->url->link('shipping/free', '', 'SSL');

        // ======================== /breadcrumbs ============================

        $this->data['free_total'] = $this->config->get('free_total');

        $this->data['free_geo_zone_id'] = $this->config->get('free_geo_zone_id');

        $this->load->model('localisation/geo_zone');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->data['free_status'] = $this->config->get('free_status');
        
        $this->data['free_sort_order'] = $this->config->get('free_sort_order') ?: 1;
        
        $this->template = 'shipping/free.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    
    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'shipping/free') )
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
