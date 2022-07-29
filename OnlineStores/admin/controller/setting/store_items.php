<?php
class ControllerSettingStoreItems extends Controller {
    private $error = array();
 
    public function index() {
        $this->language->load('setting/setting'); 

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');
        
        $this->load->model('setting/setting');
        $this->load->model('tool/image');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $result_json = array(
                'success' => '0',
                'errors' => array(),
                'success_msg' => ''
            );

            if ($this->validate()) {
                $this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("advanced_setting");
                if($pageStatus){
                $oldValue= $this->model_setting_setting->getSetting('config');
                    // add data to log_history

                    $log_history['action'] = 'updateInterfaceCustomization';
                    $log_history['reference_id'] = NULL;
                    $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
                    $log_history['type'] = 'advanced_setting';
                    $this->load->model('loghistory/histories');
                    $this->model_loghistory_histories->addHistory($log_history);
                }
                $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            } else {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_items'))
        );

        $this->data['action'] = $this->url->link('setting/store_items');
        
        $this->data['cancel'] = $this->url->link('setting/store_items');

        $this->load->model('localisation/language');

        $this->data['setting'] = $this->model_setting_setting->getSetting('config');
        $this->data['config_catalog_limit'] = $this->config->get('config_catalog_limit');
        $this->data['config_admin_limit'] = $this->config->get('config_admin_limit');
        $this->data['no_image'] = $this->model_tool_image->resize($this->config->get('no_image'),200,200);
        $this->data['product_image_without_image'] = $this->model_tool_image->resize($this->config->get('product_image_without_image'),200,200);
        $this->template = 'setting/store_items.expand';
        $this->base = "common/base";
                
        $this->response->setOutput($this->render_ecwig());
    }

    
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'setting/store_items')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['config_catalog_limit']) {
            $this->error['config_catalog_limit'] = $this->language->get('error_limit');
        }

        if (!$this->request->post['config_admin_limit']) {
            $this->error['config_admin_limit'] = $this->language->get('error_limit');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }
}
?>
