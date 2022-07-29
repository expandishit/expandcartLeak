<?php

class ControllerAliexpressIncome extends ControllerAliexpressBase
{
    public function index()
    {
        $this->language->load('aliexpress/income');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL'),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/income', '', 'SSL'),
        );

        $data['orders'] = array();

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        $this->initializer(['aliexpress/income']);
        $income = $this->income->getIncome($filter_data);
        $data['income'] = array();
        $grandsTotalOfTotal = $grandTotalPayable = $grandTotalPaid = 0;
        $grandsTotalOfWarehouse = $grandsTotalOfAdmin = $grandshippingAmount = $grandGrossIncome = 0;
        if ($income) {
            foreach ($income as $key => $value) {
                $grandsTotalOfTotal += $value['total_income'];
                $grandsTotalOfAdmin += $value['adminAmount'];
                $grandsTotalOfWarehouse += $value['warehouseAmount'];
                if (!$value['payable']) {
                    $value['payable'] = 0;
                }

                $grandTotalPayable += $value['payable'] + $value['shippingAmount'];
                $grandTotalPaid += ($value['warehouseAmount'] - $value['payable']);
                $grandshippingAmount += $value['shippingAmount'];
                $grandGrossIncome += $value['shippingAmount'] + $value['warehouseAmount'];
                // paidShippingAmount
                $data['income'][] = array(
                    'name' => $value['firstname'].' '.$value['lastname'],
                    'view_user' => $this->url->link('user/user/edit', 'user_id=' . $value['user_id'], true),
                    'warehouse_id' => $value['warehouse_id'],
                    'warehouse_code' => $value['warehouse_code'],
                    'title' => $value['title'],
                    'total_income' => $this->currency->format($value['total_income'], $this->config->get('config_currency')),
                    'adminAmount' => $this->currency->format($value['adminAmount'], $this->config->get('config_currency')),
                    'warehouseAmount' => $this->currency->format(
                        $value['warehouseAmount'], $this->config->get('config_currency')
                    ),
                    'payableAmount' => $this->currency->format(
                        $value['payable'] + $value['shippingAmount'], $this->config->get('config_currency')
                    ),
                    'paidAmount' => $this->currency->format(
                        $value['warehouseAmount'] - $value['payable'], $this->config->get('config_currency')
                    ),
                    'payable' => $value['payable'] + $value['shippingAmount'],
                    'shippingAmount' => $this->currency->format(
                        $value['shippingAmount'], $this->config->get('config_currency')
                    ),
                    'grossIncome' => $this->currency->format(
                        $value['shippingAmount'] + $value['warehouseAmount'], $this->config->get('config_currency')
                    ),
                    'pay' => $this->url->link('aliexpress/transaction', 'warehouse_id='.$value['warehouse_id'], true),
                    'warehouse_adddress' => trim(
                        $value['street'] . ', '.$value['city'] . ', ' . $value['zone'] . ', ' . $value['country'], ','
                    ),
                );
            }
        }

        $data['grandsTotalOfTotal'] = $this->currency->format($grandsTotalOfTotal, $this->config->get('config_currency'));
        $data['grandsTotalOfAdmin'] = $this->currency->format($grandsTotalOfAdmin, $this->config->get('config_currency'));
        $data['grandsTotalOfWarehouse'] = $this->currency->format(
            $grandsTotalOfWarehouse, $this->config->get('config_currency')
        );
        $data['grandTotalPayable'] = $this->currency->format($grandTotalPayable, $this->config->get('config_currency'));
        $data['grandTotalPaid'] = $this->currency->format($grandTotalPaid, $this->config->get('config_currency'));
        $data['grandshippingAmount'] = $this->currency->format($grandshippingAmount, $this->config->get('config_currency'));
        $data['grandGrossIncome'] = $this->currency->format($grandGrossIncome, $this->config->get('config_currency'));

        // $pagination = new Pagination();
        // $pagination->total = $warehouseOrdersTotal;
        // $pagination->page = $page;
        // $pagination->limit = $this->config->get('config_admin_limit');
        // $pagination->url = $this->url->link('customerpartner/managewarehouse', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');
        $data['pagination'] = '';
        // $data['pagination'] = $pagination->render();

        $data['results'] = '';
        // $data['results'] = sprintf($this->language->get('text_pagination'), ($warehouseOrdersTotal) ? (($page - 1) * $this->config->get('config_admin_limit')) + 1 : 0, ((($page - 1) * $this->config->get('config_admin_limit')) > ($warehouseOrdersTotal - $this->config->get('config_admin_limit'))) ? $warehouseOrdersTotal : ((($page - 1) * $this->config->get('config_admin_limit')) + $this->config->get('config_admin_limit')), $warehouseOrdersTotal, ceil($warehouseOrdersTotal / $this->config->get('config_admin_limit')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['cancel'] = $this->url->link('aliexpress/orders', '', 'SSL');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['isWarehouseManager'] = false;
        if ($this->user->getUserGroup() == $this->config->get('wk_dropship_user_group')) {
            $data['isWarehouseManager'] = true;
        }

        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $this->template = 'aliexpress/income.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }
}
