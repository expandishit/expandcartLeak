<?php

class ControllerProductClassificationYear extends Controller
{
    protected $error ;


    // Get Years List
    public function index() {
        $this->load->model('module/product_classification/year');
        $this->language->load('module/product_classification');

        $this->document->setTitle($this->language->get('product_classification_heading_title'));
        $this->getList();
    }

    // Get  List
    protected function getList()
    {
        $this->load->model('module/product_classification/year');
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
            'text'      => $this->language->get('year_heading_title'),
            'href'      => $this->url->link('module/product_classification', $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['insert'] = $this->url->link('product_classification/year/insert', $url, 'SSL');
        $this->data['delete'] = $this->url->link('product_classification/year/delete', $url, 'SSL');

        $this->data['years'] = array();

        $data = array(
            'sort'  => $sort,
            'order' => $order,
        );
        // get total Years
        $year_total = $this->model_module_product_classification_year->getTotalYears();
        // get all Years
        $results = $this->model_module_product_classification_year->getYears($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('product_classification/year/update', '&year_id=' . $result['pc_year_id'] . $url, 'SSL')
            );

            $this->data['years'][] = array(
                'year_id'    => $result['pc_year_id'],
                'name'            => $result['name'],
                'action'          => $action
            );
        }

        $this->data['heading_title'] = $this->language->get('year_heading_title');

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

        $this->template = 'module/product_classification/year/year_list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    public function insert()
    {
        $this->load->model('module/product_classification/year');
        $this->language->load('module/product_classification');
        
        $this->document->setTitle($this->language->get('year_heading_title'));

        $this->data['heading_title'] = $this->language->get('year_heading_title');


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
            'text'      => $this->language->get('year_heading_title'),
            'href'      => $this->url->link('product_classification/year', $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('year_heading_year_add'),
            'href'      => $this->url->link('module/product_classification', $url, 'SSL'),
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
            $this->model_module_product_classification_year->addYear($this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->data['action'] = $this->url->link('product_classification/year/insert', '', 'SSL');
        $this->data['cancel'] = $this->url->link('product_classification/year', '', 'SSL');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');


        $this->template = 'module/product_classification/year/insert.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }
    public function update()
    {
        $this->load->model('module/product_classification/year');
        $this->language->load('module/product_classification');
        
        $this->document->setTitle($this->language->get('year_heading_title'));

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
            'text'      => $this->language->get('year_heading_title'),
            'href'      => $this->url->link('product_classification/year',  $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('year_heading_year_update'),
            'href'      => $this->url->link('module/product_classification',  $url, 'SSL'),
            'separator' => ' :: '
        );

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->update_validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }
            if ( $this->model_module_product_classification_year->checkIfYearExists($this->request->post['name'],$this->request->get['year_id']) )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->language->get('error_year_exists');

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $updateData = $this->model_module_product_classification_year->editYear($this->request->get['year_id'], $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');
            $this->response->setOutput(json_encode($result_json));

            return;
        }
        // get data
        $year = $this->model_module_product_classification_year->getYear($this->request->get['year_id']);

        $this->data['year_status'] = $year['status'];
        $this->data['year_name'] = $year['name'];

        $this->data['action'] = $this->url->link('product_classification/year/update?year_id='.$this->request->get['year_id'], '', 'SSL');

        $this->data['cancel'] = $this->url->link('product_classification/year', '', 'SSL');

        $this->data['year_models'] = $this->url->link('product_classification/year/update?year_id='.$this->request->get['year_id'], '', 'SSL');

        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_settings'] = $this->language->get('text_settings');

        $this->template = 'module/product_classification/year/update.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }
    // add year models
    public function add_year_models()
    {
        $this->load->model('module/product_classification/year');
        $this->language->load('module/product_classification');
        
        $this->document->setTitle($this->language->get('year_heading_title'));

        $this->data['heading_title'] = $this->language->get('year_heading_title');


        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('product_classification_heading_title'),
            'href'      => $this->url->link('module/product_classification', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('year_heading_title'),
            'href'      => $this->url->link('product_classification/year',  $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('year_heading_year_add_models'),
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
            $this->model_module_product_classification_year->addYear($this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_success');

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->data['action'] = $this->url->link('product_classification/year/insert', '', 'SSL');
        $this->data['cancel'] = $this->url->link('product_classification/year', '', 'SSL');
        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');


        $this->template = 'module/product_classification/year/insert_models.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render());
    }


    public function dtDelete()
    {
        $this->load->model('module/product_classification/year');
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
                $this->model_module_product_classification_year->deleteYear($id);
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
        if (!$this->user->hasPermission('modify', 'product_classification/year'))
        {
            return array('error' => $this->language->get('error_permission'));
        }

        return true;
    }
    private function validate()
    {

        if(empty($this->request->post['year_from']) || !ctype_digit($this->request->post['year_from']) || strlen($this->request->post['year_from']) < 4){
            $this->error['year_from'] = $this->language->get('error_year_from_required');
        }

        if(empty($this->request->post['year_to']) || !ctype_digit($this->request->post['year_to']) || strlen($this->request->post['year_to']) < 4){
        $this->error['year_to'] = $this->language->get('error_year_to_required');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        return $this->error ? false : true;
    }

    private function update_validate()
    {

        if(empty($this->request->post['name']) || !ctype_digit($this->request->post['name'])){
            $this->error['name'] = $this->language->get('error_year_required');
        }

        if ($this->error && !isset($this->error['error'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        return $this->error ? false : true;
    }
}
