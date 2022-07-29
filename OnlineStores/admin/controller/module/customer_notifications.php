<?php

class ControllerModuleCustomerNotifications extends Controller
{
    public function install()
    {
        $this->load->model("module/customer_notifications");
        $this->model_module_customer_notifications->install();
    }

    public function uninstall()
    {
        $this->load->model("module/customer_notifications");
        $this->model_module_customer_notifications->uninstall();
    }

    public function index()
    {
        $this->language->load('module/customer_notifications');

        $this->document->setTitle($this->language->get('heading_customer_notifications_title'));

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
            'text'      => $this->language->get('heading_customer_notifications_title'),
            'href'      => $this->url->link('module/customer_notifications', '', 'SSL'),
            'separator' => ' :: '
        );
        

        $data['customer_notifications'] = $this->config->get('customer_notifications');
       
        $this->template = 'module/customer_notifications/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/customer_notifications/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/customer_notifications');
        
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{

            $this->load->model('module/customer_notifications');
            $this->load->model('setting/setting');
            $data = $this->request->post['customer_notifications'];
            
            $this->model_module_customer_notifications->updateSettings(['customer_notifications' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['redirect'] = '1';
            $result_json['to'] = 'module/customer_notifications';

        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

   

}
