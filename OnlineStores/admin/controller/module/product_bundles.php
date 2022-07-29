<?php

class ControllerModuleProductBundles extends Controller
{
    private $settings;


    
    public function init()
    {
        $this->load->model('module/product_bundles/settings');

        $this->settings = $this->model_module_product_bundles_settings;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $this->language->load('module/product_bundles');
    }

    public function index()
    {
        $this->init([
            'module/product_bundles/settings'
        ]);

        if (isset($this->request->post['product_bundles'])) {
            $data['settings'] = $this->request->post['product_bundles'];
        } elseif ($this->config->get('product_bundles')) {
            $data['settings'] = $this->config->get('product_bundles');
        }

        $this->document->setTitle($this->language->get('product_bundles_heading_title'));

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
            'text'      => $this->language->get('product_bundles_heading_title'),
            'href'      => $this->url->link('module/product_bundles', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/product_bundles/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['links'] = [
            'submit' => $this->url->link(
                'module/product_bundles/updateSettings',
                '',
                'SSL'
            ),
            'cancel' => $this->url->link('module/product_bundles', '', 'SSL'),
        ];

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $this->init([
            'module/product_bundles/settings'
        ]);

        $data = $this->request->post['product_bundles'];
        
        $this->settings->updateSettings(['product_bundles' => $data]);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_settings_success');

        $result_json['success'] = '1';

        $this->response->setOutput(json_encode($result_json));

        return;

    }

    public function install()
    {
        $this->init(['module/product_bundles/settings']);

        $this->settings->install();
    }

    public function uninstall()
    {
        $this->init(['module/product_bundles/settings']);

        $this->settings->uninstall();
    }
}
