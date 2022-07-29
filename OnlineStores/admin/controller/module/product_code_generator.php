<?php

class ControllerModuleProductCodeGenerator extends Controller
{
    private $settings;
    protected $errors = [];

    public function init($models)
    {
        // TODO modularize this.
        foreach ($models as $model) {

            $this->load->model($model);

            $object = explode('/', $model);
            $object = end($object);

            $model = str_replace('/', '_', $model);

            $this->$object = $this->{"model_" . $model};
        }

        if (isset($this->session->data['errors'])) {
            $this->data['error_warning'] = implode('</br >', $this->session->data['errors']);

            unset($this->session->data['errors']);
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        }
        $this->language->load('module/product_code_generator');
    }
    public function index()
    {

        $this->init([
            'module/product_code_generator/settings'
        ]);

        $this->document->setTitle($this->language->get('product_code_generator_heading_title'));

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
            'text'      => $this->language->get('product_code_generator_heading_title'),
            'href'      => $this->url->link('module/product_code_generator', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');

        $this->template = 'module/product_code_generator/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        // get app settings
        $this->data['settingsData'] = $this->settings ->getSettings();


        $this->data['action'] = $this->url->link('module/product_code_generator/updateSettings', '', 'SSL');
        $this->data['cancel'] = $this->url.'marketplace/home';
        $this->data['linkHistory'] = $this->url.'module/product_code_generator/history';
        $this->data['linkArchive'] = $this->url.'module/product_code_generator/archive';


        $this->response->setOutput($this->render());
    }
    // Get Codes List
    public function history() {
        $this->init([
            'module/product_code_generator/code'
        ]);
        $this->document->setTitle($this->language->get('product_code_generator_heading_title'));
        $this->getList();
    }
    public function archive()
    {
        $this->init([
            'module/product_code_generator/code'
        ]);
        $this->document->setTitle($this->language->get('product_code_generator_heading_title'));
        // define url
        $url = '';
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
            $url .= '&sort=' . $this->request->get['sort'];
        } else {
            $sort = 'p.name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
            $url .= '&order=' . $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
            $url .= '&page=' . $this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_code_generator_heading_title'),
            'href'      => $this->url->link('module/product_code_generator', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['insert'] = $this->url->link('module/product_code_generator/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['delete'] = $this->url->link('module/product_code_generator/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->data['codes'] = array();

        $data = array(
            'sort'  => $sort,
            'order' => $order,
        );
        // get total codes
        $code_total = $this->code->getTotalCodes(1);
        // get all codes
        $results = $this->code->getArciveCodes($data);

        foreach ($results as $result) {

            $this->data['codes'][] = array(
                'code_id'    => $result['product_code_generator_id'],
                'code'    => $result['code'],
                'name'            => $result['name'],
                'action'          => $action
            );
        }

        $this->data['heading_title'] = $this->language->get('product_code_generator_heading_title');

        $this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->data['column_name'] = $this->language->get('column_name');
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

        $this->data['sort_name'] = $this->url->link('module/product_code_generator/archive', 'token=' . $this->session->data['token'] . '&sort=p.name' . $url, 'SSL');
        $this->data['sort_code'] = $this->url->link('module/product_code_generator/archive', 'token=' . $this->session->data['token'] . '&sort=c.code' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }


        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        $this->template = 'module/product_code_generator/code_list_archive.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }
    // Get Codes List
    protected function getList()
    {
        $this->init([
            'module/product_code_generator/code'
        ]);
        // define url
        $url = '';
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
            $url .= '&sort=' . $this->request->get['sort'];
        } else {
            $sort = 'p.name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
            $url .= '&order=' . $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
            $url .= '&page=' . $this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_code_generator_heading_title'),
            'href'      => $this->url->link('module/product_code_generator', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['insert'] = $this->url->link('module/product_code_generator/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['delete'] = $this->url->link('module/product_code_generator/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->data['codes'] = array();

        $data = array(
            'sort'  => $sort,
            'order' => $order,
        );
        // get total codes
        $code_total = $this->code->getTotalCodes();
        // get all codes
        $results = $this->code->getCodes($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('module/product_code_generator/update', 'token=' . $this->session->data['token'] . '&code_id=' . $result['product_code_generator_id'] . $url, 'SSL')
            );

            $this->data['codes'][] = array(
                'code_id'    => $result['product_code_generator_id'],
                'code'    => $result['code'],
                'name'            => $result['name'],
                'action'          => $action
            );
        }

        $this->data['heading_title'] = $this->language->get('product_code_generator_heading_title');

        $this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->data['column_name'] = $this->language->get('column_name');
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

        $this->data['sort_name'] = $this->url->link('module/product_code_generator/history', 'token=' . $this->session->data['token'] . '&sort=p.name' . $url, 'SSL');
        $this->data['sort_code'] = $this->url->link('module/product_code_generator/history', 'token=' . $this->session->data['token'] . '&sort=c.code' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $attribute_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('module/product_code_generator/history', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        $this->template = 'module/product_code_generator/code_list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }
    public function updateSettings()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)) {
             $result_json['success'] = '0';
             $result_json['errors'] = 'Invalid Request';
        }else{
            $this->init([
                'module/product_code_generator/settings'
            ]);

            $data = $this->request->post['product_code_generator'];
            if ($data['show_codes_in_success_pg'] == 0) {
                $data['hide_codes_in_success_pg_postpaid'] = 0;
            }

            $this->settings->updateSettings(['product_code_generator' => $data]);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->data['success_message'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
        }
        $this->response->setOutput(json_encode($result_json));
        return;
    }
    public function insert()
    {
        $this->init([
            'module/product_code_generator/code'
        ]);
        $this->document->setTitle($this->language->get('product_code_generator_heading_title'));

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));

                return;
            }
            if (STORECODE == 'XQMMJS3209'){
                $log = new Log("code_generator.log");
                $arr= [
                    'post_info'=>$this->request->post['product_code'] ,
                    'data'=>str_replace(array("\r\n", "\r", "\n"), "<br />", $this->request->post['product_code']) ,
                ];
                $log->write(json_encode($arr));
            }

            $product_id = (int)$this->request->post['product_id'];
            $codes = explode("<br />",nl2br(str_replace(array("\r\n","\\r\\n", "\r", "\n"), "<br />", $this->request->post['product_code'])));

            $addData = $this->code->addCode($product_id,$codes);

            if($addData){
                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }else{
                $result_json['success'] = '0';
                $result_json['errors'] = $this->language->get('unexpected_error');
            }

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/attribute/insert', '', 'SSL')
        ];

        $this->data['cancel'] = $this->url->link('catalog/attribute', '', 'SSL');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');

        // load Model
        $this->load->model('catalog/product');
        // get products data
        $this->data['products'] = $this->model_catalog_product->getProductsFields(['name']);

        $this->template = 'module/product_code_generator/insert.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }
    public function update()
    {
        $this->init([
            'module/product_code_generator/code'
        ]);
        $this->document->setTitle($this->language->get('product_code_generator_heading_title'));

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->errors;

                $this->response->setOutput(json_encode($result_json));

                return;
            }
            $data['product_id'] = (int)$this->request->post['product_id'];
            $data['code'] = $this->request->post['product_code'];

            $updateData = $this->code->editCode($this->request->get['code_id'], $data);

            if($updateData){
                $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
                $result_json['success'] = '1';
                $result_json['success_msg'] = $this->language->get('text_success');
            }else{
                $result_json['success'] = '0';
                $result_json['errors'] = $this->language->get('unexpected_error');
            }

            $this->response->setOutput(json_encode($result_json));

            return;
        }
        // get code
        $code = $this->code->getCode($this->request->get['code_id']);

        $this->data['code'] = $code['code'];
        $this->data['product_id'] = $code['product_id'];

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/attribute/update', '', 'SSL')
        ];

        $this->data['cancel'] = $this->url->link('catalog/attribute', '', 'SSL');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');

        // load Model
        $this->load->model('catalog/product');
        // get products data
        $this->data['products'] = $this->model_catalog_product->getProductsFields(['name']);

        $this->template = 'module/product_code_generator/update.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }

    public function install()
    {
        $this->init(['module/product_code_generator/settings']);

        $this->settings->install();
    }

    public function uninstall()
    {
        $this->init(['module/product_code_generator/settings']);

        $this->settings->uninstall();
    }
    public function dtDelete()
    {
        $this->init([
            'module/product_code_generator/code'
        ]);

        if(isset($this->request->post["selected"])) {
            $ids = $this->request->post["selected"];

            $validate = $this->validateDelete($ids);

            if ( $validate !== true )
            {
                $result_json['success'] = '0';
                $result_json['error'] = $this->language->get( $validate );
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            foreach ($ids as $id) {
                $this->code->deleteCode($id);
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
    protected function validateDelete($ids)
    {
        if (!$this->user->hasPermission('modify', 'module/product_code_generator'))
        {
            return array('error' => $this->language->get('error_permission'));
        }

        return true;
    }
    private function validate()
    {
        if ((int)$this->request->post['product_id'] == 0) {
            $this->errors['product_id'] = $this->language->get('error_entry_product_id');
        }

        if (empty($this->request->post['product_code'])) {
            $this->errors['product_code'] = $this->language->get('error_entry_product_code');
        }

        if ($this->errors && !isset($this->errors['error'])) {
            $this->errors['warning'] = $this->language->get('error_warning');
        }
        return $this->errors ? false : true;
    }
}
