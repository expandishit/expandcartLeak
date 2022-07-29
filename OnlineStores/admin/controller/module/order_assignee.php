<?php

class ControllerModuleOrderAssignee extends Controller
{
    public function install()
    {
        $this->load->model("module/order_assignee");
        $this->model_module_order_assignee->install();
    }

    public function uninstall()
    {
        $this->load->model("module/order_assignee");
        $this->model_module_order_assignee->uninstall();
    }

    public function index()
    {
        $this->language->load('module/order_assignee');

        $this->document->setTitle($this->language->get('heading_order_assignee_title'));

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
            'text'      => $this->language->get('heading_order_assignee_title'),
            'href'      => $this->url->link('module/order_assignee', '', 'SSL'),
            'separator' => ' :: '
        );
        

        $data['order_assignee'] = $this->config->get('order_assignee');
       
        $this->template = 'module/order_assignee/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/order_assignee/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/order_assignee');
        
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $this->load->model('module/order_assignee');
            $this->load->model('setting/setting');
            $data = $this->request->post['order_assignee'];
            
            $this->model_module_order_assignee->updateSettings(['order_assignee' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = 'module/order_assignee';

        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

   

}
