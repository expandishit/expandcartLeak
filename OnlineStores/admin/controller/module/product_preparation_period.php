<?php

class ControllerModuleProductPreparationPeriod extends Controller

{
    private $errors = [];

    public function install()
    {
        $this->load->model('module/product_preparation_period');
        $this->model_module_product_preparation_period->install();
    }

    public function uninstall()
    {
        $this->load->model('module/product_preparation_period');
        $this->model_module_product_preparation_period->uninstall();
    }

    public function index()
    {
        $this->language->load('module/product_preparation_period');
        $this->load->model('module/product_preparation_period');
        $this->load->model('localisation/language');

        $this->document->setTitle($this->language->get('product_preparation_period'));

        $this->data['product_preparation_period'] = $this->config->get('product_preparation_period');
        $this->data['submit_link'] = $this->url->link('module/product_preparation_period/saveSettings', '', 'SSL');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_preparation_period_text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_preparation_period_title'),
            'href'      => $this->url->link('module/product_preparation_period', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/product_preparation_period/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function saveSettings()
    {
        $this->language->load('module/product_preparation_period');

        if (!$this->validate()) {
            $json['success'] = '0';
            $json['errors'] = $this->errors;
        } else {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('product_preparation_period', $this->request->post);
            $json['success'] = '1';
            $json['success_msg'] = $this->language->get('success');
        }

        $this->response->setOutput(json_encode($json));
    }

    private function validate()
    {
        return empty($this->errors);
    }

}
