<?php



class ControllerCatalogReviewOptions extends Controller {

    private $error = [];
    public function index() {
        $this->language->load('catalog/review_options');
        $this->template = 'catalog/review_options_list.expand';
        $this->base = 'common/base';
        $this->response->setOutput($this->render_ecwig());
    }
    public function insert(){
        $this->load->model('localisation/language');
        $this->load->model('catalog/review_options');

        $this->language->load('catalog/review_options');
        $this->language->load('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $this->data['languages'] = $languages;

        $this->language->load('catalog/review_options');
        $this->template = 'catalog/review_options_form.expand';
        $this->base = 'common/base';

        if($this->request->server['REQUEST_METHOD'] == 'POST'){
            // var_dump($this->request->post);

            $data = $this->request->post;
            foreach ($this->request->post['option_name'] as $language_id => $value )
            {
                if ((utf8_strlen(ltrim($value['name'] ," "))< 2) || (utf8_strlen(ltrim($value['name'] ," ")) > 255))
                    $this->error['name_'.$language_id] = $this->language->get('error_name');
            }

            if(empty($this->error)){
                $this->model_catalog_review_options->insert($data);
                $json_data = [
                    'success' => '1',
                    'success_msg' => $this->language->get('text_success'),
                ];    

            }else{
                $json_data = [
                    'success' => '0',
                    'errors' => $this->error,
                ];    
            }
    

            $this->response->setOutput(json_encode($json_data));


        }else{
            $this->response->setOutput($this->render_ecwig());

        }
    }
    public function dtHandler(){
        $this->load->model('setting/setting');
        $this->load->model('catalog/review_options');
        $language_id = $this->config->get('config_language_id');
        $options_list = $this->model_catalog_review_options->getList($language_id);

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => 0,
            "recordsFiltered" => null,
            "data" => $options_list
        );

        $this->response->setOutput(json_encode($json_data));
    }

    public function dtUpdateStatus(){
        $this->load->model('catalog/review_options');
        $data = $this->request->post;
        $updateStatus = $this->model_catalog_review_options->dtUpdateStatus($data['id'],$data['status']);
        if($updateStatus){
            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_updated_successfully');
    
        }else{
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }

        $this->response->setOutput(json_encode($response));
    }

    public function dtDelete(){
        $this->load->model('catalog/review_options');
        $data = $this->request->post['selected'];
        if(isset($data) && !empty($data)){

            foreach($data as $option_id){
                $this->model_catalog_review_options->dtDelete($option_id);
            }
            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');

        }else{
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;

        }
        $this->response->setOutput(json_encode($response));

    }

    public function edit(){
        $this->load->model('catalog/review_options');
        $this->load->model('localisation/language');
        $this->language->load('catalog/review_options');
        $this->language->load('localisation/language');

        $data = $this->request->get;
        if(isset($data['option_id']) && !empty($data['option_id'])){
            $languages = $this->model_localisation_language->getLanguages();
            $this->data['languages'] = $languages;
            $this->data['review_options'] = $this->model_catalog_review_options->getOptionById($data['option_id']);
            $this->data['review_options'] = array_merge(array(0),$this->data['review_options']);
            $this->data['status'] = $this->model_catalog_review_options->getStatus($data['option_id']);
            $this->language->load('catalog/review_options');
            $this->template = 'catalog/review_options_form.expand';
            $this->base = 'common/base';
            $this->response->setOutput($this->render_ecwig());    
        }

        if($this->request->server['REQUEST_METHOD'] == 'POST'){
            $newData = $this->request->post;

            $updateStatus = $this->model_catalog_review_options->update($newData,$data['option_id']);


            $json_data = [
                'success' => '1',
                'success_msg' => $this->language->get('text_success'),
            ];

            $this->response->setOutput(json_encode($json_data));
        }
    }
}










?>