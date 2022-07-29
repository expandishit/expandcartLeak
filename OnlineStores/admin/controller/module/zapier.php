<?php

class ControllerModuleZapier extends Controller
{
    private $error = [];

    public function index()
    {
        $this->load->model('module/zapier');

        $this->language->load('module/zapier');
        $this->load->model('setting/setting');

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
            'href'      => $this->url->link('module/zapier', '', 'SSL'),
            'separator' => ' :: '
        );

        if ($this->model_module_zapier->isInstalled()) {
            $data['zapier']= $this->model_module_zapier->getSettings();
        }
        
        $this->template = 'module/zapier/settings.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['credentials'] = $this->url->link('setting/integration', '', 'SSL');
        $data['action'] = $this->url->link('module/zapier/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url . 'marketplace/home';

        $this->data = $data;

        $this->response->setOutput($this->render());
    }



    public function install()
    {
        $this->load->model("module/zapier");
        $this->model_module_zapier->install();
    }


    public function uninstall()
    {
        $this->load->model("module/zapier");
        $this->model_module_zapier->uninstall();
    }


    public function updateSettings()
    {
        $this->language->load('module/zapier');

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
        } else {
          
            $this->load->model('module/zapier');

            $this->model_module_zapier->updateSettings(['zapier' => $this->request->post]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }

        return $this->response->setOutput(json_encode($result_json));
    }

}
