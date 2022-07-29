<?php
/*
  @Controller: Fit and shop Controller.
  @Author: Hoda Sheir.
  @Version: 1.1.0
*/
class ControllerModuleFitAndShop extends Controller 
{
    protected $errors = [];
    public function index() {
        $this->language->load('module/fit_and_shop');
        $this->load->model('module/fit_and_shop');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->template = 'module/fit_and_shop.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

		$data = array(
            'measurments_categories' => $this->model_module_fit_and_shop->get_categories(), 
            'button_save'    => $this->language->get('button_save'),
            'button_cancel'  => $this->language->get('button_cancel'),
            'action'         => $this->url->link('module/fit_and_shop/updateSettings', '', 'SSL'),
            'cancel'         => $this->url.'marketplace/home',
            'fit_and_shop_import_categories_ajax_url' => $this->url->link( 'module/fit_and_shop/ajax_import_categories', '', true),
        );

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_modules'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/fit_and_shop', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->data=$data;
        $this->data['settingsData'] = $this->model_module_fit_and_shop->getSettings();
        if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ) {
            $this->data['ajax_loader']  = HTTPS_SERVER .'view/image/knawat_ajax_loader.gif';
        }else{
            $this->data['ajax_loader']  = HTTP_SERVER .'view/image/knawat_ajax_loader.gif';
        }
        $this->response->setOutput($this->render());
    }

    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
            $result_json['success'] = '0';
            $result_json['errors'] = 'Invalid Request';
        }else{
            $this->language->load('module/fit_and_shop');
            $data = $this->request->post['fit_and_shop'];
            if ( !$data['apikey'] )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;
                $this->response->setOutput(json_encode($result_json));
                return;
            }
            $this->load->model('module/fit_and_shop');

            $this->model_module_fit_and_shop->updateSettings(['fit_and_shop' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function install()
    {
        $this->load->model('module/fit_and_shop');
        $this->model_module_fit_and_shop->install();
    }

    public function uninstall()
    {
        $this->load->model('module/fit_and_shop');
        $this->model_module_fit_and_shop->uninstall();
    }

    public function ajax_import_categories(){
        $this->load->model('module/fit_and_shop');
        $settingsData = $this->model_module_fit_and_shop->getSettings();
        $apikey = $settingsData['apikey'];
        $status = $settingsData['status'];
        if($apikey && $status){
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://app.fitandshop.me/apiv2/Store/get_categories",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => http_build_query($apikey) ,
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,true);
            $json_data = '';
            if( is_array($response) && count($response['categories']) > 0){
                foreach($response['categories'] as $key=>$category){
                    if(!$this->model_module_fit_and_shop->checkCategoryExists($category['id'])){
                        $this->model_module_fit_and_shop->add_category($category);
                        $json_data.= '<td>'.$category['name'].'</td>';

                    }
                }
            }
            echo $json_data; exit;
        }

    }

    public function ajax_get_collections(){
        $this->load->model('module/fit_and_shop');
        $settingsData = $this->model_module_fit_and_shop->getSettings();
        $this->model_module_fit_and_shop->getCollections($settingsData);
    }
}


?> 