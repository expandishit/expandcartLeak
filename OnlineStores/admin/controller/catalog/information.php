<?php

class ControllerCatalogInformation extends Controller
{
    private $error = array();

    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }
    }

    public function index()
    {
        $this->language->load('catalog/information');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/information');

        $this->data['limit_reached'] =
            ($this->model_catalog_information->getTotalInformations() + 1) > WEBPAGESLIMIT && $this->plan_id == '3'
        ;

        $this->getList();
    }

    
    public function insert()
    {
        $this->language->load('catalog/information');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/information');
        $this->load->model('teditor/teditor');

        $this->data['limit_reached'] = null;
        if ($this->plan_id == "3") {
            $pages_total = $this->model_catalog_information->getTotalInformations();
            $this->data['limit_reached']  = ($pages_total + 1 > WEBPAGESLIMIT) && $this->plan_id == '3';
        }

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( $this->data['limit_reached']){
                $this->error = $this->language->get('error_limit_reached');
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;
            }

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                $this->response->setOutput(json_encode($result_json));
                return;   
            }

            $this->model_catalog_information->addInformation($this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');


            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        if($this->plan_id == 3 && ($this->model_catalog_information->getTotalInformations() + 1) > WEBPAGESLIMIT){
            $this->base = "common/base";
            $this->data = $this->language->load('error/permission');
            $this->document->setTitle($this->language->get('heading_title'));
            $this->template = 'error/permission.expand';
            $this->response->setOutput($this->render_ecwig());
            return;
        }

        $this->data['arabic_fonts'] = $this->model_teditor_teditor->getLookupByLookupKey('ArabicFonts');
        $this->data['english_fonts'] = $this->model_teditor_teditor->getLookupByLookupKey('EnglishFonts');

        $this->data['links'] = [
            'submit' => $this->url->link('catalog/information/insert', '', 'SSL'),
            'cancel' => $this->url->link('catalog/information', '', 'SSL')
        ];

        $this->getForm();
    }

    

    
    public function update()
    {

        $information_id = (int)$this->request->get['information_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $information_id)) return false;


        $this->language->load('catalog/information');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/information');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_catalog_information->editInformation($this->request->get['information_id'], $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'catalog/information/update',
                'information_id=' . $this->request->get['information_id'],
                'SSL'
            ),
            'cancel' => $this->url->link('catalog/information', '', 'SSL')
        ];

        $this->data['cancel'] = $this->url->link('catalog/information', '', 'SSL');

        $this->getForm();
    }

    
    public function delete()
    {
        $this->language->load('catalog/information');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/information');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $information_id) {
                $this->model_catalog_information->deleteInformation($information_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('catalog/information', '', 'SSL'));
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
            'href' => $this->url->link('catalog/information', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->template = 'catalog/information/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    
    protected function getForm()
    {

        $this->data['cancel'] = $this->url->link('catalog/information', '', 'SSL');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/information', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => ! isset($this->request->get['information_id']) ?  $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('catalog/information', '', 'SSL'),
            'separator' => ' :: '
        );

        if (isset($this->request->get['information_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $information_info = $this->model_catalog_information->getInformation(
                $this->request->get['information_id']
            );
        }

        $this->load->model('localisation/language');

        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['information_description'])) {
            $this->data['information_description'] = $this->request->post['information_description'];
        } elseif (isset($this->request->get['information_id'])) {
            $this->data['information_description'] = $this->model_catalog_information->getInformationDescriptions(
                $this->request->get['information_id']
            );
        } else {
            $this->data['information_description'] = array();
        }

        $this->load->model('setting/store');

        $this->data['stores'] = $this->model_setting_store->getStores();

        if (isset($this->request->post['information_store'])) {
            $this->data['information_store'] = $this->request->post['information_store'];
        } elseif (isset($this->request->get['information_id'])) {
            $this->data['information_store'] = $this->model_catalog_information->getInformationStores(
                $this->request->get['information_id']
            );
        } else {
            $this->data['information_store'] = array(0);
        }

        if (isset($this->request->post['keyword'])) {
            $this->data['keyword'] = $this->request->post['keyword'];
        } elseif (!empty($information_info)) {
            $this->data['keyword'] = $information_info['keyword'];
        } else {
            $this->data['keyword'] = '';
        }

        if (isset($this->request->post['bottom'])) {
            $this->data['bottom'] = $this->request->post['bottom'];
        } elseif (!empty($information_info)) {
            $this->data['bottom'] = $information_info['bottom'];
        } else {
            $this->data['bottom'] = 0;
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($information_info)) {
            $this->data['status'] = $information_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        if (isset($this->request->post['sort_order'])) {
            $this->data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($information_info)) {
            $this->data['sort_order'] = $information_info['sort_order'];
        } else {
            $this->data['sort_order'] = '';
        }

        if (isset($this->request->post['information_layout'])) {
            $this->data['information_layout'] = $this->request->post['information_layout'];
        } elseif (isset($this->request->get['information_id'])) {
            $this->data['information_layout'] = $this->model_catalog_information->getInformationLayouts(
                $this->request->get['information_id']
            );
        } else {
            $this->data['information_layout'] = array();
        }

        $this->load->model('design/layout');

        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        $this->template = 'catalog/information/form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    
    private function validateForm()
    {

        if ( ! $this->user->hasPermission('modify', 'catalog/information') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        foreach ( $this->request->post['information_description'] as $language_id => $value )
        {
            if ( (utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 64) )
            {
                $this->error['title_' . $language_id] = $this->language->get('error_title');
            }

            if ( utf8_strlen($value['description']) < 3 )
            {
                $this->error['description_' . $language_id] = $this->language->get('error_description');
            }
        }

        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    
    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'catalog/information')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('setting/store');

        foreach ($this->request->post['selected'] as $information_id) {
            if ($this->config->get('config_account_id') == $information_id) {
                $this->error['warning'] = $this->language->get('error_account');
            }

            if ($this->config->get('config_checkout_id') == $information_id) {
                $this->error['warning'] = $this->language->get('error_checkout');
            }

            if ($this->config->get('config_affiliate_id') == $information_id) {
                $this->error['warning'] = $this->language->get('error_affiliate');
            }

            $store_total = $this->model_setting_store->getTotalStoresByInformationId($information_id);

            if ($store_total) {
                $this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function dtHandler()
    {
        $this->load->model('catalog/information');
        $request = $this->request->request;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = [
            0 => 'information_od',
            1 => 'title',
            2 => 'status',
            3 => 'sort_order',
        ];

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_catalog_information->dtHandler([
            'filter_name' => $search,
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'limit' => $length
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

    public function dtDelete()
    {
        $this->language->load("catalog/information");
        $this->load->model("catalog/information");

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $id) {
                $this->model_catalog_information->deleteInformation($id);
            }

            $result_json['status'] = 'success';
            $result_json['title'] = $this->language->get('notification_success_title');
            $result_json['message'] = $this->language->get('message_deleted_successfully');
        } else {
            $result_json['status'] = 'error';
            $result_json['title'] = $this->language->get('notification_error_title');
            $result_json['errors'] = $this->error;
        }

        $result_json['limit_reached'] =
            ($this->model_catalog_information->getTotalInformations() + 1) > WEBPAGESLIMIT && $this->plan_id == '3'
        ;

        $this->response->setOutput(json_encode($result_json));
        return;
    }

    public function dtUpdateStatus()
    {
        $this->load->model("catalog/information");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            $data = $this->model_catalog_information->getInformation($id);
            $dataDescription = $this->model_catalog_information->getInformationDescriptions($id);
            $data["status"] = $status;
            $data["information_description"] = $dataDescription;
            $this->model_catalog_information->editInformation($id, $data);

            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_langstatus_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_langstatus_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
