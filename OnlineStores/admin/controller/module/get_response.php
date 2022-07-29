<?php

class ControllerModuleGetResponse extends Controller
{
    public function index()
    {
        $this->language->load('module/get_response');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('extension/payment', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/get_response', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer([
            'getResponseApp' => 'module/get_response/settings',
        ]);

        $this->data['get_response'] = $settings = $this->getResponseApp->getSettings();

        $this->getResponseApp->setApiKey($settings['api_key']);
        $this->data['tags'] = $this->getResponseApp->getTags();
        $this->data['campaigns'] = $this->getResponseApp->getCampaigns();

        $this->template = 'module/get_response/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateSettings()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            return 'error';
        }

        $data = $this->request->post['get_response'];

        $this->initializer([
            'getResponseApp' => 'module/get_response/settings',
        ]);

        $this->language->load('module/get_response');

        if (!$this->getResponseApp->validate($data)) {
            $response['success'] = '0';
            $response['errors'] = $this->getResponseApp->getErrors();

            $this->response->setOutput(json_encode($response));

            return;
        }

        $this->getResponseApp->updateSettings(array_merge($this->getResponseApp->getSettings(), $data));

        $response['success_msg'] = $this->language->get('message_settings_updated');

        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));

        return;
    }
}
