<?php

class ControllerModuleRelatedProducts extends Controller
{
    private $model;

    private function init()
    {
        $this->load->model('module/related_products/settings');

        $this->model = $this->model_module_related_products_settings;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $this->language->load('module/related_products');
    }

    public function index()
    {
        $this->init();

        if (isset($this->request->post['related_products'])) {
            $data['settings'] = $this->request->post['related_products'];
        } elseif ($this->config->get('related_products')) {
            $data['settings'] = $this->config->get('related_products');
        }

        $this->document->setTitle($this->language->get('related_products_heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/related_products', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/related_products/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['links'] = [
            'submit' => $this->url->link(
                'module/related_products/updateSettings',
                '',
                'SSL'
            ),
            'cancel' => $this->url->link('module/related_products', '', 'SSL'),
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

        $this->init();

        $data = $this->request->post['related_products'];

        if (!$this->model->validate($data)) {

            if (count($this->model->errors) > 0) {
                $this->session->data['errors'] = $this->model->errors;
                $result_json['error'] = $this->model->errors;
                $result_json['success'] = '0';
                $this->response->setOutput(json_encode($result_json));
                return;
            }
        }

        /*if ($data['products_count'] < 5) {
            $data['products_count'] = 5;
        }*/

        if ($data['products_count'] > 20) {
            $data['products_count'] = 20;
        }

        if (count($data['source']) == 0) {
            $data['rp_status'] = 2;
        }


        $this->model->updateSettings(['related_products' => $data]);

        $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_settings_success');

        $result_json['success'] = '1';
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->init();

        $this->model->install();
    }

    public function uninstall()
    {
        $this->init();

        $this->model->uninstall();
    }
}
