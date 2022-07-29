<?php

class ControllerNetworkMarketingDownlines extends Controller
{

    private $settings, $agencies;

    private function init()
    {
        $this->load->model('module/network_marketing/settings');
        $this->load->model('module/network_marketing/agencies');

        $this->settings = $this->model_module_network_marketing_settings;
        $this->agencies = $this->model_module_network_marketing_agencies;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->language->load('module/network_marketing');
    }

    public function index()
    {
        $this->init();

        $this->document->setTitle($this->language->get('network_marketing_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('network_marketing_heading_title'),
            'href'      => $this->url->link('module/network_marketing', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'submit' => $this->url->link(
                'network_marketing/levels/updateLevels',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'cancel' => $this->url->link('module/network_marketing', 'token=' . $this->session->data['token'], 'SSL'),
            'viewAgency' => $this->url->link(
                'network_marketing/agencies/view', 'token=' . $this->session->data['token'], 'SSL'
            ),
            'downline' => $this->url->link(
                'network_marketing/downline/view', 'token=' . $this->session->data['token'], 'SSL'
            ),
        ];

        $this->data['agencies'] = $this->agencies->listAgencies();

        $this->template = 'module/network_marketing/agencies.tpl';

        $this->response->setOutput($this->render());
    }
}
