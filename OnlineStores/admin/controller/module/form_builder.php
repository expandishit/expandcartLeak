<?php

class ControllerModuleFormBuilder extends Controller
{
    private $model;

    private function init()
    {
        $this->load->model('module/form_builder');

        $this->model = $this->model_module_form_builder;

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }
        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->language->load('module/form_builder');
    }

    public function index()
    {
        $this->init();

        if (isset($this->request->post['form_builder'])) {
            $data['settings'] = $this->request->post['form_builder'];
        } elseif ($this->config->get('form_builder')) {
            $data['settings'] = $this->config->get('form_builder');
        }
        $this->data['fieldTypes'] = [
                "text_text"=>'text',
                "text_paragraph"=>'textarea',
                "text_email"=>'email',
                "text_select"=>'select',
                "text_radio"=>'radio',
                "text_checkbox"=>'checkbox',
                "text_file"=>'file',
            ];

        $this->document->setTitle($this->language->get('form_builder_heading_title'));

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
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/form_builder', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'module/form_builder.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['links'] = [
            'submit' => $this->url->link(
                'module/form_builder/updateSettings',
                '',
                'SSL'
            ),
            'cancel' => $this->url->link('module/form_builder', '', 'SSL'),
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

        $data = $this->request->post;

        if (!$this->model->validate($data)) {

            if (count($this->model->errors) > 0) {
                $this->session->data['errors'] = $this->model->errors;
                $result_json['error'] = $this->model->errors;
                $result_json['success'] = '0';
                $this->response->setOutput(json_encode($result_json));
                return;
            }
        }

        $this->model->updateSettings(['form_builder' => $data['settings']]);

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
