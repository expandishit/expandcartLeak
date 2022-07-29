<?php

class ControllerEbayEbayOrder extends ControllerEbayBase
{
    public function index()
    {
        $this->initializer(['ebay/commerces']);

        $this->load->language('ebay/orders');

        $this->document->setTitle($this->language->get('heading_title_ebay'));

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_ebay'),
            'href' => $this->url->link('ebay/ebay_order', '', true),
        );

        $filter_data = array();
        $filter_data['start'] = ($page - 1) * $this->config->get('config_admin_limit');
        $filter_data['limit'] = $this->config->get('config_admin_limit');

        $e_bay_orders = $this->commerces->getEbayOrders($filter_data);

        $e_bay_orders_total = $this->commerces->getEbayOrdersTotal($filter_data);
        $data['orders'] = array();
        if ($e_bay_orders) {
            foreach ($e_bay_orders as $key => $e_bay_order) {
                //get url for each product
                $products_id = explode(',',$e_bay_order['ebay_product_id']);
                $products_url = $this->commerces->getEbayProductsUrl($products_id);


                $order_products_id = explode(',',$e_bay_order['order_product_id']);
                $wk_ebay_product_id = $this->commerces->getEbayProductsId($e_bay_order['order_id'], $order_products_id);

                $place_order_url = (
                    $e_bay_order['product_url'] .
                    '?ec_order_id=' .
                    $e_bay_order['order_id'] .
                    '&ec_product_ids='.implode($wk_ebay_product_id, '_')
                );

                if (isset($e_bay_order['ebay_order_status'])) {
                    if (empty($e_bay_order['ebay_order_status'])) {
                        $ebay_order_status = $data['text_processing'];
                    } elseif (1 == $e_bay_order['ebay_order_status']) {
                        $ebay_order_status = $data['text_placed'];
                    } else {
                        $ebay_order_status = $data['text_not_placed'];
                    }
                } else {
                    if ($e_bay_order['order_status_id'] == $this->config->get('wk_ebay_dropship_complete_order_status')) {
                        $ebay_order_status = $data['text_placed'];
                    } else {
                        $ebay_order_status = $data['text_not_placed'];
                    }
                }

                $data['orders'][] = array(
                    'order_id' => $e_bay_order['order_id'],
                    'ebay_order_status' => $ebay_order_status,
                    'customer' => $e_bay_order['customer'],
                    'total' => $this->currency->format(
                        $e_bay_order['sum_price'],
                        $e_bay_order['currency_code']
                    ),
                    'date_added' => $e_bay_order['date_added'],
                    'date_modified' => $e_bay_order['date_modified'],
                    'place_order_url' => $place_order_url,
                    'products_url'=>$products_url
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $e_bay_orders_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('ebay/ebay_order', 'page={page}', true);

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

        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        } else {
            $data['error_warning'] = '';
        }

        $this->template = 'ebay/ebay/order.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function list()
    {
        $this->load->model('catalog/review');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'product_id',
            1 => 'name',
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $this->initializer([
            'settings' => 'setting/setting',
            'ebay/commerces',
            'tool/image'
        ]);
        $this->load->language('ebay/orders');
        $return = $this->commerces->getEbayOrders([
            'start' => $start,
            'limit' => $length
        ]);

        $data = [];
        foreach ($return as $key => $e_bay_order) {

            //get url for each product
            $products_id = explode(',',$e_bay_order['ebay_product_id']);
            $products_url = $this->commerces->getEbayProductsUrl($products_id);


            $order_products_id = explode(',',$e_bay_order['order_product_id']);
            $wk_ebay_product_id = $this->commerces->getEbayProductsId($e_bay_order['order_id'], $order_products_id);

            $place_order_url = (
                $e_bay_order['product_url'] .
                '?ec_order_id=' .
                $e_bay_order['order_id'] .
                '&ec_product_ids='.implode($wk_ebay_product_id, '_')
            );

            if (isset($e_bay_order['ebay_order_status'])) {
                if (empty($e_bay_order['ebay_order_status'])) {
                    $ebay_order_status = $this->language->get('text_processing');//$data['text_processing'];
                } elseif (1 == $e_bay_order['ebay_order_status']) {
                    $ebay_order_status = $this->language->get('text_placed');//$data['text_placed'];
                } else {
                    $ebay_order_status = $this->language->get('text_not_placed');//$data['text_not_placed'];
                }
            } else {
                if ($e_bay_order['order_status_id'] == $this->config->get('wk_ebay_dropship_complete_order_status')) {
                    $ebay_order_status = $this->language->get('text_placed');//$data['text_placed'];
                } else {
                    $ebay_order_status = $this->language->get('text_not_placed');//$data['text_not_placed'];
                }
            }

            $data[] = array(
                'order_id' => $e_bay_order['order_id'],
                'ebay_order_status' => $ebay_order_status,
                'customer' => $e_bay_order['customer'],
                'total' => $this->currency->format(
                    $e_bay_order['sum_price'],
                    $e_bay_order['currency_code']
                ),
                'date_added' => $e_bay_order['date_added'],
                'date_modified' => $e_bay_order['date_modified'],
                'place_order_url' => $place_order_url,
                'products_url'=>$products_url
            );
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => count($return),
            "recordsFiltered" => intval($this->commerces->getEbayOrdersTotal([])),
            "data" => $data
        );

        $this->response->setOutput(json_encode($json_data));
        return;
    }
}
