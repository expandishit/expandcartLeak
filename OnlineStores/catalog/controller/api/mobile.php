<?php
require_once('jwt_helper.php');
class ControllerApiMobile extends Controller
{
    public function GetLanguages()
    {
        $this->load->model('setting/mobile');
        $json = array();
        $params = json_decode(file_get_contents('php://input'));
        $encodedtoken = $params->token;
        $this->load->model('account/api');
        
        $output_value = "";

        if (!isset($this->session->data['api_id']))
        {
            $json['error']['warning'] = $this->language->get('error_permission');
            $output_value = json_encode($this->utf8_string_array_encode($json));
        }
        else
        {
            $languages = $this->model_setting_mobile->GetLanguages();
            $output_value = json_encode($languages);
        }
        $this->response->setOutput($output_value);
    }

    public function Settings() {
        $this->getPage('settings');
    }

    public function HomePage() {
        $params = json_decode(file_get_contents('php://input'));
        $option = $params->with_product_options ?? 0;
        $this->getPage('home',$option);
    }

    public function CategoriesPage() {
        $params = json_decode(file_get_contents('php://input'));
        $options = [];
        $options['limit'] = $params->limit ?? 10;
        $options['withProducts'] = $params->withProducts ?? 0;
        $options['sort'] = $params->sort ?? null;
        $this->getPage('categories', false, $options);
    }

    public function ContactusPage() {
        $this->getPage('contactus');
    }

    /**
     * Endpoint to get any page, home/categories/settings/contactus/search...
     *
     * @param body String "page"
     *
     * return json
     */
    public function gtPage() {
        $params = json_decode(file_get_contents('php://input'));
        $pageCodeName = $params->page;
        $this->getPage($pageCodeName);
    }

    private function getPage($pageCodeName,$with_product_options=false, $categories_options = []) {
        try {
            $this->load->model('setting/mobile');
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;

            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $json = $this->model_setting_mobile->getPageSections($pageCodeName,$with_product_options, $categories_options);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');
                //$json['cookie'] = $_COOKIE[$this->session->getId()];
            }
            $val = json_encode($this->utf8_string_array_encode($json));
            $this->response->setOutput($val);
        } catch (Exception $e) {

        }
    }

    function utf8_string_array_encode(&$array){
        $func = function(&$value,&$key){
            if(is_string($value)){
                //$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
                //$value = utf8_encode($value);
                $value = iconv(mb_detect_encoding($value, mb_detect_order(), true), "UTF-8", $value);
            }
//            if(is_string($key)){
//                //$key = htmlentities($key, ENT_QUOTES, 'UTF-8');
//                $key = utf8_encode($key);
//            }
            if(is_array($value)){
                $this->utf8_string_array_encode($value);
            }
        };
        array_walk($array,$func);
        return $array;
    }
}
