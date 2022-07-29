<?php
class ControllerSettingStoreAffiliates extends Controller {
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
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/store_affiliates'))
        );

        $this->data['action'] = $this->url->link('setting/store_affiliates');
        
        $this->data['cancel'] = $this->url->link('setting/store_affiliates');

        // Loads
        $this->load->model('localisation/stock_status');
        $this->load->model('catalog/information');
        
        // Datas
        $this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
        $this->data['informations'] = $this->model_catalog_information->getInformations();

        $this->value_from_post_or_config([
            'config_affiliate_id',
            'config_commission'
        ]);

        $this->template = 'setting/store_affiliates.expand';
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
        if (!$this->user->hasPermission('modify', 'setting/store_affiliates')) {
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