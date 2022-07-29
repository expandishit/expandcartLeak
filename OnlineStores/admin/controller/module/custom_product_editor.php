<?php

class ControllerModuleCustomProductEditor extends Controller
{
    public function install()
    {
        $this->load->model("module/custom_product_editor");
        $this->model_module_custom_product_editor->install();
    }

    public function uninstall()
    {
        $this->load->model("module/custom_product_editor");
        $this->model_module_custom_product_editor->uninstall();
    }

    public function index()
    {
        $this->language->load('module/custom_product_editor');

        $this->document->setTitle($this->language->get('heading_title'));

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
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/custom_product_editor', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('module/custom_product_editor');
        if ($this->model_module_custom_product_editor->isInstalled()) {
            $data['custom_product_editor'] = $this->model_module_custom_product_editor->getSettings();
        }

        $this->template = 'module/custom_product_editor.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/custom_product_editor/updateSettings', '', 'SSL');

        $data['cancel'] = $this->url . 'marketplace/home';

        $this->data = $data;

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/custom_product_editor');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {

            $this->load->model('module/custom_product_editor');

            $data = $this->request->post['custom_product_editor'];

            $this->model_module_custom_product_editor->updateSettings(['custom_product_editor' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }

        return $this->response->setOutput(json_encode($result_json));
    }
}
