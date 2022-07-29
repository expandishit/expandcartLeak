<?php

class ControllerModulePricePerMeter extends Controller
{
    private $settings;

    public function init($models)
    {
        // TODO modularize this.
        foreach ($models as $model) {

            $this->load->model($model);

            $object = explode('/', $model);
            $object = end($object);

            $model = str_replace('/', '_', $model);

            $this->$object = $this->{"model_" . $model};
        }

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $this->language->load('module/price_per_meter');
    }

    public function index()
    {
        $this->init([
            'module/rental_products/settings'
        ]);

        if (isset($this->request->post['price_per_meter'])) {
            $data['settings'] = $this->request->post['price_per_meter'];
        } elseif ($this->config->get('price_per_meter')) {
            $data['settings'] = $this->config->get('price_per_meter');
        }

        $this->document->setTitle($this->language->get('price_per_meter_heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('price_per_meter_heading_title'),
            'href'      => $this->url->link('module/price_per_meter', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_settings'] = $this->language->get('text_settings');

        $this->template = 'module/price_per_meter/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/price_per_meter/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{
            $this->init([
                'module/price_per_meter/settings'
            ]);

            $data = $this->request->post['price_per_meter'];

            $this->settings->updateSettings(['price_per_meter' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->init(['module/price_per_meter/settings']);

        $this->settings->install();
    }

    public function uninstall()
    {
        $this->init(['module/price_per_meter/settings']);

        $this->settings->uninstall();
    }
}
