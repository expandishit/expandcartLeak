<?php

class ControllerModuleConvertedin extends Controller
{
    protected $errors = [];

    public function index()
    {
        $this->language->load('setting/setting');
        $this->language->load('module/convertedin');

        $this->document->setTitle($this->language->get('convertedin_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link(
                'marketplace/home',
                '',
                'SSL'
            ),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('convertedin_heading_title'),
            'href'      => $this->url->link('module/convertedin', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->load->model('setting/setting');

        $this->data['general_data'] = $this->model_setting_setting->getSetting('config');
        $this->data['domain'] = DOMAINNAME;

        $this->data['appSettings'] = $this->config->get('convertedin');
        if(is_array($this->data['appSettings'])){
            $this->data['login_url'] = "https://app.converted.in/expandcart/autologin?client_id=".$this->data['appSettings']['client_id'];
        }


        $this->template = 'module/convertedin/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function register(){
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['errors'] = 'Invalid Request';
        }else{

            if ( ! $this->validateRegister() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));
                return;
            }

            // generate token
            $tokenData = $this->api_generateToken();
            if (  $tokenData == FALSE )
            {
                $result_json['success'] = '0';
                $result_json['errors'][] = $this->language->get('error_generate_token');

                $this->response->setOutput(json_encode($result_json));
                return;
            }
            // define url
            $url = "https://app.converted.in/api/expandcart/getStoreCredentials";

            $postData = $this->request->post['register_convertedin'];

            $request  = $this->sendCurlRequest($url,array_merge($postData,$tokenData));
            if($request->code != 200){

                $result_json['success'] = '0';

                $result_json['errors'][] = $request->message;

                $this->response->setOutput(json_encode($result_json));
                return;
            }
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting(
                'convertedin', ['convertedin' => $tokenData]
            );

            $result_json['success'] = '1';
            $result_json['success_msg'] = $request->message;
            $this->response->setOutput(json_encode($result_json));
            return;

        }

    }

    private function api_generateToken() {
        $this->load->model("api/clients");

        $clientId = $this->model_api_clients->generateClientId();

        $secretKey = $this->model_api_clients->generateSecretKey($clientId);

        if($clientId && $secretKey) {
            $id = $this->model_api_clients->storeClient($clientId, $secretKey, 1);
        }

        if ($id) {
            $response['client_id'] = $clientId;
            $response['client_secret'] = $secretKey;
            return $response;
        }
        return FALSE;

    }

    private function sendCurlRequest($url,$data = null){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response);

    }

    private function validateRegister()
    {
        $postData = $this->request->post['register_convertedin'];
        $this->language->load('module/convertedin');


        if (  !isset($postData['admin_name']) ||  empty($postData['admin_name']) )
        {
            $this->errors[] = $this->language->get('error_name_required');
        }


        if (  !isset($postData['admin_phone']) ||  empty($postData['admin_phone']) )
        {
            $this->errors[] = $this->language->get('error_phone_number_required');
        }

        if (  !isset($postData['admin_email']) ||  empty($postData['admin_email']) )
        {
            $this->errors[] = $this->language->get('error_email_required');
        }

        if (  !isset($postData['store_url']) ||  empty($postData['store_url']) )
        {
            $this->errors[] = $this->language->get('error_store_url_required');
        }


        return $this->errors ? false : true;
    }

}
