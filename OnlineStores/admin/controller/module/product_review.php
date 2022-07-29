<?php

/**
 *   Controller Class for product_review Module
 *
 */
class ControllerModuleProductReview extends Controller
{
    public $route = "module/product_review";
    public $module = "product_review";
    private $error = [];

    public function index()
    {
        if (!\Extension::isInstalled($this->module) || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            return $this->forward("error/permission");
        }

        $this->load->model("setting/setting");
        $this->load->language("module/{$this->module}");

        $this->data["breadcrumbs"] = array();

        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/home", "", "SSL"),
            "separator" => false
        );

        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_modules"),
            "href" => $this->url->link(
                "marketplace/home",
                "",
                "SSL"
            ),
            "separator" => " :: "
        );

        $this->data["breadcrumbs"][] = array(
            "text" => $this->language->get("{$this->module}_heading_title"),
            "href" => $this->url->link("module/{$this->module}", "", "SSL"),
            "separator" => " :: "
        );

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['settings_content'] = $this->getChild("module/{$this->module}/settings");
        $this->data['reviews_content'] = $this->getChild("module/{$this->module}/reviews");
        $this->data['options_content'] = $this->getChild("module/{$this->module}/options");
        $this->data['active'] = $_GET['active'];

        $this->template = "module/{$this->module}/index.expand";

        $this->children = array(
            "common/header",
            "common/footer",
        );

        $this->response->setOutput($this->render());
    }

    public function settings()
    {
        if (!\Extension::isInstalled($this->module) || !$this->user->hasPermission("modify", "module/{$this->module}")) {
            return $this->forward("error/permission");
        }

        $this->load->model("setting/setting");
        $this->load->language("module/{$this->module}");

        $this->data["submit_link"] = $this->url->link("module/{$this->module}/saveSettings", "", "SSL");

        $arrValues = [
            'config_review_status',
            'config_review_auto_approve',
        ];

        $this->value_from_post_or_config($arrValues);

        $this->template = "module/{$this->module}/settings.expand";

        $this->children = array(
            "common/header",
            "common/footer",
        );

        $this->response->setOutput($this->render());
    }

    public function saveSettings()
    {
        $this->language->load('module/product_review');

        if (!$this->validate()) {
            $json['success'] = '0';
            $json['errors'] = $this->errors;
        } else {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('config', $this->request->post);
            $json['success'] = '1';
            $json['success_msg'] = $this->language->get('success');
        }

        $this->response->setOutput(json_encode($json));
    }

    private function validate()
    {
        return empty($this->errors);
    }

    private function value_from_post_or_config($array)
    {
        foreach ($array as $elem) {
            $this->data[$elem] = $this->request->post[$elem] ?: $this->config->get($elem);
        }
    }

    public function reviews() {
        $this->language->load('catalog/review');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/review');

        $this->getReviewsList();
    }

    public function insert_review()
    {
        $this->language->load('catalog/review');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/review');
        
        $this->load->model('catalog/review_options');

        $this->load->model('catalog/product');

        // $this->data['products'] = $this->model_catalog_product->getProducts();

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validateReviewsForm() )
            {
                $json_data = [
                    'success' => '0',
                    'errors' => $this->error
                ];

                $this->response->setOutput(json_encode($json_data));

                return;
            }
            $review_options_list = $this->model_catalog_review_options->getReviewsOptionList();
            if($review_options_list){
                // group all review options
                foreach($review_options_list as $value){
                    $this->request->post['rate'][$value] = $this->request->post[$value];
                }
                if(isset($this->request->post['rating'])){
                    $this->request->post['rate']['rating'] = $this->request->post['rating'];
                }
            }


            $this->model_catalog_review->addReview($this->request->post);

            $json_data = ['success' => '1', 'success_msg' => $this->language->get('text_success') , 
                'redirect' => '1' ,'to'=> $this->url->link(
                    'module/product_review', 'active=reviews', 'SSL'
            )->format()];

            $this->response->setOutput(json_encode($json_data));
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'module/product_review/insert_review',
                '',
                'SSL'
            )
        ];

        $this->getReviewForm();
    }   

    
    public function update_review()
    {
        
        $this->load->model('catalog/product');
        $this->load->model('catalog/review_options');

        // $this->data['products'] = $this->model_catalog_product->getProducts();

        $this->language->load('catalog/review');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/review');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateReviewsForm() )
            {
                $json_data = [
                    'success' => '0',
                    'errors' => $this->error
                ];

                $this->response->setOutput(json_encode($json_data));
                return;
            }
            $review_options_list = $this->model_catalog_review_options->getReviewsOptionList();
            if($review_options_list){
                // group all review options
                foreach($review_options_list as $value){
                    $this->request->post['rate'][$value] = $this->request->post[$value];
                }
                if(isset($this->request->post['rating'])){
                    $this->request->post['rate']['rating'] = $this->request->post['rating'];
                }
            }
            $this->model_catalog_review->editReview($this->request->get['review_id'], $this->request->post);

            $json_data = [
                'success' => '1',
                'success_msg' => $this->language->get('text_success'),
            ];

            $this->response->setOutput(json_encode($json_data));
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'module/product_review/update_review',
                'review_id=' . $this->request->get['review_id'],
                'SSL'
            )
        ];

        $this->getReviewForm();
    }

    public function delete_review() {
        $this->language->load('catalog/review');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/review');

        if (isset($this->request->post['selected']) && $this->validateReviewDelete()) {
            foreach ($this->request->post['selected'] as $review_id) {
                $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                if($queryRewardPointInstalled->num_rows) {
                    $this->load->model('promotions/reward_points_observer');
                    $this->model_promotions_reward_points_observer->beforeUpdateReview($review_id, null, '3');
                }

                $this->model_catalog_review->deleteReview($review_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->redirect(

                $this->url->link('module/product_review', 'active=reviews&token=' . $this->session->data['token'] . $url, 'SSL')
            );
        }

        $this->getReviewsList();
    }

    protected function getReviewsList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'r.date_added';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->data['insert'] = $this->url->link('module/product_review/insert_review', 'active=reviews&token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['delete'] = $this->url->link('module/product_review/delete_review', 'active=reviews&token=' . $this->session->data['token'] . $url, 'SSL');

        $this->data['reviews'] = array();

        $data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit')
        );

        $review_total = $this->model_catalog_review->getTotalReviews();

        $results = $this->model_catalog_review->getReviews($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('module/product_review/update_review', 'active=reviews&token=' . $this->session->data['token'] . '&review_id=' . $result['review_id'] . $url, 'SSL')
            );

            $this->data['reviews'][] = array(
                'review_id'  => $result['review_id'],
                'name'       => $result['name'],
                'author'     => $result['author'],
                'rating'     => $result['rating'],
                'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'selected'   => isset($this->request->post['selected']) && in_array($result['review_id'], $this->request->post['selected']),
                'action'     => $action
            );
        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->data['sort_product'] = $this->url->link('module/product_review', 'action=reviews&token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
        $this->data['sort_author'] = $this->url->link('module/product_review', 'action=reviews&token=' . $this->session->data['token'] . '&sort=r.author' . $url, 'SSL');
        $this->data['sort_rating'] = $this->url->link('module/product_review', 'action=reviews&token=' . $this->session->data['token'] . '&sort=r.rating' . $url, 'SSL');
        $this->data['sort_status'] = $this->url->link('module/product_review', 'action=reviews&token=' . $this->session->data['token'] . '&sort=r.status' . $url, 'SSL');
        $this->data['sort_date_added'] = $this->url->link('module/product_review', 'action=reviews&token=' . $this->session->data['token'] . '&sort=r.date_added' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $review_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('module/product_review', 'active=reviews&token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        if($review_total == 0 ){
            $this->template = 'module/product_review/reviews/empty.expand';
        }else{
            $this->template = 'module/product_review/reviews/review_list.expand';
        }

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    
    private function getReviewForm()
    {

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/product_review', 'action=reviews&token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['review_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('module/product_review', 'action=reviews&token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        if (!isset($this->request->get['review_id'])) {
            $this->data['action'] = $this->url->link('module/product_review/insert_review', 'active=reviews&token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->data['action'] = $this->url->link('module/product_review/update_review', 'active=reviews&token=' . $this->session->data['token'] . '&review_id=' . $this->request->get['review_id'] . $url, 'SSL');
        }

        $this->data['cancel'] = $this->url->link(
            'module/product_review',
            'active=reviews&token=' . $this->session->data['token'] . $url, 'SSL');

        if (isset($this->request->get['review_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $review_info = $this->model_catalog_review->getReview($this->request->get['review_id']);
            $this->data['selectedProduct'] = ['product_id'=>$review_info['product_id'],'name'=>$review_info['product']];

        }

        $this->data['token'] = $this->session->data['token'];

        $this->load->model('catalog/product');

        if (isset($this->request->post['product_id'])) {
            $this->data['product_id'] = $this->request->post['product_id'];
        } elseif (!empty($review_info)) {
            $this->data['product_id'] = $review_info['product_id'];
        } else {
            $this->data['product_id'] = '';
        }

        if (isset($this->request->post['product'])) {
            $this->data['product'] = $this->request->post['product'];
        } elseif (!empty($review_info)) {
            $this->data['product'] = $review_info['product'];
        } else {
            $this->data['product'] = '';
        }

        if (isset($this->request->post['author'])) {
            $this->data['author'] = $this->request->post['author'];
        } elseif (!empty($review_info)) {
            $this->data['author'] = $review_info['author'];
        } else {
            $this->data['author'] = '';
        }

        if (isset($this->request->post['text'])) {
            $this->data['text'] = $this->request->post['text'];
        } elseif (!empty($review_info)) {
            $this->data['text'] = $review_info['text'];
        } else {
            $this->data['text'] = '';
        }

        if (isset($this->request->post['rating'])) {
            $this->data['rating'] = $this->request->post['rating'];
        } elseif (!empty($review_info)) {
            $this->data['rating'] = $review_info['rating'];
        } else {
            $this->data['rating'] = 2;
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($review_info)) {
            $this->data['status'] = $review_info['status'];
        } else {
            $this->data['status'] = '';
        }
        $this->load->model('catalog/review_options');
        $languageId = $this->config->get('config_language_id');
        $this->data['review_options_text'] = $this->model_catalog_review_options->getReviewsOptionText($languageId);
        $this->data['review_options_rate'] = $this->model_catalog_review_options->getReviewsOptionRate($languageId);
        $this->document->addScript('view/assets/js/core/libraries/jquery_ui/widgets.min.js');
        $this->document->addScript('view/javascript/pages/catalog/review.js');

        $this->template = 'module/product_review/reviews/review_form.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    
    private function validateReviewsForm()
    {
        if ( !$this->user->hasPermission('modify', 'catalog/review') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( ! $this->request->post['product_id'] )
        {
            $this->error['product_id'] = $this->language->get('error_product');
        }
        if ((utf8_strlen(ltrim($this->request->post['author']," "))< 2) || (utf8_strlen(ltrim($this->request->post['author'] ," ")) > 64))
        {
            $this->error['author'] = $this->language->get('error_author');
        }

        // if (utf8_strlen(ltrim($this->request->post['text']," "))< 1)
        // {
        //     $this->error['text'] = $this->language->get('error_text');
        // }

        // $languageId = $this->config->get('config_language_id');
        // $review_options_rate = $this->model_catalog_review_options->getReviewsOptionRate($languageId);
        // if ( !isset($this->request->post['rating']) && !$review_options_rate )
        // {
        //     $this->error['rating'] = $this->language->get('error_rating');
        // }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateReviewDelete() {
        if (!$this->user->hasPermission('modify', 'catalog/review')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function reviewsDtHandler()
    {
        $this->load->model('catalog/review');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'review_id',
            1 => 'name',
            2 => 'author',
            3 => 'rating',
            4 => 'status',
            5 => 'date_added'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_catalog_review->dtHandler([
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];


        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function reviewsDtDelete()
    {
        $this->load->model('catalog/review');

        if (isset($this->request->post['selected']) && $this->validateReviewDelete()) {
            foreach ($this->request->post['selected'] as $review_id) {

                $queryRewardPointInstalled = $this->db->query(
                    "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'"
                );

                if($queryRewardPointInstalled->num_rows) {
                    $this->load->model('promotions/reward_points_observer');
                    $this->model_promotions_reward_points_observer->beforeUpdateReview($review_id, null, '3');
                }

                $this->model_catalog_review->deleteReview($review_id);
            }
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_bulkdelete_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_bulkdelete_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function getProducts()
    {
        $this->load->model('catalog/product');

        $this->data['all_products'] = $this->model_catalog_product->getProductsFields(['name']);

        $this->response->setOutput(json_encode($this->data['all_products']));
        return;
    }

    public function options() 
    {
        $this->language->load('catalog/review_options');
        $this->template = 'module/product_review/review_options/review_options_list.expand';
        $this->base = 'common/base';
        $this->response->setOutput($this->render_ecwig());
    }
    public function insert_option()
    {
        $this->load->model('localisation/language');
        $this->load->model('catalog/review_options');

        $this->language->load('catalog/review_options');
        $this->language->load('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $this->data['languages'] = $languages;

        $this->language->load('catalog/review_options');
        $this->template = 'module/product_review/review_options/review_options_form.expand';
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
                    'redirect' => '1',
                    'to'=> $this->url->link('module/product_review', 'active=options', 'SSL')->format()
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
    public function optionsDtHandler()
    {
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

    public function optionsDtUpdateStatus()
    {
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

    public function optionsDtDelete()
    {
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

    public function edit_option()
    {
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
            $this->template = 'module/product_review/review_options/review_options_form.expand';
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
