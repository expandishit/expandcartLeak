<?php

class ControllerModuleRentalProducts extends Controller
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

        $this->language->load('module/rental_products');
    }

    public function index()
    {
        $this->init([
            'module/rental_products/settings'
        ]);

        if (isset($this->request->post['rental_products'])) {
            $data['settings'] = $this->request->post['rental_products'];
        } elseif ($this->config->get('rental_products')) {
            $data['settings'] = $this->config->get('rental_products');
        }

        $this->document->setTitle($this->language->get('rental_products_heading_title'));

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
            'text'      => $this->language->get('rental_products_heading_title'),
            'href'      => $this->url->link('module/rental_products', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/rental_products/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['links'] = [
            'submit' => $this->url->link(
                'module/rental_products/updateSettings',
                '',
                'SSL'
            ),
            'cancel' => $this->url->link('module/rental_products', '', 'SSL'),
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
            'module/rental_products/settings'
        ]);

        $data = $this->request->post['rental_products'];

        $this->settings->updateSettings(['rental_products' => $data]);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_settings_success');

        $result_json['success'] = '1';

        $this->response->setOutput(json_encode($result_json));

        return;

    }

    public function install()
    {
        $this->init(['module/rental_products/settings']);

        $this->settings->install();
    }

    public function uninstall()
    {
        $this->init(['module/rental_products/settings']);

        $this->settings->uninstall();
    }
}
