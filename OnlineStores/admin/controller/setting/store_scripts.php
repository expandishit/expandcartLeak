<?php

class ControllerSettingStoreScripts extends Controller
{
    public function index()
    {

        $this->language->load('setting/setting');

        $this->document->setTitle($this->language->get('store_scripts'));

        $this->data['direction'] = $this->language->get('direction');

        // ===========BreadCrumbs & Data===========

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('store_scripts'), 'href' => $this->url->link('setting/store_scripts'))
        );

        $this->data['config_google_analytics'] = $this->config->get('config_google_analytics');
        $this->data['config_body_scripts'] = $this->config->get('config_body_scripts');

        $this->data['action'] = $this->url->link('setting/store_scripts/save');

        // ===========/BreadCrumbs & Data===========

        $this->template = 'setting/store_scripts.expand';
        $this->base = "common/base";
                
        $this->response->setOutput($this->render_ecwig());
    }

    public function save()
    {
        if ( $this->request->server['REQUEST_METHOD'] != 'POST' )
        {
            return;
        }

        $this->load->model('setting/setting');
        $this->load->model('setting/audit_trail');
        $pageStatus = $this->model_setting_audit_trail->getPageStatus("advanced_setting");
        //$postData = str_replace('\r\n','' ,$this->request->post);
        if($pageStatus){
        $oldValue= $this->model_setting_setting->getSetting('config');
            // add data to log_history

            $log_history['action'] = 'updateCustomCode';
            $log_history['reference_id'] = NULL;
            $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
            $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
            $log_history['type'] = 'advanced_setting';
            $this->load->model('loghistory/histories');
            $this->model_loghistory_histories->addHistory($log_history);
        }
        $this->model_setting_setting->insertUpdateSettingSecured('config', $this->request->post);

        $this->language->load('setting/setting');

        $result_json['success'] = '1';
        $result_json['success_msg'] = $this->session->data['success'] = $this->language->get('text_success');

        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
