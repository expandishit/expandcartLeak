<?php

class ControllerMultisellerSellerGroup extends ControllerMultisellerBase
{
    private $error = array();

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function getTableData()
    {
        $colMap = array(
            'id' => 'msg.seller_group_id'
        );

        $sorts = array('id', 'name', 'description');
        $filters = $sorts;

        $this->initializer([
            'sellerGroup' => 'multiseller/sellergroup'
        ]);

        list($sortCol, $sortDir) = $this->getSortParams($sorts, $colMap);
        $filterParams = $this->getFilterParams($filters, $colMap);

        $results = $this->sellerGroup->getSellerGroups(
            array(),
            array(
                'order_by' => $sortCol,
                'order_way' => $sortDir,
                'filters' => $filterParams,
                'offset' => $this->request->post['start'],
                'limit' => $this->request->post['length']
            )
        );

        $total = isset($results[0]) ? $results[0]['total_rows'] : 0;

        $columns = array();
        foreach ($results as $result) {

            $rates = $this->MsLoader->MsCommission->calculateCommission([
                'seller_group_id' => $result['seller_group_id']
            ]);

            $actual_fees = '';
            foreach ($rates as $rate) {
                $fees = '';

                $fees .= '<span class="fee-rate-' . $rate['rate_type'] . '">';
                $fees .= '<b>' . $this->language->get('ms_commission_short_' . $rate['rate_type']) . ':';
                $fees .= '</b>' . ($rate['rate_type'] != MsCommission::RATE_SIGNUP ? $rate['percent'] . '%+' : '');
                $fees .= $this->currency->getSymbolLeft();
                $fees .= $this->currency->format($rate['flat'], $this->config->get('config_currency'), '', false);
                $fees .= $this->currency->getSymbolRight() . '&nbsp;&nbsp;';
                $actual_fees .= $fees;
            }

            $description = $result['description'];
            if (mb_strlen($result['description']) > 80) {
                $description = mb_substr($result['description'], 0, 80) . '...';
            }

            $columns[] = array_merge(
                $result,
                array(
                    'checkbox' => "<input type='checkbox' name='selected[]' value='{$result['seller_group_id']}' />",
                    'id' => $result['seller_group_id'],
                    'name' => $result['name'],
                    'description' => $description,
                    'rates' => $actual_fees,
                )
            );
        }

        $this->response->setOutput(json_encode(array(
            'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $total,
            'aaData' => $columns
        )));
    }

