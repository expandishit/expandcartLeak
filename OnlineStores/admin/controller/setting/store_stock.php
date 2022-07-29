<?php
class ControllerSettingStoreStock extends Controller {
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
                
                $this->processStockStatusDisplayBadgeField();

                //Get first config options
                $first_values = [];
                $first_configs = [
                    'config_stock_status_id',
                    'config_stock_display',
                    'config_stock_warning',
                    'config_stock_checkout',
                    'config_custom_stock_status_colors',
                    'config_stock_status_display_badge',
                    'config_instock_status_id'
                ];
                
                foreach($this->request->post as $key=>$val){
                    if(in_array($key,$first_configs)){
                        $first_values[$key]=$val;
                    }
                }

                //Update normal stock status settings
                $this->model_setting_setting->insertUpdateSetting('config', $first_values);
                
                //Get the colors for each stock status
                //And update the stock status colors
                $this->model_setting_setting->UpdateStockStatusColor(array_diff($this->request->post,$first_values));

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
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_stock'))
        );

        $this->data['action'] = $this->url->link('setting/store_stock');
        
        $this->data['cancel'] = $this->url->link('setting/store_stock');

        // Loads
        $this->load->model('localisation/stock_status');
        
        // Datas
        $this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

        $this->value_from_post_or_config([
            'config_stock_display',
            'config_stock_warning',
            'config_stock_checkout',
            'config_stock_status_id',
            'config_stock_status_display_badge',
            'config_custom_stock_status_colors',
            'config_instock_status_id'
        ]);

        $this->template = 'setting/store_stock.expand';
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
        if (!$this->user->hasPermission('modify', 'setting/store_stock')) {
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

    private function processStockStatusDisplayBadgeField()
    {
        if(!isset($this->request->post['config_stock_status_display_badge'])){
            $this->request->post['config_stock_status_display_badge'] = [];
        }
    }
}
?>