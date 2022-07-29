<?php

class ControllerModuleMinimumDeposit extends Controller
{
    private $model;

    public function index()
    {
        $this->load->language('module/minimum_deposit');

        $this->load->model('setting/setting');

        $data['minimum_deposit'] = array();

        if (isset($this->request->post['minimum_deposit'])) {
            $minimum_deposit['settings'] = $this->request->post['minimum_deposit'];
        } elseif ($this->config->get('minimum_deposit')) {
            $minimum_deposit['settings'] = $this->config->get('minimum_deposit');
        }

        $this->init();

        $this->document->setTitle($this->language->get('md_heading_title'));

        $data['schemaAction'] = $this->url->link(
            'module/minimum_deposit/updateSettings',
            '',
            'SSL'
        );

        $data['cancel'] = $this->url->link(
            'module/minimum_deposit',
            '',
            'SSL'
        );

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
            'href'      => $this->url->link('module/minimum_deposit', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/minimum_deposit/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['minimum_deposit'] = $minimum_deposit;
        $data['currency_code'] = $this->config->get('config_currency');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->load->language('module/minimum_deposit');
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $response['success'] = '0';
            $response['success_msg'] = "";
            $this->response->setOutput(json_encode($response));
            return;
        }

        $this->init();

        $post = $this->request->post;

        if (isset($post['formType'])) {
            if ($post['formType'] == 'settings') {
                $settings = $post['settings'];

                $this->model->updateSettings(['minimum_deposit' => $settings]);

                $response['success'] = '1';
                $response['success_msg'] = $this->language->get('text_success');
                $this->response->setOutput(json_encode($response));

            }

        }
    }

    private function init()
    {
        $this->load->model('module/minimum_deposit/settings');

        $this->model = $this->model_module_minimum_deposit_settings;
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
