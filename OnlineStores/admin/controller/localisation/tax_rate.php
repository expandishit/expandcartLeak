<?php

class ControllerLocalisationTaxRate extends Controller
{
    private $error = array();

    public function index()
    {
        $this->language->load('localisation/tax_rate');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_rate');

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('localisation/tax_rate');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_rate');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_localisation_tax_rate->addTaxRate($this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->data['cancel'] = $this->url->link('localisation/tax_rate', '', 'SSL');

        $this->getForm();
    }

    public function update()
    {
        $this->language->load('localisation/tax_rate');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_rate');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_localisation_tax_rate->editTaxRate($this->request->get['tax_rate_id'], $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'localisation/tax_rate/update',
                'tax_rate_id=' . $this->request->get['tax_rate_id'],
                'SSL'
            ),
            'cancel' => $this->url->link('localisation/tax_rate', '', 'SSL')
        ];

        $this->data['cancel'] = $this->url->link('localisation/tax_rate', '', 'SSL');

        $this->getForm();
    }

    public function delete()
    {
        $this->language->load('localisation/tax_rate');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/tax_rate');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $tax_rate_id) {
                $this->model_localisation_tax_rate->deleteTaxRate($tax_rate_id);
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

            $this->redirect($this->url->link('localisation/tax_rate', '', 'SSL'));
        }

        $this->getList();
    }

    protected function getList()
    {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'tr.name';
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
            'href' => $this->url->link('localisation/tax_rate', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'classes' => $this->url->link('localisation/tax_class', '', 'SSL')
        ];

        $this->data['tax_rates'] = array();

        $data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit')
        );

        $tax_rate_total = $this->model_localisation_tax_rate->getTotalTaxRates();

        $results = $this->model_localisation_tax_rate->getTaxRates();

        foreach ($results as $result) {
            $this->data['tax_rates'][] = array(
                'tax_rate_id' => $result['tax_rate_id'],
                'name' => $result['name'],
                'rate' => $result['rate'],
                'type' => (
                    $result['type'] == 'F' ? $this->language->get('text_amount') : $this->language->get('text_percent')
                ),
                'geo_zone' => trim($result['geo_zone']),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
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

        $this->template = 'localisation/tax/rate_list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    
    private function getForm()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('localisation/tax_rate', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => ! isset( $this->request->get['tax_rate_id'] )  ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('localisation/tax_rate', '', 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->request->get['tax_rate_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $tax_rate_info = $this->model_localisation_tax_rate->getTaxRate($this->request->get['tax_rate_id']);
        }

        $this->load->model('localisation/tax_rate');

        if (isset($this->request->post['tax_rate_name'])) {
            $this->data['tax_rate_name'] = $this->request->post['tax_rate_name'];
        } elseif (!empty($this->request->get['tax_rate_id'])) {
            $this->data['tax_rate_name'] = $this->model_localisation_tax_rate
                ->getTaxRateDescriptions($this->request->get['tax_rate_id']);
        } else {
            $this->data['tax_rate_name'] = array();
        }

        if (isset($this->request->post['rate'])) {
            $this->data['rate'] = $this->request->post['rate'];
        } elseif (!empty($tax_rate_info)) {
            $this->data['rate'] = $tax_rate_info['rate'];
        } else {
            $this->data['rate'] = '';
        }

        if (isset($this->request->post['type'])) {
            $this->data['type'] = $this->request->post['type'];
        } elseif (!empty($tax_rate_info)) {
            $this->data['type'] = $tax_rate_info['type'];
        } else {
            $this->data['type'] = '';
        }

        if (isset($this->request->post['tax_rate_customer_group'])) {
            $this->data['tax_rate_customer_group'] = $this->request->post['tax_rate_customer_group'];
        } elseif (isset($this->request->get['tax_rate_id'])) {
            $this->data['tax_rate_customer_group'] = $this->model_localisation_tax_rate->getTaxRateCustomerGroups(
                $this->request->get['tax_rate_id']
            );
        } else {
            $this->data['tax_rate_customer_group'] = array();
        }

        $this->load->model('sale/customer_group');

        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

        if (isset($this->request->post['geo_zone_id'])) {
            $this->data['geo_zone_id'] = $this->request->post['geo_zone_id'];
        } elseif (!empty($tax_rate_info)) {
            $this->data['geo_zone_id'] = $tax_rate_info['geo_zone_id'];
        } else {
            $this->data['geo_zone_id'] = '';
        }

        $this->load->model('localisation/geo_zone');
        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->template = 'localisation/tax/rate_form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'localisation/tax_rate')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        $this->load->model('localisation/tax_rate');

        foreach ( $this->request->post['tax_rate_name'] as $language_id => $value )
        {
            if ( (utf8_strlen($value) < 3) || (utf8_strlen($value) > 32) )
            {
                $this->error['name_' . $language_id] = $this->language->get('error_name');
            }
        }

        if (!$this->request->post['rate']) {
            $this->error['rate'] = $this->language->get('error_rate');
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'localisation/tax_rate')) {
            $this->error['error'] = $this->language->get('error_permission');
        }

        $this->load->model('localisation/tax_class');

        foreach ($this->request->post['selected'] as $tax_rate_id) {
            $tax_rule_total = $this->model_localisation_tax_class->getTotalTaxRulesByTaxRateId($tax_rate_id);

            if ($tax_rule_total) {
                $this->error['warning'] = sprintf($this->language->get('error_tax_rule'), $tax_rule_total);
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
        $this->load->model('localisation/tax_rate');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $id) {
                $this->model_localisation_tax_rate->deleteTaxRate($id);
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
}
