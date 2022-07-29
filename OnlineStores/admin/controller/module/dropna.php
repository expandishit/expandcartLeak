<?php

class ControllerModuleDropna extends Controller
{
    public function index()
    {
        $this->language->load('module/dropna');

        $this->load->model('module/dropna');

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
            'href'      => $this->url->link('module/dropna', '', 'SSL'),
            'separator' => ' :: '
        );
        
        $data['defaultCurrency'] = $this->config->get('config_currency');
        
        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_settings'] = $this->language->get('text_settings');

        $data['dropna'] = $this->config->get('dropna');

        // $this->load->model('api/clients');
        // if(!$this->model_api_clients->getDropnaClient()){
        //     $data['dropna_url'] = $this->url->link('setting/dropna', '', 'SSL');
        //     $data['dropna_warning'] = true;
        //     $data['text_dropna_warning'] = $this->language->get('text_dropna_warning');
        //     $data['text_dropna_url']     = $this->language->get('text_dropna_url');
        // }

        $this->load->model("api/clients");
        $checkExists = $this->model_api_clients->getDropnaClient();
        
        if($checkExists){
            $data['dropnaExists'] = true;
            $data['store_code'] = $checkExists['store_code'];
            $data['client_id']  = $checkExists['client_id'];
        }else{
            $data['dropnaExists'] = false;
        }
        $data['dropnaAction'] = DROPNA_DOMAIN.'client/login';


        $this->template = 'module/dropna.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $data['action'] = $this->url->link('module/dropna/updateSettings', '', 'SSL');
        $data['cancel'] = $this->url.'marketplace/home';

        $this->data = array_merge($this->data, $data);

        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        $this->language->load('module/dropna');
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['error'] = $this->error;
        }else{
            $this->load->model('module/dropna');
            $data = $this->request->post['dropna'];
            $this->model_module_dropna->updateSettings(['dropna' => $data]);
            $this->session->data['success'] = $this->language->get('text_settings_success');

            //Update Dropna Info
            $dropna_settings = $this->config->get('dropna');
            $this->load->model("api/clients");
            $checkExists = $this->model_api_clients->getDropnaClient();
            if( $checkExists && 
                    (
                        $data['contact_name']  != $dropna_settings['contact_name'] || 
                        $data['contact_mail']  != $dropna_settings['contact_mail'] || 
                        $data['contact_phone'] != $dropna_settings['contact_phone']
                    )
                ){
                /// Dropna API Call
                $postfields['client_name']   = $data['contact_name'];
                $postfields['client_mail']   = $data['contact_mail'];
                $postfields['client_phone']  = $data['contact_phone'];
                $postfields['apikey']        = DROPNA_APIKEY;
                //$postfields['client_api_id'] = $checkExists['id'];
                $postfields['client_secret'] = $checkExists['client_secret'];
                $postfields['client_id']     = $checkExists['client_id'];
                $postfields['store_code']    = $checkExists['store_code'];
                $dropna_api_call = $this->dropna_api_call('api/v1/updateAuth', $postfields);
                ///////////////////
            }
            ///////////////////////

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function api_generateToken() {

        if (!$this->validate()) {
            $result_json['status'] = '0';
            $result_json['error_msg'] = $this->error;
            $this->response->setOutput(json_encode($result_json));
            return;
        }
        $this->language->load('module/dropna');
        $this->load->model("api/clients");

        $checkExists = $this->model_api_clients->getDropnaClient();

        if($checkExists){
            $result_json['status'] = '1';
            $result_json['success_msg'] = $this->language->get('text_dropna_exists');
            
            $this->response->setOutput(json_encode($result_json));
            return;
        }else{
            $clientId  = $this->model_api_clients->generateClientId();
            $secretKey = $this->model_api_clients->generateSecretKey($clientId);

            if($clientId && $secretKey) {
                $id = $this->model_api_clients->storeCustomClient($clientId, $secretKey, 1,'dropna');
            }
        }
        if ($id) {
            try {
                $dropna_settings = $this->config->get('dropna');
                /// Dropna API Call
                $postfields['apikey']        = DROPNA_APIKEY;
                $postfields['client_api_id'] = $id;
                $postfields['client_name']   = $dropna_settings['contact_name'];
                $postfields['client_mail']   = $dropna_settings['contact_mail'];
                $postfields['client_phone']  = $dropna_settings['contact_phone'];
                $postfields['client_secret'] = $secretKey;
                $postfields['client_id']  = $clientId;
                $postfields['store_code'] = STORECODE;
                $dropna_api_call = $this->dropna_api_call('api/v1/addAuth', $postfields);
                ///////////////////
                $result = json_decode($dropna_api_call);
                
                $status = $result->status;
            } catch (Exception $e) {}

            if($status == 'success'){
                $resData  = array('client_id' => $clientId, 'store_code' => STORECODE);
                $result_json['status'] = '1';
                $result_json['data'] = $resData;
                $result_json['success_msg'] = $this->language->get('text_generate_success');
            }else{
                $this->model_api_clients->deleteDropnaClient($id);
                $result_json['status'] = '0';
                $result_json['error_msg'] = $exec.' Level 1';
            }
        } else {
            $this->model_api_clients->deleteDropnaClient($id);
            $result_json['status'] = '0';
            $result_json['error_msg'] = $this->language->get('text_generate_error').' Level 2';
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'setting/dropna')) {
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

    public function validate_fields(){
        $this->language->load('module/dropna');
        
        $settings = $this->config->get('dropna');
        if($settings['status'] == 1 && $settings['contact_name'] != '' && $settings['contact_mail'] != '' && $settings['contact_phone'] != '' ){
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_dropna_auth_success');
        }else{
            $result_json['success'] = '0';
            $result_json['errors']['error'] = $this->language->get('text_dropna_auth_failed');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    private function dropna_api_call($url_action, $data){
        $url = DROPNA_DOMAIN.$url_action;//'api/v1/addAuth';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $exec = curl_exec($ch);
        curl_close($ch);
        return $exec;
    }
}
