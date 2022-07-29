<?php
class ControllerSettingStoreTaxes extends Controller {
    private $error = array();
 
    public function index() {
        $this->language->load('setting/setting'); 

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');
        
        $this->load->model('setting/setting');
        
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

                    $log_history['action'] = 'updateTaxOptions';
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
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_taxes'))
        );

        //Get all tax classes
        $this->load->model('localisation/tax_class');
        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->data['action'] = $this->url->link('setting/store_taxes');
        
        $this->data['cancel'] = $this->url->link('setting/store_taxes');

        $this->value_from_post_or_config([
            'config_vat',
            'config_tax',
            'config_tax_default',
            'config_tax_customer',
           // 'config_tax_number',
            'default_tax_class'
        ]);

        $this->template = 'setting/store_taxes.expand';
        $this->base = "common/base";
                
        $this->response->setOutput($this->render_ecwig());
    }

    private function value_from_post_or_config($array)
    {
        foreach ($array as $elem)
        {
            $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
        }
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'setting/store_taxes')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
            
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
?>
