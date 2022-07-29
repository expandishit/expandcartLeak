<?php 
class ControllerModuleAffiliatePromo extends Controller { 

    private $validationErrors = [];

    public function index() {
        if (!$this->affiliate->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('module/affiliate_promo', '', 'SSL');
            $this->redirect($this->url->link('affiliate/login', '', 'SSL'));
        }

        if ($this->config->get('affiliate_promo')['off_create']) {
            $this->redirect($this->url->link('affiliate/account', '', 'SSL'));
        }

        $this->language->load_json('module/affiliate_promo');

        if ($this->request->post) {
            $this->load->model('module/affiliate_promo');
            if (!$this->validate()) {
                $this->data['errors'] = $this->validationErrors;
            } elseif ($this->model_module_affiliate_promo->promoCodeExists($this->request->post['code'])) {
                $this->data['errors']['affiliate_promo_code_exists_error'] = $this->language->get('affiliate_promo_code_exists_error');
            } else {
                $data = $this->request->post;
                $noTrackingRequired=$this->config->get('affiliate_promo')['no_tracking'];
                $this->setDefaultPromoCodeValues($data);
                $this->setCouponStatus($data ,$noTrackingRequired);
                $this->setShippingTypeToCoupon($data ,$noTrackingRequired);
                $this->setTaxTypeToCoupon($data ,$noTrackingRequired);
                $data['date_start'] = date('Y-m-d');
                $data['date_end'] = date('Y-m-d',strtotime(date('Y-m-d', time()) . ' + 365 day'));
                $this->model_module_affiliate_promo->addPromoCode($data, $this->affiliate->getId());
                if($noTrackingRequired)
                    $successMessage = $this->language->get('affiliate_promo_success_pending');
                else
                    $successMessage = $this->language->get('affiliate_promo_success');

                $this->data['success'] = $successMessage;
            }
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->language->get('affiliate_promo_heading_title'),
            'href'      => $this->url->link('module/affiliate_promo', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['promo_code_submit_link'] = $this->url->link('module/affiliate_promo', '', 'SSL');
        $this->data['promo_code_max_percent_ratio'] = $this->config->get('affiliate_promo')['max_coupon_percent_ratio'];
        $this->data['promo_code_max_fixed_ratio'] = $this->config->get('affiliate_promo')['max_coupon_fixed_ratio'];
        $this->data['coupon'] = $this->request->post ? $this->request->post : '';

        $this->data['affiliates_create_only_code_type_discount'] = $this->config->get('affiliate_promo')['affiliates_create_only_code_type_discount'];
        $this->data['affiliates_cannot_create_promo_type_discount'] = $this->config->get('affiliate_promo')['affiliates_cannot_create_promo_type_discount'];

        $this->document->setTitle($this->language->get('affiliate_promo_heading_title'));

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/affiliate_promo/form.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/affiliate_promo/form.expand';
        }
        else {
            $this->template = 'default/template/module/affiliate_promo/form.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );
                
        $this->response->setOutput($this->render_ecwig());
    }

    public function edit()
    {
        if (!$this->affiliate->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('module/affiliate_promo', '', 'SSL');
            $this->redirect($this->url->link('affiliate/login', '', 'SSL'));
        } 

        $this->language->load_json('module/affiliate_promo');
        $this->load->model('module/affiliate_promo');

        if (!$this->request->request['coupon_id']) {
            $this->response->redirect($this->url->link('module/affiliate_promo/add', '', 'SSL'));
            return;
        }

        if ($this->request->post) {
            if (!$this->validate()) {
                $this->data['errors'] = $this->validationErrors;
            }
            elseif ($this->model_module_affiliate_promo->promoCodeExists($this->request->post['code'])) {
                $this->data['errors']['affiliate_promo_code_exists_error'] = $this->language->get('affiliate_promo_code_exists_error');
            }
            else {
                $this->setDefaultPromoCodeValues($this->request->post);
                $this->model_module_affiliate_promo->editPromoCode($this->request->post, $this->affiliate->getId());
                $this->data['success'] = $this->language->get('affiliate_promo_success');
            }
        }

        if ($this->request->post) {
            $coupon = $this->request->post;
        } else {
            $coupon = $this->model_module_affiliate_promo->getPromoCode($this->request->request['coupon_id'], $this->affiliate->getId());
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->language->get('affiliate_promo_heading_title'),
            'href'      => $this->url->link('module/affiliate_promo/edit', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['promo_code_submit_link'] = $this->url->link('module/affiliate_promo/edit&coupon_id=' . $this->request->request['coupon_id'], '', 'SSL');
        $this->data['promo_code_max_percent_ratio'] = $this->config->get('affiliate_promo')['max_coupon_percent_ratio'];
        $this->data['promo_code_max_fixed_ratio'] = $this->config->get('affiliate_promo')['max_coupon_fixed_ratio'];
        $this->data['coupon'] = $coupon;
        $this->data['affiliates_create_only_code_type_discount'] = $this->config->get('affiliate_promo')['affiliates_create_only_code_type_discount'];
        $this->data['affiliates_cannot_create_promo_type_discount'] = $this->config->get('affiliate_promo')['affiliates_cannot_create_promo_type_discount'];

        $this->document->setTitle($this->language->get('affiliate_promo_heading_title'));

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/affiliate_promo/edit.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/affiliate_promo/edit.expand';
        }
        else {
            $this->template = 'default/template/module/affiliate_promo/edit.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );
                
        $this->response->setOutput($this->render_ecwig());
    }

    public function list() {
        if (!$this->affiliate->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('module/affiliate_promo/list', '', 'SSL');
            $this->redirect($this->url->link('affiliate/login', '', 'SSL'));
        } 

        $this->language->load_json('module/affiliate_promo');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        ); 

        $this->data['breadcrumbs'][] = array(       	
            'text'      => $this->language->get('affiliate_promo_list'),
            'href'      => $this->url->link('module/affiliate_promo/list', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['ajax_link'] = $this->url->link('module/affiliate_promo/getAjaxList', '', 'SSL');
        $this->data['ajax_delete_link'] = $this->url->link('module/affiliate_promo/delete', '', 'SSL');

        $this->document->setTitle($this->language->get('affiliate_promo_list'));

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/affiliate_promo/list.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/module/affiliate_promo/list.expand';
        }
        else {
            $this->template = 'default/template/module/affiliate_promo/list.expand';
        }
        
        $this->children = array(
            'common/footer',
            'common/header'
        );
                
        $this->response->setOutput($this->render_ecwig());
    }

    public function getAjaxList()
    {
        $this->load->model('module/affiliate_promo');
        $this->language->load_json('module/affiliate_promo');
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;
        $request = $this->request->request;

        $start = $request['start'];
        $length = $request['length'];

        $columns = [
            0 => 'code',
            1 => 'type',
            2 => 'discount',
            3 => 'status'
        ];

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];


        $return = $this->model_module_affiliate_promo->getPromoCodes([
            'affiliate_id' => $this->affiliate->getId(),
            'search' => $request['search']['value'],
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = [];

        foreach($return['data'] as $row) {
            if ($row['status'])
                $row['status']= $this->language->get('enabled');
            else
                $row['status']= $this->language->get('pending');

            $records[] = [
                'coupon_id' => $row['coupon_id'],
                'code' => $row['code'],
                'type' => $row['type'],
                'discount' => $row['discount'],
                'status' => $row['status'],
                'total_use' => $row['total_use'],
                'share_link' => str_replace('?route=', '', $this->url->link('?promo=' . $row['code'] . '&tracking=' . $row['tracking_code'], '', 'SSL')),
                'edit' => $this->url->link('module/affiliate_promo/edit&coupon_id=' . $row['coupon_id'], '', 'SSL'),
                'order_history' => $this->url->link('affiliate/history', 'coupon='.$row['coupon_id'], 'SSL')
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
        $this->model_module_affiliate_promo->deletePromoCode($this->request->post['coupon_id'], $this->affiliate->getId());
    }

    private function validate()
    {
        $affiliatesCannotCreatePromoTypeDiscount
            = $this->config->get('affiliate_promo')['affiliates_cannot_create_promo_type_discount'];
        if (empty($this->request->post['code'])) {
            $this->validationErrors['affiliate_promo_code_error'] = $this->language->get('affiliate_promo_code_error');
        }
        if (!$affiliatesCannotCreatePromoTypeDiscount){
            if (empty($this->request->post['type'])) {
                $this->validationErrors['affiliate_promo_code_type_error'] = $this->language->get('affiliate_promo_code_type_error');
            }

            if (empty($this->request->post['discount'])) {
                $this->validationErrors['affiliate_promo_code_ratio_error'] = $this->language->get('affiliate_promo_code_ratio_error');
            }

            if (empty($this->request->post['discount'])) {
                $this->validationErrors['affiliate_promo_code_ratio_error'] = $this->language->get('affiliate_promo_code_ratio_error');
            }

            if ($this->request->post['type'] === 'P') {
                $ratio = (float) $this->config->get('affiliate_promo')['max_coupon_percent_ratio'];
                if ( (float) $this->request->post['discount'] > $ratio) {
                    $this->validationErrors['affiliate_promo_code_ratio_error'] = $this->language->get('affiliate_promo_code_max_ratio_error') . $ratio . '%';
                }
            }

            if ($this->request->post['type'] === 'F') {
                $ratio = (float) $this->config->get('affiliate_promo')['max_coupon_fixed_ratio'];
                if ( (float) $this->request->post['discount'] > $ratio) {
                    $this->validationErrors['affiliate_promo_code_ratio_error'] = $this->language->get('affiliate_promo_code_max_ratio_error') . $ratio;
                }
            }
        }


        return empty($this->validationErrors);
    }
    private function setDefaultPromoCodeValues(&$postData){
        if ($this->config->get('affiliate_promo')['affiliates_create_only_code_type_discount']){
            $postData['minimum_to_apply'] = '1';
            $postData['maximum_limit'] = '0';
            $postData['uses_per_coupon'] = '0';
            $postData['uses_per_customer'] = '0';
        }
    }
    private function setCouponStatus(&$postData , $noTrackingOption)
    {
        if ($noTrackingOption)
            $postData['status'] = 0;
        else
            $postData['status'] = 1;
    }

    //  'No Tracking required' option  enabled
    // then do not apply coupon on shipping cost
    private function setShippingTypeToCoupon(&$postData , $noTrackingOption){
        if ($noTrackingOption)
            $postData['shipping'] = 2;
        else
            $postData['shipping'] = 0;
    }
    //  'No Tracking required' option  enabled
    // then do not apply coupon on tax
    private function setTaxTypeToCoupon(&$postData , $noTrackingOption){
        if ($noTrackingOption)
            $postData['tax_excluded'] = 1;
        else
            $postData['tax_excluded'] = 0;
    }

}
