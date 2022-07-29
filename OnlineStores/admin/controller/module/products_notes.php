<?php

class ControllerModuleProductsNotes extends Controller

{
    private $errors = [];

    public function install()
    {
        $this->load->model('module/products_notes');
        $this->model_module_products_notes->install();
    }

    public function uninstall()
    {
        $this->load->model('module/products_notes');
        $this->model_module_products_notes->uninstall();
    }

    public function index()
    {
        $this->language->load('module/products_notes');
        $this->load->model('module/products_notes');
        $this->load->model('localisation/language');

        $this->document->setTitle($this->language->get('products_notes'));

        $this->data['products_notes'] = $this->config->get('products_notes');
        $this->data['submit_link'] = $this->url->link('module/products_notes/saveSettings', '', 'SSL');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('products_notes_text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('products_notes'),
            'href'      => $this->url->link('module/products_notes', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/products_notes/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function saveSettings()
    {
        $this->language->load('module/products_notes');

        if (!$this->validate()) {
            $json['success'] = '0';
            $json['errors'] = $this->errors;
        } else {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('products_notes', $this->request->post);
            $json['success'] = '1';
            $json['success_msg'] = $this->language->get('success');
        }

        $this->response->setOutput(json_encode($json));
    }

    private function validate(): bool
    {
        return empty($this->errors);
    }

}
