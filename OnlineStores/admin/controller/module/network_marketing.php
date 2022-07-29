<?php

class ControllerModuleNetworkMarketing extends Controller
{
    public function index()
    {
        $this->language->load('module/network_marketing');

        if (isset($this->request->post['network_marketing'])) {
            $data['settings'] = $this->request->post['network_marketing'];
        } elseif ($this->config->get('network_marketing')) {
            $data['settings'] = $this->config->get('network_marketing');
        }

        $this->document->setTitle($this->language->get('network_marketing_heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('network_marketing_heading_title'),
            'href'      => $this->url->link('module/network_marketing', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/network_marketing/home.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['links'] = [
            'settings' => $this->url->link(
                'network_marketing/settings', '', 'SSL'
            ),
            'levels' => $this->url->link(
                'network_marketing/levels', '', 'SSL'
            ),
            'agencies' => $this->url->link(
                'network_marketing/agencies', '', 'SSL'
            ),
            'withdrawls' => $this->url->link(
                'network_marketing/withdrawls', '', 'SSL'
            ),
        ];

        $data['currency'] = $this->config->get('config_currency');

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    private function init()
    {
        $this->load->model('module/network_marketing/settings');
        $this->load->model('module/network_marketing/levels');

        $this->settings = $this->model_module_network_marketing_settings;
        $this->levels = $this->model_module_network_marketing_levels;

        if (isset($this->session->data['errors'])) {
            $this->tmp['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->tmp['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $this->language->load('module/network_marketing');
    }

    public function install()
    {
        $this->load->model('module/network_marketing/settings');

        $this->model_module_network_marketing_settings->install();
    }

    public function uninstall()
    {
        $this->load->model('module/network_marketing/settings');

        $this->model_module_network_marketing_settings->uninstall();
    }
}
