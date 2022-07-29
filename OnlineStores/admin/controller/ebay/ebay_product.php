<?php

class ControllerEbayEbayProduct extends ControllerEbayBase
{
    private $error = array();

    public function index()
    {
        $this->load->language('ebay/products');
        $this->initializer(['ebay/assign_commerce']);

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
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('ebay/ebay_product', '', true),
        );

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

        $this->template = 'ebay/ebay/products.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }

    public function delete()
    {
        $data = array_merge($data = array(), $this->load->language('ebay/products'));

        $this->document->setTitle($data['heading_title']);

        $this->initializer(['ebay/assign_commerce']);

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            $this->load->model('catalog/product');
            foreach ($this->request->post['selected'] as $product_id) {
                $this->model_catalog_product->deleteProduct($product_id);
            }

            $this->session->data['success'] = $this->language->get('success_commerce_product_delete');
        }

        $this->response->redirect($this->url->link('ebay/ebay_product', '', true));
    }

    public function pushToStore()
    {
        $this->load->language('ebay/products');
        if ('POST' == $this->request->server['REQUEST_METHOD'] && isset($this->request->post['selected'])) {
            $list = $this->request->post['selected'];
            if ($list) {
                $this->initializer(['ebay/assign_commerce']);
                foreach ($list as $key => $value) {
                    $this->assign_commerce->pushToStore($value);
                }
                $this->session->data['success'] = $this->language->get('success_product_approved');
            } else {
                $this->session->data['error_warning'] = $this->language->get('error_commerce_product_approve_no_select');
            }
            $this->response->redirect($this->url->link('ebay/ebay_product', '', true));
        } else {
            $this->session->data['error_warning'] = $this->language->get('error_general');
            $this->response->redirect($this->url->link('ebay/ebay_product', '', true));
        }
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'ebay/ebay_product')) {
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
            3 => 'ebay_product_id',
            4 => 'status',
            5 => 'date_added'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $this->initializer([
            'settings' => 'setting/setting',
            'ebay/assign_commerce',
            'tool/image'
        ]);

        $return = $this->assign_commerce->getEbayProducts([
            'start' => $start,
            'limit' => $length,
            'filter_product_name' => $search,
            'sort' => $orderColumn,
            'order' => $orderType
        ]);

        $data = [];
        foreach ($return as $key => $e_bay_product) {
            if ($e_bay_product['image']) {
                $image = $this->image->resize($e_bay_product['image'], 40, 40);
            } else {
                $image = $this->image->resize('no_image.png', 50, 50);
            }

            $pid = $e_bay_product['product_id'];

            $data[] = array(
                'id' => $e_bay_product['id'],
                'ebay_product_id' => $e_bay_product['ebay_product_id'],
                'product_url' => $e_bay_product['product_url'],
                'product_id' => $e_bay_product['product_id'],
                'image' => $image,
                'name' => $e_bay_product['name'],
                'status' => $e_bay_product['status'],
                'date_added' => $e_bay_product['date_added'],
                'update_url' => $e_bay_product['product_url'] . '?ec_product_id=' . $pid . '&ec_product_update=true',
                'edit' => (string)$this->url->link('catalog/product/update', 'product_id=' . $pid , true),
            );
        }

        $json_data = array(
            "draw" => (int)$request['draw'],
            "recordsTotal" => count($return),
            "recordsFiltered" => (int)$this->assign_commerce->getEbayProductsTotal([]),
            "data" => $data
        );

        $this->response->setOutput(json_encode($json_data));
        return;
    }

}
