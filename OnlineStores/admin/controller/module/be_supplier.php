<?php

class ControllerModuleBeSupplier extends Controller
{
    public function index()
    {
        $this->language->load('module/be_supplier');

        $this->load->model('module/be_supplier');

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
            'href'      => $this->url->link('module/be_supplier', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $data['defaultCurrency'] = $this->config->get('config_currency');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_settings'] = $this->language->get('text_settings');

        $data['be_supplier'] = $this->config->get('be_supplier');
        
        $this->load->model('api/clients');
        if(!$this->model_api_clients->getDropnaClient()){
            $data['dropna_url'] = $this->url->link('module/dropna', '', 'SSL');
            $data['dropna_warning'] = true;
            $data['text_dropna_warning'] = $this->language->get('text_dropna_warning');
            $data['text_dropna_url']     = $this->language->get('text_dropna_url');
        }

        $this->template = 'module/be_supplier.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/be_supplier/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/be_supplier');
        
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $this->load->model('module/be_supplier');

            $data = $this->request->post['be_supplier'];

            $this->model_module_be_supplier->updateSettings(['be_supplier' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