    // List all the seller groups
    public function index()
    {
        $this->validate(__FUNCTION__);

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_catalog_seller_groups_breadcrumbs'),
                'href' => $this->url->link('multiseller/seller-group', '', 'SSL'),
            )
        ));

        $this->data['insert'] = $this->url->link('multiseller/seller-group/insert', '', 'SSL');
        $this->data['delete'] = $this->url->link('multiseller/seller-group/delete', '', 'SSL');

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

        $this->data['token'] = $this->session->data['token'];
        $this->data['heading'] = $this->language->get('ms_catalog_seller_groups_heading');
        $this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->document->setTitle($this->language->get('ms_catalog_seller_groups_heading'));

        $this->template = 'multiseller/seller/group-list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    // Insert a new seller group
    public function insert()
    {
        $this->load->model('tool/image');
        $this->data['token'] = $this->session->data['token'];
        $this->data['heading'] = $this->language->get('ms_catalog_insert_seller_group_heading');
        $this->document->setTitle($this->language->get('ms_catalog_insert_seller_group_heading'));

        $this->data['cancel'] = $this->url->link('multiseller/seller-group', '', 'SSL');

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->data['seller_group'] = NULL;

        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_catalog_seller_groups_breadcrumbs'),
                'href' => $this->url->link('multiseller/seller-group', '', 'SSL'),
            )
        ));

        $this->data['rateSale'] = MsCommission::RATE_SALE;
        $this->data['rateListing'] = MsCommission::RATE_LISTING;
        $this->data['rateSignup'] = MsCommission::RATE_SIGNUP;
        $this->data['method_balance'] = MsPayment::METHOD_BALANCE;
        $this->data['method_paypal'] = MsPayment::METHOD_PAYPAL;
        $this->data['leftCurrencySymbol'] = $this->currency->getSymbolLeft();
        $this->data['rightCurrencySymbol'] = $this->currency->getSymbolRight();

        $this->template = 'multiseller/seller/group-form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    // Update a seller group
    public function update()
    {
        $this->validate(__FUNCTION__);
        $this->load->model('tool/image');
        $this->data['token'] = $this->session->data['token'];
        $this->data['heading'] = $this->language->get('ms_catalog_edit_seller_group_heading');
        $this->document->setTitle($this->language->get('ms_catalog_edit_seller_group_heading'));

        $this->data['cancel'] = $this->url->link('multiseller/seller-group', '', 'SSL'); //'multiseller/seller-group';

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $seller_group = $this->MsLoader->MsSellerGroup->getSellerGroup($this->request->get['seller_group_id']);

        if (is_null($seller_group['commission_id']))
            $rates = NULL;
        else
            $rates = $this->MsLoader->MsCommission->getCommissionRates($seller_group['msg.commission_id']);

        $this->data['seller_group'] = array(
            'seller_group_id' => $seller_group['seller_group_id'],
            'description' => $this->MsLoader->MsSellerGroup->getSellerGroupDescriptions(
                $this->request->get['seller_group_id']
            ),
            'product_period' => $seller_group['product_period'],
            'product_quantity' => $seller_group['product_quantity'],
            'commission_id' => $seller_group['commission_id'],
            'commission_rates' => $rates,
        );
        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->admSetBreadcrumbs(array(
            array(
                'text' => $this->language->get('ms_menu_multiseller'),
                'href' => $this->url->link('module/multiseller', '', 'SSL'),
            ),
            array(
                'text' => $this->language->get('ms_catalog_seller_groups_breadcrumbs'),
                'href' => $this->url->link('multiseller/seller-group', '', 'SSL'),
            )
        ));

        $this->data['rateSale'] = MsCommission::RATE_SALE;
        $this->data['rateListing'] = MsCommission::RATE_LISTING;
        $this->data['rateSignup'] = MsCommission::RATE_SIGNUP;
        $this->data['method_balance'] = MsPayment::METHOD_BALANCE;
        $this->data['method_paypal'] = MsPayment::METHOD_PAYPAL;
        $this->data['leftCurrencySymbol'] = $this->currency->getSymbolLeft();
        $this->data['rightCurrencySymbol'] = $this->currency->getSymbolRight();

        // print_r($this->data['seller_group']); die();

        $this->template = 'multiseller/seller/group-form.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    // Bulk delete of seller groups
    public function delete()
    {
        if (isset($this->request->get['seller_group_id'])) {
            $this->request->post['selected'] = array($this->request->get['seller_group_id']);
        }

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $seller_group_id) {
                $this->MsLoader->MsSellerGroup->deleteSellerGroup($seller_group_id);
            }

            $this->session->data['success'] = $this->language->get('ms_success');
        }

        $this->redirect($this->url->link('multiseller/seller-group', '', 'SSL'));
    }

    // Get form for adding/editing seller groups
    private function getEditForm()
    {
        $this->data['heading'] = $this->language->get('ms_catalog_insert_seller_group_heading');

        if (!isset($this->request->get['seller_group_id'])) {
            $this->data['action'] = $this->url->link('multiseller/seller-group/insert', '', 'SSL');
        } else {
            $this->data['action'] = $this->url->link(
                'multiseller/seller-group/update',
                'seller_group_id=' . $this->request->get['seller_group_id'],
                'SSL'
            );
        }

        $this->data['cancel'] = $this->url->link('multiseller/seller-group', '', 'SSL');

        if (isset($this->request->get['seller_group_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            //$seller_group_info = $this->MsLoader->MsSellerGroup->getSellerGroup($
            //this->request->get['seller_group_id']
            //);
        }

        //$this->MsLoader->MsSellerGroup->getSellerGroupDescriptions($this->request->get['seller_group_id']);


        if (isset($this->request->post['seller_group_description'])) {
            $this->data['seller_group_description'] = $this->request->post['seller_group_description'];
        } elseif (isset($this->request->get['seller_group_id'])) {
            $this->data['seller_group_description'] = 'a';
        } else {
            $this->data['seller_group_description'] = array();
        }

        list($this->template, $this->children) = $this->MsLoader->MsHelper->admLoadTemplate('seller-group-form');
        $this->response->setOutput($this->render());
    }

    private function validateForm()
    {
    }

    // Validate delete of the seller group
    private function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'multiseller/seller-group')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->request->post['selected'] as $seller_group_id) {
            if ($this->config->get('msconf_default_seller_group_id') == $seller_group_id) {
                $this->error['warning'] = $this->language->get('ms_error_seller_group_default');
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function jxSave()
    {
        $data = $this->request->post['seller_group'];
        $json = array();

        if (!isset($data['product_period']) || empty($data['product_period'])) $data['product_period'] = 0;
        if (empty($data['product_quantity'])) $data['product_quantity'] = 0;

        foreach ($data['description'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
                $json['errors']['name_' . $language_id] = $this->language->get('ms_error_seller_group_name');
            }
        }

        if (
            !empty($data['seller_group_id']) &&
            $this->config->get('msconf_default_seller_group_id') == $data['seller_group_id']
        ) {
            foreach ($data['commission_rates'] as &$rate) {
                if (empty($rate['flat'])) $rate['flat'] = 0;
                if (empty($rate['percent'])) $rate['percent'] = 0;
                if (!isset($rate['payment_method']) || (int)$rate['payment_method'] == 0) $rate['payment_method'] = 1;
            }
            unset($rate);
        }
        // var_dump(empty($data['seller_group_id']));
        // print_r($data);die();
        if (empty($json['errors'])) {
            if (empty($data['seller_group_id'])) {
                $this->MsLoader->MsSellerGroup->createSellerGroup($data);
                $this->session->data['success'] = $this->language->get('ms_success_seller_group_created');
            } else {
                $this->MsLoader->MsSellerGroup->editSellerGroup($data['seller_group_id'], $data);
                $this->session->data['success'] = $this->language->get('ms_success_seller_group_updated');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function jxGetCategories(){
        $this->load->model('catalog/category');
        $categories = $this->model_catalog_category->getCategories($data);
        $categories = array_column($categories, 'name','category_id');
        $this->response->setOutput(json_encode($categories));
    }
}

