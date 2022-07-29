<?php

class ControllerModuleMailchimp extends Controller
{
    public function index()
    {
        $this->language->load('module/mailchimp');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/mailchimp', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->initializer([
            'mailchimp' => 'module/mailchimp/settings',
        ]);

        $this->data['mailchimp'] = $settings = $this->mailchimp->getSettings();

        $this->template = 'module/mailchimp/settings.expand';
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

        $data = $this->request->post['mailchimp'];

        $this->initializer([
            'mailchimp' => 'module/mailchimp/settings',
        ]);

        $this->language->load('module/mailchimp');

        if (!$this->mailchimp->validate($data)) {
            $response['success'] = '0';
            $response['errors'] = $this->mailchimp->getErrors();

            $this->response->setOutput(json_encode($response));

            return;
        }

        $this->mailchimp->updateSettings(array_merge($this->mailchimp->getSettings(), $data));

        $response['success_msg'] = $this->language->get('message_settings_updated');

        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));

        return;
    }
}
