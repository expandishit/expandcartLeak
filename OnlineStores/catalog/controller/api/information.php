<?php
require_once('jwt_helper.php');
class ControllerApiInformation extends Controller
{
    public function GetContactInfo(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $json['StoreEmail'] = $this->config->get('config_email');
                $json['StorePhone'] = $this->config->get('config_telephone');
                $json['StoreAddress'] = $this->config->get('config_address');
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {

        }
    }

    public function GetInfoPage(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->load->model('setting/mobile');
                $mobilesettings = $this->model_setting_mobile->getPageSections("settings");
                if($mobilesettings) {
                    $infoPage_id = $mobilesettings["infopageId"];
                } else {
                    $infoPage_id = $this->config->get('mapp_infopage');
                }

                if ($infoPage_id != 0) {
                    $this->load->model('catalog/information');

                    $information_info = $this->model_catalog_information->getInformation($infoPage_id);

                    if ($information_info) {
                        $json['InfoPage'] = array(
                            'Title' => $information_info['title'],
                            'Description' => html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8'),
                        );
                    }
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {

        }
    }
}