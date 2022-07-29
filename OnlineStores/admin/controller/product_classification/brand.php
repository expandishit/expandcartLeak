<?php

class ControllerProductClassificationBrand extends Controller
{
    protected $error ;
    

    // Get Brands List
    public function index() {
        $this->load->model('module/product_classification/brand');
        $this->language->load('module/product_classification');
        
        $this->document->setTitle($this->language->get('product_classification_heading_title'));
        $this->getList();
    }

    // Get  List
    protected function getList()
    {
        $this->load->model('module/product_classification/brand');
        $this->language->load('module/product_classification');
        
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
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_classification_heading_title'),
            'href'      => $this->url->link('module/product_classification',  $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('brand_heading_title'),
            'href'      => $this->url->link('module/product_classification', $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['insert'] = $this->url->link('product_classification/brand/insert', $url, 'SSL');
        $this->data['delete'] = $this->url->link('product_classification/brand/delete', $url, 'SSL');

        $this->data['brands'] = array();

        $data = array(
            'sort'  => $sort,
            'order' => $order,
        );
        // get total Brnds
        $brand_total = $this->model_module_product_classification_brand->getTotalBrands();
        // get all brands
        $results = $this->model_module_product_classification_brand->getBrands($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('product_classification/brand/update', '&brand_id=' . $result['pc_brand_id'] . $url, 'SSL')
            );

            $this->data['brands'][] = array(
                'brand_id'    => $result['pc_brand_id'],
                'name'            => $result['name'],
                'action'          => $action
            );
        }

        $this->data['heading_title'] = $this->language->get('brand_heading_title');

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

        $this->data['sort_name'] = $this->url->link('module/product_classification/history', '&sort=p.name' . $url, 'SSL');
        $this->data['sort_code'] = $this->url->link('module/product_classification/history', '&sort=c.code' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }


        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        $this->template = 'module/product_classification/brand/brand_list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function insert()
    {
        $this->load->model('module/product_classification/brand');
        $this->language->load('module/product_classification');
        
        $this->document->setTitle($this->language->get('brand_heading_title'));


        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['heading_title'] = $this->language->get('brand_heading_title');


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_classification_heading_title'),
            'href'      => $this->url->link('module/product_classification',  $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('brand_heading_title'),
            'href'      => $this->url->link('product_classification/brand', $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('brand_heading_brand_add'),
            'href'      => $this->url->link('module/product_classification',  $url, 'SSL'),
            'separator' => ' :: '
        );

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {


            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }
            $addData = $this->model_module_product_classification_brand->addBrand($this->request->post);

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


        $this->data['action'] = $this->url->link('product_classification/brand/insert', '', 'SSL');
        $this->data['cancel'] = $this->url->link('product_classification/brand', '', 'SSL');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');


        $this->template = 'module/product_classification/brand/insert.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }
    public function update()
    {
        $this->load->model('module/product_classification/brand');
        $this->language->load('module/product_classification');
        
        $this->document->setTitle($this->language->get('brand_heading_title'));

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_classification_heading_title'),
            'href'      => $this->url->link('module/product_classification', $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('brand_heading_title'),
            'href'      => $this->url->link('product_classification/brand',  $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('brand_heading_brand_update'),
            'href'      => $this->url->link('module/product_classification', $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $updateData = $this->model_module_product_classification_brand->editBrand($this->request->get['brand_id'], $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));

            return;
        }
        // get data
        $brand = $this->model_module_product_classification_brand->getBrand($this->request->get['brand_id']);
        $this->data['brand_description'] = $this->model_module_product_classification_brand->getBrandDescriptions($this->request->get['brand_id']);

        $this->data['brand_status'] = $brand['status'];

        $this->data['action'] = $this->url->link('product_classification/brand/update?brand_id='.$this->request->get['brand_id'], '', 'SSL');

        $this->data['cancel'] = $this->url->link('product_classification/brand', '', 'SSL');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');


        $this->template = 'module/product_classification/brand/update.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }

    public function dtDelete()
    {
        $this->load->model('module/product_classification/brand');
        $this->language->load('module/product_classification');

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
                $this->model_module_product_classification_brand->deleteBrand($id);
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
        if (!$this->user->hasPermission('modify', 'product_classification/brand'))
        {
            return array('error' => $this->language->get('error_permission'));
        }

        return true;
    }
    private function validate()
    {
        foreach ( $this->request->post['product_classification'] as $language_id => $value )
        {
            if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 32)) {
                $this->error['name_' . $language_id] = $this->language->get('error_name');
            }
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        return $this->error ? false : true;
    }

    public function get_models_by_brand()
    {
        if ($this->request->server['REQUEST_METHOD'] != 'POST') {

            $this->response->setOutput(json_encode(['status' => 'fail']));
            return;

        }
        $brand = (int)$this->request->post['brand'];

        $this->load->model('module/product_classification/brand');
        $this->language->load('module/product_classification');

        $models = $this->model_module_product_classification_brand->getModelsByBrandId($brand);
        $response['status'] = 'success';
        $response['models'] = $models;

        $this->response->setOutput(json_encode($response));
        return;
    }
}
