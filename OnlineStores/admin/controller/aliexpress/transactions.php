<?php

class ControllerAliexpressTransactions extends ControllerAliexpressBase
{
    public function index()
    {
        $this->language->load('aliexpress/transactions');

        $this->document->setTitle($this->language->get('heading_title'));

        $warehouse_id = 0;
        if (isset($this->request->get['warehouse_id'])) {
            $warehouse_id = $this->request->get['warehouse_id'];
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_no_warehouse');
            $this->response->redirect($this->url->link('aliexpress/income', '', 'SSL'));
        }

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
            'href' => $this->url->link('warehouse/warehousetransaction', 'warehouse_id='.$warehouse_id, 'SSL'),
        );

        $data['orders'] = array();

        $filter_data = array(
            'warehouse_id' => $warehouse_id,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        $this->initializer(['aliexpress/income']);
        $data['supplierInfo'] = $this->income->getSupplierDetailsByWarehouseId($warehouse_id);

        $orders = $this->income->getUnpaidOrderProductsByWarehouseId($filter_data);
        $data['income'] = array();
        $totalPayable = $grandsTotalOfWarehouse = $grandsTotalOfAdmin = 0;
        if ($orders) {
            foreach ($orders as $key => $order) {
                $totalPayable += $order['warehouseAmount'];
                $data['orders'][] = array(
                    'warehouse_order_id' => $order['warehouse_order_id'],
                    'product_name' => $order['name'],
                    'model' => $order['model'],
                    'order_id' => $order['order_id'],
                    'warehouse_id' => $order['warehouse_id'],
                    'product_id' => $order['product_id'],
                    'total' => $this->currency->format($order['total'], $this->config->get('config_currency')),
                    'warehouse_amount' => $this->currency->format($order['warehouseAmount'], $this->config->get('config_currency')),
                    'admin_amount' => $this->currency->format($order['adminAmount'], $this->config->get('config_currency')),
                    'paybale_amount' => $order['warehouseAmount'],
                    'order_status' => $order['order_status'],
                    'order_date' => date_format(date_create($order['date_added']), 'd-m-Y'),
                );
            }
        }

        $data['totalPayable'] = $this->currency->format($totalPayable, $this->config->get('config_currency'));

        // $pagination = new Pagination();
        // $pagination->total = $warehouseOrdersTotal;
        // $pagination->page = $page;
        // $pagination->limit = $this->config->get('config_admin_limit');
        // $pagination->url = $this->url->link('customerpartner/managewarehouse', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');
        $data['pagination'] = '';
        // $data['pagination'] = $pagination->render();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $this->template = 'aliexpress/transactions/list.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function log()
    {
        $this->language->load('aliexpress/transactions');

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
            'text' => $this->language->get('heading_title_log'),
            'href' => $this->url->link('aliexpress/transactions/log', '', 'SSL'),
        );

        $data['orders'] = array();
        $filter_data = array();
        $filter_data['start'] = ($page - 1) * $this->config->get('config_admin_limit');
        $filter_data['limit'] = $this->config->get('config_admin_limit');

        $this->initializer(['aliexpress/income']);
        $transactionLogs = $this->income->getTransactionLogs($filter_data);
        $orderstransactionLogsTotal = $this->income->getTransactionLogsTotal($filter_data);
        $data['transactionLogs'] = array();
        $totalPaid = $grandsTotalOfWarehouse = $grandsTotalOfAdmin = 0;
        if ($transactionLogs) {
            foreach ($transactionLogs as $key => $transactionLog) {
                $totalPaid += $transactionLog['total'];
                if (strlen($transactionLog['description']) > 50) {
                    $transactionLog['description'] = substr($transactionLog['description'], 0, 50).'<button type="button" class="view-more-description" data-toggle="popover" data-content="'.$transactionLog['description'].'" >...</button>';
                }
                $data['transactionLogs'][] = array(
                    'warehouse_id' => $transactionLog['warehouse_id'],
                    'warehouse_code' => $transactionLog['warehouse_code'],
                    'title' => $transactionLog['title'],
                    'name' => $transactionLog['name'],
                    'description' => $transactionLog['description'],
                    'date_added' => $transactionLog['date_added'],
                    'total' => $this->currency->format($transactionLog['total'], $this->config->get('config_currency')),
                    // 'title' => $transactionLog['title'],
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $orderstransactionLogsTotal;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/transactions/log', 'page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $this->template = 'aliexpress/transactions/log.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function addTransactionlog()
    {
        $this->language->load('warehouse/warehousetransaction');
        if ('POST' == $this->request->server['REQUEST_METHOD']) {
            $data = $this->request->post;
            if ($data && isset($data['selected']) && $data['selected']) {
                if (isset($data['selected'][0]) && 0 == $data['selected'][0]) {
                    array_shift($data['selected']);
                }
                $description = $data['description'];
                $this->initializer(['aliexpress/income']);
                $this->income->addTransactionLog($data['selected'], $description);
                $this->session->data['success'] = $this->language->get('success_transaction_logged');
                $this->response->redirect($this->url->link('warehouse/warehouseincome', 'user_token='.$this->session->data['user_token'], true));
            } else {
                $this->session->data['warning'] = $this->language->get('error_nothing_selected');
                $this->response->redirect($this->url->link('warehouse/warehouseincome', 'user_token='.$this->session->data['user_token'], true));
            }
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_server');
        }
    }
}
