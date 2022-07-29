<?php
class ControllerSettingIntegration extends Controller {
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
                $result_json['error'] = $this->error;
            }

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/advanced_integration'))
        );

        $this->data['action'] = $this->url->link('setting/integration');

        $this->data['cancel'] = $this->url->link('setting/integration');

        $this->template = 'setting/integration.expand';
        $this->base = "common/base";

        $this->data['config_webhook_url']=$this->model_setting_setting->getSetting('config_webhook_url')['url'];
        $this->data['order_webhook_url']=$this->model_setting_setting->getSetting('config')['config_webhook_url'];

        $this->response->setOutput($this->render_ecwig());
    }

    public function api() {
    //$this->language->load('setting/integration');

    //$this->document->setTitle($this->language->get('heading_title'));

    //$this->data['direction'] = $this->language->get('direction');

//    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
//            $result_json = array(
//                'success' => '0',
//                'errors' => array(),
//                'success_msg' => ''
//            );
//
//            if ($this->validate()) {
//                $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);
//
//                $result_json['success'] = '1';
//                $result_json['success_msg'] = $this->language->get('text_success');
//            } else {
//                $result_json['success'] = '0';
//                $result_json['error'] = $this->error;
//            }
//
//            $this->response->setOutput(json_encode($result_json));
//            return;
//        }

        $this->load->model("api/clients");

        $apiclients = $this->model_api_clients->getAllClients();

        $this->response->setOutput(json_encode(array("data" =>$apiclients)));
        return;

    }

    public function api_generateToken() {
        $this->load->model("api/clients");

        $clientId = $this->model_api_clients->generateClientId();

        $secretKey = $this->model_api_clients->generateSecretKey($clientId);

        if($clientId && $secretKey) {
            $id = $this->model_api_clients->storeClient($clientId, $secretKey, 1);
        }

        if ($id) {
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_generate_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_generate_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }

    public function api_updateTokenStatus() {
        $this->load->model("api/clients");
        if(isset($this->request->post["clientId"]) && isset($this->request->post["status"])) {
            $clientId = $this->request->post["clientId"];
            $status = $this->request->post["status"];
            $this->model_api_clients->updateStatus($clientId, $status);
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_tokenstatus_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_tokenstatus_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }

    public function api_removeToken() {
        $this->load->model("api/clients");
        if(isset($this->request->post["clientId"])) {
            $clientId = $this->request->post["clientId"];
            // check if convertedin app installed and customer delete token remove token from app
            if( \Extension::isInstalled('convertedin') ){
                $client_data = $this->model_api_clients->getClientById($clientId);
                $appData = $this->config->get('convertedin');
                if($appData['client_id'] == $client_data['client_id']){
                    $this->load->model('setting/setting');
                    $this->model_setting_setting->deleteSetting('convertedin');
                }
            }
            $this->model_api_clients->deleteClient($clientId);

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_tokenremove_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_tokenremove_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;

    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'setting/advanced_integration')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['config_name']) {
            $this->error['config_name'] = $this->language->get('error_name');
        }

        if ((utf8_strlen($this->request->post['config_address']) < 3) || (utf8_strlen($this->request->post['config_address']) > 256)) {
            $this->error['config_address'] = $this->language->get('error_address');
        }

        if ((utf8_strlen($this->request->post['config_email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['config_email'])) {
            $this->error['config_email'] = $this->language->get('error_email');
        }

        if ((utf8_strlen($this->request->post['config_telephone']) < 3) || (utf8_strlen($this->request->post['config_telephone']) > 32)) {
            $this->error['config_telephone'] = $this->language->get('error_telephone');
        }

        if (!$this->request->post['config_title']) {
            $this->error['config_title'] = $this->language->get('error_title');
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


    private $feeds = array(
        'articles_google_base',
        'articles_google_sitemap',
//        'google_base',
        'google_sitemap'
    );
    public function feed() {
        $this->language->load('setting/feed');
        $data = array();

        foreach($this->feeds as $feed) {
            $data[] = array(
                "id" => $feed,
                "name" => $this->language->get('text_' . $feed),
                "status" => $this->config->get($feed . '_status'),
                "url" => HTTP_CATALOG . 'index.php?route=feed/' . $feed
            );
        }

        $this->response->setOutput(json_encode(array("data" =>$data)));
        return;

    }

    public function feed_updateStatus() {
        $this->language->load('setting/feed');
        $this->load->model("setting/setting");
        if(isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $feedId = $this->request->post["id"];
            $status = $this->request->post["status"];
            $this->model_setting_setting->editSetting($feedId, array($feedId.'_status' => $status));
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_status_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_status_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function update_webhook_url() {
        $this->language->load('setting/integration');
        $this->load->model("setting/setting");
        if(isset($this->request->post["config_webhook_url"])) {
            $config_webhook_url = $this->request->post["config_webhook_url"];
            $this->model_setting_setting->insertUpdateSetting("config_webhook_url", array("url"=>$config_webhook_url));

            //order_webhook_url
            $order_webhook_url = $this->request->post["order_webhook_url"];
            $this->model_setting_setting->insertUpdateSetting("config", array("config_webhook_url"=>$order_webhook_url));

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_webhook_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_webhook_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
?>