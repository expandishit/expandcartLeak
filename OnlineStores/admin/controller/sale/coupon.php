<?php

class ControllerSaleCoupon extends Controller
{
    private $error = array();

    private $plan_id = PRODUCTID;

    public function __construct($registry){
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        if ($this->plan_id ==3 && ! $this->config->get('coupon_trial_ended')
        && $this->config->get('trial_is_active')
        ){
            $this->load->model('sale/coupon');
            $this->model_sale_coupon->disableCoupons();

            $this->load->model('setting/setting');
            $data['coupon_trial_ended']=1;
            $this->model_setting_setting->insertUpdateSetting('trial', $data);
        }
    }
    public function index()
    {

        $this->language->load('sale/coupon');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/coupon');

        $this->data['limit_reached'] =
            ($this->model_sale_coupon->getTotalCoupons() + 1) > (COUPONSLIMIT) &&
            $this->plan_id == '3'
        ;

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('sale/coupon');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/coupon');

        $this->data['limit_reached'] = null;
        if ($this->plan_id == "3") {
            $coupon_total = $this->model_sale_coupon->getTotalCoupons();
            $this->data['limit_reached']  = ($coupon_total + 1 > COUPONSLIMIT) && $this->plan_id == '3';
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

            if ( !$this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            if( $this->request->post['automatic_apply']==1){
                $this->request->post['code'] = $this->getCode();
            }
            $this->handlePostData();

            $coupon_id = $this->model_sale_coupon->addCoupon($this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("coupon");
            if($pageStatus){
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $coupon_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'coupon';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
            $result_json['success'] = '1';

            $result_json['redirect'] = '1';
            $result_json['to'] = (string) $this->url->link('sale/coupon', '', 'SSL');

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        if($this->plan_id == 3 && ($this->model_sale_coupon->getTotalCoupons() + 1) > COUPONSLIMIT){
            $this->base = "common/base";
            $this->data = $this->language->load('error/permission');
            $this->document->setTitle($this->language->get('heading_title'));
            $this->template = 'error/permission.expand';
            $this->response->setOutput($this->render_ecwig());
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('sale/coupon/insert', '', 'SSL'),
            'cancel' => $this->url->link('sale/coupon', '', 'SSL'),
        ];

        $this->getForm();
    }

    public function update()
    {
        $not_allowed = false;
        $this->language->load('sale/coupon');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/coupon');

        $coupon_id = (int)$this->request->get['coupon_id'];

        if($this->plan_id == 3){
            $last_coupon_in_limit_id = $this->model_sale_coupon->getLastCouponInLimitId();

            if($last_coupon_in_limit_id && $coupon_id > $last_coupon_in_limit_id){
                $not_allowed= true;
                $this->data['limit_reached']=1;
            }
        }

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if(!preg_match("/^[1-9][0-9]*$/", $coupon_id)) return false;

            if ( ! $this->validateForm() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }
            // get coupon current value for log history
            $this->load->model('loghistory/coupon');
            $oldValue = $this->model_sale_coupon->getCoupon($this->request->get['coupon_id']);
            $oldValue['coupon_category'] = $this->model_loghistory_coupon->getCouponCategories($this->request->get['coupon_id'],0);
            $oldValue['coupon_product'] = $this->model_loghistory_coupon->getCouponProducts($this->request->get['coupon_id'],0);
            $oldValue['coupon_manufacturer'] = $this->model_loghistory_coupon->getCouponManufacturer($this->request->get['coupon_id'],0);

            $oldValue['coupon_category_excluded'] = $this->model_loghistory_coupon->getCouponCategories($this->request->get['coupon_id'],1);
            $oldValue['coupon_product_excluded'] = $this->model_loghistory_coupon->getCouponProducts($this->request->get['coupon_id'],1);
            $oldValue['coupon_manufacturer_excluded'] = $this->model_loghistory_coupon->getCouponManufacturer($this->request->get['coupon_id'],1);

            if( $this->request->post['automatic_apply']==1){
                $this->request->post['code'] = $oldValue['code'];
            }

            if($not_allowed){
                $this->request->post['status']=0;
            }

            $this->handlePostData();
            $this->model_sale_coupon->editCoupon($this->request->get['coupon_id'], $this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("coupon");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $this->request->get['coupon_id'];
                $log_history['old_value'] = json_encode($oldValue);
                $log_history['new_value'] = json_encode($this->request->post);
                $log_history['type'] = 'coupon';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link('sale/coupon/update', 'coupon_id=' . $this->request->get['coupon_id'], 'SSL'),
            'history' => $this->url->link(
                'sale/coupon/history',
                'coupon_id=' . $this->request->get['coupon_id'],
                'SSL'
            ),
            'cancel' => $this->url->link('sale/coupon', '', 'SSL'),
        ];

        $this->getForm();
    }

    public function delete()
    {
        $this->language->load('sale/coupon');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/coupon');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            $this->load->model('loghistory/histories');
            foreach ($this->request->post['selected'] as $coupon_id) {
                // get copoun current value for log history
                $oldValue = $this->model_sale_coupon->getCoupon($coupon_id);
                $this->load->model('loghistory/coupon');
                $oldValue['coupon_category'] = $this->model_loghistory_coupon->getCouponCategories($coupon_id,0);
                $oldValue['coupon_product'] = $this->model_loghistory_coupon->getCouponProducts($coupon_id,0);
                $oldValue['coupon_manufacturer'] = $this->model_loghistory_coupon->getCouponManufacturer($coupon_id,0);

                $oldValue['coupon_category_excluded'] = $this->model_loghistory_coupon->getCouponCategories($coupon_id,1);
                $oldValue['coupon_product_excluded'] = $this->model_loghistory_coupon->getCouponProducts($coupon_id,1);
                $oldValue['coupon_manufacturer_excluded'] = $this->model_loghistory_coupon->getCouponManufacturer($coupon_id,1);

                $this->model_sale_coupon->deleteCoupon($coupon_id);
                // add data to log_history
                $this->load->model('setting/audit_trail');
                $pageStatus = $this->model_setting_audit_trail->getPageStatus("coupon");
                if($pageStatus){
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $coupon_id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'coupon';
                    $this->model_loghistory_histories->addHistory($log_history);
                }

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

            $this->redirect($this->url->link('sale/coupon', '', 'SSL'));
        }

        $this->getList();
    }

    protected function validateCopy()
    {
        if (!$this->user->hasPermission('modify', 'sale/coupon')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function copy()
    {
        $this->language->load('sale/coupon');
        $this->load->model('sale/coupon');
        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->request->post['selected'])) {

            if (!$this->validateCopy()) {
                $response['status'] = '0';
                $response['title'] = $this->language->get('notification_error_title');
                $response['errors'] = $this->error;
                $this->response->setOutput(json_encode($response));
                return;
            }

            foreach ($this->request->post['selected'] as $coupon_id) {
                $new_coupon_id = $this->model_sale_coupon->copyCoupon($coupon_id);
                $response['new_ids'][] = $new_coupon_id;

            }
            $response['status'] = '1';
            $response['success_msg'] = $this->language->get('text_success');
            $this->response->setOutput(json_encode($response));
            return;
        }
        $response['status'] = '0';
        $response['errors'] = $this->language->get('error_invalid_parameters');
        $this->response->setOutput(json_encode($response));
        return;
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
            'href' => $this->url->link('sale/coupon', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['insert'] = $this->url->link('sale/coupon/insert', '', 'SSL');
        $this->data['delete'] = $this->url->link('sale/coupon/delete', '', 'SSL');

        $this->data['coupons'] = array();

        $data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit')
        );

        $coupon_total = $this->model_sale_coupon->getTotalCoupons();
        $results = $this->model_sale_coupon->getCoupons($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('sale/coupon/update', 'coupon_id=' . $result['coupon_id'] . $url, 'SSL')
            );

            $this->data['coupons'][] = array(
                'coupon_id' => $result['coupon_id'],
                'name' => $result['name'],
                'code' => $result['code'],
                'discount' => $result['discount'],
                'date_start' => date($this->language->get('date_format_short'), strtotime($result['date_start'])),
                'date_end' => date($this->language->get('date_format_short'), strtotime($result['date_end'])),
                'status' => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
                'selected' => isset($this->request->post['selected']) && in_array($result['coupon_id'], $this->request->post['selected']),
                'action' => $action
            );
        }

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['column_code'] = $this->language->get('column_code');
        $this->data['column_discount'] = $this->language->get('column_discount');
        $this->data['column_date_start'] = $this->language->get('column_date_start');
        $this->data['column_date_end'] = $this->language->get('column_date_end');
        $this->data['column_status'] = $this->language->get('column_status');
        $this->data['column_action'] = $this->language->get('column_action');

        $this->data['button_insert'] = $this->language->get('button_insert');
        $this->data['button_delete'] = $this->language->get('button_delete');

//        if (isset($this->error['warning'])) {
//            $this->data['error_warning'] = $this->error['warning'];
//        } else {
//            $this->data['error_warning'] = '';
//        }

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

        $this->data['sort_name'] = HTTPS_SERVER . 'index.php?route=sale/coupon?sort=name' . $url;
        $this->data['sort_code'] = HTTPS_SERVER . 'index.php?route=sale/coupon?sort=code' . $url;
        $this->data['sort_discount'] = HTTPS_SERVER . 'index.php?route=sale/coupon?sort=discount' . $url;
        $this->data['sort_date_start'] = HTTPS_SERVER . 'index.php?route=sale/coupon?sort=date_start' . $url;
        $this->data['sort_date_end'] = HTTPS_SERVER . 'index.php?route=sale/coupon?sort=date_end' . $url;
        $this->data['sort_status'] = HTTPS_SERVER . 'index.php?route=sale/coupon?sort=status' . $url;

        $this->data['affiliate_promo'] = \Extension::isInstalled('affiliate_promo') && $this->config->get('affiliate_promo')['status'];

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $coupon_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = HTTPS_SERVER . 'index.php?route=sale/coupon?page={page}';

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        $this->data['limit_reached']  = ($coupon_total + 1 > COUPONSLIMIT) && $this->plan_id == '3';

        if ($coupon_total == 0){
            $this->template = 'sale/coupon/empty.expand';
        }else{
            $this->template = 'sale/coupon/list.expand';
        }

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function getForm()
    {
        $get_products=array();
        $get_categories=array();
        $get_manufacturers=array();
        $details=null;
        $this->data['token'] = null;

        if (isset($this->request->get['coupon_id'])) {
            $this->data['coupon_id'] = $this->request->get['coupon_id'];
        } else {
            $this->data['coupon_id'] = false;
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/coupon', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => !isset($this->request->get['coupon_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('sale/coupon', '', 'SSL'),
            'separator' => ' :: '
        );

        if (!isset($this->request->get['coupon_id'])) {
            $this->data['action'] = $this->url->link('sale/coupon/insert', '', 'SSL');
        } else {
            $this->data['action'] = $this->url->link('sale/coupon/update', 'coupon_id=' . $this->request->get['coupon_id'] . $url, 'SSL');
        }

        $this->data['cancel'] = $this->url->link('sale/coupon', '', 'SSL');

        if (isset($this->request->get['coupon_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
            $coupon_info = $this->model_sale_coupon->getCoupon($this->request->get['coupon_id']);
            $details = json_decode($coupon_info['details'],true);
        }

        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } elseif (!empty($coupon_info)) {
            $this->data['name'] = $coupon_info['name'];
        } else {
            $this->data['name'] = '';
        }

        if (isset($this->request->post['code'])) {
            $this->data['code'] = $this->request->post['code'];
        } elseif (!empty($coupon_info)) {
            $this->data['code'] = $coupon_info['code'];
        } else {
            $this->data['code'] = '';
        }

        if (isset($this->request->post['type'])) {
            $this->data['type'] = $this->request->post['type'];
        } elseif (!empty($coupon_info)) {
            $this->data['type'] = $coupon_info['type'];
        } else {
            $this->data['type'] = '';
        }

        if (isset($this->request->post['discount'])) {
            $this->data['discount'] = $this->request->post['discount'];
        } elseif (!empty($coupon_info)) {
            $this->data['discount'] = $coupon_info['discount'];
        } else {
            $this->data['discount'] = '';
        }

        if (isset($this->request->post['maximum_limit'])) {
            $this->data['maximum_limit'] = $this->request->post['maximum_limit'];
        } elseif (!empty($coupon_info)) {
            if ($coupon_info['minimum_to_apply'] > 0){
                $this->data['minimum_to_apply_enabled'] = true;
                $this->data['maximum_limit'] = $coupon_info['maximum_limit'];
            }
        } else {
            $this->data['maximum_limit'] = '';
        }

        if (isset($this->request->post['minimum_to_apply'])) {
            $this->data['minimum_to_apply'] = $this->request->post['minimum_to_apply'];
        } elseif (!empty($coupon_info)) {
            if ($coupon_info['minimum_to_apply'] > 0){
                $this->data['minimum_to_apply_enabled'] = true;
                $this->data['minimum_to_apply'] = $coupon_info['minimum_to_apply'];
            }
        } else {
            $this->data['minimum_to_apply'] = '';
        }

        if (isset($this->request->post['logged'])) {
            $this->data['logged'] = $this->request->post['logged'];
        } elseif (!empty($coupon_info)) {
            $this->data['logged'] = $coupon_info['logged'];
        } else {
            $this->data['logged'] = '';
        }

        if (isset($this->request->post['shipping'])) {
            $this->data['shipping'] = $this->request->post['shipping'];
        } elseif (!empty($coupon_info)) {
            $this->data['shipping'] = $coupon_info['shipping'];
            if ($this->data['shipping'] == 1){
                $this->data['type']="S";
            }
        } else {
            $this->data['shipping'] = '';
        }

        if (isset($this->request->post['total'])) {
            $this->data['total'] = $this->request->post['total'];
        } elseif (!empty($coupon_info)) {
            $this->data['total'] = $coupon_info['total'];
        } else {
            $this->data['total'] = '';
        }

        if (isset($this->request->post['notification_status'])) {
            $this->data['notification_status'] = $this->request->post['notification_status'];
        } elseif (!empty($coupon_info)) {
            $this->data['notification_status'] = $coupon_info['notification_status'];
        } else {
            $this->data['notification_status'] = '';
        }

        if (isset($this->request->post['details'])) {
            $this->data['details'] = $this->request->post['details'];
        } elseif (!empty($coupon_info)) {
            $this->data['details'] = $details;
        } else {
            $this->data['details'] = null;
        }

        if (isset($this->request->post['coupon_product'])) {
            $products = $this->request->post['coupon_product'];
        } elseif (isset($this->request->get['coupon_id'])) {
            $products = $this->model_sale_coupon->getCouponProducts($this->request->get['coupon_id'],true);
            if (isset($details['get_item_from']) && $details['get_item_from'] == "product"){
                $get_products = $details['coupon_product_get'];
            }
        } else {
            $products = array();
        }

        $this->load->model("sale/customer_group");

        $customer_groups = $this->model_sale_customer_group->getCustomerGroups();
        $coupon_groups = $this->model_sale_coupon->getCouponGroup($this->request->get['coupon_id']);

        $this->data['coupon_group'] = array();

        foreach ($customer_groups as $group) {
            if (in_array($group['customer_group_id'], $coupon_groups)) {
                $this->data['coupon_group'][] = ['group_id' => $group['customer_group_id'], 'name' => $group['name'], 'selected' => true];
            } else {
                $this->data['coupon_group'][] = ['group_id' => $group['customer_group_id'], 'name' => $group['name'], 'selected' => false];
            }
        }

        $this->load->model("sale/customer");
        $this->data['coupon_customer'] = array();
        $coupon_customers =array();
        $coupon_customers_ids = $this->model_sale_coupon->getCouponCustomer($this->request->get['coupon_id']);
        if($coupon_customers_ids&&count($coupon_customers_ids) >0){
            $coupon_customers = $this->model_sale_customer->getCustomers(['filter_customer_ids'=>$coupon_customers_ids]);
        }
        foreach ($coupon_customers as $coupon_customer) {
            $this->data['coupon_customer'][] =
                [
                    'customer_id' => $coupon_customer['customer_id'],
                    'name' => $coupon_customer['name'],
                    'selected' => true
                ];
        }
        $this->data['number_of_usage'] = $this->model_sale_coupon->getTotalCouponHistories($this->request->get['coupon_id']);
        $this->load->model('catalog/product');

//        $this->data['all_products'] = $this->model_catalog_product->getProductsFields(['name']);

        $this->data['coupon_product'] = array();

        foreach ($products as $product) {
            $product_info = $this->model_catalog_product->getProduct($product['product_id']);

            if ($product_info) {
                $this->data['coupon_product'][] = ['product_id'=>$product_info['product_id'],'name'=>$product_info['name'],'excluded'=>$product['product_excluded']];
            }
        }

        foreach ($get_products as $id) {
            $product_info = $this->model_catalog_product->getProduct($id);
            if ($product_info) {
                $this->data['coupon_product_get'][] = ['product_id'=>$product_info['product_id'],'name'=>$product_info['name'],'excluded'=>0];
            }
        }

        if (isset($this->request->post['coupon_category'])) {
            $categories = $this->request->post['coupon_category'];
        } elseif (isset($this->request->get['coupon_id'])) {
            $categories = $this->model_sale_coupon->getCouponCategories($this->request->get['coupon_id'],true);
            if (isset($details['get_item_from']) && $details['get_item_from'] == "category"){
                $get_categories = $details['coupon_category_get'];
            }
        } else {
            $categories = array();
        }

        $this->load->model('catalog/category');


//        $this->data['all_cats'] = $this->model_catalog_category->getCategories();

        $this->data['coupon_category'] = array();

        foreach ($categories as $category) {
            $category_info = $this->model_catalog_category->getCategory($category['category_id']);
            if ($category_info) {
                $this->data['coupon_category'][] = ['category_id'=>$category_info['category_id'],'name'=>$category_info['name'],'excluded'=>$category['category_excluded']];
            }
        }

        $this->data['coupon_category_get'] = array();

        foreach ($get_categories as $id) {
            $category_info = $this->model_catalog_category->getCategory($id);
            if ($category_info) {
                $this->data['coupon_category_get'][] = ['category_id'=>$category_info['category_id'],'name'=>$category_info['name'],'excluded'=>0];
            }
        }

        if (isset($this->request->post['coupon_manufacturer'])) {
            $manufacturers = $this->request->post['coupon_manufacturer'];
        } elseif (isset($this->request->get['coupon_id'])) {
            $manufacturers = $this->model_sale_coupon->getCouponManufacturer($this->request->get['coupon_id'],true);
            if (isset($details['get_item_from']) && $details['get_item_from'] == "manufacturer"){
                $get_manufacturers = $details['coupon_manufacturer_get'];
            }
        } else {
            $manufacturers = array();
        }

        $this->load->model('catalog/manufacturer');

        $this->data['coupon_manufacturer'] = array();

        foreach ($manufacturers as $manufacturers) {
            $manufacturers_info = $this->model_catalog_manufacturer->getManufacturer($manufacturers['manufacturer_id']);

            if ($manufacturers_info) {
                $this->data['coupon_manufacturer'][] = ['manufacturer_id'=>$manufacturers_info['manufacturer_id'], 'name'=>$manufacturers_info['name'],'excluded'=>$manufacturers['manufacturer_excluded']];
            }
        }

        $this->data['coupon_manufacturer_get'] = array();
        foreach ($get_manufacturers as $id) {
            $manufacturers_info = $this->model_catalog_manufacturer->getManufacturer($id);

            if ($manufacturers_info) {
                $this->data['coupon_manufacturer_get'][] = [
                    'manufacturer_id'=>$manufacturers_info['manufacturer_id'],
                    'name'=>$manufacturers_info['name'],
                    'excluded'=>0
                ];
            }
        }

        if (isset($this->request->post['date_start'])) {
            $this->data['date_start'] = $this->request->post['date_start'];
        } elseif (!empty($coupon_info)) {
            $this->data['date_start'] = date('Y-m-d', strtotime($coupon_info['date_start']));
        } else {
            $this->data['date_start'] = date('Y-m-d', time());
        }

        if (isset($this->request->post['date_end'])) {
            $this->data['date_end'] = $this->request->post['date_end'];
        } elseif (!empty($coupon_info)) {
            $this->data['date_end'] = date('Y-m-d', strtotime($coupon_info['date_end']));
        } else {
            $this->data['date_end'] = date('Y-m-d', time());
        }

        if (isset($this->request->post['uses_total'])) {
            $this->data['uses_total'] = $this->request->post['uses_total'];
        } elseif (!empty($coupon_info)) {
            if ($coupon_info['uses_total'] > 0){
                $this->data['uses_total_enabled'] = true;
                $this->data['uses_total'] = $coupon_info['uses_total'];
            }
        } else {
            $this->data['uses_total'] = '';
        }

        if (isset($this->request->post['uses_customer'])) {
            $this->data['uses_customer'] = $this->request->post['uses_customer'];
        } elseif (!empty($coupon_info)) {
            if ($coupon_info['uses_customer'] > 0){
                $this->data['uses_customer_enabled'] = true;
                $this->data['uses_customer'] = $coupon_info['uses_customer'];
            }
        } else {
            $this->data['uses_customer'] = 1;
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($coupon_info)) {
            $this->data['status'] = $coupon_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        if (isset($this->request->post['notify_mobile'])) {
            $this->data['notify_mobile'] = $this->request->post['notify_mobile'];
        } elseif (!empty($coupon_info)) {
            $this->data['notify_mobile'] = $coupon_info['notify_mobile'];
        } else {
            $this->data['notify_mobile'] = '';
        }

        if (isset($this->request->post['automatic_apply'])) {
            $this->data['automatic_apply'] = $this->request->post['automatic_apply'];
        } elseif (!empty($coupon_info)) {
            $this->data['automatic_apply'] = $coupon_info['automatic_apply'];
        } else {
            $this->data['automatic_apply'] = $this->request->get['automatic_apply']?:0;
        }

        //Affiliate promo app
        if (\Extension::isInstalled('affiliate_promo') && $this->config->get('affiliate_promo')['status'] == 1) {
            $this->data['affiliate_promo'] = true;
            $this->data['coupon_creator'] = false;
            $this->load->model('sale/affiliate');
            if (isset($this->request->post['from_affiliate'])) {
                $this->data['from_affiliate'] = $this->request->post['from_affiliate'];
            } elseif (!empty($coupon_info)) {
                $this->data['from_affiliate'] = $coupon_info['from_affiliate'];
                if ($coupon_info['from_affiliate'] && !empty($coupon_info['from_affiliate'])){
                    $coupon_creator = $this->model_sale_affiliate->getAffiliateByCoupon($coupon_info['coupon_id']);
                    if (isset($coupon_creator['affiliate_id']) && !empty($coupon_creator['affiliate_id'])){
                        $this->data['coupon_creator'] = $coupon_creator['affiliate_id'];
                    }
                }
            } else {
                $this->data['from_affiliate'] = 0;
            }

            $this->data['affiliates'] = $this->model_sale_affiliate->getAffiliates(['selections' => ['affiliate_id', 'CONCAT(firstname, \' \', lastname) AS name', 'email']]);
        }
        //////////////////////

        $this->template = 'sale/coupon/form-v2.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    protected function validateForm()
    {
        $this->initializer([
            'catalog/product'
        ]);

        if ( ! $this->user->hasPermission('modify', 'sale/coupon') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( (utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 128) )
        {
            $this->error['name'] = $this->language->get('error_name');
        }

        if
        (
            ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 20)) &&
            $this->request->post['automatic_apply'] != 1
        ) {
            $this->error['code'] = $this->language->get('error_code');
        }

        // minimum_to_apply
        if ($this->request->post["minimum_to_apply_enabled"] && !($this->request->post['type']=="B" && $this->request->post["details"]["buy_option"]== "purchase")){
            if ((utf8_strlen($this->request->post['minimum_to_apply']) == 0 || $this->request->post['minimum_to_apply'] <= 0)){
                $this->error['minimum_to_apply'] = $this->language->get('error_coupon_minimum_to_apply');
            }
        }

        // uses_total
        if ($this->request->post["uses_total_enabled"]){
            if ((utf8_strlen($this->request->post['uses_total']) == 0 || $this->request->post['uses_total'] <= 0)){
                $this->error['uses_total'] = $this->language->get('error_coupon_uses_total');
            }
        }

        // uses_customer
        if ($this->request->post["uses_customer_enabled"]){
            if ((utf8_strlen($this->request->post['uses_customer']) == 0 || $this->request->post['uses_customer'] <= 0)){
                $this->error['uses_customer'] = $this->language->get('error_coupon_uses_customer');
            }
        }

        // coupon_customer
        if ($this->request->post["details"]["customer_option"] == "customer"){
            if (! $this->request->post['coupon_customer']){
                $this->error['coupon_customer'] = $this->language->get('error_coupon_customer');
            }
        }

        // coupon_group
        if ($this->request->post["details"]["customer_option"] == "group"){
            if (! $this->request->post['coupon_group']){
                $this->error['coupon_group'] = $this->language->get('error_coupon_group');
            }
        }

        if ($this->request->post['type']== 'F' || $this->request->post['type']== 'P'){
            // discount
            if ($this->request->post['type']== 'F' && (utf8_strlen($this->request->post['discount']) == 0 || $this->request->post['discount'] <= 0)){
                $this->error['discount'] = $this->language->get('error_coupon_discount');
            }

            if ($this->request->post['type']== 'P' && (utf8_strlen($this->request->post['percent']) == 0 || $this->request->post['percent'] <= 0)){
                $this->error['percent'] = $this->language->get('error_coupon_percent');
            }

            if ($this->request->post["details"]["apply_item_from"] == "category"){
                if (! $this->request->post['coupon_category']){
                    $this->error['coupon_category'] = $this->language->get('error_coupon_category');
                }
            }

            if ($this->request->post["details"]["apply_item_from"] == "manufacturer"){
                if (! $this->request->post['coupon_manufacturer']){
                    $this->error['coupon_manufacturer'] = $this->language->get('error_coupon_manufacturer');
                }
            }

            if ($this->request->post["details"]["apply_item_from"] == "product"){
                if (! $this->request->post['coupon_product']){
                    $this->error['coupon_product'] = $this->language->get('error_coupon_product');
                }
            }
            if ($this->request->post["details"]["exclude_item"] == "product"){
                if (! $this->request->post['coupon_product_excluded']){
                    $this->error['coupon_product_excluded'] = $this->language->get('error_coupon_product_excluded');
                }
            }
            if ($this->request->post["details"]["exclude_item"] == "manufacturer"){
                if (! $this->request->post['coupon_manufacturer_excluded']){
                    $this->error['coupon_manufacturer_excluded'] = $this->language->get('error_coupon_manufacturer_excluded');
                }
            }
            if ($this->request->post["details"]["exclude_item"] == "category"){
                if (! $this->request->post['coupon_category_excluded']){
                    $this->error['coupon_category_excluded'] = $this->language->get('error_coupon_category_excluded');
                }
            }

        }

        if ($this->request->post['type']== 'B'){

            $details =  $this->request->post["details"];
            if ($details["buy_option"] == "quantity"){
                $this->validateRequiredNumber($this->request->post["details"]["buy_quantity"],'buy_quantity','error_coupon_buy_quantity');
            }else{
                $this->validateRequiredNumber($this->request->post["details"]["buy_amount"],'buy_amount','error_coupon_buy_amount');
            }

            $this->validateRequiredNumber($this->request->post["details"]["get_quantity"],'get_quantity','error_coupon_buy_amount');

            if ($this->request->post["details"]["buy_item_from"] == "product"){
                if (! $this->request->post['coupon_product_buy']){
                    $this->error['coupon_product_buy'] = $this->language->get('error_coupon_product_buy');
                }
            }

            if ($this->request->post["details"]["buy_item_from"] == "manufacturer"){
                if (! $this->request->post['coupon_manufacturer_buy']){
                    $this->error['coupon_manufacturer_buy'] = $this->language->get('error_coupon_manufacturer_buy');
                }
            }

            if ($this->request->post["details"]["buy_item_from"] == "category"){
                if (! $this->request->post['coupon_category_buy']){
                    $this->error['coupon_category_buy'] = $this->language->get('error_coupon_category_buy');
                }
            }

            if ($this->request->post["details"]["get_item_from"] == "product"){
                if (! $this->request->post['coupon_product_get']){
                    $this->error['coupon_product_get'] = $this->language->get('error_coupon_product_get');
                }
            }

            if ($this->request->post["details"]["get_item_from"] == "manufacturer"){
                if (! $this->request->post['coupon_manufacturer_get']){
                    $this->error['coupon_manufacturer_get'] = $this->language->get('error_coupon_manufacturer_get');
                }
            }

            if ($this->request->post["details"]["get_item_from"] == "category"){
                if (! $this->request->post['coupon_category_get']){
                    $this->error['coupon_category_get'] = $this->language->get('error_coupon_category_get');
                }
            }

            if ($this->request->post["details"]["buy_option"]== "purchase" && $this->request->post["details"]["buy_item_from"]== "product"){

                $ids = $this->request->post['coupon_product_buy'];
                $products = $this->product->getProductsByIds($ids);
                $status = false;

                foreach ($products as $product){
                    if( $product['price'] <= $this->request->post['details']['buy_amount'] ) {
                        $status = true;
                        continue;
                    }
                }

                if (!$status){
                    $this->error['buy_amount'] = $this->language->get('error_small_buy_amount');
                }
            }
        }

        $coupon_info = isset($this->request->post['code']) ? $this->model_sale_coupon->getCouponByCode($this->request->post['code']) : false;

        if ( $coupon_info )
        {
            if (!isset($this->request->get['coupon_id'])) {
                $this->error['code'] = $this->language->get('error_exists');
            } elseif ($coupon_info['coupon_id'] != $this->request->get['coupon_id']) {
                $this->error['code'] = $this->language->get('error_exists');
            }
        }

        if ( $this->error && !isset($this->error['error']) )
        {
    //        $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'sale/coupon')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function history()
    {
        $this->language->load('sale/coupon');

        $this->load->model('sale/coupon');

        $this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->data['column_order_id'] = $this->language->get('column_order_id');
        $this->data['column_customer'] = $this->language->get('column_customer');
        $this->data['column_amount'] = $this->language->get('column_amount');
        $this->data['column_date_added'] = $this->language->get('column_date_added');
        $this->data['coupon_id'] = $this->request->get['coupon_id'];

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/coupon', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => !isset($this->request->get['coupon_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href' => $this->url->link('sale/coupon', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['links'] = [
            'update' => $this->url->link('sale/coupon/update', 'coupon_id=' . $this->request->get['coupon_id'], 'SSL'),
        ];

        $this->document->setTitle($this->language->get('coupon_history'));

        $this->template = 'sale/coupon/history.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render_ecwig());
    }

    public function dtHistoryHandler()
    {
        $this->load->model('sale/coupon');
        $request = $this->request->request;

        $start = $request['start'];
        $length = $request['length'];

        $return = $this->model_sale_coupon->dtHistoryHandler([
            'start' => $start,
            'length' => $length,
            'coupon_id' => $request['coupon_id']
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

    public function dtHandler()
    {
        $this->load->model('sale/coupon');
        $request = $this->request->request;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'manufacturer_id',
            1 => 'name',
            4 => 'sort_order',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $last_coupon_in_limit_id =null;
        if($this->plan_id == 3){
            $last_coupon_in_limit_id = $this->model_sale_coupon->getLastCouponInLimitId();
        }
        $return = $this->model_sale_coupon->dtHandler([
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];

        if($this->plan_id == 3){
            $last_coupon_in_limit_id = $this->model_sale_coupon->getLastCouponInLimitId();
            foreach ($records as $key => $record){
                if($last_coupon_in_limit_id && $record['coupon_id'] > $last_coupon_in_limit_id){
                    $records[$key]['limit_reached']=1;
                }else{
                    $records[$key]['limit_reached']=0;
                }
            }
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records,
            'last_coupon_in_limit_id'=> $last_coupon_in_limit_id
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
        $this->language->load('sale/coupon');
        $this->load->model('sale/coupon');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("coupon");

            $this->load->model('loghistory/histories');
            foreach ($this->request->post['selected'] as $coupon_id) {
                // get copoun current value for log history
                $oldValue = $this->model_sale_coupon->getCoupon($coupon_id);
                $this->load->model('loghistory/coupon');
                $oldValue['coupon_category'] = $this->model_loghistory_coupon->getCouponCategories($coupon_id,0);
                $oldValue['coupon_product'] = $this->model_loghistory_coupon->getCouponProducts($coupon_id,0);
                $oldValue['coupon_manufacturer'] = $this->model_loghistory_coupon->getCouponManufacturer($coupon_id,0);

                $oldValue['coupon_category_excluded'] = $this->model_loghistory_coupon->getCouponCategories($coupon_id,1);
                $oldValue['coupon_product_excluded'] = $this->model_loghistory_coupon->getCouponProducts($coupon_id,1);
                $oldValue['coupon_manufacturer_excluded'] = $this->model_loghistory_coupon->getCouponManufacturer($coupon_id,1);

                $this->model_sale_coupon->deleteCoupon($coupon_id);
                // add data to log_history
                if($pageStatus){
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $coupon_id;
                    $log_history['old_value'] = json_encode($oldValue);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'coupon';
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

        $this->load->model("sale/coupon");
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];

            if($this->plan_id == 3){
                $last_coupon_in_limit_id = $this->model_sale_coupon->getLastCouponInLimitId();

                if($last_coupon_in_limit_id && $id > $last_coupon_in_limit_id){
                    $response['limit_reached'] = '1';
                    $this->response->setOutput(json_encode($response));
                    return;
                }
            }

            $coupon = $this->model_sale_coupon->getCoupon($id);
            $this->load->model('loghistory/coupon');
            $oldValue = $coupon;
            $coupon["status"] = $status;
            // the oldValue the same with current except status
            $oldValue['coupon_category'] =  $coupon['coupon_category'] = $this->model_sale_coupon->getCouponCategories($id,0);
            $oldValue['coupon_product'] = $coupon['coupon_product']= $this->model_sale_coupon->getCouponProducts($id,0);
            $oldValue['coupon_manufacturer'] = $coupon['coupon_manufacturer'] = $this->model_sale_coupon->getCouponManufacturer($id,0);

            $oldValue['coupon_category_excluded'] = $coupon['coupon_category_excluded'] =  $this->model_sale_coupon->getCouponCategories($id,1);
            $oldValue['coupon_product_excluded'] = $coupon['coupon_product_excluded'] = $this->model_sale_coupon->getCouponProducts($id,1);
            $oldValue['coupon_manufacturer_excluded'] = $coupon['coupon_manufacturer_excluded'] = $this->model_sale_coupon->getCouponManufacturer($id,1);

            $this->model_sale_coupon->editCoupon($id, $coupon);
            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("coupon");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $id;
                $log_history['old_value'] = json_encode($oldValue);
                $coupon['status'] = $status;
                $log_history['new_value'] = json_encode($coupon);
                $log_history['type'] = 'coupon';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }


            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_langstatus_success');
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

    public function massEditCouponsStatus(){
        $this->load->model("sale/coupon");
        if (!isset($this->request->post['coupon_id'])) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_REQUEST',
                'errors' => ['1']
            ]));
            return;
        }
        $couponIds = $this->request->post['coupon_id'];

        $affiliateIds = $this->request->post['affiliate_id'];
        foreach ($affiliateIds as &$id) {
            $num = intval($id);
            if (!intval($id))
                unset($id);
            else
                $id = $num;
        }
        $affiliateIds = array_unique($affiliateIds);
        $discount = $this->request->post['discount'];
        $couponsType = $this->request->post['type'];
        $status = ($this->request->post['status'] &&
            strtolower($this->request->post['status']) == "on") ? 1 : 0;
        $affiliate_commission = $this->request->post['affiliate_commission'];
        $data = [
            'couponIds' => $couponIds ,
            'discount' => $discount ,
            'type' => $couponsType ,
            'status' => $status ,
            'affiliateIds' =>$affiliateIds ,
            'affiliate_commission'=>$affiliate_commission
        ];

        $this->model_sale_coupon->massEditCouponsStatus($data);

        $this->response->setOutput(json_encode([
            'status' => 'OK'
        ]));
        return;

    }

    public function generate_code()
    {
        $result_json['success'] = '1';
        $randomString = $this->getCode();
        $result_json['code'] =$randomString;
        $this->response->setOutput(json_encode($result_json));
        return;
    }

    private function getCode(){
        $length = 20;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    private function handlePostData()
    {
        // by default does not apply coupon on shipping
        $this->request->post['shipping'] = 2;
        if( $this->request->post['type']== 'P'){
            $this->request->post['discount'] = $this->request->post['percent'];
        }

        if (! $this->request->post['minimum_to_apply_enabled'] ){
            $this->request->post['minimum_to_apply']=null;
            $this->request->post['maximum_limit']=null;
        }

        if (! $this->request->post['uses_customer_enabled'] ){
            $this->request->post['uses_customer']=null;
        }

        if (! $this->request->post['uses_total_enabled'] ){
            $this->request->post['uses_total']=null;
        }

        if( $this->request->post['type']== 'B'){

            if ($this->request->post['details']['buy_item_from'] != "all") {
                if ($this->request->post['details']['buy_item_from'] == "product") {
                    $this->request->post['coupon_product'] = $this->request->post['coupon_product_buy'];
                }

                if ($this->request->post['details']['buy_item_from'] == "category") {
                    $this->request->post['coupon_category'] = $this->request->post['coupon_category_buy'];
                }

                if ($this->request->post['details']['buy_item_from'] == "manufacturer") {
                    $this->request->post['coupon_manufacturer'] = $this->request->post['coupon_manufacturer_buy'];
                }
            }

            if ($this->request->post['details']['get_item_from'] != "all"){
                if ($this->request->post['details']['get_item_from'] == "product"){
                    $this->request->post['details']['coupon_product_get']= $this->request->post['coupon_product_get'];
                }else if($this->request->post['details']['get_item_from'] == "category"){
                    $this->request->post['details']['coupon_category_get']= $this->request->post['coupon_category_get'];
                }else if($this->request->post['details']['get_item_from'] == "manufacturer"){
                    $this->request->post['details']['coupon_manufacturer_get']= $this->request->post['coupon_manufacturer_get'];
                }
            }

            $this->request->post['details']['exclude_item']=null;
            $this->request->post['details']['apply_item_from']=null;
            $this->request->post['coupon_category_excluded']=null;
            $this->request->post['coupon_product_excluded']=null;
            $this->request->post['coupon_manufacturer_excluded']=null;
            if ( $this->request->post['details']['buy_option']=="purchase"){
                $this->request->post['minimum_to_apply']=null;
            }
        }else{
            if ($this->request->post['details']["apply_item_from"] !="all" || $this->request->post['details']["exclude_item"] !="none"){
                // to apply the coupon on the subtotal without including the shipping fee
                $this->request->post['shipping'] = 2;
            }
            $this->request->post['details']['buy_item_from']=null;
            $this->request->post['details']['buy_option']=null;
            $this->request->post['details']['buy_quantity']=null;
            $this->request->post['details']['buy_amount']=null;

            $this->request->post['details']['get_item_from']=null;
            $this->request->post['details']['get_quantity']=null;
            $this->request->post['details']['get_discount_value_option']=null;
            $this->request->post['details']['get_percentage']=null;
        }

        if( $this->request->post['type']== 'S'){
            $this->request->post['type'] = "F";
            $this->request->post['shipping'] = 1;
            $this->request->post['discount'] = 0;
        }

        if ( $this->request->post['details']["customer_option"] != "all"){
            $this->request->post['logged']=1;
        }

        if($this->request->post['details']["customer_option"] == "customer"){
            $this->request->post['coupon_group']=null;
        }else if($this->request->post['details']["customer_option"] == "group"){
            $this->request->post['coupon_customer']=null;
        }else{
            $this->request->post['coupon_group']=null;
            $this->request->post['coupon_customer']=null;
        }
    }

    private function validateRequiredNumber($value,$name,$text){
        if ((utf8_strlen($value) == 0 || $value <= 0)){
            $this->error[$name] = $this->language->get($text);
        }
    }
}
