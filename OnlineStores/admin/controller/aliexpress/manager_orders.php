<?php

class ControllerAliexpressManagerOrders extends ControllerAliexpressBase
{
    public function index()
    {
        $this->language->load('aliexpress/orders');

        $this->initializer(['aliexpress/orders']);

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
            'href' => $this->url->link('aliexpress/manager_orders', '', 'SSL'),
        );

        $data['orders'] = array();

        $filter_data = array(
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit'),
        );

        $warehouseOrders = $this->orders->getWarehouseOrders($filter_data);
        $warehouseOrdersTotal = $this->orders->getWarehouseOrdersTotal($filter_data);

        $data['warehouseOrders'] = array();
        if ($warehouseOrders) {
            foreach ($warehouseOrders as $warehouseOrder) {
                $data['warehouseOrders'][] = array(
                    'order_id' => $warehouseOrder['order_id'],
                    'title' => $warehouseOrder['title'],
                    'warehouse_code' => $warehouseOrder['warehouse_code'],
                    'date_added' => $warehouseOrder['date_added'],
                    'billname' => $warehouseOrder['payment_firstname'].' '.$warehouseOrder['payment_lastname'],
                    'shipname' => $warehouseOrder['shipping_firstname'].' '.$warehouseOrder['shipping_lastname'],
                    'totalprice' => $this->currency->format(
                        $warehouseOrder['product_total'] + $warehouseOrder['cost'],
                        $this->config->get('config_currency')
                    ),
                    'order_status' => $warehouseOrder['order_status'],
                    'view' => $this->url->link(
                        'aliexpress/manager_orders/info',
                        'order_id=' . $warehouseOrder['order_id'].'&warehouse_id='.$warehouseOrder['warehouse_id'],
                        'SSL'
                    ),
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $warehouseOrdersTotal;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/manager_orders', 'page={page}', 'SSL');

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

        $this->template = 'aliexpress/manager/orders.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function info()
    {
        $this->language->load('aliexpress/orders');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('sale/order');
        $this->initializer(['aliexpress/orders']);

        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        if (isset($this->request->get['warehouse_id'])) {
            $warehouse_id = $this->request->get['warehouse_id'];
        } else {
            $warehouse_id = 0;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('aliexpress/manager_orders', 'user_token='.$this->session->data['user_token'], 'SSL'),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/manager_orders/info', 'user_token='.$this->session->data['user_token'].'&order_id='.$this->request->get['order_id'].$url, 'SSL'),
        );

        $order_info = $this->orders->getOrder($order_id);

        if ($order_info) {
            $this->document->setTitle($this->language->get('heading_title'));
            $orderDetails = $this->orders->getOrderProducts($this->request->get['order_id'], $warehouse_id);
            $data['orderDetails'] = array();
            $grandTotal = 0;
            if ($orderDetails && $orderDetails['products']) {
                foreach ($orderDetails['products'] as $key => $product) {
                    $grandTotal += $product['price'] * $product['quantity'];
                    $data['orderDetails']['products'][] = array(
                        'product_id' => $product['product_id'],
                        'model' => $product['model'],
                        'name' => $product['name'],
                        'productOptions' => $this->orders->getOrderOptions($order_id, $product['order_product_id']),
                        'quantity' => $product['quantity'],
                        'price' => $this->currency->format(
                            $product['price'],
                            $this->config->get('config_currency')
                        ),
                        'total' => $this->currency->format(
                            $product['price'] * $product['quantity'],
                            $this->config->get('config_currency')
                        ),
                    );
                }

                $data['orderDetails']['others'] = $orderDetails['shipping'];
                $grandTotal = $grandTotal + $data['orderDetails']['others']['cost'];
                $data['orderDetails']['others']['text'] = $this->currency->format(
                    $data['orderDetails']['others']['cost'],
                    $this->config->get('config_currency')
                );
                $data['orderDetails']['grandTotal'] = $this->currency->format(
                    $grandTotal,
                    $this->config->get('config_currency')
                );
            }
        }

        $data['invoice'] = $this->url->link(
            'aliexpress/manager_orders/invoice',
            'order_id='.(int) $this->request->get['order_id'].'&warehouse_id='.(int) $warehouse_id,
            true
        );

        $data['cancel'] = $this->url->link('aliexpress/manager_orders', '', true);

        $this->template = 'aliexpress/manager/order_info.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function invoice()
    {
        $this->load->language('sale/order');
        $this->language->load('aliexpress/orders');

        if ($this->request->server['HTTPS']) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        $this->load->model('sale/order');

        $this->initializer(['aliexpress/orders']);

        $this->load->model('setting/setting');

        $data['orders'] = array();

        $orders = array();

        if (isset($this->request->get['warehouse_id'])) {
            $warehouse_id = $this->request->get['warehouse_id'];
        } else {
            $warehouse_id = 0;
        }

        if (isset($this->request->post['selected'])) {
            $orders = $this->request->post['selected'];
        } elseif (isset($this->request->get['order_id'])) {
            $orders[] = $this->request->get['order_id'];
        }

        foreach ($orders as $order_id) {
            $order_info = $this->orders->getOrder($order_id);

            if ($order_info) {
                $store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

                if ($store_info) {
                    $store_address = $store_info['config_address'];
                    $store_email = $store_info['config_email'];
                    $store_telephone = $store_info['config_telephone'];
                    $store_fax = $store_info['config_fax'];
                } else {
                    $store_address = $this->config->get('config_address');
                    $store_email = (!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email'));
                    $store_telephone = $this->config->get('config_telephone');
                    $store_fax = $this->config->get('config_fax');
                }

                if ($order_info['invoice_no']) {
                    $invoice_no = $order_info['invoice_prefix'].$order_info['invoice_no'];
                } else {
                    $invoice_no = '';
                }

                if ($order_info['payment_address_format']) {
                    $format = $order_info['payment_address_format'];
                } else {
                    $format = '{firstname} {lastname}'."\n".'{company}'."\n".'{address_1}'."\n".'{address_2}'."\n".'{city} {postcode}'."\n".'{zone}'."\n".'{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}',
                );

                $replace = array(
                    'firstname' => $order_info['payment_firstname'],
                    'lastname' => $order_info['payment_lastname'],
                    'company' => $order_info['payment_company'],
                    'address_1' => $order_info['payment_address_1'],
                    'address_2' => $order_info['payment_address_2'],
                    'city' => $order_info['payment_city'],
                    'postcode' => $order_info['payment_postcode'],
                    'zone' => $order_info['payment_zone'],
                    'zone_code' => $order_info['payment_zone_code'],
                    'country' => $order_info['payment_country'],
                );

                $payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                if ($order_info['shipping_address_format']) {
                    $format = $order_info['shipping_address_format'];
                } else {
                    $format = '{firstname} {lastname}'."\n".'{company}'."\n".'{address_1}'."\n".'{address_2}'."\n".'{city} {postcode}'."\n".'{zone}'."\n".'{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}',
                );

                $replace = array(
                    'firstname' => $order_info['shipping_firstname'],
                    'lastname' => $order_info['shipping_lastname'],
                    'company' => $order_info['shipping_company'],
                    'address_1' => $order_info['shipping_address_1'],
                    'address_2' => $order_info['shipping_address_2'],
                    'city' => $order_info['shipping_city'],
                    'postcode' => $order_info['shipping_postcode'],
                    'zone' => $order_info['shipping_zone'],
                    'zone_code' => $order_info['shipping_zone_code'],
                    'country' => $order_info['shipping_country'],
                );

                $shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                $product_data = array();

                $orderDetails = $this->orders->getOrderProducts($this->request->get['order_id'], $warehouse_id);
                $data['orderDetails'] = array();
                $grandTotal = 0;
                if ($orderDetails && $orderDetails['products']) {
                    foreach ($orderDetails['products'] as $key => $product) {
                        $grandTotal += $product['price'] * $product['quantity'];
                        $data['orderDetails']['products'][] = array(
                            'product_id' => $product['product_id'],
                            'model' => $product['model'],
                            'name' => $product['name'],
                            'productOptions' => $this->orders->getOrderOptions($order_id, $product['order_product_id']),
                            'quantity' => $product['quantity'],
                            'price' => $this->currency->format($product['price'], $this->config->get('config_currency')),
                            'total' => $this->currency->format($product['price'] * $product['quantity'], $this->config->get('config_currency')),
                        );
                    }

                    $data['orderDetails']['others'] = $orderDetails['shipping'];
                    $grandTotal = $grandTotal + $data['orderDetails']['others']['cost'];
                    $data['orderDetails']['others']['text'] = $this->currency->format($data['orderDetails']['others']['cost'], $this->config->get('config_currency'));
                    $data['orderDetails']['grandTotal'] = $this->currency->format($grandTotal, $this->config->get('config_currency'));
                }

                $voucher_data = array();

                $vouchers = $this->orders->getOrderVouchers($order_id);

                foreach ($vouchers as $voucher) {
                    $voucher_data[] = array(
                        'description' => $voucher['description'],
                        'amount' => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
                    );
                }

                $results = $this->orders->getwarehouseorderById($this->request->get['order_id'], $warehouse_id);

                $data['orders'][] = array(
                    'order_id' => $order_id,
                    'invoice_no' => $invoice_no,
                    'date_added' => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                    'store_name' => $order_info['store_name'],
                    'store_url' => rtrim($order_info['store_url'], '/'),
                    'store_address' => nl2br($store_address),
                    'store_email' => $store_email,
                    'store_telephone' => $store_telephone,
                    'store_fax' => $store_fax,
                    'email' => $order_info['email'],
                    'telephone' => $order_info['telephone'],
                    'shipping_address' => $shipping_address,
                    'shipping_method' => $order_info['shipping_method'],
                    'payment_address' => $payment_address,
                    'payment_method' => $order_info['payment_method'],
                    'product' => $product_data,
                    'voucher' => $voucher_data,
                    // 'total'              => $data['totals'],
                    'comment' => nl2br($order_info['comment']),
                );
            }
        }

        $this->template = 'aliexpress/manager/order_invoice.expand';

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }
}
