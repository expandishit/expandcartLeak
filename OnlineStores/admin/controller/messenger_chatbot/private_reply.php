<?php

use Facebook\Facebook;

class ControllerMessengerChatbotPrivateReply extends Controller
{
    private $error = array();

    private $languages = [
        'en' => [
            'name' => 'en',
            'code' => 'en',
            'locale' => 'en_US.UTF-8,en_US,en-gb,english',
            'image' => 'gb.png',
            'directory' => 'english',
            'filename' => 'english',
        ],
        'ar' => [
            'name' => 'ع',
            'code' => 'ar',
            'locale' => 'ar.UTF-8,ar,ar,arabic',
            'image' => 'eg.png',
            'directory' => 'arabic',
            'filename' => 'arabic',
        ]
    ];

    /**
     * @var string
     */
    private $facebook_app_id = '329928231042768';
    /**
     * @var string
     */
    private $facebook_secret_key = '89b8ba250426527ac48bafeead4bb19c';
    /**
     * @var Facebook $fb
     */
    private $fb;

    public function index()
    {
        $this->language->load('messenger_chatbot/private_reply');

        if(!$this->default_page_set()){
            $this->result_json = ['success' => false, 'message' => $this->language->get('error_default_page_not_set'), 'code' => '500'];
            $this->session->data['main_message'] = json_encode($this->result_json);
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('module/messenger_chatbot/private_reply');

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('messenger_chatbot/private_reply');

        if(!$this->default_page_set()){
            $this->result_json = ['success' => false, 'message' => $this->language->get('error_default_page_not_set'), 'code' => '500'];
            $this->session->data['main_message'] = json_encode($this->result_json);
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('module/messenger_chatbot/private_reply');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if (!$this->validateForm())
            {
                $result_json['success'] = '0';
                $result_json['title'] = $this->language->get('check_errors');
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $private_reply_id = $this->model_module_messenger_chatbot_private_reply->addPrivateReply($this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("private_reply");
            if($pageStatus){
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $private_reply_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'private_reply';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_create_success');
            $result_json['success'] = '1';

            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link('messenger_chatbot/private_reply', '', 'SSL');

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('messenger_chatbot/private_reply/insert', '', 'SSL'),
            'cancel' => $this->url->link('messenger_chatbot/private_reply', '', 'SSL'),
        ];

        $this->getForm();
    }

    public function update()
    {
        $this->language->load('messenger_chatbot/private_reply');
        $this->load->model('module/messenger_chatbot/private_reply');

        $page = null;
        $reply = null;
        if(!$page = $this->default_page_set()){
            $this->result_json = ['success' => false, 'message' => $this->language->get('error_default_page_not_set'), 'code' => '500'];
            $this->session->data['main_message'] = json_encode($this->result_json);
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }
        if(!$reply = $this->model_module_messenger_chatbot_private_reply->getPrivateReply($this->request->get['private_reply_id'])){
            $this->template = 'error/not_found.expand';
            $this->children = array(
                'common/header',
                'common/footer'
            );
            $this->response->setOutput($this->render_ecwig());
            return;
        }
        if($page['id'] != $reply['page_id']){
            $this->result_json = ['success' => false, 'message' => $this->language->get('error_reply_not_in_default'), 'code' => '500'];
            $this->session->data['main_message'] = json_encode($this->result_json);
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }

        $this->document->setTitle($this->language->get('heading_title'));

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['title'] = $this->language->get('check_errors');
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }
   
            $this->model_module_messenger_chatbot_private_reply->editPrivateReply($this->request->get['private_reply_id'], $this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("private_reply");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $this->request->get['private_reply_id'];
                $log_history['old_value'] = json_encode($oldValue);
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'private_reply';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_edit_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('messenger_chatbot/private_reply/update', 'private_reply_id=' . $this->request->get['private_reply_id'], 'SSL'),
            'cancel' => $this->url->link('messenger_chatbot/private_reply', '', 'SSL'),
        ];

        $this->getForm();
    }

    public function delete()
    {
        $this->language->load('messenger_chatbot/private_reply');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('module/messenger_chatbot/private_reply');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $private_reply_id) {
                // get copoun current value for log history
                $oldValue = $this->model_module_messenger_chatbot_private_reply->getPrivateReply($private_reply_id);
             
                $this->model_module_messenger_chatbot_private_reply->deletePrivateReply($private_reply_id);

                $this->load->model('setting/audit_trail');
                $this->load->model('loghistory/histories');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("private_reply");
                if($pageStatus){
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $private_reply_id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'private_reply';
                    $this->model_loghistory_histories->addHistory($log_history);
                }

            }

            $this->session->data['success'] = $this->language->get('text_delete_success');

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

            $this->redirect($this->url->link('messenger_chatbot/private_reply', '', 'SSL'));
        }

        $this->getList();
    }

    protected function getList()
    {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
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

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('messenger_chatbot/private_reply', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['insert'] = $this->url->link('messenger_chatbot/private_reply/insert', '', 'SSL');
        $this->data['delete'] = $this->url->link('messenger_chatbot/private_reply/delete', '', 'SSL');

        $this->data['private_replies'] = array();

        $data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit')
        );

        $private_reply_total = $this->model_module_messenger_chatbot_private_reply->getTotalPrivateReplies();

        $results = $this->model_module_messenger_chatbot_private_reply->getPrivateReplies($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('messenger_chatbot/private_reply/update', 'private_Reply_id=' . $result['id'] . $url, 'SSL')
            );

            $this->data['private_replies'][] = array(
                'private_reply_id' => $result['id'],
                'name' => $result['name'],
                'type' => $this->language->get($result['type']),
                'created_at' => date($this->language->get('date_format_short'), strtotime($result['created_at'])),
                'status' => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
                'selected' => isset($this->request->post['selected']) && in_array($result['id'], $this->request->post['selected']),
                'action' => $action
            );
        }

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['column_type'] = $this->language->get('column_type');
        $this->data['column_created_at'] = $this->language->get('column_created_at');
        $this->data['column_status'] = $this->language->get('column_status');
        $this->data['column_action'] = $this->language->get('column_action');

        $this->data['button_insert'] = $this->language->get('button_insert');
        $this->data['button_delete'] = $this->language->get('button_delete');

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

        $this->data['sort_name'] = HTTPS_SERVER . 'index.php?route=messenger_chatbot/private_reply?sort=name' . $url;
        $this->data['sort_type'] = HTTPS_SERVER . 'index.php?route=messenger_chatbot/private_reply?sort=type' . $url;
        $this->data['sort_created_at'] = HTTPS_SERVER . 'index.php?route=messenger_chatbot/private_reply?sort=created_at' . $url;
        $this->data['sort_status'] = HTTPS_SERVER . 'index.php?route=messenger_chatbot/private_reply?sort=status' . $url;

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $private_reply_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = HTTPS_SERVER . 'index.php?route=messenger_chatbot/private_reply?page={page}';

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        if ($private_reply_total == 0){
            $this->template = 'module/messenger_chatbot/replies/empty.expand';
        }else{
            $this->template = 'module/messenger_chatbot/replies/list.expand';
        }

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function getForm()
    {
        $this->load->model('setting/setting');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('catalog/manufacturer');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');
        $this->load->model('tool/image');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('messenger_chatbot/private_reply', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => !isset($this->request->get['private_reply_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('messenger_chatbot/private_reply', '', 'SSL'),
            'separator' => ' :: '
        );

        if (!isset($this->request->get['private_reply_id'])) {
            $this->data['action'] = $this->url->link('messenger_chatbot/private_reply/insert', '', 'SSL');
        } else {
            $this->data['action'] = $this->url->link('messenger_chatbot/private_reply/update', 'private_reply_id=' . $this->request->get['private_reply_id'] . $url, 'SSL');
        }

        $this->data['cancel'] = $this->url->link('messenger_chatbot/private_reply', '', 'SSL');

        if (isset($this->request->get['private_reply_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
            $reply_info = $this->model_module_messenger_chatbot_private_reply->getPrivateReply($this->request->get['private_reply_id']);
        }

        foreach ($this->languages as $code => $lang) {
            if (!empty($this->request->post)) {
                foreach ($this->request->post as $key => $value) {
                    $this->data[$key] = $value;
                }
            } elseif (!empty($reply_info)) {
                $formdata = $this->model_module_messenger_chatbot_private_reply->getPrivateReplyFormdata($reply_info['id']);
                foreach ($formdata as $typeOrLang => $object) {
                    if(in_array($typeOrLang, array_keys($this->languages))){
                        $lang = $typeOrLang;
                        foreach ($formdata[$lang] as $type => $object) {
                            if(isset($formdata[$lang][$type]['product'])){
                                $id = $formdata[$lang][$type]['product'];
                                $product = $this->model_catalog_product->getProduct($id);
                                $formdata[$lang][$type]['product'] = [];
                                $formdata[$lang][$type]['product']['id'] = $id;
                                $formdata[$lang][$type]['product']['name'] = $product['name'];
                                $formdata[$lang][$type]['product']['image'] = $this->model_tool_image->resize($product['image'], 200, 200);
                                $formdata[$lang][$type]['product']['price'] = $product['price'];
                            }
                            if(isset($formdata[$lang][$type]['products'])){
                                $products = [];
                                foreach ($formdata[$lang][$type]['products'] as $id) {
                                    $product = $this->model_catalog_product->getProduct($id);
                                    $products[] = [
                                        'id' => $id,
                                        'name' => $product['name'],
                                        'image' => $this->model_tool_image->resize($product['image'], 200, 200),
                                        'price' => $product['price']
                                    ];
                                }
                                $formdata[$lang][$type]['products'] = $products;
                            }
                            if(isset($formdata[$lang][$type]['category'])){
                                $id = $formdata[$lang][$type]['category'];
                                $category = $this->model_catalog_category->getCategory($id);
                                $formdata[$lang][$type]['category'] = [];
                                $formdata[$lang][$type]['category']['id'] = $id;
                                $formdata[$lang][$type]['category']['name'] = $category['name'];
                            }
                            if(isset($formdata[$lang][$type]['manufacturer'])){
                                $id = $formdata[$lang][$type]['manufacturer'];
                                $manufacturer = $this->model_catalog_manufacturer->getManufacturer($id);
                                $formdata[$lang][$type]['manufacturer'] = [];
                                $formdata[$lang][$type]['manufacturer']['id'] = $id;
                                $formdata[$lang][$type]['manufacturer']['name'] = $manufacturer['name'];
                            }

                            if(isset($formdata[$lang][$type]['buttons'])){
                                foreach ($formdata[$lang][$type]['buttons'] as $key => $button) {
                                    if(isset($formdata[$lang][$type]['buttons'][$key]['product'])){
                                        $id = $formdata[$lang][$type]['buttons'][$key]['product'];
                                        $product = $this->model_catalog_product->getProduct($id);
                                        $formdata[$lang][$type]['buttons'][$key]['product'] = [];
                                        $formdata[$lang][$type]['buttons'][$key]['product']['id'] = $id;
                                        $formdata[$lang][$type]['buttons'][$key]['product']['name'] = $product['name'];
                                        $formdata[$lang][$type]['buttons'][$key]['product']['image'] = $this->model_tool_image->resize($product['image'], 200, 200);
                                        $formdata[$lang][$type]['buttons'][$key]['product']['price'] = $product['price'];
                                    }
                                    elseif(isset($formdata[$lang][$type]['buttons'][$key]['category'])){
                                        $id = $formdata[$lang][$type]['buttons'][$key]['category'];
                                        $category = $this->model_catalog_category->getCategory($id);
                                        $formdata[$lang][$type]['buttons'][$key]['category'] = [];
                                        $formdata[$lang][$type]['buttons'][$key]['category']['id'] = $id;
                                        $formdata[$lang][$type]['buttons'][$key]['category']['name'] = $category['name'];
                                    }
                                }
                            }
                        }
                        continue;
                    }
                    if(isset($formdata[$typeOrLang]['product'])){
                        $id = $formdata[$typeOrLang]['product'];
                        $product = $this->model_catalog_product->getProduct($id);
                        $formdata[$typeOrLang]['product'] = [];
                        $formdata[$typeOrLang]['product']['id'] = $id;
                        $formdata[$typeOrLang]['product']['name'] = $product['name'];
                        $formdata[$typeOrLang]['product']['image'] = $this->model_tool_image->resize($product['image'], 200, 200);
                        $formdata[$typeOrLang]['product']['price'] = $product['price'];
                    }
                    if(isset($formdata[$typeOrLang]['products'])){
                        $products = [];
                        foreach ($formdata[$typeOrLang]['products'] as $id) {
                            $product = $this->model_catalog_product->getProduct($id);
                            $products[] = [
                                'id' => $id,
                                'name' => $product['name'],
                                'image' => $this->model_tool_image->resize($product['image'], 200, 200),
                                'price' => $product['price']
                            ];
                        }
                        $formdata[$typeOrLang]['products'] = $products;
                    }
                    if(isset($formdata[$typeOrLang]['category'])){
                        $id = $formdata[$typeOrLang]['category'];
                        $category = $this->model_catalog_category->getCategory($id);
                        $formdata[$typeOrLang]['category'] = [];
                        $formdata[$typeOrLang]['category']['id'] = $id;
                        $formdata[$typeOrLang]['category']['name'] = $category['name'];
                    }
                    if(isset($formdata[$typeOrLang]['manufacturer'])){
                        $id = $formdata[$typeOrLang]['manufacturer'];
                        $manufacturer = $this->model_catalog_manufacturer->getManufacturer($id);
                        $formdata[$typeOrLang]['manufacturer'] = [];
                        $formdata[$typeOrLang]['manufacturer']['id'] = $id;
                        $formdata[$typeOrLang]['manufacturer']['name'] = $manufacturer['name'];
                    }
                }
                foreach ($formdata as $key => $value) {
                    $this->data[$key] = $value;
                }
            }
        }

        if (isset($this->request->get['private_reply_id'])) {
            $this->data['private_reply_id'] = $this->request->get['private_reply_id'];
        } else {
            $this->data['private_reply_id'] = false;
        }

        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } elseif (!empty($reply_info)) {
            $this->data['name'] = $reply_info['name'];
        } else {
            $this->data['name'] = '';
        }

        if (isset($this->request->post['type'])) {
            $this->data['type'] = $this->request->post['type'];
        } elseif (!empty($reply_info)) {
            $this->data['type'] = $reply_info['type'];
        } else {
            $this->data['type'] = '';
        }

        if (isset($this->request->post['applied_on'])) {
            $this->data['applied_on'] = $this->request->post['applied_on'];
        } elseif (!empty($reply_info)) {
            $this->data['applied_on'] = $reply_info['applied_on'];
        } else {
            $this->data['applied_on'] = '';
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($reply_info)) {
            $this->data['status'] = $reply_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        $this->data['languages'] = $this->languages;

        $web_pages = [];
        $this->load->model('catalog/information');
        $results = $this->model_catalog_information->getInformations(null);
        foreach ($results as $result) {
            $web_pages[] = [
                'id' => $result['information_id'],
                'title' => $result['title']
            ];
        }
        $this->data['web_pages'] = $web_pages;

        $this->data['other_pages'] = $this->model_module_messenger_chatbot_private_reply->get_other_pages(null);


        $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
        $accessToken = $mcb_settings['access_token'];
        $user_id = $mcb_settings['user_id'];
        $this->fb = new Facebook([
            'app_id' => $this->facebook_app_id,
            'app_secret' => $this->facebook_secret_key,
        ]);

        $default_page = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($user_id);
        try{
            $this->data['posts'] = $this->fb->get($default_page['page_id'] . '/posts', $default_page['access_token'])->getDecodedBody()['data'];
        }
        catch (Exception $e) {
            $this->result_json = ['success' => false, 'message' => $this->language->get('error_reply_page_diconnected'), 'code' => '500'];
            $this->session->data['main_message'] = json_encode($this->result_json);
            $this->redirect($this->url->link('module/messenger_chatbot'));
        }

        if (isset($this->request->post['selected_posts'])) {
            $this->data['selected_posts'] = $this->request->post['selected_posts'];
        } elseif (!empty($reply_info)) {
            $this->data['selected_posts'] = $this->model_module_messenger_chatbot_private_reply->getPrivateReplyPosts($reply_info['id']);
        } else {
            $this->data['selected_posts'] = [];
        }

        $this->load->model('tool/image');
        $this->data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 200, 200);
        $this->data['store_name'] = $this->config->get('config_name');

        $this->template = 'module/messenger_chatbot/replies/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validateForm()
    {
        if ( ! $this->user->hasPermission('modify', 'module/messenger_chatbot') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( (utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 50) )
        {
            $this->error['name'] = $this->language->get('error_name');
        }

        $reply_info = $this->model_module_messenger_chatbot_private_reply->getPrivateReplyByName($this->request->post['name']);

        if ( $reply_info )
        {
            if (!isset($this->request->get['private_reply_id'])) {
                $this->error['code'] = $this->language->get('error_exists');
            } elseif ($reply_info['id'] != $this->request->get['private_reply_id']) {
                $this->error['code'] = $this->language->get('error_exists');
            }
        }

        if(!isset($this->request->post['type'])){
            $this->error['type'] = $this->language->get('error_type');
        }

        if(!isset($this->request->post['applied_on'])){
            $this->error['applied_on'] = $this->language->get('error_applied_on');
        }

        if($this->request->post['applied_on'] == 'posts' && !count($this->request->post['posts'])){
            $this->error['posts'] = $this->language->get('error_posts');
        }

        switch ($this->request->post['type']) {
            case 'store_link':
                foreach($this->languages as $code => $lang){

                    if(!isset($this->request->post[$code]['store_link']['link_type'])){
                        $this->error['link_type'] = $this->language->get('error_link_type');
                    }
                    if(!isset($this->request->post[$code]['store_link']['subtitle']) || empty($this->request->post[$code]['store_link']['subtitle']) || utf8_strlen($this->request->post[$code]['store_link']['subtitle']) > 50){
                        $this->error['link_type'] = $this->language->get('error_description');
                    }

                    switch ($this->request->post[$code]['store_link']['link_type']) {
                        case 'product':
                            if(!isset($this->request->post[$code]['store_link']['product'])){
                                $this->error['product'] = $this->language->get('error_product');
                            }
                            break;
                        case 'category':
                            if(!isset($this->request->post[$code]['store_link']['category'])){
                                $this->error['category'] = $this->language->get('error_category');
                            }
                            break;
                        case 'web_page':
                            if(!isset($this->request->post[$code]['store_link']['web_page'])){
                                $this->error['web_page'] = $this->language->get('error_web_page');
                            }
                            break;
                        case 'other_page':
                            if(!isset($this->request->post[$code]['store_link']['other_page'])){
                                $this->error['other_page'] = $this->language->get('error_other_page');
                            }
                            break;
                        default:
                            break;
                    }

                    if(isset($this->request->post[$code]['store_link']['buttons'])){
                        $this->validate_buttons($this->request->post[$code]['store_link']['buttons']);
                    }
                }
                break;

            case 'free_text':
                foreach($this->languages as $code => $lang){

                    if(!isset($this->request->post[$code]['free_text']['body']) || empty($this->request->post[$code]['free_text']['body'])){
                        $this->error['body'] = $this->language->get('error_body');
                    }
                    if(utf8_strlen($this->request->post[$code]['free_text']['body']) > 450){
                        $this->error['body'] = $this->language->get('error_body_length');
                    }

                }
                break;

            case 'media_template':
                if(!isset($this->request->post['media_template']['attachment_id']) || empty($this->request->post['media_template']['attachment_id'])){
                    $this->error['attachment_id'] = $this->language->get('error_attachment_id');
                }
                foreach($this->languages as $code => $lang){
                    if(isset($this->request->post[$code]['media_template']['buttons'])){
                        $this->validate_buttons($this->request->post[$code]['media_template']['buttons']);
                    }
                }
                break;

            case 'direct_product':
                if(!isset($this->request->post['direct_product']['product']) || empty($this->request->post['direct_product']['product'])){
                    $this->error['direct_product'] = $this->language->get('error_product');
                }
                foreach($this->languages as $code => $lang){
                    if(isset($this->request->post[$code]['direct_product']['buttons'])){
                        $this->validate_buttons($this->request->post[$code]['direct_product']['buttons']);
                    }
                }
                break;

            case 'specific_category':
                if(!isset($this->request->post['specific_category']['category']) || empty($this->request->post['specific_category']['category'])){
                    $this->error['specific_category'] = $this->language->get('error_category');
                }

                if(!isset($this->request->post['specific_category']['products']) || count($this->request->post['specific_category']['products']) == 0){
                    $this->error['products'] = $this->language->get('error_product');
                }

                if(isset($this->request->post[$code]['specific_category']['buttons'])){
                    $this->validate_buttons($this->request->post[$code]['specific_category']['buttons']);
                }
                break;

            case 'specific_manufacturer':
                foreach($this->languages as $code => $lang){

                    if(!isset($this->request->post['specific_manufacturer']['manufacturer']) || empty($this->request->post['specific_manufacturer']['manufacturer'])){
                        $this->error['specific_manufacturer'] = $this->language->get('error_brand');
                    }

                    if(!isset($this->request->post['specific_manufacturer']['products']) || count($this->request->post['specific_manufacturer']['products']) == 0){
                        $this->error['products'] = $this->language->get('error_product');
                    }

                    if(isset($this->request->post[$code]['specific_manufacturer']['buttons'])){
                        $this->validate_buttons($this->request->post[$code]['specific_manufacturer']['buttons']);
                    }
                }
                break;
            
            default:
                $this->error['type'] = $this->language->get('error_type');
                break;
        }
        
        return $this->error ? false : true;
    }

    private function validate_buttons($buttons)
    {
        foreach ($buttons as $button) {
            if(!isset($button['link_type'])){
                $this->error['button_link_type'] = $this->language->get('error_button_link_type');
            }

            if(!isset($button['title']) || empty($button['title'])){
                $this->error['button_title'] = $this->language->get('error_button_title');
            }

            switch ($button['link_type']) {
                case 'product':
                    if(!isset($button['product'])){
                        $this->error['product'] = $this->language->get('error_product');
                    }
                    break;
                case 'category':
                    if(!isset($button['category'])){
                        $this->error['category'] = $this->language->get('error_category');
                    }
                    break;
                case 'web_page':
                    if(!isset($button['web_page'])){
                        $this->error['web_page'] = $this->language->get('error_web_page');
                    }
                    break;
                case 'other_page':
                    if(!isset($button['other_page'])){
                        $this->error['other_page'] = $this->language->get('error_other_page');
                    }
                    break;
                default:
                    break;
            }
        }
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'module/messenger_chatbot')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function dtHandler()
    {
        $this->load->model('module/messenger_chatbot/private_reply');
        $this->language->load('messenger_chatbot/private_reply');
        $request = $this->request->request;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            1 => 'name'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_module_messenger_chatbot_private_reply->dtHandler([
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        foreach ($records as &$record) {
            $record['type'] = $this->language->get($record['type']);
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
        $this->language->load('messenger_chatbot/private_reply');
        $this->load->model('module/messenger_chatbot/private_reply');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("private_reply");

            $this->load->model('loghistory/histories');
            foreach ($this->request->post['selected'] as $private_reply_id) {
                // get copoun current value for log history
                $oldValue = $this->model_module_messenger_chatbot_private_reply->getPrivateReply($private_reply_id);
                $this->load->model('loghistory/coupon');
                $this->model_module_messenger_chatbot_private_reply->deletePrivateReply($private_reply_id);
                // add data to log_history
                if($pageStatus){
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $private_reply_id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'private_reply';
                    $this->model_loghistory_histories->addHistory($log_history);
                }

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

    public function dtUpdateStatus()
    {
        $this->load->model("module/messenger_chatbot/private_reply");
        $this->load->model("module/messenger_chatbot/messenger_chatbot");
        $this->language->load('messenger_chatbot/private_reply');
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $private_reply = $this->model_module_messenger_chatbot_private_reply->getPrivateReply($id);
            $posts = $this->model_module_messenger_chatbot_private_reply->getPrivateReplyPosts($id);
            $page = $this->model_module_messenger_chatbot_messenger_chatbot->get_page($private_reply['page_id']);
            if($private_reply['applied_on'] == 'posts' && !count($posts)){
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('error_posts');
            }
            else if($private_reply['applied_on'] == 'page' && $page['reply_id'] != $id){
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get('error_page_already_assigned');
            }
            else{
                $this->model_module_messenger_chatbot_private_reply->updateStatus($id, $status);

                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_langstatus_success');
            }
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_langstatus_error');
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

    public function upload_attachment()
    {
        $this->language->load('messenger_chatbot/private_reply');

        $data = [
            "message" => json_encode([
                "attachment" => [
                    "type" => $_POST['type'], 
                    "payload" => [
                        "is_reusable" => true
                    ]
                ]
            ])
        ];

        // Allowed file extension types
        $allowedImage = array(
            'jpg',
            'jpeg',
            'gif',
            'png'
        );

        $allowedVideo = array(
            'mp4',
            'mov',
            'wmv',
            'avi',
            'flv',
            'webm'
        );

        if(($_POST['type'] == 'image' && !in_array(utf8_strtolower(utf8_substr(strrchr($_FILES['file']['name'], '.'), 1)), $allowedImage)) || ($_POST['type'] == 'video' && !in_array(utf8_strtolower(utf8_substr(strrchr($_FILES['file']['name'], '.'), 1)), $allowedVideo))){
            $json['error'] = $this->language->get('error_file_type');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if ($_POST['type'] == 'image' && $_FILES["file"]["size"] > 2000000) {
            $json['error'] = $this->language->get('error_image_size');
            $this->response->setOutput(json_encode($json));
            return;
        }
        else if ($_POST['type'] == 'video' && $_FILES["file"]["size"] > 25000000) {
            $json['error'] = $this->language->get('error_video_size');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->load->model('setting/setting');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');

        $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
        $access_token = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($mcb_settings['user_id'])['access_token'];
        
        $headers = [
            'Content-Type: multipart/form-data'
        ];

        $url = "https://graph.facebook.com/v11.0/me/message_attachments?access_token=" . $access_token;

        $ch = curl_init();

        $target_file = TEMP_DIR_PATH . $_FILES["file"]["name"];
        if (!file_exists(TEMP_DIR_PATH)) {
            mkdir(TEMP_DIR_PATH, 0777, true);
        }
        move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

        $filePath;
        if(function_exists('curl_file_create')){
            $filePath = curl_file_create($target_file, $_FILES['file']['type']);
        } else{
            $filePath = '@' . realpath($_FILES['file']['tmp_name']);
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }

        $data['filedata'] = $filePath;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);

        if ($cer = curl_error($ch)) {
            print_r($cer);
            die;
        }
        if(!json_decode($return)->attachment_id){
            print_r($return);
            $json['error'] = json_decode($return)->error->message;
            $this->response->setOutput(json_encode($json));
            return;
        }

        $attachment_id = json_decode($return)->attachment_id;
        $directory = 'image/data';
        $filename = $attachment_id . '.' . utf8_strtolower(utf8_substr(strrchr($_FILES["file"]["name"], '.'), 1));
        $new_filepath = $directory . '/' . $filename;

        $u = \Filesystem::setPath($new_filepath)->upload($target_file);
        $imageUrl = \Filesystem::getUrl($directory . '/' . $u['file_name']);
        $this->response->setOutput(json_encode([
            'attachment_id' => $attachment_id,
            'attachment_url' => $imageUrl
        ]));
        return;
    }

    private function default_page_set(){
        $this->load->model('setting/setting');
        $this->load->model('module/messenger_chatbot/messenger_chatbot');

        $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
        if($page = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($mcb_settings['user_id'])){
            return $page;
        }

        return false;
    }

    public function check_assigned_items(){
        $this->language->load('messenger_chatbot/private_reply');
        if($_POST['applied_on'] == 'page'){
            $this->load->model('setting/setting');
            $this->load->model('module/messenger_chatbot/messenger_chatbot');

            $mcb_settings = $this->model_setting_setting->getSetting('messenger_chatbot');
            $page = $this->model_module_messenger_chatbot_messenger_chatbot->get_default_page($mcb_settings['user_id']);
            if(isset($_POST['reply_id']) && $_POST['reply_id']){
                if($page['reply_id'] != $_POST['reply_id'] && $page['reply_id']){
                    $result_json['status'] = '0';
                    $result_json['message'] = $this->language->get('error_page_already_assigned');
                }
                else{
                    $result_json['status'] = '1';
                }
            }
            elseif($page['reply_id']){
                $result_json['status'] = '0';
                $result_json['message'] = $this->language->get('error_page_already_assigned');
            }
            else{
                $result_json['status'] = '1';
            }
        }
        elseif($_POST['applied_on'] == 'posts'){
            $this->load->model('module/messenger_chatbot/private_reply');
            $reply_id = isset($_POST['reply_id']) && strlen($_POST['reply_id']) ? $_POST['reply_id'] : 0;
            $posts = $this->model_module_messenger_chatbot_private_reply->getPostsByIds($_POST['posts'], $reply_id);
            if(count($posts)){
                $result_json['status'] = '0';
                $result_json['message'] = $this->language->get('error_posts_already_assigned');
            }
            else{
                $result_json['status'] = '1';
            }
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
