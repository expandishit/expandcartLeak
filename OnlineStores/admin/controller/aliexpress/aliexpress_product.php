<?php

class ControllerAliexpressAliexpressProduct extends ControllerAliexpressBase
{
    private $error = array();

    public function index()
    {
        $this->load->language('aliexpress/products');
        $this->initializer(['aliexpress/assign_warehouse']);

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
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/aliexpress_product', '', true),
        );

        $filter_data = array();
        $filter_data['start'] = ($page - 1) * $this->config->get('config_admin_limit');
        $filter_data['limit'] = $this->config->get('config_admin_limit');

        $ali_express_products = $this->assign_warehouse->getAliExpressProducts($filter_data);
        $ali_express_products_total = $this->assign_warehouse->getAliExpressProductsTotal($filter_data);

        $data['products'] = array();
        $this->load->model('tool/image');
        if ($ali_express_products) {
            foreach ($ali_express_products as $key => $ali_express_product) {
                if ($ali_express_product['image']) {
                    $image = $this->model_tool_image->resize($ali_express_product['image'], 40, 40);
                } else {
                    $image = $this->model_tool_image->resize('no_image.png', 50, 50);
                }

                $pid = $ali_express_product['product_id'];
                if(strpos($ali_express_product['product_url'], 'aliexpress.com') == false){
                    $ali_express_product['product_url']="https://ar.aliexpress.com".$ali_express_product['product_url'];
                 }

                $data['products'][] = array(
                    'id' => $ali_express_product['id'],
                    'ali_product_id' => $ali_express_product['ali_product_id'],
                    'product_url' => $ali_express_product['product_url'],
                    'product_id' => $ali_express_product['product_id'],
                    'image' => $image,
                    'name' => $ali_express_product['name'],
                    'status' => $ali_express_product['status'],
                    'update_url' => $ali_express_product['product_url'] . '?ec_product_id=' . $pid . '&ec_product_update=true',
                    'edit' => $this->url->link('catalog/product/update', 'product_id=' . $pid , true),
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $ali_express_products_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/aliexpress_product', 'page={page}', true);

        $data['pagination'] = $pagination->render();

        if (isset($this->request->get['filter_aliexpress_seller_id'])) {
            $data['filter_aliexpress_seller_id'] = $this->request->get['filter_aliexpress_seller_id'];
            $data['cancel'] = $this->url->link('aliexpress/aliexpress_seller', '', true);
        }

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

        $this->template = 'aliexpress/aliexpress/products.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function delete()
    {
        $data = array_merge($data = array(), $this->load->language('aliexpress/products'));

        $this->document->setTitle($data['heading_title']);

        $this->initializer(['aliexpress/assign_warehouse']);

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            $this->load->model('catalog/product');
            foreach ($this->request->post['selected'] as $product_id) {
                $this->model_catalog_product->deleteProduct($product_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->response->redirect($this->url->link('aliexpress/aliexpress_product', '', true));
    }

    public function pushToStore()
    {
        $this->load->language('aliexpress/products');
        if ('POST' == $this->request->server['REQUEST_METHOD'] && isset($this->request->post['selected'])) {
            $list = $this->request->post['selected'];
            if ($list) {
                $this->initializer(['aliexpress/assign_warehouse']);
                foreach ($list as $key => $value) {
                    $this->assign_warehouse->pushToStore($value);
                }
                $this->session->data['success'] = $this->language->get('success_product_approved');
            } else {
                $this->session->data['error_warning'] = $this->language->get('error_warehouse_product_approve_no_select');
            }
            $this->response->redirect($this->url->link('aliexpress/aliexpress_product', '', true));
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_general');
            $this->response->redirect($this->url->link('aliexpress/aliexpress_product', '', true));
        }
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'aliexpress/aliexpress_product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
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
            1 => 'image',
            2 => 'name',
            3 => 'ali_product_id',
            4 => 'status',
            5 => 'date_added'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $this->initializer([
            'settings' => 'setting/setting',
            'aliexpress/manager',
            'aliexpress/assign_warehouse',
            'tool/image'
        ]);

        $return = $this->assign_warehouse->getAliExpressProducts([
            'start' => $start,
            'limit' => $length,
            'filter_product_name' => $search,
            'sort' => $orderColumn,
            'order' => $orderType
        ]);

        $data = [];
        foreach ($return as $key => $ali_express_product) {
            if ($ali_express_product['image']) {
                $image = $this->image->resize($ali_express_product['image'], 40, 40);
            } else {
                $image = $this->image->resize('no_image.png', 50, 50);
            }

            $pid = $ali_express_product['product_id'];
            if(strpos($ali_express_product['product_url'], 'aliexpress.com') == false){
                $ali_express_product['product_url']="https://ar.aliexpress.com".$ali_express_product['product_url'];
             }
            $data[] = array(
                'id' => $ali_express_product['id'],
                'ali_product_id' => $ali_express_product['ali_product_id'],
                'product_url' => $ali_express_product['product_url'],
                'product_id' => $ali_express_product['product_id'],
                'image' => $image,
                'name' => $ali_express_product['name'],
                'status' => $ali_express_product['status'],
                'date_added' => $ali_express_product['date_added'],
                'update_url' => $ali_express_product['product_url'] . '?ec_product_id=' . $pid . '&ec_product_update=true',
                'edit' => (string)$this->url->link('catalog/product/update', 'product_id=' . $pid , true),
            );
        }

        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => count($return),
            "recordsFiltered" => intval($this->assign_warehouse->getAliExpressProductsTotal([])),
            "data" => $data
        );

        $this->response->setOutput(json_encode($json_data));
        return;
    }
}
