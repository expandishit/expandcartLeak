<?php

// use FacebookAds\Api;
// use FacebookAds\Logger\CurlLogger;
// use FacebookAds\Object\AdAccount;
// use FacebookAds\Object\Campaign;
// use FacebookAds\Object\Fields\CampaignFields;

class ControllerSettingDropna extends Controller {
    private $error = array();

    public function index() {

        // $app_id = "1234";
        // $app_secret = "1234";
        // $access_token = "1234";
        // $account_id = "act_1234";

        // Api::init($app_id, $app_secret, $access_token);

        // $account = new AdAccount($account_id);
        // $cursor = $account->getCampaigns();

        // // Loop over objects
        // foreach ($cursor as $campaign) {
        //   echo $campaign->{CampaignFields::NAME}.PHP_EOL;
        // }
        // exit;

        $this->language->load('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['direction'] = $this->language->get('direction');

        // $this->load->model('setting/setting');

        // if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
        //     $result_json = array(
        //         'success' => '0',
        //         'errors' => array(),
        //         'success_msg' => ''
        //     );

        //     if ($this->validate()) {
        //         $this->model_setting_setting->insertUpdateSetting('config', $this->request->post);

        //         $result_json['success'] = '1';
        //         $result_json['success_msg'] = $this->language->get('text_success');
        //     } else {
        //         $result_json['success'] = '0';
        //         $result_json['error'] = $this->error;
        //     }

        //     $this->response->setOutput(json_encode($result_json));
        //     return;
        // }

        $this->load->model("api/clients");
        $checkExists = $this->model_api_clients->getDropnaClient();
        if($checkExists){
            $this->data['dropnaExists'] = true;
            $this->data['store_code'] = $checkExists['store_code'];
            $this->data['client_id']  = $checkExists['client_id'];
        }else{
            $this->data['dropnaExists'] = false;
        }


        $this->data['breadcrumbs'] = array(
            array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')),
            array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('setting/advanced_integration'))
        );
        $this->data['action'] = DROPNA_DOMAIN.'client/login';
        $this->template = 'setting/dropna.expand';
        $this->base = "common/base";

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

        if (!$this->validate()) {
            $result_json['success'] = '0';
            $result_json['error'] = $this->error;
            $this->response->setOutput(json_encode($result_json));
            return;
        }
       
        $this->load->model("api/clients");

        $checkExists = $this->model_api_clients->getDropnaClient();

        if($checkExists){
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_dropna_exists');
            
            $this->response->setOutput(json_encode($result_json));
            return;
        }else{
            $clientId = $this->model_api_clients->generateClientId();
            $secretKey = $this->model_api_clients->generateSecretKey($clientId);

            if($clientId && $secretKey) {
                $id = $this->model_api_clients->storeCustomClient($clientId, $secretKey, 1,'dropna');
            }
        }
        
        if ($id) {

            try {
                $url = DROPNA_DOMAIN.'client/storeSettings/addAuth';
                $postfields['client_secret'] = $secretKey;
                $postfields['client_id'] = $clientId;
                $postfields['store_code'] = STORECODE;
                //Insert store categories after registeration
                $this->load->model('catalog/category');
                $postfields['categories'] = $this->model_catalog_category->getCategoriesWithDesc();

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 100);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $data = json_decode(curl_exec($ch));
                curl_close($ch);
                $status = $data->status;
            } catch (Exception $e) {}

            $resData  = array('client_id' => $clientId, 'store_code' => STORECODE);
            $result_json['success'] = '1';
            $result_json['data'] = $resData;
            $result_json['success_msg'] = $this->language->get('text_generate_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_generate_error');
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
}
?>