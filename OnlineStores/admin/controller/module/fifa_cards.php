<?php

class ControllerModuleFifaCards extends Controller
{
    public function index()
    {
        $this->load->model('module/fifa_cards/settings');

        if (isset($this->request->post['fifa_cards'])) {
            $data['settings'] = $this->request->post['fifa_cards'];
        } elseif ($this->config->get('fifa_cards')) {
            $data['settings'] = $this->config->get('fifa_cards');
        }

        $this->language->load('module/fifa_cards');

        $this->document->setTitle($this->language->get('fifa_cards_heading_title'));

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
            'text'      => $this->language->get('fifa_cards_heading_title'),
            'href'      => $this->url->link('module/fifa_cards', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_settings'] = $this->language->get('text_settings');

        $this->template = 'module/fifa_cards/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/fifa_cards/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $this->load->model('module/fifa_cards/settings');

            $data = $this->request->post['fifa_cards'];

            $this->model_module_fifa_cards_settings->updateSettings(['fifa_cards' => $data]);
            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->load->model('module/fifa_cards/settings');
        $this->model_module_fifa_cards_settings->install();
    }

    public function uninstall()
    {
        $this->load->model('module/fifa_cards/settings');
        $this->model_module_fifa_cards_settings->uninstall();
    }
}
