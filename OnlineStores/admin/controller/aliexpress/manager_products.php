<?php

class ControllerAliexpressManagerProducts extends ControllerAliexpressBase
{
    public function index()
    {
        $this->load->language('aliexpress/products');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->initializer([
            'settings' => 'setting/setting',
            'aliexpress/manager',
        ]);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', '', 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('aliexpress/manager_products', '', 'SSL')
        );

        $data['products'] = array();

        $data['wk_dropship_warehouse_manager_add_product'] = $this->config->get('wk_dropship_warehouse_manager_add_product');
        $data['wk_dropship_product_mass_upload'] = $this->config->get('wk_dropship_product_mass_upload');
        $warehouseList = $this->manager->getWarehouseByUserId();
        $data['warehouseList'] = array();
        if($warehouseList) {
            foreach ($warehouseList as $key => $value) {
                $data['warehouseList'][] = array(
                    'title' => $value['title'],
                    'href' => $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . '&warehouse_id='. $value['warehouse_id'], true),
                );
            }
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $filter_data['start'] = ($page - 1) * $this->config->get('config_admin_limit');
        $filter_data['limit'] = $this->config->get('config_admin_limit');

        $results = $this->manager->getProducts($filter_data);

        $product_total = $this->manager->getTotalProducts($filter_data);
        $data['products'] = array();
        if($results) {
            foreach ($results as $result) {
                $sold_quantity = 0;
                if($result['sold_quantity']) {
                    $sold_quantity = $result['sold_quantity'];
                }
                if($result['quantity'] - $sold_quantity <= 0) {
                    $warehouse_quantity = 0;
                } else {
                    $warehouse_quantity = $result['quantity'] - $sold_quantity;
                }
                $data['products'][] = array(
                    'warehouse_code'         => $result['warehouse_code'],
                    'warehouse_title'         => $result['title'],
                    'name'         => $result['name'],
                    'model'        => $result['model'],
                    'quantity'     => $result['quantity'],
                    'price'        => $this->currency->format($result['price'], $this->config->get('config_currency')),
                    'status'       => $result['approved']
                );

            }
        }

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->url = $this->url->link('aliexpress/manager_products', 'page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_admin_limit')) + 1 : 0, ((($page - 1) * $this->config->get('config_admin_limit')) > ($product_total - $this->config->get('config_admin_limit'))) ? $product_total : ((($page - 1) * $this->config->get('config_admin_limit')) + $this->config->get('config_admin_limit')), $product_total, ceil($product_total / $this->config->get('config_admin_limit')));


        $this->template = 'aliexpress/manager/products.expand';

        $this->children = array(
            'common/header',
            'common/footer',
        );

        $this->data = $data;

        $this->response->setOutput($this->render_ecwig());
    }
}
