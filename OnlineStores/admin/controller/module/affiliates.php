<?php

class ControllerModuleAffiliates extends Controller
{
    private $errors = [];

    /**
     * Install module/app
     *
     * @return void
     */
    public function install()
    {
        $this->load->model('module/affiliates');
        $this->model_module_affiliates->install();
    }

    /**
     * Uninstall module/app
     *
     * @return void
     */
    public function uninstall()
    {
        $this->load->model('module/affiliates');
        $this->model_module_affiliates->uninstall();
    }

    public function index()
    {
        $this->language->load('module/affiliates');
        $this->document->setTitle($this->language->get('affiliates_title'));

        $this->data['affiliates'] = $this->config->get('affiliates');
        $this->data['submit_link'] = $this->url->link('module/affiliates/saveSettings', '', 'SSL');
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('affiliates_title'),
            'href'      => $this->url->link('module/affiliates', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/affiliates/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    /**
     * Update Module Settings in DB settings table.
     *
     * @return JSON response.
     */
    public function saveSettings()
    {
        $this->language->load('module/affiliates');
        if (!$this->validate()) {
            $json['success'] = '0';
            $json['errors'] = $this->errors;
            $this->response->setOutput(json_encode($json));
            return;
        }
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('affiliates', $this->request->post);
        $json['success'] = '1';
        $json['success_msg'] = $this->language->get('affiliates_success_save');
        $this->response->setOutput(json_encode($json));
    }

    private function validate()
    {
        return empty($this->errors);
    }
}