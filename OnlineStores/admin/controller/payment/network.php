<?php

class ControllerPaymentNetwork extends Controller
{

    private $error = array();
    private $name = '';


    public function index()
    {

        $this->language->load('payment/network');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        $this->load->model('localisation/geo_zone');

        $this->load->model('localisation/order_status');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if (!$this->validate()) {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'network', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('network', $this->request->post);
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));

            return;
        }


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
            'href' => $this->url->link('payment/network', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/network', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('payment/network', 'token=' . $this->session->data['token'], 'SSL');

        // ============ /breadcrumbs ===========

        $fields = ['email', 'outlet_id', 'api_key', 'currency', 'completed_order_status_id', 'geo_zone_id', 'live_mode'];

        $settings = $this->model_setting_setting->getSetting('network');

        foreach ($fields as $field) {
            $this->data['network_' . $field] = $settings['network_' . $field];
        }

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/language');

        $settings = $this->model_setting_setting->getSetting('network');

        $this->data['languages'] = $languages = $this->model_localisation_language->getLanguages();


        foreach ($languages as $language) {
            $this->data['network_field_name_' . $language['language_id']] = $settings['network_field_name_' . $language['language_id']];
            $this->data['network_' . $language['language_id']] = $settings['network_' . $language['language_id']];
        }


        $this->template = 'payment/network.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }


    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/network')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['network_email']) {
            $this->error['network_email'] = $this->language->get('error_email');
        }
        if (!$this->request->post['network_outlet_id']) {
            $this->error['outlet_id'] = $this->language->get('outlet_id');
        }
        if (!$this->request->post['network_api_key']) {
            $this->error['api_key'] = $this->language->get('api_key');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return $this->error ? false : true;
    }
}
