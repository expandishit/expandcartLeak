<?php

class ControllerNetworkMarketingSettings extends Controller
{
    private $settings;

    private $tmp = [];

    private function init()
    {
        $this->load->model('module/network_marketing/settings');

        $this->settings = $this->model_module_network_marketing_settings;

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

    public function index()
    {
        $this->init();

        $data = $this->tmp;

        if (isset($this->request->post['network_marketing'])) {
            $data['settings'] = $this->request->post['network_marketing'];
        } elseif ($this->config->get('network_marketing')) {
            $data['settings'] = $this->config->get('network_marketing');
        }

        $this->document->setTitle($this->language->get('network_marketing_heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('network_marketing_heading_title'),
            'href'      => $this->url->link('module/network_marketing', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/network_marketing/settings.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['links'] = [
            'submit' => $this->url->link(
                'network_marketing/settings/updateSettings',
                'token=' . $this->session->data['token'],
                'SSL'
            ),
            'cancel' => $this->url->link('module/network_marketing', 'token=' . $this->session->data['token'], 'SSL'),
        ];

        $data['currency'] = $this->config->get('config_currency');

        $this->data = $data;

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

        $data = $this->request->post['network_marketing'];

        if (!$this->settings->validate($data)) {

            if (count($this->settings->errors) > 0) {
                $this->session->data['errors'] = $this->settings->errors;
            }

            $this->redirect(
                $this->url->link(
                    'module/network_marketing',
                    'token=' . $this->session->data['token'],
                    'SSL'
                )
            );
        }

        $this->settings->updateSettings(['network_marketing' => array_merge($this->settings->getSettings(), $data)]);

        $this->session->data['success'] = $this->language->get('text_settings_success');

        $this->redirect(
            $this->url->link(
                'network_marketing/settings',
                'token=' . $this->session->data['token'],
                'SSL'
            )
        );
    }
}
