<?php

class ControllerLocalisationTaxClass extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('localisation/tax_class');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_class');

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('localisation/tax_class');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_class');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_localisation_tax_class->addTaxClass($this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('localisation/tax_class/insert', '', 'SSL'),
            'cancel' => $this->url->link('localisation/tax_class', '', 'SSL'),
        ];

        $this->data['cancel'] = $this->url->link('localisation/tax_class', '', 'SSL');

        $this->getForm();
    }

    public function update()
    {
        $this->language->load('localisation/tax_class');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_class');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_localisation_tax_class->editTaxClass( $this->request->get['tax_class_id'], $this->request->post );

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'localisation/tax_class/update',
                'tax_class_id=' . $this->request->get['tax_class_id'],
                'SSL'
            ),
            'cancel' => $this->url->link('localisation/tax_class', '', 'SSL'),
        ];

        $this->getForm();
    }

    public function delete()
    {
        $this->language->load('localisation/tax_class');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_class');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $tax_class_id) {
                $this->model_localisation_tax_class->deleteTaxClass($tax_class_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('localisation/tax_class', '', 'SSL'));
        }

        $this->getList();
    }

    protected function getList()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/tax_class', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['tax_classes'] = array();

        $results = $this->model_localisation_tax_class->getTaxClasses();

        foreach ($results as $result) {
            $this->data['tax_classes'][] = array(
                'tax_class_id' => $result['tax_class_id'],
                'title' => $result['title'],
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

        $this->data['links'] = [
            'rates' => $this->url->link('localisation/tax_rate', '', 'SSL')
        ];

        $this->template = 'localisation/tax/class_list.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    protected function getForm()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/tax_class', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => ! isset( $this->request->get['tax_class_id'] )  ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('localisation/tax_class', '', 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->request->get['tax_class_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $tax_class_info = $this->model_localisation_tax_class->getTaxClass($this->request->get['tax_class_id']);
        }

        if (isset($this->request->post['title'])) {
            $this->data['title'] = $this->request->post['title'];
        } elseif (!empty($tax_class_info)) {
            $this->data['title'] = $tax_class_info['title'];
        } else {
            $this->data['title'] = '';
        }

        if (isset($this->request->post['description'])) {
            $this->data['description'] = $this->request->post['description'];
        } elseif (!empty($tax_class_info)) {
            $this->data['description'] = $tax_class_info['description'];
        } else {
            $this->data['description'] = '';
        }

        $this->load->model('localisation/tax_rate');

        $this->data['tax_rates'] = $this->model_localisation_tax_rate->getTaxRates();

        if (isset($this->request->post['tax_rule'])) {
            $this->data['tax_rules'] = $this->request->post['tax_rule'];
        } elseif (isset($this->request->get['tax_class_id'])) {
            $this->data['tax_rules'] = $this->model_localisation_tax_class->getTaxRules(
                $this->request->get['tax_class_id']
            );
        } else {
            $this->data['tax_rules'] = array();
        }

        $this->template = 'localisation/tax/class_form.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'localisation/tax_class')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if (
            (utf8_strlen($this->request->post['title']) < 3) ||
            (utf8_strlen($this->request->post['title']) > 32)
        ) {
            $this->error['title'] = $this->language->get('error_title');
        }

        if (
            (utf8_strlen($this->request->post['description']) < 3) ||
            (utf8_strlen($this->request->post['description']) > 255)
        ) {
            $this->error['description'] = $this->language->get('error_description');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'localisation/tax_class')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        $this->load->model('catalog/product');

        foreach ($this->request->post['selected'] as $tax_class_id) {
            $product_total = $this->model_catalog_product->getTotalProductsByTaxClassId($tax_class_id);

            if ($product_total) {
                $this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
            }
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    public function dtDelete()
    {
        $this->load->model('localisation/tax_class');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $id) {
                $this->model_localisation_tax_class->deleteTaxClass($id);
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($response));
        return;
    }


    public function applyTaxClassTo(){
        $tax_class_id = $this->request->post['tax_class_id'];
        $is_all_products_checked = $this->request->post['is_all_products_checked'];
        $entities_ids = array_column($this->request->post['products_ids'], 'value');
        $entity_type  = $this->request->post['entity_type'];

        $this->load->model('localisation/tax_class');
        //
        if($entity_type === 'product' && isset($is_all_products_checked) && $is_all_products_checked == TRUE ){
            //update all products tax_class_id
            $this->model_localisation_tax_class->updateProductsTaxClassId($tax_class_id, $entity_type);
        }
        else{
            $this->model_localisation_tax_class->updateProductsTaxClassId($tax_class_id, $entity_type, $entities_ids);
        }

        $response['success'] = '1';
        $this->response->setOutput(json_encode($response));
    }



}
