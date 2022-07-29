<?php

class ControllerModuleCategoryDroplist extends Controller
{
    public function index()
    {
        $this->language->load('module/category_droplist');

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
            'href'      => $this->url->link('module/category_droplist', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['category_droplist'] = $this->config->get('category_droplist');
        
        if(!isset($data['category_droplist']['levels']))
            $data['category_droplist']['levels'] = 1;
        
        $this->template = 'module/category_droplist.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/category_droplist/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/category_droplist');
        
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $this->load->model('module/category_droplist');

            $data = $this->request->post['category_droplist'];

            $this->model_module_category_droplist->updateSettings(['category_droplist' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->initializer(['module/category_droplist']);
        $this->category_droplist->install();
    }

    public function uninstall()
    {
        $this->initializer(['module/category_droplist']);
        $this->category_droplist->uninstall();
    }
}
