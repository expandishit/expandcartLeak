<?php

class ControllerModuleProductClassification extends Controller
{
    private $settings;
    protected $errors = [];

    public function index()
    {
        $this->load->model('module/product_classification/settings');
        $this->language->load('module/product_classification');

        $this->document->setTitle($this->language->get('product_classification_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_classification_heading_title'),
            'href'      => $this->url->link('module/product_classification', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');
        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();


        $this->template = 'module/product_classification/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        // get app settings
        $this->data['settingsData'] = $this->model_module_product_classification_settings->getSettings();


        $this->data['action'] = $this->url->link('module/product_classification/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url->link('marketplace/home', '', 'SSL');


        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['errors'] = 'Invalid Request';
        }else{
            $this->load->model('module/product_classification/settings');
            $this->language->load('module/product_classification');

            $data = $this->request->post['product_classification'];

            $this->model_module_product_classification_settings->updateSettings(['product_classification' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }


    public function install()
    {
        $this->load->model('module/product_classification/settings');

        $this->model_module_product_classification_settings->install();
    }

    public function uninstall()
    {
        $this->load->model('module/product_classification/settings');

        $this->model_module_product_classification_settings->uninstall();
    }

}
