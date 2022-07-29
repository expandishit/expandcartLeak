<?php

class ControllerAliexpressAliexpressOrder extends ControllerAliexpressBase
{
    public function index()
    {
        $this->initializer(['aliexpress/warehouses']);

        $this->load->language('aliexpress/orders');

        $this->document->setTitle($this->language->get('heading_title_aliexpress'));

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
            'text' => $this->language->get('heading_title_aliexpress'),
            'href' => $this->url->link('aliexpress/aliexpress_order', '', true),
        );

        $filter_data = array();
        $filter_data['start'] = ($page - 1) * $this->config->get('config_admin_limit');
        $filter_data['limit'] = $this->config->get('config_admin_limit');

        $ali_express_orders = $this->warehouses->getAliExpressOrders($filter_data);

        $ali_express_orders_total = $this->warehouses->getAliExpressOrdersTotal($filter_data);
        $data['orders'] = array();
        if ($ali_express_orders) {
            foreach ($ali_express_orders as $key => $ali_express_order) {
                $options = array();
                $wk_product_options = array();
                $wk_alix_product_id = array();
                foreach (array_reverse(explode(',', $ali_express_order['order_product_id'])) as $order_product_id) {
                    $alix_product_id = $this->warehouses->getAlixProductId($ali_express_order['order_id'], $order_product_id);

                    if ($alix_product_id) {
                        $wk_alix_product_id[] =number_format($alix_product_id,0,'','');
                    } else {
                        $wk_alix_product_id[] = '';
                    }
                }

                $place_order_url = (
                    $ali_express_order['product_url'] .
                    '?ec_order_id=' .
                    $ali_express_order['order_id'] .
                    '&ec_product_ids='.implode($wk_alix_product_id, '_')
                );

                if(strpos($place_order_url, 'aliexpress.com') == false){
                    $place_order_url="https://ar.aliexpress.com".$place_order_url;
                 }

                if (isset($ali_express_order['aliexpress_order_status'])) {
                    if (empty($ali_express_order['aliexpress_order_status'])) {
                        $aliexpress_order_status = $data['text_processing'];
                    } elseif (1 == $ali_express_order['aliexpress_order_status']) {
                        $aliexpress_order_status = $data['text_placed'];
                    } else {
                        $aliexpress_order_status = $data['text_not_placed'];
                    }
                } else {
                    if ($ali_express_order['order_status_id'] == $this->config->get('wk_dropship_complete_order_status')) {
                        $aliexpress_order_status = $data['text_placed'];
                    } else {
                        $aliexpress_order_status = $data['text_not_placed'];
                    }
                }

                $data['orders'][] = array(
                    'order_id' => $ali_express_order['order_id'],
                    'aliexpress_order_status' => $aliexpress_order_status,
                    'customer' => $ali_express_order['customer'],
                    'total' => $this->currency->format(
                        $ali_express_order['sum_price'],
                        $ali_express_order['currency_code']
                    ),
                    'date_added' => $ali_express_order['date_added'],
                    'date_modified' => $ali_express_order['date_modified'],
                    'place_order_url' => $place_order_url,
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $ali_express_orders_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/aliexpress_order', 'page={page}', true);

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

        $this->template = 'aliexpress/aliexpress/order.expand';

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
            'aliexpress/warehouses',
            'tool/image'
        ]);
        $this->load->language('aliexpress/orders');    
        $return = $this->warehouses->getAliExpressOrders([
            'start' => $start,
            'limit' => $length
        ]);

        $data = [];
        foreach ($return as $key => $ali_express_order) {
            $options = array();
            $wk_product_options = array();
            $wk_alix_product_id = array();
            foreach (array_reverse(explode(',', $ali_express_order['order_product_id'])) as $order_product_id) {
                $alix_product_id = $this->warehouses->getAlixProductId($ali_express_order['order_id'], $order_product_id);

                if ($alix_product_id) {
                    $wk_alix_product_id[] = number_format($alix_product_id,0,'','');
                } else {
                    $wk_alix_product_id[] = '';
                }
            }

            $place_order_url = (
                $ali_express_order['product_url'] .
                '?ec_order_id=' .
                $ali_express_order['order_id'] .
                '&ec_product_ids='.implode($wk_alix_product_id, '_')
            );
            if(strpos($place_order_url, 'aliexpress.com') == false){
               $place_order_url="https://ar.aliexpress.com".$place_order_url;
            }

            if (isset($ali_express_order['aliexpress_order_status'])) {
                if (empty($ali_express_order['aliexpress_order_status'])) {
                    $aliexpress_order_status = $this->language->get('text_processing');//$data['text_processing'];
                } elseif (1 == $ali_express_order['aliexpress_order_status']) {
                    $aliexpress_order_status = $this->language->get('text_placed');//$data['text_placed'];
                } else {
                    $aliexpress_order_status = $this->language->get('text_not_placed');//$data['text_not_placed'];
                }
            } else {
                if ($ali_express_order['order_status_id'] == $this->config->get('wk_dropship_complete_order_status')) {
                    $aliexpress_order_status = $this->language->get('text_placed');//$data['text_placed'];
                } else {
                    $aliexpress_order_status = $this->language->get('text_not_placed');//$data['text_not_placed'];
                }
            }

            $data[] = array(
                'order_id' => $ali_express_order['order_id'],
                'aliexpress_order_status' => $aliexpress_order_status,
                'customer' => $ali_express_order['customer'],
                'total' => $this->currency->format(
                    $ali_express_order['sum_price'],
                    $ali_express_order['currency_code']
                ),
                'date_added' => $ali_express_order['date_added'],
                'date_modified' => $ali_express_order['date_modified'],
                'place_order_url' => $place_order_url,
            );
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => count($return),
            "recordsFiltered" => intval($this->warehouses->getAliExpressOrdersTotal([])),
            "data" => $data
        );

        $this->response->setOutput(json_encode($json_data));
        return;
    }
}
