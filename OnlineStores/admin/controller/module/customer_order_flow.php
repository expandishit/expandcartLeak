<?php

/**
 *   Controller Class for order flow up Module
 *
 * @author Hoda Sheir.
 */
class ControllerModuleCustomerOrderFlow extends Controller
{
    public $module = 'customer_order_flow';
    private $errors =array();

    public function index(){
        if (!\Extension::isInstalled($this->module) || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            return $this->forward("error/permission");
        }
        
        $this->load->model("setting/setting");
        $this->load->model('localisation/order_status');
        $this->load->model('module/customer_order_flow');

        $this->load->language("module/{$this->module}");

        $this->document->setTitle($this->language->get("{$this->module}_heading_title"));
        $this->data["breadcrumbs"] = array();

        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/home", "", "SSL"),
            "separator" => false
        );

        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_modules"),
            "href" => $this->url->link(
                "marketplace/home",
                "",
                "SSL"
          ),
            "separator" => " :: "
        );

        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("{$this->module}_heading_title"),
            "href" => $this->url->link("module/{$this->module}", "", "SSL"),
            "separator" => " :: "
        );
                
        $this->data["action"] = $this->url->link("module/{$this->module}/updateSettings", "", "SSL");

        $this->data["{$this->module}_data"] = $this->model_setting_setting->getSetting($this->module);

        $this->template = "module/{$this->module}.expand";
        $this->data['heading_title'] =  $this->language->get("{$this->module}_heading_title");
        $this->data['order_statues'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->data['action'] =  $this->url->link('module/customer_order_flow/updateSettings', '', 'SSL');
        $this->data['cof'] = $this->model_module_customer_order_flow->getSettings();

        $this->children = array(
            "common/header",
            "common/footer",
        );

        $this->response->setOutput($this->render());

    }

    public function updateSettings(){
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['errors'] = 'Invalid Request';
        }else{
            $this->language->load('module/customer_order_flow');
            $data = $this->request->post['cof'];

            if(!$this->validate()){
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
               $result_json['errors']['warning'] = $this->language->get('text_error');;
            }else{
                
                $this->load->model('module/customer_order_flow');

                $this->model_module_customer_order_flow->updateSettings(['customer_order_flow' => $data]);
                $result_json['success'] = '1';
                $this->session->data['success'] = $this->language->get('text_settings_success');
                $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            }
            $this->response->setOutput(json_encode($result_json));
            return;
            
        }
    }

    private function validate(){
        $data = $this->request->post['cof'];
        if($data['cancel_orders_status'] && empty($data['orders_cancellation_statues'])){
            $this->errors['cancel_orders_status'] = $this->language->get('error_cancel_orders_status');
        }
        if($data['cancel_orders_status'] && !$data['cof_cancelled_order_status_id']){
            $this->errors['cancel_orders_status'] = $this->language->get('error_cancelled_order_status');
        }
        if($data['reorder_orders_status'] && empty($data['orders_reordring_statues'])){
            $this->errors['reorder_orders_status'] = $this->language->get('error_reorder_status');
        }
        if($data['archiving_orders_status'] && empty($data['orders_archiving_statues'])){
            $this->errors['archiving_orders_status'] = $this->language->get('error_archiving_order_status');
        }
        return $this->errors ? false : true;
    }


}

?>