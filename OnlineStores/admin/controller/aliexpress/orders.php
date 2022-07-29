<?php

class ControllerAliexpressOrders extends ControllerAliexpressBase
{
    public function index()
    {
        $this->load->language('aliexpress/orders');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer([
            'aliexpress/orders',
        ]);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/orders', '', 'SSL')
        );

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['orders'] = array();
        $filter_data = array();
        $filter_data['start']  = ($page - 1) * $this->config->get('config_admin_limit');
        $filter_data['limit']  = $this->config->get('config_admin_limit');

        $warehouseOrders = $this->orders->getWarehouseOrders($filter_data);
        $warehouseOrdersTotal = $this->orders->getWarehouseOrdersTotal($filter_data);

        $data['warehouseOrders'] = array();
        if ($warehouseOrders) {
            foreach ($warehouseOrders as $warehouseOrder) {
                $shippingCost = 0;
                if(isset($warehouseOrder['shipping']) && $warehouseOrder['shipping']) {
                    $shippingCost = unserialize($warehouseOrder['shipping'])['cost'];
                }
                if(!$warehouseOrder['title']) {
                    $warehouseOrder['title'] = "Admin";
                    $warehouseOrder['warehouse_code'] = "Admin";
                }
                // $this->currency->format($this->orders->getOrderTotal($warehouseOrder['order_id'],implode(unserialize($warehouseOrder['products']),','))+$shippingCost, $this->config->get('config_currency'))
                $data['warehouseOrders'][] = array(
                    'order_id'   => $warehouseOrder['order_id'],
                    'title'   => $warehouseOrder['title'],
                    'warehouse_code'   => $warehouseOrder['warehouse_code'],
                    'date_added'   => $warehouseOrder['date_added'],
                    'billname'   => $warehouseOrder['payment_firstname']." ".$warehouseOrder['payment_lastname'],
                    'shipname'   => $warehouseOrder['shipping_firstname']." ".$warehouseOrder['shipping_lastname'],
                    'totalprice' => $this->currency->format(
                        $warehouseOrder['product_total'] + $warehouseOrder['cost'],
                        $this->config->get('config_currency')
                    ),
                    'order_status' => $warehouseOrder['order_status'],
                    'view'        => $this->url->link(
                        'aliexpress/manager_orders/info',
                        'order_id=' . $warehouseOrder['order_id'] . '&warehouse_id=' . $warehouseOrder['warehouse_id'],
                        'SSL'
                    )
                );
            }
        }

        $this->template = 'aliexpress/orders.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function updateAccount()
    {
        if (
            $this->request->server['REQUEST_METHOD'] != 'POST' || !isset($_POST)
        ) {
            $this->response->setOutput(json_encode(['errors' => true]));

            return;
        }

        $userId = $this->session->data['user_id'];

        if ($userId == 999999999) {
            return $this->response->setOutput(json_encode([
                'success' => '0',
                'errors' => ['error' => $this->language->get('user_is_not_editable')]
            ]));
        }

        $this->initializer([
            'aliexpress/manager'
        ]);

        $this->manager->editUser($userId, $this->request->post);
        $this->manager->editPosition($userId, $this->request->post);

        $response['success_msg'] = $this->language->get('text_settings_success');
        $response['success'] = '1';

        $this->response->setOutput(json_encode($response));
    }
}
