<?php

class ControllerModuleAffiliatePromo extends Controller
{
    private $errors = [];

    public function install()
    {
        $this->load->model('module/affiliate_promo');
        $this->model_module_affiliate_promo->install();
    }

    public function uninstall()
    {
        $this->load->model('module/affiliate_promo');
        $this->model_module_affiliate_promo->uninstall();
    }

    public function index()
    {
        $this->language->load('module/affiliate_promo');
        $this->document->setTitle($this->language->get('affiliate_promo_title'));
        $this->data['affiliate_promo'] = $this->config->get('affiliate_promo');
        // display 'affiliates_create_only_code_type_discount' option as enabled by default
        if (!$this->data['affiliate_promo']){
            $this->data['affiliate_promo'] = array('affiliates_create_only_code_type_discount' => 1);
        }
        $this->data['submit_link'] = $this->url->link('module/affiliate_promo/saveSettings', '', 'SSL');
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('affiliate_promo_title'),
            'href'      => $this->url->link('module/affiliate_promo', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/affiliate_promo/settings.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->response->setOutput($this->render_ecwig());
    }

    public function list()
    {
        $this->language->load('module/affiliate_promo');
        $this->document->setTitle($this->language->get('affiliate_promo_title'));

        $this->data['affiliate_promo'] = $this->config->get('affiliate_promo');
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', 'type=apps', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('affiliate_promo_title'),
            'href'      => $this->url->link('module/affiliate_promo/list', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->template = 'module/affiliate_promo/list.expand';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data['ajax_link'] = $this->url->link('module/affiliate_promo/getAjaxList', '', 'SSL');
        $this->data['ajax_delete_link'] = $this->url->link('module/affiliate_promo/delete', '', 'SSL');

        $this->response->setOutput($this->render_ecwig());
    }

    public function getAjaxList()
    {
        $this->load->model('module/affiliate_promo');

        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $request = $this->request->request;

        $start = $request['start'];
        $length = $request['length'];

        $columns = [
            0 => 'code',
            1 => 'type',
            2 => 'discount',
        ];

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_module_affiliate_promo->getPromoCodes([
            'search' => $request['search']['value'],
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = [];

        foreach($return['data'] as $row) {
            $records[] = [
                'affiliate_name' => $row['affiliate_name'],
                'affiliate_id' => $row['affiliate_id'],
                'coupon_id' => $row['coupon_id'],
                'code' => $row['code'],
                'type' => $row['type'],
                'discount' => $row['discount']
            ];
        }
        
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        $json_data = [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        ];
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function delete()
    {
        $this->load->model('module/affiliate_promo');
        foreach($this->request->post['selected'] as $id) {
            $this->model_module_affiliate_promo->delete($id);
        }
        $this->response->setOutput(json_encode(['success' => 1]));
    }

    public function saveSettings()
    {
        $this->language->load('module/affiliate_promo');
        if (!$this->validate()) {
            $json['success'] = '0';
            $json['errors'] = $this->errors;
            $this->response->setOutput(json_encode($json));
            return;
        }
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('affiliate_promo', $this->request->post);
        $json['success'] = '1';
        $json['success_msg'] = $this->language->get('affiliate_promo_success_save');
        $this->response->setOutput(json_encode($json));
    }

    private function validate()
    {
        if (empty($this->request->post['affiliate_promo']['max_coupon_percent_ratio'])) {
            $this->errors[] = $this->language->get('max_coupon_percent_ratio_required');
        }

        if (empty($this->request->post['affiliate_promo']['max_coupon_fixed_ratio'])) {
            $this->errors[] = $this->language->get('max_coupon_fixed_ratio_required');
        }

        return empty($this->errors);
    }
}